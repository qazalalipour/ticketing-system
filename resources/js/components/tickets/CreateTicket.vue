<script setup>
import { ref } from 'vue'
import { createTicket } from '../../services/ticket'

const emit = defineEmits(['created', 'close'])

const title = ref('')
const description = ref('')
const file = ref(null)

const loading = ref(false)
const error = ref('')
const errors = ref({})

const handleFileChange = (event) => {
    const selectedFile = event.target.files[0]

    if (!selectedFile) {
        file.value = null
        return
    }

    const allowedTypes = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/jpg',
        'image/webp',
    ]

    if (!allowedTypes.includes(selectedFile.type)) {
        error.value = 'Only PDF files and images are allowed.'
        file.value = null
        event.target.value = ''
        return
    }

    file.value = selectedFile
}

const submit = async () => {
    error.value = ''
    errors.value = {}

    if (!title.value.trim()) {
        errors.value.title = ['Title is required.']
        return
    }

    if (!description.value.trim()) {
        errors.value.description = ['Description is required.']
        return
    }

    if (!file.value) {
        error.value = 'Please select an attachment.'
        return
    }

    const formData = new FormData()

    formData.append('title', title.value)
    formData.append('description', description.value)
    formData.append('attachment', file.value)

    loading.value = true

    try {
        const response = await createTicket(formData)

        emit('created', response.data)

        title.value = ''
        description.value = ''
        file.value = null
    } catch (err) {
        error.value = err.message ?? 'Failed to create ticket.'
        errors.value = err.errors ?? {}
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <!-- Modal Overlay -->
    <div
        class="fixed inset-0 z-50 flex min-h-screen items-start justify-center overflow-y-auto bg-black/40 px-4 py-8 sm:items-center sm:py-10">
        <!-- Modal -->
        <div class="my-auto w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl sm:p-7">
            <!-- Header -->
            <div class="mb-6 flex items-start justify-between gap-4">

                <div>
                    <h2 class="text-xl font-bold text-gray-900">
                        Create New Ticket
                    </h2>

                    <p class="mt-1 text-sm leading-6 text-gray-500">
                        Submit your request to the support team.
                    </p>
                </div>

                <button type="button"
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-xl text-gray-400 transition hover:bg-gray-100 hover:text-gray-700"
                    @click="emit('close')">
                    ×
                </button>

            </div>

            <form class="space-y-5" @submit.prevent="submit">

                <!-- Title -->
                <div>
                    <label for="ticket-title" class="mb-2 block text-sm font-medium text-gray-700">
                        Title
                    </label>

                    <input id="ticket-title" v-model="title" type="text" placeholder="Enter ticket title"
                        class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none transition placeholder:text-gray-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10"
                        :class="{
                            'border-red-400': errors.title
                        }">

                    <p v-if="errors.title" class="mt-1 text-xs text-red-500">
                        {{ errors.title[0] }}
                    </p>
                </div>

                <!-- Description -->
                <div>
                    <label for="ticket-description" class="mb-2 block text-sm font-medium text-gray-700">
                        Description
                    </label>

                    <textarea id="ticket-description" v-model="description" rows="5"
                        placeholder="Enter ticket description"
                        class="w-full resize-none rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none transition placeholder:text-gray-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10"
                        :class="{
                            'border-red-400': errors.description
                        }"></textarea>

                    <p v-if="errors.description" class="mt-1 text-xs text-red-500">
                        {{ errors.description[0] }}
                    </p>
                </div>

                <!-- Attachment -->
                <div>
                    <label for="ticket-attachment" class="mb-2 block text-sm font-medium text-gray-700">
                        Attachment
                        <span class="text-red-500">*</span>
                    </label>

                    <label for="ticket-attachment"
                        class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-200 px-5 py-8 text-center transition hover:border-blue-400 hover:bg-blue-50/30">
                        <div
                            class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-blue-50 text-blue-600">
                            ↑
                        </div>

                        <span class="text-sm font-medium text-gray-700">
                            Click to select a file
                        </span>

                        <span class="mt-1 text-xs text-gray-400">
                            PDF or image
                        </span>

                        <span v-if="file" class="mt-3 max-w-full truncate text-xs font-medium text-blue-600">
                            {{ file.name }}
                        </span>
                    </label>

                    <input id="ticket-attachment" type="file" class="hidden" accept=".pdf,image/*"
                        @change="handleFileChange">
                </div>

                <!-- Error -->
                <div v-if="error"
                    class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm leading-6 text-red-600">
                    {{ error }}
                </div>

                <!-- Actions -->
                <div class="flex gap-3 pt-2">

                    <button type="button"
                        class="flex-1 rounded-xl border border-gray-200 px-4 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                        @click="emit('close')">
                        Cancel
                    </button>

                    <button type="submit" :disabled="loading"
                        class="flex-1 rounded-xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60">
                        {{ loading ? 'Creating...' : 'Create Ticket' }}
                    </button>

                </div>

            </form>
        </div>
    </div>
</template>
