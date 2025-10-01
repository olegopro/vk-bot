# MEMORY BANK - VK BOT PROJECT

## СПЕЦИАЛИЗАЦИЯ АССИСТЕНТА

Ты специализирован для помощи разработчикам, работающим с Vue 3 / Laravel 10, Bootstrap, VueRouter, Pinia и использующим TypeScript.

При генерации кода для vue компонентов используй следующий порядок тегов: `<script setup lang="ts">` -> `<template>` -> `<style scoped lang="scss">`

Порядок расположения блоков в теге `<script>`: Imports -> Props/Emits -> Refs -> Computed -> Methods -> Watchers -> Lifecycle Hooks

В шаблоне (теге `<template>`) компонента используй простые однострочные функции и условия, базовые операции с props, emits, slots и другими встроенными свойствами Vue. Например: `@click="$emit('delete')"`, `v-bind="$props"`, `@reset="isEditMode = false"`.

Всегда используй абсолютные импорты в секции `<script setup lang="ts">` через алиас `@/`. Никогда не используй `./filename` или `../filename` - только импорты через `@/`!

При именовании методов для обработки событий добавляй окончание `Handler` при создании обработчиков событий типа `@click`, `@submit`, `@change`. Например: `clickHandler`, `submitHandler`, `changeHandler`.

Всегда отвечай на русском языке. Никогда не извиняйся. При ответах всегда используй "Знания проекта". При ответах всегда используй принятые стандарты кода в проекте.

---

## BACKEND АРХИТЕКТУРА (Laravel 10)

### Общие правила

- **Строгая типизация**: Все PHP файлы начинаются с `declare(strict_types=1)`
- **Final классы**: Все контроллеры объявляются как `final class`
- **Namespace**: Используется стандартная PSR-4 структура `App\`
- **PHP версия**: ^7.3 | ^8.0

### Структура слоев

```
app/
├── Console/
│   ├── Kernel.php                 # Планировщик задач
│   └── Commands/                  # Консольные команды (everyMinute)
├── Http/
│   ├── Controllers/               # Контроллеры (final class)
│   └── Middleware/
├── Models/                        # Eloquent модели
├── Repositories/                  # Репозитории (Interface + Implementation)
├── Services/                      # Бизнес-логика (Interface + Implementation)
├── Jobs/                          # Асинхронные задачи (Queue)
├── Facades/                       # Laravel Facades
├── Filters/                       # Фильтры для поисковых запросов
├── Listeners/                     # Event Listeners
└── Providers/                     # Service Providers
```

### Репозитории

**Обязательный паттерн:**
1. Создается интерфейс `*RepositoryInterface` в `app/Repositories/`
2. Реализация интерфейса `*Repository` в том же каталоге
3. Регистрация в `AppServiceProvider::register()`:
```php
$this->app->bind(AccountRepositoryInterface::class, AccountRepository::class);
```

**Примеры:**
- `AccountRepositoryInterface` + `AccountRepository`
- `TaskRepositoryInterface` + `TaskRepository`
- `CyclicTaskRepositoryInterface` + `CyclicTaskRepository`

**Методы репозиториев:**
- Возвращают типизированные данные
- Используют строгую типизацию параметров
- Работают только с моделями и БД

### Сервисы

**Обязательный паттерн:**
1. Создается интерфейс `*ServiceInterface` в `app/Services/`
2. Реализация `*Service` в том же каталоге
3. Регистрация в `AppServiceProvider::register()`

**Примеры:**
- `LoggingServiceInterface` + `LoggingService`
- `VkClientService` (с Facade `VkClient`)

**Особенности VkClientService:**
- Использует `ATehnix\VkClient\Client` для работы с VK API
- Централизованная обработка запросов через метод `request()`
- Поддержка токенов аутентификации
- Автоматический rate limiting

### Контроллеры

**Структура:**
```php
declare(strict_types=1);

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

final class ExampleController extends Controller
{
    public function __construct(
        private readonly ServiceInterface $service,
        private readonly RepositoryInterface $repository
    ) {}
    
