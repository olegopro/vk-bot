import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from '@/helpers/axiosConfig'
import { showErrorNotification, showSuccessNotification } from '@/helpers/notyfHelper'
import useApi from '@/composables/useApi'
import type { Nullable } from '@/types'
import type {
  OwnerData,
  VkUser,
  VkGroup,
  NewsItem,
  OwnerDataResponse,
  FollowersResponse,
  FriendsResponse,
  FriendsCountResponse,
  NewsFeedApiResponse,
  LikeRequest,
  LikeResponse,
  PostsForLikeRequest,
  PostsForLikeResponse,
  CreateTasksRequest,
  CreateTasksResponse,
  GroupDataResponse
} from '@/models/AccountsModel'

export const useAccountStore = defineStore('account', () => {
  // Состояние
  const account = ref<any[]>([])
  const ownerData = ref<OwnerData[]>([])
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
  const fetchOwnerData = useApi(async (parameters?: { accountId: number; ownerId: number; taskId?: any }) => {
    if (!parameters) throw new Error('Не указаны параметры')

    const { accountId, ownerId, taskId = null } = parameters
    isOwnerDataLoading.value = taskId

    // Поиск владельца по ID в массиве ownerData
    const existingOwnerData = ownerData.value.find(owner => owner.id === ownerId)

    // Если данные о владельце уже есть, немедленно возвращаем эти данные
    if (existingOwnerData) {
      isOwnerDataLoading.value = null
      return { data: existingOwnerData, success: true, message: 'Данные уже загружены' }
    }

    // Если данных о владельце нет, выполняем запросы
    const responses = await Promise.all([
      axios.get<OwnerDataResponse>(`account/data/${ownerId}`),
      axios.get<FriendsCountResponse>(`account/friends/count/${accountId}/${ownerId}`)
    ])

    const ownerDataResponse = responses[0]
    const friendsCountResponse = responses[1]

    const accountData = ownerDataResponse.data.data.response[0]
    const friendsCount = friendsCountResponse.data.data.response.count

    // Объединяем полученные данные
    const combinedData = { ...accountData, friends_count: friendsCount }

    // Добавляем новые данные о владельце в массив ownerData
    ownerData.value.push(combinedData)

    // Отображаем уведомления об успехе
    showSuccessNotification(ownerDataResponse.data.message)
    showSuccessNotification(friendsCountResponse.data.message)

    isOwnerDataLoading.value = null
    return { data: combinedData, success: true, message: 'Данные владельца загружены' }
  })

  /**
   * Получает подписчиков аккаунта
   */
  const fetchAccountFollowers = useApi(async (parameters?: { accountId: string }) => {
    if (!parameters) throw new Error('Не указан ID аккаунта')

    const response = await axios.get<FollowersResponse>(`account/followers/${parameters.accountId}`)
    accountFollowers.value[parameters.accountId] = response.data.data.response.items
    showSuccessNotification(response.data.message)

    return response.data
  })

  /**
   * Получает друзей аккаунта
   */
  const fetchAccountFriends = useApi(async (parameters?: { accountId: string }) => {
    if (!parameters) throw new Error('Не указан ID аккаунта')

    const response = await axios.get<FriendsResponse>(`account/friends/${parameters.accountId}`)
    accountFriends.value[parameters.accountId] = response.data.data.response.items
    showSuccessNotification(response.data.message)

    return response.data
  })

  /**
   * Получает количество друзей
   */
  const fetchAccountFriendsCount = useApi(async (parameters?: { id: string }) => {
    if (!parameters) throw new Error('Не указан ID')

    const response = await axios.get<FriendsCountResponse>(`account/friends/count/${parameters.id}`)
    accountFriendsCount.value[parameters.id] = response.data.data.response.count
    showSuccessNotification(response.data.message)

    return response.data
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
      // установка флага загрузки в false, т.е. загрузка завершена
      isLoadingFeed.value = false
    }
  })

  /**
   * Добавляет лайк к посту
   */
  const addLike = useApi(async (parameters?: { accountId: number | string; ownerId: number | string; itemId: number | string }) => {
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
   * Получает screen name по ID (TODO: Нигде не используется)
   */
  const getScreenNameById = useApi(async (parameters?: { accountId: number | string }) => {
    if (!parameters) throw new Error('Не указан ID аккаунта')

    const response = await axios.post('account/get-screen-name-by-id', {
      user_id: parameters.accountId
    })

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
   * Получает данные группы
   */
  const fetchGroupData = useApi(async (parameters?: { groupId: number | string }) => {
    if (!parameters) throw new Error('Не указан ID группы')

    const { groupId } = parameters

    // Поиск владельца по ID в массиве ownerData
    const existingGroupData = ownerData.value.find(owner => owner.id === Math.abs(Number(groupId)))
    console.log('existingGroupData', existingGroupData)

    // Если данные о владельце уже есть, немедленно возвращаем эти данные
    if (existingGroupData) {
      return { data: existingGroupData, success: true, message: 'Данные группы уже загружены' }
    }

    const response = await axios.get<GroupDataResponse>(`group/data/${Math.abs(Number(groupId))}`)
    addOwnerData(response.data.data.response[0])
    showSuccessNotification(response.data.message)

    return response.data
  })

  /**
   * Добавляет или обновляет данные владельца
   */
  const addOwnerData = (accountData: OwnerData) => {
    const index = ownerData.value.findIndex((item) => item.id === accountData.id)

    if (index !== -1) {
      // Объект с таким же идентификатором уже существует, обновляем его
      ownerData.value[index] = { ...ownerData.value[index], ...accountData }
    } else {
      // Добавляем новый объект в массив
      ownerData.value.push(accountData)
    }
  }

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
  const getAccountFollowers = computed(() => (accountId: string) => accountFollowers.value[accountId] || [])
  const getAccountFriends = computed(() => (accountId: string) => accountFriends.value[accountId] || [])
  const getOwnerDataById = computed(() => (id: number | string) => ownerData.value.find(user => user.id === Math.abs(Number(id))))
  const getAccountById = computed(() => (id: number | string) => account.value.find(account => account.id === Math.abs(Number(id))))

  return {
    // Состояние
    account,
    ownerData,
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
    getScreenNameById,
    addPostsToLike,
    fetchGroupData,
    addOwnerData,
    getAccountDetails,
    createTasksForUsers,

    // Геттеры
    getAccountFollowers,
    getAccountFriends,
    getOwnerDataById,
    getAccountById
  }
})
