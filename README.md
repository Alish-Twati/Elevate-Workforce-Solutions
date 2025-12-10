# Elevate Workforce Solutions

> A comprehensive job portal application connecting job seekers with employers in Nepal. 

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![PHP](https://img.shields.io/badge/PHP-8.0+-purple.svg)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-orange.svg)
![License](https://img.shields.io/badge/license-MIT-green.svg)

---

## 📋 Table of Contents

- [About](#about)
- [Features](#features)
- [Screenshots](#screenshots)
- [Technology Stack](#technology-stack)
- [Installation](#installation)
- [Usage](#usage)
- [Project Structure](#project-structure)
- [Documentation](#documentation)
- [Testing](#testing)
- [Contributing](#contributing)
- [License](#license)
- [Author](#author)

---

## 📖 About

Elevate Workforce Solutions is a modern, full-featured job portal application designed to streamline the recruitment process.  Built as an academic project for Unit 22: Application Development, this system demonstrates professional software engineering practices including:

- Object-Oriented Programming (OOP)
- Model-View-Controller (MVC) Architecture
- Secure Authentication & Authorization
- RESTful Design Principles
- Database Normalization
- Responsive Web Design

---

## ✨ Features

### For Job Seekers
- ✅ User registration and authentication
- 🔍 Advanced job search and filtering
- 📄 One-click job applications
- 📊 Application tracking dashboard
- 📱 Mobile-responsive interface
- 📥 Resume upload and management

### For Companies
- 🏢 Company profile management
- ➕ Easy job posting interface
- 📋 Application management system
- 📈 Dashboard analytics
- ✏️ Edit and update job listings
- 👥 Applicant review and tracking

### Security Features
- 🔒 BCrypt password hashing
- 🛡️ SQL injection protection
- 🚫 XSS prevention
- 🔐 CSRF token validation
- ✔️ File upload validation
- 🔑 Role-based access control

---

## 📸 Screenshots

### Homepage
![Homepage](docs/screenshots/homepage.png)

### Job Listings
![Job Listings](docs/screenshots/jobs.png)

### Dashboard
![Dashboard](docs/screenshots/dashboard.png)

---

## 🛠️ Technology Stack

**Backend:**
- PHP 8.0+
- MySQL 8.0+
- PDO for database operations

**Frontend:**
- HTML5
- CSS3
- Bootstrap 5. 3
- JavaScript (ES6+)
- Font Awesome Icons

**Architecture:**
- MVC Pattern
- Object-Oriented Programming
- Singleton Pattern (Database)
- Front Controller Pattern

**Development Tools:**
- XAMPP
- Visual Studio Code
- Git & GitHub
- phpMyAdmin

---

## 🚀 Installation

### Prerequisites

- XAMPP (or any Apache + PHP + MySQL stack)
- PHP 8.0 or higher
- MySQL 8.0 or higher
- Web browser (Chrome, Firefox, Edge, Safari)

### Quick Start

1. **Clone the repository**
   ```bash
   git clone https://github.com/Alish-Twati/elevate-workforce-solutions.git
   cd elevate-workforce-solutions
   ```

2. **Move to XAMPP directory**
   ```bash
   # Windows
   move elevate-workforce-solutions C:\xampp\htdocs\
   
   # Linux/Mac
   sudo mv elevate-workforce-solutions /opt/lampp/htdocs/
   ```

3. **Create database**
   - Open phpMyAdmin: `http://localhost/phpmyadmin`
   - Create database: `elevate_jobs`
   - Import: `database/schema.sql`
   - Import sample data: `database/seed.sql`

4. **Configure application**
   - Open `config/database.php`
   - Verify database credentials (default XAMPP settings should work)

5. **Set permissions**
   ```bash
   chmod 755 public/uploads
   ```

6. **Access application**
   - Open browser: `http://localhost/elevate-workforce-solutions/`

### Default Login Credentials

**Admin:**
- Email: `admin@elevate. com`
- Password: `admin123`

**Company:**
- Email: `hr@technepal.com`
- Password: `company123`

**Job Seeker:**
- Email: `john. doe@email.com`
- Password: `jobseeker123`

---

## 📘 Usage

### For Job Seekers

1. **Register** - Create your account
2. **Search Jobs** - Browse available positions
3. **Apply** - Submit your application with resume
4. **Track** - Monitor application status

### For Companies

1. **Register** - Create company account
2. **Profile** - Complete company information
3. **Post Jobs** - Create job listings
4. **Review** - Manage incoming applications

For detailed instructions, see [User Manual](docs/USER_MANUAL.md).

---

## 📁 Project Structure

```
elevate-workforce-solutions/
│
├── config/                 # Configuration files
│   ├── config.php
│   └── database.php
│
├── models/                 # Data models
│   ├── Database.php
│   ├── User.php
│   ├── Company.php
│   ├── Job.php
│   ├── Application.php
│   └── Category.php
│
├── controllers/            # Business logic
│   ├── AuthController.php
│   ├── JobController.php
│   ├── ApplicationController.php
│   ├── DashboardController.php
│   └── CompanyController.php
│
├── views/                  # Presentation layer
│   ├── layouts/
│   ├── auth/
│   ├── jobs/
│   ├── applications/
│   ├── dashboard/
│   ├── company/
│   └── home. php
│
├── public/                 # Public assets
│   ├── css/
│   ├── js/
│   ├── images/
│   └── uploads/
│
├── helpers/                # Helper functions
│   ├── functions.php
│   └── Session.php
│
├── database/               # Database files
│   ├── schema.sql
│   └── seed.sql
│
├── docs/                   # Documentation
│   ├── INSTALLATION.md
│   ├── USER_MANUAL.md
│   └── TESTING.md
│
├── . htaccess
├── .gitignore
├── index.php
└── README.md
```

---

## 📚 Documentation

Comprehensive documentation is available:

- [Installation Guide](docs/INSTALLATION.md) - Setup instructions
- [User Manual](docs/USER_MANUAL.md) - How to use the system
- [Testing Documentation](docs/TESTING.md) - Test cases and results
- [API Documentation](docs/API. md) - API endpoints (if applicable)

---

## 🧪 Testing

The application has been thoroughly tested:

- ✅ **75 test cases** executed
- ✅ **97.3% pass rate**
- ✅ All security tests passed
- ✅ Performance benchmarks met

See [Testing Documentation](docs/TESTING.md) for detailed results.

---

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3.  Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

---

## 📋 Assignment Compliance

This project fulfills all requirements for:

**Unit 22: Application Development**

**Pass Criteria:**
- ✅ P1: Problem definition statement
- ✅ P2: Risk assessment
- ✅ P3: Tool research
- ✅ P4: Peer review
- ✅ P5: Functional application
- ✅ P6: Performance review

**Merit Criteria:**
- ✅ M1: Software design document
- ✅ M2: Justified tool selection
- ✅ M3: Peer feedback interpretation
- ✅ M4: Evidence of methodology
- ✅ M5: Critical review

**Distinction Criteria:**
- ✅ D1: Tool evaluation
- ✅ D2: Justified improvements
- ✅ D3: Professional presentation

---

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details. 

---

## 👨‍💻 Author

**Alish Twati**

- GitHub: [@Alish-Twati](https://github.com/Alish-Twati)
- Email: alish.twati@example.com
- Institution: International School of Management & Technology Nepal

---

## 🙏 Acknowledgments

- **ISMT Nepal** - For academic support
- **Bipin Dhakal** - Assessor and mentor
- **Bootstrap Team** - For the excellent framework
- **PHP Community** - For comprehensive documentation
- **Stack Overflow** - For problem-solving assistance

---

## 📞 Support

For issues, questions, or suggestions:

1. Check the [User Manual](docs/USER_MANUAL.md)
2. Review [Installation Guide](docs/INSTALLATION.md)
3. Open an issue on GitHub
4. Contact the author

---

## 🗺️ Roadmap

Future enhancements planned:

- [ ] Email notification system
- [ ] Advanced search filters
- [ ] Resume builder tool
- [ ] Video interview integration
- [ ] Mobile application
- [ ] AI-powered job matching
- [ ] Chat system
- [ ] Payment integration for premium features

---

## ⭐ Show Your Support

If you found this project helpful, please give it a ⭐️! 

---

**Made with ❤️ in Nepal** 🇳🇵

Last Updated: June 2025