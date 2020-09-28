<template>
    <div>

        <v-navigation-drawer
            dark
            :color="$vuetify.theme.themes.dark.primary.darken5"
            v-model="drawer"
            app
            clipped
            class="menu-category"
            width="230"
            :style="{top: $vuetify.application.top*2 + 'px'}"
        >
            <v-list
                nav
                dense
                class="pa-4"
            >
                <v-list-item-group
                    active-class="nav-item-active"
                >
                    <v-list-item
                        v-for="(menu, i) in menus"
                        :key="i"
                        :to="{name: menu.routeName}"
                    >
                        <v-list-item-icon>
                            <v-icon v-text="menu.icon"></v-icon>
                        </v-list-item-icon>

                        <v-list-item-content>
                            <v-list-item-title v-text="menu.label"></v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>
                </v-list-item-group>
            </v-list>
        </v-navigation-drawer>

        <v-app-bar
            :color="$vuetify.theme.themes.dark.primary.darken5"
            app
            clipped-left
            fixed
            elevation="0"
            height="54"
            class="header-container"
        >
            <v-toolbar-title
                class="pr-6"
            >
                <div @click="$router.push({name:'signs.list'})" class="d-flex">
                    <img height="30" :src="logo.image">
                </div>
            </v-toolbar-title>

            <search></search>

            <v-spacer></v-spacer>

            <v-menu bottom left offset-y>
                <template v-slot:activator="{ on, attrs }">
                    <v-icon
                        v-bind="attrs"
                        v-on="on"
                        large
                    >
                        more_vert
                    </v-icon>
                </template>

                <v-list>
                    <v-list-item-group :color="$vuetify.theme.themes.dark.primary.lighten4">
                        <v-list-item
                            @click="$router.push({name:'settings.profile'})"
                        >
                            <v-list-item-icon>
                                <v-icon>mdi-sign-out-alt</v-icon>
                            </v-list-item-icon>

                            <v-list-item-content>
                                <v-list-item-title>{{ $t('settings') }}</v-list-item-title>
                            </v-list-item-content>
                        </v-list-item>
                        <v-list-item
                            @click.prevent="logout"
                        >
                            <v-list-item-icon>
                                <v-icon>mdi-sign-out-alt</v-icon>
                            </v-list-item-icon>

                            <v-list-item-content>
                                <v-list-item-title>{{ $t('logout') }}</v-list-item-title>
                            </v-list-item-content>
                        </v-list-item>
                    </v-list-item-group>
                </v-list>
            </v-menu>
        </v-app-bar>

        <v-app-bar
            elevation="1"
            clipped-left
            app
            fixed
            ripple="false"
            :color="$vuetify.theme.themes.dark.primary.darken4"
            height="54"
            class="sub-header-container pl-8 pr-6"
            :style="{marginTop: $vuetify.application.top + 'px'}"
        >
            <v-app-bar-nav-icon
                :color="$vuetify.theme.themes.dark.primary.lighten5"
                @click.stop="drawer = !drawer"
            ></v-app-bar-nav-icon>

            <v-breadcrumbs dark color="white" :items="items">
                <template v-slot:item="{ item }">
                    <v-breadcrumbs-item
                        :href="item.href"
                        :disabled="item.disabled"
                    >
                        {{ item.text.toUpperCase() }}
                    </v-breadcrumbs-item>
                </template>
            </v-breadcrumbs>
        </v-app-bar>
    </div>
</template>

<script>
    import {mapGetters} from 'vuex'
    import LocaleDropdown from './LocaleDropdown'
    import Search from './search/Search'
    import logo from '../../images/logo.png'

    export default {
        components: {
            LocaleDropdown, Search
        },

        data: () => ({
            appName: window.config.appName,
            drawer: true,
            navigatorAnimation: {},
            menus: [
                {
                    'label': '리스트',
                    'icon': 'mdi-pencil-box-outline',
                    'routeName': 'signs.list'
                },
                {
                    'label': '작성',
                    'icon': 'mdi-pencil-box-outline',
                    'routeName': 'signs.create'
                },
                {
                    'label': '광고계정관리',
                    'icon': 'mdi-pencil-box-outline',
                    'routeName': 'signs.create'
                },
            ],
            logo: {
                title: 'logo',
                image: logo
            },
            items: [
                {
                    text: 'Dashboard',
                    disabled: false,
                    href: 'signs',
                },
                {
                    text: 'Link 1',
                    disabled: true,
                    href: 'signs.list',
                },
            ],
        }),

        mounted() {

        },

        computed: {
            ...mapGetters({
                user: 'auth/user',
            }),
        },

        methods: {
            async logout() {
                // Log out the user.
                await this.$store.dispatch('auth/logout')

                // Redirect to login.
                await this.$router.push({name: 'login'})
            },
        }
    }
</script>

<style lang="scss" scoped>
    @import '~vuetify/src/styles/styles.sass';

    .menu-category {
        border-right: 1px solid #0e1726;
    }

    .header-container {
        border-bottom: 1px solid #060818;
    }

    .v-list-item--link:before {
        background: none;
    }

    .menu-category.theme--dark.v-list {
        background:none;
    }
    .menu-category .v-list-item {
        font-family: $body-font-family;
        transition: .600s;
    }

    .menu-category .theme--dark.v-list-item .v-icon {
        color:inherit;
    }

    .menu-category .theme--dark.v-list-item:not(.v-list-item--active):not(.v-list-item--disabled) {
        color: var(--v-primary-lighten1);
    }

    .menu-category .theme--dark.v-list-item:hover {
        background: var(--v-primary-darken1);
        color:#fff !important;
    }

    .menu-category .theme--dark.v-list-item--active.nav-item-active {
        color: var(--v-primary-lighten5) !important;
        background: rgba(96, 125, 139, 0.54);
    }

    .v-list-item--dense .v-list-item__title, .v-list-item--dense .v-list-item__subtitle, .v-list--dense .v-list-item .v-list-item__title, .v-list--dense .v-list-item .v-list-item__subtitle {
        font-weight: 600;
    }

</style>
