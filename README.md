# Ticketing System

A simple ticketing system built with **Laravel** and **Vue.js** as a technical assignment.

The project demonstrates clean code practices, SOLID principles, Repository and Service layers, ticket workflow management, role-based authorization, background processing, and integration with a fake external web service.

## Features

### User

* Register and login.
* Authentication using Laravel Sanctum personal access tokens.
* Create a ticket with:

  * Title
  * Description
  * Required attachment (PDF or image)
* View own tickets.
* View ticket details.
* Receive notifications when a ticket is approved or rejected.

### Admin Level 1

* View tickets.
* Approve tickets waiting for Level 1 review.
* Reject tickets with a reason.
* Bulk approve multiple tickets.

When approved, the ticket status changes from:

`pending_admin_1`

to:

`pending_admin_2`

### Admin Level 2

* View tickets waiting for Level 2 review.
* Approve or reject tickets.
* Bulk approve tickets.

When approved, the ticket is sent to the fake external web service.

### External Service

A fake external API randomly returns:

* HTTP 200 (success)
* HTTP 500 (failure)

If the request fails, the system retries the operation every hour until it succeeds.

Successful and failed attempts are logged.

## Ticket Workflow

```text
User creates ticket
        |
        v
pending_admin_1
        |
        | Admin Level 1 Approves
        v
pending_admin_2
        |
        | Admin Level 2 Approves
        v
Send to External Service
        |
   +----+----+
   |         |
  200       500
   |         |
Success    Retry every hour
```

A ticket may also be rejected by the current reviewing admin with a rejection reason.

## Architecture

The project follows a layered architecture inspired by Clean Architecture.

```text
HTTP Controller
      |
      v
Service Layer
      |
      v
Repository Layer
      |
      v
Eloquent Models
```

### Layers

#### Controllers

Responsible for:

* Handling HTTP requests.
* Returning API responses.
* Authorization through Policies.

#### Services

Contain business logic such as:

* Creating tickets.
* Approving tickets.
* Rejecting tickets.
* Bulk approving tickets.
* Ticket workflow transitions.

#### Repositories

Responsible for database access.

Examples:

* Fetching paginated tickets.
* Finding tickets.
* Updating ticket status.

#### Policies

`TicketPolicy` controls access to ticket actions based on user roles and ticket status.

## Design Patterns

The project uses the following patterns:

### Repository Pattern

Used to separate persistence logic from business logic.

### Service Layer

Used to keep controllers thin and centralize business rules.

### Adapter Pattern

The external web service is accessed through an interface so the fake implementation can be replaced with a real service without changing business logic.

## Ticket States

Ticket statuses are implemented using a PHP Enum.

Possible states include:

* `pending_admin_1`
* `pending_admin_2`
* `approved`
* `rejected`
* `sent`
* `failed`

This keeps ticket transitions explicit and structured.

## Frontend

The frontend uses Vue.js mounted inside Laravel Blade views.

Main components include:

* `TicketList.vue`
* `CreateTicket.vue`
* `TicketDetails.vue`
* `AdminTicketList.vue`

Features include:

* Ticket listing.
* Pagination.
* Ticket details modal.
* Ticket creation.
* Approve and reject actions.
* Bulk approval.
* Loading and error states.
* Tailwind CSS for styling.

## Authentication

Authentication uses Laravel Sanctum personal access tokens.

After login:

* Normal users are redirected to `/tickets`.
* Admin users are redirected to `/admin/tickets`.

The frontend also provides:

* `me()` endpoint to retrieve the authenticated user.
* `logout()` endpoint to revoke the current access token.

## Default Admin Accounts

The following admin users are created using database seeders.

| Role          | Email                | Password   |
| ------------- | -------------------- | ---------- |
| Admin Level 1 | `admin1@example.com` | `password` |
| Admin Level 2 | `admin2@example.com` | `password` |

> These credentials are intended for development and testing only.

## Installation

### Requirements

* PHP 8.4+
* Composer
* MySQL
* Node.js
* npm

### Clone the repository

```bash
git clone <repository-url>
cd ticketing-system
```

### Install PHP dependencies

```bash
composer install
```

### Install frontend dependencies

```bash
npm install
```

### Environment

Copy the example environment file.

```bash
cp .env.example .env
```

Configure your database in `.env`.

Example:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ticketing_system
DB_USERNAME=root
DB_PASSWORD=
```

Generate the application key.

```bash
php artisan key:generate
```

### Run migrations and seeders

```bash
php artisan migrate --seed
```

### Build frontend assets

For development:

```bash
npm run dev
```

For production:

```bash
npm run build
```

### Start the application

```bash
php artisan serve
```

The application will be available at:

```text
http://127.0.0.1:8000
```

## Queue and Scheduler

The project uses Laravel's background processing for retrying failed external service requests.

Start the queue worker:

```bash
php artisan queue:work
```

Run the scheduler locally:

```bash
php artisan schedule:work
```

In production, the scheduler should be configured as a cron job.

## Running Tests

The project includes a feature test covering the ticket workflow.

Run all tests:

```bash
php artisan test
```

Or run the workflow test specifically:

```bash
php artisan test --filter=TicketWorkflowTest
```

The test verifies that an Admin Level 1 user can approve a ticket and that the ticket transitions to the next workflow state.

## Assumptions

* Only normal users can register through the public registration page.
* Admin users are created via seeders.
* A ticket can only be approved or rejected by the admin level responsible for its current state.
* Rejection requires a textual reason.
* Attachments are limited to images and PDF files.
* The external service is intentionally fake and randomly simulates successful and failed responses.
* Failed external service requests are retried hourly until successful.

## Technical Stack

* Laravel
* PHP
* Vue.js
* Tailwind CSS
* MySQL
* Laravel Sanctum

## Project Highlights

* Clean separation of concerns using Controller, Service, and Repository layers.
* SOLID-oriented structure.
* Role-based authorization with Policies.
* Explicit ticket workflow using Enums.
* Bulk approval functionality.
* Fake external service integration through an Adapter.
* Retry mechanism using Laravel background processing.
* Vue.js frontend loaded through Laravel Blade views.
* Automated test for ticket workflow.
