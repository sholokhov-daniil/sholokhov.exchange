# IBlockSectionProvider

**Namespace:** `Sholokhov\Exchange\Provider\Entity`  
**Класс:** `IBlockSectionProvider`  
**Реализует:** `EntityProviderInterface`  
**Использует:** `ProviderSelectionTrait`

---

## Назначение

Провайдер для работы с разделами информационных блоков (IBlock) в Bitrix.  
Позволяет удобно настраивать запрос к разделам через:

- фильтры,
- сортировку,
- выборку полей,
- лимит элементов.

Особенности:

- Использует `CIBlockSection::GetList` для получения данных.
- Поддерживает фильтры, сортировку, поля выборки и лимит.
- Позволяет лениво получать разделы через итераторы.
- Проверяет наличие модуля `iblock` при инициализации (выбрасывает `LoaderException`, если отсутствует).

---

## Конфигурация через ProviderSelectionTrait

| Метод       | Параметры     | Значение по умолчанию     | Описание                                  |
|-------------|---------------|---------------------------|-------------------------------------------|
| `setFilter` | array $filter | []                        | Устанавливает фильтр для выборки разделов |
| `getFilter` | —             | —                         | Возвращает текущий фильтр                 |
| `setOrder`  | array $order  | ['SORT' => 'ASC']         | Устанавливает порядок сортировки          |
| `setSelect` | array $select | ['ID','NAME','IBLOCK_ID'] | Устанавливает поля выборки                |
| `setLimit`  | int $limit    | 0                         | Лимит количества выбираемых разделов      |

> ⚠️ По умолчанию выбираются поля: `ID`, `NAME`, `IBLOCK_ID`.

---

## Методы класса

### `__construct()`

Инициализация провайдера. Проверяет наличие модуля `iblock` и устанавливает стандартный набор полей выборки.

```php
$provider = new IBlockSectionProvider();
```

Если модуль `iblock` не подключен — выбрасывает `LoaderException`.

`query(): ?Result`  
Выполняет запрос к разделам IBlock с текущими настройками (фильтр, сортировка, выборка полей, навигация).  
Возвращает объект `Bitrix\Main\DB\Result` через `DbResultConverter::fromOld` или `null`, если запрос не удался.

**Пример использования:**

```php
$provider = new IBlockSectionProvider();
$provider->setFilter(['IBLOCK_ID' => 10])
         ->setOrder(['ID' => 'ASC'])
         ->setLimit(20);

$result = $provider->query();
foreach ($result as $section) {
    echo $section['ID'] . ' - ' . $section['NAME'] . "\n";
}
```

## Особенности
- Подходит для ленивой итерации через `EntitySource`.
- Навигация учитывает только лимит (`nPageSize`), смещение (`nOffset`) по умолчанию не используется.
- Использует конвертер `DbResultConverter::fromOld` для совместимости с новым API `Bitrix\Main\DB\Result`.
- Исключает дублирование логики фильтров и сортировки, которые можно конфигурировать через сеттеры.