<?php

namespace App\Library;

use ATehnix\VkClient\Client;

class VkClient
{
    private $api;
    private $account_access_token;

    public function __construct($account_access_token = null)
    {
        $this->api = new Client(config('services.vk.version'));
        $this->api->setDefaultToken(
            $account_access_token === null
                ? config('services.vk.token')
                : $account_access_token
        );
    }

    public function request($method, $parameters = [])
    {
        return $this->api->request($method, $parameters);
    }
}
