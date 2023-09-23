<template>
    <div class="modal fade" id="taskDetails" tabindex="-1" aria-labelledby="Task details" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header" v-if="taskData">
                    <h1 class="modal-title fs-5" id="Delete task">Информация о задаче #{{ taskId }}</h1>
                    <!--<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>-->
                </div>

                <div class="modal-body py-0">
                    <div class="d-flex mb-3" v-if="taskData">
                        <img :src="taskData.attachments[0].photo.sizes[2].url" class="rounded-1" alt="">
                        <div class="ps-3 d-flex flex-column">
                            <p class="mb-1 w-100">Количество лайков: <b>{{ taskData.likes.count }}</b></p>
                            <p>Дата публикации: <b>{{ formatData(taskData.attachments[0].photo.date) }}</b></p>
                        </div>
                    </div>

                    <div class="accordion" v-if="taskData">
                        <div class="accordion-item">

                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#listOfLikes" aria-expanded="false" aria-controls="listOfLikes">
                                    Список лайкнувших
                                </button>
                            </h2>

                            <div id="listOfLikes" class="accordion-collapse collapse">
                                <div class="accordion-body p-0">
                                    <div class="accordion">
                                        <div class="accordion-item" v-for="user in taskData.liked_users" :key="user.id">
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
                    <button type="button" class="btn btn-danger" @click="deleteLikeById" v-if="isUserLiked" :disabled="disableSubmit">Отменить лайк</button>
                    <button type="button" class="btn btn-secondary" @click="modalHide">Закрыть</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import { Modal } from 'bootstrap'

    export default {
        props: ['taskData', 'taskId'],
        emits: ['deleteLike'],

        data() {
            return {
                disableSubmit: false
            }
        },

        computed: {
            isUserLiked() {
                if (this.taskData && this.taskData.liked_users && this.taskData.account_id) {
                    return this.taskData.liked_users.some(user => user.id === this.taskData.account_id)
                }

                return false
            }
        },

        mounted() {
            this.modal = new Modal(document.getElementById('taskDetails'))
        },

        methods: {
            modalHide() {
                this.modal.hide()
            },

            formatData(timestamp) {
                const date = new Date(timestamp * 1000)
                const year = date.getFullYear()
                const month = date.getMonth() + 1 // Месяцы начинаются с 0, поэтому добавляем 1
                const day = date.getDate()
                const hours = date.getHours()
                const minutes = date.getMinutes()

                return `${year}-${month}-${day} ${hours}:${minutes}`
            },

            deleteLikeById() {
                this.$emit('deleteLike', this.taskId)
                this.disableSubmit = true
            }
        }
    }
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
                    &:not(:first-child) {
                        .accordion-button {
                            //border-radius: 0;
                        }
                    }

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
                                    //border-top: 0;
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
