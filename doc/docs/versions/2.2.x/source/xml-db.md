# Медленный XML

Класс: **Sholokhov\Exchange\Source\Xml**

## Введение

Источник данных предназначен, для преобразования из XML в массив и передаче обмену.  
Текущий источник данных является более медленным по сравнению с [SimpleXml](/2.1.x/source/xml-simple)
но может работать с любым объемом данных.

Чтение XML файла идет по строкам, а результат чтения записывается таблицы, уже после этого идет взаимодействия с базой данных,
и не приходится держать в памяти весь файл.  

Для хранения данных создается уникальная таблица, а по окончанию работы удаляется,
если произошло экстренное прерывание выполнения php скрипта, то библиотека использует агентов, которые производят мониторинг динамических таблиц.  
Источник данных использует стандартный парсер битрикса [CIBlockXMLFile](https://dev.1c-bitrix.ru/api_help/iblock/classes/ciblockxmlfile/index.php)

> ⚠️ Внимание
> 
> Для работы необходимо наличие установленного штатного модуля iblock

## Конфигурация

При инициализации объекта в конструктор мы обязаны указать путь до xml файла.
Разберем пример инициализации источника данных  
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
use Sholokhov\Exchange\Source\Xml;

$source = new Xml('https:\\my-web.com');
$source->setRootTag('items');

foreach($source as $value) {
    echo "<pre>";
    var_dump($value);
    echo "</pre>";
}
```

**Результат**
```php
array(4) {
  ["item_attribute_id"]=>
  string(2) "15"
  ["item_name"]=>
  string(23) "Тут название"
  ["item_color"]=>
  string(12) "Черный"
  ["item_color_attribute_id"]=>
  string(2) "33"
}

array(3) {
  ["item_name"]=>
  string(24) "Тут название2"
  ["item_color"]=>
  string(10) "Белый"
  ["item_color_attribute_id"]=>
  string(2) "12"
}
```

Текущий источник данных имеет собственный синтаксис указания пути до размещения корня данных.
В большинстве случаев вложенный путь указывается через символ ".", но в данном случае указываем нужный тег и уровень вложенности.  
Рассмотрим пример получения данных из более глубокого размещения

**Формат данных**
```xml
<?xml version="1.0"?>
<data>
    <handbook>
        <catalog>
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
        </catalog>
    </handbook>
</data>
```

**Создание источника**
```php
use Sholokhov\Exchange\Source\Xml;

$source = new Xml('https:\\my-web.com');
$source->setRootTag('items');
$source->setRootTagDepth(3);

foreach($source as $value) {
    echo "<pre>";
    var_dump($value);
    echo "</pre>";
}
```

**Результат**
```php
array(4) {
  ["item_attribute_id"]=>
  string(2) "15"
  ["item_name"]=>
  string(23) "Тут название"
  ["item_color"]=>
  string(12) "Черный"
  ["item_color_attribute_id"]=>
  string(2) "33"
}
array(3) {
  ["item_name"]=>
  string(24) "Тут название2"
  ["item_color"]=>
  string(10) "Белый"
  ["item_color_attribute_id"]=>
  string(2) "12"
}
```

Можно было заметить, что мы использовали метод **setRootTagDepth**.
Данный метод предназначен, для указания коревого уровня вложенности, чтобы избежать ошибок определения.  
Парсер битрикса не учитывает вложенность и подразумевает, что каждый тег уникальный, и не может размещаться в разных местах XML файла.
Если мы не укажем уровень вложенности, то будет производиться глобальный поиск по всему XML файлу

```php
// ✅ Хорошо
$source->setRootTag('items');
$source->setRootTagDepth(3);
$exchange->execute($source);

// ❌ Плохо
$source->setRootTag('items');
$exchange->execute($source);
```