<?php

namespace Sholokhov\Exchange\Target\Import\IBlock;

use CUtil;
use Exception;
use CIBlockSection;

use Sholokhov\Exchange\AbstractImport;
use Sholokhov\Exchange\Builder\SectionUtsBuilder;
use Sholokhov\Exchange\Dispatcher\ExternalEventTypes;
use Sholokhov\Exchange\ExchangeMapTrait;
use Sholokhov\Exchange\Fields\FieldInterface;
use Sholokhov\Exchange\MappingExchangeInterface;
use Sholokhov\Exchange\Messages\Type\Error;
use Sholokhov\Exchange\Helper\Site;
use Sholokhov\Exchange\Repository\Fields\UFRepository;
use Sholokhov\Exchange\Preparation\UserField as Prepare;
use Sholokhov\Exchange\Messages\DataResultInterface;
use Sholokhov\Exchange\Messages\Type\DataResult;
use Sholokhov\Exchange\Target\Options\Import\IBlock\IBlockOption;

use Bitrix\Main\ArgumentException;
use Bitrix\Iblock\SectionTable;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\StringField;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Импорт разделов информационного блока
 *
 * @package Import
 */
class Section extends AbstractImport implements MappingExchangeInterface, IBlockExchangeInterface
{
    use ExchangeMapTrait,
        IBlockTrait;

    protected ?UFRepository $ufRepository = null;

    private string $ufEntityId;

    /**
     * Конфигурация импорта
     *
     * @var IBlockOption
     */
    protected readonly IBlockOption $option;

    /**
     * @param IBlockOption $option Конфигурация импорта
     */
    public function __construct(IBlockOption $option)
    {
        $this->option = $option;
        parent::__construct();
    }

    /**
     * ID инфоблока в который производится импорт
     *
     * @return int
     */
    public function getIBlockID(): int
    {
        return $this->option->iBlockId;
    }

    /**
     * Свойство является множественным
     *
     * @inheritDoc
     * @param FieldInterface $field
     * @return bool
     */
    public function isMultipleField(FieldInterface $field): bool
    {
        $repository = $this->getUfFieldRepository();
        return $repository->has($field->getTo()) && $repository->get($field->getTo())['MULTIPLE'] === 'Y';
    }

    /**
     * @return string
     *
     */
    public function getUfEntityID(): string
    {
        return $this->ufEntityId ??= 'IBLOCK_' . $this->getIBlockID() . '_SECTION';
    }

    /**
     * Получить хранилище данных свойств
     *
     * @return UFRepository
     */
    public function getUfFieldRepository(): UFRepository
    {
        return $this->ufRepository ??= new UFRepository(['entity_id' => 'IBLOCK_' . $this->getIBlockID() . '_SECTION']);
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
        $types->beforeDeactivate = 'onBeforeIBlockSectionsDeactivate';
        $types->beforeUpdate = 'onBeforeIBlockSectionUpdate';
        $types->afterUpdate = 'onAfterIBlockSectionUpdate';
        $types->beforeAdd = 'onBeforeIBlockSectionAdd';
        $types->afterAdd = 'onAfterIBlockSectionAdd';

        return $types;
    }

    /**
     * Конфигурация импорта
     *
     * @inheritDoc
     * @return void
     * @throws Exception
     */
    protected function configuration(): void
    {
        parent::configuration();
        $this->initPrepared();
    }

    /**
     * Получение ID раздела из кэша
     *
     * @param array $item
     * @return int
     */
    protected function resolveId(array $item): int
    {
        $key = $this->getPrimaryField()->getTo();
        return (int)$this->getCache()->get($item[$key]);
    }

    /**
     * Проверка наличия раздела
     *
     * @param array $item
     * @return bool
     */
    protected function doExist(array $item): bool
    {
        $keyField = $this->getPrimaryField();

        $filter = [
            'IBLOCK_ID' => $this->getIBlockID(),
            $keyField->getTo() => $item[$keyField->getTo()],
        ];

        if ($hashField = $this->getHashField()) {
            $filter[$hashField->getTo()] = $this->option->hash;
        }

        if ($section = CIBlockSection::GetList([], $filter)->Fetch()) {
            // TODO: Проверить хэш импорта
            $this->getCache()->set($item[$keyField->getTo()], (int)$section['ID']);
            return true;
        }

        return false;
    }


    /**
     * Создание нового раздела
     *
     * @param array $fields
     * @param array $originalFields
     * @return DataResultInterface
     */
    protected function doAdd(array $fields, array $originalFields): DataResultInterface
    {
        $result = new DataResult;
        $section = new CIBlockSection;

        if ($id = $section->Add($fields)) {
            $result->setData((int)$id);
            $this->logger?->debug(sprintf('An element with the identifier "%s" has been added to the %s information block', $this->getIBlockID(), $id));

            if ($keyField = $this->getPrimaryField()) {
                $this->getCache()->set($fields[$keyField->getTo()], (int)$id);
            }
        } else {
            $result->addError(new Error('Error while adding IBLOCK section: ' . strip_tags($section->getLastError()), 500));
        }

        return $result;
    }

