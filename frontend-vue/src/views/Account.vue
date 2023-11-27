<template>
    <div class="row">
        <div class="col-12">
            <div class="d-flex account">
                <div v-if="!specificAccount?.photo_200" class="stub">
                    <div class="spinner-border" style="width: 3rem; height: 3rem;" role="status"></div>
                </div>
                <img v-else class="col-3 ps-0" width="200" height="200" :src="specificAccount?.photo_200" alt="">
                <div class="col-4 p-3 d-flex flex-column justify-content-between">
                    <div>
                        <h2>{{ specificAccount?.first_name }} {{ specificAccount?.last_name }}</h2>
                        <h3>{{ specificAccount?.screen_name }}</h3>
                    </div>
                    <div class="mb-3">
                        <p>Статус - {{ specificAccount?.status }}</p>
                        <p>Последняя активность - {{ date(specificAccount?.last_seen?.time) }}</p>
                    </div>
                </div>
                <div class="col-4 p-3 d-flex flex-column">
                    <h4 class="mb-3">
                        <svg width="28" height="28" class="me-3">
                            <use xlink:href="#friends"></use>
                        </svg>
                        Друзья - {{ specificAccount?.friends_count }}
                    </h4>
                    <h4 class="mb-3">
                        <svg width="28" height="28" class="me-3">
                            <use xlink:href="#followers"></use>
                        </svg>
                        Подписчики - {{ specificAccount?.followers_count }}
                    </h4>
                    <h4>
                        <svg width="28" height="28" class="me-3">
                            <use xlink:href="#address"></use>
                        </svg>
                        Город - {{ specificAccount?.city?.title }}
                    </h4>
                </div>
                <div class="col p-3">
                    <OnlineStatus :type="specificAccount?.online === 0 ? 'offline' : 'online'" />
                </div>
            </div>
        </div>
    </div>
    <Followers v-if="settingsStore.getSettings().show_followers === 1" />
    <Friends v-if="settingsStore.getSettings().show_friends === 1" />
    <Newsfeed />
</template>

<script setup>
    import { ref, onMounted } from 'vue'
    import { useAccountStore } from '@/stores/AccountStore'
    import { useRoute } from 'vue-router'
    import Followers from '../components/Account/Followers.vue'
    import Friends from '../components/Account/Friends.vue'
    import OnlineStatus from '../components/Account/OnlineStatus.vue'
    import Newsfeed from '../components/Account/Newsfeed.vue'
    import { useSettingsStore } from '../stores/SettingsStore'

    const accountStore = useAccountStore()
    const settingsStore = useSettingsStore()

    const route = useRoute()

    const userID = route.params.id
    const specificAccount = ref(null)

    const date = (timestamp) => new Date(timestamp * 1000).toLocaleTimeString('ru-RU')

    onMounted(async () => {
        await accountStore.fetchOwnerData(userID)
        await accountStore.getOwnerDataById(userID)
        await settingsStore.fetchSettings()
        specificAccount.value = accountStore.getOwnerDataById(userID)
    })

</script>

<style scoped lang="scss">
    .account {
        box-shadow: 0 1px 27px 0 rgba(34, 60, 80, 0.2);
        border-radius: 0.375rem;

        h1, h2, h3, h4, h5, h6, p {
            margin: 0;
        }

        h4 {
            display: flex;
            align-items: center;
        }

        img {
            border-top-left-radius: 0.375rem;;
            border-bottom-left-radius: 0.375rem;;
            width: 200px;
            height: 200px;
            object-fit: cover;
        }

        .stub {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 200px;
            height: 200px;
            border-right: 1px solid whitesmoke;
        }
    }
</style>
