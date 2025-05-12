# Techie Admin Panel

Admin panel for managing projects and categories in the Techie application.

## Features

- **Dashboard**: Overview of projects and categories with statistics
- **Projects Management**:
  - List all projects
  - Create new projects
  - Edit existing projects
  - Delete projects
  - Change project status (Approve/Reject)
  - View project details

- **Categories Management**:
  - List all categories
  - Create new categories
  - Edit existing categories
  - Delete categories (with constraints check)
  - View category details

## Structure

The application follows the MVC (Model-View-Controller) pattern:

- **Models**: Handle database interactions using PDO for security
  - `Project.php`: Project-related database operations
  - `Category.php`: Category-related database operations

- **Views**: UI templates using Argon Dashboard design
  - Admin dashboard
  - Project management pages
  - Category management pages
  - Error pages

- **Controllers**: Handling business logic and requests
  - `ProjectController.php`: Project-related actions
  - `CategoryController.php`: Category-related actions

- **Config**: Configuration files
  - `Database.php`: Database connection using PDO

## Installation

1. Place the folder in your web server directory
2. Ensure the database is properly configured in `config/Database.php`
3. Access the admin panel through `index.php`

## Requirements

- PHP 7.4+
- MySQL/MariaDB
- PDO PHP extension

## Database Structure

The application expects the following database tables:

- `projet`: For storing project information
- `categorie`: For storing category information
- `utilisateur`: For storing user information

## Usage

Access the admin panel by navigating to the `/techieadmin/` directory in your browser.

## Credits

- Bootstrap framework
- Argon Dashboard template
- PHP PDO for database connectivity
