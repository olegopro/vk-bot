import { Task, TaskStatuses, TaskDetails } from '@/types/tasks'

export type TaskStatus = 'queued' | 'done' | 'failed' | ''

export interface TasksListData {
  tasks: Task[]
  total: number
  statuses: TaskStatuses
}

// Response типы
export type TasksListResponse = TasksListData
export type TaskDetailsResponse = TaskDetails
export type DeleteLikeResponse = null
export type DeleteTaskResponse = null
export type DeleteAllTasksResponse = null

// Request типы
export interface CreateCyclicTaskRequest {
  account_id: number
  interval_minutes: number
  max_likes_per_execution: number
  is_active: boolean
}