    #[OA\Get(...)]
    public function method(): JsonResponse
    {
        // implementation
    }
}
```

**Правила:**
- Всегда `final class`
- Dependency Injection через конструктор с `readonly`
- OpenAPI аннотации обязательны для всех публичных методов
- Возвращают только `JsonResponse`
- Не содержат бизнес-логику (только координация сервисов)

**Примеры контроллеров:**
- `AccountController` - работа с одним аккаунтом (детали, друзья, лента)
- `AccountsController` - добавление и удаление аккаунтов в систему
- `TaskController` - управление задачами
- `CyclicTaskController` - циклические задачи
- `FilterController` - фильтрация данных
- `SettingsController` - настройки
- `StatisticController` - статистика

### Jobs (Асинхронные задачи)

**Структура:**
```php
class JobName implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public function __construct(
        private Task $task,
        private string $token,
        private LoggingService $loggingService
    ) {}
    
    public function handle(): void
    {
        // Основная логика
    }
    
    public function failed(Throwable $exception): void
    {
        // Обработка ошибок
    }
}
```

**Примеры:**
- `addLikeToPost` - добавление лайка к посту в VK

**Особенности:**
- Задачи ставятся в очередь через `::dispatch()` с `->delay()`
- Обязательна обработка `failed()`
- Логирование через `LoggingService`
- Используется `run_at` для контроля времени выполнения

### Console Commands

**Структура:**
```php
class CommandName extends Command
{
    protected $signature = 'run:CommandName';
    protected $description = 'Description';
    
    public function handle(): void
    {
        // Logic
    }
}
```

**Регистрация в Kernel.php:**
```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('run:DispatchLikeToNewsfeedPost')->everyMinute();
}
```

**Примеры:**
- `DispatchLikeToNewsfeedPost` - запускается каждую минуту для обработки циклических задач

### Models

**Особенности:**
```php
class ModelName extends Model
{
    use HasFactory;
    
    protected $fillable = ['field1', 'field2'];
    protected $casts = ['json_field' => 'array'];
    protected $appends = ['computed_field'];
    protected $hidden = ['relation'];
    public $timestamps = false; // если не нужны
    
    // Relationships
    public function relation()
    {
        return $this->belongsTo(Related::class, 'foreign_key');
    }
    
    // Accessors
    public function getComputedFieldAttribute()
    {
        return $this->relation->field ?? null;
    }
}
```

**Примеры моделей:**
- `Account` - аккаунты VK (шифрование токенов)
- `Task` - задачи на лайки
- `CyclicTask` - циклические задачи (с аксессорами)
- `Settings` - настройки приложения

### Filters

**Структура:**
```php
class VkUserSearchFilter
{
    protected array $filters = [];
    
    public function setParameter(?int $value): self
    {
        if ($value !== null) {
            $this->filters['parameter'] = $value;
        }
        return $this;
    }
    
    public function getFilters(): array
    {
        return $this->filters;
    }
}
```

**Использование:**
- Fluent interface для построения фильтров
- Поддержка extended filters для дополнительной обработки
- Используется в `searchUsers()` для фильтрации пользователей VK

### Facades

**Регистрация:**
1. Создать Facade в `app/Facades/`
2. Зарегистрировать ServiceProvider в `config/app.php`
3. Добавить alias в `config/app.php`

**Пример:**
```php
class VkClient extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'VkClientService';
    }
}
```

### API Routes

**Структура routes/api.php:**
```php
Route::prefix('entity')->group(function() {
    Route::get('/', [Controller::class, 'index']);
    Route::post('/action', [Controller::class, 'action']);
    Route::delete('/{id}', [Controller::class, 'delete']);
});
```

**Особенности:**
- Группировка по префиксу
- Специфичные маршруты перед общими
- RESTful naming

### Database

**Migrations:**
- Имя файла: `YYYY_MM_DD_HHMMSS_create_table_name_table.php`
- Foreign keys с `onDelete('cascade')`
- Обязательные поля с `nullable()` где нужно

**Factories:**
- Используются для генерации тестовых данных
- Faker для рандомных данных
- Возвращают массив с данными модели

**Seeders:**
- Создание начальных данных
- Используют Factories
- Вызываются из DatabaseSeeder

### OpenAPI Documentation

**Обязательные аннотации для контроллеров:**
```php
#[OA\Get(
    path: '/api/endpoint',
    description: 'Описание',
    summary: 'Краткое описание',
    tags: ['Tag'],
    parameters: [
        new OA\Parameter(...)
    ],
    responses: [
        new OA\Response(...)
    ]
)]
```

**Схемы в app/OpenApi/Schemas/:**
- `BaseResponseSchema` - базовые ответы
- `*ResponseSchema` - специфичные схемы ответов
- Использование `ref: '#/components/schemas/SchemaName'`

### Logging

**Структура:**
```php
$loggingService->log(
    'channel_name',     // Канал логирования
    'account_name',     // Имя аккаунта (для директории)
    'Message text',     // Сообщение
    ['context' => 'data'] // Контекст
);
```

**Особенности:**
- Логи по аккаунтам в `storage/logs/{account_name}/{channel}.log`
- Автоматическое определение уровня (info/error) по ключу 'exception'
- Channels в `config/logging.php`: `account_task_likes`, `account_like`, `account_newsfeed`

### Config Files

**Важные конфигурации:**
- `config/services.php` - VK API настройки
- `config/queue.php` - конфигурация очередей
- `config/app.php` - timezone: 'Europe/Moscow', access_token_salt

### Encryption

**Токены VK:**
- Шифруются через отдельный Encrypter с солью `ACCESS_TOKEN_SALT`
- Автоматическое шифрование через Accessor
- Статический метод `Account::decryptToken()` для расшифровки

---

## FRONTEND АРХИТЕКТУРА (Vue 3 + TypeScript)

### Общие правила

- **TypeScript**: Строгая типизация везде
- **Composition API**: Только `<script setup lang="ts">`
- **Single File Components**: Порядок тегов: `<script setup>` -> `<template>` -> `<style>`
- **Абсолютные импорты**: Всегда через `@/` alias

### Структура проекта

```
src/
├── assets/                    # Статика, стили
├── components/               # Компоненты
│   ├── Account/
│   ├── Tasks/
│   ├── CyclicTasks/
│   └── Accounts/
├── composables/              # Переиспользуемая логика
├── global-components/        # Глобальные компоненты
├── handlers/                 # Обработчики событий
├── helpers/                  # Вспомогательные функции
├── layouts/                  # Layouts
├── models/                   # TypeScript модели (Request/Response)
├── router/                   # Vue Router
├── stores/                   # Pinia stores
├── types/                    # TypeScript types & interfaces
└── views/                    # Страницы
```

### Composables

**useApi** - универсальный хук для API запросов:
```typescript
const apiCall = useApi(async (params?: T) => {
  return await axios.get<ResponseType>('/endpoint')
})

