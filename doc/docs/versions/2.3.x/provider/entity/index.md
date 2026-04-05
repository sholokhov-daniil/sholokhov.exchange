# Провайдер сущностей

Интерфейс: **Sholokhov\Exchange\Provider\Entity\EntityProviderInterface**

## Введение

Провайдер сущностей предназначен для выполнения запросов к источникам данных (например, элементам инфоблоков Bitrix) и
предоставляет единый интерфейс для работы с фильтрацией, выборкой и сортировкой данных.

EntitySource использует провайдер для батчевой подгрузки данных, поэтому провайдер должен поддерживать:

- Построение фильтров (`setFilter`, `getFilter`)
- Сортировку (`setOrder`)
- Выборку полей (`setSelect`)
- Ограничение количества записей (`setLimit`)
- Выполнение запроса (`query`)

> ⚠️ Внимание
>
> Провайдер должен возвращать
> объект [Bitrix\Main\DB\Result](https://dev.1c-bitrix.ru/api_help/main/reference/cdbresult/index.php) или `null`, если
> данных нет.

## Методы интерфейса

| Метод     | Параметры     | Возвращаемое значение | Описание                                   |
|-----------|---------------|-----------------------|--------------------------------------------|
| query     | —             | Result                | null                                       | Выполняет запрос к источнику данных и возвращает результат               |
| setFilter | array $filter | static                | Устанавливает фильтр для запроса           |
| getFilter | —             | array                 | Возвращает текущий фильтр                  |
| setOrder  | array $order  | static                | Устанавливает порядок сортировки элементов |
| setSelect | array $select | static                | Определяет набор полей для выборки         |
| setLimit  | int $limit    | static                | Устанавливает лимит количества записей     |

## Пример реализации провайдера

```php
use Sholokhov\Exchange\Provider\Entity\EntityProviderInterface;
use Bitrix\Main\DB\Result;

class MyEntityProvider implements EntityProviderInterface
{
    protected array $filter = [];
    protected array $order = [];
    protected array $select = [];
    protected int $limit = 0;

    public function query(): ?Result
    {
        // Выполнение запроса к БД, например через CIBlockElement::GetList
        return CIBlockElement::GetList($this->order, $this->filter, false, $this->limit ? ['nTopCount' => $this->limit] : false, $this->select);
    }

    public function setFilter(array $filter): static
    {
        $this->filter = $filter;
        return $this;
    }

    public function getFilter(): array
    {
        return $this->filter;
    }

    public function setOrder(array $order): static
    {
        $this->order = $order;
        return $this;
    }

    public function setSelect(array $select): static
    {
        $this->select = $select;
        return $this;
    }

    public function setLimit(int $limit): static
    {
        $this->limit = $limit;
        return $this;
    }
}
```