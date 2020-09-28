import Vue from 'vue'
import Vuetify from 'vuetify/lib'
import '@mdi/font/css/materialdesignicons.css' // Ensure you are using css-loader

Vue.use(Vuetify);

const opts = {
    theme: {
        options: {
            customProperties: true,
        },
        themes: {
            dark: {
                primary: {
                    'lighten5':'#e0e6ed',
                    'lighten4':'#bfc9d4',
                    'lighten3':'#888ea8',
                    'lighten2':'#6c7186',
                    'lighten1':'#506690',
                    'base':'#494c5a',
                    'darken1':'#3b3f5c',
                    'darken2':'#2f3249',
                    'darken3':'#1b2e4b',
                    'darken4':'#1a1c2d',
                    'darken5':'#060818',
                },
                secondary: {
                    'base': '#009688',
                    'darken1': '#006e64',
                    'darken2': '#006057',
                    'darken3': '#004842',
                    'darken4': '#1b2e4b',
                    'darken5': '#0e1726'
                },
                accent: '#ffbb44',
                error: '#FF5252',
                info: '#2196F3',
                success: '#4CAF50',
                warning: '#FFC107',
            }
        },
        dark: true,
    },
    icons: {
        iconfont: 'mdi', // default - only for display purposes
    }
};

export default new Vuetify(opts)
