# Описание тега в xml экспорте

Класс: **Sholokhov\Exchange\Fields\Export\XmlField**

Класс является наследником [стандартного свойства](/2.1.x/guide/map/) и поведение идентично ему.

Текущее свойство предназначено для описания xml тегов при экспорте
и используется [экспортом](/2.1.x/export/xml)

## Конфигурация

| Название       | Тип данных                                 | Описание                                      |
|----------------|--------------------------------------------|-----------------------------------------------|
| addAttribute   | Sholokhov\Exchange\Fields\FieldInterface   | Указание значения и названия атрибута         |
| setAttributes  | Sholokhov\Exchange\Fields\FieldInterface[] | Указание списка значений и названий атрибутов |
| getAttributes  | Sholokhov\Exchange\Fields\FieldInterface[] | Получение доступных атрибутов                 |
| setChildrenTag | string                                     | Название вложенного тега                      |При указании названия дочернего тега - это означает, что текущий тег является множественным, и хранит массив значений|
| getChildrenTag | string                                     | Получение названия дочернего тега             |

## Пример

### Указание атрибутов
```xml
<?xml version="1.0" encoding="UTF-8"?>
<root>
    <item>
        <picture name="picture_name">src</picture>
        <name>TEST</name>
    </item>
</root>
```
```php
use Sholokhov\Exchange\Fields\Export\XmlField;
use Sholokhov\Exchange\Fields\Field;

$field = new XmlField;
$field->setFrom('PICTURE');
$field->setTo('picture');
$field->addAttribute(
    (new Field)
        ->setFrom('KEY1.KEY2.VALUE')
        ->setTo('name')
);
```

### Указание дочернего тега
```xml
<?xml version="1.0" encoding="UTF-8"?>
<root>
    <item>
        <pictures>
            <img>...</img>
            <img>...</img>
            <img>...</img>
        </pictures>
        <name>TEST</name>
    </item>
</root>
```

```php
use Sholokhov\Exchange\Fields\Export\XmlField;
use Sholokhov\Exchange\Fields\Field;

$field = new XmlField;
$field->setFrom('PICTURES');
$field->setTo('pictures');
$field->setChildrenTag('img')
```