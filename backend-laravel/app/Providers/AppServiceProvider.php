<?php

namespace App\Providers;

use App\Repositories\AccountRepositoryInterface;
use App\Repositories\AccountRepository;
use App\Repositories\CyclicTaskRepository;
use App\Repositories\CyclicTaskRepositoryInterface;
use App\Repositories\TaskRepository;
use App\Repositories\TaskRepositoryInterface;
use App\Services\LoggingService;
use App\Services\LoggingServiceInterface;
use App\Services\VkClientService;
use ATehnix\VkClient\Client;
use Illuminate\Support\ServiceProvider;

/**
 * Провайдер приложения.
 *
 * Этот класс отвечает за регистрацию основных сервисов приложения в контейнере служб.
 * Он связывает интерфейсы с их конкретными реализациями, обеспечивая инверсию контроля и упрощая тестирование.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Регистрация любых сервисов приложения.
     *
     * Этот метод вызывается Laravel при загрузке приложения. Он используется для связывания интерфейсов
     * с их реализациями, что позволяет приложению гибко изменять реализации сервисов без изменения кода,
     * зависящего от этих сервисов.
     *
     * @return void
     */
    public function register()
    {
        /* Регистрация сервиса логирования в контейнере приложения.
        LoggingServiceInterface - это интерфейс, определяющий методы логирования.
        LoggingService - конкретная реализация этого интерфейса, которая будет использоваться
        для записи логов в систему. Это позволяет легко заменить способ логирования, не изменяя код,
        зависящий от интерфейса логирования. */
        $this->app->bind(LoggingServiceInterface::class, LoggingService::class);

        /* Регистрация репозитория аккаунтов в контейнере приложения.
        AccountRepositoryInterface - это интерфейс, определяющий методы для работы с данными аккаунтов.
        AccountRepository - конкретная реализация интерфейса, которая обеспечивает доступ к данным аккаунтов,
        например, для их создания, обновления, получения и удаления. Использование абстракции репозитория
        позволяет упростить замену способа хранения данных без изменения бизнес-логики приложения. */
        $this->app->bind(AccountRepositoryInterface::class, AccountRepository::class);

        /* Регистрация репозитория задач в контейнере приложения.
        TaskRepositoryInterface - это интерфейс, определяющий методы для управления задачами.
        TaskRepository - реализация этого интерфейса, предоставляющая функциональность для работы с задачами,
        такую как их создание, обновление, получение и удаление. Применение репозитория позволяет
        абстрагироваться от конкретной реализации хранения данных, делая компоненты системы менее связанными
        и более гибкими в разработке и тестировании. */
        $this->app->bind(TaskRepositoryInterface::class, TaskRepository::class);

        $this->app->bind(CyclicTaskRepositoryInterface::class, CyclicTaskRepository::class);
    }

    /**
     * Инициализация любых сервисов приложения.
     *
     * Этот метод вызывается после всех регистраций сервисов и может быть использован для выполнения
     * любой инициализации, требуемой зарегистрированными сервисами.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
