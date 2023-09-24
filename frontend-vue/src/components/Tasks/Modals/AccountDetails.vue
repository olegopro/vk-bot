<template>
    <div class="modal fade" id="accountDetails" tabindex="-1" aria-labelledby="Task details" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" >
                <div class="modal-header" v-if="accountData">
                    <h1 class="modal-title fs-4" >
                        <span>{{ accountData.first_name }} {{ accountData.last_name }}</span>

                    </h1>
                    <OnlineStatus class="h-6" :type="accountData.online === 0 ? 'offline' : 'online'" />
                    <!--<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>-->
                </div>

                <div class="modal-body py-2" v-if="accountData">
                    <div class="row">
                        <div class="mb-3 col-6">
                            <img :src="accountData.photo_200" class="rounded-1 " alt="">
                        </div>

                       <div class="col-6">
                           <p class="mb-1"><b>Страна:</b> {{ accountData?.country?.title }}</p>
                           <p class="mb-1"><b>Город:</b> {{ accountData?.city?.title }}</p>
                           <p class="mb-1"><b>Друзья:</b> {{ accountData.friends_count }}</p>
                           <p class="mb-1"><b>Подписчики:</b> {{ accountData.followers_count }}</p>
                           <p class="mb-1"><b>Пол:</b> {{ formattedSex }}</p>
                           <p class="mb-1"><b>День рождения:</b> {{ accountData.bdate }}</p>
                           <p class="mb-0"><b>{{formattedSex === 'Мужской' ? 'Был' : 'Была'}} в сети: </b> {{ date(accountData.last_seen?.time) }}</p>
                       </div>
                    </div>

                    <p class="mb-0"><b>Статус:</b> {{ accountData.status }}</p>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="modalHide">Закрыть</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import { Modal } from 'bootstrap'
    import OnlineStatus from '../../Account/OnlineStatus.vue'

    export default {
        components: { OnlineStatus },
        props: ['accountData'],

        mounted() {
            this.modal = new Modal(document.getElementById('accountDetails'))
        },

        computed: {
            formattedSex() {
                switch (this.accountData.sex) {
                    case 1:
                        return 'Женский'
                    case 2:
                        return 'Мужской'
                    default:
                        return 'Не указан'
                }
            }
        },

        methods: {
            modalHide() {
                this.modal.hide()
            },

            date(timestamp) {
                return new Date(timestamp * 1000).toLocaleTimeString('ru-RU')
            }
        }
    }
</script>

<style scoped lang="scss">
    #accountDetails {
        .modal-body {
            img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                object-position: center;
            }
        }
    }

</style>
