CREATE DATABASE IF NOT EXISTS outpass_db;
USE outpass_db;

-- Drop tables if exist to reset with new columns/data
DROP TABLE IF EXISTS requests;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'parent', 'coordinator', 'hod', 'gate') NOT NULL,
    student_reg_no VARCHAR(100),
    department VARCHAR(100),
    class_batch VARCHAR(100)
);

CREATE TABLE requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('check_in', 'check_out') NOT NULL,
    reason TEXT NOT NULL,
    out_date DATE NULL,
    out_time TIME NULL,
    status ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending',
    coordinator_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    hod_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    gate_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Seed Data: Logic for Arts & Science Departments
-- Depts: 'BCA', 'B.sc(CS)'

INSERT INTO users (name, email, password, role, department, class_batch) VALUES 
-- BCA Staff
('BCA Coordinator', 'bca_coord@college.com', 'admin123', 'coordinator', 'BCA', ''),
('BCA HOD', 'bca_hod@college.com', 'admin123', 'hod', 'BCA', ''),

-- B.sc(CS) Staff
('CS Coordinator', 'cs_coord@college.com', 'admin123', 'coordinator', 'B.sc(CS)', ''),
('CS HOD', 'cs_hod@college.com', 'admin123', 'hod', 'B.sc(CS)', ''),

-- College Gate (Common)
('Main Gate', 'gate@college.com', 'admin123', 'gate', 'Gate', 'Gate');
