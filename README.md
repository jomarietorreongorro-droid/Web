# TaskFlow — PHP Task Management System

A self-contained task manager: user accounts, task CRUD, status/priority
tracking, due dates with overdue highlighting, search, and filtering.

## Requirements
- PHP 8.0 or newer (uses the `match` expression)
- PHP's `pdo_sqlite` extension (bundled with PHP by default)

No separate database server is needed — it uses a SQLite file that's
created automatically on first run.

## Running it

From the project folder:

```bash
php -S localhost:8000
```

Then open http://localhost:8000 in your browser, click **Sign up**, create
an account, and start adding tasks.

The database file is created at `data/taskmanager.sqlite` the first time
the app runs. Delete that file to reset all data.

## Project structure

```
taskmanager/
├── config.php              # DB connection (SQLite by default)
├── schema_sqlite.sql        # auto-loaded schema for SQLite
├── schema_mysql.sql         # reference schema if you switch to MySQL
├── index.php                 # dashboard: list/search/filter tasks
├── add_task.php              # create task
├── edit_task.php              # edit task / change status
├── delete_task.php            # delete task
├── toggle_status.php           # quick complete/undo toggle
├── login.php / register.php / logout.php
├── includes/
│   ├── functions.php        # session, auth guard, CSRF, helpers
│   ├── header.php
│   └── footer.php
└── assets/css/style.css
```

## Switching to MySQL

1. Create the database: `mysql -u root -p < schema_mysql.sql`
2. In `config.php`, comment out the SQLite block and uncomment the MySQL
   block, filling in your host/user/password.

## Security notes
- Passwords are hashed with `password_hash()` (bcrypt).
- All queries use prepared statements (PDO) — no raw SQL concatenation.
- Forms are protected with CSRF tokens.
- All task queries are scoped to `user_id`, so users can only see/edit
  their own tasks.

## Ideas for extending it
- Task categories/tags
- Email reminders for due dates
- File attachments per task
- Shared/team task lists
