# Event Planner

A full-stack web application for organizing events and managing invitations between registered users, built with a Laravel REST API backend and a React SPA frontend.

## Overview

Event Planner allows users to create activities, build private contact lists, and invite other registered users to their events. Invited users can accept or decline invitations.

This project was built as a learning exercise to practice modern full-stack development: Docker-based environments, REST API design with Laravel, and a React SPA with token-based authentication.

## Features

- User registration and authentication via **Laravel Sanctum** (token-based)
- Create and manage **contact lists** (private, members are unaware they are listed)
- Create **activities** with title, location, date/time, and notes
- **Invite users** to activities — individually or by selecting entire contact lists
- Accept or decline invitations
- Role-aware responses (`organizer` / `invited`) on activity endpoints
- Authorization via **Laravel Policies**
- Soft deletes on lists and activities
- React SPA with protected routes, persistent login via localStorage, and full CRUD UI

## Tech Stack

### Backend
- **PHP 8.2**, Laravel 11
- **MySQL 8**
- **Laravel Sanctum** (token-based auth)

### Frontend
- **React 19** (JSX), Vite
- **React Router** (client-side routing with protected routes)
- **Axios** (with request interceptors for token injection)

### Infrastructure
- **Docker / Docker Compose** (PHP-FPM, Nginx, MySQL)
- **VPS:** Hetzner Ubuntu 24
- **Version control:** Git, GitHub

## Project Structure

```
event-planner/
├── docker/
│   ├── nginx/              # Nginx configuration
│   └── php/                # PHP Dockerfile
├── docker-compose.yml      # Container orchestration
└── src/                    # Laravel application
    ├── app/
    │   ├── Http/
    │   │   ├── Controllers/Api/
    │   │   └── Resources/
    │   ├── Models/
    │   └── Policies/
    ├── database/migrations/
    ├── resources/
    │   └── js/
    │       ├── api/            # Axios instances
    │       ├── Components/     # Reusable components
    │       ├── Context/        # AuthContext + AuthProvider
    │       ├── Hooks/          # useAuth, useAxiosPrivate
    │       └── Pages/          # Auth, ContactList, Activities, Home
    └── routes/
        ├── api.php
        └── web.php             # Catch-all for React Router
```

## Database Schema

| Table | Description |
|---|---|
| `users` | Registered users |
| `contact_lists` | Private lists owned by a user |
| `contact_list_members` | Members of each contact list |
| `activities` | Events created by users |
| `invitations` | Invitations with status: `pending`, `accepted`, `declined` |

## Getting Started

### Requirements

- Docker
- Docker Compose
- Node.js + npm (for frontend development)

### Setup

1. Clone the repository

```bash
git clone git@github.com:lbortolon/event-planner.git
cd event-planner
```

2. Create environment files

```bash
# Root .env for Docker (DB credentials)
cp .env.example .env

# Laravel .env
cp src/.env.example src/.env
```

Edit both files with your credentials. In `src/.env`, make sure to set:

```
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=event_planner
DB_USERNAME=laravel
DB_PASSWORD=your_password

VITE_API_URL=http://localhost/api
```

3. Start the containers

```bash
docker compose up -d --build
```

4. Install PHP dependencies and run migrations

```bash
docker exec -it event_app bash
composer install
php artisan key:generate
php artisan migrate
exit
```

5. Fix storage permissions

```bash
docker exec -it event_app bash
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
exit
```

6. Install frontend dependencies and start Vite

```bash
cd src
npm install
npm run dev
```

The application is now available at `http://localhost`.

## API Endpoints

All protected endpoints require the header:
```
Authorization: Bearer <token>
```

### Auth

| Method | Endpoint | Description | Auth |
|---|---|---|---|
| POST | `/api/register` | Register a new user | No |
| POST | `/api/login` | Login and receive token | No |
| POST | `/api/logout` | Invalidate current token | Yes |
| GET | `/api/user` | Get authenticated user | Yes |

### Users

| Method | Endpoint | Description | Auth |
|---|---|---|---|
| GET | `/api/users?q=<string>` | Search users by email (partial match) | Yes |

### Contact Lists

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/contact-lists` | List all my contact lists |
| POST | `/api/contact-lists` | Create a new list |
| GET | `/api/contact-lists/{id}` | Get list detail with members |
| PUT | `/api/contact-lists/{id}` | Rename a list |
| DELETE | `/api/contact-lists/{id}` | Soft delete a list |
| POST | `/api/contact-lists/{id}/members` | Add a member to a list |
| DELETE | `/api/contact-lists/{id}/members/{userId}` | Remove a member |

### Activities

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/activities` | List my activities (created + invited to) |
| POST | `/api/activities` | Create a new activity |
| GET | `/api/activities/{id}` | Get activity detail with invitations |
| PUT | `/api/activities/{id}` | Update an activity (organizer only) |
| DELETE | `/api/activities/{id}` | Soft delete an activity (organizer only) |

### Invitations

| Method | Endpoint | Description |
|---|---|---|
| POST | `/api/activities/{id}/invitations` | Invite users and/or contact lists |
| PATCH | `/api/activities/{id}/invitations/{invId}` | Accept or decline an invitation |
| DELETE | `/api/activities/{id}/invitations/{invId}` | Revoke an invitation (organizer only) |

### Invite Request Body

```json
{
    "user_ids": [2, 3],
    "contact_list_ids": [1]
}
```

Both fields are optional, but at least one must be provided. Duplicates across users and lists are deduplicated automatically.

## Authorization

Authorization is handled via Laravel Policies:

- **ActivityPolicy** — only the organizer can update, delete, or manage invitations. Both the organizer and invited users can view an activity.
- **ContactListPolicy** — only the owner can view, update, delete, or manage members of a list.

## Frontend Architecture

The React SPA communicates exclusively with the Laravel REST API via Axios. Key design decisions:

- **AuthContext + useAuth:** global authentication state (token, user) shared across the component tree without prop drilling, persisted in `localStorage` across sessions.
- **useAxiosPrivate:** custom hook that returns an Axios instance with a request interceptor that automatically attaches the Sanctum Bearer token to every protected API call.
- **RequireAuth:** layout route component that checks authentication state and redirects unauthenticated users to login, preserving the intended destination via `location.state`.
- **React Router catch-all:** a Laravel wildcard route (`/{any}`) serves the Blade entry point for any URL, letting React Router handle client-side navigation.

## Development Workflow

```
[Local] → git push → [GitHub] → git pull → [VPS]
```

Local development uses `docker-compose.override.yml` to expose the database port locally (HeidiSQL / TablePlus). This file is gitignored and never deployed.

## What I Learned

- Docker Compose for multi-container environments (PHP-FPM, Nginx, MySQL)
- Laravel Sanctum for stateless token authentication
- REST API design with proper HTTP status codes
- API Resources to control response shape and hide internal fields
- Laravel Policies for authorization
- Eager loading and the N+1 query problem
- Soft deletes and their implications
- Git workflow across local and remote environments
- React SPA architecture: component tree, hooks, Context API, custom hooks
- Client-side routing with protected routes and redirect-after-login
- Axios interceptors for transparent token injection
- localStorage for session persistence in a token-based auth flow
- Debugging toolchain issues (Vite + React plugin compatibility)
