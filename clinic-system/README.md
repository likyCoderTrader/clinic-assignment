# City Clinic - Patient Management System

A PHP-based patient management system using file-based storage for a medical clinic.

## Overview

This is a simple clinic management system built with pure PHP that handles patient registration, status tracking, and doctor notes without requiring a database. All data is stored in JSON files.

## Features

### Authentication
- Login with email and password
- User registration with role selection (Staff/Doctor)
- Session-based authentication

### Staff Features
- Register new patients with personal details and complaints
- View today's patient queue
- Dashboard with patient statistics

### Doctor Features
- View all patients assigned for the day
- Update patient status (Unseen, Seen, In Progress, Unresolved, Unable to Treat)
- Add clinical notes for each patient
- Search patients by name or ID

### Data Storage
- Users stored in: `storage/users.json`
- Patients stored in daily files: `storage/patients_YYYY-MM-DD.json`

## Installation

1. Make sure PHP is installed on your system
2. Navigate to the project directory in terminal
3. Start the built-in PHP server:
   ```
   php -S localhost:8000
   ```
4. Open your browser and go to: `http://localhost:8000`

## Test Accounts

| Position    | Email              | Password     |
|-------------|--------------------|--------------|
| Reception   | staff@clinic.com   | password123  |
| Doctor      | doctor@clinic.com  | password123  |

## Project Structure

```
clinic-system/
├── index.php              # Login page
├── register.php           # User registration
├── dashboard.php          # Role-based dashboard router
├── staff_dashboard.php    # Staff/reception dashboard
├── doctor_dashboard.php   # Doctor consultation dashboard
├── logout.php             # Session termination
├── assets/
│   └── style.css          # Stylesheet
├── includes/
│   └── file_helper.php    # File handling utilities
└── storage/
    ├── users.json         # User accounts
    └── patients_*.json    # Daily patient records
```

## Patient Status Options

- **Unseen** - Patient waiting to be seen
- **Seen** - Consultation completed
- **In Progress** - Currently under treatment
- **Unresolved** - Seen but issue not resolved
- **Unable to Treat** - Case beyond doctor's scope

## Requirements

- PHP 7.4 or higher
- Web browser
- No external dependencies
- No database required

## Usage Notes

- Patient files are organized by date, making it easy to review historical records
- Each patient receives a unique ID (format: PTxxxxxxxx)
- User IDs follow the format: USRxxxxxx
- All form inputs are sanitized to prevent XSS attacks