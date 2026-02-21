# Airbnb Tool

Automated property management tool for **Casa Pupuya** and **Cabaña Pullinque**.

Built with Laravel 12 + Vue 3 + Inertia v2. Deployed on Kubernetes at [airbnb.freshwork.dev](https://airbnb.freshwork.dev).

## REST API

Base URL: `https://airbnb.freshwork.dev/api/v1`

All endpoints require a Sanctum token:

```
Authorization: Bearer <token>
Accept: application/json
```

---

### Properties

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/properties` | List all properties |
| `GET` | `/properties/{id}` | Get a single property |

---

### Reservations

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/reservations` | List reservations |
| `POST` | `/reservations` | Create a reservation |
| `GET` | `/reservations/{id}` | Get a single reservation |
| `PUT` | `/reservations/{id}` | Update a reservation |
| `DELETE` | `/reservations/{id}` | Soft delete a reservation |

**Query params for `GET /reservations`:**

| Param | Type | Description |
|-------|------|-------------|
| `property_id` | integer | Filter by property |
| `status` | string | Filter by status (e.g. `confirmed`, `cancelled`) |
| `check_in_from` | date `Y-m-d` | Check-in on or after this date |
| `check_in_to` | date `Y-m-d` | Check-in on or before this date |
| `upcoming` | `1` / `0` | Only return upcoming reservations |

**Body for `POST /reservations` and `PUT /reservations/{id}`:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `property_id` | integer | Yes (create) | Property ID |
| `guest_name` | string | Yes (create) | Guest full name |
| `guest_phone` | string | No | Guest phone number |
| `guest_email` | string | No | Guest email |
| `number_of_guests` | integer | No | Defaults to 1 |
| `check_in` | date `Y-m-d` | Yes (create) | Check-in date |
| `check_out` | date `Y-m-d` | Yes (create) | Check-out date (must be after check-in) |
| `status` | string | No | `confirmed`, `checked_in`, `checked_out`, `cancelled` |
| `notes` | string | No | Internal notes |
| `source` | string | No | Booking source (default: `manual`) |
| `airbnb_reservation_id` | string | No | Airbnb's ID (unique) |

Deleted reservations are soft-deleted and excluded from all listing/show endpoints.

---

### Cleaning Tasks

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/cleaning-tasks` | List cleaning tasks |
| `GET` | `/cleaning-tasks/{id}` | Get a single cleaning task |
| `PATCH` | `/cleaning-tasks/{id}/status` | Update task status |

**Query params for `GET /cleaning-tasks`:**

| Param | Type | Description |
|-------|------|-------------|
| `property_id` | integer | Filter by property |
| `status` | string | Filter by status (`pending`, `in_progress`, `completed`) |
| `upcoming` | `1` / `0` | Only return upcoming tasks |

**Body for `PATCH /cleaning-tasks/{id}/status`:**

```json
{ "status": "in_progress" }
```

Valid values: `pending`, `in_progress`, `completed`.

---

### Contacts

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/contacts` | List all contacts |
| `GET` | `/contacts/{id}` | Get a single contact |

---

### Reservation Notes

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/reservations/{id}/notes` | List notes for a reservation |
| `POST` | `/reservations/{id}/notes` | Create a note |
| `GET` | `/reservation-notes/{id}` | Get a single note |
| `PUT` | `/reservation-notes/{id}` | Update a note |
| `DELETE` | `/reservation-notes/{id}` | Delete a note |

**Body for `POST /reservations/{id}/notes`:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `content` | string | Yes | Note content |
| `from_agent` | string | No | Agent name (e.g. `alma`, `clo`) |
| `needs_response` | boolean | No | Whether the note requires an owner response |

When a note is created, an email is sent to all users via `ReservationNoteCreated`.

#### Agent Notes & Response Flow

Notes support a request/response workflow between AI agents and property owners:

1. **Agent creates a note** — An agent (e.g. Alma) creates a note via the API with `from_agent` and `needs_response=true`. An email notification is sent to all users.
2. **Owner sees it in the web UI** — The reservation show page displays agent notes with a "Pending response" badge and an inline response form.
3. **Owner responds** — `PUT /reservation-notes/{id}/respond` (web route, not API). This creates a new `ReservationNote` linked to the original via `parent_id`, sets `responded_at` on the original, and dispatches the `NotifyAgentResponse` job.
4. **Webhook notifies the agent** — The job sends a POST to the OpenClaw webhook proxy (`/agent-response`) with `note_id`, `from_agent`, `content` (the response), `guest_name`, and `property_name`. The proxy forwards it to the correct agent's gateway.

```
Guest asks Alma → Alma creates note (needs_response) → Email to owner
→ Owner responds in web UI → Reply note created (parent_id)
→ NotifyAgentResponse job → Webhook proxy → Alma receives response
```

**Key details:**
- Responses are stored as separate `ReservationNote` records with `parent_id` pointing to the original note, preserving the original content.
- The web UI only shows top-level notes (no `parent_id`) with their replies nested below.
- Only notes with `needs_response=true` and no `responded_at` show the response form.
- Atlas is not notified — only the originating agent receives the response.
