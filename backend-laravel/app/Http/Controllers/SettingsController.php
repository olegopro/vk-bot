<?php

namespace App\Http\Controllers;

use App\Models\Settings;
use Illuminate\Http\Request;

/**
 * Контроллер для управления настройками приложения.
 *
 * Предоставляет методы для получения и сохранения глобальных настроек
 * системы, таких как отображение подписчиков, друзей и таймаут задач.
 */
final class SettingsController extends Controller
{
    /**
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
