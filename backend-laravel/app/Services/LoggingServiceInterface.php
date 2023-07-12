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
    public function log($channel, $account_name, $message, $context = []);
}
