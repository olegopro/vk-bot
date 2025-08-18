<?php

declare(strict_types=1);

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
    name: 'Account',
    description: 'Операции для управления аккаунтами ВКонтакте'
)]
#[OA\Tag(
    name: 'Accounts',
    description: 'Операции для управления аккаунтами системы'
)]
#[OA\Tag(
    name: 'Tasks',
    description: 'Операции для управления задачами'
)]
#[OA\Tag(
    name: 'Cyclic Tasks',
    description: 'Операции для управления циклическими задачами'
)]
#[OA\Tag(
    name: "Filters",
    description: "API для фильтрации и поиска пользователей ВКонтакте"
)]
#[OA\Tag(
    name: "Statistics",
    description: "API для работы со статистикой задач"
)]
#[OA\Tag(
    name: "Settings",
    description: "API для управления настройками приложения")
]
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
