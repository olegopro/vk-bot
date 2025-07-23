import { ApiResponseWrapper } from './ApiModel'

export interface Task {
  id: number
  account_id: number
  status: 'queued' | 'done' | 'failed'
  type: string
  target_id: number
  target_type: string
  created_at: string
  updated_at: string
  error_message?: string
}

export interface TaskDetails extends Task {
  liked_users?: Array<{
    id: number
    first_name: string
    last_name: string
  }>
}

export interface TaskStatuses {
  queued: number
  done: number
  failed: number
}

export interface TasksPaginatedData {
  data: Task[]
  current_page: number
  last_page: number
  per_page: number
  total: number
}

export interface TasksListData {
  tasks: TasksPaginatedData
  total: number
  statuses: TaskStatuses
}

export type TasksListResponse = ApiResponseWrapper<TasksListData>

export type TaskDetailsResponse = ApiResponseWrapper<TaskDetails>

export type TaskCountByStatusResponse = ApiResponseWrapper<TaskStatuses>

export type DeleteTaskResponse = ApiResponseWrapper<null>

export type DeleteLikeResponse = ApiResponseWrapper<null>

export interface CreateTasksRequest {
  account_id?: number
  user_ids?: number[]
  count?: number
}

export interface CreateCyclicTaskRequest {
  account_id: number
  interval_minutes: number
  max_likes_per_execution: number
  is_active: boolean
}

export type TaskStatus = 'queued' | 'done' | 'failed' | ''
