<script setup>
import { computed, onMounted, ref } from 'vue'
import { apiFetch } from '../../services/api'
import { logout } from '../../services/auth'
import { bulkApprove } from '../../services/ticket'
import TicketDetails from '../tickets/TicketDetails.vue'

const tickets = ref([])
const user = ref(null)
const selectedTickets = ref([])

const loading = ref(false)
const actionLoading = ref(null)
const error = ref('')

const showRejectModal = ref(false)
const selectedTicket = ref(null)
const rejectionReason = ref('')

const selectedTicketId = ref(null)

const currentPage = ref(1)
const perPage = ref(10)

const pagination = ref({
    currentPage: 1,
    lastPage: 1,
    total: 0,
    from: 0,
    to: 0,
})

const fetchTickets = async (page = 1) => {
    loading.value = true
    error.value = ''

    try {
        const response = await apiFetch(
            `/api/tickets?page=${page}&per_page=${perPage.value}`
        )

        tickets.value = response.data

        user.value = response.user ?? null

        pagination.value = {
            currentPage: response.meta.current_page,
            lastPage: response.meta.last_page,
            total: response.meta.total,
            from: response.meta.from,
            to: response.meta.to,
        }

        currentPage.value = response.meta.current_page
        selectedTickets.value = []
    } catch (err) {
        error.value = err.message ?? 'Failed to load tickets.'
    } finally {
        loading.value = false
    }
}

const changePage = (page) => {
    if (
        page < 1 ||
        page > pagination.value.lastPage ||
        page === currentPage.value
    ) {
        return
    }

    fetchTickets(page)
}

const changePerPage = () => {
    fetchTickets(1)
}

const approveTicket = async (ticket) => {
    actionLoading.value = ticket.id
    error.value = ''

    try {
        await apiFetch(`/api/tickets/${ticket.id}/approve`, {
            method: 'POST',
        })

        await fetchTickets(currentPage.value)
    } catch (err) {
        error.value = err.message ?? 'Failed to approve the ticket.'
    } finally {
        actionLoading.value = null
    }
}

const openRejectModal = (ticket) => {
    selectedTicket.value = ticket
    rejectionReason.value = ''
    showRejectModal.value = true
}

const rejectTicket = async () => {
    if (!rejectionReason.value.trim()) {
        return
    }

    actionLoading.value = selectedTicket.value.id
    error.value = ''

    try {
        await apiFetch(
            `/api/tickets/${selectedTicket.value.id}/reject`,
            {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    reason: rejectionReason.value,
                }),
            }
        )

        showRejectModal.value = false
        selectedTicket.value = null
        rejectionReason.value = ''

        await fetchTickets(currentPage.value)
    } catch (err) {
        error.value = err.message ?? 'Failed to reject the ticket.'
    } finally {
        actionLoading.value = null
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
    pending_admin_1: 'Pending Level 1 Review',
    pending_admin_2: 'Pending Level 2 Review',
    approved: 'Approved',
    rejected: 'Rejected',
    sent: 'Sent',
    failed: 'Failed',
}

const handleLogout = async () => {
    try {
        await logout()
    } catch (error) {
        console.error(error)
    } finally {
        localStorage.removeItem('token')

        window.location.href = '/login'
    }
}

const toggleTicket = (ticketId) => {
    if (selectedTickets.value.includes(ticketId)) {
        selectedTickets.value = selectedTickets.value.filter(
            id => id !== ticketId
        )

        return
    }

    selectedTickets.value.push(ticketId)
}

const selectableTickets = computed(() => {
    return tickets.value.filter(
        ticket =>
            ticket.status === 'pending_admin_1' ||
            ticket.status === 'pending_admin_2'
    )
})

const allSelectableSelected = computed(() => {
    return (
        selectableTickets.value.length > 0 &&
        selectableTickets.value.every(ticket =>
            selectedTickets.value.includes(ticket.id)
        )
    )
})

