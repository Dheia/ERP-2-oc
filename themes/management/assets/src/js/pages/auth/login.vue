<template>
    <div class="content">
        <v-container fluid fill-height>
            <!-- form loader -->
            <div v-if="formLoader" class="form-loader">
                <dot-loader color="#faa61a" size="80px"></dot-loader>
            </div>

            <keep-alive>
                <component :is="formState">
                    <!-- 비활성화 된 컴포넌트는 캐시 됩니다! -->
                </component>
            </keep-alive>

            <div class="form-form">
                <div class="form-form-wrap">
                    <div class="form-container">
                        <div class="form-content">
                            <h1>Login</h1>
                            <p class="signup-link">
                                계정이 없으신가요? <a href="#" @click="$router.push({name:'register'})">{{ $t('register') }}</a>
                            </p>
                            <v-form ref="form" class="text-left" v-model="valid" lazy-validation
                                    @keyup.native.enter="submit">
                                <div class="form">

                                    <v-text-field
                                        v-model="form.username"
                                        :rules="form.usernameRules"
                                        name="username"
                                        label="Username"
                                        type="text"
                                        :error-messages="form.errorMessages.username"
                                    >
                                        <template slot="prepend">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                 viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                 stroke-linecap="round" stroke-linejoin="round"
                                                 class="feather feather-user">
                                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                                <circle cx="12" cy="7" r="4"></circle>
                                            </svg>
                                        </template>
                                    </v-text-field>

                                    <v-text-field
                                        v-model="form.password"
                                        :rules="form.passwordRules"
                                        id="password"
                                        prepend-icon="lock"
                                        name="password"
                                        label="Password"
                                        type="password"
                                        :error-messages="form.errorMessages.password"
                                    >
                                        <template slot="prepend">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                 viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                 stroke-linecap="round" stroke-linejoin="round"
                                                 class="feather feather-lock">
                                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                            </svg>
                                        </template>
                                    </v-text-field>


                                    <div class="d-sm-flex justify-content-between">
                                        <div class="field-wrapper toggle-pass">
                                            <v-switch v-model="showPassword" label="show Password"></v-switch>
                                        </div>
                                        <div class="field-wrapper">
                                            <v-btn @click="submit" color="primary">{{ $t('login') }}</v-btn>
                                        </div>
                                    </div>

                                    <div class="field-wrapper text-center keep-logged-in">
                                        <div class="n-chk new-checkbox checkbox-outline-primary">
                                            <v-switch v-model="remember"
                                                      :label="$t('remember_me')"
                                                      name="remember"></v-switch>
                                        </div>
                                    </div>

                                    <div class="field-wrapper">
                                        <router-link :to="{ name: 'password.request' }" class="small ml-auto my-auto">
                                            {{ $t('forgot_password') }}
                                        </router-link>
                                    </div>
                                </div>
                            </v-form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-image">
                <div class="l-image">
                </div>
            </div>
        </v-container>
    </div>
</template>

<script>
    import DotLoader from 'vue-spinner/src/DotLoader.vue';

    export default {
        middleware: 'guest',
        layout: 'basic',

        metaInfo() {
            return {title: this.$t('login')}
        },

        components: {
            DotLoader: DotLoader
        },

        data: () => ({
            valid: true,
            form: {
                username: '',
                usernameRules: [
                    v => !!v || '아이디를 입력해주세요.',
                ],
                password: '',
                passwordRules: [
                    v => !!v || '비밀번호를 입력해주세요.',
                ],
                errorMessages: []
            },
            remember: false,
            formState: '',
            formLoader: false,
            showLoading: false,
            showPassword: false
        }),

        mounted() {
        },
        methods: {
            submit() {
                if (this.$refs.form.validate()) {
                    this.errorMessages = '';
                    this.login()
                }
            },
            async login() {

                this.formLoader = true;

                try {
                    // Submit the form.
                    const {data} = await axios.post('/api/auth/login', {
                        login: this.form.username,
                        password: this.form.password
                    });

                    if (data.token) {
                        // Save the token.
                        this.$store.dispatch('auth/saveToken', {
                            token: data.token,
                            remember: this.remember
                        });

                        // Fetch the user.
                        await this.$store.dispatch('auth/fetchUser');

                        await this.loginLoader();

                        // Redirect home.
                        await this.$router.push({name: 'home'});

                    } else {
                        this.form.errorMessages = data
                    }
                } catch (err) {
                    console.log(err)
                }

                this.formLoader = false;
            },
            loginLoader() {
                return new Promise(resolve => {
                    resolve(this.showLoading = true);
                });
            },
        }
    }
</script>

<style lang="scss" scoped>

    .form-form {
        width: 50%;
        display: flex;
        flex-direction: column;
        min-height: 100%;

        .form-form-wrap {
            max-width: 480px;
            margin: 0 auto;
            min-width: 311px;
            min-height: 100%;
            height: 100vh;
            align-items: center;
            justify-content: center;

            h1 {
                .brand-name {
                    color: var(--v-secondary-base);
                    font-weight: 600;
                }
            }

            p.signup-link {
                font-size: 14px;
                color: #e0e6ed;
                margin-bottom: 50px;

                a {
                    color: var(--v-secondary-base);
                    border-bottom: 1px solid;
                }
            }

            form .field-wrapper {
                &.input {
                    position: relative;
                    padding: 11px 0 25px 0;
                    border-bottom: none;
                }

                svg {
                    position: absolute;
                    top: 13px;
                    color: var(--v-secondary-base);
                    fill: rgba(33, 150, 243, 0.10980392156862745);
                }
            }
        }

        .form-container {
            align-items: center;
            display: flex;
            flex-grow: 1;
            padding: .71428571rem 2.85714286rem;
            width: 100%;
            min-height: 100%;

            .form-content {
                display: block;
                width: 100%;
            }

            h1 {
                color: #bfc9d4;
            }
        }
    }

    .form-image {
        display: -webkit-box;
        display: -ms-flexbox;
        display: -webkit-flex;
        display: flex;
        -webkit-flex-direction: column;
        -ms-flex-direction: column;
        flex-direction: column;
        position: fixed;
        right: 0;
        min-height: auto;
        height: 100vh;
        width: 50%;
    }
</style>
