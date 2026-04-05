# Экспорт данных в xml формате

Класс: **Sholokhov\Exchange\Target\Export\Xml**

## Введение

Экспорт предоставляет возможность работать с любым итерируемым значением. 
Формирование xml файла производится через поток и хранит
минимальную информацию в оперативной памяти, что дает возможность обрабатывать данные любого размера.

Поддерживаемый формат данных

```php
// ✅ Верно
$source = [
    [/* ... */],
    [/* ... */],
    [/* ... */],
];

// ❌ Неверно
$source = [
    'value1',
    'value2',
];
```

Элемент массива может иметь любую вложенность и структуру.

Для описания атрибутов тега необходимо использовать специальное свойство [XmlField](/2.1.x/guide/map/export-xml)

## Конфигурация

Экспорт поддерживает следующий формат конфигурации (иные ключи пропускаются и не используются):

| Название        | Обязательное | Тип данных                                 | Значение по умолчанию           | Описание                                                                                          |
|-----------------|--------------|--------------------------------------------|---------------------------------|---------------------------------------------------------------------------------------------------|
| encoding        | Нет          | string                                     | Кодировка из кластера или utf-8 | Кодировка данных в источнике. Если кодировка не utf-8, то производится автоматическая конвертация |
| version         | Нет          | string                                     | 1.0                             | Версия xml файла                                                                                  |
| root            | Нет          | string                                     | root                            | Название корневого тега, который хранит список импортируемых элементов                            |
| element_tag     | Нет          | string                                     | item                            | Тег хранения элемента сущности                                                                    |
| save_path       | Нет          | string                                     | /upload/tmp/export.xml          | Путь хранения экспорта                                                                            |
| item_attributes | Нет          | Sholokhov\Exchange\Fields\FieldInterface[] |                                 | Карта атрибутов элемента сущности. Логика работы аналогична карте импорта\экспорта                |


## Пример

### Инициализация
```php
use Sholokhov\Exchange\Target;
use Sholokhov\Exchange\Fields\Field;
use Sholokhov\Exchange\Fields\Export\XmlField;
use Sholokhov\Exchange\Source\Entities\IBlock\Element;
use Sholokhov\Exchange\Factory\Exchange\MapperFactory;
use Sholokhov\Exchange\Normalizers\File\FileIdToPathNormalizer;

$source = new Element([
    'FILTER' => [
        'IBLOCK_ID' => 4
    ],
    'PROPERTIES' => [
        'VIN',
        'HASH',
        'HTML',
        'IMAGES'
    ]
]);

$map = [
    (new XmlField)
        ->setFrom('ID')
        ->setTo('id')
        ->addAttribute(
            (new Field)
                ->setFrom('PROPERTIES.HASH.VALUE')
                ->setTo('hash')
        )
        ->setPrimary(),
    (new Field)
        ->setFrom('NAME')
        ->setTo('name'),
    (new Field)
        ->setFrom('TIMESTAMP_X')
        ->setTo('date_update'),
    (new Field)
        ->setFrom('PROPERTIES.VIN.VALUE')
        ->setTo('vin'),

    (new XmlField)
        ->setFrom('PROPERTIES.IMAGES.VALUE')
        ->setTo('images')
        ->setChildrenTag('image')
        ->setPreparation((new FileIdToPathNormalizer('s1'))->normalize(...))
];

$mapRepository = MapperFactory::create();
$mapRepository->setFields($map);

$exchange = new Target\Export\Xml([
    'encoding' => 'windows-1251',
    'item_attributes' => [
        (new Field)
            ->setFrom('PROPERTIES.HASH.VALUE')
            ->setTo('hash')
    ]
]);
$exchange->setMappingRegistry($mapRepository);

$result = $exchange->execute($source);
```

### Результат
```xml
<?xml version="1.0" encoding="UTF-8"?>
<root>
  <item hash="c0b6ae77-7c2f-59af-9f7e-619313b78667">
    <id hash="c0b6ae77-7c2f-59af-9f7e-619313b78667">342</id>
    <name>Автомобиль 1</name>
    <date_update>31.08.2025 16:10:07</date_update>
    <vin>df3222456t56222</vin>
    <images/>
  </item>
  <item hash="c0b6ae77-7c2f-59af-9f7e-619313b78667">
    <id hash="c0b6ae77-7c2f-59af-9f7e-619313b78667">343</id>
    <name>Автомобиль 2</name>
    <date_update>31.08.2025 16:10:07</date_update>
    <vin>ssdffgghhjj</vin>
    <images/>
  </item>
  <item hash="">
    <id hash="">345</id>
    <name>341</name>
    <date_update>14.10.2025 18:10:08</date_update>
    <vin/>
    <images>
      <image>https://web.ru/upload/iblock/6e6/6v7dsfejx1h67met6rgn9yaesl2fjxo0.gif</image>
      <image>https://web.ru/upload/iblock/21e/15r8j031qkyf0lnpk50frbl662taeoh3.gif</image>
      <image>https://web.ru/upload/iblock/341/m7odtvf9pa8fu49zh33rgwuw46dzxj7e.png</image>
    </images>
  </item>
</root>
```