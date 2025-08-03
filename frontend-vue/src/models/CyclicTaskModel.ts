export interface CyclicTask {
  id: number
  account_id: number
  total_task_count: number
  remaining_tasks_count: number
  tasks_per_hour: number
  likes_distribution?: string
  selected_times?: Record<string, boolean[]>
  status: 'active' | 'done' | 'pause'
  started_at?: string
  created_at: string
  updated_at: string
  first_name?: string
  last_name?: string
}

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
