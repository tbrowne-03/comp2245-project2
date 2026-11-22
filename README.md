# COMP2245 Project 2: Fix-It Desk

| Name | GitHub username | What they worked on |
| --- | --- | --- |
| Tajhere Browne | tbrowne-03 | schema.sql, .gitignore, final bug fixes and file editing |
| Ethan Watson | drftyit-bot |login.php, logout.php, index.php|
| Emmore Baltimore | EBaltimoreTech24 | README, merging setup, shared helpers, ticket list, new/edit ticket forms, stylesheet |
| D'Shonell Hughes | DShonellHughes | users.php, ticket-delete |

## Running the app

You need PHP and MySQL (or MariaDB).

```sh
mysql -u root -p < schema.sql
cp config.example.php config.php
php -S localhost:8000
```

Open http://localhost:8000/. Run `schema.sql` again at any time to reset the data.

`config.php` holds the database password for the `comp2245` MySQL user and is not committed to Git.

## Seed accounts

All use the password `password123`.

| Email | Name | Role |
| --- | --- | --- |
| admin@fixit.test | Admin User | admin |
| member1@fixit.test | Emmore Baltimore | member |
| member2@fixit.test | D'Shonell Hughes | member |
| member3@fixit.test | Ethan Watson | member |

## How it is organised

| Path | Purpose |
| --- | --- |
| `includes/bootstrap.php` | Loads the config, starts the session, connects with PDO, login and CSRF helpers |
| `includes/helpers.php` | Date formatting, flash messages, ticket queries and validation |
| `includes/header.php`, `footer.php` | The top (navigation, log out button) and bottom of every page |
| `index.php`, `ticket.php`, `ticket-new.php`, `ticket-edit.php`, `ticket-delete.php`, `users.php` | The pages |
| `api/` | JSON scripts called with `fetch()` (ticket list, add note, change status / assign) |
| `app.js`, `js/list.js`, `js/ticket.js` | JavaScript for the filters and the ticket page |

## Security

- Prepared statements for every query that uses a value
- `htmlspecialchars()` (`e()`) on output, `textContent` in JavaScript
- `password_hash()` and `password_verify()`
- Session id regenerated at login, session destroyed at logout
- CSRF token on every form and every `fetch()` request that changes data
- POST for anything that changes data
