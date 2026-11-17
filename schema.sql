-- Fix-It Desk: builds the whole database from nothing.
-- Run with:  mysql -u root -p < schema.sql
-- Safe to run again: it drops and rebuilds everything, restoring the starting data.

DROP DATABASE IF EXISTS helpdesk;
CREATE DATABASE helpdesk CHARACTER SET utf8mb4;
USE helpdesk;

CREATE TABLE users (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    firstname  VARCHAR(50)  NOT NULL,
    lastname   VARCHAR(50)  NOT NULL,
    email      VARCHAR(255) NOT NULL,
    password   VARCHAR(255) NOT NULL,
    role       ENUM('admin', 'member') NOT NULL DEFAULT 'member',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB;

CREATE TABLE tickets (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title       VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    priority    ENUM('Low', 'Medium', 'High') NOT NULL DEFAULT 'Medium',
    status      ENUM('Open', 'In progress', 'Closed') NOT NULL DEFAULT 'Open',
    created_by  INT UNSIGNED NOT NULL,
    assigned_to INT UNSIGNED NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_tickets_created_by  FOREIGN KEY (created_by)  REFERENCES users (id),
    CONSTRAINT fk_tickets_assigned_to FOREIGN KEY (assigned_to) REFERENCES users (id)
        ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE notes (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    ticket_id  INT UNSIGNED NOT NULL,
    body       TEXT NOT NULL,
    created_by INT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_notes_ticket     FOREIGN KEY (ticket_id)  REFERENCES tickets (id)
        ON DELETE CASCADE,
    CONSTRAINT fk_notes_created_by FOREIGN KEY (created_by) REFERENCES users (id)
) ENGINE=InnoDB;

INSERT INTO users (id, firstname, lastname, email, password, role, created_at) VALUES
(1, 'Admin',   'User',     'admin@fixit.test',   '$2y$10$bqYil7p61bzjWReUA4XAMegNqWSbczPjVUtW0AQ5MqeP3cGtchUUG', 'admin',  '2026-10-31 09:00:00'),
(2, 'Aaliyah', 'Thomas',   'aaliyah@fixit.test', '$2y$10$bqYil7p61bzjWReUA4XAMegNqWSbczPjVUtW0AQ5MqeP3cGtchUUG', 'member', '2026-10-31 09:00:00'),
(3, 'Keisha',  'Brown',    'keisha@fixit.test',  '$2y$10$bqYil7p61bzjWReUA4XAMegNqWSbczPjVUtW0AQ5MqeP3cGtchUUG', 'member', '2026-10-31 09:00:00'),
(4, 'Marcus',  'Campbell', 'marcus@fixit.test',  '$2y$10$bqYil7p61bzjWReUA4XAMegNqWSbczPjVUtW0AQ5MqeP3cGtchUUG', 'member', '2026-10-31 09:00:00');

INSERT INTO tickets (id, title, description, priority, status, created_by, assigned_to, created_at, updated_at) VALUES
(1, 'Printer in Room 4 jams on every page',
    'The printer in Room 4 jams as soon as it starts printing. Clearing the paper path does not help.',
    'Medium', 'Open', 3, 4, '2026-10-26 09:12:00', '2026-10-26 09:12:00'),
(2, 'Cannot connect to staff Wi-Fi',
    'My laptop sees the StaffNet network but says "Can''t connect". It worked last week.',
    'High', 'In progress', 2, 3, '2026-10-27 14:30:00', '2026-10-28 09:40:00'),
(3, 'Projector remote missing in Lab B',
    'The remote for the projector in Lab B has gone missing. We can only turn the projector on from the wall panel.',
    'Low', 'Open', 4, NULL, '2026-10-28 08:05:00', '2026-10-28 08:05:00'),
(4, 'Email bounces from the finance office',
    'Messages sent to the finance office come back as undeliverable. Other offices are fine.',
    'High', 'Open', 1, 1, '2026-10-29 11:47:00', '2026-10-29 11:47:00'),
(5, 'New starter needs an account',
    'A new member of staff starts on Monday and needs a login and an email address.',
    'Medium', 'Closed', 1, 3, '2026-10-20 10:00:00', '2026-10-21 15:20:00');

INSERT INTO notes (ticket_id, body, created_by, created_at) VALUES
(2, 'Forgot the network and joined again. Same error. Trying with a cable next.', 3, '2026-10-27 15:02:00'),
(2, 'The cable works, so it''s the Wi-Fi profile. Will reset the certificate tomorrow.', 3, '2026-10-28 09:40:00'),
(2, 'Reset the Wi-Fi certificate. Keisha says it connects now.', 1, '2026-10-31 21:52:00'),
(1, 'Checked the rollers. They look worn. Ordering a replacement kit.', 4, '2026-10-26 10:30:00'),
(5, 'Account created and welcome email sent.', 3, '2026-10-21 15:20:00');

DROP USER IF EXISTS 'comp2245'@'localhost';
CREATE USER 'comp2245'@'localhost' IDENTIFIED BY 'comp2245-demo';
GRANT SELECT, INSERT, UPDATE, DELETE ON helpdesk.* TO 'comp2245'@'localhost';
FLUSH PRIVILEGES;
