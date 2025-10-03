import { CyclicTask } from '@/types/tasks'

export interface CyclicTasksPaginatedData {
  data: CyclicTask[]
  current_page: number
  last_page: number
  per_page: number
  total: number
}

export interface CyclicTasksListData {
  data: CyclicTask[]
  pagination: {
    total: number
    current_page: number
    last_page: number
    per_page: number
  }
}

// Response типы
export type CyclicTasksListResponse = CyclicTask[]
export type CyclicTaskResponse = CyclicTask
export type CreateCyclicTaskResponse = null
export type EditCyclicTaskResponse = CyclicTask
export type DeleteCyclicTaskResponse = null
export type PauseCyclicTaskResponse = null

// Request типы
export interface CreateCyclicTaskRequest {
  account_id: number
  tasks_per_hour: number
  total_task_count: number
  status: 'active' | 'done' | 'pause'
  selected_times?: Record<string, boolean[]>
}

export interface EditCyclicTaskRequest {
  account_id?: number
  total_task_count?: number
  tasks_per_hour?: number
  status?: 'active' | 'done' | 'pause'
  selected_times?: Record<string, boolean[]>
}