const toggleSelectAll = () => {
    if (!selectableTickets.value.length) {
        return
    }

    if (allSelectableSelected.value) {
        selectedTickets.value = selectedTickets.value.filter(
            id =>
                !selectableTickets.value.some(
                    ticket => ticket.id === id
                )
        )

        return
    }

    selectedTickets.value = [
        ...new Set([
            ...selectedTickets.value,
            ...selectableTickets.value.map(ticket => ticket.id),
        ]),
    ]
}

const handleBulkApprove = async () => {
    if (!selectedTickets.value.length) {
        return
    }

    loading.value = true
    error.value = ''

    try {
        await bulkApprove(selectedTickets.value)

        selectedTickets.value = []

        await fetchTickets(currentPage.value)
    } catch (err) {
        error.value = err.message ?? 'Bulk approval failed.'
    } finally {
        loading.value = false
    }
}

const openTicket = (ticketId) => {
    selectedTicketId.value = ticketId
}

onMounted(() => {
    fetchTickets()
})
</script>

<template>
    <div class="min-h-screen bg-gray-50">

        <!-- Header -->
        <header class="border-b border-gray-200 bg-white">
            <div
                class="mx-auto flex max-w-7xl items-center justify-between px-6 py-5"
            >
                <div>
                    <h1 class="text-xl font-bold text-gray-900">
                        Ticket Management
                    </h1>

                    <p class="mt-1 text-sm text-gray-500">
                        Review and manage user support requests
                    </p>
                </div>

                <button
                    type="button"
                    class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                    @click="handleLogout"
                >
                    Logout
                </button>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-6 py-8">

            <!-- Error -->
            <div
                v-if="error"
                class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-600"
            >
                {{ error }}
            </div>

            <!-- Toolbar -->
            <div
                v-if="!loading && tickets.length"
                class="mb-6 flex flex-col gap-4 rounded-2xl border border-gray-200 bg-white p-5 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <p class="text-sm font-semibold text-gray-900">
                        {{ tickets.length }} tickets
                    </p>

                    <p
                        v-if="selectedTickets.length"
                        class="mt-1 text-xs text-gray-500"
                    >
                        {{ selectedTickets.length }} tickets selected
                    </p>
                </div>

                <div class="flex gap-3">

                    <!-- Select All -->
                    <button
                        type="button"
                        :disabled="!selectableTickets.length"
                        class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50"
                        @click="toggleSelectAll"
                    >
                        {{
                            allSelectableSelected
                                ? 'Deselect All'
                                : 'Select All'
                        }}
                    </button>

                    <!-- Bulk Approve -->
                    <button
                        v-if="selectedTickets.length"
                        type="button"
                        :disabled="loading"
                        class="rounded-xl bg-green-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-50"
                        @click="handleBulkApprove"
                    >
                        <span v-if="loading">
                            Processing...
                        </span>

                        <span v-else>
                            Bulk Approve
                            ({{ selectedTickets.length }})
                        </span>
                    </button>

                </div>
            </div>

            <!-- Loading -->
            <div
                v-if="loading && !tickets.length"
                class="rounded-2xl border border-gray-200 bg-white p-12 text-center"
            >
                <div
                    class="mx-auto h-8 w-8 animate-spin rounded-full border-4 border-gray-200 border-t-blue-600"
                ></div>

                <p class="mt-4 text-sm text-gray-500">
                    Loading tickets...
                </p>
            </div>

            <!-- Empty -->
            <div
                v-else-if="!loading && tickets.length === 0"
                class="rounded-2xl border border-gray-200 bg-white p-12 text-center"
            >
                <div
                    class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-gray-100"
                >
                    <svg
                        class="h-7 w-7 text-gray-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.5"
                            d="M9 12h6m-6 4h4m5-10v12a2 2 0 01-2 2H8a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2z"
                        />
                    </svg>
                </div>

                <h2 class="mt-4 font-semibold text-gray-900">
                    No tickets available for review.
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    There are currently no tickets to review.
                </p>
            </div>

            <!-- Tickets -->
            <div v-else class="space-y-4">

                <article
                    v-for="ticket in tickets"
                    :key="ticket.id"
                    class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition hover:shadow-md"
                >
                    <div
                        class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between"
                    >

                        <!-- Ticket Info -->
                        <div class="flex min-w-0 flex-1 gap-4">

                            <!-- Checkbox -->
                            <div
                                v-if="
                                    ticket.status === 'pending_admin_1' ||
                                    ticket.status === 'pending_admin_2'
                                "
                                class="pt-1"
                            >
                                <input
                                    type="checkbox"
                                    :checked="selectedTickets.includes(ticket.id)"
                                    class="h-5 w-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    @change="toggleTicket(ticket.id)"
                                >
                            </div>

                            <div class="min-w-0 flex-1">

                                <!-- ID + Status -->
                                <div
                                    class="mb-3 flex flex-wrap items-center gap-2"
                                >
                                    <span
                                        class="text-xs font-medium text-gray-400"
                                    >
                                        #{{ ticket.id }}
                                    </span>

                                    <span
                                        class="rounded-full px-3 py-1 text-xs font-medium"
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

                                <!-- Title -->
                                <h2 class="text-lg font-bold text-gray-900">
                                    {{ ticket.title }}
                                </h2>

                                <!-- Description -->
                                <p
                                    class="mt-2 line-clamp-2 text-sm leading-7 text-gray-500"
                                >
                                    {{ ticket.description }}
                                </p>

                                <!-- Date -->
                                <p
                                    v-if="ticket.created_at"
                                    class="mt-3 text-xs text-gray-400"
                                >
                                    {{ ticket.created_at }}
                                </p>

                                <!-- Rejection Reason -->
                                <div
                                    v-if="ticket.rejection_reason"
                                    class="mt-4 rounded-xl border border-red-100 bg-red-50 p-4"
                                >
                                    <p
                                        class="text-xs font-semibold text-red-500"
                                    >
                                        Rejection Reason
                                    </p>

                                    <p
                                        class="mt-1 text-sm leading-6 text-red-700"
                                    >
                                        {{ ticket.rejection_reason }}
                                    </p>
                                </div>

                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex shrink-0 flex-wrap gap-2">

                            <!-- View -->
                            <button
                                type="button"
                                class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                                @click="openTicket(ticket.id)"
                            >
                                View
                            </button>

                            <!-- Approve -->
                            <button
                                v-if="
                                    ticket.status === 'pending_admin_1' ||
                                    ticket.status === 'pending_admin_2'
                                "
                                type="button"
                                :disabled="actionLoading === ticket.id"
                                class="rounded-xl bg-green-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-50"
                                @click="approveTicket(ticket)"
                            >
                                {{
                                    actionLoading === ticket.id
                                        ? 'Processing...'
                                        : 'Approve'
                                }}
                            </button>

                            <!-- Reject -->
                            <button
                                v-if="
                                    ticket.status === 'pending_admin_1' ||
                                    ticket.status === 'pending_admin_2'
                                "
                                type="button"
                                :disabled="actionLoading === ticket.id"
                                class="rounded-xl bg-red-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50"
                                @click="openRejectModal(ticket)"
                            >
                                Reject
                            </button>

                        </div>

                    </div>
                </article>

            </div>

            <!-- Pagination -->
            <div
                v-if="!loading && tickets.length && pagination.lastPage > 1"
                class="mt-6 flex flex-col gap-4 rounded-xl border border-gray-200 bg-white p-4 sm:flex-row sm:items-center sm:justify-between"
            >

                <!-- Info -->
                <div class="text-sm text-gray-500">
                    Showing
                    <span class="font-semibold text-gray-900">
                        {{ pagination.from }}
                    </span>
                    to
                    <span class="font-semibold text-gray-900">
                        {{ pagination.to }}
                    </span>
                    of
                    <span class="font-semibold text-gray-900">
                        {{ pagination.total }}
                    </span>
                    tickets
                </div>

                <!-- Controls -->
                <div class="flex items-center gap-2">

                    <!-- Previous -->
                    <button
                        type="button"
                        :disabled="currentPage === 1 || loading"
                        @click="changePage(currentPage - 1)"
                        class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40"
                    >
                        Previous
                    </button>

                    <!-- Page -->
                    <div
                        class="rounded-lg bg-gray-50 px-4 py-2 text-sm text-gray-600"
                    >
                        Page
                        <span class="font-semibold text-gray-900">
                            {{ currentPage }}
                        </span>
                        of
                        <span class="font-semibold text-gray-900">
                            {{ pagination.lastPage }}
                        </span>
                    </div>

                    <!-- Next -->
                    <button
                        type="button"
                        :disabled="
                            currentPage === pagination.lastPage ||
                            loading
                        "
                        @click="changePage(currentPage + 1)"
                        class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40"
                    >
                        Next
                    </button>

                </div>

                <!-- Per Page -->
                <div class="flex items-center gap-2">

                    <label
                        for="adminPerPage"
                        class="text-sm text-gray-500"
                    >
                        Per page:
                    </label>

                    <select
                        id="adminPerPage"
                        v-model="perPage"
                        @change="changePerPage"
                        class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500"
                    >
                        <option :value="10">10</option>
                        <option :value="20">20</option>
                        <option :value="50">50</option>
                    </select>

                </div>

            </div>

        </main>

        <!-- Ticket Details -->
        <TicketDetails
            v-if="selectedTicketId"
            :ticket-id="selectedTicketId"
            @close="selectedTicketId = null"
        />

        <!-- Reject Modal -->
        <div
            v-if="showRejectModal"
            class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/40 p-4"
        >
            <div
                class="my-8 w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl"
            >

                <!-- Modal Header -->
                <div class="flex items-start justify-between">

                    <div>
                        <h2 class="text-lg font-bold text-gray-900">
                            Reject Ticket
                        </h2>

                        <p class="mt-1 text-sm text-gray-500">
                            Please provide a reason for rejecting this ticket.
                        </p>
                    </div>

                    <button
                        type="button"
                        class="flex h-9 w-9 items-center justify-center rounded-lg text-xl text-gray-400 transition hover:bg-gray-100 hover:text-gray-700"
                        @click="showRejectModal = false"
                    >
                        ×
                    </button>

                </div>

                <!-- Reason -->
                <div class="mt-5">

                    <label
                        for="rejection-reason"
                        class="mb-2 block text-sm font-medium text-gray-700"
                    >
                        Rejection Reason
                    </label>

                    <textarea
                        id="rejection-reason"
                        v-model="rejectionReason"
                        rows="5"
                        placeholder="Enter the reason for rejecting this ticket..."
                        class="w-full resize-none rounded-xl border border-gray-200 px-4 py-3 text-sm leading-6 outline-none transition placeholder:text-gray-400 focus:border-red-500 focus:ring-4 focus:ring-red-500/10"
                    ></textarea>

                    <p
                        v-if="!rejectionReason.trim()"
                        class="mt-2 text-xs text-gray-400"
                    >
                        A rejection reason is required.
                    </p>

                </div>

                <!-- Modal Actions -->
                <div class="mt-6 flex gap-3">

                    <button
                        type="button"
                        class="flex-1 rounded-xl border border-gray-200 px-4 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                        @click="showRejectModal = false"
                    >
                        Cancel
                    </button>

                    <button
                        type="button"
                        :disabled="
                            !rejectionReason.trim() ||
                            actionLoading === selectedTicket?.id
                        "
                        class="flex-1 rounded-xl bg-red-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50"
                        @click="rejectTicket"
                    >
                        {{
                            actionLoading === selectedTicket?.id
                                ? 'Processing...'
                                : 'Reject Ticket'
                        }}
                    </button>

                </div>

            </div>
        </div>

    </div>
</template>
