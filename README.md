# COMP2245 Project 2: Fix-It Desk

| Name | GitHub username | What they worked on |
| --- | --- | --- |
| Tajhere Browne | tbrowne-03 | schema.sql, .gitignore |
| Ethan Watson | drftyit-bot |login.php, logout.php, index.php|
| Emmore Baltimore | EBaltimoreTech24 | Repository README, merging setup |
| D'Shonell Hughes | DShonellHughes | |

## Running the app

```sh
mysql -u root -p < schema.sql
php -S localhost:8000
```

Open http://localhost:8000/. Run `schema.sql` again at any time to reset the data.

## Seed accounts

All use the password `password123`.

| Email | Name | Role |
| --- | --- | --- |
| admin@fixit.test | Admin User | admin |
| member1@fixit.test | Emmore Baltimore | member |
| member2@fixit.test | D'Shonell Hughes | member |
| member3@fixit.test | Ethan Watson | member |