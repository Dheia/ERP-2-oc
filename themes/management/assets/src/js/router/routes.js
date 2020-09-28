const Welcome = () => import('~/pages/welcome').then(m => m.default || m);
const Login = () => import('~/pages/auth/login').then(m => m.default || m);
const Register = () => import('~/pages/auth/register').then(m => m.default || m);
const PasswordEmail = () => import('~/pages/auth/password/email').then(m => m.default || m);
const PasswordReset = () => import('~/pages/auth/password/reset').then(m => m.default || m);
const NotFound = () => import('~/pages/errors/404').then(m => m.default || m);

const Home = () => import('~/pages/home').then(m => m.default || m);
const Settings = () => import('~/pages/settings/index').then(m => m.default || m);
const SettingsProfile = () => import('~/pages/settings/profile').then(m => m.default || m);
const SettingsPassword = () => import('~/pages/settings/password').then(m => m.default || m);

const Sign = () => import('~/pages/signs/index').then(m => m.default || m);
const SignLists = () => import('~/pages/signs/SignLists').then(m => m.default || m);
const SignView = () => import('~/pages/signs/SignView').then(m => m.default || m);
const SignFormAdd = () => import('~/pages/signs/SignFormAdd').then(m => m.default || m);
const SignFormEdit = () => import('~/pages/signs/SignFormEdit').then(m => m.default || m);

export default [
    {path: '/', name: 'welcome', component: Welcome},

    {path: '/login', name: 'login', component: Login},
    {path: '/register', name: 'register', component: Register},
    {path: '/password/reset', name: 'password.request', component: PasswordEmail},
    {path: '/password/reset/:token', name: 'password.reset', component: PasswordReset},

    {path: '/home', name: 'home', component: Home},
    {
        path: '/settings',
        component: Settings,
        children: [
            {path: '', redirect: {name: 'settings.profile'}},
            {path: 'profile', name: 'settings.profile', component: SettingsProfile},
            {path: 'password', name: 'settings.password', component: SettingsPassword}
        ]
    },
    {
        path: '/signs',
        name: 'signs',
        component: Sign,
        redirect: {name: 'signs.list'},
        children: [
            {
                path: 'all',
                name: 'signs.list',
                component: SignLists
            },
            {
                path: 'all/:id',
                name: 'signs.view',
                component: SignView,
                props: (route) => ({propSignId: route.params.id}),
            },
            {
                path: 'not',
                name: 'signs.list2',
                component: SignLists
            },
            {
                path: 'not/:id',
                name: 'signs.view',
                component: SignView,
                props: (route) => ({propSignId: route.params.id}),
            },
            {
                path: 'create',
                name: 'signs.create',
                component: SignFormAdd
            },
            {
                path: 'edit/:id',
                name: 'signs.edit',
                component: SignFormEdit,
                props: (route) => ({propSignId: route.params.id}),
            }
        ]
    },

    {path: '*', component: NotFound}
]
