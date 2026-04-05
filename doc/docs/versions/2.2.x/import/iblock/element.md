# Импорт элементов инфоблока

Класс: **Sholokhov\Exchange\Target\Import\IBlock\Element**

## Конфигурация
Конфигурация импорта производится через DTO **Sholokhov\Exchange\Target\Options\Import\IBlock\IBlockOption**.
Описание параметров

> ⚠️ Внимание
> 
> Поддерживает разграничение данных на основе идентификатора обмена

| Название | Обязательное | Тип данных | Значение по умолчанию | Описание                                                                    |
|----------|--------------|------------|-----------------------|-----------------------------------------------------------------------------|
| iBlockId | Да           | int        | Нет                   | Идентификатор информационного блока в который производится импорт элементов |
| hash     | Нет          | string     |                       | Идентификатор обмена                                                        |

## Пример
Разберем пример запуска импорта элементов информационного блока

```php
use Sholokhov\Exchange\Fields\Field;
use Sholokhov\Exchange\Target\Import\IBlock\Element;
use Sholokhov\Exchange\Factory\Exchange\MapperFactory;
use Sholokhov\Exchange\Target\Options\Import\IBlock\IBlockOption;

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
        ->setTo('NAME'),
];

$iBlockId = 3;
$options = new IBlockOption($iBlockId);
$options->hash = 'test_import';
$options->deactivate = true;

$repository = MapperFactory::create();
$repository->setFields($map);

$exchange = new Element($options);
$exchange->setMappingRegistry($repository);
$exchange->execute($data);
```

## Преобразователи
Импорт элементов информационного блока поддерживает свойства типов:

- Дата
- Дата и время
- Число
- Список
- Файл
- Привязка к элементу информационного блока
- Привязка к разделу информационного блока
- HTML\Text
- Привязка к элементу справочника(HL)

## События

Импорт в ходе своей работы вызывает события, которые могут помочь произвести его модификацию и проконтролировать процесс выполнения импорта элементов.  
Все события реализованы на [d7](https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=43&LESSON_ID=3113)

Рассмотрим список доступных событий

### onBeforeIBlockElementUpdate
Вызывается перед обновлением элемента.  
Передаваемые параметры в обработчик

| Название | Обязательное | Тип данных | Передается по ссылке | Описание                                         |
|----------|--------------|------------|----------------------|--------------------------------------------------|
| fields   | Да           | int        | Нет                  | Идентификатор элемента, который было обновляться |

> ⚠️ Внимание
> 
> Присутствует возможность отменить создание элемента, для этого необходимо вызвать исключение **Sholokhov\Exchange\Exception\Target\ExchangeItemStoppedException**
> 
> После отмены создания производится запись в лог файл с возможностью переопределения сообщения. В качестве сообщения, для записи в лог берется сообщение исключения.

> ⚠️ Внимание
> 
> Если событие вернет статут отличный от успешного, то будет считаться, что в пользовательском событии возникла ошибка, и добавление не произойдет.  
> Присутствует возможность передачи массив ошибок, которые будут перенесены в результат работы импорта.

#### Пример модификации данных
```php
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\EventManager;

EventManager::getInstance()->addEventHandler(
    'sholokhov.exchange',
    'onBeforeIBlockElementUpdate',
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
    'onBeforeIBlockElementUpdate',
    function(Event $event) {
        /** @var $exchange Enumeration **/
        $exchange = $event->getParameter('exchange');
        $parameters = $event->getParameters();
        
        if ($exchange->getIBlockID() === 3) {
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
    'onBeforeIBlockElementUpdate',
    fn() => new EventResult(EventResult::ERROR, ['errors' => ['Ошибка 1', 'Ошибка 2']])
);
```

### onAfterIBlockElementUpdate
Вызывается после выполнения обновления элемента.  

Передаваемые параметры в обработчик

| Название | Обязательное | Тип данных                                      | Передается по ссылке | Описание                                                             |
|----------|--------------|-------------------------------------------------|----------------------|----------------------------------------------------------------------|
| fields   | Да           | array                                           | Да                   | Подготовленные данные, которые будут принимать участие в обновлении. |
| id       | Да           | int                                             | Нет                  | Идентификатор элемента, который будет обновляться                    |
| result   | Да           | Sholokhov\Exchange\Messages\DataResultInterface | Нет                  | Результат обновления элемента                                        |

