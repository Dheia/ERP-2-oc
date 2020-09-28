<template>
    <div class="main-layout">
        <v-app id="inspire" :style="{background: $vuetify.theme.themes[theme].primary.darken5}">
            <navbar/>
            <child/>
        </v-app>
    </div>
</template>

<script>
    import Navbar from '~/components/Navbar'

    export default {
        name: 'MainLayout',
        components: {
            Navbar,
        },
        computed: {
            theme() {
                return (this.$vuetify.theme.dark) ? 'dark' : 'light'
            }
        }
    }
</script>

<style lang="scss">
</style>
