<script setup>
import { onMounted, ref } from 'vue'
import { apiFetch } from '../../services/api'

const props = defineProps({
    ticketId: {
        type: [Number, String],
        required: true,
    },
})

const emit = defineEmits(['close'])

const ticket = ref(null)
const loading = ref(true)
const error = ref('')

const fetchTicket = async () => {
    try {
        const response = await apiFetch(`/api/tickets/${props.ticketId}`)
        ticket.value = response.data
    } catch (err) {
        error.value = err.message ?? 'Failed to load ticket details.'
    } finally {
        loading.value = false
    }
}

const statusClasses = {
    pending_admin_1: 'bg-amber-50 text-amber-700',
    pending_admin_2: 'bg-blue-50 text-blue-700',
    approved: 'bg-green-50 text-green-700',
    rejected: 'bg-red-50 text-red-700',
    sent: 'bg-purple-50 text-purple-700',
    failed: 'bg-red-50 text-red-700',
}

const statusLabels = {
    pending_admin_1: 'Pending Admin Level 1',
    pending_admin_2: 'Pending Admin Level 2',
    approved: 'Approved',
    rejected: 'Rejected',
    sent: 'Sent',
    failed: 'Failed',
}

onMounted(fetchTicket)
</script>

<template>
    <div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/40 px-4 py-8 sm:items-center sm:py-10">

        <div class="my-auto w-full max-w-2xl rounded-2xl bg-white shadow-xl">

            <!-- Header -->

            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-5">

                <div>
                    <h2 class="text-lg font-bold text-gray-900">
                        Ticket Details
                    </h2>

                    <p v-if="ticket" class="mt-1 text-xs text-gray-400">
                        #{{ ticket.id }}
                    </p>
                </div>

                <button
                    type="button"
                    class="flex h-9 w-9 items-center justify-center rounded-lg text-xl text-gray-400 transition hover:bg-gray-100 hover:text-gray-700"
                    @click="emit('close')"
                >
                    ×
                </button>

            </div>

            <!-- Loading -->

            <div
                v-if="loading"
                class="p-10 text-center text-sm text-gray-500"
            >
                Loading ticket details...
            </div>

            <!-- Error -->

            <div
                v-else-if="error"
                class="m-6 rounded-xl bg-red-50 p-4 text-sm text-red-600"
            >
                {{ error }}
            </div>

            <!-- Content -->

            <div
                v-else-if="ticket"
                class="space-y-6 p-6"
            >

                <!-- Title -->

                <div>
                    <p class="mb-2 text-xs text-gray-400">
                        Title
                    </p>

                    <h3 class="text-lg font-semibold text-gray-900">
                        {{ ticket.title }}
                    </h3>
                </div>

                <!-- Status -->

                <div>
                    <p class="mb-2 text-xs text-gray-400">
                        Status
                    </p>

                    <span
                        class="inline-flex rounded-full px-3 py-1 text-xs font-medium"
                        :class="
                            statusClasses[ticket.status]
                            ?? 'bg-gray-100 text-gray-700'
                        "
                    >
                        {{
                            statusLabels[ticket.status]
                            ?? ticket.status
                        }}
                    </span>
                </div>

                <!-- Description -->

                <div>
                    <p class="mb-2 text-xs text-gray-400">
                        Description
                    </p>

                    <div class="rounded-xl bg-gray-50 p-4 text-sm leading-7 text-gray-700">
                        {{ ticket.description }}
                    </div>
                </div>

                <!-- Attachment -->

                <div v-if="ticket.attachment">
                    <p class="mb-2 text-xs text-gray-400">
                        Attachment
                    </p>

                    <a
                        :href="ticket.attachment[0].file_path"
                        target="_blank"
                        class="inline-flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-blue-600 transition hover:bg-blue-50"
                    >
                        View Attachment
                    </a>
                </div>

                <!-- Rejection -->

                <div
                    v-if="ticket.rejection_reason"
                    class="rounded-xl border border-red-100 bg-red-50 p-4"
                >
                    <p class="mb-1 text-xs font-medium text-red-500">
                        Rejection Reason
                    </p>

                    <p class="text-sm leading-6 text-red-700">
                        {{ ticket.rejection_reason }}
                    </p>
                </div>

                <!-- Date -->

                <div class="border-t border-gray-100 pt-4 text-xs text-gray-400">
                    Created at:
                    {{ ticket.created_at }}
                </div>

            </div>

            <!-- Footer -->

            <div class="border-t border-gray-100 px-6 py-4">

                <button
                    type="button"
                    class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                    @click="emit('close')"
                >
                    Close
                </button>

            </div>

        </div>

    </div>
</template>
