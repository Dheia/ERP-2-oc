import store from '~/store'
import swal from 'sweetalert2'

export default (to, from, next) => {
    if (store.getters['auth/user'].has_groups.indexOf('seller') === -1) {
        swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: '접근 권한이 없습니다!',
        })
        next({name: 'home'})
    } else {
        next()
    }
}
