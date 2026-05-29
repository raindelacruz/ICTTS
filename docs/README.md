# ICTSD Ticketing System Documentation

This folder contains the working technical documentation for the ICTSD Ticketing System.

## Documents

- [System Design](system-design.md) describes the application architecture, modules, runtime flow, integrations, and deployment assumptions.
- [Workflow and Status Controls](workflow-status-controls.md) documents ticket lifecycle rules, role responsibilities, status transitions, and notification behavior.
- [Database Design](database-design.md) documents the MySQL schema, relationships, audit tables, seed data, and migration notes.
- [Security and Access Controls](security-and-access-controls.md) summarizes authentication, authorization, CSRF protection, input validation, auditing, and configuration risks.

## System Summary

The ICTSD Ticketing System is a PHP 8.1 MVC web application for submitting, assigning, tracking, completing, and confirming ICT service requests. It uses MySQL for persistence, Bootstrap-based server-rendered views for the interface, PHPMailer for outbound email, and in-app notifications for authenticated staff.

Primary users:

- Public requester: submits requests and confirms completion through emailed links.
- Technical personnel: accepts assigned work and updates ticket status.
- Unit head and division chief: monitor tickets, assign work, and receive status notifications.
- Admin: manages users, libraries, logs, and all supervisory functions.

