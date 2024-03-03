<template>
    <div>
        <table>
            <thead>
                <tr>
                    <th><i class="bi bi-calendar-check fs-5" @click="toggleAll" /></th>
                    <th v-for="day in days" :key="day" @click="toggleDay(day)">{{ day }}</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="hour in hours" :key="hour">
                    <td @click="toggleHour(hour)">{{ hour }}:00</td>
                    <td :class="{'active-cell': selectedTimes[day][hour], 'inactive-cell': !selectedTimes[day][hour]}"
                        v-for="day in days"
                        :key="day"
                        @mousedown="handleMouseDown($event, day, hour)"
                        @mousemove="handleMouseMove($event, day, hour)"
                    >
                        <label :for="`checkbox-${day}-${hour}`" class="custom-checkbox-label">
                            <input class="hidden-checkbox"
                                   type="checkbox"
                                   :id="`checkbox-${day}-${hour}`"
                                   v-model="selectedTimes[day][hour]"
                            />
                            <span class="checkmark" />
                        </label>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup>
    import { reactive, computed, onMounted, onUnmounted, defineEmits, defineProps, watch } from 'vue'

    const days = ['пн', 'вт', 'ср', 'чт', 'пт', 'сб', 'вс']
    const hours = Array.from({ length: 24 }, (_, i) => i)

    const selectedTimes = reactive(
        days.reduce((acc, day) => {
            acc[day] = Array.from({ length: 24 }, () => false)
            return acc
        }, {})
    )

    let isDragging = false
    let dragStartState = false
    let dragTimeout = null

    const props = defineProps({ initialSelectedTimes: Object })
    const emits = defineEmits(['update:selectedTimes'])

    const allSelectedComputed = computed(() => days.every(day => selectedTimes[day].every(hour => hour)))

    watch(() => props.initialSelectedTimes, (newValue) => {
            if (newValue) Object.assign(selectedTimes, newValue)
        },
        { deep: true, immediate: true }
    )

    watch(() => selectedTimes, () => submitSelectedTimes(), { deep: true })
    const submitSelectedTimes = () => emits('update:selectedTimes', selectedTimes)

    const toggleAll = () => {
        const allSelected = allSelectedComputed.value

        days.forEach(day => {
            for (let hour = 0; hour < 24; hour++) {
                selectedTimes[day][hour] = !allSelected
            }
        })
    }

    const toggleDay = selectedDay => {
        // Проверяем, все ли часы в выбранном дне уже true
        const allHoursTrue = selectedTimes[selectedDay].every(hour => hour === true)

        // Если все часы true, инвертируем. В противном случае, устанавливаем все в true.
        selectedTimes[selectedDay].forEach((_, index) => {
            selectedTimes[selectedDay][index] = !allHoursTrue
        })
    }

    const toggleHour = selectedHour => {
        // Проверяем, выбран ли данный час во всех днях
        const isHourSelectedInAllDays = days.every(day => selectedTimes[day][selectedHour])

        // Если выбран во всех, инвертируем. В противном случае, устанавливаем в true.
        days.forEach(day => {
            selectedTimes[day][selectedHour] = !isHourSelectedInAllDays
        })
    }

    const handleMouseDown = (event, day, hour) => {
        event.preventDefault() // Предотвратить выделение текста при перетаскивании
        dragStartState = !selectedTimes[day][hour]

        // Установка задержки для определения перетаскивания
        dragTimeout = setTimeout(() => {
            isDragging = true
            selectedTimes[day][hour] = dragStartState
        }, 30) // Задержка в 30 мс должна быть достаточной для определения перетаскивания
    }

    const handleMouseMove = (event, day, hour) => {
        if (!isDragging) return
        selectedTimes[day][hour] = dragStartState
    }

    const handleMouseUp = () => {
        if (!isDragging && dragTimeout) {
            // Это был клик, меняем состояние ячейки
            clearTimeout(dragTimeout)
            dragTimeout = null
        }
        if (isDragging) {
            // Завершение процесса перетаскивания
            isDragging = false
        }
    }

    onMounted(() => document.addEventListener('mouseup', handleMouseUp))
    onUnmounted(() => document.removeEventListener('mouseup', handleMouseUp))
</script>

<style scoped lang="scss">
    table {
        width: 100%;
        box-shadow: none;
        border-collapse: collapse;

        thead {
            tr {
                th {
                    padding: 8px;
                    border-top: 1px solid #ddd;
                    border-right: 1px solid #ddd;
                    border-bottom: 1px solid #ddd;
                    border-left: 1px solid #ddd;
                    text-transform: uppercase;
                    text-align: center;
                    cursor: pointer;
                    user-select: none;

                    &:first-child {
                        border-left: none;

                        i {
                            display: block;
                            width: 100%;
                            height: 100%;
                        }
                    }

                    &:last-child {
                        border-right: none;
                    }
                }
            }
        }

        tbody {
            tr {
                td {
                    position: relative;
                    padding: 8px;
                    border-top: 1px solid #ddd;
                    border-right: 1px solid #ddd;
                    border-bottom: 1px solid #ddd;
                    border-left: 1px solid #ddd;
                    text-align: center;

                    &:first-child {
                        border-left: none;
                        cursor: pointer;
                        user-select: none;
                    }

                    &:last-child {
                        border-right: none;
                    }
                }

                &:last-child {
                    td {
                        border-bottom: none;
                    }
                }

                .custom-checkbox-label {
                    display: block;
                    width: 100%;
                    height: 100%;
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    margin: 0;
                    cursor: pointer;

                    .hidden-checkbox {
                        position: absolute;
                        width: 0;
                        height: 0;
                        opacity: 0;
                    }

                    .checkmark {
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        position: absolute;
                        top: 0;
                        left: 0;
                        right: 0;
                        bottom: 0;
                        background-color: #eee; // Цвет фона для неотмеченного состояния

                        &:after {
                            content: "";
                            display: none; // Дополнительные стили для кастомной галочки
                            position: absolute;
                        }
                    }

                    input:checked {
                        & ~ .checkmark {
                            background-color: #2196F3; // Цвет фона для отмеченного состояния

                            &:after {
                                display: block; // Стили для отображения галочки
                            }
                        }
                    }
                }
            }
        }
    }
</style>
