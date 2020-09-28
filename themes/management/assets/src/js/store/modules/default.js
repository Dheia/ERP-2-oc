import * as types from '../mutation-types'

// state
export const state = {
    // loader
    showLoader: false,

    // snackbar
    showSnackbar: false,
    snackbarMessage: '',
    snackbarColor: '',
    snackbarDuration: 3000,

    // dialog
    dialogShow: false,
    dialogType: '',
    dialogTitle: '',
    dialogMessage: '',
    dialogIcon: null,
    dialogOkCb: ()=>{},
    dialogCancelCb: ()=>{},

    //pagination
    pagination: {
        page:1,
        rowsPerPage: 25,
        totalItems: '',
        sortBy: 'created_at',
        descending: true,
    }
}

// getters
export const getters = {
    // loader
    showLoader: state => {
        return state.showLoader
    },

    // snackbar
    showSnackbar: state => {
        return state.showSnackbar
    },
    snackbarMessage: state => {
        return state.snackbarMessage
    },
    snackbarColor: state => {
        return state.snackbarColor
    },
    snackbarDuration: state => {
        return state.snackbarDuration
    },

    // dialog
    showDialog: state => {
        return state.dialogShow
    },
    dialogType: state => {
        return state.dialogType
    },
    dialogTitle: state => {
        return state.dialogTitle
    },
    dialogMessage: state => {
        return state.dialogMessage
    },
    dialogIcon: state => {
        return state.dialogIcon
    },

    // get pagination
    getPagination: state => {
        return state.pagination
    },
}

// mutations
export const mutations = {
    // loader
    showLoader(state) {
        state.showLoader = true
    },
    hideLoader(state) {
        state.showLoader = false
    },

    // snackbar
    showSnackbar(state, data) {
        state.snackbarDuration = data.duration || 3000;
        state.snackbarMessage = data.message || 'No message.';
        state.snackbarColor = data.color || 'info';
        state.showSnackbar = true;
    },
    hideSnackbar(state) {
        state.showSnackbar = false;
    },

    // dialog
    showDialog(state, data) {
        state.dialogType = data.type || 'confirm';
        state.dialogTitle = data.title;
        state.dialogMessage = data.message;
        state.dialogIcon = data.icon || null;
        state.dialogOkCb = data.okCb || function(){};
        state.dialogCancelCb = data.cancelCb || function(){};
        state.dialogShow = true;
    },
    hideDialog(state) {
        state.dialogShow = false;
    },
    dialogOk(state) {
        state.dialogOkCb();
        state.dialogShow = false;
    },
    dialogCancel(state) {
        state.dialogCancelCb();
        state.dialogShow = false;
    },

    // pagination
    setPagination(state, page) {
        state.pagination = page;
    },
}

