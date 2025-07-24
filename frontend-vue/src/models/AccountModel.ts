export interface AddAccountResponse {
  account_id: number
  access_token: string
  screen_name: string
  first_name: string
  last_name: string
  bdate?: string
}

export interface AddAccountRequest {
  access_token: string
}
