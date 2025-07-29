<script setup lang="ts">
  import { ref, computed, defineProps, getCurrentInstance } from 'vue'
  import { format } from 'date-fns'
  import { useTasksStore } from '@/stores/TasksStore'
  import { showErrorNotification, showSuccessNotification } from '@/helpers/notyfHelper'
  import { useModal } from '@/composables/useModal'

  const props = defineProps<{
    taskId: number
  }>()

  const modalId = getCurrentInstance()?.type.__name
  const tasksStore = useTasksStore()
  const disableSubmit = ref(false)
  const { closeModal } = useModal()

  const isUserLiked = computed(() => {
    return (taskId: number) => {
      const task = tasksStore.tasks.find(task => task.id === taskId)
      if (tasksStore.fetchTaskDetails.data?.liked_users && task?.account_id) {
        return tasksStore.fetchTaskDetails.data.liked_users.some(user => user.id === task.account_id)
      }
      return false
    }
  })

  const formatData = timestamp => format(new Date(timestamp * 1000), 'yyyy-MM-dd HH:mm:ss')

  const deleteLikeById = () => {
    disableSubmit.value = true
    tasksStore.deleteLike.execute({ taskId: props.taskId })
      .then(response => {
        showSuccessNotification(response.message)
        tasksStore.fetchTaskDetails.execute({ taskId: props.taskId })
      })
      .finally(() => disableSubmit.value = false)
  }
</script>

<template>
  <div class="modal fade" :id="modalId" tabindex="-1" aria-labelledby="Task details" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header" v-if="tasksStore.taskDetails">
          <h1 class="modal-title fs-5" id="Delete task">Информация о задаче #{{ taskId }}</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <PerfectScrollbar class="ps-modal">
          <div class="modal-body py-0">
            <div class="d-flex mb-3" v-if="tasksStore.taskDetails">
              <img :src="tasksStore.fetchTaskDetails.data.attachments[0].photo.sizes[4].url" class="rounded-1 w-100" alt="">
            </div>

            <div class="ps-3 d-flex flex-column">
              <p class="mb-1 w-100">Количество лайков: <b>{{ tasksStore.fetchTaskDetails.data.likes.count }}</b></p>
              <p>Дата публикации: <b>{{ formatData(tasksStore.fetchTaskDetails.data.attachments[0].photo.date) }}</b>
              </p>
            </div>

            <div class="accordion" v-if="tasksStore.taskDetails">
              <div class="accordion-item">
                <h2 class="accordion-header">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#listOfLikes" aria-expanded="false" aria-controls="listOfLikes">
                    Список лайкнувших
                  </button>
                </h2>

                <div id="listOfLikes" class="accordion-collapse collapse">
                  <div class="accordion-body p-0">
                    <div class="accordion">
                      <div class="accordion-item" v-for="user in tasksStore.fetchTaskDetails.data.liked_users" :key="user.id">
                        <h2 class="accordion-header">
                          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" :data-bs-target="'#id' + user.id " aria-expanded="false" :aria-controls="user.id">
                            {{ user.first_name }} {{ user.last_name }}
                          </button>
                        </h2>
                        <div :id="'id' + user.id" class="accordion-collapse collapse">
                          <div class="accordion-body">
                            <strong>This is the first item's accordion body.</strong> It is shown by default,
                            until the collapse plugin adds the appropriate classes that we use to style each element.
                            These classes control the overall appearance, as well as the showing and hiding via CSS transitions.
                            You can modify any of this with custom CSS or overriding our default variables.
                            It's also worth noting that just about any HTML can go within the
                            <code>.accordion-body</code>, though the transition does limit overflow.
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </PerfectScrollbar>

        <div class="modal-footer">
          <button type="button"
            class="btn btn-danger"
            @click="deleteLikeById"
            :disabled="disableSubmit"
            v-show="disableSubmit || isUserLiked(props.taskId)"
          >
            Отменить лайк
          </button>
          <button type="button" class="btn btn-secondary" @click="closeModal(modalId)">Закрыть</button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss">
  #taskDetails {
    .modal-body {
      img {
        width: 200px;
        height: 200px;
        object-fit: cover;
        object-position: center;
      }

      .accordion {
        .accordion-item {
          .accordion-header {
            .accordion-button {
              box-shadow: none;
            }
          }

          #listOfLikes {
            .accordion {
              .accordion-item {
                border-radius: 0 !important;

                &:first-child {
                  border-radius: 0;
                }

                &:last-child {
                  border: none;

                  .accordion-button.collapsed {
                    border-bottom-right-radius: var(--bs-accordion-border-radius);
                    border-bottom-left-radius: var(--bs-accordion-border-radius);
                  }
                }

                .accordion-header {
                  border-radius: 0;

                  button {
                    border-radius: 0;
                  }
                }
              }
            }
          }
        }
      }

    }
  }

</style>
