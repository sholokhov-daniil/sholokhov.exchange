<?php

namespace Sholokhov\Exchange\UI\EntitySelector;

use Bitrix\Main\Context;
use Bitrix\UI\EntitySelector\BaseProvider;
use Bitrix\UI\EntitySelector\Dialog;
use Bitrix\UI\EntitySelector\Item;
use Bitrix\UI\EntitySelector\SearchQuery;
use Bitrix\UI\EntitySelector\Tab;

final class UserFieldProvider extends BaseProvider
{
    /**
     * ID провайдера сущности
     *
     * @since 1.2.0
     * @version 1.2.0
     */
    public const ENTITY_ID = 'sholokhov-exchange-user-field';

    /**
     * Ограничение количества отображаемых элементов в диалоге
     *
     * @since 1.2.0
     * @version 1.2.0
     */
    protected const ITEM_LIMIT = 100;

    /**
     * @since 1.2.0
     * @version 1.2.0
     */
    public const QUERY_SEARCH = 'S';

    /**
     * @since 1.2.0
     * @version 1.2.0
     */
    public const QUERY_SUBSTRING = 'L';

    /**
     * @since 1.2.0
     * @version 1.2.0
     */
    public const QUERY_BEGIN = 'B';

    /**
     * @param array $options
     *
     * @since 1.2.0
     * @version 1.2.0
     */
    public function __construct(array $options = [])
    {
        parent::__construct();
        $this->options = $this->normalizeOptions($options);
    }

    /**
     * @return bool
     *
     * @since 1.2.0
     * @version 1.2.0
     */
    public function isAvailable(): bool
    {
        return mb_strlen($this->getEntityId());
    }

    /**
     * @param array $ids
     * @return array|Item[]
     * @since 1.2.0
     * @version 1.2.0
     */
    public function getItems(array $ids): array
    {
        $items = [];

        $iterator = $this->query();

        foreach ($iterator as $iBlock) {
            $items[] = $this->makeItem($iBlock);
        }

        return $items;
    }

    /**
     * @param Dialog $dialog
     * @return void
     * @since 1.2.0
     * @version 1.2.0
     */
    public function fillDialog(Dialog $dialog): void
    {
        $dialog->loadPreselectedItems();

        if ($dialog->getItemCollection()->count() > 0)
        {
            foreach ($dialog->getItemCollection() as $item)
            {
                $dialog->addRecentItem($item);
            }
        }

        $recentItems = $dialog->getRecentItems()->getEntityItems(self::ENTITY_ID);
        $recentItemsCount = count($recentItems);

        if ($recentItemsCount < self::ITEM_LIMIT)
        {
            $elementList = $this->query([
                'filter' => [],
                'limit' => self::ITEM_LIMIT,
            ]);
            foreach ($elementList as $element)
            {
                $dialog->addRecentItem($this->makeItem($element));
            }
        }

        $this->addTab($dialog);
    }

    /**
     * @param SearchQuery $searchQuery
     * @param Dialog $dialog
     * @return void
     *
     * @since 1.2.0
     * @version 1.2.0
     */
    public function doSearch(SearchQuery $searchQuery, Dialog $dialog): void
    {
        $filter = $this->getQueryFilter($searchQuery);
        if ($filter === null)
        {
            return;
        }

        $elementList = $this->query();

        foreach ($elementList as $element)
        {
            $dialog->addItem(
                $this->makeItem($element)
            );
        }
    }

    /**
     * @return array
     *
     * @since 1.2.0
     * @version 1.2.0
     */
    private function query(): array
    {
        global $USER_FIELD_MANAGER;

        $lang = Context::getCurrent()->getLanguage();
        $iterator = $USER_FIELD_MANAGER->GetUserFields($this->getEntityId(), 0, $lang);

        return array_filter($iterator, function($field)  {
            if (($type = $this->getOption('propertyType')) && $field['USER_TYPE_ID'] <> $type) {
                return false;
            }

            return true;
        });
    }

