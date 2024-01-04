<?php

namespace App\Services;

use ATehnix\VkClient\Client;

class VkClientService
{
    private $api;

    public function __construct()
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
