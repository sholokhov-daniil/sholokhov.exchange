# Что изменилось в версии 2.2.x

> ⚠️ Нарушена обратная совестимость

Текущая версия содержит глобальные изменения в области конфигурации и изменения структуры библиотеки. 
Что негативно сказалось на обратной совместимости. При переходе на текущую версию необходимо уделить должное внимание проверке работоспособности обменов, чтобы выполненное действие не привело к потере работоспособности функционала.

## Изменено пространство имен импортов

### Было
```php
use Sholokhov\Exchange\Target\File;
```

### Стало
```php
use Sholokhov\Exchange\Target\Import\File;
```

Все импорты были перемещены в директорию **Import**

## Изменен формат данных конфигурации обмена

### Было
```php
$config = [
    'iblock_id' => 13,
    'hash' => 'test',
];

new Exchange($config);
```

### Стало
```php
use Sholokhov\Exchange\Target\Options\Import\IBlock\IBlockOption;

$option = new IBlockOption(13);
$option->hash = 'test';

new Exchange($config);
```

Теперь все конфигурации импорта и экспорта выполнены в виде отдельных классов(DTO).  
У каждого обмена используется свой собственный класс. 

Все доступные конфигурационные объекты размещаются в директории
```
/lib/Target/Options
```

## Удаление поддержки метода configuration в обмене

Базовый класс обмена **Sholokhov\Exchange\AbstractExchange** больше не имеет конструктора и вся логика инициализации ложится на плечи разработчика обмена

### Было
```php
use Sholokhov\Exchange\AbstractExchange;

class MyExchange extends AbstractExchange
{
    protected function configuration(): void 
    {
        parent::configuration();
        // logic...
    }
}
```

### Стало
```php
use Sholokhov\Exchange\AbstractExchange;

class MyExchange extends AbstractExchange
{
    public function __construct() {
        $this->configuration();
    }

    protected function configuration(): void 
    {
        // logic...
    }
}
```

## Закрытие параметров в базовом классе обмена
Все параметры базового класса **Sholokhov\Exchange\AbstractExchange** были закрыты, и теперь доступ производится через геттеры

### Было
```php
use Sholokhov\Exchange\AbstractExchange;

class MyExchange extends AbstractExchange
{
    protected function myMethod(): void 
    {
        $this->cache->set('key', 'value');
        foreach($this->validators as $validator) {
            // ...
        }
    }
}
```

### Стало
```php
use Sholokhov\Exchange\AbstractExchange;

class MyExchange extends AbstractExchange
{
    protected function myMethod(): void 
    {
        $this->getCache()->set('key', 'value');
        foreach($this->getValidators() as $validator) {
            // ...
        }
    }
}
```

## Удалено свойство хранения конфигурации обмена

### Было
```php
use Sholokhov\Exchange\AbstractExchange;

class MyExchange extends AbstractExchange
{
    protected function myMethod(): void 
    {
        $this->options->set('key', 'value');
    }
}
```

### Стало
```php
use Sholokhov\Exchange\AbstractExchange;
use Sholokhov\Exchange\Repository\Types\Memory;

class MyExchange extends AbstractExchange
{
    protected Memory $options;

    protected function myMethod(): void 
    {
        $this->options->set('key', 'value');
    }
}
```

Роль хранилища конфигурации выступают DTO объекты(**/lib/Target/Options**), которые приходят из вне.

## Изменена логика деактивации

Был закрыт доступ к методу **deactivate** - вся логика должна храниться в методе **doDeactivate**.  
Запуск деактивации уходит под управление каждому импорту. По умолчанию деактивация недоступна

### Было
```php
use Sholokhov\Exchange\AbstractImport;

class MyExchange extends AbstractImport
{
    protected function deactivate(): void 
    {
        // ...
    }
}
```

### Стало
```php
use Sholokhov\Exchange\AbstractImport;
use Sholokhov\Exchange\Target\Options\Import\BaseImportOption;

class MyExchange extends AbstractImport
{
    private BaseImportOption $option;

    protected function doDeactivate(): void 
    {
        // ...
    }
    
    protected function canDeactivate(): bool 
    {
        return $this->option->deactivate;
    }
}
```
