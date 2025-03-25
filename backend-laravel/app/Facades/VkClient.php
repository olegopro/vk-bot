<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 *  Фасад для взаимодействия с API ВКонтакте.
 *
 *  Предоставляет удобный интерфейс для доступа к методам сервиса VkClientService.
 *  Используется для выполнения запросов к API ВКонтакте, получения данных аккаунтов,
 *  управления задачами лайков и другими операциями, связанными с ВКонтакте.
 *
 * @mixin \App\Services\VkClientService
 */


class VkClient extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'VkClientService'; // Название сервиса в контейнере
    }
}
