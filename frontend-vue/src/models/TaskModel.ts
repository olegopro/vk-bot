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

export interface VkUser {
  id: number
  first_name: string
  last_name: string
  can_access_closed: boolean
  is_closed: boolean
}

export interface VkAttachment {
  type: 'photo'
  photo: {
    id: number
    album_id: number
    owner_id: number
    date: number
    access_key: string
    post_id: number
    sizes: Array<{
      height: number
      type: string
      width: number
      url: string
    }>
    text: string
    web_view_token: string
    has_tags: boolean
    orig_photo: {
      height: number
      type: string
      url: string
      width: number
    }
  }
}

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

export interface TaskStatuses {
  queued: number
  done: number
  failed: number
}

export interface TasksListData {
  tasks: Task[]
  total: number
  statuses: TaskStatuses
}

export interface CreateCyclicTaskRequest {
  account_id: number
  interval_minutes: number
  max_likes_per_execution: number
  is_active: boolean
}
