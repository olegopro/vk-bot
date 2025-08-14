import { ApiResponseWrapper } from './ApiModel'

// Интерфейс для информации о последнем визите пользователя VK
export interface VkLastSeen {
  platform: number // Платформа (1 - мобильная версия, 2 - iPhone, 3 - iPad, 4 - Android, 5 - Windows Phone, 6 - Windows 10, 7 - полная версия сайта)
  time: number // Время последнего визита в формате Unix timestamp
}

// Типы для AccountStore
export interface OwnerData {
  id: number | string
  friends_count?: number
  first_name?: string
  last_name?: string
  screen_name?: string
  photo_50?: string
  photo_100?: string
  photo_200?: string
  last_seen?: VkLastSeen

  [key: string]: any // для дополнительных свойств из API
}

export interface VkUser {
  id: number
  first_name: string
  last_name: string
  screen_name?: string
  photo_50?: string
  photo_100?: string
  photo_200?: string

  [key: string]: any
}

export interface VkGroup {
  id: number
  name: string
  screen_name?: string
  photo_50?: string
  photo_100?: string
  photo_200?: string
  status?: string
  members_count?: number
  city?: {
    title?: string
    [key: string]: any
  }

  [key: string]: any
}

export interface NewsItem {
  post_id: number
  owner_id: number
  from_id: number
  date: number
  text: string
  attachments?: Array<{
    type: string
    photo?: any
    [key: string]: any
  }>

  [key: string]: any
}

export interface NewsFeedResponse {
  items: NewsItem[]
  next_from?: string

  [key: string]: any
}

export type OwnerDataApiResponse = OwnerData

export interface FriendsCountApiResponse {
  response: {
    id: string
    count: number
  }
}
export type NewsFeedApiResponse = ApiResponseWrapper<NewsFeedResponse>

export interface LikeRequest {
  account_id: number | string
  owner_id: number | string
  item_id: number | string
}

export type LikeResponse = ApiResponseWrapper<any>

export interface PostsForLikeRequest {
  account_id: number | string
  task_count: number
}

export type PostsForLikeResponse = ApiResponseWrapper<NewsItem[]>

export interface CreateTasksRequest {
  account_id: number
  domains: string[]
}

export type CreateTasksResponse = ApiResponseWrapper<any>

export interface NewsFeedRequest {
  account_id: string
  start_from: string | null
}

export type GroupDataResponse = ApiResponseWrapper<VkGroup>
