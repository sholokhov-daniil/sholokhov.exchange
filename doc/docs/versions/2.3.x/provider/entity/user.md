# Провайдер пользователей Bitrix

Класс: `Sholokhov\Exchange\Provider\Entity\UserProvider`

## Введение

`UserProvider` — провайдер для работы с пользователями Bitrix.  
Позволяет последовательно получать пользователей батчами, используя **курсоровую пагинацию** по `ID` и лимит выборки через `nTopCount`.

Особенности:

- Использует курсорную пагинацию (`>ID`) — исключает дубли и пропуски.
- Не применяет OFFSET и постраничную навигацию.
- Подходит для больших объемов данных.
- Возвращает "сырые" данные пользователей Bitrix без дополнительных преобразований.
- Поддерживает выборку UF-полей через метод `setUserFields`.

> ⚠️ Внимание  
> Поля `PASSWORD` и `PASSWORD_HASH` автоматически исключаются из выборки для безопасности.

## Конфигурация

Провайдер использует **ProviderSelectionTrait**, который предоставляет методы для фильтрации, сортировки, выборки полей и лимита.

| Метод                 | Тип данных / Параметры | Значение по умолчанию | Описание                                              |
|-----------------------|----------------------|----------------------|-------------------------------------------------------|
| setFilter             | array $filter        | []                   | Устанавливает фильтр для выборки пользователей       |
| getFilter             | —                    | —                    | Возвращает текущий фильтр                             |
| setOrder              | array $order         | ['SORT'=>'ASC']      | Устанавливает порядок сортировки                     |
| setSelect             | array $select        | ['ID']               | Устанавливает поля выборки                            |
| setLimit              | int $limit           | 0                    | Устанавливает лимит количества элементов            |
| setUserFields         | array $userFields    | []                   | Устанавливает UF-поля для выборки                    |

## Пример использования

```php
use Sholokhov\Exchange\Source\Entities\EntitySource;
use Sholokhov\Exchange\Provider\Entity\UserProvider;

$provider = new UserProvider();
$provider->setFilter(['ACTIVE' => 'Y'])
         ->setOrder(['ID' => 'ASC'])
         ->setLimit(100)
         ->setUserFields(['UF_DEPARTMENT', 'UF_POSITION']);

$source = new EntitySource($provider);

foreach($source as $user) {
    echo $user['ID'] . ': ' . $user['NAME'] . "\n";
}
```

## Особенности реализации

- Метод `query()` использует `CUser::GetList` и возвращает объект `Bitrix\Main\DB\Result` через конвертер `DbResultConverter::fromOld`.
- Метод `buildSelect()` исключает поля `PASSWORD` и `PASSWORD_HASH` из выборки и гарантирует наличие поля `ID`.
- Поля UF добавляются через отдельный метод `setUserFields()`.