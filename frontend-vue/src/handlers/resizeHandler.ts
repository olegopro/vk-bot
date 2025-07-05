import { debounce } from 'lodash'
import { ref } from 'vue'

export const vh = ref<number>()

export const setVhVariable = () => {
    document.addEventListener('DOMContentLoaded', function () {
        vh.value = window.innerHeight * 0.01
        document.documentElement.style.setProperty('--vh', `${vh.value}px`)
    })
}

export const initResizeHandler = () => {
    // Вызываю функцию один раз при загрузке страницы
    setVhVariable()

    // Обновление значение при изменении размеров окна
    const debouncedSetVhVariable = debounce(setVhVariable, 500)
    window.addEventListener('resize', debouncedSetVhVariable)
}
