
# Teacher Course Allocation System

## Overview
The **Teacher Course Allocation System** is a web-based application developed using **PHP, MySQL, HTML, CSS, and JavaScript**. It allows the **Head of Department (HOD)** to manage and assign courses, labs, and electives to teachers based on their workload capacity. The system ensures an efficient allocation process while keeping track of faculty workload distribution.

## Features
- **User Authentication**: Secure login for HOD and faculty members.
- **Department Management**: Supports **CS (Computer Science) and AI (Artificial Intelligence)** departments.
- **Workload Allocation**: 
  - **Courses (Electives)** → Counted as **1 workload (hours per week)**
  - **Labs** → Counted as **2 workloads (hours per week)**
- **Course Selection**:
  - Faculty members apply for available courses/labs.
  - HOD selects faculty based on available vacancies.
- **Multi-Section Support**:
  - **CS Department** → 3 sections.
  - **AI Department** → Section count varies.
- **Real-Time Updates**: Workload is automatically updated after allocation.

## User Roles & Credentials
```plaintext
Role       | Username   | Password
-----------|-----------|---------
AI Faculty | rajesh    | rajesh
CS Faculty | sreevidhya | sreevidhya
HOD        | hod       | hod
```

## Installation & Setup
### **Requirements**
- **WAMP Server** (Windows, Apache, MySQL, PHP)
- **Web Browser** (Chrome, Firefox, Edge, etc.)

### **Steps to Run the Project**
1. **Download & Install WAMP**: [Download WAMP](https://www.wampserver.com/en/)
2. **Clone or Download the Project**:
   ```sh
   git clone https://github.com/your-repo/teacher-course-allocation.git
   ```
   OR manually download and extract the files inside `C:\wamp64\www\teacher_course_allocation`.
3. **Start WAMP Server** and ensure Apache & MySQL services are running.
4. **Import the Database**:
   - Open `phpMyAdmin` (http://localhost/phpmyadmin/)
   - Create a new database: `teacher_allocation`
   - Import the provided `database.sql` file.
5. **Run the Project**:
   - Open a browser and go to:
     ```
     http://localhost/teacher_course_allocation/
     ```
   - Login using the provided credentials.
6. **Start Allocating Courses!** 🎯

## Security Note
🚨 **Before deploying this project**, make sure to:
- Change the default credentials.
- Restrict access to `config.php` to secure database credentials.
- Enable HTTPS in production environments.

## License
This project is released under the **MIT License**. Feel free to use and modify it for educational purposes.

## Contact
For queries or collaborations, reach out at **your.email@example.com**.

---
✅ **Now your README is professional and portfolio-ready!** 🚀 Let me know if you need any modifications. 🔥

