import { ApiResponseWrapper } from './ApiModel'

export type TaskStatus = 'queued' | 'done' | 'failed' | ''

export interface Task {
  id: number
  job_id: number
  account_id: number
  owner_id: number
  first_name: string
  last_name: string
  item_id: number
  error_message: string | null
  status: TaskStatus
  is_cyclic: number
  run_at: string
  created_at: string
  updated_at: string
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

export interface CreateCyclicTaskRequest {
  account_id: number
  interval_minutes: number
  max_likes_per_execution: number
  is_active: boolean
}
