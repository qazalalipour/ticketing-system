<script setup>
import { reactive, ref } from 'vue'
import { login, me } from '../../services/auth'

const form = reactive({
    email: '',
    password: '',
})

const loading = ref(false)
const error = ref('')
const errors = ref({})

const submit = async () => {
    loading.value = true
    error.value = ''
    errors.value = {}

    try {
        const data = await login(form)
        localStorage.setItem('token', data.token)
        const userResponse = await me()
        const user = userResponse.user
        if (
            user.role === 'admin_level_1' ||
            user.role === 'admin_level_2'
        ) {
            window.location.href = '/admin/tickets'
        } else {
            window.location.href = '/tickets'
        }
    } catch (err) {
        error.value = err.message ?? 'Login failed.'
        errors.value = err.errors ?? {}
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <main
        class="flex min-h-screen items-center justify-center bg-[#f5f7fb] px-4 py-6"
    >
        <div
            class="w-full max-w-[440px] rounded-[20px] border border-[#e8ebf2] bg-white p-7 shadow-[0_20px_50px_rgba(25,42,70,0.08)] sm:p-10"
        >
            <!-- Header -->
            <div class="mb-8 text-center">

                <div
                    class="mx-auto mb-[18px] flex h-[52px] w-[52px] items-center justify-center rounded-[14px] bg-[#4179f0] text-[22px] font-bold text-white shadow-[0_10px_25px_rgba(65,121,240,0.25)]"
                >
                    T
                </div>

                <h1 class="mb-2 text-[23px] font-bold text-[#172033] sm:text-[26px]">
                    Welcome Back
                </h1>

                <p class="text-sm leading-7 text-[#7a8499]">
                    Enter your credentials to access your account.
                </p>
            </div>

            <!-- Form -->
            <form
                class="flex flex-col gap-5"
                @submit.prevent="submit"
            >
                <!-- Email -->
                <div class="flex flex-col gap-2">

                    <label
                        for="email"
                        class="text-sm font-semibold text-[#30394d]"
                    >
                        Email
                    </label>

                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        placeholder="example@email.com"
                        autocomplete="email"
                        class="h-12 w-full rounded-[10px] border bg-white px-[15px] text-[#172033] outline-none transition placeholder:text-[#a5adbc] focus:border-[#4179f0] focus:ring-4 focus:ring-[#4179f0]/10"
                        :class="
                            errors.email
                                ? 'border-[#e5484d]'
                                : 'border-[#dfe4ed]'
                        "
                    >

                    <p
                        v-if="errors.email"
                        class="text-xs text-[#e5484d]"
                    >
                        {{ errors.email[0] }}
                    </p>
                </div>

                <!-- Password -->
                <div class="flex flex-col gap-2">

                    <label
                        for="password"
                        class="text-sm font-semibold text-[#30394d]"
                    >
                        Password
                    </label>

                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        class="h-12 w-full rounded-[10px] border bg-white px-[15px] text-[#172033] outline-none transition placeholder:text-[#a5adbc] focus:border-[#4179f0] focus:ring-4 focus:ring-[#4179f0]/10"
                        :class="
                            errors.password
                                ? 'border-[#e5484d]'
                                : 'border-[#dfe4ed]'
                        "
                    >

                    <p
                        v-if="errors.password"
                        class="text-xs text-[#e5484d]"
                    >
                        {{ errors.password[0] }}
                    </p>
                </div>

                <!-- General Error -->
                <div
                    v-if="error"
                    class="rounded-[10px] border border-[#f3b8bb] bg-[#fff3f3] px-3.5 py-3 text-[13px] leading-6 text-[#d92d35]"
                >
                    {{ error }}
                </div>

                <!-- Submit -->
                <button
                    type="submit"
                    :disabled="loading"
                    class="h-12 w-full rounded-[10px] bg-[#4179f0] text-sm font-semibold text-white shadow-sm transition hover:bg-[#3168df] hover:shadow-[0_8px_20px_rgba(65,121,240,0.2)] active:translate-y-px disabled:cursor-not-allowed disabled:opacity-65"
                >
                    {{ loading ? 'Signing in...' : 'Sign In' }}
                </button>
            </form>

            <!-- Footer -->
            <div class="mt-[26px] text-center text-[13px] text-[#7a8499]">
                Don't have an account?

                <a
                    href="/register"
                    class="font-semibold text-[#4179f0] hover:underline"
                >
                    Create an account
                </a>
            </div>
        </div>
    </main>
</template>
