# Inventory Management System

A clean, minimalist, fully functional **Inventory Management System** built as an academic software engineering project. This system demonstrates core CRUD operations, MySQL database connectivity, session-based authentication, validation checks, and stock threshold status handling.

---

## Technologies Used

* **Backend:** PHP 8+
* **Database:** MySQL
* **Frontend:** HTML5, CSS3, JavaScript
* **CSS Framework:** Bootstrap 5
* **Icons:** Bootstrap Icons
* **Database Driver:** PHP PDO (Prepared Statements)
* **Environment:** XAMPP (Apache + MySQL)
* **Database Manager:** phpMyAdmin

---

## Requirements

* XAMPP (with Apache and MySQL modules)
* PHP 8.0 or newer
* MySQL 5.7+ / MariaDB 10+
* Modern web browser (Chrome, Firefox, Edge, Safari)

---

## Installation & Setup

Follow these steps to set up and run the application locally:

1. **Download and Install XAMPP**: Ensure you have XAMPP installed on your system.
2. **Start Services**: Open the XAMPP Control Panel and start the **Apache** and **MySQL** modules.
3. **Deploy Project**:
   * Copy the entire project folder `Inventory Management System` into the XAMPP `htdocs` directory.
   * Rename the project folder to `inventory-management-system` so that it is served under `http://localhost/inventory-management-system/`.
4. **Import Database Schema**:
   * Open your web browser and navigate to `http://localhost/phpmyadmin/`.
   * Click **New** in the left sidebar to create a new database.
   * Enter `inventory_management` as the database name and click **Create**.
   * Select the newly created `inventory_management` database, then click on the **Import** tab at the top.
   * Click **Browse...** or **Choose File** and select the SQL file located at:
     `database/inventory_management.sql`
   * Scroll down and click the **Import** / **Go** button.
5. **Run the Application**:
   * Open your web browser and go to:
     `http://localhost/inventory-management-system/`

---

## Administrator Demo Credentials

To log into the system, use the following credentials:

* **Username:** `admin`
* **Password:** `admin123`

---

## Core Features Implemented

1. **Authentication:** Session security checks prevent unauthenticated users from accessing administrative pages.
2. **Dashboard KPIs:** Dynamic calculation of total products, total stock, low stock warnings, and out of stock warnings.
3. **Category CRUD:** Complete create, read, update, and delete actions for categories with uniqueness validation and delete constraints checking.
4. **Product CRUD:** Complete creation, reading, editing, and deleting of products.
5. **Real-time Filters:** Search by product name or code, and filter by categories or stock level status dynamically via database queries.
6. **Automatic Stock Statuses:** Stock badges are dynamically evaluated on the fly:
   * `Quantity = 0` &rarr; **Out of Stock** (Red badge)
   * `Quantity > 0 AND Quantity <= Minimum Stock` &rarr; **Low Stock** (Warning badge)
   * `Quantity > Minimum Stock` &rarr; **In Stock** (Success badge)
7. **Security Measures:** SQL Injection prevention via PDO prepared statements, XSS mitigation via output escaping, and CSRF prevention using session token checks.