> Событие не позволяет вмешаться в процесс обмена, а только служит флагом, дря разработчика

#### Пример подписки на событие
```php
EventManager::getInstance()->addEventHandler(
    'sholokhov.exchange',
    'onAfterIBlockElementUpdate',
    fn() => CEvent::Send(....)
);
```

### onBeforeIBlockElementAdd

Вызывается перед созданием элемента.  

Передаваемые параметры в обработчик

| Название | Обязательное | Тип данных | Передается по ссылке | Описание                                                            |
|----------|--------------|------------|----------------------|---------------------------------------------------------------------|
| fields   | Да           | array      | Да                   | Подготовленные данные, которые будут принимать участие в добавлении |

> ⚠️ Внимание
> 
> Присутствует возможность отменить добавления элемента, для этого необходимо вызвать исключение Sholokhov\Exchange\Exception\Target\ExchangeItemStoppedException
> 
> После отмены добавления производится запись в лог файл с возможностью переопределения сообщения. В качестве сообщения, для записи в лог берется сообщение исключения.

> ⚠️ Внимание
> 
> Если событие вернет статут отличный от успешного, то будет считаться, что в пользовательском событии возникла ошибка, и добавление не произойдет.  
> Присутствует возможность передачи массив ошибок, которые будут перенесены в результат работы импорта.

#### Пример модификации данных
```php
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\EventManager;

EventManager::getInstance()->addEventHandler(
    'sholokhov.exchange',
    'onBeforeIBlockElementAdd',
    function(Event $event) {
        $parameters = &$event->getParameters();
        $parameters['fields']['NAME'] = 'Теперь такое название';
        
        return new EventResult(EventResult::SUCCESS, $parameters);
    }
);
````

#### Пример отмены
```php
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\EventManager;

EventManager::getInstance()->addEventHandler(
    'sholokhov.exchange',
    'onBeforeIBlockElementAdd',
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
    'onBeforeIBlockElementAdd',
    fn() => new EventResult(EventResult::ERROR, ['errors' => ['Ошибка 1', 'Ошибка 2']])
);
```

### onAfterIBlockElementAdd
Вызывается после выполнения добавления элемента.

Передаваемые параметры в обработчик

| Название | Обязательное | Тип данных                                      | Передается по ссылке | Описание                                                            |
|----------|--------------|-------------------------------------------------|----------------------|---------------------------------------------------------------------|
| fields   | Да           | array                                           | Да                   | Подготовленные данные, которые будут принимать участие в добавлении |
| id       | Да           | int                                             | Нет                  | Идентификатор элемента, который было добавлено                      |
| result   | Да           | Sholokhov\Exchange\Messages\DataResultInterface | Нет                  | Результат добавления элемента                                       |

> Событие не позволяет вмешаться в процесс обмена, а только служит флагом, дря разработчика

#### Пример подписки на событие
```php
use Bitrix\Main\Event;
use Bitrix\Main\EventManager;

EventManager::getInstance()->addEventHandler(
    'sholokhov.exchange',
    'onAfterIBlockElementAdd',
    fn() => CEvent::Send(....)
);
```

### onBeforeIBlockElementsDeactivate

Вызывается перед деактивацией элементов, которые не пришли в импорте

Передаваемые параметры в обработчик

| Название   | Обязательное | Тип данных | Передается по ссылке | Описание                                                                                                        |
|------------|--------------|------------|----------------------|-----------------------------------------------------------------------------------------------------------------|
| parameters | Да           | array      | Да                   | Параметры запроса [ElementTable::getList](https://dev.1c-bitrix.ru/api_d7/bitrix/iblock/elementtable/index.php) |

> Событие не позволяет вмешаться в процесс деактивации, а только служит флагом, дря разработчика

#### Пример подписки на событие
```php
use Bitrix\Main\Event;
use Bitrix\Main\EventManager;

EventManager::getInstance()->addEventHandler(
    'sholokhov.exchange',
    'onBeforeIBlockElementsDeactivate',
    function(Event $event) {
        ...
    }
);
```