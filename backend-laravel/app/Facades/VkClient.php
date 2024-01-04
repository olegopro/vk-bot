<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed request(string $method, array $parameters = [], string|null $token = null)
 */
class VkClient extends Facade
{
	protected static function getFacadeAccessor()
	{
		return 'VkClientService'; // Название сервиса в контейнере
	}
}