    /**
     * @param array $item
     * @return Item
     *
     * @since 1.2.0
     * @version 1.2.0
     */
    private function makeItem(array $item): Item
    {


        return new Item([
            'id' => $item['ID'],
            'fieldName' => $item['FIELD_NAME'],
            'title' => str_replace(
                array_map(fn($name) => "#$name#", array_keys($item)),
                array_values($item),
                $this->getNameTemplate()
            ),
            'entityId' => self::ENTITY_ID,
            'tabs' => $this->getTabs(),
        ]);
    }

    /**
     * @param SearchQuery $searchQuery
     * @return array|string[]|null
     *
     * @since 1.2.0
     * @version 1.2.0
     */
    private function getQueryFilter(SearchQuery $searchQuery): ?array
    {
        $query = $searchQuery->getQuery();
        if (mb_strlen($query) < 2)
        {
            return [];
        }

        return match ($this->getQueryMethod())
        {
            self::QUERY_SEARCH => [
                '*NAME' => $query,
            ],
            self::QUERY_SUBSTRING => [
                '%NAME' => $query,
            ],
            default => [
                'NAME' => $query.'%',
            ],
        };
    }

    /**
     * @param Dialog $dialog
     * @return void
     *
     * @since 1.2.0
     * @version 1.2.0
     */
    private function addTab(Dialog $dialog): void
    {
        $icon = $this->getTabIcon();
        if (!$icon) {
            return;
        }

        $tab = new Tab([
            'id' => self::ENTITY_ID,
            'title' => 'UF',
            'stub' => true,
            'icon' => [
                'default' => $icon,
                'selected' => str_replace('ABB1B8', 'FFF', $icon),
            ],
        ]);

        $dialog->addTab($tab);
    }

    /**
     * @return string[]
     *
     * @since 1.2.0
     * @version 1.2.0
     */
    private function getTabs()
    {
        return [self::ENTITY_ID];
    }

    /**
     * @return string
     *
     * @since 1.2.0
     * @version 1.2.0
     */
    private function getTabIcon(): string
    {
        return <<<HTML
    data:image/svg+xml;charset=utf-8,%3Csvg width='28' height='28' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath opacity='.5' fill-rule='evenodd' clip-rule='evenodd' d='M14 28c7.732 0 14-6.268 14-14S21.732 0 14 0 0 6.268 0 14s6.268 14 14 14z' fill='%23828B95'/%3E%3Cpath fill-rule='evenodd' clip-rule='evenodd' d='M15.06 8.296a.262.262 0 00-.448.186v3.39H8.646a.262.262 0 00-.263.262v3.732c0 .145.118.262.263.262h5.966v3.39c0 .234.283.351.448.186l5.518-5.518a.262.262 0 000-.372L15.06 8.296z' fill='%23fff'/%3E%3C/svg%3E"
HTML;
    }

    /**
     * @return string
     *
     * @since 1.2.0
     * @version 1.2.0
     */
    public function getNameTemplate(): string
    {
        return (string)$this->getOption('nameTemplate', '');
    }

    /**
     * @return string
     *
     * @since 1.2.0
     * @version 1.2.0
     */
    private function getQueryMethod(): string
    {
        return trim((string)$this->getOption('queryMethod', self::QUERY_BEGIN));
    }

    /**
     * @return string
     *
     * @since 1.2.0
     * @version 1.2.0
     */
    private function getEntityId(): string
    {
        return (string)$this->getOption('entityId', '');
    }

    /**
     * @param array $options
     * @return array
     *
     * @since 1.2.0
     * @version 1.2.0
     */
    private function normalizeOptions(array $options): array
    {
        $options['entityId'] = (string)($options['entityId'] ?? '');

        if (empty($options['nameTemplate'])) {
            $options['nameTemplate'] = '#LIST_COLUMN_LABEL#';
        }

        return $options;
    }
}