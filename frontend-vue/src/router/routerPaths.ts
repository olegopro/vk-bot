import { TaskStatus } from '@/models/TaskModel'

/** Функция для создания типизированных роутов с параметрами */
// eslint-disable-next-line @typescript-eslint/no-explicit-any
export const createTypedRoute = <T extends Record<string, any>>(path: string) => {
  return (params?: Partial<T>): string => {
    if (!params) return path

    return Object.entries(params).reduce((currentPath, [key, value]) => {
      return value !== undefined && value !== ''
        ? currentPath.replace(`:${key}?`, String(value))
        : currentPath
    }, path).replace(/\/:[^/]*\?/g, '')
  }
}

/** Типизированные интерфейсы для параметров */
export interface TasksRouteParams {
  status?: TaskStatus
  accountId?: string
}

export interface AccountRouteParams {
  id: string
}

/** Централизованные пути */
export default {
  home: '/',
  tasks: createTypedRoute<TasksRouteParams>('/tasks/:status?/:accountId?'),
  cyclicTasks: createTypedRoute<TasksRouteParams>('/cyclic-tasks/:status?/:accountId?'),
  account: createTypedRoute<AccountRouteParams>('/account/:id'),
  statistics: '/statistics',
  settings: '/settings'
} as const