// Использование
await apiCall.execute({ userId: 123 })
apiCall.loading    // реактивное состояние загрузки
apiCall.data       // данные ответа
apiCall.error      // ошибка
apiCall.isLoadingKey('key') // параметризованная загрузка
```

**useModal** - управление модальными окнами:
```typescript
const { showModal, closeModal } = useModal()

// Открыть модальное окно с пропсами
showModal(ComponentName, { prop1: 'value', prop2: 123 })

// Закрыть модальное окно
closeModal(modalId)
```

**useTypedRoute** - типизированные параметры роута:
```typescript
const routeParams = useTasksRoute()
// Возвращает типизированные параметры { status, accountId }
```

**useImageUrl** - работа с изображениями:
```typescript
const { getAdjustedQualityImageUrl } = useImageUrl()
const url = getAdjustedQualityImageUrl(sizes, currentColumnClass)
```

### Pinia Stores

**Структура store:**
```typescript
export const useExampleStore = defineStore('example', () => {
  // API методы через useApi
  const fetchData = useApi(async (parameters?: ParamsType) => {
    return (await axios.get<ResponseType>('endpoint')).data
  })
  
  const createData = useApi(async (parameters?: CreateParams) => {
    if (!parameters) throw new Error('Message')
    return (await axios.post<ResponseType>('endpoint', parameters)).data
  })
  
  return {
    fetchData,
    createData
  }
})
```

**Правила:**
- Один store на сущность
- Используется Composition API подход
- Все API вызовы через `useApi`
- Обязательная валидация параметров

**Существующие stores:**
- `AccountsStore` - добавление и удаление аккаунтов в систему
- `AccountStore` - работа с одним аккаунтом (детали, друзья, лента)
- `TasksStore` - задачи
- `CyclicTasksStore` - циклические задачи
- `FilterStore` - фильтры
- `SettingsStore` - настройки
- `StatisticsStore` - статистика

### Components

**Структура компонента:**
```vue
<script setup lang="ts">
import { computed, ref } from 'vue'
import type { PropType } from 'vue'

// Props
const props = defineProps<{
  requiredProp: string
  optionalProp?: number
}>()

// Emits
const emit = defineEmits<{
  update: [value: string]
  delete: []
}>()

// Computed
const computedValue = computed(() => props.requiredProp.toUpperCase())

// Methods
const clickHandler = (): void => {
  emit('update', 'value')
}

// Lifecycle
onMounted(() => {
  // initialization
})
</script>

<template>
  <div @click="clickHandler">
    {{ computedValue }}
  </div>
</template>

<style scoped lang="scss">
// Styles
</style>
```

**Правила компонентов:**
- Используй `defineProps<Interface>()` для типизации
- Используй `defineEmits<Interface>()` для событий
- Обработчики событий с суффиксом `Handler`
- В template простые выражения: `@click="$emit('delete')"`

### Модальные окна

**Архитектура:**
1. Глобальный компонент `GlobalModal.vue` с Teleport
2. Каждое модальное окно - отдельный компонент
3. Управление через `useModal` composable
4. ID модального окна из `getCurrentInstance()?.type.__name`

**Структура модального окна:**
```vue
<script setup lang="ts">
const modalId = getCurrentInstance()?.type.__name
const { closeModal } = useModal()

