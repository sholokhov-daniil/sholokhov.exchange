# Что изменилось в версии 2.3.x

> ⚠️ Нарушена обратная совестимость

Текущая версия содержит глобальные изменения в области конфигурации и изменения структуры библиотеки.
Что негативно сказалось на обратной совместимости. При переходе на текущую версию необходимо уделить должное внимание
проверке работоспособности обменов, чтобы выполненное действие не привело к потере работоспособности функционала.

## Добавлены провайдеры данных

[EntityProviderInterface](/2.3.x/provider/entity/) - провайдер сущностей предназначен для выполнения запросов к источникам данных (например, элементам инфоблоков Bitrix) и
предоставляет единый интерфейс для работы с фильтрацией, выборкой и сортировкой данных. Провайдеры предназначены, для
использования источника `Sholokhov\Exchange\Source\Entities\EntitySource`

## Добавлены способы чтения данных

[Data Reader](/2.3.x/reader/) - это базовый контракт для всех классов, которые читают данные из разных источников. Он определяет единый интерфейс, через который Source получает доступ к данным, не заботясь о том, откуда они приходят.

## Добавлены новые источники данных

### EntitySource

Класс: `Sholokhov\Exchange\Source\Entities\EntitySource`

Источник данных возвращает элементы произвольной [сущности](/2.3.x/source/entity) (пользователи, элементы инфоблока и
т.д.).   
Работа с сущностью производится через провайдер данных `Sholokhov\Exchange\Provider\Entity\EntityProviderInterface`

```php
$provider = new UserProvider;
$source = new EntitySource($provider);
```

### SerializeItem

Класс: `Sholokhov\Exchange\Source\Entities\SerializeItem`  

Источник данных занимается десериализацией строки в массив

```php
use Sholokhov\Exchange\Source\SerializeItem;
use Sholokhov\Exchange\Reader\LocalFileReader;

$reader = new LocalFileReader('/local/www/file.txt');;
new SerializeItem($reader);
```

## Удалены источники данных

`Sholokhov\Exchange\Source\JsonFile` - Вместо него необходимо использовать `Sholokhov\Exchange\Source\Json`  
`Sholokhov\Exchange\Source\Entites\IBlock\Element` - Вместо него необходимо использовать `\Sholokhov\Exchange\Source\Entities\EntitySource`

## Конфигурация источников данных

### Csv

Логика получения файла вынесена в отдельный [класс](/2.3.x/reader/), который по установленной логике читает файл. Теперь источник отвечает только за правило парсинга файла. Доступна возможность указания своего способа чтения файла.

#### Было
```php
use Sholokhov\Exchange\Source\Csv;

$source = new Csv('/local/www/file.csv');
```

#### Стало

```php
use Sholokhov\Exchange\Source\Csv;
use Sholokhov\Exchange\Reader\LocalFileReader;

$reader = new LocalFileReader('/local/www/file.csv');
$source = new Csv($reader);
```

### Json

Логика получения файла вынесена в отдельный [класс](/2.3.x/reader/), который по установленной логике читает файл. Теперь источник отвечает только за правило парсинга файла. Доступна возможность указания своего способа чтения файла.

#### Было
```php
use Sholokhov\Exchange\Source\Json;

$source = new Json('/local/www/file.json');
```

#### Стало

```php
use Sholokhov\Exchange\Source\Json;
use Sholokhov\Exchange\Reader\LocalFileReader;

$reader = new LocalFileReader('/local/www/file.json');
$source = new Json($reader);
```

### Xml и SimpleXml

Логика получения файла вынесена в отдельный [класс](/2.3.x/reader/), который по установленной логике читает файл. Теперь источник отвечает только за правило парсинга файла. Доступна возможность указания своего способа чтения файла.

#### Было
```php
use Sholokhov\Exchange\Source\Xml;

$source = new Xml('/local/www/file.xml');
```

#### Стало

```php
use Sholokhov\Exchange\Source\Xml;
use Sholokhov\Exchange\Reader\LocalFileReader;

$reader = new LocalFileReader('/local/www/file.xml');
$source = new Xml($reader);
```


## Сопутствующие обновления

- Устранены баг перебора данных в источниках
- Активное добавление unit-тестов
- Документация вынесена в модуль