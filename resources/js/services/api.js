export async function apiFetch(url, options = {}) {
    const token = localStorage.getItem('token')

    const headers = {
        Accept: 'application/json',
        ...options.headers,
    }

    if (token) {
        headers.Authorization = `Bearer ${token}`
    }

    const response = await fetch(url, {
        ...options,
        headers,
    })

    const data = await response.json()

    if (!response.ok) {
        throw data
    }

    return data
}
