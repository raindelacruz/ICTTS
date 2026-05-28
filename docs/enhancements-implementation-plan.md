# Enhancement Implementation Plan

## Affected Data Model

- `tickets`: add severity/priority, SLA targets/status, first response timestamp, responsible group, and `Returned for Further Action` status.
- `ticket_assignees`: current primary/supporting technical personnel for one canonical ticket.
- `ticket_assignments`: assignment, reassignment, supporting-assignee history.
- `ticket_attachments`: uploaded requester and technical files with metadata.
- `ticket_feedback`: requester satisfaction rating, resolved flag, comments.
- `ticket_escalations`: overdue/manual escalation notices with duplicate-prevention key.
- `ticket_reopen_logs`: requester/supervisor return and reopen history.
- `ticket_endorsements`: technical group/category/service endorsement history.

## Affected Application Files

- Models: `Ticket`, `User`, `Notification`.
- Controllers: `PublicController`, `TicketController`, `DashboardController`, `ReportController`.
- Views: public request/confirmation, ticket list/detail, dashboard, reports.
- Services: `EmailService`, `ActivityLogger`.
- Routes: assignment, reassignment, status update, attachment upload, endorsement, reopen, escalation.
- Database: `database/add_enhancement_controls.sql` and `database/schema.sql`.

## Implementation Steps

1. Apply database migration and keep base schema aligned.
2. Extend `Ticket` model with SLA calculation, priority filters, multi-assignee checks, history queries, and audit inserts.
3. Update public submission for requester attachments and apply severity from the selected specific request.
4. Update requester confirmation for satisfaction feedback or return for further action.
5. Add manager workflows for reassignment, endorsement, reopen, and overdue escalation.
6. Allow all currently assigned technical personnel to update one ticket while preserving canonical `tickets.status`.
7. Surface severity, SLA status, overdue tickets, team assignments, feedback, attachments, and histories in dashboards/reports/details.
8. Verify with PHP syntax checks and database migration review before deployment.

## Authorization Rules

- Admin, unit head, and division chief may assign, reassign, endorse, reopen, and run escalations.
- Technical users may update only tickets where they are active primary or supporting assignee.
- Requesters may confirm completion or return a ticket only through a valid confirmation token.
- Multiple assignees never create duplicate tickets; all updates remain tied to the same `tickets.id` and `ticket_no`.
