<?php

namespace Sholokhov\Exchange\Events;

/**
 * Зарегистрированные события обмена
 */
enum ExchangeEvent: string
{
    /**
     * Событие вызывается перед началом обмена
     */
    case BeforeRun = 'onBeforeRun';

    /**
     * Событие вызывается после завершения обмена
     */
    case AfterRun = 'onAfterRun';

    /**
     * Событие вызывается перед добавлением элемента сущности
     */
    case BeforeAdd = 'onBeforeAdd';

    /**
     * Событие вызывается после создания элемента сущности
     */
    case AfterAdd = 'onAfterAdd';

    /**
     * Событие вызывается перед обновлением элемента сущности
     */
    case BeforeUpdate = 'onBeforeUpdate';

    /**
     * Событие вызывается после обновления элемента сущности
     */
    case AfterUpdate = 'onAfterUpdate';

    /**
     * Событий перед импортом значения и его преобразованием
     */
    case BeforeImportItem = 'onBeforeImportItem';

    /**
     * Событие после импорта значения
     */
    case AfterImportItem = 'onAfterImportItem';
}