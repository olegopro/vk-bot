<template>
    <div class="row mb-3 align-items-center">
        <div class="col">
            <h1 class="h2">Статистика</h1>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <Bar
                id="my-chart-id"
                :options="chartOptions"
                :data="chartData"
            />
        </div>
    </div>
</template>

<script>
    import { Bar } from 'vue-chartjs'
    import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js'

    ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)

    export default {
        name: 'BarChart',
        components: { Bar },
        data() {
            return {
                chartData: {
                    labels: ['Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота', 'Воскресенье'],
                    datasets: [{ data: [40, 20, 12, 64, 42, 22, 74] }]
                },
                chartOptions: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                            // position: 'bottom'
                        }
                    }
                }
            }
        }
    }
</script>

<style scoped lang="scss">

</style>
