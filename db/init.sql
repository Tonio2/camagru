CREATE DATABASE IF NOT EXISTS db;

USE db;

CREATE TABLE IF NOT EXISTS users (
	id INT AUTO_INCREMENT PRIMARY KEY,
	username VARCHAR(255) NOT NULL UNIQUE,
	email VARCHAR(255) NOT NULL UNIQUE,
	password VARCHAR(255) NOT NULL,
	email_validated BOOLEAN DEFAULT 0,
	email_validation_code VARCHAR(255),
	password_reset_code VARCHAR(255),
	email_preference BOOLEAN DEFAULT 1
);

CREATE TABLE IF NOT EXISTS pictures (
	id INT AUTO_INCREMENT PRIMARY KEY,
	user_id INT NOT NULL,
	src VARCHAR(255) NOT NULL,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS likes (
    user_id INT NOT NULL,
    picture_id INT NOT NULL,
    FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY(picture_id) REFERENCES pictures(id) ON DELETE CASCADE,
		PRIMARY KEY (user_id, picture_id)
);

CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    picture_id INT NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY(picture_id) REFERENCES pictures(id) ON DELETE CASCADE
);