<template>
    <div class="modal fade" id="accountDetails" tabindex="-1" aria-labelledby="Task details" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="Delete task">Информация о аккаунте</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body" v-if="accountData">
                    <img :src="accountData.photo_200" alt="">
                    <OnlineStatus :type="accountData.online === 0 ? 'offline' : 'online'" />
                    <p>{{ accountData.first_name }} {{ accountData.last_name }}</p>
                    <p>Статус: {{ accountData.status }}</p>
                    <p>День рождения: {{ accountData.bdate }}</p>
                    <p>Пол: {{ formattedSex }}</p>
                    <p>Последняя активность - {{ date(accountData.last_seen?.time) }}</p>
                    <p>Страна: {{ accountData.country.title }}</p>
                    <p>Город: {{ accountData.city.title }}</p>
                    <p>Друзья: {{ accountData.friends_count }}</p>
                    <p>Подписчики: {{ accountData.followers_count }}</p>
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
                width: 200px;
                height: 200px;
                object-fit: cover;
                object-position: center;
            }
        }
    }

</style>
