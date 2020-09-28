<template>
    <transition name="page" mode="out-in">
        <v-main
            color="background"
            :style="{paddingTop: $vuetify.application.top*2 + 'px'}"
        >
            <div class="pa-4">
                <transition name="fade">
                    <router-view></router-view>
                </transition>
            </div>
        </v-main>
    </transition>
</template>

<script>
    export default {
        name: 'Child'
    }
</script>
