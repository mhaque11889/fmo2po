# FMO2PO — Project Feature Documentation

## Overview

**FMO2PO** is a Laravel-based requirement request management system that facilitates a structured workflow between two organizational units:

- **FMO** (Field Management Office) — submits and tracks requirement requests
- **PO** (Procurement Office) — receives, assigns, and fulfills those requests

---

## User Roles

| Role | Side | Description |
|---|---|---|
| `fmo_user` | FMO | Creates and tracks requests |
| `fmo_admin` | FMO | Reviews, approves/rejects requests |
| `po_admin` | PO | Assigns requests to PO staff |
| `po_user` | PO | Processes and fulfills requests |
| `super_admin` | Both | Full system access |

---

## Request Lifecycle

```
[FMO User creates] → pending
       ↓
[FMO Admin approves/rejects/clarifies] → approved / rejected / clarification_needed
       ↓
[PO Admin assigns] → assigned
       ↓
[PO User marks in-progress] → in_progress
       ↓
[PO User marks complete] → completed
```

Also possible: `cancelled` (by creator), `clarification_needed` → `pending` (resubmit)

---

## Features by User Segment

---

### FMO User

**Request Management**
- Create new requirement requests with:
  - Location field
  - Multiple line items (item name, quantity, specifications)
  - Remarks/notes
  - File attachments (PDF/images, max 5MB, up to 10 files)
- Edit pending or clarification-needed requests
- Cancel pending or clarification-needed requests
- Resubmit clarification-needed requests after addressing feedback
- Delete rejected requests
- View own requests filtered by status

**Group View**
- If assigned to an FMO group, can view all group members' requests in a shared view

**Communication (Nudges)**
- Send update requests ("nudges") to assigned PO users asking for status
- Receive replies from PO users
- Mark nudge replies as "seen"
- Mark completed request notifications as seen

**Dashboard**
- Counts of requests by status
- List of last 10 submitted requests

---

### FMO Admin

**All FMO User features**, plus:

**Request Administration**
- View all requests from all FMO users (paginated list)
- Approve pending requests
- Reject pending requests with rejection remarks
- Request clarification on pending requests with remarks (sends request back to creator)
- Edit pending requests

**Escalation**
- Generate a pre-filled Gmail compose link to escalate issues to the PO assignee/assigner, with FMO admins CC'd — available on approved, assigned, or in-progress requests

**Communication (Nudges)**
- Send nudges to PO assignees on any request (not just own)
- Receive unseen replies and completion notifications

**Reports**
- View reports with status/date range filters
- Export to CSV or Excel

**User & Group Management**
- Create, edit, deactivate FMO users
- Manage FMO groups (create groups, add/remove members)

**Dashboard**
- Month-to-date stats
- Pending requests requiring approval action

---

### PO Admin

**Request Processing**
- View approved requests ready for assignment
- Assign approved requests to PO users
- Reassign assigned/in-progress requests if needed
- View own assigned tasks
- Mark own assigned requests as in-progress and completed

**Communication (Nudges)**
- Receive nudges from FMO side
- Acknowledge or reply to nudges

**Reports**
- View reports with filters
- Export to CSV or Excel

**User & Group Management**
- Create, edit, deactivate PO users
- Manage PO groups (create groups, add/remove members)

**Dashboard**
- Approved requests awaiting assignment
- Assigned/in-progress tasks
- Unread nudge count

---

### PO User

**Task Processing**
- View own assigned requests (filterable by status)
- Mark assigned requests as in-progress with progress remarks
- Mark requests as completed with completion remarks

**Group View**
- If in a PO group, can view all group members' assigned requests

**Communication (Nudges)**
- Receive nudges from FMO side
- Acknowledge nudge receipt
- Reply to nudges with a status message

**Dashboard**
- Assigned and in-progress task counts
- Unread nudge notifications

---

### Super Admin

**All features of all roles**, plus:

