<template>
    <div class="component-wrap">
        <v-card
            class="sign-list widget"
        >
            <!-- data table -->
            <v-data-table
                v-model="selected"
                :headers="headers"
                :page.sync="getPagination"
                :items="items"
                :server-items-length="totalItems"
                show-select
                class="elevation-0 my-4"
            >
                <template slot="headerCell" slot-scope="props">
                    <span v-if="props.header.value==='company'">
                        <v-icon>store</v-icon> {{ props.header.text }}
                    </span>
                    <span v-else-if="props.header.value==='mb_name'">
                        <v-icon>person</v-icon> {{ props.header.text }}
                    </span>
                    <span v-else-if="props.header.value==='day'">
                        <v-icon>av_timer</v-icon> {{ props.header.text }}
                    </span>
                    <span v-else-if="props.header.value==='created_at'">
                        <v-icon>av_timer</v-icon> {{ props.header.text }}
                    </span>
                    <span v-else-if="props.header.value==='last_login'">
                        <v-icon>av_timer</v-icon> {{ props.header.text }}
                    </span>
                    <span v-else-if="props.header.value==='progress'">
                        <v-icon>timeline</v-icon> {{ props.header.text }}
                    </span>
                    <span v-else>{{ props.header.text }}</span>
                </template>

                <template slot="items" slot-scope="props">
                    <td :active="props.selected" @click="props.selected = !props.selected">
                        <v-checkbox
                            :input-value="props.selected"
                            primary
                            hide-details
                        ></v-checkbox>
                    </td>
                    <td @click="$router.push({name:'signs.view',params:{id: props.item.id}})" class="subject">
                        {{ props.item.company }}
                    </td>
                    <td>{{ props.item.user ? props.item.user.name : ''}}</td>
                    <td>{{ props.item.day }}</td>
                    <td>{{ props.item.created_at.substr(0,10) }}</td>
                    <td class="text-xs-center">
                        <v-btn small @click="showDialog('sign_progress',props.item.product_process)" round
                               color="props.item.button_color"
                               dark>
                        <span v-if="props.item.product_process.length > 0">
                            {{props.item.product_process[0].state}}
                        </span>
                            <span v-else>접수중</span>
                        </v-btn>

                    </td>
                </template>

            </v-data-table>


            <!-- dialog for show permissions -->
            <v-dialog v-model="dialogs.showProgress.show" lazy absolute max-width="300px">
                <v-card>
                    <v-card-title>
                        <div class="headline">
                            <v-icon>vpn_key</v-icon>
                            제작 진행상황
                        </div>
                    </v-card-title>
                    <v-card-text>
                        <v-chip v-for="(process,key) in dialogs.showProgress.items" :key="key" class="white--text">
                            {{process.name}}
                            {{process.content}}
                            {{process.state}}
                        </v-chip>
                        <p v-if="dialogs.showProgress.items.length==0">접수대기</p>
                    </v-card-text>
                </v-card>
            </v-dialog>
        </v-card>
    </div>
</template>

<script>
    export default {
        middleware: 'admin',
        data() {
            return {
                selected: [],
                headers: [
                    {text: '업체명', value: 'company', align: 'left', sortable: false},
                    {text: '계약자', value: 'mb_name', align: 'left', sortable: false},
                    {text: '계약일', value: 'day', align: 'left', sortable: false},
                    {text: '등록일', value: 'created_at', align: 'left', sortable: false},
                    {text: '진행상태', value: 'progress', align: 'center', sortable: false},
                ],
                items: [],
                totalItems: 0,
                filters: {
                    titleSearch: '',
                    company: '',
                    mb_name: '',
                    email: '',
                    groupId: [],
                    serviceOptions: ['홈페이지', '블로그']
                },
                dialogs: {
                    showProgress: {
                        items: [],
                        show: false
                    }
                },
            }
        },
        mounted() {
            const self = this;

            self.loadSigns(() => {
            });

            self.$eventBus.$on(['SIGN_ADDED', 'SIGN_UPDATED', 'SIGN_DELETED'], () => {
                self.loadSigns(() => {
                });
            });
        },
        watch: {
            'getPagination.page': function () {
                this.loadSigns(() => {
                });
                this.$store.commit('default/setPagination', this.getPagination)
            },
            'getPagination.rowsPerPage': function () {
                this.loadSigns(() => {
                });
                this.$store.commit('setPagination', this.getPagination)
            },
        },
        methods: {
            trash(sign) {
                const self = this;

                self.$store.commit('showDialog', {
                    type: "confirm",
                    icon: 'warning',
                    title: "Confirm Deletion",
                    message: "정말로 계약업체를 삭제하시겠습니까?",
                    okCb: () => {

                        axios.delete('/app/signs/' + sign.id).then(function (response) {

                            self.$store.commit('showSnackbar', {
                                message: response.data.message,
                                color: 'success',
                                duration: 3000
                            });

                            self.$eventBus.$emit('SIGN_DELETED');

                        }).catch(function (error) {

                            self.$store.commit('default/hideLoader');

                            if (error.response) {
                                self.$store.commit('showSnackbar', {
                                    message: error.response.data.message,
                                    color: 'error',
                                    duration: 3000
                                });
                            } else if (error.request) {
                                console.log(error.request);
                            } else {
                                console.log('Error', error.message);
                            }
                        });
                    },
                    cancelCb: () => {
                        console.log("CANCEL");
                    }
                });
            },
            loadSigns(cb) {

                const self = this;

                let params = {
                    titlesearch: self.filters.titleSearch,
                    page: self.getPagination.page,
                    per_page: self.getPagination.rowsPerPage
                };

                self.$store.commit('default/showLoader');

                axios.get('/api/signs', {params: params}).then(function (response) {
                    self.items = response.data.data.data.reverse();
                    self.totalItems = response.data.data.total;
                    self.getPagination.totalItems = response.data.data.total;

                    self.$store.commit('default/hideLoader');

                    (cb || Function)();
                }).catch(function (error) {
                    if (error.response) {
                        self.$store.commit('default/showSnackbar', {
                            message: error.response.data.message,
                            color: 'error',
                            duration: 3000
                        });
                    } else if (error.request) {
                        console.log(error.request);
                    } else {
                        console.log('Error', error.message);
                    }

                    self.$store.commit('default/hideLoader');
                });
            },
            showDialog(dialog, data) {

                const self = this;

                switch (dialog) {
                    case 'sign_progress':
                        self.dialogs.showProgress.items = data || 0;
                        self.dialogs.showProgress.show = true;
                        break;
                }
            },
            toggleAll() {
                if (this.selected.length) this.selected = []
                else this.selected = this.desserts.slice()
            }
        },
        computed: {
            // pagination
            getPagination: {
                get: function () {
                    return this.$store.getters["default/getPagination"]
                },
                set: function (newValue) {
                    this.$store.commit('default/setPagination', newValue)
                }
            },
        }
    }
</script>

<style lang="scss">
    .sign-list .v-data-table {
        background: var(--v-secondary-darken5)
    }
</style>
