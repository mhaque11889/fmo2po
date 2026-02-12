# FMO2PO - Facilities Management Office to Purchase Office

A Laravel-based workflow management system for handling requirement requests between the Facilities Management Office (FMO) and Purchase Office (PO).

## Features

- **Role-Based Access Control** - Five user roles with specific permissions:
  - `super_admin` - Full system access
  - `fmo_admin` - Approve/reject FMO requests, manage FMO users
  - `fmo_user` - Create and track requirement requests
  - `po_admin` - Assign requests to PO users, manage PO users
  - `po_user` - Process assigned requests

- **Request Workflow**
  - FMO User creates request → FMO Admin approves → PO Admin assigns → PO User completes
  - Status flow: `pending` → `approved`/`rejected` → `assigned` → `in_progress` → `completed`

- **File Attachments** - Secure file uploads (PDF, images) with authenticated access

- **Reports** - Filter requests by status, date range, and export to CSV/Excel

- **Dashboard Auto-Refresh** - Configurable refresh intervals with notification sounds

- **Google OAuth Authentication** - Only pre-registered users can log in

## Requirements

- PHP 8.2+
- Composer
- MySQL / PostgreSQL / SQLite
- Node.js & NPM (for development)

## Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd fmo2po
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure database** in `.env`
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=fmo2po
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. **Configure Google OAuth** in `.env`
   ```
   GOOGLE_CLIENT_ID=your-client-id
   GOOGLE_CLIENT_SECRET=your-client-secret
   GOOGLE_REDIRECT_URI=https://your-domain.com/auth/google/callback
   ```

6. **Run migrations**
   ```bash
   php artisan migrate
   ```

7. **Seed initial users** (update `database/seeders/UserSeeder.php` with real emails first)
   ```bash
   php artisan db:seed
   ```

8. **Start the server**
   ```bash
   php artisan serve
   ```

## User Management

### Authentication Flow
1. Admin adds user with email and role via admin panel or database seeder
2. User signs in with Google using their registered email
3. If email exists in database, user is logged in with their assigned role
4. If email not found, access is denied

### Adding Users
- **Via Admin Panel**: Navigate to Admin → Users → Add New User
- **Via Seeder**: Update `database/seeders/UserSeeder.php` with user details

Example seeder entry:
```php
User::create([
    'name' => 'John Doe',
    'email' => 'john.doe@company.com',
    'role' => 'fmo_user',
]);
```

## User Roles & Permissions

| Role | Can Create Requests | Can Approve | Can Assign | Can Process | Can View Reports |
|------|---------------------|-------------|------------|-------------|------------------|
| super_admin | Yes | Yes | Yes | Yes | Yes |
| fmo_admin | Yes | Yes | No | No | Yes |
| fmo_user | Yes | No | No | No | No |
| po_admin | No | No | Yes | Yes | Yes |
| po_user | No | No | No | Yes | No |

## Directory Structure

```
app/
├── Http/Controllers/
│   ├── Admin/UserController.php    # User management
│   ├── AttachmentController.php    # Secure file access
│   ├── DashboardController.php     # Role-based dashboards
│   ├── ReportsController.php       # Reports & exports
│   ├── RequirementRequestController.php  # Request CRUD & workflow
│   └── SettingsController.php      # User settings
├── Models/
│   ├── User.php
│   ├── RequirementRequest.php
│   ├── RequestAttachment.php
│   └── RequestHistory.php
```

## Configuration

### Dashboard Auto-Refresh
Users can configure refresh settings via Settings menu:
- Refresh interval (30s, 1min, 2min, 5min, or disabled)
- Notification sounds (chime, bell, ping, or none)
- Notification triggers (new request, status change, task assigned)

### File Storage
Attachments are stored in `storage/app/attachments/` (private directory). Access requires authentication and proper authorization.

## Development

### Local Testing
In local environment, a "fake login" feature allows testing different roles without Google OAuth:
```bash
php artisan db:seed
```
Then use the role buttons on the login page.

### Running Tests
```bash
php artisan test
```

## License

This project is proprietary software.
