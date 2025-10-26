-- ORGANISED AND SIMPLIFIED UNIVERSITY MANAGEMENT SYSTEM DDL

-- 1. DATABASE SETUP
-- -------------------------------------------------------------------
CREATE DATABASE IF NOT EXISTS tandojam_ums;
USE tandojam_ums;

-- Optional: Drop tables if they exist to start fresh
DROP TABLE IF EXISTS attendance;
DROP TABLE IF EXISTS enrollment;
DROP TABLE IF EXISTS course_assignments;
DROP TABLE IF EXISTS courses;
DROP TABLE IF EXISTS teachers;
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS admins;


-- 2. USER TABLES (Simplified for Project Requirement)
-- -------------------------------------------------------------------

-- Admin Table (For single, fixed admin access)
CREATE TABLE admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL
);

-- Teachers Table (Self-contained registration/login data)
CREATE TABLE teachers (
    teacher_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL -- Full Name
);

-- Students Table (Self-contained registration/login data + new fields)
CREATE TABLE students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,              -- Full Name
    registration_number VARCHAR(20) UNIQUE NOT NULL,
    department VARCHAR(50) NOT NULL,
    year VARCHAR(20) NOT NULL
);


-- 3. ACADEMIC MANAGEMENT TABLES
-- -------------------------------------------------------------------

-- Courses Table
CREATE TABLE courses (
    course_id INT AUTO_INCREMENT PRIMARY KEY,
    course_code VARCHAR(10) UNIQUE NOT NULL,
    course_name VARCHAR(100) NOT NULL
);

-- Course Assignments (Linking Teacher to Course)
CREATE TABLE course_assignments (
    assignment_id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT,
    teacher_id INT,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES teachers(teacher_id) ON DELETE CASCADE
);

-- Enrollment (Linking Student to Course)
CREATE TABLE enrollment (
    enrollment_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    course_id INT,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE,
    UNIQUE KEY (student_id, course_id)
);

-- Attendance Table
CREATE TABLE attendance (
    attendance_id INT AUTO_INCREMENT PRIMARY KEY,
    enrollment_id INT,
    date DATE NOT NULL,
    status ENUM('Present', 'Absent', 'Late') NOT NULL,
    FOREIGN KEY (enrollment_id) REFERENCES enrollment(enrollment_id) ON DELETE CASCADE,
    UNIQUE KEY (enrollment_id, date)
);

USE tandojam_ums;

-- Table to store final grades/results for students in a course
CREATE TABLE grades (
    grade_id INT AUTO_INCREMENT PRIMARY KEY,
    enrollment_id INT NOT NULL,
    exam_type VARCHAR(50) NOT NULL, -- e.g., 'Midterm', 'Final', 'Assignment 1'
    score DECIMAL(5, 2),            -- Optional: Store raw score
    final_grade VARCHAR(5) NOT NULL, -- e.g., 'A+', 'B', 'F'
    date_recorded DATE NOT NULL,
    FOREIGN KEY (enrollment_id) REFERENCES enrollment(enrollment_id) ON DELETE CASCADE,
    UNIQUE KEY (enrollment_id, exam_type) -- Ensures only one entry per student per exam type
);

select * from students;
select * from teachers;