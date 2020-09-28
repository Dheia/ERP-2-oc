<template>
    <div class="main-layout">
        <v-app id="inspire" :style="{background: $vuetify.theme.themes[theme].primary.darken5}">
            <child/>
        </v-app>
    </div>
</template>

<script>
    export default {
        name: 'BasicLayout',
        computed: {
            theme() {
                return (this.$vuetify.theme.dark) ? 'dark' : 'light'
            }
        }
    }
</script>

<style lang="scss">
</style>
