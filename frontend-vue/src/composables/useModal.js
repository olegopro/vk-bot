import { ref, nextTick } from 'vue'
import { Modal } from 'bootstrap'

export function useModal() {
    const modals = ref({})
    const isOpen = ref(false)

    const preparedModal = component => {
        isOpen.value = true
        return component
    }

    // Эта функция будет вызвана после монтирования компонента, чтобы показать модальное окно.
    const showModal = async modalId => {
        await nextTick() // Дожидаемся, когда компонент будет в DOM

        const currentModal = document.getElementById(modalId)

        if (!currentModal) {
            console.error(`Modal element with ID ${modalId} was not found in the DOM.`)
            isOpen.value = false
            return
        }

        if (!modals.value[modalId]) {
            const modalInstance = new Modal(currentModal)

            currentModal.addEventListener('hidden.bs.modal', () => {
                modalInstance.dispose()
                delete modals.value[modalId]
                isOpen.value = false
            }, { once: true })

            modals.value[modalId] = modalInstance
        }

        modals.value[modalId].show()
    }

    const closeModal = (modalId) => {
        if (modals.value[modalId]) {
            modals.value[modalId].hide()
        }
    }

    return { isOpen, preparedModal, showModal, closeModal }
}
