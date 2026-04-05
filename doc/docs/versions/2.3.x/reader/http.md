# HttpReader

`HttpReader` — класс для получения данных из внешних HTTP/HTTPS источников.  
Возвращает поток данных (`resource`), который можно использовать в источниках (`Source`) вашего проекта.

---

## Назначение

- Выполнение HTTP-запросов (GET, POST, PUT, DELETE, PATCH, HEAD)
- Добавление заголовков, авторизации и прокси
- Потоковое чтение тела ответа (PHP `resource`)
- Интеграция с Source класами (`Json Source`, `Csv Source`)

---

## Возможности

- Поддержка **Basic, Bearer, API Key** авторизации
- Настройка **заголовков** (одного или нескольких)
- Настройка **HTTP метода**
- Настройка **прокси**
- Поддержка **логгера PSR-3** через `LoggerAwareInterface`
- Потоковое чтение ответа (не загружает весь контент в память)

---

## Принципы работы
1. Создаётся HTTP-запрос с заданным методом, заголовками и телом
2. Выполняется через Bitrix HttpClient
3. Проверяется HTTP-статус (2xx считается успешным)
4. Тело ответа читается чанками в поток php://temp
5. Поток возвращается для использования в Source

---

## Потоковое чтение
- Размер чанка: 8192 байт
- Позволяет обрабатывать большие ответы без перегрузки памяти
- Используется в Json Source, Csv Source и других DataReader’ах


---

## Пример использования

```php
use Sholokhov\Exchange\Reader\HttpReader;
use Nyholm\Psr7\Uri;
use Nyholm\Psr7\Stream;

// Создание URI и тела запроса
$uri = new Uri('https://api.example.com/data');
$body = Stream::create('');

// Создание HttpReader
$reader = new HttpReader($uri, $body)
    ->withBearerAuth('YOUR_TOKEN')
    ->addHeader('Custom-Header', 'Value');

// Получение ресурса
$stream = $reader->read();

// Использование данных
$content = stream_get_contents($stream);
fclose($stream);
```

## Основной метод

`read()` - Выполняет HTTP-запрос и возвращает поток с данными.  

**Возвращает:**  
`resource` — поток с телом ответа  

**Исключения:**  

| Исключение            | Причина                                    |
|-----------------------|--------------------------------------------|
| AccessDeniedException | 	Сервер вернул 401 или 403                 |
| ReadException         | 	Любой HTTP статус вне диапазона 2xx       |
| ReaderException       | 	Ошибка при создании потока или соединении |

## Методы настройки

### Заголовки

```php
addHeader(string $key, string $value): static
setHeaders(array $headers): static
```

### Авторизация
````php
withBasicAuth(string $login, string $password): static
withBearerAuth(string $token): static
withApiKeyAuth(string $apiKey): static
````

### Метод HTTP
```php
setMethod(string $method): static
```
Поддерживаемые методы: `GET`, `POST`, `PUT`, `DELETE`, `PATCH`, `HEAD`

### Прокси
```php
setProxy(string $host, ?int $port = null, ?string $user = null, ?string $password = null): static
```

### Логгер
```php
setLogger(LoggerInterface $logger): void
```

```php
use Sholokhov\Exchange\Source\Json;
use Sholokhov\Exchange\Reader\HttpReader;

use Bitrix\Main\Web\Uri;
use Bitrix\Main\Web\Http\Stream;

$uri = new Uri('https://api.example.com/items');
$stream = new Stream('php://temp', 'r+');

$reader = new HttpReader($uri, $stream)
    ->withBearerAuth('TOKEN');

$source = new Json($reader);

foreach ($source as $item) {
    // обработка элементов
}
```