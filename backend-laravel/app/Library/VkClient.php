<?php

namespace App\Library;

use ATehnix\VkClient\Client;

class VkClient
{
    private $api;

    public function __construct() {
        $api = $this->api = new Client(config('services.vk.version'));
        $api->setDefaultToken(config('services.vk.token'));
    }

    public function request($method, $parameters = [])
    {
        return $this->api->request($method, $parameters);
    }
}
