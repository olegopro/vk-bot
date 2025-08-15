/**
 * Сущности для работы с задачами
 */

import { VkUser, VkAttachment } from './vkontakte'

// Интерфейс для задачи
export interface Task {
  id: number
  job_id: number
  account_id: number
  owner_id: number
  first_name: string
  last_name: string
  item_id: number
  error_message: string | null
  status: 'queued' | 'done' | 'failed' | ''
  is_cyclic: number
  run_at: string
  created_at: string
  updated_at: string
}

// Интерфейс для деталей задачи
export interface TaskDetails {
  attachments: VkAttachment[]
  likes: {
    can_like: number
    count: number
    user_likes: number
    can_publish: number
    repost_disabled: boolean
  }
  liked_users: VkUser[]
  account_id: number
}

// Интерфейс для статусов задач
export interface TaskStatuses {
  queued: number
  done: number
  failed: number
}

// Интерфейс для циклической задачи
export interface CyclicTask {
  id: number
  account_id: number
  total_task_count: number
  remaining_tasks_count: number
  tasks_per_hour: number
  likes_distribution?: string
  selected_times?: Record<string, boolean[]>
  status: 'active' | 'done' | 'pause'
  started_at: string
  created_at: string
  updated_at: string
  first_name?: string
  last_name?: string
}
