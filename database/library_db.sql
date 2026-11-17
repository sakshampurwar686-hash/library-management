-- Library Management System schema
CREATE DATABASE IF NOT EXISTS library_db CHARACTER SET utf8mb4;
USE library_db;

CREATE TABLE IF NOT EXISTS admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS books (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  author VARCHAR(150) NOT NULL,
  category VARCHAR(100) NOT NULL,
  isbn VARCHAR(20) NOT NULL UNIQUE,
  quantity INT NOT NULL DEFAULT 1,
  available_quantity INT NOT NULL DEFAULT 1,
  added_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS students (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  phone VARCHAR(20),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- return_date = due date; returned_on = actual return date
CREATE TABLE IF NOT EXISTS issued_books (
  id INT AUTO_INCREMENT PRIMARY KEY,
  book_id INT NOT NULL,
  student_id INT NOT NULL,
  issue_date DATE NOT NULL,
  return_date DATE NOT NULL,
  returned_on DATE NULL,
  status ENUM('issued','returned') NOT NULL DEFAULT 'issued',
  FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE RESTRICT,
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE RESTRICT
);

-- Default admin: admin@library.com / admin123
INSERT INTO admins (name,email,password) VALUES ('Librarian','admin@library.com','$2y$12$FYiqgGcr4aF55Cc9EhFLG.j7od0dzuKJC/wG3Rt0g4p8XwE4tUnqG');

-- Sample data
INSERT INTO books (title,author,category,isbn,quantity,available_quantity) VALUES
('Clean Code','Robert C. Martin','Programming','9780132350884',5,5),
('The Alchemist','Paulo Coelho','Fiction','9780062315007',3,3),
('Sapiens','Yuval Noah Harari','History','9780062316097',4,4);
INSERT INTO students (name,email,phone) VALUES
('Aarav Sharma','aarav@example.com','9876543210'),
('Priya Verma','priya@example.com','9123456780');
