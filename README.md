# Courier Services Portal

A comprehensive web-based courier and logistics management system for tracking shipments, managing users, and handling delivery operations.

## Features

- **Shipment Tracking**: Real-time tracking of shipments with status updates
- **User Management**: Admin panel for managing users and their profiles
- **Shipment Management**: Create, manage, and cancel shipments
- **Multi-carrier Support**: Integration with DHL, DPD, SkyNET, FedEx, and UPS
- **Responsive Design**: Mobile-friendly interface for all devices
- **Admin Dashboard**: Comprehensive admin panel for system management

## Project Structure

```
├── admin/                    # Admin panel
│   ├── auth/                # Admin authentication
│   └── user-management/     # User management interface
├── api/                     # API endpoints
├── assets/                  # Static assets
│   ├── css/                # Stylesheets
│   ├── images/             # Images and logos
│   └── js/                 # JavaScript files
├── config/                 # Configuration files
├── pages/                  # Public pages
│   ├── about/              # About us page
│   ├── contact/            # Contact page
│   └── services/           # Services page
├── shipments/              # Shipment management
├── uploads/                # User uploads directory
├── database.sql            # Database schema
└── index.html              # Home page
```

## Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache, Nginx, etc.)
- Modern web browser

## Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/courier-services-portal.git
   cd courier-services-portal
   ```

2. **Set up the database**
   - Create a MySQL database
   - Import the `database.sql` file:
     ```bash
     mysql -u root -p your_database_name < database.sql
     ```

3. **Configure database connection**
   - Copy `config/database.php.example` to `config/database.php`
   - Update the database credentials:
     ```php
     return [
         'host' => 'localhost',
         'username' => 'your_username',
         'password' => 'your_password',
         'dbname' => 'courier_services_portal',
         'charset' => 'utf8mb4',
     ];
     ```

4. **Set up the web server**
   - Point your web server to the project root directory
   - Ensure the `uploads/` directory has write permissions

5. **Access the application**
   - Open your browser and navigate to `http://localhost` (or your configured domain)
   - Admin login: Use the credentials from the database setup

## Default Admin Credentials

After database setup, use these credentials to log in:
- **Username**: `Rehman-Ali-Khan`
- **Password**: `Admin@123`

⚠️ **Important**: Change these credentials immediately after first login.

## Usage

### For Customers
- Visit the home page to track shipments
- Enter your tracking number to view shipment status
- Contact support via WhatsApp for assistance

### For Admins
- Access the admin panel at `/admin/auth/admin-login.html`
- Manage users and shipments
- View booking statistics and reports

## API Endpoints

- `POST /api/track-shipment.php` - Track shipment by ID

## Security Considerations

- Never commit `config/database.php` with real credentials
- Use environment variables for sensitive data
- Implement HTTPS in production
- Regularly update dependencies
- Use prepared statements to prevent SQL injection
- Validate and sanitize all user inputs

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support, contact us at:
- **Email**: info@worldwiderapidexpress.com
- **Phone**: +92 313 0558314
- **WhatsApp**: [Chat with us](https://wa.me/923130558314)

## Disclaimer

This is a sample project for educational purposes. Ensure all sensitive information is properly secured before deploying to production.
