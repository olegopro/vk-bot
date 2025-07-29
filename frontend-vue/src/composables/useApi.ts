import { ref } from 'vue'
import { ApiResponseWrapper } from '@/models/ApiModel'
import { Nullable } from '@/types'
import { showErrorNotification } from '@/helpers/notyfHelper'

/**
 * Хук для управления состоянием API запросов
 *
 * Предоставляет унифицированный интерфейс для выполнения HTTP запросов
 * с автоматическим управлением состоянием загрузки, данных и ошибок
 *
 * @template T - Тип параметров, передаваемых в API функцию
 * @template D - Тип ответа API, должен расширять ApiResponseWrapper
 *
 * @param apiFunction - Функция для выполнения API запроса
 * @returns Объект с методом fetch и реактивными состояниями
 *
 * @example
 * ```typescript
 * const getUserData = useApi(async (params?: { userId: number }) => {
 *   return await axios.get<UserResponse>(`/users/${params?.userId}`)
 * })
 *
 * // Базовое использование
 * await getUserData.execute({ userId: 123 })
 * console.log(getUserData.data.value) // данные пользователя
 * console.log(getUserData.loading.value) // общее состояние загрузки
 *
 * // Использование с параметризованной загрузкой
 * await getUserData.execute({ userId: 123 }, '123') // второй параметр - ключ загрузки
 * console.log(getUserData.isLoadingKey('123')) // проверка загрузки для конкретного ключа
 *
 * // В шаблоне Vue
 * // :disabled="getUserData.loading && getUserData.isLoadingKey(userId.toString())"
 * ```
 */
// eslint-disable-next-line @typescript-eslint/no-explicit-any
export default <T, D extends ApiResponseWrapper<any>>(
  apiFunction: (parameters?: T) => Promise<D>
) => {
  /** Данные, полученные от API */
  const data = ref<D['data'] | undefined>()

  /** Состояние загрузки запроса */
  const loading = ref<boolean>(false)

  /** Сообщение об ошибке, если запрос завершился неудачно */
  const error = ref<Nullable<string>>(null)

  /** Set для отслеживания ключей параметризованной загрузки */
  const loadingKeys = ref<Set<string>>(new Set())

  /**
   * Выполняет API запрос с управлением состоянием
   *
   * @param parameters - Параметры для передачи в API функцию
   * @param loadingKey - Опциональный ключ для отслеживания параметризованной загрузки
   * @returns Promise с результатом выполнения API запроса
   *
   * @throws {Error} Пробрасывает ошибку для обработки в вызывающем коде
   */
  const execute = async (parameters?: T, loadingKey?: string) => {
    try {
      // Очищаем предыдущую ошибку
      error.value = null

      // Устанавливаем состояние загрузки
      loading.value = true

      // Если указан ключ параметризованной загрузки, добавляем его в Set
      if (loadingKey) loadingKeys.value.add(loadingKey)

      // Выполняем API запрос
      const response = await apiFunction(parameters)

      // Сохраняем полученные данные в реактивное состояние
      data.value = response?.data

      // Возвращаем ответ для случаев, когда нужен доступ к полному response
      // (например, для получения headers, status кода и т.д.)
      return response
    } catch (exception: unknown) {
      // Извлекаем сообщение из объекта ошибки или используем дефолтное
      error.value = (exception instanceof Error ? exception.message : 'Произошла ошибка')
      showErrorNotification(error.value)

      // Пробрасываем ошибку для дальнейшей обработки в store
      throw exception
    } finally {
      // Сбрасываем состояние загрузки независимо от результата
      loading.value = false

      // Если указан ключ параметризованной загрузки, удаляем его из Set
      if (loadingKey) loadingKeys.value.delete(loadingKey)
    }
  }

  /**
   * Проверяет, выполняется ли загрузка для конкретного ключа
   *
   * @param key - Ключ для проверки состояния загрузки
   * @returns true, если для указанного ключа выполняется загрузка
   */
  const isLoadingKey = (key: string): boolean => loadingKeys.value.has(key)

  return {
    /** Метод для выполнения API запроса */
    execute,
    /** Реактивное состояние загрузки */
    loading,
    /** Реактивное состояние ошибки */
    error,
    /** Реактивные данные, полученные от API */
    data,
    /** Метод для проверки параметризованной загрузки по ключу */
    isLoadingKey
  }
}
