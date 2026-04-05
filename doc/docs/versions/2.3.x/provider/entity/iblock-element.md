# Провайдер разделов IBlock

Класс: **Sholokhov\Exchange\Provider\Entity\IBlockSectionProvider**

## Введение

`IBlockSectionProvider` — провайдер для работы с разделами информационных блоков (IBlock) Bitrix.  
Обеспечивает удобный интерфейс для конфигурации запроса через фильтр, сортировку, выборку полей и лимит элементов.

Особенности:

- Использует стандартный метод Bitrix `CIBlockSection::GetList` для выборки разделов.
- Поддерживает настройку фильтрации, сортировки, полей выборки и лимита.
- Позволяет лениво получать разделы без необходимости загружать все данные в память.
- Проверяет наличие модуля `iblock` при инициализации, выбрасывает исключение `LoaderException` если модуль отсутствует.

> ⚠️ Внимание  
> Для работы необходимо наличие установленного штатного модуля iblock.

## Конфигурация

Провайдер использует **ProviderSelectionTrait**, который предоставляет методы для фильтрации, сортировки, выборки полей
и лимита.

| Метод     | Тип данных / Параметры | Значение по умолчанию     | Описание                                           |
|-----------|------------------------|---------------------------|----------------------------------------------------|
| setFilter | array $filter          | []                        | Устанавливает фильтр для выборки разделов          |
| getFilter | —                      | —                         | Возвращает текущий фильтр                          |
| setOrder  | array $order           | ['SORT'=>'ASC']           | Устанавливает порядок сортировки                   |
| setSelect | array $select          | ['ID','NAME','IBLOCK_ID'] | Устанавливает поля выборки                         |
| setLimit  | int $limit             | 0                         | Устанавливает лимит количества выбираемых разделов |

## Пример использования

```php
use Sholokhov\Exchange\Source\Entities\EntitySource;
use Sholokhov\Exchange\Provider\Entity\IBlockSectionProvider;

$provider = new IBlockSectionProvider();
$provider->setFilter(['IBLOCK_ID' => 5])
         ->setOrder(['ID' => 'ASC'])
         ->setLimit(50)
         ->setSelect(['ID', 'NAME', 'IBLOCK_ID']);

$source = new EntitySource($provider);

foreach($source as $section) {
    echo $section['ID'] . ': ' . $section['NAME'] . "\n";
}
```

## Особенности реализации

- Метод `query()` возвращает объект `Bitrix\Main\DB\Result` через конвертер `DbResultConverter::fromOld`.
- Метод `getNav()` формирует массив навигации для Bitrix (`nPageSize`) с учетом установленного лимита.
- Поля выборки по умолчанию: `ID`, `NAME`, `IBLOCK_ID`.
- Поддерживает ленивую итерацию через `EntitySource`, что позволяет работать с большими объемами разделов.