<template>
    <div class="modal fade" id="accountDetails" tabindex="-1" aria-labelledby="Add account" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" v-if="ownerType">

                <Account
                    v-if="ownerType === 'account'"
                    :accountData="ownerData"
                />

                <Group
                    v-if="ownerType === 'group'"
                    :groupData="ownerData"
                />

            </div>
        </div>
    </div>
</template>

<script>
    import { Modal } from 'bootstrap'
    import Account from './OwnerDetails/Account.vue'
    import Group from './OwnerDetails/Group.vue'

    export default {
        components: { Group, Account },
        props: ['ownerData'],

        mounted() {
            this.modal = new Modal(document.getElementById('accountDetails'))
        },

        computed: {
            ownerType() {
                switch (true) {
                    case Boolean(this.ownerData?.type):
                        return 'group'
                    case Boolean(this.ownerData?.first_name):
                        return 'account'
                    default:
                        return null
                }
            }
        },

        methods: {
            date(timestamp) {
                return new Date(timestamp * 1000).toLocaleTimeString('ru-RU')
            },

            modalHide() {
                console.log('modalHide')
                this.modal.hide()
            }
        }
    }
</script>

<style lang="scss" scoped>
    :deep {
        img {
            width: 200px;
            height: 200px;
            border-radius: 0.5rem;
        }

        .online-status {
            position: absolute;
            right: 0;
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
