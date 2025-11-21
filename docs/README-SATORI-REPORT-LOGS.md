# SATORI Report Logs  
## Internal Documentation – Developer Overview

---

## 1. Introduction
SATORI Report Logs is a modular WordPress plugin enabling monthly service and maintenance reporting with structured inputs, editor UI, and export options.  
This README provides a high‑level developer overview.

---

## 2. Purpose
To provide consistent, Ball Australia–style service logs within WP using:
- A monthly report architecture  
- A structured, spreadsheet-style editor  
- Standardised export formats (HTML, CSV, PDF)

---

## 3. Key Features
- Monthly service log records  
- Grid/table-style admin editor  
- Custom database tables  
- HTML/CSV/PDF exports  
- Capability‑based access restrictions  
- SATORI namespace + autoloader compliance  
- Hooks & filters for extensibility

---

## 4. Folder Structure (Summary)
```
satori-report-logs/
├─ satori-report-logs.php
├─ uninstall.php
├─ includes/
│   ├─ class-plugin.php
│   ├─ class-reports-manager.php   (future)
│   ├─ class-logger.php            (future)
│   └─ helpers.php
├─ admin/
│   ├─ class-admin.php
│   ├─ views/
│   └─ assets/
├─ templates/
│   ├─ export-html.php
│   └─ export-pdf.php
└─ docs/
    ├─ README-SATORI-REPORT-LOGS.md
    ├─ SPEC-SATORI-REPORT-LOGS.md
    └─ PRODUCT-BRIEF-SATORI-REPORT-LOGS.md
```

---

## 5. Related Documents
- **Technical Specification:** `docs/SPEC-SATORI-REPORT-LOGS.md`  
- **Product Brief:** `docs/PRODUCT-BRIEF-SATORI-REPORT-LOGS.md`

---

## 6. Author
**Satori Graphics Pty Ltd**  
Director: Andy Garard  
https://satori.com.au/

---

_End of README-SATORI-REPORT-LOGS.md_
