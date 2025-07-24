<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "AccountModel",
    description: "Модель аккаунта ВКонтакте",
    properties: [
        new OA\Property(
            property: "account_id",
            description: "Уникальный идентификатор аккаунта ВК",
            type: "integer",
            example: 123456789
        ),
        new OA\Property(
            property: "access_token",
            description: "Токен доступа к API ВКонтакте",
            type: "string",
            example: "vk1.a.abc123def456..."
        ),
        new OA\Property(
            property: "screen_name",
            description: "Короткое имя пользователя",
            type: "string",
            example: "john_doe",
            nullable: true
        ),
        new OA\Property(
            property: "first_name",
            description: "Имя пользователя",
            type: "string",
            example: "Иван",
            nullable: true
        ),
        new OA\Property(
            property: "last_name",
            description: "Фамилия пользователя",
            type: "string",
            example: "Иванов",
            nullable: true
        ),
        new OA\Property(
            property: "bdate",
            description: "Дата рождения",
            type: "string",
            format: "date",
            example: "1990-01-01",
            nullable: true
        )
    ],
    type: "object"
)]

class Account extends Model
{
    use HasFactory;

    protected $fillable = ['account_id', 'access_token', 'screen_name', 'first_name', 'last_name', 'bdate'];
    protected $primaryKey = 'account_id';
    public $timestamps = false;
    public $incrementing = false;
}
