/**
 * Модель для фильтров и связанных данных
 */

// Интерфейс для города
export interface City {
  id: number
  title: string
  area?: string
  region?: string
}

// Интерфейс для пользователя
export interface User {
  id: number
  first_name: string
  last_name: string
  screen_name?: string
  domain?: string
  photo_50?: string
  photo_100?: string
  city?: City
  last_seen?: {
    time: number
    platform: number
  }
  friends_count?: number
  followers_count?: number
}

// Интерфейс для данных поиска городов
export interface CitySearchData {
  items: City[]
  count: number
}

// Интерфейс для данных пользователей по городу
export interface UsersByCityData {
  domains: string[]
  users?: User[]
  count?: number
}

// Типы ответов API
export type CitySearchResponse = {
  success: boolean
  data: CitySearchData
  message: string
}

export type UsersByCityResponse = {
  success: boolean
  data: UsersByCityData
  message: string
}
