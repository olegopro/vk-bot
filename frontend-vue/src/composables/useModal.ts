import { ref, Ref, nextTick } from 'vue'
import { Modal } from 'bootstrap'

// Определение типа для модальных окон
interface Modals {
    [key: string]: Modal | undefined; // Интерфейс для объекта, хранящего модальные окна
}

export function useModal() {
    // Создаем реактивные ссылки для хранения состояния модальных окон и их видимости
    const modal: Ref<Modals> = ref<Modals>({})
    const isOpen: Ref<boolean> = ref<boolean>(false)

    // Функция для подготовки модального окна к показу, устанавливает isOpen в true
    const preparedModal = <T>(component: T): T => {
        isOpen.value = true // Отмечаем, что модальное окно готово к показу
        return component // Возвращаем компонент
    }

    // Асинхронная функция для отображения модального окна
    const showModal = async (modalId: string): Promise<void> => {
        await nextTick() // Ждем следующего "тика" Vue, чтобы убедиться, что DOM обновлен

        // Получаем элемент модального окна по ID
        const currentModal = document.getElementById(modalId)

        // Если элемент не найден, выводим ошибку и выходим из функции
        if (!currentModal) {
            console.error(`Модальное окно с ID ${modalId} не найдено в DOM дереве.`)
            isOpen.value = false // Устанавливаем состояние видимости в false
            return
        }

        // Если модального окна нет в нашем реактивном объекте modal, создаем его
        if (!modal.value[modalId]) {
            const modalInstance = new Modal(currentModal) // Создаем экземпляр модального окна Bootstrap

            // Устанавливаем слушатель события 'hidden' для очистки и удаления модального окна
            currentModal.addEventListener('hidden.bs.modal', () => {
                modalInstance.dispose() // Удаляем модальное окно
                delete modal.value[modalId] // Удаляем запись из объекта modals
                isOpen.value = false // Устанавливаем состояние видимости в false
            }, { once: true })

            modal.value[modalId] = modalInstance // Сохраняем экземпляр модального окна в объекте modals
        }

        modal.value[modalId]?.show() // Показываем модальное окно, если оно есть
    }

    // Функция для закрытия модального окна
    const closeModal = (modalId: string): void => {
        const modalToClose = modal.value[modalId] // Получаем модальное окно по ID
        modalToClose?.hide() // Если модальное окно найдено, скрываем его
    }

    // Возвращаем функции и состояния для использования во вне
    return { isOpen, preparedModal, showModal, closeModal }
}
