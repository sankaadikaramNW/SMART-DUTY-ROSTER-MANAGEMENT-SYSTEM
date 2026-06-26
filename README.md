# 🛡️ SLAF Smart Duty Roster Management System

A professional **Smart Duty Roster Management System** developed for the **Sri Lanka Air Force (SLAF)** to automate personnel duty assignment, shift management, duty approval workflows, and reporting. The system provides secure, role-based access control while ensuring efficient management of daily operational duties.

---

## 📌 Project Overview

The SLAF Smart Duty Roster Management System is designed to replace manual duty roster preparation with a secure, web-based application.

The system enables SNCOs to assign duties only to personnel within their own posting location, allows OCPROVST officers to approve duty rosters, and provides personnel with real-time access to their assigned duties.

---

## 🚀 Key Features

### Authentication & Authorization

- Secure Login
- Role-Based Access Control (RBAC)
- Session Management
- Password Hashing
- User Profile Management

---

### Dashboard

Separate dashboards for:

- Administrator
- SNCO
- OCPROVST
- Airman (Normal User)

Dashboard includes:

- Duty Statistics
- Pending Approvals
- Upcoming Duties
- Notifications
- Quick Actions
- Interactive Calendar

---

### Personnel Management

- Add Personnel
- Update Personnel
- View Personnel
- Search Personnel
- Personnel Status Management
- Posting History
- Service Details

---

### Posting Management

- Create Posting
- Transfer Personnel
- Maintain Posting History
- Active Posting Tracking

Personnel can only belong to one active posting while preserving historical posting records.

---

### Camp Management

Manage:

- Air Force Camps
- Bases
- Stations

Examples:

- SLAF Ekala
- SLAF Ratmalana
- SLAF Katunayake
- SLAF China Bay

---

### Shift Management

Administrator can:

- Create Shifts
- Edit Shifts
- Activate/Deactivate Shifts

Example shifts:

| Shift | Time |
|--------|------|
| Morning | 0600 - 1400 |
| Afternoon | 1400 - 2200 |
| Night | 2200 - 0600 |
| 24 Hour Duty | 0800 - 0800 |

---

### Duty Type Management

Examples:

- Guard Duty
- Main Gate Duty
- Armoury Duty
- Patrol Duty
- Operations Room
- Security Check Point
- VIP Protection

---

### Duty Assignment

SNCO can:

- Create Duty Assignments
- Select Duty Date
- Select Shift
- Select Duty Type
- Select Duty Location
- Assign Personnel
- Add Remarks

Only personnel belonging to the SNCO's own posting location are displayed.

---

### Smart Conflict Detection

System detects:

✅ Normal Duplicate Assignments

⚠ Same Person – Same Shift

🔴 Overlapping Duty Assignments

Duplicate duties are allowed when different personnel perform the same duty.

Conflicting assignments are highlighted with warning colors instead of being blocked.

---

### Duty Approval Workflow

Workflow:

```
Draft
    ↓
Submitted by SNCO
    ↓
Reviewed by OCPROVST
    ↓
Approved / Rejected
    ↓
Published
```

Once approved:

- Duty becomes visible to assigned personnel.
- Notification is generated.

---

### Duty Calendar

Interactive calendar displaying:

- Daily View
- Weekly View
- Monthly View

Color Codes:

- 🟢 Approved
- 🟡 Pending
- 🔴 Rejected
- 🟠 Conflict
- 🔵 Current Duty

---

### Reports

Generate:

- Personnel Duty Reports
- Camp Reports
- Shift Reports
- Monthly Duty Reports
- Approval Reports

Export:

- PDF
- Excel
- CSV

---

### Notifications

Automatic notifications for:

- Duty Assigned
- Duty Updated
- Duty Approved
- Duty Rejected
- Duty Cancelled

---

### Audit Log

Every activity is logged.

Tracks:

- User
- Action
- Module
- Previous Data
- New Data
- IP Address
- Browser
- Timestamp

Audit logs cannot be modified.

---

## 👥 User Roles

### Administrator

