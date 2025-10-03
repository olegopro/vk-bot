import { Settings } from '@/types/settings'

// Response типы
export type SettingsResponse = Settings
export type SaveSettingsResponse = null

// Request типы
export interface SaveSettingsRequest {
  show_followers: boolean
  show_friends: boolean
  task_timeout: number
}