const submitHandler = () => {
  // logic
  closeModal(modalId)
}
</script>

<template>
  <div class="modal fade" :id="modalId">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5">Title</h1>
          <button type="button" class="btn-close" @click="closeModal(modalId)"></button>
        </div>
        
        <BodySection>
          <!-- Content -->
        </BodySection>
        
        <FooterSection
          :on-submit="submitHandler"
          :on-cancel="() => closeModal(modalId)"
        />
      </div>
    </div>
  </div>
</template>
```

**Компоненты для модалок:**
- `BodySection` - обертка для body с PerfectScrollbar
- `FooterSection` - футер с кнопками и состоянием загрузки

### Types & Models

**Структура:**
```
types/
├── index.ts              # Общие типы (Nullable, Optional, etc)
├── vkontakte.ts          # VK сущности
├── tasks.ts              # Задачи
├── statistics.ts         # Статистика
└── settings.ts           # Настройки

models/
├── ApiModel.ts           # ApiResponseWrapper
├── AccountModel.ts       # Request/Response для аккаунтов
├── TaskModel.ts          # Request/Response для задач
└── ...
```

**Паттерн моделей:**
```typescript
// Request типы
export interface CreateTaskRequest {
  account_id: number
  task_count: number
}

// Response типы
export type TaskResponse = Task
export type TasksListResponse = TasksListData
```

### Router

**Структура:**
- Централизованные пути в `routerPaths.ts`
- Типизированные параметры роутов
- Использование `createTypedRoute` для генерации путей

**Пример:**
```typescript
export interface TasksRouteParams {
  status?: TaskStatus
  accountId?: string
}

export default {
  tasks: createTypedRoute<TasksRouteParams>('/tasks/:status?/:accountId?')
} as const
```

### Axios Configuration

**Особенности:**
- Base URL из переменных окружения: `VITE_API_URL`
- Rate limiting: 10 запросов в секунду через `axios-rate-limit`
- Централизованная настройка в `helpers/axiosConfig.ts`

### Notifications

**Использование Notyf:**
```typescript
import { showSuccessNotification, showErrorNotification } from '@/helpers/notyfHelper'

showSuccessNotification('Success message')
showErrorNotification('Error message')
```

**Настройки:**
- Duration: 3000ms
- Стили переопределены в `main.scss`

### Perfect Scrollbar

**CSS переменные:**
```css
:root {
  --ps-shadow-box: 0 1px 27px 0 rgba(34, 60, 80, 0.2);
  --ps-border-radius: calc(var(--bs-border-radius) * 2);
  --ps-height: calc(100 * var(--vh) - var(--header-height) - ...);
}
```

**Классы:**
- `.ps-table` - для таблиц
- `.ps-modal` - для модальных окон
- `.ps-detailed-info` - для детальной информации

### Bootstrap Integration

**Компоненты Bootstrap:**
- Modal (через useModal composable)
- Tooltip (инициализация в mounted)
- Dropdown, Accordion, Collapse

**Инициализация:**
```typescript
import { Tooltip } from 'bootstrap'

// Tooltip
const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]')
[...tooltips].map(el => new Tooltip(el))
```

### Стилизация

**SCSS архитектура:**
```
assets/scss/
├── main.scss
├── _bootstrap.scss       # Кастомизация Bootstrap
├── _main-overflow.scss   # Глобальные стили
└── bootstrap/
    └── _variables.scss   # Переменные Bootstrap
```

**Правила:**
- Scoped styles для компонентов
- Глобальные стили в `_main-overflow.scss`
- Bootstrap переменные в `_variables.scss`
- SCSS синтаксис везде

### Environment Variables

**Vite переменные:**
- `VITE_API_URL` - URL бэкенда

---

## СПЕЦИФИЧНЫЕ ПАТТЕРНЫ ПРОЕКТА

### Управление задачами на лайки

**Типы задач:**
1. **Обычные задачи (Task):**
   - Создаются через `TaskController::createAndQueueLikeTasksFromNewsfeed()`
   - Статусы: `pending` -> `queued` -> `active` -> `done`/`failed`/`canceled`
   - Хранятся в таблице `tasks`
   - Выполняются через Job `addLikeToPost`

2. **Циклические задачи (CyclicTask):**
   - Создаются через `CyclicTaskController::createCyclicTask()`
   - Имеют расписание `selected_times` (по дням недели и часам)
   - Распределение лайков по минутам `likes_distribution`
   - Выполняются через Console Command каждую минуту
   - Статусы: `active`, `pause`, `done`

### Работа с VK API

**Централизация через VkClientService:**
```php
// Все запросы через единый метод
public function request(string $method, array $parameters = [], string|null $token = null)

