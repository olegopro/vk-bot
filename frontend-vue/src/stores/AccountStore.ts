import { defineStore } from 'pinia'
import axios from '@/helpers/axiosConfig'
import useApi from '@/composables/useApi'
import type { Nullable } from '@/types'
import type {
  VkUser,
  VkGroup,
  OwnerDataApiResponse,
  FriendsCountApiResponse,
  NewsFeedApiResponse,
  LikeRequest,
  LikeResponse,
  PostsForLikeRequest,
  PostsForLikeResponse,
  CreateTasksRequest,
  CreateTasksResponse
} from '@/models/AccountsModel'
import type { ApiResponseWrapper } from '@/models/ApiModel'

export const useAccountStore = defineStore('account', () => {
  /**
   * Получает данные владельца аккаунта
   */
  const fetchOwnerData = useApi(async (parameters?: { ownerId: number }) => {
    if (!parameters) throw new Error('Не указаны параметры')
    return (await (axios.get<ApiResponseWrapper<OwnerDataApiResponse>>(`account/data/${parameters.ownerId}`))).data
  })

  /**
   * Получает данные группы
   */
  const fetchGroupData = useApi(async (parameters?: { groupId: number | string }) => {
    if (!parameters) throw new Error('Не указан ID группы')
    return (await axios.get<ApiResponseWrapper<VkGroup>>(`group/data/${Math.abs(Number(parameters.groupId))}`)).data
  })

  /**
   * Получает подписчиков аккаунта
   */
  const fetchAccountFollowers = useApi(async (parameters?: { accountId: number }) => {
    if (!parameters) throw new Error('Не указан ID аккаунта')
    return (await axios.get<ApiResponseWrapper<VkUser[]>>(`account/followers/${parameters.accountId}`)).data
  })

  /**
   * Получает друзей аккаунта
   */
  const fetchAccountFriends = useApi(async (parameters?: { accountId: number }) => {
    if (!parameters) throw new Error('Не указан ID аккаунта')
    return (await axios.get<ApiResponseWrapper<VkUser>>(`account/friends/${parameters.accountId}`)).data
  })

  /**
   * Получает количество друзей
   */
  const fetchAccountFriendsCount = useApi(async (parameters?: { id: string }) => {
    if (!parameters) throw new Error('Не указан ID')
    return (await axios.get<ApiResponseWrapper<FriendsCountApiResponse>>(`account/friends/count/${parameters.id}`)).data
  })

  /**
   * Получает новостную ленту аккаунта
   */
  const fetchAccountNewsFeed = useApi(async (parameters?: { accountId: string; startFrom: Nullable<string> }) => {
    if (!parameters) throw new Error('Не указаны параметры')
    return (await axios.post<NewsFeedApiResponse>('account/newsfeed', {
      account_id: parameters.accountId,
      start_from: parameters.startFrom
    })).data
  })

  /**
   * Добавляет лайк к посту
   */
  const addLike = useApi(async (parameters?: { likeData: LikeRequest }) => {
    if (!parameters) throw new Error('Не указаны параметры для добавления лайка')
    return (await axios.post<LikeResponse>('account/like', parameters.likeData)).data
  })

  /**
   * Добавляет посты для лайков
   */
  const addPostsToLike = useApi(async (parameters?: { postsData: PostsForLikeRequest }) => {
    if (!parameters) throw new Error('Не указаны параметры для добавления постов')
    return (await axios.post<PostsForLikeResponse>('tasks/get-posts-for-like', parameters.postsData)).data
  })

  /**
   * Создает задачи на лайки для указанных пользователей
   */
  const createTasksForUsers = useApi(async (parameters?: { tasksData: CreateTasksRequest }) => {
    if (!parameters) throw new Error('Не указаны параметры для создания задач')
    return (await axios.post<CreateTasksResponse>('tasks/create-for-users', parameters.tasksData)).data
  })

  /**
   * Создает задачи на лайки для пользователей из выбранного города
   */
  const createTasksForCity = useApi(async (parameters?: { cityData: { account_id: number; city_id: number; count?: number } }) => {
    if (!parameters) throw new Error('Не указаны параметры для создания задач по городу')
    return (await axios.post<CreateTasksResponse>('tasks/create-for-city', parameters.cityData)).data
  })

  return {
    // Методы
    fetchOwnerData,
    fetchAccountFollowers,
    fetchAccountFriends,
    fetchAccountFriendsCount,
    fetchAccountNewsFeed,
    addLike,
    addPostsToLike,
    fetchGroupData,
    createTasksForUsers,
    createTasksForCity
  }
})