**Exclusive Admin Functions**
- Reactivate deactivated users
- Bulk import users via CSV (with downloadable template)
- Delete all users (except self) — for system reset
- Delete all requests and attachments — for system reset
- Manage both FMO and PO groups

---

## Cross-Role Features

### Attachments
- FMO users attach PDF/image files to requests at creation
- Authenticated users with access to the request can view/download attachments
- Files stored with UUID names for security; served via authorized route

### Request History / Audit Trail
- Every status change is logged with the acting user, action type, field changes, and remarks
- Visible on the request detail page

### User Settings
- Dashboard auto-refresh interval (30s – 5min, or off)
- Notification sound (chime, bell, ping, none)
- Email notification preferences:
  - Master switch: all, key events only, custom, none
  - Per-event toggles (new request, approved, assigned, completed, etc.)

### Email Notifications
- Triggered on status transitions
- Queued via `emails` queue
- Respects per-user email preferences

---

## Admin Panel

| Feature | FMO Admin | PO Admin | Super Admin |
|---|---|---|---|
| Create users | FMO only | PO only | All roles |
| Edit users | FMO only | PO only | All |
| Deactivate users | FMO (if no active tasks) | PO (if no active tasks) | All |
| Reactivate users | — | — | Yes |
| Bulk CSV import | — | — | Yes |
| Delete all users | — | — | Yes |
| Delete all requests | — | — | Yes |
| FMO Group management | Yes | — | Yes |
| PO Group management | — | Yes | Yes |
| Reports & Export | Yes | Yes | Yes |

---

## Authentication

- **Google OAuth 2.0** (via Laravel Socialite)
- Only pre-registered email addresses can log in
- Deactivated users are blocked from login
- Avatar synced from Google profile
- Local dev only: fake login selector for testing all roles

---

## Reports

- Filterable by: status, date range
- Paginated display
- Export formats: CSV, Excel
- Includes all relevant timestamps, remarks, and user names

---

## Database Tables Summary

| Table | Purpose |
|---|---|
| `users` | All users with role and settings |
| `requirement_requests` | Core request records |
| `requirement_request_items` | Line items per request (multi-item) |
| `request_attachments` | File uploads per request |
| `request_history` | Audit trail of all status changes |
| `request_nudges` | FMO↔PO communication thread |
| `user_groups` | Named teams (FMO or PO type) |
| `user_group_members` | Pivot: users in groups |

---

## Key Routes Reference

| Method | URI | Role Required | Description |
|---|---|---|---|
| GET | `/dashboard` | All | Role-specific dashboard |
| GET | `/requests/create` | fmo_user, fmo_admin, super_admin | Create request form |
| POST | `/requests` | fmo_user, fmo_admin, super_admin | Submit new request |
| GET | `/my-requests` | fmo_user, fmo_admin, super_admin | Own requests list |
| GET | `/requests` | fmo_admin, super_admin | All requests list |
| POST | `/requests/{id}/approve` | fmo_admin, super_admin | Approve request |
| POST | `/requests/{id}/reject` | fmo_admin, super_admin | Reject request |
| POST | `/requests/{id}/clarification` | fmo_admin, super_admin | Request clarification |
| POST | `/requests/{id}/assign` | po_admin, super_admin | Assign to PO user |
| GET | `/my-assigned` | po_user, po_admin, super_admin | Assigned tasks list |
| POST | `/requests/{id}/in-progress` | po_user, po_admin, super_admin | Mark in progress |
| POST | `/requests/{id}/complete` | po_user, po_admin, super_admin | Mark completed |
| POST | `/requests/{id}/nudge` | fmo_user, fmo_admin, super_admin | Send nudge to PO |
| GET | `/reports` | fmo_admin, po_admin, super_admin | Reports view |
| GET | `/admin/users` | fmo_admin, po_admin, super_admin | User management |
| GET | `/admin/fmo-groups` | fmo_admin, super_admin | FMO group management |
| GET | `/admin/po-groups` | po_admin, super_admin | PO group management |

---

*Last updated: 2026-04-13*
