<?php

namespace App\Providers;

use App\Repositories\AccountRepositoryInterface;
use App\Repositories\AccountRepository;
use App\Repositories\TaskRepository;
use App\Repositories\TaskRepositoryInterface;
use App\Services\LoggingService;
use App\Services\LoggingServiceInterface;
use App\Services\VkClientService;
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
		$this->app->bind(TaskRepositoryInterface::class, TaskRepository::class);
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
