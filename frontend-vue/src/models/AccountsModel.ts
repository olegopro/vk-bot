import { VkUser, VkGroup, NewsItem } from '../types/vkontakte'

export interface NewsFeedResponse {
  items: NewsItem[]
  next_from?: string

  [key: string]: any
}

export interface FriendsCountApiResponse {
  response: {
    id: string
    count: number
  }
}

// Response интерфейсы
export type OwnerDataResponse = VkUser

export type GroupDataResponse = VkGroup

export type AccountFollowersResponse = VkUser[]

export type AccountFriendsResponse = VkUser

export type PostsForLikeResponse = NewsItem[]

export interface LikeResponse {
  success: boolean
  likes?: number
}

export interface CreateTasksResponse {
  created_count: number
  success: boolean
}

// Request интерфейсы
export interface LikeRequest {
  account_id: number | string
  owner_id: number | string
  item_id: number | string
}

export interface PostsForLikeRequest {
  account_id: number | string
  task_count: number
}

export interface CreateTasksRequest {
  account_id: number
  domains: string[]
}

export interface NewsFeedRequest {
  account_id: string
  start_from: string | null
}
