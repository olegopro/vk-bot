<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \App\Services\VkClientService
 */
class VkClient extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'VkClientService'; // Название сервиса в контейнере
    }
}
