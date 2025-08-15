/**
 * Сущности для работы с VK API
 */

// Интерфейс для города
export interface VkCity {
  id: number
  title: string
  area?: string
  region?: string
}

// Интерфейс для элемента новостной ленты
export interface VkNewsFeedItem {
  post_id: number
  owner_id: number
  from_id: number
  date: number
  text: string
  attachments?: Array<{
    type: string
    photo: string
    [key: string]: string
  }>
}

// Интерфейс для пользователя VK
export interface VkUser {
  id: number
  first_name: string
  last_name: string
  screen_name?: string
  domain?: string
  photo_50?: string
  photo_100?: string
  photo_200?: string
  city?: VkCity
  last_seen?: {
    time: number
    platform: number
  }
  friends_count?: number
  followers_count?: number
  name?: string
  online?: number
  status?: string
  sex?: number
  country?: {
    title?: string
  }
  bdate?: string
}

// Интерфейс для информации о последнем визите пользователя VK
export interface VkLastSeen {
  platform: number // Платформа (1 - мобильная версия, 2 - iPhone, 3 - iPad, 4 - Android, 5 - Windows Phone, 6 - Windows 10, 7 - полная версия сайта)
  time: number // Время последнего визита в формате Unix timestamp
}

// Интерфейс для группы VK
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
  }
}

// Интерфейс для вложения VK
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
