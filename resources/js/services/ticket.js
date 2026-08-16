import { apiFetch } from './api'

export function createTicket(formData) {
    return apiFetch('/api/tickets', {
        method: 'POST',
        body: formData,
    })
}

export function getTickets() {
    return apiFetch('/api/tickets')
}

export function bulkApprove(ticketIds) {
    return apiFetch('/api/tickets/bulk-approve', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            ticket_ids: ticketIds,
        }),
    })
}
