<?php

namespace Sholokhov\Exchange\Target\Import\IBlock;

use CUtil;
use Exception;
use CIBlockElement;

use Sholokhov\Exchange\AbstractImport;
use Sholokhov\Exchange\Dispatcher\ExternalEventTypes;
use Sholokhov\Exchange\ExchangeMapTrait;
use Sholokhov\Exchange\Fields\FieldInterface;
use Sholokhov\Exchange\MappingExchangeInterface;
use Sholokhov\Exchange\Messages\DataResultInterface;
use Sholokhov\Exchange\Messages\Type\Error;
use Sholokhov\Exchange\Messages\Type\DataResult;
use Sholokhov\Exchange\Helper\Site;
use Sholokhov\Exchange\Preparation\IBlock\Element as Prepare;
use Sholokhov\Exchange\Fields\IBlock\ElementFieldInterface;
use Sholokhov\Exchange\Repository\IBlock\PropertyRepository;
use Sholokhov\Exchange\Target\Options\Import\IBlock\IBlockOption;

use Bitrix\Main\LoaderException;
use Bitrix\Main\Type\DateTime;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Импортирование элемента информационного блока
 *
 * @package Import
 */
class Element extends AbstractImport implements MappingExchangeInterface
{
    use ExchangeMapTrait,
        IBlockTrait;

    /**
     * Хранилище свойств инфоблока
     *
     * @var PropertyRepository|null
     */
    private ?PropertyRepository $propertyRepository = null;

    /**
     * Конфигурация импорта
     *
     * @var IBlockOption
     */
    protected readonly IBlockOption $option;

    /**
     * @param IBlockOption $option
     * @throws Exception
     */
    public function __construct(IBlockOption $option)
    {
        $this->option = $option;
        parent::__construct();
    }

    /**
     * Информационный блок в который идет импорт
     *
     * @return int
     */
    public function getIBlockID(): int
    {
        return $this->option->iBlockId;
    }

    /**
     * Деактивация элементов, которые не пришли в импорте
     *
     * @inheritDoc
     * @return void
     */
    public function deactivate(): void
    {
        $filter = [
            'IBLOCK_ID' => $this->getIBlockID(),
            '<TIMESTAMP_X' => DateTime::createFromTimestamp($this->getDateStarted()),
            'ACTIVE' => 'Y',
        ];

        if ($hashField = $this->getHashFieldCode()) {
            $filter[$hashField] = $this->option->hash;
        }

        $select = ['ID'];

        $parameters = compact('filter', 'select');

        $this->events->beforeDeactivate(['parameters' => &$parameters]);

        $iBlock = new CIBlockElement;
        $iterator = CIBlockElement::GetList([], $parameters['filter'], false, false, $parameters['select']);

        while ($element = $iterator->fetch()) {
            $iBlock->Update($element['ID'], ['ACTIVE' => 'N']);
        }
    }

    /**
     * Проверка, что поле является множественным
     *
     * @inheritDoc
     * @param FieldInterface $field
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function isMultipleField(FieldInterface $field): bool
    {
        $property = $this->getPropertyRepository()->get($field->getTo());
        return $property && $property['MULTIPLE'] === 'Y';
    }

    /**
     * Конфигурация импорта
     *
     * @inheritDoc
     * @return void
     */
    protected function configuration(): void
    {
        parent::configuration();
        $this->initPrepared();
    }

    /**
     * Получение доступных внешних событий обмена
     *
     * @inheritDoc
     * @return ExternalEventTypes
     */
    protected function getEventTypes(): ExternalEventTypes
    {
        $types = new ExternalEventTypes;
        $types->beforeDeactivate = 'onBeforeIBlockElementsDeactivate';
        $types->beforeUpdate = 'onBeforeIBlockElementUpdate';
        $types->afterUpdate = 'onAfterIBlockElementUpdate';
        $types->beforeAdd = 'onBeforeIBlockElementAdd';
        $types->afterAdd = 'onAfterIBlockElementAdd';

        return $types;
    }

    /**
     * Получение ID элемента из кэша
     *
     * @inheritDoc
     * @param array $item
     * @return int
     */
    protected function resolveId(array $item): int
    {
        $key = $this->getPrimaryField()->getTo();
        return (int)$this->getCache()->get($item[$key]);
    }

    /**
     * Проверка наличия элемента
     *
     * @inheritDoc
     * @param array $item
     * @return bool
     */
    protected function doExist(array $item): bool
    {
        $keyField = $this->getPrimaryField();
        $externalId = $item[$keyField->getTo()];

        $filter = [
            'IBLOCK_ID' => $this->getIBlockID(),
        ];

        if ($hashField = $this->getHashFieldCode()) {
            $filter[$hashField] = $this->option->hash;
        }

        if ($keyField instanceof ElementFieldInterface) {
            $filter['PROPERTY_' . $keyField->getTo()] = $externalId;
        } else {
            $filter[$keyField->getTo()] = $externalId;
        }

        if ($element = CIBlockElement::GetList([], $filter)->Fetch()) {
            $this->getCache()->set($item[$keyField->getTo()], (int)$element['ID']);

            return true;
        }

        return false;
    }

