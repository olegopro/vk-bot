<?php

namespace App\Providers;

use App\Facades\VkClient;
use Illuminate\Support\ServiceProvider;
use App\Services\VkClientService;

class VkClientServiceProvider extends ServiceProvider
{
	public function register()
	{
		$this->app->singleton('VkClientService', function ($app) {
			return new VkClientService();
		});
	}

	public function boot()
	{
		$this->app->alias('VkClientService', VkClient::class);
	}
}
