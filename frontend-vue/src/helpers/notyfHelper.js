import { Notyf } from 'notyf'

const notyf = new Notyf()

export function showNotification(message, type) {
    if (type === 'success') {
        notyf.success(message)
    } else if (type === 'error') {
        notyf.error(message)
    }
    // и так далее для других типов
}

export function showSuccessNotification(message) {
    notyf.success(message)
}

export function showErrorNotification(message) {
    notyf.error(message)
}
