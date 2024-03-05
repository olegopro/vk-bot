<template>
    <div class="modal-header">
        <svg width="28" height="28" class="me-2" >
            <use xlink:href="#group"></use>
        </svg>

        <h1 class="modal-title fs-5">{{ groupData?.name }}</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col-12">
                <div class="d-flex account flex-wrap">

                    <div class="d-flex">
                        <div class="position-relative">
                            <div v-if="!groupData?.photo_200" class="stub">
                                <div class="spinner-border" style="width: 3rem; height: 3rem;" role="status">
                                </div>
                            </div>

                            <img v-else width="200" height="200" :src="groupData?.photo_200" alt="">

                        </div>

                        <p class="m-3 mt-0"><b>Статус: </b>{{ groupData?.status }}</p>
                    </div>

                    <div class="col-12 d-flex flex-column justify-content-between">
                        <div>
                            <h5>{{ groupData?.screen_name }}</h5>
                        </div>
                    </div>
                    <div class="col-12 d-flex flex-column">
                        <h4 class="mb-3">
                            <svg width="28" height="28" class="me-3">
                                <use xlink:href="#followers"></use>
                            </svg>
                            Подписчики - {{ groupData?.members_count }}
                        </h4>
                        <h4>
                            <svg width="28" height="28" class="me-3">
                                <use xlink:href="#address"></use>
                            </svg>
                            Город - {{ groupData?.city?.title ?? 'Не указан' }}
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
    export default {
        props: ['groupData']
    }
</script>
