<?php

namespace App\Services;

interface LoggingServiceInterface
{
    /**
     * @param string $channel
     * @param string $account_name
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function log(string $channel, string $account_name, string $message, array $context = []);
}
