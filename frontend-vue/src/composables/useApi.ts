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
 * // Использование
 * await getUserData.fetch({ userId: 123 })
 * console.log(getUserData.data.value) // данные пользователя
 * console.log(getUserData.loading.value) // состояние загрузки
 * ```
 */
export default <T, D extends ApiResponseWrapper<any>>(
  apiFunction: (parameters?: T) => Promise<D>
) => {
  /** Данные, полученные от API */
  const data = ref<D['data'] | undefined>()

  /** Состояние загрузки запроса */
  const loading = ref<boolean>(false)

  /** Сообщение об ошибке, если запрос завершился неудачно */
  const error = ref<Nullable<string>>(null)

  /**
   * Выполняет API запрос с управлением состоянием
   *
   * @param parameters - Параметры для передачи в API функцию
   * @returns Promise с результатом выполнения API запроса
   *
   * @throws {Error} Пробрасывает ошибку для обработки в вызывающем коде
   */
  const execute = async (parameters?: T) => {
    try {
      // Очищаем предыдущую ошибку
      error.value = null

      // Устанавливаем состояние загрузки
      loading.value = true

      // Выполняем API запрос
      const response = await apiFunction(parameters)

      // Сохраняем полученные данные в реактивное состояние
      data.value = response.data

      // Возвращаем ответ для случаев, когда нужен доступ к полному response
      // (например, для получения headers, status кода и т.д.)
      return response
   } catch (exception: unknown) {
      // Извлекаем сообщение из объекта ошибки или используем дефолтное
      error.value = (exception instanceof Error ? exception.message : 'Произошла ошибка')
      showErrorNotification(error.value)

      // Пробрасываем ошибку для дальнейшей обработки в store
      // throw exception
    } finally {
      // Сбрасываем состояние загрузки независимо от результата
      loading.value = false
    }
  }

  return {
    /** Метод для выполнения API запроса */
    execute,
    /** Реактивное состояние загрузки */
    loading,
    /** Реактивное состояние ошибки */
    error,
    /** Реактивные данные, полученные от API */
    data
  }
}
