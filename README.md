# SATORI Report Logs

A modular WordPress plugin for creating, editing, and exporting **monthly service and maintenance reports**.  
Built to match and modernise the Ball Australia service log format and engineered according to SATORI development standards.

---

## 📌 Features

- Create monthly service reports (Month + Year)
- Structured, spreadsheet-style editor UI
- Custom database tables for accurate, queryable data
- Export formats:
  - **HTML**
  - **CSV**
  - **PDF** (Ball-style layout)
- Capability-based access control
- SATORI-standard architecture (namespaces, classes, documentation)
- Hooks & filters for extensions

---

## 📁 Folder Structure (Primary)

```
satori-report-logs/
├─ satori-report-logs.php          # Main plugin bootstrap
├─ uninstall.php                   # Cleanup placeholder
│
├─ includes/
│   ├─ class-plugin.php            # Core loader
│   ├─ class-reports-manager.php   # CRUD + DB logic (future)
│   ├─ class-logger.php            # Debug logger (future)
│   └─ helpers.php
│
├─ admin/
│   ├─ class-admin.php             # Admin menu & screens
│   ├─ views/
│   └─ assets/
│
├─ templates/
│   ├─ export-html.php
│   └─ export-pdf.php
│
└─ docs/
    ├─ README-SATORI-REPORT-LOGS.md
    ├─ SPEC-SATORI-REPORT-LOGS.md
    └─ PRODUCT-BRIEF-SATORI-REPORT-LOGS.md
```

---

## 🛠 Installation

1. Clone the repository into `wp-content/plugins/` as:

   ```
   satori-report-logs
   ```

2. Activate from **WordPress Admin → Plugins**.

3. A new admin menu item **Report Logs** will appear.

---

## 🧩 Documentation

Full documentation is inside the `/docs/` directory:

- **Developer README**  
  `docs/README-SATORI-REPORT-LOGS.md`

- **Technical Specification**  
  `docs/SPEC-SATORI-REPORT-LOGS.md`

- **Product Brief (Client/Stakeholder)**  
  `docs/PRODUCT-BRIEF-SATORI-REPORT-LOGS.md`

---

## 🔧 Development

### Requirements

- PHP 8.0+
- WordPress 6.0+
- LocalWP or equivalent dev environment

### Standards

This plugin follows:

- SATORI Coding Standards  
- Namespaced class structure (`Satori\Report_Logs\*`)
- PSR-4-style autoloading (normalised to lowercase)

---

## 🔍 Roadmap

- DB schema + installer
- Editor UI implementation
- HTML export engine
- CSV export engine
- PDF export engine
- Report duplication
- Revision history
- Integration with SATORI Audit (future)

---

## 👤 Maintainers

**Satori Graphics Pty Ltd**  
Director: Andy Garard  
https://satori.com.au/

---

_End of repo README_
