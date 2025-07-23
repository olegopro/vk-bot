<script setup lang="ts">
  import { ref, computed, onMounted } from 'vue'
  import { Bar } from 'vue-chartjs'
  import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js'
  import ChartDataLabels from 'chartjs-plugin-datalabels'
  import { useStatisticsStore } from '@/stores/StatisticsStore'

  const statisticsStore = useStatisticsStore()

  ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ChartDataLabels)

  const chartData = computed(() => {
    if (Object.keys(statisticsStore.weeklyTaskStats).length === 0) {
      // Возвращаем пустую структуру данных, если ещё не загружены
      return { labels: [], datasets: [] }
    }

    return {
      labels: Object.keys(statisticsStore.weeklyTaskStats),

      datasets: [
        {
          data: Object.values(statisticsStore.weeklyTaskStats),
          backgroundColor: [
            'rgba(255, 99, 132, 0.2)',
            'rgba(54, 162, 235, 0.2)',
            'rgba(255, 206, 86, 0.2)',
            'rgba(75, 192, 192, 0.2)',
            'rgba(153, 102, 255, 0.2)',
            'rgba(255, 159, 64, 0.2)',
            'rgba(199, 199, 199, 0.2)'
          ],

          borderColor: [
            'rgba(255, 99, 132, 1)',
            'rgba(54, 162, 235, 1)',
            'rgba(255, 206, 86, 1)',
            'rgba(75, 192, 192, 1)',
            'rgba(153, 102, 255, 1)',
            'rgba(255, 159, 64, 1)',
            'rgba(199, 199, 199, 1)'
          ],

          borderWidth: 1,

          datalabels: {
            display: true
          }
        }
      ]
    }
  })

  const chartOptions = ref({
    responsive: true,
    scales: {
      y: {
        ticks: {
          display: false,
          font: {
            size: 16
          }
        },

        grid: {
          display: true,
          drawBorder: false
        }
      },

      x: {
        grid: {
          display: false,
          drawBorder: true,
          drawOnChartArea: false
        },

        ticks: {
          font: {
            size: 16
          }
        }
      }
    },

    plugins: {
      legend: {
        display: false
      },

      tooltip: {
        position: 'nearest', // Устанавливает tooltip появляющийся ближе к точке
        yAlign: 'bottom', // Позиционирование tooltip сверху точки

        titleFont: {
          size: 16 // Размер шрифта для заголовка всплывающей подсказки
        },

        bodyFont: {
          size: 16 // Размер шрифта для текста всплывающей подсказки
        },

        footerFont: {
          size: 16 // Размер шрифта для подвала всплывающей подсказки
        }
      },

      datalabels: {
        align: 'center',
        anchor: 'center',

        color: '#000', // Установите цвет текста, чтобы он хорошо смотрелся на фоне колонки

        font: {
          size: 16 // Увеличенный размер шрифта для меток
        },

        formatter: (value) => {
          // Если значение равно 0, не отображать метку
          return value !== 0 ? value : ''
        }
      }
    }
  })

  onMounted(() => statisticsStore.fetchWeeklyTaskStats.execute())
</script>

<template>
  <div class="row mb-3 align-items-center">
    <div class="col">
      <h1 class="h2">Статистика успешных задач за 7 дней</h1>
    </div>
  </div>
  <div class="row">
    <div class="col-12">
      <PerfectScrollbar>
        <Bar :options="chartOptions" :data="chartData" />
      </PerfectScrollbar>
    </div>
  </div>
</template>

<style lang="scss" scoped>
    .ps {
        height: auto;
        max-height: var(--ps-height);
        box-shadow: var(--ps-shadow-box);
        border-radius: var(--ps-border-radius);

        canvas {
            padding: 2rem 1.5rem 1rem;
        }
    }
</style>
