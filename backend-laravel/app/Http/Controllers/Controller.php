<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    description: 'API для управления VK ботом',
    title: 'VK Bot API'
)]
#[OA\Server(
    url: '/api',
    description: 'API Server'
)]
#[OA\Tag(
    name: 'Accounts',
    description: 'Операции для управления аккаунтами ВКонтакте'
)]
#[OA\Tag(
    name: 'Tasks',
    description: 'Операции для управления задачами'
)]
#[OA\Tag(
    name: 'Cyclic Tasks',
    description: 'Операции для управления циклическими задачами'
)]
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
