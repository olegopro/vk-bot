<template>
    <tr>
        <th scope="row">{{ task.id }}</th>
        <td>
            {{ username }}
            <router-link custom :to="{name: 'Account', params: {id: task.account_id}}" v-slot="{navigate}">
                <a class="btn btn-primary me-2" @click="navigate">
                    <svg width="16" height="20">
                        <use xlink:href="#right-arrow"></use>
                    </svg>
                </a>
            </router-link>

        </td>
        <td>
            <TaskStatus :type="task.payload" />
        </td>

        <td>{{ task.attempts }}</td>

        <td>
            <router-link custom to="/" v-slot="{navigate}">
                <a class="btn btn-primary me-2" @click="navigate">
                    <svg width="16" height="20">
                        <use xlink:href="#info"></use>
                    </svg>
                </a>
            </router-link>

            <button
                class="btn btn-danger"
                data-bs-target="#deleteTask"
                data-bs-toggle="modal"
                type="button"
            >
                <svg width="16" height="20">
                    <use xlink:href="#delete"></use>
                </svg>
            </button>
        </td>

        <td>{{ task.created_at }}</td>
    </tr>
</template>

<script>
    import { mapActions } from 'vuex'
    import TaskStatus from './TaskStatus.vue'

    export default {
        components: { TaskStatus },

        props: ['task'],

        data() {
            return {
                username: ''
            }
        },

        created() {
            this.accountByTaskId(this.task.id)
                .then(({ data }) => {
                    this.username = data.username
                })
        },

        methods: {
            ...mapActions('tasks', ['accountByTaskId'])
        }
    }
</script>

<style scoped>

</style>
