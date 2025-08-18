<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\CyclicTask;

/**
 * Репозиторий для управления циклическими задачами.
 *
 * Этот класс предоставляет методы для работы с циклическими задачами в системе,
 * включая получение, удаление, приостановку и редактирование задач.
 * Репозиторий реализует интерфейс CyclicTaskRepositoryInterface и инкапсулирует
 * всю логику взаимодействия с базой данных для циклических задач.
 */
class CyclicTaskRepository implements CyclicTaskRepositoryInterface
{
    /**
     * Получает все циклические задачи без пагинации.
     *
     * Метод возвращает коллекцию всех циклических задач из базы данных,
     * отсортированных по ID. Используется для загрузки всех задач сразу
     * без разбиения на страницы.
     *
     * @return \Illuminate\Database\Eloquent\Collection Коллекция всех циклических задач
     */
    public function getAllCyclicTasks()
    {
        return CyclicTask::all();
    }

    /**
     * Удаляет циклическую задачу по её идентификатору.
     *
     * Метод находит и удаляет циклическую задачу из базы данных по-указанному ID.
     * Возвращает результат операции удаления (количество удаленных записей).
     *
     * @param int $taskId Идентификатор циклической задачи для удаления
     * @return int Количество удаленных записей (обычно 0 или 1)
     */
    public function deleteCyclicTask($taskId) {
        return CyclicTask::destroy($taskId);
    }

    /**
     * Удаляет все циклические задачи из базы данных.
     *
     * Метод полностью очищает таблицу циклических задач, удаляя все записи.
     * Используется метод truncate() для более эффективного удаления всех записей.
     *
     * @return CyclicTask Результат операции удаления всех записей
     */
    public function deleteAllCyclicTasks()
    {
        return CyclicTask::truncate();
    }

    /**
     * Изменяет статус циклической задачи (пауза/активация).
     *
     * Метод находит циклическую задачу по ID и меняет её статус:
     * - Если задача была активна ('active'), устанавливает статус 'pause'
     * - Если задача была приостановлена ('pause'), устанавливает статус 'active'
     *
     * @param int $taskId Идентификатор циклической задачи
     * @return array|null Массив с обновленной задачей и новым статусом, или null если задача не найдена
     */
    public function pauseCyclicTask($taskId)
    {
        $task = CyclicTask::find($taskId);

        if ($task) {
            $task->status = $task->status === 'active' ? 'pause' : 'active';
            $task->save();

            return [
                'task' => $task,
                'statusChangedTo' => $task->status,
            ];
        }

        return $task;
    }

    /**
     * Редактирует циклическую задачу с указанными данными.
     *
     * Метод находит циклическую задачу по ID и обновляет её данные согласно
     * переданному массиву. Поддерживается обновление полей: account_id, total_task_count,
     * tasks_per_hour, status, selected_times и других полей, определенных в модели.
     *
     * @param int $taskId Идентификатор циклической задачи для редактирования
     * @param array $data Ассоциативный массив с новыми значениями полей задачи
     * @return \App\Models\CyclicTask|null Обновленный объект задачи или null, если задача не найдена
     */
    public function editCyclicTask($taskId, $data)
    {
        $task = CyclicTask::find($taskId);

        if ($task) {
            $task->update($data);

            return $task;
        }

        return $task;
    }
}
