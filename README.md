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
