--DB erstellen (if not exist)

CREATE DATABASE  IF NOT EXIST 'motorcycle.db'
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;


USE 'motorcycle.db';

--1.Kunden tabbele 

CREATE TABLE IF NOT EXIST 'kunden'
(
    'id'  INT AUTO_INCREMENT PRIMARY KEY,
    'vorname' VARCHAR(50) NOT NULL ,
    'nachname' VARCHAR(50) NOT NULL,
    'telefon' VARCHAR(30)  NULL,
    'email' VARCHAR(100)  NULL,
    'create_at' TIMESTAMP DEFAULT CURRENT_TIMETAPS

) ENGINE=Innodb DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--2.Fahrzeuge tabbele

CREATE TABLE IF NOT EXISTS `fahrzeuge` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `marke` VARCHAR(50) NOT NULL,
    `modell` VARCHAR(50) NOT NULL,
    `kennzeichen` VARCHAR(20) NOT NULL UNIQUE,
    `baujahr` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



--3 Zwischentabbele ( K <-> F) n:m

CREATE TABLE IF NOT EXISTS `kunde_fahrzeug` (
    `kunde_id` INT NOT NULL,
    `fahrzeug_id` INT NOT NULL,
    PRIMARY KEY (`kunde_id`, `fahrzeug_id`),
    CONSTRAINT `fk_kf_kunde` FOREIGN KEY (`kunde_id`) REFERENCES `kunden` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_kf_fahrzeug` FOREIGN KEY (`fahrzeug_id`) REFERENCES `fahrzeuge` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tabelle Wartung/Inspektion (Wartungen) – 1:n-Beziehung zu fahrzeuge

CREATE TABLE IF NOT EXISTS `wartungen` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `fahrzeug_id` INT NOT NULL,
    `datum` DATE NOT NULL,
    `km_stand` INT NOT NULL,
    `beschreibung` TEXT NOT NULL,
    `kosten` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_wartung_fahrzeug` FOREIGN KEY (`fahrzeug_id`) REFERENCES `fahrzeuge` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;