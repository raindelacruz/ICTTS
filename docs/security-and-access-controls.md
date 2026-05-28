# Security and Access Controls

## Authentication

Authenticated access is handled by `App\Core\Auth` with PHP sessions.

Controls:

- Successful login regenerates the session ID.
- Authenticated user data is stored in `$_SESSION['user']`.
- `Auth::requireLogin()` protects staff routes.
- `Auth::requireRole()` protects role-specific staff routes.
- Passwords are stored as hashes using PHP `password_hash()`.
- Inactive users cannot authenticate through `User::findByEmail()`.

## Authorization

Application roles:

- `technical`
- `unit_head`
- `division_chief`
- `admin`

High-level access rules:

| Area | Allowed Roles |
| --- | --- |
| Dashboard | Any authenticated role |
| Ticket list/details | Any authenticated role |
| Ticket assignment | Manager roles and eligible self-assignment path |
| Technical status update | Assigned technical personnel only |
| Reports | Any authenticated role |
| Registration | Public form creates inactive technical accounts |
| User management and activation | Admin only |
| Library management | Admin only |
| Activity and email logs | Admin only |

Manager roles are defined by `Auth::canManage()`:

- `admin`
- `unit_head`
- `division_chief`

## CSRF Protection

State-changing form submissions use `App\Core\Csrf`.

Controls:

- A random CSRF token is stored in the session.
- Forms include a hidden `_csrf` field.
- POST handlers validate the token before making changes.
- Invalid or missing tokens return HTTP 419.

## Input Validation

Public ticket submission validates:

- Required requester fields.
- Email format.
- Contact number format.
- Description length from 10 to 5000 characters.
- Valid region and office relationship.
- Valid service category and service item relationship.
- Valid requested date/time.

Database writes use prepared PDO statements throughout the models.

## Output Escaping

Views and email templates use the helper `e()` where values are rendered into HTML contexts. Continue using this helper for any user-provided or database-provided text.

## Audit Controls

The system records:

- Status changes in `ticket_status_logs`.
- Assignment history in `ticket_assignments`.
- User/public actions in `activity_logs`.
- Email attempts in `email_logs`.
- Notification read state in `notifications`.

These records support operational review and incident tracing.

## Token Controls

Requester confirmation links use random tokens created by `random_bytes(32)`.

Controls:

- Only the SHA-256 token hash is stored.
- Tokens expire after 14 days.
- Tokens are single-use through `used_at`.
- Completion confirmation updates happen in a database transaction.

## Configuration Risks

`config/config.php` reads database, URL, mail, and SMTP settings from environment variables, with local development defaults where safe. For production:

- Provide secrets through environment variables or a server-only configuration file outside the web root.
- Rotate any credentials that have been committed or shared.
- Use a dedicated database user with least-privilege permissions.
- Ensure `APP_PUBLIC_URL` matches the HTTPS production URL.
- Set secure PHP session cookie options at the server/runtime level.

## Recommended Hardening

- Enforce HTTPS in production.
- Configure `session.cookie_secure`, `session.cookie_httponly`, and `session.cookie_samesite`.
- Add rate limiting for login and public request submission.
- Add password complexity and reset flows if accounts are managed broadly.
- Review public registration behavior before production exposure.
- Keep `/register` only if inactive technical self-registration is part of the operating procedure.
- Add database backups and a restore test procedure.
- Monitor failed email logs and repeated invalid confirmation attempts.
