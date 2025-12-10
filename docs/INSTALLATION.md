# Installation Guide - Elevate Workforce Solutions

Complete step-by-step installation guide for XAMPP environment.

## Prerequisites

- XAMPP (Apache, MySQL, PHP 8.0+)
- Visual Studio Code (recommended)
- Web Browser (Chrome, Firefox, Edge)
- Git (optional)

---

## Step 1: Install XAMPP

1. Download XAMPP from: https://www.apachefriends.org/
2. Run the installer
3. Install to default location: `C:\xampp`
4. Complete installation

---

## Step 2: Start XAMPP Services

1. Open XAMPP Control Panel
2. Click **Start** on Apache
3. Click **Start** on MySQL
4.  Verify both services are running (green indicators)

---

## Step 3: Setup Project Files

### Option A: Download ZIP

1. Download the project ZIP file
2. Extract to: `C:\xampp\htdocs\elevate-workforce-solutions\`

### Option B: Git Clone

```bash
cd C:\xampp\htdocs
git clone https://github.com/Alish-Twati/elevate-workforce-solutions.git
```

---

## Step 4: Create Database

1. Open browser and navigate to: `http://localhost/phpmyadmin`
2. Click **New** in left sidebar
3. Enter database name: `elevate_jobs`
4. Select collation: `utf8mb4_unicode_ci`
5. Click **Create**

---

## Step 5: Import Database Schema

1. Click on `elevate_jobs` database (left sidebar)
2. Click **Import** tab
3. Click **Choose File**
4. Navigate to: `C:\xampp\htdocs\elevate-workforce-solutions\database\schema.sql`
5. Click **Go**
6. Wait for "Import has been successfully finished" message

---

## Step 6: Import Sample Data (Optional)

1. Still in `elevate_jobs` database
2. Click **Import** tab again
3. Choose file: `database\seed.sql`
4. Click **Go**
5.  Verify success message

---

## Step 7: Configure Application

1. Open project in VS Code
2. Navigate to `config/database.php`
3.  Verify configuration:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'elevate_jobs');
define('DB_USER', 'root');
define('DB_PASS', '');
```

4. Save file (usually no changes needed for XAMPP)

---

## Step 8: Set Folder Permissions

1. Navigate to `public/uploads` folder
2. Right-click → Properties → Security
3. Click **Edit** → Select your user
4. Check **Full Control**
5. Click **Apply** → **OK**

---

## Step 9: Access Application

1.  Open web browser
2. Navigate to: `http://localhost/elevate-workforce-solutions/`
3. You should see the homepage

---

## Step 10: Test Login

### Admin Account
- Email: `admin@elevate.com`
- Password: `admin123`

### Company Account
- Email: `hr@technepal.com`
- Password: `company123`

### Job Seeker Account
- Email: `john.doe@email. com`
- Password: `jobseeker123`

---

## Troubleshooting

### Problem: "Database connection failed"

**Solution:**
1. Verify MySQL is running in XAMPP
2. Check database name in phpMyAdmin
3. Verify credentials in `config/database.php`

### Problem: "Apache won't start - Port 80 in use"

**Solution:**
1. Stop IIS or other web servers
2. OR change Apache port:
   - Open `C:\xampp\apache\conf\httpd.conf`
   - Find `Listen 80`
   - Change to `Listen 8080`
   - Restart Apache
   - Access via `http://localhost:8080/`

### Problem: "MySQL won't start - Port 3306 in use"

**Solution:**
1. Open XAMPP Control Panel → Config (MySQL) → my.ini
2. Change `port=3306` to `port=3307`
3. Update `config/database.php` accordingly
4. Restart MySQL

### Problem: "File upload fails"

**Solution:**
1. Check folder permissions on `public/uploads`
2.  Verify `upload_max_filesize` in php.ini:
   - Open XAMPP Control Panel
   - Click Config (Apache) → php.ini
   - Find `upload_max_filesize`
   - Set to `10M` or higher
   - Restart Apache

### Problem: "Page not found" errors

**Solution:**
1.  Verify `. htaccess` file exists in project root
2. Enable mod_rewrite in Apache:
   - Open `C:\xampp\apache\conf\httpd.conf`
   - Find `#LoadModule rewrite_module modules/mod_rewrite.so`
   - Remove the `#` to uncomment
   - Restart Apache

---

## Post-Installation Checklist

- [ ] Apache running
- [ ] MySQL running
- [ ] Database created and imported
- [ ] Can access homepage
- [ ] Can login as admin
- [ ] Can login as company
- [ ] Can login as job seeker
- [ ] Can register new user
- [ ] Can post a job (as company)
- [ ] Can apply for job (as job seeker)
- [ ] File uploads working

---

## Next Steps

1.  Read `USER_MANUAL.md` for usage instructions
2. Review `TESTING.md` for testing procedures
3.  Customize the application as needed
4. Start using the system! 

---

## Support

For issues or questions:
- Check the troubleshooting section
- Review error logs in `C:\xampp\apache\logs\error.log`
- Consult the README. md file

---

**Installation Complete!  🎉**

You're now ready to use Elevate Workforce Solutions. 