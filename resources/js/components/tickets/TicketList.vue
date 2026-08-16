<script setup>
import { onMounted, ref } from 'vue'
import { apiFetch } from '../../services/api'
import CreateTicket from './CreateTicket.vue'
import TicketDetails from './TicketDetails.vue'
import { logout } from '../../services/auth'

const tickets = ref([])
const loading = ref(false)
const error = ref('')

const showCreateTicket = ref(false)
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

        pagination.value = {
            currentPage: response.meta.current_page,
            lastPage: response.meta.last_page,
            total: response.meta.total,
            from: response.meta.from,
            to: response.meta.to,
        }

        currentPage.value = response.meta.current_page
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

const statusClasses = (status) => {
    const classes = {
        pending_admin_1: 'bg-amber-50 text-amber-700',
        pending_admin_2: 'bg-blue-50 text-blue-700',
        approved: 'bg-green-50 text-green-700',
        rejected: 'bg-red-50 text-red-700',
        sent: 'bg-purple-50 text-purple-700',
        failed: 'bg-red-50 text-red-700',
    }

    return classes[status] ?? 'bg-gray-100 text-gray-700'
}

const statusLabels = {
    pending_admin_1: 'Pending Admin Level 1',
    pending_admin_2: 'Pending Admin Level 2',
    approved: 'Approved',
    rejected: 'Rejected',
    sent: 'Sent',
    failed: 'Failed',
}

const handleTicketCreated = (ticket) => {
    tickets.value.unshift(ticket)

    showCreateTicket.value = false
}

const openTicket = (ticketId) => {
    selectedTicketId.value = ticketId
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

onMounted(() => {
    fetchTickets()
})
</script>

<template>
    <div class="min-h-screen bg-gray-50">

        <!-- Header -->
        <header class="border-b border-gray-200 bg-white">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">

                <div>
                    <h1 class="text-xl font-bold text-gray-900">
                        Ticketing System
                    </h1>

                    <p class="mt-1 text-sm text-gray-500">
                        Support ticket management
                    </p>
                </div>

                <button
                    type="button"
                    @click="handleLogout"
                    class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                >
                    Logout
                </button>

            </div>
        </header>

        <!-- Content -->
        <main class="mx-auto max-w-7xl px-6 py-8">

            <!-- Title -->
            <div class="mb-6 flex items-center justify-between">

                <div>
                    <h2 class="text-2xl font-bold text-gray-900">
                        My Tickets
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        List of your submitted tickets
                    </p>
                </div>

                <button
                    type="button"
                    @click="showCreateTicket = true"
                    class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700"
                >
                    + Create Ticket
                </button>

            </div>

            <!-- Pagination Info -->
            <div
                v-if="!loading && tickets.length"
                class="mb-4 flex flex-col gap-3 rounded-xl border border-gray-200 bg-white p-4 sm:flex-row sm:items-center sm:justify-between"
            >

                <p class="text-sm text-gray-500">
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
                </p>

                <div class="flex items-center gap-2">

                    <label for="perPage" class="text-sm text-gray-500">
                        Per page:
                    </label>

                    <select
                        id="perPage"
                        v-model="perPage"
                        @change="changePerPage"
                        class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500"
                    >
                        <option :value="10">
                            10
                        </option>

                        <option :value="20">
                            20
                        </option>

                        <option :value="50">
                            50
                        </option>
                    </select>

                </div>

            </div>

            <!-- Loading -->
            <div
                v-if="loading"
                class="rounded-xl border border-gray-200 bg-white p-10 text-center"
            >

                <div
                    class="mx-auto mb-3 h-8 w-8 animate-spin rounded-full border-4 border-gray-200 border-t-blue-600"
                ></div>

                <p class="text-sm text-gray-500">
                    Loading tickets...
                </p>

            </div>

            <!-- Error -->
            <div
                v-else-if="error"
                class="rounded-xl border border-red-200 bg-red-50 p-5 text-sm text-red-700"
            >
                {{ error }}
            </div>

            <!-- Empty -->
            <div
                v-else-if="tickets.length === 0"
                class="rounded-xl border border-gray-200 bg-white p-12 text-center"
            >

                <div
                    class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 text-xl"
                >
                    🎫
                </div>

                <h3 class="font-semibold text-gray-900">
                    No tickets found
                </h3>

                <p class="mt-2 text-sm text-gray-500">
                    Create your first support ticket.
                </p>

            </div>

            <!-- Tickets -->
            <div v-else class="space-y-4">

                <div
                    v-for="ticket in tickets"
                    :key="ticket.id"
                    class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:shadow-md"
                >

                    <div class="flex items-start justify-between gap-4">

                        <div class="min-w-0">

                            <div class="mb-2 flex items-center gap-3">

                                <span class="text-xs font-medium text-gray-400">
                                    #{{ ticket.id }}
                                </span>

                                <span
                                    class="rounded-full px-3 py-1 text-xs font-medium"
                                    :class="statusClasses(ticket.status)"
                                >
                                    {{ statusLabels[ticket.status] ?? ticket.status }}
                                </span>

                            </div>

                            <h3 class="text-base font-semibold text-gray-900">
                                {{ ticket.title }}
                            </h3>

                            <p class="mt-2 line-clamp-2 text-sm leading-6 text-gray-500">
                                {{ ticket.description }}
                            </p>

                        </div>

                        <button
                            type="button"
                            @click="openTicket(ticket.id)"
                            class="shrink-0 rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                        >
                            View
                        </button>

                    </div>

                    <div
                        class="mt-4 flex items-center justify-between border-t border-gray-100 pt-4"
                    >

                        <span class="text-xs text-gray-400">
                            {{ ticket.created_at }}
                        </span>

                        <span
                            v-if="ticket.rejection_reason"
                            class="text-xs text-red-500"
                        >
                            Rejection reason:
                            {{ ticket.rejection_reason }}
                        </span>

                    </div>

                </div>

                <!-- Pagination -->
                <div
                    v-if="pagination.lastPage > 1"
                    class="mt-6 flex items-center justify-between rounded-xl border border-gray-200 bg-white p-4"
                >

                    <button
                        type="button"
                        :disabled="currentPage === 1 || loading"
                        @click="changePage(currentPage - 1)"
                        class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40"
                    >
                        Previous
                    </button>

                    <div class="text-sm text-gray-500">

                        Page

                        <span class="font-semibold text-gray-900">
                            {{ currentPage }}
                        </span>

                        of

                        <span class="font-semibold text-gray-900">
                            {{ pagination.lastPage }}
                        </span>

                    </div>

                    <button
                        type="button"
                        :disabled="
                            currentPage === pagination.lastPage || loading
                        "
                        @click="changePage(currentPage + 1)"
                        class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40"
                    >
                        Next
                    </button>

                </div>

            </div>

        </main>

        <!-- Create Ticket -->
        <CreateTicket
            v-if="showCreateTicket"
            @close="showCreateTicket = false"
            @created="handleTicketCreated"
        />

        <!-- Ticket Details -->
        <TicketDetails
            v-if="selectedTicketId"
            :ticket-id="selectedTicketId"
            @close="selectedTicketId = null"
        />

    </div>
</template>