- Full System Access
- User Management
- Camp Management
- Shift Management
- Duty Types
- Security Monitoring
- Reports

---

### SNCO

- Create Duty Rosters
- Assign Duties
- Submit Rosters
- View Camp Personnel
- Duty Reports

Restrictions:

Can only assign personnel from their own posting location.

---

### OCPROVST

- Review Duty Rosters
- Approve Duty Rosters
- Reject Duty Rosters
- Return for Corrections

---

### Airman

- View Assigned Duties
- View Duty History
- View Calendar
- View Notifications
- Update Own Profile

---

# 💻 Technology Stack

### Backend

- PHP 8+
- Object-Oriented Programming (OOP)
- MVC Architecture
- PDO

### Frontend

- HTML5
- CSS3
- Bootstrap 5
- JavaScript
- jQuery
- AJAX
- DataTables
- FullCalendar
- Chart.js

### Database

- MySQL

### Web Server

- Apache (XAMPP)

---

# 📂 Project Structure

```
smart-roster/

│
├── assets/
│   ├── css/
│   ├── js/
│   ├── images/
│   ├── icons/
│
├── config/
│   └── database.php
│
├── controllers/
│
├── models/
│
├── views/
│
├── middleware/
│
├── helpers/
│
├── uploads/
│
├── reports/
│
├── logs/
│
├── database/
│   └── smart_roster.sql
│
├── index.php
│
└── README.md
```

---

# 🗄 Database

Main Tables

- users
- roles
- user_roles
- personnel
- camps
- postings
- shifts
- duty_types
- duties
- duty_assignments
- duty_rosters
- approvals
- notifications
- audit_logs
- activity_logs
- login_history

---

# 🔒 Security Features

- Role-Based Access Control (RBAC)
- Secure Session Management
- Password Hashing
- PDO Prepared Statements
- SQL Injection Prevention
- Cross-Site Scripting (XSS) Protection
- Cross-Site Request Forgery (CSRF) Protection
- Input Validation
- Output Escaping
- Audit Logging

---

# 📱 Responsive Design

The application is fully responsive and optimized for:

- Desktop
- Laptop
- Tablet
- Mobile

Responsive components include:

- Dashboard
- Sidebar
- Tables
- Forms
- Modals
- Calendar
- Reports

---

# ⚙️ Installation

## Requirements

- PHP 8.1 or later
- MySQL 8+
- XAMPP
- Apache
- phpMyAdmin

---

## Installation Steps

### 1. Clone the project

```bash
git clone https://github.com/yourusername/smart-duty-roster.git
```

or copy the project into:

```
C:\xampp\htdocs\
```

---

### 2. Start XAMPP

Start:

- Apache
- MySQL

---

### 3. Import Database

Open:

```
http://localhost/phpmyadmin
```

Create database:

```
smart_roster
```

Import:

```
database/smart_roster.sql
```

---

### 4. Configure Database

Update:

```
config/database.php
```

Example:

```php
$host = "localhost";
$dbname = "smart_roster";
$username = "root";
$password = "";
```

---

### 5. Run Application

```
http://localhost/smart-roster/
```

---

# 📊 Future Enhancements

- Mobile Application (Android/iOS)
- QR Code Duty Verification
- Biometric Attendance Integration
- SMS Notifications
- Email Notifications
- AI-Based Duty Recommendation
- Automatic Duty Rotation
- Leave Management Integration
- Digital Signature Approval
- Multi-Camp Management
- REST API Integration

---

# 📄 License

This project is developed for educational and organizational use. Modify and distribute according to your organization's policies.

---

# 👨‍💻 Developed By

**SLAF Smart Duty Roster Management System**

Designed and developed as a secure, scalable, and professional military duty management solution using **PHP**, **MySQL**, **Bootstrap 5**, **AJAX**, and **XAMPP**.

---

## 📧 Support

For technical support or system enhancements, please contact the system administrator or development team.

---

**Version:** 1.0.0  
**Status:** Development / Production Ready  
**Platform:** Web-Based Application  
**Framework:** Core PHP (MVC)  
**Database:** MySQL  
**Server:** Apache (XAMPP)
