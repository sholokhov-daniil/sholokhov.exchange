# Создание первого обмена

После установки пакета через Composer вы готовы создавать обмены данных.  
Ранее мы ознакомились как производить конфигурацию обмена и настройке маршрутизации, на данном этапе разберем какой формат данных должен быть у импортируемых данных.  
Хранилище импортируемых данных должно быть [итерируемым](https://www.php.net/manual/ru/language.types.iterable.php).

Разберем примеры основных итерируемых значений

```php

$arr = [
    ['TITLE' => 'Name1'],
    ['TITLE' => 'Name2'],
    // ...
];

$iterator = new EmptyIterator;

$generator = function() {
    yield ['TITLE' => 'Name1'];
    yield ['TITLE' => 'Name2'];
};

$obj = new class implements Iterator {
    public current() {}
    public key() {}
    public next() {}
    public rewind() {}
    public valid() {}
}
```

Рассмотрим создание простого импорта
```php
use Sholokhov\Exchange\Fields\Field;
use Sholokhov\Exchange\Target\Import\IBlock\Element;
use Sholokhov\Exchange\Factory\Exchange\MapperFactory;

$options = [
    'iblock_id' => 13
];

$data = [
    [
        'id' => 56,
        'name' => 'Какой-то элемент',
    ],
    [
        'id' => 15,
        'name' => 'Какой-то элемент 2',
    ]
];

$map = [
    (new Field)
        ->setFrom('id')
        ->setTo('XML_ID')
        ->setPrimary(),
    (new Field)
        ->setFrom('name')
        ->setTo('NAME'),
];

$repository = MapperFactory::create();
$repository->setFields($map);

$exchange = new Element($options);
$exchange->setMappingRegistry($repository);

$result = $exchange->execute($data);
```