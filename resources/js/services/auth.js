import { apiFetch } from './api'

export function login(payload) {
    return apiFetch('/api/login', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(payload),
    })
}

export function register(payload) {
    return apiFetch('/api/register', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(payload),
    })
}

export function me() {
    return apiFetch('/api/me')
}

export function logout() {
    return apiFetch('/api/logout', {
        method: 'POST',
    })
}
