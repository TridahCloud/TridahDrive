# TridahDrive

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

### 💰 Invoicing System
- Create customizable invoices with your branding
- Client management and reusable line items
- Invoice profiles for different business entities
- Project tracking on invoices
- Status management (Draft, Sent, Paid, Overdue, Cancelled)
- Print-optimized layouts
- Light/Dark mode support

### 👥 User Management
- Secure authentication
- Profile management
- Notification system for drive invitations and updates

## 🔮 Planned Features

### 📊 Book Keeping
Integrated accounting tools that will:
- Sync with your invoice system
- Track income and expenses
- Generate financial reports
- Manage receipts and documents

### 👔 Staff & Volunteer Management
Comprehensive organizational management:
- Team member profiles
- Role and permission management
- Time tracking
- Task assignment
- Performance tracking

### 🛠️ Additional Tools
- Document management
- Calendar and scheduling
- Project management
- Client relationship management (CRM)
- And more as the platform evolves...

## 🚀 Getting Started

### Requirements
- PHP 8.2 or higher
- Composer
- MySQL or SQLite
- Node.js and NPM (for asset compilation)

### Installation

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

Visit `http://localhost:8000` in your browser.

## 🛠️ Tech Stack

- **Backend**: Laravel 11
- **Frontend**: Bootstrap 5.3, Blade Templates
- **Icons**: Font Awesome 6.5
- **Database**: MySQL/SQLite
- **Styling**: Custom CSS with theme support (Light/Dark mode)

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

