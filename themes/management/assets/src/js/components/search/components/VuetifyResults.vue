<template>
    <v-list
            two-line
            subheader
            class="algolia-autocomplete-listbox"
    >
        <v-list-item
            v-for="result in results"
            :key="result.objectID"
            avatar
            @click="resultClickEvent(result)"
        >
            <v-list-item-content>
                <v-list-item-title>
                    <ais-highlight :result="result" attribute-name="company"></ais-highlight>
                </v-list-item-title>
                <v-list-item-subtitle>
                    <ais-highlight :result="result" attribute-name="mb_name"></ais-highlight>
                </v-list-item-subtitle>
            </v-list-item-content>
        </v-list-item>

        <v-list-item v-if="results.length === 0">
            <v-list-item-title>
                <span class="grey--text text-xs-center">
                    <v-icon left size="18">error</v-icon> 검색어에 맞는 최근기록이 없습니다.
                </span>
            </v-list-item-title>
        </v-list-item>
    </v-list>
</template>

<script>
    import { Results } from 'vue-instantsearch';

    export default {
        extends: Results,
        name: "VuetifyResults",
        methods: {
            resultClickEvent: function (result) {
                this.$router.push({name:'signs.view',params:{id: result.id}});
                this.$emit('clearQuery')
            }
        }
    }
</script>

<style>
    .ais-highlight em {
        font-style: normal;
        font-weight: bold;
    }
    .algolia-autocomplete-listbox {
        position: absolute;
        left: 0;
        right: 0;
        box-shadow: 0px 3px 5px -1px rgba(0,0,0,0.2), 0px 6px 10px 0px rgba(0,0,0,0.14), 0px 1px 18px 0px rgba(0,0,0,0.12);
    }
</style>
