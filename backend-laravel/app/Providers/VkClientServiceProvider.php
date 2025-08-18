<?php
declare(strict_types=1);

namespace App\Providers;

use App\Facades\VkClient;
use App\Repositories\AccountRepositoryInterface;
use Illuminate\Support\ServiceProvider;
use App\Services\VkClientService;

/**
 * Сервис-провайдер для VkClientService.
 *
 * Этот класс регистрирует VkClientService в контейнере служб Laravel,
 * позволяя легко внедрять зависимости и использовать сервис в приложении.
 */
class VkClientServiceProvider extends ServiceProvider
{
    /**
     * Регистрация сервиса в контейнере приложения.
     *
     * Этот метод регистрирует VkClientService как singleton в контейнере служб,
     * обеспечивая один и тот же экземпляр сервиса на протяжении всего запроса.
     */
    public function register()
    {
        $this->app->singleton('VkClientService', function ($app) {
            return new VkClientService($app->make(AccountRepositoryInterface::class));
        });
    }

    /**
     * Загрузка любых сервисов, требуемых провайдером.
     *
     * Этот метод создает псевдоним 'VkClientService' для фасада VkClient,
     * упрощая доступ к сервису через фасад.
     */
	public function boot()
	{
		$this->app->alias('VkClientService', VkClient::class);
	}
}
