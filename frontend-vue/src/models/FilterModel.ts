/**
 * Модель для фильтров и связанных данных
 */

import { City, VkUser as User } from '../types/vkontakte'

// Response типы
export interface CitySearchResponse {
  items: City[]
  count: number
}

export interface UsersByCityResponse {
  domains: string[]
  users?: User[]
  count?: number
}

// Request типы
export interface CitySearchRequest {
  q: string
  country_id?: number
  count?: number
}

export interface UsersByCityRequest {
  account_id: number
  city_id: number
  count?: number
}
