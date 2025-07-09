import { ApiResponseWrapper } from './ApiModel'

export interface Account {
  account_id: number
  access_token: string
  screen_name: string
  first_name: string
  last_name: string
  bdate?: string
}

export interface AccountListResponse extends ApiResponseWrapper<Account[]> {
  data: Account[]
}

export interface AccountResponse extends ApiResponseWrapper<Account> {
  data: Account
}

export interface DeleteAccountResponse extends ApiResponseWrapper<null> {
  data: null
}

export interface AddAccountRequest {
  access_token: string
}
