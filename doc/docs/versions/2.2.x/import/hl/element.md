# Импорт элементов справочника

Класс: **Sholokhov\Exchange\Target\Import\Highloadblock\Element**

## Конфигурация

Конфигурация импорта производится через DTO **Sholokhov\Exchange\Target\Options\Import\HlElementOption**.
Описание параметров

> Поддерживаются форматы хеширования для идентификации записей

| Название | Обязательное | Тип данных | Значение по умолчанию | Описание                                                        |
|----------|--------------|------------|-----------------------|-----------------------------------------------------------------|
| entityId | Да           | int        | Нет                   | Идентификатор сущности, в которую производится импорт элементов |
| hash     | Нет          | string     |                       | Идентификатор обмена                                            |

> ⚠️ Внимание
> 
> Деактивация элементов не поддерживается.

## Пример

Разберем пример запуска импорта элементов справочника.

```php
use Sholokhov\Exchange\Fields\Field;
use Sholokhov\Exchange\Target\Import\Highloadblock\Element;
use Sholokhov\Exchange\Factory\Exchange\MapperFactory;
use Sholokhov\Exchange\Target\Options\Import\HlElementOption;

$data = [
    [
        'code' => 'black',
        'title' => 'Черный'
    ],
    [
        'code' => 'white',
        'title' => 'Белый'
    ]
];

$map = [
    (new Field)
        ->setFrom('code')
        ->setTo('UF_XML_ID')
        ->setPrimary(),
    (new Field)
        ->setFrom('title'),
        ->setTo('UF_NAME'),
];

$repository = MapperFactory::create();
$repository->setFields($map);

$hlId = 1;
$options = new HlElementOption($hlId);
$options->hash = 'import_hash'; 

$exchange = new Element($options);
$exchange->setMappingRegistry($repository);
$exchange->execute($data);
```

## Преобразователи

Импорт элементов справочника поддерживает свойства следующих типов:

- Дата
- Дата и время
- Список
- Файл
- Логическое значение
- Привязка к элементу информационного блока
- Привязка к разделу информационного блока

## События

Импорт в ходе работы вызывает события, которые помогают модифицировать и контролировать процесс импорта элементов.

Все события реализованы на [d7](https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=43&LESSON_ID=3113). Рассмотрим доступные события.

### onBeforeHighloadblockElementUpdate

Вызывается перед обновлением элемента.

**Параметры обработчика:**

| Название | Обязательное | Тип данных | Передается по ссылке | Описание                                       |
|----------|--------------|------------|----------------------|------------------------------------------------|
| fields   | Да           | array      | Да                   | Подготовленные данные для обновления           |
| id       | Да           | int        | Нет                  | Идентификатор элемента, который будет обновлен |
| exchange | Да           | int        | Нет                  | Текущий объект обмена                          |

> ⚠️ Внимание
> 
> Для отмены обновления вызовите исключение Sholokhov\Exchange\Exception\Target\ExchangeItemStoppedException.
> 
> После отмены создается запись в лог с сообщением исключения.

> ⚠️ Внимание
> 
> Если событие вернет статус отличный от успешного, обновление не произойдет.   
> Можно передать массив ошибок в результат импорта.

#### Пример модификации данных

```php
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\EventManager;

EventManager::getInstance()->addEventHandler(
    'sholokhov.exchange',
    'onBeforeHighloadblockElementUpdate',
    function(Event $event) {
        $parameters = &$event->getParameters();
        $parameters['fields']['FIELDS']['NAME'] = 'Теперь такое название';
        $parameters['fields']['PROPERTIES']['VIN'] = '.....';
        
        return new EventResult(EventResult::SUCCESS, $parameters);
    }
);
```

#### Пример отмены обновления

```php
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\EventManager;

use Sholokhov\Exchange\Target\Import\UserFields\Enumeration;
use Sholokhov\Exchange\Exception\Target\ExchangeItemStoppedException;

/**
 *  Отмена изменения элемента нужного свойства нужной сущности 
 *  и записью в лог стандартного(системного) сообщения
**/
EventManager::getInstance()->addEventHandler(
    'sholokhov.exchange',
    'onBeforeHighloadblockElementUpdate',
    function(Event $event) {
        /** @var $exchange Enumeration **/
        $exchange = $event->getParameter('exchange');
        $parameters = $event->getParameters();
        
        if ($exchange->getHlID() === 1) {
            throw new ExchangeItemStoppedException('Так захотелось');
        }
        
        return new EventResult(EventResult::SUCCESS, $parameters);
    }
);
```

