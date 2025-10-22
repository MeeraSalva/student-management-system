
DROP DATABASE IF EXISTS student_db;
CREATE DATABASE student_db;
USE student_db;

CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    dob DATE NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    course VARCHAR(100) NOT NULL,
    gender ENUM('Male','Female','Other') NOT NULL,
    address TEXT NOT NULL,
    status ENUM('Active','Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO students (student_id, name, dob, email, phone, course, gender, address, status) VALUES
('STD001','John Doe','2000-05-15','john@example.com','1234567890','Computer Science','Male','New York, USA','Active'),
('STD002','Jane Smith','1999-08-22','jane@example.com','0987654321','Business Administration','Female','London, UK','Active'),
('STD003','Mike Johnson','2001-03-10','mike@example.com','5551234567','Engineering','Male','Sydney, Australia','Active');
