# Hostel Complaint Management System 🏢

A complete, responsive, and beginner-friendly Web Programming Mini Project built using **HTML5, CSS3, JavaScript, PHP, and MySQL (XAMPP)**.

---

## 📌 Project Overview
The **Hostel Complaint Management System** is a web-based grievance tracking platform designed for colleges. It allows hostel students to lodge maintenance requests (Electrical, Plumbing, Wi-Fi, Furniture, Cleaning, etc.) and enables the hostel warden to track, filter, and resolve complaints efficiently.

---

## 🛠️ Technology Stack
- **Frontend:** HTML5, CSS3 (Custom Responsive Styling), Vanilla JavaScript
- **Backend:** PHP 7.4+ / PHP 8.x (Procedural / Object-Oriented MySQLi with Prepared Statements)
- **Database:** MySQL (Configured on port **3307** for XAMPP)
- **Server:** Apache (XAMPP)

---

## 📁 Project Directory Structure
```text
HostelComplaintSystem/
├── db.php               # Central MySQL database connection (Port 3307)
├── setup.php            # One-click database installer & sample data generator
├── index.php            # Unified student & warden login portal
├── student.php          # Student dashboard: submit and track complaints
├── submit_complaint.php # Server-side handler to validate & store student complaints
├── warden.php           # Warden dashboard: review complaints, metrics, & filter
├── update_status.php    # Server-side handler to update complaint status
├── logout.php           # Secure session termination and redirect
├── style.css            # Modern responsive CSS (Navy blue, Light blue, White, Grey)
├── script.js            # Client-side form validation, search filter & demo autofill
├── database.sql         # Raw SQL schema backup for manual phpMyAdmin import
└── README.md            # Comprehensive project documentation
```

---

## 🚀 How to Set Up & Run in XAMPP (Step-by-Step)

### Step 1: Place Files into XAMPP `htdocs`
1. Navigate to your XAMPP installation directory:
   ```
   C:\xampp\htdocs\
   ```
2. Create a new folder named `HostelComplaintSystem` (or copy all project files into it):
   ```
   C:\xampp\htdocs\HostelComplaintSystem\
   ```

### Step 2: Start Apache and MySQL in XAMPP
1. Open the **XAMPP Control Panel** on your computer.
2. Click **Start** next to **Apache**.
3. Click **Start** next to **MySQL** (Ensure it is running on port **3307**).

### Step 3: Run the 1-Click Database Setup
Open your web browser (Chrome, Edge, Firefox) and go to:
```
http://localhost/HostelComplaintSystem/setup.php
```
*What happens:*
- The script automatically connects to MySQL on port **3307**.
- It creates the `hostel_complaint_db` database if not present.
- It builds both `users` and `complaints` tables with primary & foreign keys.
- It seeds demo accounts with secure `password_hash()` hashes and sample complaints.

### Step 4: Open the Application
Once setup confirms success, click the **Proceed to Login** button, or navigate directly to:
```
http://localhost/HostelComplaintSystem/index.php
```

---

## 🔑 Demo Login Credentials

| Role | Username | Password | Role Description |
| :--- | :--- | :--- | :--- |
| **Student** | `student1` | `student123` | Rahul Sharma (Roll: 21CS101, Room: B-204) |
| **Student** | `student2` | `student123` | Sneha Patel (Roll: 21CS102, Room: A-108) |
| **Warden** | `warden1` | `warden123` | Mr. Rajesh Kumar (Chief Hostel Warden) |

> 💡 **Tip:** On the login page, you can click the **🎓 Student** or **👔 Warden** quick-fill buttons to autofill the credentials instantly during your project demo.

---

## 💡 Key Features Implemented

### 1. Student Module
- Secure login and personalized dashboard welcome message.
- Summary cards showing: Total Complaints, Pending, In Progress, and Resolved.
- Complaint submission form with auto-filled student details and category selector:
  - *Electrical, Plumbing, Cleaning, Furniture, Internet, Other*.
- Live character counter (max 500 characters) with validation.
- Responsive table listing only complaints submitted by the logged-in student.
- Live status tracking with color-coded badges (*Pending* - Yellow, *In Progress* - Blue, *Resolved* - Green).

### 2. Warden Module
- Administrative login with restricted access.
- Global statistics cards displaying hostel-wide complaint counts.
- Status filter tabs: **All**, **Pending**, **In Progress**, and **Resolved**.
- Real-time client-side search bar to filter complaints by student name, roll number, or room number.
- Instant complaint status update dropdown (*Pending*, *In Progress*, *Resolved*) with update confirmation prompt.

### 3. Security Highlights
- **Prepared Statements (`mysqli_prepare`):** Protects against SQL Injection on all authentication and data forms.
- **Password Hashing:** Uses PHP's `password_hash()` with `PASSWORD_DEFAULT` (Bcrypt) and verification via `password_verify()`.
- **Session Protection:** Prevents unauthorized URL access; students cannot access `warden.php` and wardens cannot submit student complaints.
- **XSS Protection:** All user-supplied data is escaped with `htmlspecialchars()` before rendering.
