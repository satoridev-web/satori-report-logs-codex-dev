# SATORI Report Logs — Technical Specification (v1.0.0)

## 1. Overview
SATORI Report Logs is a WordPress plugin enabling monthly service and maintenance reporting with structured data entry, review tools, and export formats (HTML, CSV, PDF). Derived from Ball Australia service log requirements.

## 2. Functional Requirements
- Create and manage monthly reports
- Record service details, tasks completed, issues, notes
- Spreadsheet-like admin UI
- Export HTML/CSV/PDF
- Revision history
- Settings page

## 3. Data Model
### Tables
- `satori_report_logs`
  - id (PK), month, year, created_at, updated_at
- `satori_report_log_items`
  - id (PK), log_id (FK), row_type, label, value, sort_order

## 4. Admin UI
- Main Dashboard: list months
- Report Editor: dynamic table grid
- Export Panel

## 5. Architecture
- Namespace: `Satori\Report_Logs`
- Folders: admin/, includes/, assets/, templates/
- Core classes: class-plugin.php, class-admin.php, class-reports-manager.php

## 6. Hooks
- Actions: satori_report_logs_saved, satori_report_logs_export
- Filters: satori_report_logs_item_types

## 7. Export
- HTML: template-based
- CSV: generated row-wise
- PDF: using DOMPDF or mPDF

## 8. Security
- Capability: manage_options
- Nonce protection
- Sanitisation on save

## 9. Future Enhancements
- Scheduled exports
- Remote API integration
