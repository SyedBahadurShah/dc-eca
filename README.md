# Registration, Authentication, and Login System

## Overview
This project implements a robust registration, authentication, and login system for managing user accounts in an educational context. It includes different user roles such as students, teachers, student leaders, and heads of extracurricular activities (ECAs). Each role has specific features and functionalities tailored to their needs.

## Features

### Common Features
- **Home Page**: 
  - Logo navigation for quick access.
  - Login and language change buttons available on all pages.
  - Photo background carousel with rules and instructions.
  - "GET STARTED" button leading to the registration page.

### Registration Page
- User registration with email, password, year group, and account type.
- Account types: Student, Teacher, Student Leader, Head of ECAs.
- Email validation to match specific server and domain formats.
- Standard password requirements: 8 characters minimum, one uppercase letter, one digit, one special character.
- Email verification system with unique token generation.
- User receives a flash message to check their email upon successful registration.

### Login Page
- Basic login functionality for accessing the home page.
- Links for new users to register.

### User Dashboards
- **Student Dashboard**: 
  - Displays recent ECA transactions and available activities based on year group.
  - Options for searching and filtering activities.
  - Application forms for invite-only ECAs.
  
- **Teacher Dashboard**: 
  - Lists students who are frequently absent or late.
  - Attendance management and messaging features for clubs.
  
- **Student Leader Dashboard**: 
  - Combines features from both student and teacher dashboards.

- **Head of ECAs Dashboard**: 
  - Global and local messaging for sending notifications.
  - Real-time ECA data manipulation.

### Notifications
- Notification system for alerts about applications, submissions, and changes in status.

### Settings
- Option for users to change their passwords securely.

## Getting Started

### Prerequisites
- Web server (Apache, Nginx, etc.)
- Database (MySQL, PostgreSQL, etc.)
- Backend framework (Node.js, Flask, Django, etc. depending on your implementation)
- Frontend framework (React, Vue, etc. depending on your implementation)

### Installation
1. Clone the repository:
   ```bash
   git clone https://github.com/syedbahadurshah/registration-authentication-system.git
   cd registration-authentication-system
