<template>
    <div class="row mt-5 mb-5">
        <h1>Публикации в ленте (photo)</h1>
        <div class="col-3" v-for="post in newsfeed" :key="post.source_id">
            <div class="card">
                <img class="bd-placeholder-img card-img-top"
                     width="100%"
                     height="200"
                     alt=""
                     :src="post.attachments[0].photo.sizes[4].url"
                />
                <!--<div class="card-body">
                    <p class="card-text">{{ post?.text }}</p>
                </div>-->
                <p>{{post.owner_id}}</p>
                <p>{{post.post_id}}</p>
            </div>
        </div>
    </div>
</template>

<script>
    import { mapActions, mapGetters } from 'vuex'

    export default {
        mounted() {
            this.accountNewsfeed(this.$route.params.id)
        },

        computed: {
            ...mapGetters('account', ['getAccountNewsFeed']),

            newsfeed() {
                return this.getAccountNewsFeed
            }
        },

        methods: {
            ...mapActions('account', ['accountNewsfeed'])
        }
    }
</script>

<style scoped lang="scss">
    .card {
        border: none;
        box-shadow: 0 1px 27px 0 rgba(34, 60, 80, 0.2);

        .bd-placeholder-img {
            object-fit: cover;
            height: 100%;
            border-radius: 0.375rem;
        }

        //.card-body {
        //    .card-text {
        //        overflow: hidden;
        //        white-space: nowrap;
        //        text-overflow: ellipsis;
        //    }
        //}
    }
</style>
