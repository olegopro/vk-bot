import { ref, nextTick, ComponentInternalInstance, type ComponentPublicInstance, Component } from 'vue'
import { Modal } from 'bootstrap'
import { Nullable, ComponentProps } from '@/types'
import { showErrorNotification } from '@/helpers/notyfHelper'
import type { ModalProps } from '@/types'

// Определение типа для модальных окон
interface Modals {
  [key: string]: Modal // Интерфейс для объекта, хранящего модальные окна
}

// Глобальное состояние модальных окон
const modals = ref<Modals>({})
const isOpen = ref<boolean>(false)
const currentComponent = ref<Nullable<Component>>(null)
const currentProps = ref<Nullable<ModalProps>>(null)
const GlobalModalRef = ref<Nullable<ComponentInternalInstance>>(null)

export function useModal() {
  const showModal = async <T extends Component>(component: T, props?: ComponentProps<T>): Promise<void> => {
  // Устанавливаем текущий компонент и пропсы
    currentComponent.value = component
    currentProps.value = props ?? null
    isOpen.value = true

    await nextTick() // Ждем следующего "тика" Vue

    // Получаем компонент через ref
    const currentModal = (GlobalModalRef.value?.refs?.modalComponentRef as ComponentPublicInstance)?.$el as Nullable<HTMLElement>

    if (!currentModal) {
      showErrorNotification('Не удалось получить DOM элемент модального компонента')
      isOpen.value = false
      return
    }

    // Используем ID элемента модального окна как ключ для экземпляра модального окна
    const modalKey = currentModal.id

    // Если модального окна нет в нашем реактивном объекте modal, создаем его
    if (!modals.value[modalKey]) {
      const modalInstance = new Modal(currentModal)

      // Устанавливаем слушатель события 'hidden' для очистки
      currentModal.addEventListener('hidden.bs.modal', () => {
        isOpen.value = false
        currentComponent.value = null
        currentProps.value = null
        delete modals.value[modalKey]
      })

      modals.value[modalKey] = modalInstance
    }

    // Показываем модальное окно
    modals.value[modalKey].show()
  }

  // Функция для закрытия модального окна
  const closeModal = (modalId?: string) => {
    console.log(modalId)
    if (!modalId) return showErrorNotification('Не удалось определить идентификатор модального окна')

    const modalToClose = modals.value[modalId] // Получаем модальное окно по ID
    modalToClose?.hide() // Если модальное окно найдено, скрываем его
  }

  // Функция для регистрации ModalComponent ref
  const setGlobalModalRef = (globalModalRef: Nullable<ComponentInternalInstance>): void => {
    GlobalModalRef.value = globalModalRef
  }

  return {
    isOpen,
    currentComponent,
    currentProps,
    showModal,
    closeModal,
    setGlobalModalRef
  }
}
