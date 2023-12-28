<?php

namespace App\Providers;

use App\Repositories\AccountRepositoryInterface;
use App\Repositories\AccountRepository;
use App\Services\LoggingService;
use App\Services\LoggingServiceInterface;
use App\Services\VkClient;
use ATehnix\VkClient\Client;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->bind(LoggingServiceInterface::class, LoggingService::class);
		$this->app->bind(AccountRepositoryInterface::class, AccountRepository::class);

		// Регистрация VkClient как сервиса
		$this->app->singleton(VkClient::class, function ($app) {
			return new VkClient();
		});
	}

	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		//
	}
}
