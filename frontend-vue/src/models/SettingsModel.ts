import { ApiResponseWrapper } from './ApiModel'

// Типы для SettingsStore
export interface Settings {
  id?: number
  show_followers: boolean
  show_friends: boolean
  task_timeout: number
  created_at?: string
  updated_at?: string
}

export interface SaveSettingsRequest {
  show_followers: boolean
  show_friends: boolean
  task_timeout: number
}

export type SettingsResponse = ApiResponseWrapper<Settings[]>

export type SaveSettingsResponse = ApiResponseWrapper<Settings>