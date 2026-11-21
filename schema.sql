-- COMP2245 Project 2: Fix-It Desk 

-- Drop and recreate database 
DROP DATABASE IF EXISTS helpdesk;
CREATE DATABASE helpdesk CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE helpdesk;

-- Tables

CREATE TABLE users (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    firstname   VARCHAR(50) NOT NULL,
    lastname    VARCHAR(50) NOT NULL,
    email       VARCHAR(255) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    role        ENUM('admin', 'member') NOT NULL DEFAULT 'member',
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB; 
-- engine tells sql which storage engine to use

CREATE TABLE tickets (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title        VARCHAR(150) NOT NULL,
    description  TEXT         NOT NULL,
    priority     ENUM('Low', 'Medium', 'High') NOT NULL DEFAULT 'Medium',
    status       ENUM('Open', 'In progress', 'Closed') NOT NULL DEFAULT 'Open',
    created_by   INT UNSIGNED NOT NULL,
    assigned_to  INT UNSIGNED NULL,
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
                              ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_tickets_created_by
        FOREIGN KEY (created_by) REFERENCES users(id)
        ON DELETE RESTRICT,

    CONSTRAINT fk_tickets_assigned_to
        FOREIGN KEY (assigned_to) REFERENCES users(id)
        ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE notes (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_id   INT UNSIGNED NOT NULL,
    body        TEXT         NOT NULL,
    created_by  INT UNSIGNED NOT NULL,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_notes_ticket
        FOREIGN KEY (ticket_id) REFERENCES tickets(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_notes_created_by
        FOREIGN KEY (created_by) REFERENCES users(id)
        ON DELETE RESTRICT
) ENGINE=InnoDB;

-- starting data 

INSERT INTO users (firstname, lastname, email, password, role) VALUES
('Admin',   'User',      'admin@fixit.test',        '$2y$12$go8JERLDwuJyclRzBYLqju4Elax9UOI91.1X7abRQAbVIMY/7w/Uq', 'admin'),
('Emmore', 'Baltimore', 'member1@fixit.test',    '$2y$12$go8JERLDwuJyclRzBYLqju4Elax9UOI91.1X7abRQAbVIMY/7w/Uq', 'member'),
('D''Shonell', 'Hughes', 'member2@fixit.test',    '$2y$12$go8JERLDwuJyclRzBYLqju4Elax9UOI91.1X7abRQAbVIMY/7w/Uq', 'member'),
('Ethan', 'Watson', 'member3@fixit.test',    '$2y$12$go8JERLDwuJyclRzBYLqju4Elax9UOI91.1X7abRQAbVIMY/7w/Uq', 'member');

-- tickets, using reference from the project doc
-- IDS: admin=1, member1=2, member2=3, member3=4

INSERT INTO tickets
    (title, description, priority, status, created_by, assigned_to, created_at) VALUES
    ('Printer in Room 4 jams on every page',
    'The printer on the 4th floor jams on every single page, no matter which tray. Paper seems to be catching just past the first roller.',
    'Medium', 'Open',        2, 3, '2026-10-26 09:12:00'),
    ('Cannot connect to staff Wi-Fi',
    'My laptop sees the StaffNet network but says "Cannot connect". It worked last week.',
    'High',   'In progress', 4, 2, '2026-10-27 14:30:00'),
    ('Projector remote missing in Lab B',
    'The projector in Lab B has no remote. We have been switching it on manually via the panel.',
    'Low',    'Open',        3, NULL, '2026-10-28 08:05:00'),
    ('Email bounces from the finance office',
    'Messages sent to the finance shared inbox bounce back with a "mailbox full" error.',
    'High',   'Open',        1, 1, '2026-10-29 11:47:00'),
    ('New starter needs an account',
    'New starter joining Monday needs an email account, AD account and access to the shared drive.',
    'Medium', 'Closed',      1, 2, '2026-10-20 10:00:00');

INSERT INTO notes (ticket_id, body, created_by, created_at) VALUES
    (2, 'Forgot the network and joined again. Same error. Trying with a cable next.',
        2, '2026-10-27 15:02:00'),
    (2, 'The cable works, so it''s the Wi-Fi profile. Will reset the certificate tomorrow.',
        2, '2026-10-28 09:40:00'),
    (2, 'Reset the Wi-Fi certificate. Ethan says it connects now.',
        1, '2026-10-31 21:52:00'),
    (1, 'Replaced the pickup roller. Testing with a 20-page job.',
        3, '2026-10-26 11:15:00');

UPDATE tickets SET updated_at = '2026-10-31 21:52:00' WHERE id = 2;
UPDATE tickets SET updated_at = '2026-10-26 11:15:00' WHERE id = 1;
UPDATE tickets SET updated_at = created_at WHERE id IN (3, 4, 5);


-- application user 
DROP USER IF EXISTS 'comp2245'@'localhost';
CREATE USER 'comp2245'@'localhost' IDENTIFIED BY 'comp2245-demo';
GRANT SELECT, INSERT, UPDATE, DELETE ON helpdesk.* TO 'comp2245'@'localhost';
FLUSH PRIVILEGES;