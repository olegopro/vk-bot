import { Notyf } from 'notyf'

type NotificationType = 'success' | 'error'

const notyf = new Notyf({
    duration: 3000
    // другие опции
})

export const showNotification = (message: string, type: NotificationType) => {
    switch (type) {
        case 'success':
            notyf.success(message)
            break
        case 'error':
            notyf.error(message)
            break
        // и так далее для других типов
    }
}

export const showSuccessNotification = (message: string) => notyf.success(message)
export const showErrorNotification = (message: string) => notyf.error(message)
