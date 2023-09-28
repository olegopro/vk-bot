<?php

namespace App\Http\Controllers;

use Socialite;

class OAuthController extends Controller
{
    public function redirectToProvider()
    {
        return Socialite::driver('vk')->redirect();
    }

    public function handleProviderCallback()
    {
        $user = Socialite::driver('vk')->user();

        // Сохранение данных пользователя и токена в базу данных
    }

}