    /**
     * Логика обновления раздела
     *
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
        $section = new CIBlockSection;

        if (!$section->Update($id, $fields)) {
            return $result->addError(new Error('Error while updating IBLOCK section: ' . $section->getLastError(), 500, ['ID' => $id]));
        }

        $this->logger?->debug('Updated properties IBLOCK section: ' . $id);
        $this->cleanCache();

        return $result;
    }

    /**
     * Деактивация разделов, которые не пришли в импорте
     *
     * @inheritDoc
     * @return void
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws Exception
     */
    protected function doDeactivate(): void
    {
        $query = SectionTable::query()
            ->where('IBLOCK_ID', $this->getIBlockID())
            ->where('TIMESTAMP_X', '<', DateTime::createFromTimestamp($this->getDateStarted()))
            ->where('ACTIVE', 'Y')
            ->addSelect('ID');

        if ($hashField = $this->getHashField()) {
            if ($this->getUfFieldRepository()->has($hashField->getTo())) {
                $factory = new SectionUtsBuilder($this->getIBlockID());
                $uts = $factory->make([new StringField($hashField->getTo())]);
                $query->registerRuntimeField(
                    new Reference('UF', $uts, ['=this.ID' => 'ref.VALUE_ID'], ['join_type' => 'inner'])
                );
            } else {
                $query->where($hashField->getTo(), $this->option->hash);
            }
        }

        $this->events->beforeDeactivate(['query' => &$query]);

        $iterator = $query->exec();
        while ($section = $iterator->fetch()) {
            SectionTable::update($section['ID'], ['ACTIVE' => 'N']);
        }
    }

    /**
     * Проверка необходимости деактивации разделов после импорта
     *
     * @return bool
     */
    protected function deactivateEnabled(): bool
    {
        return $this->option->deactivate;
    }

    /**
     * Подготовка данных, для создания раздела
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
        return $this->preparation($item);
    }

    /**
     * Подготовка данных, для обновления раздела
     *
     * @inheritDoc
     * @param array $item
     * @return array
     * @throws ContainerExceptionInterface
     * @throws LoaderException
     * @throws NotFoundExceptionInterface
     */
    protected function prepareForUpdate(array $item): array
    {
        $prepared = $this->prepareForAdd($item);

        if (!isset($prepared['ACTIVE'])) {
            $prepared['ACTIVE'] = 'Y';
        }

        return $prepared;
    }

    /**
     * Преобразование данных, которые поддерживаются разделами
     *
     * @param array $item
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws LoaderException
     */
    protected function preparation(array $item): array
    {
        $result = [];
        $translitOptions = $this->getIBlockInfo()->get('FIELDS')['CODE']['DEFAULT_VALUE'] ?? [];

        foreach ($this->getMappingRegistry()->getFields() as $field) {
            $value = $item[$field->getTo()] ?? '';

            if ($field->getTo() === 'CODE' && $translitOptions) {
                $value = CUtil::translit($value, Site::getLanguage(), $translitOptions);
            }

            $result[$field->getTo()] = $value;
        }

        if (!isset($result['NAME'])) {
            $result['NAME'] = $item[$this->getPrimaryField()?->getTo()] ?? '';
        }

        if (!isset($result['CODE'])) {
            $result['CODE'] = CUtil::translit($result['NAME'], Site::getLanguage(), $translitOptions);
        }

        $result['IBLOCK_ID'] = $this->getIBlockID();

        if ($hashField = $this->getHashField()) {
            $result[$hashField->getTo()] = $this->option->hash;
        }

        return $result;
    }

    /**
     * Конфигурация обмена данными по умолчанию
     *
     * @return void
     */
    private function initPrepared(): void
    {
        $entityId = $this->getUfEntityID();
        $this->processor
            ->addPrepared(new Prepare\File($entityId))
            ->addPrepared(new Prepare\Date($entityId))
            ->addPrepared(new Prepare\DateTime($entityId))
            ->addPrepared(new Prepare\Boolean($entityId))
            ->addPrepared(new Prepare\IBlockElement($entityId))
            ->addPrepared(new Prepare\IBlockSection($entityId))
            ->addPrepared(new Prepare\Enumeration($entityId));

        // Адрес
        // Видео
        // Деньги
        // Опрос
        // Привязка к элементам справочника
        // Содержимое ссылки
        // Ссылка
        // Целое число
        // Число
        // Шаблон
    }
}