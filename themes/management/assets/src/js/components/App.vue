<template>
    <div id="app">
        <loading ref="loading"/>

        <transition name="page" mode="out-in">
            <component v-if="layout" :is="layout"/>
        </transition>

        <!-- loader -->
        <div v-if="showLoader" class="wask_loader">
            <dot-loader color="#faa61a" size="80px"></dot-loader>
        </div>

        <!-- snackbar -->
        <v-snackbar
            :timeout="snackbarDuration"
            :color="snackbarColor"
            top
            v-model="showSnackbar">
            {{ snackbarMessage }}

            <template v-slot:action="{ attrs }">
                <v-btn
                    dark
                    text
                    v-bind="attrs"
                    @click="snackbar = false"
                >
                    Close
                </v-btn>
            </template>
        </v-snackbar>

        <!-- dialog confirm -->
        <v-dialog v-show="showDialog" v-model="showDialog" absolute max-width="450px">
           <v-card>
                <v-card-title>
                    <div class="headline">
                        <v-icon v-if="dialogIcon">{{dialogIcon}}</v-icon>
                        {{ dialogTitle }}
                    </div>
                </v-card-title>
                <v-card-text>{{ dialogMessage }}</v-card-text>
                <v-card-actions v-if="dialogType==='confirm'">
                    <v-spacer></v-spacer>
                    <v-btn color="green darken-1" flat="flat" @click.native="dialogCancel">Cancel</v-btn>
                    <v-btn color="green darken-1" flat="flat" @click.native="dialogOk">Ok</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>

<script>
    import Loading from './Loading'
    import DotLoader from 'vue-spinner/src/DotLoader.vue';

    // Load layout components dynamically.
    const requireContext = require.context('~/layouts', false, /.*\.vue$/);

    const layouts = requireContext.keys()
        .map(file =>
            [file.replace(/(^.\/)|(\.vue$)/g, ''), requireContext(file)]
        )
        .reduce((components, [name, component]) => {
            components[name] = component.default || component;
            return components
        }, {});

    export default {
        components: {
            Loading,
            DotLoader
        },

        data: () => ({
            layout: null,
            defaultLayout: 'default'
        }),

        metaInfo() {
            const {appName} = window.config;

            return {
                title: appName,
                titleTemplate: `%s · ${appName}`
            }
        },

        mounted() {
            this.$loading = this.$refs.loading
        },

        computed: {
            showLoader() {
                return this.$store.getters['default/showLoader'];
            },
            showSnackbar: {
                get() {
                    return this.$store.getters['default/showSnackbar'];
                },
                set(val) {
                    if (!val) this.$store.commit('default/hideSnackbar');
                }
            },
            snackbarMessage() {
                return this.$store.getters['default/snackbarMessage'];
            },
            snackbarColor() {
                return this.$store.getters['default/snackbarColor'];
            },
            snackbarDuration() {
                return this.$store.getters['default/snackbarDuration'];
            },

            // dialog
            showDialog: {
                get() {
                    return this.$store.getters['default/showDialog'];
                },
                set(val) {
                    if (!val) this.$store.commit('default/hideDialog');
                }
            },
            dialogType() {
                return this.$store.getters['default/dialogType']
            },
            dialogTitle() {
                return this.$store.getters['default/dialogTitle']
            },
            dialogMessage() {
                return this.$store.getters['default/dialogMessage']
            },
            dialogIcon() {
                return this.$store.getters['default/dialogIcon']
            },
        },

        methods: {
            /**
             * Set the application layout.
             *
             * @param {String} layout
             */
            setLayout(layout) {
                if (!layout || !layouts[layout]) {
                    layout = this.defaultLayout
                }

                this.layout = layouts[layout]
            },

            menuClick(routeName, routeType) {

                let rn = routeType || 'vue';

                if (rn === 'vue') {

                    this.$router.push({name: routeName});
                }
                if (rn === 'full_load') {

                    window.location.href = routeName;
                }
            },
            clickLogout(logoutUrl, afterLogoutRedirectUrl) {
                axios.post(logoutUrl).then((r) => {
                    window.location.href = afterLogoutRedirectUrl;
                });
            },
            dialogOk() {
                this.$store.commit('default/dialogOk');
            },
            dialogCancel() {
                this.$store.commit('default/dialogCancel');
            },
        }
    }
</script>

<style lang="scss" scoped>
    .wask_loader {
        background: #FFF
    }
</style>
