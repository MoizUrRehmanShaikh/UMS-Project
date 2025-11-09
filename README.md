
# UMS-Project (University Management System)

This is a **University Management System (UMS)** project, developed as a 2nd Semester project. It is a web-based application designed to manage various university-related tasks and information.

---

## 
Features

* **User Authentication:** Secure login and registration system for students and staff.
* **Admin Panel:** A dedicated dashboard for administrators to manage a wide range of system functionalities.
* **User Dashboard:** A personalized dashboard for users (students/faculty) to access relevant information and features.
* **Database Integration:** Manages data efficiently using a SQL database.

---

## 
Technologies Used

This project is built using a classic web stack:

* **Frontend:**
    * HTML
    * CSS
    * JavaScript
* **Backend:**
    * PHP
* **Database:**
    * MySQL (inferred from `sql.sql` file)

---

## 
Project Structure

The repository contains the following key directories and files:


/
├── admin/         # Contains all administrative panel files
├── config/        # Database connection and configuration files
├── css/           # CSS stylesheets
├── dashboard/     # User dashboard files
├── pictures/      # Image and media assets
├── public/        # Publicly accessible files
├── index.php      # Main landing/home page
├── login.php      # User login page
├── register.php   # User registration page
├── logout.php     # Handles user logout
├── sql.sql        # The SQL database schema and initial data
└── ...

---

## 
How To Use

### Prerequisites

* A web server (like Apache)
* PHP
* A MySQL database server

### Installation

1.  **Clone the repository:**
    ```bash
    git clone [https://github.com/MoizUrRehmanShaikh/UMS-Project.git](https://github.com/MoizUrRehmanShaikh/UMS-Project.git)
    ```
2.  **Move to your web server directory:**
    * Move the cloned project folder to your web server's root directory (e.g., `htdocs` for XAMPP, `www` for WAMP).
3.  **Import the Database:**
    * Create a new database in your MySQL server (e.g., via phpMyAdmin).
    * Import the `sql.sql` file into the newly created database.
4.  **Configure Database Connection:**
    * Navigate to the `config` folder.
    * Update the database connection file (likely `config.php` or similar) with your database name, username, and password.
5.  **Run the application:**
    * Open your web browser and navigate to the project's local URL (e.g., `http://localhost/UMS-Project`).

