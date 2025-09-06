# OTP Authentication with PHP and PHPMailer

This project demonstrates a simple **OTP (One-Time Password) authentication system** using **PHP**, **MySQL**, and **PHPMailer**.  
The user enters their email, receives a 6-digit OTP via email, and then verifies it to log in.

---

## ✨ Features
- Generate random 6-digit OTP.  
- Send OTP via **Gmail SMTP** using PHPMailer.  
- Store OTP securely in the database (in the `users` table).  
- Verify user’s OTP input.  
- Simple and easy-to-understand codebase.  

---

## 🛠️ Technologies Used
- **PHP** (Core logic)  
- **MySQL** (Database for users and OTP)  
- **PHPMailer** (Email sending via SMTP)  
- **HTML + CSS** (Frontend form)  

---

## 📂 Project Structure
```
project-folder/
│── otp.php          # Main file (send & verify OTP)
│── database.php     # Database connection
│── vendor/          # PHPMailer dependencies (via Composer)
│── assets/css/      # Styles
```

---

## ⚙️ Setup Instructions

1. **Clone the repository**
   ```bash
   git clone https://github.com/your-username/otp-auth-php.git
   cd otp-auth-php
   ```

2. **Install PHPMailer with Composer**
   ```bash
   composer require phpmailer/phpmailer
   ```

3. **Create Database**
   ```sql
   CREATE DATABASE auth_system;

   CREATE TABLE users (
       id INT AUTO_INCREMENT PRIMARY KEY,
       username VARCHAR(100) NOT NULL,
       email VARCHAR(150) NOT NULL UNIQUE,
       mobile VARCHAR(11),
       password VARCHAR(100),
       otp VARCHAR(6)
   );
   ```

4. **Configure Database Connection**  
   Update your `database.php`:
   ```php
   <?php
   $conn = new mysqli("localhost", "root", "", "auth_system");
   if($conn->connect_error){
       die("Connection failed: " . $conn->connect_error);
   }
   ?>
   ```

5. **Configure PHPMailer**  
   In `otp.php`, replace with your Gmail credentials:
   ```php
   $mail->Username   = 'your-email@gmail.com';
   $mail->Password   = 'your-app-password';
   ```

---

## 🚀 Usage
1. Open the app in the browser (`http://localhost/project-folder/otp.php`).  
2. Enter your email address.  
3. Check your email inbox for the OTP code.  
4. Enter the OTP on the form to verify.  

---

## 📌 Notes
- Make sure you **enable App Passwords** in your Gmail account.  
- Use **PHP ≥ 7.4** and **MySQL ≥ 5.7** for best compatibility.  
- OTPs are stored in the `users` table under the `otp` column.  

---

## 📜 License
This project is licensed under the MIT License.
