<template>
    <div class="component-wrap">
        <ais-instant-search
            :search-client="searchClient"
            index-name="signs"
        >
            <ais-index
                index-name="signs"
            >
                <!--                <vuetify-input @query="getQuery" :search-text="searchText">-->
                <!--                </vuetify-input>-->

                <!--                <vuetify-results v-if="searchResultShow" :results-per-page="6" @clearQuery="clearQuery"></vuetify-results>-->
            </ais-index>
            <ais-autocomplete>
                <div slot-scope="{ currentRefinement, indices, refine }">
                    <v-text-field
                        type="search"
                        :value="currentRefinement"
                        placeholder="Search for a product"
                        @input="refine($event.currentTarget.value)"
                    />
                    <ul v-if="currentRefinement" v-for="index in indices" :key="index.label">
                        <li>
                            <h3>{{ index.label }}</h3>
                            <ul>
                                <li v-for="hit in index.hits" :key="hit.objectID">
                                    <ais-highlight attribute="name" :hit="hit"/>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </ais-autocomplete>
        </ais-instant-search>
    </div>
</template>

<script>
    import algoliasearch from 'algoliasearch/lite';
    // import VuetifyInput from './components/VuetifyInput.vue';
    // import VuetifyResults from './components/VuetifyResults.vue';

    export default {
        name: "Search",
        // components: {VuetifyResults, VuetifyInput},
        data: () => ({
            searchText: '',
            searchResultShow: false,
            searchClient: algoliasearch(
                'MLSD6D8PTP',
                '8650199c9bcd974cc22fe0077fadcdd7'
            ),
        }),
        watch: {
            'searchText': function () {
                this.searchResultShow = !!this.searchText;
            }
        },
        methods: {
            getQuery: function (value) {
                this.searchText = value;
            },
            clearQuery: function () {
                this.searchText = '';
            }
        }
    }
</script>

<style scoped>
    .component-wrap {
        position: relative;
    }
</style>
