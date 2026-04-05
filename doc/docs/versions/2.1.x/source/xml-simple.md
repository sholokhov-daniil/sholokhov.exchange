# Быстрый XML

Класс: **Sholokhov\Exchange\Source\SimpleXml**


## Введение

Источник данных предназначен, для преобразования из XML в массив и передаче обмену.  
Текущий источник все данные хранит в памяти и подходит для не больших данных, в ином случае необходимо использовать [источник](/2.1.x/source/xml-db)  
Источник данных использует сериализатор [symfony serializer](https://github.com/symfony/serializer-pack/releases/tag/v1.3.0)

## Конфигурация

При инициализации объекта в конструктор мы обязаны указать путь до xml файла.
Разберем пример инициализации источника данных

```php
use Sholokhov\Exchange\Source\SimpleXml;

$source = new SimpleXml('https:\\my-web.com');

foreach($source as $value) {
    echo "<pre>";
    var_dump($value);
    echo "</pre>";
}
```

Может потребоваться получить данные из определенной точки xml, для этого необходимо указать путь через **setRootTag**  
Рассмотрим пример получения значения из атрибута item

**Формат данных**

```xml
<?xml version="1.0"?>
<data>
    <items>
        <item id="15">
            <name>Тут название</name>
            <color id="33">Черный</color>
        </item>
        <item>
            <name>Тут название2</name>
            <color id="12">Белый</color>
        </item>
    </items>
</data>
```

**Создание источника**
```php
use Sholokhov\Exchange\Source\SimpleXml;

$source = new SimpleXml('https:\\my-web.com');
$source->setRootTag('items.item');

foreach($source as $value) {
    echo "<pre>";
    var_dump($value);
    echo "</pre>";
}
```

**Результат**
```php
array(3) {
  ["@id"]=>
  int(15)
  ["name"]=>
  string(23) "Тут название"
  ["color"]=>
  array(2) {
    ["@id"]=>
    int(33)
    ["#"]=>
    string(12) "Черный"
  }
}
array(2) {
  ["name"]=>
  string(24) "Тут название2"
  ["color"]=>
  array(2) {
    ["@id"]=>
    int(12)
    ["#"]=>
    string(10) "Белый"
  }
}
```