DROP DATABASE IF EXISTS `skill-share`;

CREATE DATABASE IF NOT EXISTS `skill-share`;
USE `skill-share`;

CREATE TABLE `user` (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    avatar VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    email_token VARCHAR(100),
    is_verified BOOLEAN,
    verified_at DATETIME,
    password VARCHAR(255) NOT NULL,
    `role` JSON NOT NULL,
    created_at DATETIME
);

CREATE TABLE skill (
    id_skill INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    descriptions TEXT,
    etat ENUM('propose','recherche') NOT NULL,
    created_at DATETIME,
    updated_at DATETIME,
    FOREIGN KEY (id_user) REFERENCES `user`(id_user)
);

CREATE TABLE share (
    id_share INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    id_skill INT NOT NULL,
    etat ENUM('en attente','accepté','rejeté','terminé') DEFAULT 'en attente',
    descriptions TEXT,
    created_at DATETIME,
    updated_at DATETIME,
    FOREIGN KEY (id_user) REFERENCES `user`(id_user),
    FOREIGN KEY (id_skill) REFERENCES skill(id_skill)
);

CREATE TABLE rating (
    id_rating INT AUTO_INCREMENT PRIMARY KEY,
    id_share INT NOT NULL,
    id_user INT NOT NULL,
    rating_value TINYINT NOT NULL CHECK (rating_value BETWEEN 1 AND 5),
    commentaire TEXT,
    created_at DATETIME,
    updated_at DATETIME,
    FOREIGN KEY (id_share) REFERENCES `share`(id_share),
    FOREIGN KEY (id_user) REFERENCES `user`(id_user)
);