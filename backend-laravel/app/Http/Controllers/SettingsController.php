<?php

namespace App\Http\Controllers;

use App\Models\Settings;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Settings",
 *     description="API для управления настройками приложения"
 * )
 */

/**
 * Контроллер для управления настройками приложения.
 *
 * Предоставляет методы для получения и сохранения глобальных настроек
 * системы, таких как отображение подписчиков, друзей и таймаут задач.
 */
final class SettingsController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/settings",
     *     tags={"Settings"},
     *     summary="Получить настройки приложения",
     *     description="Возвращает все настройки приложения",
     *     @OA\Response(
     *         response=200,
     *         description="Успешное получение настроек",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="show_followers", type="boolean", example=true),
     *                 @OA\Property(property="show_friends", type="boolean", example=true),
     *                 @OA\Property(property="task_timeout", type="integer", example=30),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     )
     * )
     *
     * Получает все настройки приложения.
     *
     * Этот метод извлекает все записи из таблицы settings
     * и возвращает их в ответе HTTP запроса.
     *
     * @return \Illuminate\Http\Response Ответ, содержащий все настройки приложения
     */
    public function getSettings()
    {
        $settings = Settings::all();

        return response($settings);
    }

    /**
     * @OA\Post(
     *     path="/api/settings/save",
     *     tags={"Settings"},
     *     summary="Сохранить настройки приложения",
     *     description="Обновляет настройки приложения",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"show_followers", "show_friends", "task_timeout"},
     *             @OA\Property(property="show_followers", type="boolean", example=true, description="Отображение подписчиков"),
     *             @OA\Property(property="show_friends", type="boolean", example=true, description="Отображение друзей"),
     *             @OA\Property(property="task_timeout", type="integer", example=30, description="Таймаут задач в секундах")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Настройки успешно сохранены",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Настройки сохранены")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Ошибка валидации"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     *
     * Сохраняет настройки приложения.
     *
     * Этот метод обновляет значения настроек в базе данных на основе
     * данных, полученных в HTTP запросе. Обновляются следующие настройки:
     * - show_followers: отображение подписчиков
     * - show_friends: отображение друзей
     * - task_timeout: таймаут задач
     *
     * @param \Illuminate\Http\Request $request HTTP запрос, содержащий новые значения настроек
     * @return void
     */
    public function saveSettings(Request $request)
    {
        Settings::find(1)->update([
            'show_followers' => $request->input('show_followers'),
            'show_friends'   => $request->input('show_friends'),
            'task_timeout'   => $request->input('task_timeout')
        ]);
    }
}
