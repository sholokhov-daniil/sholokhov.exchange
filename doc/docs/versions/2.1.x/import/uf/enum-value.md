# Импорт значения списка пользовательского свойства (UF)

Класс: **Sholokhov\Exchange\Target\UserFields\Enumeration**

## Введение

Импорт значений списка позволяет создавать и изменять значения списка пользовательского свойства (UF).

> ⚠️ Внимание
> 
> Импорт не поддерживает массив значений.

```php
// ✅ Верно
$source = [
    [
        'value' => '....',
    ],
    [
        'value' => '....'
    ]
];

// ❌ Неверно
$source = [
    [
        'value' => [
            '...',
            '...',
        ]
    ],
    [
        'value' => [
            '...',
            '...',
        ]
    ]
];
```

## Конфигурация

Импорт поддерживает следующий формат конфигурации (иные ключи пропускаются и не используются):

| Название      | Обязательное | Тип данных | Значение по умолчанию | Описание                                |
|---------------|--------------|------------|-----------------------|-----------------------------------------|
| entity_id     | Да           | string     | Нет                   | Сущность, в которую производится импорт |
| property_code | Да           | string     | Нет                   | Свойство, в которое производится импорт |

## Пример

Разберем пример запуска импорта значения списка для свойства, которое принадлежит справочнику (highload).

```php
use Sholokhov\Exchange\Fields\Field;
use Sholokhov\Exchange\Target\UserFields\Enumeration;
use Sholokhov\Exchange\Factory\Exchange\MapperFactory;

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
        ->setTo('XML_ID')
        ->setPrimary(),
    (new Field)
        ->setFrom('title'),
        ->setTo('VALUE'),
];

$repository = MapperFactory::create();
$repository->setFields($map);

$options = [
    'entity_id' => 'HLBLOCK_1',
    'property_code' => 'UF_COLOR'
];

$exchange = new Enumeration($options);
$exchange->setMappingRegistry($repository);
$exchange->execute($data);
```

## События

Импорт вызывает события, которые помогают модифицировать и контролировать процесс выполнения.

Все события реализованы на [d7](https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=43&LESSON_ID=3113).

### onBeforeUFEnumerationUpdate

Вызывается перед обновлением значения списка.

**Параметры обработчика:**

| Название | Обязательное | Тип данных | Передается по ссылке | Описание                             |
|----------|--------------|------------|----------------------|--------------------------------------|
| fields   | Да           | array      | Да                   | Подготовленные данные для обновления |
| id       | Да           | int        | Нет                  | Идентификатор обновляемого значения  |

> ⚠️ Внимание
> 
> Для отмены обновления вызовите исключение Sholokhov\Exchange\Exception\Target\ExchangeItemStoppedException. 
> 
> После отмены создаётся запись в лог с текстом исключения.

> ⚠️ Внимание
> 
> Если событие вернёт статус отличный от успешного, обновление не произойдет.  
> Можно передать массив ошибок.

#### Пример модификации данных

```php
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\EventManager;

EventManager::getInstance()->addEventHandler(
    'sholokhov.exchange',
    'onBeforeUFEnumerationUpdate',
    function(Event $event) {
        $parameters = &$event->getParameters();
        $parameters['fields']['VALUE'] = 'Теперь такое название';
        
        return new EventResult(EventResult::SUCCESS, $parameters);
    }
);
```

#### Пример отмены обновления

```php
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\EventManager;

use Sholokhov\Exchange\Target\UserFields\Enumeration;
use Sholokhov\Exchange\Exception\Target\ExchangeItemStoppedException;

/**
 *  Отмена изменения элемента нужного свойства нужной сущности 
 *  и записью в лог стандартного(системного) сообщения
**/
EventManager::getInstance()->addEventHandler(
    'sholokhov.exchange',
    'onBeforeUFEnumerationUpdate',
    function(Event $event) {
        /** @var $exchange Enumeration **/
        $exchange = $event->getParameter('exchange');
        $parameters = $event->getParameters();
        
        if (
            $exchange->getEntityId() === 'HLBLOCK_1'
            && $exchange->getPropertyCode() === 'UF_COLOR'
            && $event->getParameters()['fields']['XML_ID'] === 'black'
        ) {
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
    'onBeforeUFEnumerationUpdate',
    fn() => new EventResult(EventResult::ERROR, ['errors' => ['Ошибка 1', 'Ошибка 2']])
);
```

### onAfterUFEnumerationUpdate

Вызывается после выполнения обновления.

**Параметры обработчика:**

| Название | Обязательное | Тип данных                                      | Передается по ссылке | Описание                             |
|----------|--------------|-------------------------------------------------|----------------------|--------------------------------------|
| fields   | Да           | array                                           | Нет                  | Подготовленные данные для обновления |
| id       | Да           | int                                             | Нет                  | Идентификатор обновленного значения  |
| result   | Да           | Sholokhov\Exchange\Messages\DataResultInterface | Нет                  | Результат обновления                 |

> Событие служит флагом для разработчика.

#### Пример подписки на событие

```php
EventManager::getInstance()->addEventHandler(
    'sholokhov.exchange',
    'onAfterUFEnumerationUpdate',
    fn() => CEvent::Send(....)
);
```

### onBeforeUFEnumerationAdd

Вызывается перед созданием значения списка.

**Параметры обработчика:**

| Название | Обязательное | Тип данных | Передается по ссылке | Описание                             |
|----------|--------------|------------|----------------------|--------------------------------------|
| fields   | Да           | array      | Да                   | Подготовленные данные для добавления |

> ⚠️ Внимание
> 
> Для отмены добавления вызовите исключение Sholokhov\Exchange\Exception\Target\ExchangeItemStoppedException.
> 
> После отмены создаётся запись в лог с текстом исключения.

> ⚠️ Внимание
> 
> Если событие вернёт статус отличный от успешного, добавление не произойдет.   
> Можно передать массив ошибок.

#### Пример модификации данных

```php
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\EventManager;

EventManager::getInstance()->addEventHandler(
    'sholokhov.exchange',
    'onBeforeUFEnumerationAdd',
    function(Event $event) {
        $parameters = &$event->getParameters();
        $parameters['fields']['VALUE'] = 'Теперь такое название';
        
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
    'onBeforeUFEnumerationAdd',
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
    'onBeforeUFEnumerationAdd',
    fn() => new EventResult(EventResult::ERROR, ['errors' => ['Ошибка 1', 'Ошибка 2']])
);
```

### onAfterUFEnumerationAdd

Вызывается после выполнения добавления значения.

**Параметры обработчика:**

| Название | Обязательное | Тип данных                                      | Передается по ссылке | Описание                            |
|----------|--------------|-------------------------------------------------|----------------------|-------------------------------------|
| fields   | Да           | array                                           | Да                   | Подготовленные данные добавления    |
| id       | Да           | int                                             | Нет                  | Идентификатор добавленного значения |
| result   | Да           | Sholokhov\Exchange\Messages\DataResultInterface | Нет                  | Результат добавления                |

Событие служит флагом для разработчика.

#### Пример подписки на событие

```php
EventManager::getInstance()->addEventHandler(
    'sholokhov.exchange',
    'onAfterUFEnumerationAdd',
    fn() => CEvent::Send(....)
);
```