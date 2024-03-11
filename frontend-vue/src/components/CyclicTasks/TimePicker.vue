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
                        @mouseup="handleMouseUp"
                        @click="toggleCell(day, hour)"
                    >
                        <label :for="`checkbox-${day}-${hour}`" class="custom-checkbox-label">
                            <input class="hidden-checkbox"
                                   type="checkbox"
                                   :id="`checkbox-${day}-${hour}`"
                                   :checked="selectedTimes[day][hour]"
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

    const props = defineProps({ initialSelectedTimes: Object })
    const emits = defineEmits(['update:selectedTimes'])

    const allSelectedComputed = computed(() => days.every(day => selectedTimes[day].every(hour => hour)))

    watch(() => props.initialSelectedTimes, (newValue) => {
            if (newValue && Object.keys(newValue).length !== 0) {
                Object.assign(selectedTimes, newValue)
            }
        },
        { deep: false, immediate: true }
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

    const handleMouseDown = async (event, day, hour) => {
        event.preventDefault()
        isDragging = true
        dragStartState = !selectedTimes[day][hour]
        selectedTimes[day][hour] = dragStartState
    }

    const handleMouseMove = (event, day, hour) => {
        if (isDragging) { // Проверить, начато ли перетаскивание
            selectedTimes[day][hour] = dragStartState // Обновить состояние при перемещении
        }
    }

    const handleMouseUp = () => {
        if (isDragging) {
            isDragging = false // Завершение перетаскивания
        }
    }

    const toggleCell = (day, hour) => {
        if (!isDragging) { // Действовать только если не происходит перетаскивание
            selectedTimes[day][hour] = !selectedTimes[day][hour]
        }
    }

    onMounted(() => {
        console.log('TimePicker onMounted')
    })

    onUnmounted(() => {
        console.log('TimePicker onUnmounted')
    })
</script>

<style scoped lang="scss">
    table {
        width: 100%;
        box-shadow: none;
        border-collapse: collapse;

        thead {
            tr {
                th {
                    position: relative;
                    padding: 8px;
                    background-color: white;
                    border-top: 1px solid #ddd;
                    border-right: 1px solid #ddd;
                    border-bottom: 1px solid #ddd;
                    border-left: 1px solid #ddd;
                    text-transform: uppercase;
                    text-align: center;
                    cursor: pointer;
                    user-select: none;

                    &::before {
                        content: '';
                        position: absolute;
                        top: -1px;
                        left: 0;
                        right: 0;
                        border-top: 1px solid #ddd;
                    }

                    &:first-child {
                        border-top-left-radius: 0;
                        border-left: none;

                        i {
                            display: block;
                            width: 100%;
                            height: 100%;
                        }
                    }

                    &:last-child {
                        border-top-right-radius: 0;
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
