<?php

namespace App\Jobs;

use App\Facades\VkClient;
use App\Models\Task;
use App\Services\LoggingService;
use App\Services\VkClientService;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * Задание для добавления лайка к посту в VK.
 *
 * Это задание добавляет лайк к определенному посту в VK, используя VK API.
 * Оно помещается в очередь и выполняется асинхронно.
 */
class addLikeToPost implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $task;
    private $token;
    private $loggingService;
    private $screenName;
    private $vkClient;

    /**
     * Создает новый экземпляр задания.
     *
     * @param Task $task Экземпляр задачи, содержащий данные для выполнения.
     * @param string $token Токен доступа для авторизации в VK API.
     * @param LoggingService $loggingService Сервис для логирования операций.
     */
    public function __construct($task, $token, LoggingService $loggingService)
    {
        $this->task = $task;
        $this->token = $token;
        $this->loggingService = $loggingService;

        // Получение screen name аккаунта по токену доступа
        $this->screenName = DB::table('accounts')
                              ->where('access_token', $token)
                              ->value('screen_name');
    }

    /**
     * Выполняет задание.
     *
     * @throws Exception Если задача просрочена или возникают другие ошибки в процессе выполнения.
     */
    public function handle()
    {
        $task = Task::find($this->task->id);

        // Проверка задачи на просроченность
        $deltaSeconds = 10; // разрешенная дельта в секундах

        /*
            $task->run_at проверяет, установлено ли в объекте задачи свойство run_at, которое обычно содержит временную метку, когда задача должна быть выполнена.
            now() получает текущее время и дату в виде экземпляра Carbon, который является расширением класса DateTime в PHP.
            new Carbon($task->run_at) создает новый экземпляр Carbon на основе времени, когда задача должна была быть запущена.
            diffInSeconds(...) сравнивает два объекта Carbon и возвращает разницу в секундах между ними.
            > $deltaSeconds проверяет, превышает ли разница установленное значение deltaSeconds, которое является допустимым временным интервалом для начала выполнения задачи. Если разница больше, значит задача считается просроченной.

            Если условие истинно (задача просрочена), тогда выполняется код внутри блока if, обновляющий статус задачи на 'canceled' и записывающий сообщение об ошибке.
            Затем генерируется исключение, чтобы сообщить о просроченной задаче.
        */
        if ($task->run_at && now()->diffInSeconds(new Carbon($task->run_at)) > $deltaSeconds) {
            $task->update([
                'status'        => 'canceled',
                'error_message' => "Просрочено: должна была запуститься в $task->run_at"
            ]);

            $this->loggingService->log(
                'account_task_likes',
                $this->screenName,
                "Просрочено: должна была запуститься в $task->run_at",
                ['task_id' => $this->task->id]
            );

            throw new Exception("Просрочено: должна была запуститься в " . $task->run_at);
        }

        // Обновление статуса задачи на 'active'
        $task->update(['status' => 'active']);

        // Выполнение основной логики задачи
        $response = $response = VkClient::request('likes.add', [
            'type'     => 'post',
            'owner_id' => $this->task->owner_id,
            'item_id'  => $this->task->item_id
        ], $this->token);

        $this->loggingService->log(
            'account_task_likes',
            $this->screenName,
            'VK API Response',
            ['response' => $response]
        );

        // Обновление статуса задачи на 'done'
        $task->update(['status' => 'done']);
    }

    /**
     * Обрабатывает неудачное выполнение задания.
     *
     * @param Throwable $exception Исключение, возникшее в процессе выполнения задания.
     */
    public function failed(Throwable $exception)
    {
        $this->loggingService->log(
            'account_task_likes',
            $this->screenName,
            'Failed Job Exception',
            ['exception' => $exception->getMessage()]
        );

        // Находим задачу и обновляем её статус и сообщение об ошибке
        $task = Task::find($this->task->id);

        $task?->update([
            'status'        => 'failed',
            'error_message' => $exception->getMessage()
        ]);
    }

    /**
     * Получает статус задачи.
     *
     * @return string Статус задачи.
     */
    public function getTaskStatus()
    {
        return $this->task->status;
    }

    /**
     * Получает идентификатор аккаунта, связанного с задачей.
     *
     * @return int Идентификатор аккаунта.
     */
    public function getAccountId() {
        return $this->task->account_id;
    }

    /**
     * Получает идентификатор задачи.
     *
     * @return int Идентификатор задачи.
     */
    public function getTaskId()
    {
        return $this->task->id;
    }
}
