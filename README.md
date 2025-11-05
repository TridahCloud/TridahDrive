# TridahDrive

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-blue.svg)](https://php.net)
[![Docker](https://img.shields.io/badge/Docker-Ready-2496ED.svg)](https://www.docker.com/)

A comprehensive platform for managing personal and shared organizational resources, with an integrated suite of business tools.

🌐 **Website**: https://drive.tridah.cloud  
📦 **GitHub**: https://github.com/TridahCloud/TridahDrive

## 🚧 Work in Progress

**TridahDrive is actively under development** with new features and tools being added regularly. This platform will serve as the foundation for building integrated business applications.

## ✨ Current Features

### 📁 Drive Management
- **Personal Drives**: Private organizational spaces for individual use
- **Shared Drives**: Collaborative workspaces for teams and organizations
- Drive-based access control and member management
- Customizable drive colors and icons
- Custom roles and permissions system

### 💰 Invoicing System
- Create customizable invoices with your branding
- Client management and reusable line items
- Invoice profiles for different business entities
- Project tracking on invoices
- Status management (Draft, Sent, Paid, Overdue, Cancelled)
- Print-optimized layouts
- Light/Dark mode support

### 📊 Bookkeeping System
- **Accounts Management**: Chart of accounts with account types
- **Transaction Management**: Income and expense tracking
- **Categories**: Organize transactions by category
- **Recurring Transactions**: Automate recurring income and expenses
- **Transaction Attachments**: Store receipts and documents
- **Tax Reports**: Generate tax reports for reporting periods
- **Invoice Sync**: Automatic synchronization with invoice system
- **Payroll Sync**: Integration with payroll entries

### 📋 Project Management
- **Project Board**: Kanban-style project boards
- **Task Management**: Create, assign, and track tasks
- **Task Labels**: Organize tasks with custom labels
- **Task Comments**: Collaborate with comments on tasks
- **Task Attachments**: Attach files to tasks
- **Status Tracking**: Manage task statuses (To Do, In Progress, Done, etc.)
- **People Assignment**: Assign team members to projects
- **Public Project Views**: Share projects with public links

### 👔 Staff & Volunteer Management
- **People Profiles**: Comprehensive profiles for staff and volunteers
- **Manager Profiles**: Configure manager settings and permissions
- **Scheduling**: Create and manage schedules for team members
- **Schedule Builder**: Bulk schedule creation tool
- **Time Logging**: Track hours worked with clock in/out functionality
- **Time Log Approval**: Approve or reject time logs
- **Time Reports**: Generate printable time reports
- **Payroll Management**: Generate payroll entries from time logs
- **Self-Service Portal**: Team members can view schedules and log time
- **Payroll Integration**: Sync payroll to bookkeeping system

### 👥 User Management
- Secure authentication with email verification
- Profile management
- Notification system for drive invitations and updates
- Role-based access control

## 🔮 Planned Features

### 🛠️ Additional Tools
- Enhanced document management
- Advanced calendar and scheduling features
- Client relationship management (CRM)
- Reporting and analytics dashboards
- And more as the platform evolves...

## 🚀 Getting Started

### Option 1: Using Docker (Recommended)

The easiest way to get started is using [Laravel Sail](https://laravel.com/docs/sail), which provides a Docker-based development environment.

#### Prerequisites
- Docker Desktop installed ([Download Docker](https://www.docker.com/products/docker-desktop))
- Git

#### Installation Steps

1. Clone the repository:
```bash
git clone https://github.com/TridahCloud/TridahDrive.git
cd TridahDrive
```

2. Copy environment file:
```bash
cp .env.example .env
```

3. Start Docker containers:
```bash
./vendor/bin/sail up -d
```

   > **Note**: On Windows, you may need to use `vendor\bin\sail up -d` or alias sail first.

4. Install PHP dependencies (if not already installed):
```bash
./vendor/bin/sail composer install
```

5. Install JavaScript dependencies:
```bash
./vendor/bin/sail npm install
```

6. Generate application key:
```bash
./vendor/bin/sail artisan key:generate
```

7. Run migrations:
```bash
./vendor/bin/sail artisan migrate
```

8. (Optional) Seed the database:
```bash
./vendor/bin/sail artisan db:seed
```

9. Access the application:
   - Visit `http://localhost` in your browser (default port 80)
   - Or configure `APP_PORT` in `.env` for a custom port

#### Common Sail Commands

```bash
# Start containers
./vendor/bin/sail up -d

# Stop containers
./vendor/bin/sail stop

# View logs
./vendor/bin/sail logs

# Run Artisan commands
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan tinker

# Run Composer commands
./vendor/bin/sail composer require package-name

# Run NPM commands
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev

# Access MySQL CLI
./vendor/bin/sail mysql

# Access application shell
./vendor/bin/sail shell
```

### Option 2: Local Development Setup

If you prefer to run the application locally without Docker:

#### Requirements
- PHP 8.2 or higher
- Composer
- MySQL 8.0+ or SQLite
- Node.js 18+ and NPM

#### Installation Steps

1. Clone the repository:
```bash
git clone https://github.com/TridahCloud/TridahDrive.git
cd TridahDrive
```

2. Install PHP dependencies:
```bash
composer install
```

3. Install JavaScript dependencies:
```bash
npm install
```

4. Copy environment file:
```bash
cp .env.example .env
```

5. Generate application key:
```bash
php artisan key:generate
```

6. Configure your database in `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tridah_drive
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

   Or use SQLite:
```env
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database/database.sqlite
```

7. Run migrations:
```bash
php artisan migrate
```

8. (Optional) Seed the database:
```bash
php artisan db:seed
```

9. Start the development server:
```bash
php artisan serve
```

10. Visit `http://localhost:8000` in your browser.

## 🐳 Docker Configuration

TridahDrive uses Laravel Sail for Docker containerization. The configuration is defined in `compose.yaml`:

- **PHP 8.4** container with Laravel application
- **MySQL 8.0** database container
- Port **80** for the web application (configurable via `APP_PORT`)
- Port **5173** for Vite HMR (configurable via `VITE_PORT`)
- Port **3306** for MySQL (configurable via `FORWARD_DB_PORT`)

### Environment Variables for Docker

The `.env.example` file is pre-configured for Docker/Sail usage with:
- `DB_HOST=mysql` (Docker service name)
- `DB_PORT=3306`
- `DB_DATABASE=laravel` (default, can be changed)
- `DB_USERNAME=sail` (default Sail user)
- `DB_PASSWORD=password` (default Sail password)

## 🛠️ Tech Stack

- **Backend**: Laravel 12.x
- **PHP**: 8.2+
- **Frontend**: Bootstrap 5.3, Blade Templates
- **Icons**: Font Awesome 6.5
- **Database**: MySQL 8.0+ / SQLite
- **Styling**: Custom CSS with theme support (Light/Dark mode)
- **Containerization**: Docker with Laravel Sail

## 🤝 Contributing

We welcome contributors of all skill levels! TridahDrive is a community-driven project and there are many ways to get involved:

### How to Contribute

1. **Code Contributions**
   - Small fixes and bug reports
   - Feature implementations
   - Tool development
   - Code refactoring

2. **Documentation**
   - Writing guides and tutorials
   - API documentation
   - User guides
   - Code comments

3. **Project Management**
   - Issue triaging
   - Feature planning
   - Roadmap development

4. **Git Maintenance**
   - Branch management
   - Release management
   - Code review
   - Merge conflict resolution

### Getting Involved

- Fork the repository
- Create a feature branch
- Make your changes
- Submit a pull request

We're looking for:
- Developers (all levels welcome)
- Documentation writers
- Project managers
- Git maintainers
- Anyone passionate about building useful tools!

## 📄 License

This project is open-sourced software licensed under the [MIT License](LICENSE).

## 🌟 Thank You

Thank you for considering contributing to TridahDrive! Every contribution, no matter how small, helps make this platform better for everyone.

---

**Note**: This project is in active development. Features and APIs may change frequently. We recommend checking the documentation regularly for updates.
