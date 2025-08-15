/**
 * Сущности для работы с настройками
 */

// Интерфейс для настроек
export interface Settings {
  id?: number
  show_followers: boolean
  show_friends: boolean
  task_timeout: number
  created_at?: string
  updated_at?: string
}
