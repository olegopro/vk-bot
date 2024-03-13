<template>
    <div class="modal fade" id="taskDetailsModal" tabindex="-1" aria-labelledby="Task details" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header" v-if="tasksStore.taskDetails">
                    <h1 class="modal-title fs-5" id="Delete task">Информация о задаче #{{ taskData.taskId }}</h1>
                    <!--<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>-->
                </div>

                <div class="modal-body py-0">
                    <div class="d-flex mb-3" v-if="tasksStore.taskDetails">
                        <img :src="tasksStore.taskDetails.attachments[0].photo.sizes[2].url" class="rounded-1" alt="">
                        <div class="ps-3 d-flex flex-column">
                            <p class="mb-1 w-100">Количество лайков: <b>{{ tasksStore.taskDetails.likes.count }}</b></p>
                            <p>Дата публикации: <b>{{ formatData(tasksStore.taskDetails.attachments[0].photo.date) }}</b></p>
                        </div>
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
                                        <div class="accordion-item" v-for="user in tasksStore.taskDetails.liked_users" :key="user.id">
                                            <h2 class="accordion-header">
                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" :data-bs-target="'#id' + user.id " aria-expanded="false" :aria-controls="user.id">
                                                    {{ user.first_name }} {{ user.last_name }}
                                                </button>
                                            </h2>
                                            <div :id="'id' + user.id" class="accordion-collapse collapse">
                                                <div class="accordion-body">
                                                    <strong>This is the first item's accordion body.</strong> It is shown by default, until the collapse plugin adds the appropriate classes that we use to style each element. These classes control the overall appearance, as well as the showing and hiding via CSS transitions. You can modify any of this with custom CSS or overriding our default variables. It's also worth noting that just about any HTML can go within the
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
                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-danger"
                            @click="deleteLikeById"
                            :disabled="disableSubmit"
                            v-show="disableSubmit || isUserLiked"
                    >
                        Отменить лайк
                    </button>
                    <button type="button" class="btn btn-secondary" @click="modalHide">Закрыть</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
    import { ref, computed, watch, defineProps, inject, onMounted, onUnmounted } from 'vue'
    import { format } from 'date-fns'
    import { useTasksStore } from '@/stores/TasksStore'
    import { showErrorNotification } from '@/helpers/notyfHelper'

    const props = defineProps({
        taskData: Object
    })

    const tasksStore = useTasksStore()
    const disableSubmit = ref(false)

    const closeModal = inject('closeModal')

    const isUserLiked = computed(() => {
        if (props.taskData && props.taskData.liked_users && props.taskData.account_id) {
            return props.taskData.liked_users.some(user => user.id === props.taskData.account_id)
        }

        return false
    })

    watch(() => props.taskData, newTaskData => {
        if (newTaskData && newTaskData.taskId) {
            tasksStore.fetchTaskDetails(newTaskData.taskId)
        }
    }, { immediate: true })

    const modalHide = () => closeModal('taskDetailsModal')

    const formatData = timestamp => format(new Date(timestamp * 1000), 'yyyy-MM-dd HH:mm:ss')

    const deleteLikeById = () => {
        disableSubmit.value = true
        tasksStore.deleteLike(props.taskData.taskId)
            .then(() => tasksStore.fetchTaskDetails(props.taskData.taskId))
            .catch(data => showErrorNotification(data.response.data.message))
            .finally(() => disableSubmit.value = false)
    }

    onMounted(() => console.log('TaskDetails onMounted'))
    onUnmounted(() => console.log('TaskDetails onUnmounted'))
</script>

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
                                border-left: 0;
                                border-right: 0;

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