    /**
     * Логика создания нового элемента ИБ
     *
     * @inheritDoc
     * @param array $fields
     * @param array $originalFields
     * @return DataResultInterface
     */
    protected function doAdd(array $fields, array $originalFields): DataResultInterface
    {
        $result = new DataResult;
        $iBlock = new CIBlockElement;

        if ($itemId = $iBlock->Add($fields)) {
            $result->setData((int)$itemId);
            $this->logger?->debug(sprintf('An element with the identifier "%s" has been added to the %s information block', $this->getIBlockID(), $itemId));
            $this->getCache()->set($originalFields[$this->getPrimaryField()->getTo()], (int)$itemId);
        } else {
            $result->addError(new Error('Error while adding IBLOCK element: ' . strip_tags($iBlock->getLastError()), 500));
        }

        return $result;
    }

    /**
     * Обновление элемента инфоблока
     *
     * @inheritDoc
     * @param int $id
     * @param array $fields
     * @param array $originalFields
     * @return DataResultInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function doUpdate(int $id, array $fields, array $originalFields): DataResultInterface
    {
        $result = new DataResult;
        $iBlock = new CIBlockElement;

        if (!$iBlock->Update($id, $fields['FIELDS'])) {
            return $result->addError(new Error('Error while updating IBLOCK element: ' . $iBlock->getLastError(), 500, ['ID' => $id]));
        }

        $this->logger?->debug('Updated fields IBLOCK element: ' . $id);

        $iBlock::SetPropertyValuesEx($id, $this->getIBlockID(), $fields['PROPERTIES']);

        $this->logger?->debug('Updated properties IBLOCK element: ' . $id);
        $this->cleanCache();

        return $result;
    }

    /**
     * Преобразование данных для добавления элемента
     *
     * @inheritDoc
     * @param array $item
     * @return array
     * @throws ContainerExceptionInterface
     * @throws LoaderException
     * @throws NotFoundExceptionInterface
     */
    protected function prepareForAdd(array $item): array
    {
        $fields = $this->preparation($item);
        $data = $fields['FIELDS'];
        $data['IBLOCK_ID'] = $this->getIBlockID();
        $data['PROPERTY_VALUES'] = $fields['PROPERTIES'] ?? [];

        return $data;
    }

    /**
     * Преобразование данных, для обновления элемента
     *
     * @inheritDoc
     * @param array $item
     * @return array[]
     * @throws ContainerExceptionInterface
     * @throws LoaderException
     * @throws NotFoundExceptionInterface
     */
    protected function prepareForUpdate(array $item): array
    {
        return $this->preparation($item);
    }

    /**
     * Разделение импортируемых данных на группы
     *
     * @param array{FIELDS: array, PROPERTIES: array} $item
     * @return array|array[]
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws LoaderException
     */
    protected function preparation(array $item): array
    {
        $result = [
            'FIELDS' => [],
            'PROPERTIES' => []
        ];

        foreach ($this->getMappingRegistry()->getFields() as $field) {
            $group = 'FIELDS';
            $value = $item[$field->getTo()] ?? null;

            if ($field instanceof ElementFieldInterface) {
                $group = 'PROPERTIES';
            } elseif ($field->getTo() === 'CODE') {
                $translitOptions = $this->getIBlockInfo()->get('FIELDS')['CODE']['DEFAULT_VALUE'] ?? [];

                if ($translitOptions) {
                    $value = CUtil::translit($value, Site::getLanguage(), $translitOptions);
                }
            }

            $result[$group][$field->getTo()] = $value;
        }

        $requiredFields = ['NAME', 'CODE', 'XML_ID'];
        array_walk($requiredFields, function($field) use (&$result, $item) {
            if (!isset($result['FIELDS'][$field])) {
                $result['FIELDS'][$field] = $item[$this->getPrimaryField()?->getTo()] ?? '';
            }
        });

        if ($hashField = $this->getHashField()) {
            if ($hashField instanceof ElementFieldInterface) {
                $result['PROPERTIES'][$hashField->getTo()] = $this->option->hash;
            } else {
                $result['FIELDS'][$hashField->getTo()] = $this->option->hash;
            }
        }

        return $result;
    }

    /**
     * Получение свойства в котором хранится хэш импорта.
     *
     * Если это пользовательское свойство, то автоматически преобразовывается в формат PROPERTY_xxxx
     *
     * @return string
     */
    protected function getHashFieldCode(): string
    {
        $field = $this->getHashField();

        if (!$field) {
            return '';
        }

        return $field instanceof ElementFieldInterface ? "PROPERTY_" . $field->getTo() : $field->getTo();
    }

    /**
     * Инициализация преобразователей импортируемых данных
     *
     * @return void
     */
    protected function initPrepared(): void
    {
        $iBlockID = $this->getIBlockID();
        $this->processor
            ->addPrepared(new Prepare\Date($iBlockID))
            ->addPrepared(new Prepare\DateTime($iBlockID))
            ->addPrepared(new Prepare\Number($iBlockID))
            ->addPrepared(new Prepare\Enumeration($iBlockID))
            ->addPrepared(new Prepare\PropertyFile($iBlockID))
            ->addPrepared(new Prepare\IBlockElement($iBlockID))
            ->addPrepared(new Prepare\File)
            ->addPrepared(new Prepare\IBlockSection($iBlockID))
            ->addPrepared(new Prepare\HtmlText($iBlockID))
            ->addPrepared(new Prepare\HandbookElement($iBlockID));

        // Video
        // Деньги
        // Привязка к яндекс.карте
        // Привязка к Google.Maps
        // Привязка к пользователю
        // Привязка к разделам автозаполнения
        // Привязка к теме форума
        // Привязка к товару(SKU)
        // Привязка к файлу (на сервере)
        // Привязка к элементам в виде списка
        // Привязка к элементам по XML_ID
        // Привязка к элементам с автозаполнением
        // Счетчик
    }
}
