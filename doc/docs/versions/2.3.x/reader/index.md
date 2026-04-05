# Data Readers

`DataReaderInterface` — это базовый контракт для всех классов, которые читают данные из разных источников.

Он определяет единый интерфейс, через который **Source** получает доступ к данным, не заботясь о том, откуда они приходят.

---

## Основные цели

- **Абстрагирование источников данных** — любой источник (локальный файл, HTTP, база данных, память) можно подключить через Reader.
- **Единый API** — методы `read()` всегда возвращают поток данных (resource) или выбрасывают исключение.
- **Потоковая обработка** — большие файлы и массивы можно читать без загрузки всего в память.
- **Расширяемость** — легко добавить новые типы источников, не меняя логику Source.

---

## Общая структура использования

1. Создаём Reader:

```php
$reader = new LocalFileReader('/path/to/file.json');
```

2. Передаём его в Source:
```php
$source = new Json($reader);
```

3. Итерация по данным:

```php
foreach ($source as $item) {
    // обработка
}
```

## Принципы работы

- Единый контракт — любой Reader реализует `DataReaderInterface`.
- Потоковое чтение — возвращается resource, Source решает, как его парсить.
- Обработка ошибок — все Reader выбрасывают исключения при проблемах с доступом или чтением.

## Расширяемость
- Чтобы создать новый Reader, достаточно:
- Создать класс, реализующий `DataReaderInterface`.
- Реализовать метод `read()`, который возвращает поток данных (resource) или выбрасывает исключение.
- Передавать новый Reader в Source.

Пример нового Reader:

```php
class HttpReader implements DataReaderInterface
{
    public function __construct(private string $url) {}
    
    public function read() {
        $content = file_get_contents($this->url);
        $resource = fopen('php://memory', 'r+b');
        fwrite($resource, $content);
        rewind($resource);
        return $resource;
    }
}
```