#### Пример передачи ошибки при выполнении события

```php
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\EventManager;

EventManager::getInstance()->addEventHandler(
    'sholokhov.exchange',
    'onBeforeHighloadblockElementUpdate',
    fn() => new EventResult(EventResult::ERROR, ['errors' => ['Ошибка 1', 'Ошибка 2']])
);
```

### onAfterHighloadblockElementUpdate

Вызывается после выполнения обновления элемента.

**Параметры обработчика:**

| Название | Обязательное | Тип данных                                      | Передается по ссылке | Описание                             |
|----------|--------------|-------------------------------------------------|----------------------|--------------------------------------|
| fields   | Да           | array                                           | Нет                  | Подготовленные данные для обновления |
| id       | Да           | int                                             | Нет                  | Идентификатор обновленного элемента  |
| result   | Да           | Sholokhov\Exchange\Messages\DataResultInterface | Нет                  | Результат обновления                 |

>Событие служит флагом для разработчика

#### Пример подписки на событие

```php
EventManager::getInstance()->addEventHandler(
    'sholokhov.exchange',
    'onAfterHighloadblockElementUpdate',
    fn() => CEvent::Send(....)
);
```

### onBeforeHighloadblockElementAdd

Вызывается перед созданием элемента.

**Параметры обработчика:**

| Название | Обязательное | Тип данных | Передается по ссылке | Описание                             |
|----------|--------------|------------|----------------------|--------------------------------------|
| fields   | Да           | array      | Да                   | Подготовленные данные для добавления |
| exchange | Да           | Element    | Нет                  | Текущий объект обмена                |

> ⚠️ Внимание
> 
> Для отмены добавления вызовите исключение Sholokhov\Exchange\Exception\Target\ExchangeItemStoppedException.
> 
> После отмены создается запись в лог с сообщением исключения.

> ⚠️ Внимание
> 
> Если событие вернет статус отличный от успешного, добавление не произойдет.   
> Можно передать массив ошибок.

#### Пример модификации данных

```php
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\EventManager;

EventManager::getInstance()->addEventHandler(
    'sholokhov.exchange',
    'onBeforeHighloadblockElementAdd',
    function(Event $event) {
        $parameters = &$event->getParameters();
        $parameters['fields']['NAME'] = 'Теперь такое название';
        
        return new EventResult(EventResult::SUCCESS, $parameters);
    }
);
```

#### Пример отмены

```php
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\EventManager;

EventManager::getInstance()->addEventHandler(
    'sholokhov.exchange',
    'onBeforeHighloadblockElementAdd',
    fn() => throw new ExchangeItemStoppedException('Так захотелось')
);
```

#### Пример передачи ошибки при выполнении события

```php
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\EventManager;

EventManager::getInstance()->addEventHandler(
    'sholokhov.exchange',
    'onBeforeHighloadblockElementAdd',
    fn() => new EventResult(EventResult::ERROR, ['errors' => ['Ошибка 1', 'Ошибка 2']])
);
```

### onAfterHighloadblockElementAdd

Вызывается после выполнения добавления элемента.

**Параметры обработчика:**

| Название | Обязательное | Тип данных                                      | Передается по ссылке | Описание                            |
|----------|--------------|-------------------------------------------------|----------------------|-------------------------------------|
| fields   | Да           | array                                           | Да                   | Подготовленные данные добавления    |
| id       | Да           | int                                             | Нет                  | Идентификатор добавленного элемента |
| result   | Да           | Sholokhov\Exchange\Messages\DataResultInterface | Нет                  | Результат добавления                |

> Событие служит флагом для разработчика.

#### Пример подписки на событие

```php
use Bitrix\Main\Event;
use Bitrix\Main\EventManager;

EventManager::getInstance()->addEventHandler(
    'sholokhov.exchange',
    'onAfterHighloadblockElementAdd',
    fn() => CEvent::Send(....)
);
```