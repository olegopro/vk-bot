<template>
    <tr>
        <th scope="row">{{ task.id }}</th>
        <td>
            {{ firstName }} {{ lastName }}
        </td>
        <td>
            <TaskStatus :type="task.status" />
        </td>

        <td>{{ task.attempt_count }}</td>

        <td>
            <router-link custom to="/" v-slot="{navigate}">
                <a class="btn btn-primary me-2 button-style" @click="navigate">
                    <svg width="16" height="16">
                        <use xlink:href="#info"></use>
                    </svg>
                </a>
            </router-link>

            <button
                class="btn btn-danger button-style"
                data-bs-target="#deleteTask"
                data-bs-toggle="modal"
                type="button"
            >
                <svg width="16" height="16">
                    <use xlink:href="#delete"></use>
                </svg>
            </button>
        </td>

        <td>{{ dateFormat(task.created_at) }}</td>
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
                firstName: '',
                lastName: ''
            }
        },

        created() {
            this.getScreenNameById(this.task.owner_id)
                .then((data) => {
                    this.firstName = data[0].first_name
                    this.lastName = data[0].last_name
                })
                .catch(error => console.log(error.response.data.message))
        },

        methods: {
            ...mapActions('tasks', ['accountByTaskId']),
            ...mapActions('account', ['getScreenNameById']),

            dateFormat(date) {
                return new Date(date).toISOString()
                    .replace('T', ' ')
                    .replace('Z', '')
                    .split('.')[0]
            }
        }
    }
</script>

<style scoped lang="scss">
    .account-link {
        cursor: pointer;
    }
</style>
