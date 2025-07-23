import { ApiResponseWrapper } from './ApiModel'

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

export type OwnerDataResponse = ApiResponseWrapper<{ response: OwnerData[] }>

export type FriendsCountResponse = ApiResponseWrapper<{ response: { count: number } }>

export type FollowersResponse = ApiResponseWrapper<{ response: { items: VkUser[] } }>

export type FriendsResponse = ApiResponseWrapper<{ response: { items: VkUser[] } }>

export type NewsFeedApiResponse = ApiResponseWrapper<{ response: NewsFeedResponse }>

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

export type GroupDataResponse = ApiResponseWrapper<{ response: VkGroup[] }>
