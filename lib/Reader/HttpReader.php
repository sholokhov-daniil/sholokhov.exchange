<?php

namespace Sholokhov\Exchange\Reader;

use Throwable;

use Sholokhov\Exchange\Exception\Reader\ReadException;
use Sholokhov\Exchange\Exception\Reader\ReaderException;
use Sholokhov\Exchange\Exception\Reader\AccessDeniedException;

use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Http\Request;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

/**
 * HTTP Reader для получения данных из внешних HTTP/HTTPS источников.
 *
 * Позволяет:
 * - использовать различные HTTP методы (GET, POST, PUT, DELETE, PATCH, HEAD)
 * - добавлять произвольные заголовки
 * - настраивать Basic, Bearer и API Key авторизацию
 * - использовать прокси
 * - возвращать данные в виде PHP resource потока
 *
 * Поток создаётся в памяти и содержит тело HTTP ответа.
 *
 * Примеры:
 * ```
 * $reader = new HttpReader($uri, $bodyStream)
 *     ->withBearerAuth($token)
 *     ->addHeader('Custom-Header', 'Value');
 * $stream = $reader->read();
 * ```
 * @package Reader
 */
class HttpReader implements DataReaderInterface, LoggerAwareInterface
{
    /**
     * Текущий HTTP метод запроса
     *
     * @var string
     */
    private string $method = 'GET';

    /**
     * URI запроса
     *
     * @var UriInterface
     */
    private readonly UriInterface $uri;

    /**
     * Тело запроса
     *
     * @var StreamInterface
     */
    private StreamInterface $body;

    /**
     * HTTP клиент Bitrix
     *
     * @var HttpClient
     */
    private readonly HttpClient $client;

    /**
     * Размер чанка при чтении тела ответа
     */
    private const CHUNK_SIZE = 8192;

    /**
     * HttpReader constructor.
     *
     * @param UriInterface $uri URI запроса
     * @param StreamInterface $body Тело запроса
     */
    public function __construct(UriInterface $uri, StreamInterface $body)
    {
        $this->body = $body;
        $this->uri = $uri;
        $this->client = new HttpClient;
    }

    /**
     * Выполняет HTTP запрос и возвращает тело ответа в виде потока (resource)
     *
     * @return resource Поток с данными HTTP ответа
     *
     * @throws ReaderException В случае ошибок соединения, создания потока или других проблем
     * @throws AccessDeniedException Если сервер вернул 401 или 403
     * @throws ReadException Если HTTP статус не 2xx
     */
    public function read()
    {
        $body = null;

        try {
            $request = new Request($this->method, $this->uri, [], $this->body);
            $response = $this->client->sendRequest($request);

            $this->checkResponse($response);

            $body = $response->getBody();
            $stream = fopen('php://temp', 'r+');

            if ($stream === false) {
                throw new ReaderException("Unable to open temp stream");
            }

            while (!$body->eof()) {
                fwrite($stream, $body->read(self::CHUNK_SIZE));
            }

            rewind($stream);

            return $stream;
        } catch (Throwable $e) {
            throw new ReaderException(
                "Failed to fetch data from $this->uri",
                previous: $e
            );
        } finally {
            $body?->close();
        }
    }

    /**
     * Устанавливает логгер для HTTP клиента
     *
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->client->setLogger($logger);
    }

    /**
     * Добавляет один заголовок к запросу
     *
     * @param string $key Название заголовка
     * @param string $value Значение заголовка
     * @return static
     */
    public function addHeader(string $key, string $value): static
    {
        $this->client->setHeader($key, $value);

        return $this;
    }

    /**
     * Устанавливает несколько заголовков сразу
     *
     * @param array $headers ['Header-Name' => 'Value', ...]
     * @return static
     */
    public function setHeaders(array $headers): static
    {
        $this->client->setHeaders($headers);
        return $this;
    }

    /**
     * Добавляет Basic авторизацию
     *
     * @param string $login Логин
     * @param string $password Пароль
     * @return static
     */
    public function withBasicAuth(string $login, string $password): static
    {
        $this->addHeader('Authorization', 'Basic ' . base64_encode($login . ':' . $password));
        return $this;
    }

    /**
     * Добавляет Bearer авторизацию
     *
     * @param string $token Токен
     * @return static
     */
    public function withBearerAuth(string $token): static
    {
        $this->addHeader('Authorization', 'Bearer ' . $token);
        return $this;
    }

    /**
     * Добавляет API Key авторизацию через заголовок X-API-Key
     *
     * @param string $apiKey
     * @return static
     */
    public function withApiKeyAuth(string $apiKey): static
    {
        $this->addHeader('X-API-Key', $apiKey);
        return $this;
    }

    /**
     * Устанавливает HTTP метод запроса
     *
     * @param string $method GET, POST, PUT, DELETE, PATCH, HEAD
     * @return static
     *
     * @throws ReaderException Если метод не поддерживается
     */
    public function setMethod(string $method): static
    {
        if (!in_array($method, $this->supportedMethods())) {
            throw new ReaderException('Invalid HTTP method: ' . $method);
        }

        $this->method = $method;
        return $this;
    }

    /**
     * Настраивает прокси
     *
     * @param string $host Хост прокси
     * @param int|null $port Порт
     * @param string|null $user Логин
     * @param string|null $password Пароль
     * @return static
     */
    public function setProxy(string $host, ?int $port = null, ?string $user = null, ?string $password = null): static
    {
        $this->client->setProxy($host, $port, $user, $password);
        return $this;
    }

    /**
     * Проверяет статус ответа
     *
     * @param ResponseInterface $response
     *
     * @throws AccessDeniedException 401/403
     * @throws ReadException Любой другой код ответа вне 2xx
     */
    private function checkResponse(ResponseInterface $response): void
    {
        $status = $response->getStatusCode();

        if ($status === 401 || $status === 403) {
            throw new AccessDeniedException();
        }

        if ($status < 200 || $status > 299) {
            throw new ReadException('HTTP status code: ' . $status);
        }
    }

    /**
     * Список поддерживаемых HTTP методов
     *
     * @return string[]
     */
    private function supportedMethods(): array
    {
        return ['GET', 'POST', 'PUT', 'HEAD', 'PATCH', 'DELETE'];
    }
}