// Специализированные методы обертки:
fetchAccountData()
fetchAccountNewsfeed()
searchUsers()
getCities()
```

**Rate limiting:**
- На фронте: 10 запросов/сек через axios-rate-limit
- На бэкенде: VK API v5.199, пауза 1 сек между запросами в циклах

### Шифрование токенов

**Алгоритм:**
1. Производный ключ: `hash('sha256', config('app.key') . $salt)`
2. Encrypter с `AES-256-CBC`
3. Автоматическое шифрование через Accessor
4. Статический метод расшифровки

### Фильтрация пользователей VK

**Двухуровневая фильтрация:**
1. **API фильтры** - применяются в `users.search`:
   - sex, age_from, age_to, city, online_only, has_photo, sort, last_seen, is_friend

2. **Extended фильтры** - применяются после `users.get`:
   - min_friends, max_friends, min_followers, max_followers

**Реализация:**
```php
$filter = (new VkUserSearchFilter())
    ->setCity($cityId)
    ->setSex($sex)
    ->setExtendedFilters($minFriends, $maxFriends, ...);
```

### Логирование

**Структура логов:**
```
storage/logs/
└── {account_screen_name}/
    ├── account_task_likes.log
    ├── account_like.log
    └── account_newsfeed.log
```

**Уровни:**
- `info` - обычные операции
- `error` - при наличии ключа 'exception' в контексте

---

## ВАЖНЫЕ ТЕХНИЧЕСКИЕ ДЕТАЛИ

### База данных

**Миграции:**
- `accounts` - аккаунты (без timestamps, PK: account_id)
- `tasks` - задачи с Foreign Key на accounts
- `cyclic_tasks` - циклические задачи с JSON полями
- `jobs` - очередь задач Laravel
- `failed_jobs` - проваленные задачи
- `settings` - настройки (одна запись, без timestamps)

### Очереди

**Конфигурация:**
- Driver: database
- Queue: default
- Timeout: 60 сек
- Tries: 1

### Планировщик

**Kernel schedule:**
```php
$schedule->command('run:DispatchLikeToNewsfeedPost')->everyMinute();
```

**Логика работы:**
1. Получить активные CyclicTasks
2. Фильтровать по расписанию (день недели + час)
3. Проверить текущую минуту в `likes_distribution`
4. Создать обычную Task с флагом `is_cyclic = true`
5. Декрементить `remaining_tasks_count`

### TypeScript строгость

**tsconfig.json:**
- strict: true
- noImplicitAny: true
- esModuleInterop: true
- Paths: `@/*` -> `src/*`

### ESLint правила

- vue/multi-word-component-names: off
- vue/require-explicit-emits: error
- @typescript-eslint/no-explicit-any: error
- vue/script-indent: ['error', 2]
- Порядок тегов: ['script', 'template', 'style']

---

## NAMING CONVENTIONS

### Backend

- **Контроллеры**: `{Entity}Controller` (единственное число для одного, множественное для списка)
- **Сервисы**: `{Name}Service` + `{Name}ServiceInterface`
- **Репозитории**: `{Entity}Repository` + `{Entity}RepositoryInterface`
- **Jobs**: `{action}{Entity}` (camelCase), например `addLikeToPost`
- **Commands**: `{Action}{Entity}` (PascalCase)
- **Модели**: `{Entity}` (единственное число)

### Frontend

- **Компоненты**: PascalCase (`AccountDetails.vue`)
- **Composables**: `use{Name}` (`useModal`, `useApi`)
- **Stores**: `use{Entity}Store` (`useTasksStore`)
- **Types**: интерфейсы с суффиксом или без
- **Методы**: camelCase с суффиксом `Handler` для обработчиков событий



## ЦИКЛИЧЕСКИЕ ЗАДАЧИ - ДЕТАЛЬНАЯ РЕАЛИЗАЦИЯ

### Console Command (DispatchLikeToNewsfeedPost)

**Алгоритм работы (каждую минуту):**

1. **Получение активных задач:**
   ```php
   $activeTasks = CyclicTask::where('status', 'active')->get();
   ```

2. **Определение текущего времени:**
   ```php
   $currentDay = now()->shortDayName;      // Mon, Tue, Wed...
   $currentHour = now()->hour;             // 0-23
   $currentMinute = now()->minute;         // 0-59
   ```

3. **Конвертация дня недели:**
   ```php
   $dayMap = ['Mon' => 'пн', 'Tue' => 'вт', ...];
   $currentDayRu = $dayMap[$currentDay];
   ```

4. **Фильтрация по расписанию:**
   - Проверка `selected_times[$currentDayRu][$currentHour] === true`
   - Только задачи с активным текущим днем и часом

5. **Обновление расписания лайков (если начался новый час):**
   ```php
   if ($currentMinute === 0 && $task->updated_at->hour !== $currentHour) {
       $newLikesDistribution = generateUniqueRandomMinutes($task->tasks_per_hour);
       $task->likes_distribution = json_encode($newLikesDistribution);
   }
   ```

6. **Проверка и выполнение:**
   ```php
   if (in_array($currentMinute, $likesDistribution) && $task->remaining_tasks_count > 0) {
       $request = Request::create('', 'POST', ['account_id' => ..., 'task_count' => 1]);
       $response = app(TaskController::class)->createAndQueueLikeTasksFromNewsfeed($request, true);
       $task->decrement('remaining_tasks_count');
       if ($task->remaining_tasks_count == 0) {
           $task->status = 'done';
       }
   }
   ```

**Ключевые поля CyclicTask:**
- `selected_times` - JSON: `{"пн": [true, false, ...], "вт": [...]}` (24 булевых значения для каждого дня)
- `likes_distribution` - JSON массив минут: `[5, 15, 25, 35, 45, 55]`
- `tasks_per_hour` - количество задач в час
- `remaining_tasks_count` - оставшееся количество задач

---

## FRONTEND PATTERNS - ПРОДВИНУТЫЕ ТЕХНИКИ

### Infinite Scroll (IntersectionObserver)

**Реализация в Newsfeed.vue:**
```typescript
let observer = null

onMounted(() => {
  observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        debounceLoadMore()
      }
    })
  }, { threshold: 0 })
  
  observer.observe(document.getElementById('loader'))
})

onUnmounted(() => {
  if (observer) observer.disconnect()
})
```

**Debounce для загрузки:**
```typescript
const debounceLoadMore = debounce(() => loadMore(), 500, {
  leading: true,
  trailing: false
})
```

**Пагинация через next_from:**
- VK API возвращает `next_from` для следующей страницы
- Проверка дубликатов: `if (nextFrom === previousNextFrom) return`
- Очистка при сбросе: `if (previousNextFrom === null) accountNewsFeed = []`

### Vue Masonry (Сетка постов)

**Использование:**
```vue
<div v-masonry item-selector=".item" transition-duration="0s">
  <div v-masonry-tile :class="[columnClass, 'item']">
    <!-- Content -->
  </div>
</div>
```

**Адаптивная сетка:**
- `col-6` - 2 колонки (больше изображения)
- `col-4` - 3 колонки (средние)
- `col-3` - 4 колонки (меньшие)

**Динамическое качество изображений:**
```typescript
const getAdjustedQualityImageUrl = (sizes, currentColumnClass) => {
  const sizeMapping = {
    'col-6': ['w', 'z', 'x', 'm'],
    'col-4': ['z', 'x', 'm', 'w'],
    'col-3': ['x', 'm', 'z', 'w']
  }
  // Возвращает URL с оптимальным качеством для размера колонки
}
```

### Provide/Inject Pattern

**Использование в модальных окнах:**
```typescript
// В родительском компоненте
provide('closeModal', closeModal)

// В дочернем компоненте
const closeModal = inject('closeModal')
```

### ShallowRef для компонентов

**Оптимизация:**
```typescript
const modalComponent = shallowRef(null)
// Вместо ref() для компонентов - не отслеживает изменения внутри
```

---

## МОДАЛЬНЫЕ ОКНА - АРХИТЕКТУРА

### Глобальный менеджер модальных окон

**GlobalModal.vue структура:**
```vue
<script setup lang="ts">
const { isOpen, currentComponent, currentProps, setGlobalModalRef } = useModal()

const modalComponent = computed(() => currentComponent.value)
const modalProps = computed(() => currentProps.value || {})

onMounted(() => setGlobalModalRef(getCurrentInstance()))
</script>

<template>
  <Teleport to="body">
    <component
      v-if="isOpen && modalComponent"
      :is="modalComponent"
      v-bind="modalProps"
      ref="modalComponentRef"
    />
  </Teleport>
</template>
```

### useModal Composable - Централизованное управление

**Глобальное состояние:**
```typescript
const modals = ref<Modals>({})
const isOpen = ref<boolean>(false)
const currentComponent = ref<Nullable<Component>>(null)
const currentProps = ref<Nullable<ModalProps>>(null)
const GlobalModalRef = ref<Nullable<ComponentInternalInstance>>(null)
```

**Метод showModal:**
```typescript
const showModal = async <T extends Component>(component: T, props?: ComponentProps<T>) => {
  currentComponent.value = component
  currentProps.value = props ?? null
  isOpen.value = true
  
  await nextTick()
  
  const currentModal = (GlobalModalRef.value?.refs?.modalComponentRef as ComponentPublicInstance)?.$el
  const modalKey = currentModal.id
  
  if (!modals.value[modalKey]) {
    const modalInstance = new Modal(currentModal)
    
    currentModal.addEventListener('hidden.bs.modal', () => {
      isOpen.value = false
      currentComponent.value = null
      currentProps.value = null
      delete modals.value[modalKey]
    })
    
    modals.value[modalKey] = modalInstance
  }
  
  modals.value[modalKey].show()
}
```

**Метод closeModal:**
```typescript
const closeModal = (modalId?: string) => {
  const modalToClose = modals.value[modalId]
  modalToClose?.hide()
}
```

### Модальное окно - Шаблон

**Минимальная структура:**
```vue
<script setup lang="ts">
import { getCurrentInstance } from 'vue'
import { useModal } from '@/composables/useModal'
import BodySection from '@/global-components/modal-component/BodySection.vue'
import FooterSection from '@/global-components/modal-component/footer/FooterSection.vue'

const modalId = getCurrentInstance()?.type.__name
const { closeModal } = useModal()

const submitHandler = () => {
  // Logic
  closeModal(modalId)
}
</script>

<template>
  <div class="modal fade" :id="modalId" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5">Title</h1>
          <button type="button" class="btn-close" @click="closeModal(modalId)"></button>
        </div>
        
        <BodySection>
          <!-- Content with auto PerfectScrollbar -->
        </BodySection>
        
        <FooterSection
          :on-submit="submitHandler"
          :on-cancel="() => closeModal(modalId)"
          :is-loading="store.loading"
        />
      </div>
    </div>
  </div>
</template>
```

### Глобальные компоненты для модалок

**BodySection.vue:**
- Автоматическая обертка в PerfectScrollbar
- Класс `.ps-modal` с предустановленной высотой
- Убирает padding: `py-0`

**FooterSection.vue:**
```vue
<script setup lang="ts">
interface FooterSectionProps {
  onSubmit: () => void
  onCancel: () => void
  submitDisabled?: boolean
  cancelDisabled?: boolean
  isLoading?: boolean
  submitText?: string
  cancelText?: string
  showCancel?: boolean
}
</script>

<template>
  <div class="modal-footer">
    <button v-if="showCancel" @click="onCancel">{{ cancelText }}</button>
    <button @click="onSubmit" :disabled="submitDisabled || isLoading">
      {{ isLoading ? 'Загрузка...' : submitText }}
      <span v-if="isLoading" class="spinner-border spinner-border-sm"></span>
    </button>
  </div>
</template>
```

### Типы модальных окон в проекте

**Account modals:**
- `AccountDetailsModal` - детали пользователя/группы
- `GroupDetailsModal` - детали группы VK

**Tasks modals:**
- `AddTaskModal` - создание задачи (с подкомпонентами)
  - `NewsfeedSearch` - поиск по ленте
  - `CitySearch` - поиск по городу
- `DeleteTaskModal` - удаление одной задачи
- `DeleteAllTasksModal` - удаление всех задач
- `TaskDetailsModal` - детали задачи с аккордеоном лайков

**CyclicTasks modals:**
- `AddCyclicTaskModal` - создание циклической задачи
- `EditCyclicTaskModal` - редактирование
- `DeleteCyclicTaskModal` - удаление одной
- `DeleteAllCyclicTasksModal` - удаление всех

**Accounts modals:**
- `AddAccountModal` - добавление аккаунта
- `DeleteAccountModal` - удаление аккаунта

### Модальное окно с подкомпонентами (AddTaskModal)

**Паттерн композиции:**
```vue
<script setup lang="ts">
const newsfeedSearchRef = ref<InstanceType<typeof NewsfeedSearch>>()
const citySearchRef = ref<InstanceType<typeof CitySearch>>()

const searchType = ref<'newsfeed' | 'city'>('newsfeed')

const handleTaskSubmit = () => {
  switch (searchType.value) {
    case 'newsfeed':
      newsfeedSearchRef.value?.addFeedTask()
      break
    case 'city':
      citySearchRef.value?.addCityTask()
      break
  }
}

// Динамическая загрузка
const loadingStates = {
  newsfeed: computed(() => accountStore.addPostsToLike.loading),
  city: computed(() => accountStore.createTasksForCity.loading)
}

const isFooterLoading = computed(() => loadingStates[searchType.value]?.value ?? false)
</script>

<template >
  <BodySection :key="searchType">
    <NewsfeedSearch v-if="searchType === 'newsfeed'" ref="newsfeedSearchRef" />
    <CitySearch v-if="searchType === 'city'" ref="citySearchRef" />
  </BodySection>
  
  <FooterSection :is-loading="isFooterLoading" />
</template>
```

**Подкомпоненты:**
- Используют `defineExpose({ methodName })` для доступа к методам
- Emit событие `success` с данными ответа
- Не управляют модальным окном напрямую



## ОБРАБОТКА ОШИБОК

### Backend

**Единообразный формат ответов:**
```php
return response()->json([
    'success' => true/false,
    'data' => $data,
    'message' => 'Сообщение'
], $statusCode);
```

**При ошибках:**
```php
return response()->json([
    'success' => false,
    'error' => 'Описание ошибки'
], 500);
```

### Frontend

**Через useApi:**
- Автоматический catch ошибок
- Показ уведомления через `showErrorNotification()`
- Пробрасывание ошибки для обработки в store

**Валидация в формах:**
```typescript
if (!parameters) throw new Error('Не указаны параметры')
```



## КРИТИЧЕСКИЕ ПРАВИЛА ПРОЕКТА

### Backend MUST HAVE:

1. ✅ `declare(strict_types=1)` в начале каждого PHP файла
2. ✅ `final class` для всех контроллеров
3. ✅ Interface + Implementation для сервисов и репозиториев
4. ✅ OpenAPI аннотации на всех публичных методах контроллеров
5. ✅ Dependency Injection через конструктор с `readonly`
6. ✅ Типизация параметров и возвращаемых значений
7. ✅ Логирование через `LoggingService`
8. ✅ Foreign keys с `onDelete('cascade')`

### Frontend MUST HAVE:

1. ✅ Абсолютные импорты через `@/` (никогда `./` или `../`)
2. ✅ TypeScript строгая типизация (no `any`)
3. ✅ Суффикс `Handler` для обработчиков событий
4. ✅ Порядок тегов: `<script>` -> `<template>` -> `<style>`
5. ✅ Порядок в `<script>`: Computed -> Methods -> Watchers -> Hooks
6. ✅ Простые выражения в template
7. ✅ Все API через `useApi` composable
8. ✅ Модальные окна через `useModal` + `GlobalModal`



## ОБРАБОТКА ОШИБОК

### Backend

**Единообразный формат ответов:**
```php
return response()->json([
    'success' => true/false,
    'data' => $data,
    'message' => 'Сообщение'
], $statusCode);
```

**При ошибках:**
```php
return response()->json([
    'success' => false,
    'error' => 'Описание ошибки'
], 500);
```

### Frontend

**Через useApi:**
- Автоматический catch ошибок
- Показ уведомления через `showErrorNotification()`
- Пробрасывание ошибки для обработки в store

**Валидация в формах:**
```typescript
if (!parameters) throw new Error('Не указаны параметры')
```

---

## КЛЮЧЕВЫЕ ОТЛИЧИЯ ПРОЕКТА

### Разделение контроллеров по единственному/множественному числу
- `AccountController` - работа с ОДНИМ аккаунтом (детали, друзья, лента)
- `AccountsController` - добавление и удаление аккаунтов в систему (не работа со списком, а именно управление аккаунтами в системе)

### Циклические задачи = Console Command + Обычные Task
- Console команда создает обычные Task с флагом `is_cyclic = true`
- Расписание в CyclicTask, выполнение через addLikeToPost Job

### Двухуровневая система типов
- `types/` - базовые интерфейсы сущностей
- `models/` - Request/Response обертки для API

### Параметризованная загрузка в useApi
```typescript
await apiCall.execute({ id: 123 }, '123') // loadingKey
apiCall.isLoadingKey('123') // проверка
```

### Fluent Interface для фильтров
```php
$filter->setCity($id)->setSex($sex)->setAgeFrom($age)->getFilters()
```

---

## ВАЖНЫЕ НЮАНСЫ

### Backend:
- Токены VK шифруются с отдельной солью `ACCESS_TOKEN_SALT`
- Timezone всегда `Europe/Moscow`
- Логи создаются по аккаунтам в отдельных директориях
- Задачи имеют поле `run_at` для контроля просрочки (delta 10 сек)
- При удалении задач удаляются и из `jobs` таблицы

### Frontend:
- Модальные окна управляются глобально через один `GlobalModal`
- PerfectScrollbar с кастомными классами для разных контекстов
- IntersectionObserver для infinite scroll
- Vue Masonry для сетки изображений
- Debounce на 500ms для поиска и загрузки
- Rate limit axios: 10 req/sec

### API Response всегда:
```typescript
interface ApiResponseWrapper<T> {
  success: boolean
  data: T
  message: string
}
```

---

## ЗАКЛЮЧЕНИЕ

Этот документ описывает установленные технические правила и архитектурные решения проекта VK Bot. Все новые функции должны следовать этим паттернам для поддержания консистентности кодовой базы.
