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
    import { reactive, computed } from 'vue'

    const days = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс']
    const hours = Array.from({ length: 24 }, (_, i) => i)

    const selectedTimes = reactive(
        days.reduce((acc, day) => {
            acc[day] = Array.from({ length: 24 }, () => false)
            return acc
        }, {})
    )

    // Метод для переключения выбора для всей таблицы
    const toggleAll = () => {
        const allSelected = allSelectedComputed.value

        days.forEach(day => {
            for (let hour = 0; hour < 24; hour++) {
                selectedTimes[day][hour] = !allSelected
            }
        })
    }

    // Вычисляемое свойство для проверки, выбраны ли все чекбоксы
    const allSelectedComputed = computed(() => {
        return days.every(day => selectedTimes[day].every(hour => hour))
    })

    const toggleDay = selectedDay => {
        const isSelected = selectedTimes[selectedDay].includes(true)

        selectedTimes[selectedDay].forEach((_, index) => {
            selectedTimes[selectedDay][index] = !isSelected
        })
    }

    const toggleHour = selectedHour => {
        days.forEach(day => selectedTimes[day][selectedHour] = !selectedTimes[day][selectedHour])
    }
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
                    border-top: none;
                    border-right: 1px solid #ddd;
                    border-bottom: 1px solid #ddd;
                    border-left: 1px solid #ddd;
                    text-transform: uppercase;
                    text-align: center;
                    cursor: pointer;

                    &:first-child {
                        border-left: none;
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
