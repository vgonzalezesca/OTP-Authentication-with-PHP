# OTP Authentication with PHP and PHPMailer

This project demonstrates a simple **OTP (One-Time Password) authentication system** using **PHP**, **MySQL**, and **PHPMailer**.  
The user enters their email, receives a 6-digit OTP via email, and then verifies it to log in.

---

## ✨ Features
- Generate random 6-digit OTP.  
- Send OTP via **Microsoft Exchange Online SMTP** using PHPMailer.
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

5. **Configure Microsoft Exchange Online**
   Define las variables de `.env.example` en el entorno del servidor (no guardes la contraseña en Git):
   ```text
   GESEX_SMTP_HOST=smtp.office365.com
   GESEX_SMTP_PORT=587
   GESEX_SMTP_SECURE=tls
   GESEX_SMTP_USERNAME=alertas@geexsa.com
   GESEX_SMTP_PASSWORD=la-clave-del-buzon-alertas
   GESEX_SMTP_FROM=wifivisitas@geexsa.com
   GESEX_SMTP_FROM_NAME=Visitas GESEX
   ```
   El buzón `alertas@geexsa.com` debe tener SMTP AUTH habilitado y permiso **Send As** sobre el alias `wifivisitas@geexsa.com`.

---

## 🚀 Usage
1. Open the app in the browser (`http://localhost/project-folder/portal.php`).
2. Enter your email address.  
3. Check your email inbox for the OTP code.  
4. Enter the OTP on the form to verify.  

## 🐳 Despliegue con Docker en Ubuntu Server

Requisitos: Docker Engine y Docker Compose Plugin.

1. Copia la configuración y edita todas las claves:
   ```bash
   cp .env.example .env
   nano .env
   ```
2. Construye y levanta la aplicación y MySQL:
   ```bash
   docker compose up -d --build
   docker compose ps
   docker compose logs -f app
   ```
3. Accede a `http://IP_DEL_SERVIDOR:8080/portal.php`.

La aplicación queda en el puerto `8080` del servidor y Apache escucha en el puerto 80 dentro del contenedor. Puedes cambiar el puerto externo con `APP_PORT`. Los datos de MySQL quedan persistidos en el volumen `mysql_data`.

El archivo `.env` no debe subirse al repositorio. Para una base de datos MySQL existente, usa el servicio `app` con `GESEX_DB_HOST`, `GESEX_DB_PORT`, `GESEX_DB_NAME`, `GESEX_DB_USER` y `GESEX_DB_PASSWORD`, y no levantes el servicio `mysql` incluido.

Para integrar el portal con FortiGate, cambia `GESEX_SUCCESS_URL` por la URL de éxito que corresponda a tu instalación.

---

## 📌 Notes
- Exchange Online debe permitir SMTP AUTH para el buzón utilizado.
- Use **PHP ≥ 7.4** and **MySQL ≥ 5.7** for best compatibility.  
- OTPs are stored in the `users` table under the `otp` column.  

---

## 📜 License
This project is licensed under the MIT License.
