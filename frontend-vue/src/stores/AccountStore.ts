import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from '@/helpers/axiosConfig'
import { showErrorNotification, showSuccessNotification } from '@/helpers/notyfHelper'
import useApi from '@/composables/useApi'
import type { Nullable } from '@/types'
import type {
  VkUser,
  VkGroup,
  NewsItem,
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
  // Состояние
  const account = ref<any[]>([])
  const accountFollowers = ref<Record<string, VkUser[]>>({})
  const accountFriends = ref<Record<string, VkUser[]>>({})
  const accountFriendsCount = ref<Record<string, number>>({})
  const accountNewsFeed = ref<NewsItem[]>([])
  const nextFrom = ref<Nullable<string>>(null)
  const previousNextFrom = ref<Nullable<string>>(null)
  const isOwnerDataLoading = ref<any>(null)
  const isLoadingFeed = ref<boolean>(false)

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

    const { accountId, startFrom } = parameters

    if (startFrom !== null && startFrom === previousNextFrom.value) {
      return { data: null, success: true, message: 'Данные уже загружены' }
    }

    previousNextFrom.value = startFrom
    isLoadingFeed.value = true

    try {
      const response = await axios.post<NewsFeedApiResponse>('account/newsfeed', {
        account_id: accountId,
        start_from: startFrom
      })

      const { data: { response: feedResponse }, message } = response.data

      // фильтрация массива items, оставляя только те элементы, у которых первое вложение имеет тип 'photo'
      const result = feedResponse.items.filter(item => item.attachments?.[0]?.type === 'photo')

      // сохранение маркера для следующего запроса новостей из response
      nextFrom.value = feedResponse.next_from || null

      // если начальный маркер равен null, очистить ленту новостей
      if (startFrom === null) accountNewsFeed.value = []

      // добавление отфильтрованных новостей к текущему списку новостей в аккаунте
      accountNewsFeed.value = [...accountNewsFeed.value, ...result]

      showSuccessNotification(message)
      return response.data
    } catch (error) {
      // повторный запрос новостей для аккаунта в случае ошибки
      showErrorNotification('Новые данные ленты не получены')
      throw error
    } finally {
      // Установка флага загрузки в false, значит загрузка завершена
      isLoadingFeed.value = false
    }
  })

  /**
   * Добавляет лайк к посту
   */
  const addLike = useApi(async (parameters?: {
    accountId: number | string;
    ownerId: number | string;
    itemId: number | string
  }) => {
    if (!parameters) throw new Error('Не указаны параметры')

    const request: LikeRequest = {
      account_id: parameters.accountId,
      owner_id: parameters.ownerId,
      item_id: parameters.itemId
    }

    const response = await axios.post<LikeResponse>('account/like', request)
    showSuccessNotification(response.data.message)

    return response.data
  })

  /**
   * Добавляет посты для лайков
   */
  const addPostsToLike = useApi(async (parameters?: { accountId: number | string; taskCount: number }) => {
    if (!parameters) throw new Error('Не указаны параметры')

    const request: PostsForLikeRequest = {
      account_id: parameters.accountId,
      task_count: parameters.taskCount
    }

    const response = await axios.post<PostsForLikeResponse>('tasks/get-posts-for-like', request)
    accountNewsFeed.value = [...accountNewsFeed.value, ...response.data.data]
    showSuccessNotification(response.data.message)

    return response.data
  })

  /**
   * Получает детали аккаунта
   */
  const getAccountDetails = useApi(async (parameters?: { ownerId: number | string }) => {
    if (!parameters) throw new Error('Не указан ID владельца')

    const response = await axios.get(`account/${parameters.ownerId}`)
    return response.data
  })

  /**
   * Создает задачи на лайки для указанных пользователей
   */
  const createTasksForUsers = useApi(async (parameters?: { accountId: number; domains: string[] }) => {
    if (!parameters) throw new Error('Не указаны параметры')

    const request: CreateTasksRequest = {
      account_id: parameters.accountId,
      domains: parameters.domains
    }

    const response = await axios.post<CreateTasksResponse>('tasks/create-for-users', request)
    showSuccessNotification(response.data.message)

    return response.data
  })

  // Геттеры
  const getAccountById = computed(() => (id: number | string) => account.value.find(account => account.id === Math.abs(Number(id))))

  return {
    // Состояние
    account,
    accountFollowers,
    accountFriends,
    accountFriendsCount,
    accountNewsFeed,
    nextFrom,
    previousNextFrom,
    isOwnerDataLoading,
    isLoadingFeed,

    // Методы
    fetchOwnerData,
    fetchAccountFollowers,
    fetchAccountFriends,
    fetchAccountFriendsCount,
    fetchAccountNewsFeed,
    addLike,
    addPostsToLike,
    fetchGroupData,
    getAccountDetails,
    createTasksForUsers,

    // Геттеры
    getAccountById
  }
})
