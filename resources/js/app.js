import '../css/app.css'

import { createApp } from 'vue'

import Login from './components/auth/Login.vue'
import Register from './components/auth/Register.vue'

const path = window.location.pathname

if (path === '/login') {
    createApp(Login).mount('#auth-app')
}

if (path === '/register') {
    createApp(Register).mount('#auth-app')
}
