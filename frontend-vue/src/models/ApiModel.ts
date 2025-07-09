export interface ApiResponseWrapper<T> {
  success: boolean
  data: T
  message: string
}
