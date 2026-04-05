# Импорт значения списка

Класс: **Sholokhov\Exchange\Target\Import\IBlock\Property\PropertyEnumeration**

## Введение

Импорт значений списка позволяет создавать и изменять значения списка элемента информационного блока.

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

Конфигурация импорта производится через DTO **Sholokhov\Exchange\Target\Options\Import\IBlock\PropertyEnumerationOption**.
Описание параметров

| Название     | Обязательное | Тип данных | Значение по умолчанию | Описание                                                |
|--------------|--------------|------------|-----------------------|---------------------------------------------------------|
| iBlockId     | Да           | int        | Нет                   | ID информационного блока, которому принадлежит свойство |
| propertyCode | Да           | string     | Нет                   | Свойство, в которое производится импорт значения        |
| hash         | Нет          | string     |                       | Идентификатор обмена                                    |

## Пример

Разберем пример запуска импорта значения списка для свойства.

```php
use Sholokhov\Exchange\Fields\Field;
use Sholokhov\Exchange\Factory\Exchange\MapperFactory;
use Sholokhov\Exchange\Target\Import\IBlock\Property\PropertyEnumeration;
use Sholokhov\Exchange\Target\Options\Import\IBlock\PropertyEnumerationOption;

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

$iblockId = 1;
$propertyCode = 'COLOR';
$options = new PropertyEnumerationOption($iblockId, $propertyCode); 
$options->hash = 'import_hash';

$exchange = new PropertyEnumeration($options);
$exchange->setMappingRegistry($repository);
$exchange->execute($data);
```

## События

Импорт в ходе своей работы вызывает события, которые могут помочь произвести его модификацию и проконтролировать процесс
выполнения импорта значений.

Все события реализованы на [d7](https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=43&LESSON_ID=3113).
Рассмотрим список доступных событий.

### onBeforeIBlockPropertyEnumerationUpdate

Вызывается перед обновлением значения списка.

**Передаваемые параметры в обработчик:**

| Название | Обязательное | Тип данных | Передается по ссылке | Описание                                                             |
|----------|--------------|------------|----------------------|----------------------------------------------------------------------|
| fields   | Да           | array      | Да                   | Подготовленные данные, которые будут принимать участие в обновлении. |
| id       | Да           | int        | Нет                  | Идентификатор значения списка, который будет обновляться             |


> ⚠️ Внимание  
> 
> Присутствует возможность отменить обновление значения, для этого необходимо вызвать исключение Sholokhov\Exchange\Exception\Target\ExchangeItemStoppedException.
> 
> После отмены обновления производится запись в лог файл с возможностью переопределения сообщения.   
> В качестве сообщения берется текст исключения.

> ⚠️ Внимание  
> 
> Если событие вернет статус отличный от успешного, обновление не произойдет. Возможна передача массива ошибок, которые
> будут добавлены в результат работы импорта.


#### Пример модификации данных

```php
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\EventManager;

EventManager::getInstance()->addEventHandler(
    'sholokhov.exchange',
    'onBeforeIBlockPropertyEnumerationUpdate',
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

use Sholokhov\Exchange\Target\Import\UserFields\Enumeration;
use Sholokhov\Exchange\Exception\Target\ExchangeItemStoppedException;

/**
 *  Отмена изменения элемента нужного свойства нужной сущности 
 *  и записью в лог стандартного(системного) сообщения
**/
EventManager::getInstance()->addEventHandler(
    'sholokhov.exchange',
    'onBeforeIBlockPropertyEnumerationUpdate',
    function(Event $event) {
        /** @var $exchange Enumeration **/
        $exchange = $event->getParameter('exchange');
        $parameters = $event->getParameters();
        
        if (
            $exchange->getIBlockID() === 1'
            && $exchange->getPropertyCode() === 'COLOR'
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
    'onBeforeIBlockPropertyEnumerationUpdate',
    fn() => new EventResult(EventResult::ERROR, ['errors' => ['Ошибка 1', 'Ошибка 2']])
);
```

### onAfterIBlockPropertyEnumerationUpdate

Вызывается после выполнения обновления значения.

**Передаваемые параметры в обработчик:**

| Название | Обязательное | Тип данных                                      | Передается по ссылке | Описание                             |
|----------|--------------|-------------------------------------------------|----------------------|--------------------------------------|
| fields   | Да           | array                                           | Да                   | Подготовленные данные для обновления |
| id       | Да           | int                                             | Нет                  | Идентификатор обновленного значения  |
| result   | Да           | Sholokhov\Exchange\Messages\DataResultInterface | Нет                  | Результат обновления                 |

> Событие служит только флагом для разработчика.

#### Пример подписки на событие

```php
EventManager::getInstance()->addEventHandler(
    'sholokhov.exchange',
    'onAfterIBlockPropertyEnumerationUpdate',
    fn() => CEvent::Send(....)
);
```

### onBeforeIBlockPropertyEnumerationAdd

Вызывается перед созданием значения списка.

**Передаваемые параметры в обработчик:**

| Название | Обязательное | Тип данных | Передается по ссылке | Описание                                                      |
|----------|--------------|------------|----------------------|---------------------------------------------------------------|
| fields   | Да           | array      | Да                   | Подготовленные данные, которые будут участвовать в добавлении |

> ⚠️ Внимание
> 
> Присутствует возможность отменить добавление значения, для этого необходимо вызвать исключение Sholokhov\Exchange\Exception\Target\ExchangeItemStoppedException.  
> 
> После отмены записи создается лог с сообщением исключения.

> ⚠️ Внимание
> 
> Если событие вернет статус отличный от успешного, добавление не произойдет.   
> Возможна передача массива ошибок.

#### Пример модификации данных

```php
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\EventManager;

EventManager::getInstance()->addEventHandler(
    'sholokhov.exchange',
    'onBeforeIBlockPropertyEnumerationAdd',
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
    'onBeforeIBlockPropertyEnumerationAdd',
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
    'onBeforeIBlockPropertyEnumerationAdd',
    fn() => new EventResult(EventResult::ERROR, ['errors' => ['Ошибка 1', 'Ошибка 2']])
);
```

### onAfterIBlockPropertyEnumerationAdd

Вызывается после выполнения добавления значения.

**Передаваемые параметры в обработчик:**

| Название | Обязательное | Тип данных                                      | Передается по ссылке | Описание                            |
|----------|--------------|-------------------------------------------------|----------------------|-------------------------------------|
| fields   | Да           | array                                           | Да                   | Подготовленные данные добавления    |
| id       | Да           | int                                             | Нет                  | Идентификатор добавленного значения |
| result   | Да           | Sholokhov\Exchange\Messages\DataResultInterface | Нет                  | Результат добавления                |

> Событие служит только флагом для разработчика.

#### Пример подписки на событие

```php
EventManager::getInstance()->addEventHandler(
    'sholokhov.exchange',
    'onAfterIBlockPropertyEnumerationAdd',
    fn() => CEvent::Send(....)
);
```