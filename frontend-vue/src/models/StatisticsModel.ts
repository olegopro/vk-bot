import type { ApiResponseWrapper } from './ApiModel'

export interface WeeklyTaskStats {
  [key: string]: number
}

export type StatisticsResponse = ApiResponseWrapper<WeeklyTaskStats>
