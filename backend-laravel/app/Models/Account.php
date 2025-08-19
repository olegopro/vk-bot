<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
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
            property: "birthday_date",
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

    protected $fillable = ['account_id', 'access_token', 'screen_name', 'first_name', 'last_name', 'birthday_date'];
    protected $primaryKey = 'account_id';
    public $timestamps = false;
    public $incrementing = false;

    /**
     * Шифрует access_token с использованием соли из конфига
     */
    private function encryptAccessToken(string $token): string
    {
        $salt = config('app.access_token_salt', '');
        return Crypt::encryptString($token . $salt);
    }

    /**
     * Дешифрует access_token
     */
    private function decryptAccessToken(string $encryptedToken): string
    {
        $salt = config('app.access_token_salt', '');
        $decrypted = Crypt::decryptString($encryptedToken);
        return str_replace($salt, '', $decrypted);
    }

    /**
     * Автоматически шифрует access_token при сохранении
     */
    public function setAccessTokenAttribute($value)
    {
        if ($value) {
            $this->attributes['access_token'] = $this->encryptAccessToken($value);
        }
    }

    /**
     * Автоматически дешифрует access_token при получении
     */
    public function getAccessTokenAttribute($value)
    {
        if ($value) {
            try {
                return $this->decryptAccessToken($value);
            } catch (\Exception $e) {
                // Если не удалось дешифровать, возвращаем как есть (для обратной совместимости)
                return $value;
            }
        }
        return $value;
    }
}
