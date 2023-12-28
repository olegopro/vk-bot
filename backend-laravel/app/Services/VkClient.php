<?php

namespace App\Services;

use ATehnix\VkClient\Client;
use Illuminate\Support\Facades\Log;

class VkClient
{
    private $api;
    private $account_access_token;

    public function __construct($account_access_token = null)
    {
	    $this->api = new Client(config('services.vk.version'));
	    $this->api->setDefaultToken(config('services.vk.token'));
    }

	public function request($method, $parameters = [], $token = null)
	{
		if ($token !== null) {
			$this->api->setDefaultToken($token);
		}

		return $this->api->request($method, $parameters);
	}
}
