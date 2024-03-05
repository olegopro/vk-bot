<template>
    <div class="modal-header">
        <svg width="28" height="28" class="me-2" >
            <use xlink:href="#account"></use>
        </svg>

        <h1 class="modal-title fs-5">{{ accountData?.first_name }} {{ accountData?.last_name }} {{ accountData?.name }}</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col-12">
                <div class="d-flex account flex-wrap">

                    <div class="d-flex">
                        <div class="position-relative">
                            <div v-if="!accountData?.photo_200" class="stub">
                                <div class="spinner-border" style="width: 3rem; height: 3rem;" role="status">
                                </div>
                            </div>

                            <img v-else width="200" height="200" :src="accountData?.photo_200" alt="">

                            <OnlineStatus v-if="accountData?.photo_200" :type="accountData?.online === 0 ? 'offline' : 'online'" class="online-status" />
                        </div>

                        <p class="m-3 mt-0"><b>Статус: </b>{{ accountData?.status }}</p>
                    </div>

                    <div class="col-12 d-flex flex-column justify-content-between">
                        <div>
                            <h5>{{ accountData?.screen_name }}</h5>
                        </div>

                        <div class="mb-3">
                            <p>Последняя активность - {{ this.$parent.date(accountData?.last_seen?.time) }}</p>
                        </div>
                    </div>
                    <div class="col-12 d-flex flex-column">
                        <h4 class="mb-3">
                            <svg width="28" height="28" class="me-3">
                                <use xlink:href="#friends"></use>
                            </svg>
                            Друзья - {{ accountData?.friends_count }}
                        </h4>
                        <h4 class="mb-3">
                            <svg width="28" height="28" class="me-3">
                                <use xlink:href="#followers"></use>
                            </svg>
                            Подписчики - {{ accountData?.followers_count }}
                        </h4>
                        <h4>
                            <svg width="28" height="28" class="me-3">
                                <use xlink:href="#address"></use>
                            </svg>
                            Город - {{ accountData?.city?.title }}
                        </h4>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <button class="btn btn-secondary" @click="this.$parent.modalHide">Закрыть</button>
    </div>
</template>

<script>
    import OnlineStatus from '../../OnlineStatus.vue'

    export default {
        components: { OnlineStatus },
        props: ['accountData']
    }
</script>

<style scoped lang="scss">

</style>
