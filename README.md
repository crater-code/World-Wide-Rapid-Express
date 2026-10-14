# World Wide Rapid Express — International Logistics & Consignment Tracking Portal

[![PHP](https://img.shields.io/badge/Backend-PHP_7.4%2B_%2F_8.x-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/Database-MySQL_5.7%2B-4479A1?logo=mysql&logoColor=white)](https://www.mysql.com/)
[![JavaScript](https://img.shields.io/badge/Frontend-Vanilla_JS_ES6%2B-F7DF1E?logo=javascript&logoColor=black)](https://developer.mozilla.org/en-US/docs/Web/JavaScript)
[![CSS3](https://img.shields.io/badge/Styling-CSS3_Responsive-1572B6?logo=css3&logoColor=white)](https://developer.mozilla.org/en-US/docs/Web/CSS)
[![Carrier Integration](https://img.shields.io/badge/Carriers-DHL%20%7C%20FedEx%20%7C%20UPS%20%7C%20SkyNET%20%7C%20DPD-red)](#-multi-carrier-telemetry--tracking)
[![License](https://img.shields.io/badge/License-MIT-green)](LICENSE)

Official web and operations portal for **World Wide Rapid Express**, an international freight forwarding, express courier, and consignment tracking service. The platform connects customer-facing shipment telemetry with internal booking management, volumetric weight pricing, daily dispatch manifests, and carrier network routing (DHL, FedEx, UPS, SkyNET, DPD).

---

## 📑 Table of Contents

- [Overview & Architecture](#-overview--architecture)
- [Key Features](#-key-features)
- [Multi-Carrier Telemetry & Tracking](#-multi-carrier-telemetry--tracking)
- [Technology Stack](#-technology-stack)
- [Project Directory Structure](#-project-directory-structure)
- [Database Schema & Structure](#-database-schema--structure)
- [Local Development Setup](#-local-development-setup)
- [Operations & Staff Workflow](#-operations--staff-workflow)
- [API Reference](#-api-reference)
- [Contributors](#-contributors)
- [Organization & Support](#-organization--support)

---

## 🌟 Overview & Architecture

World Wide Rapid Express operates a dual-tier web architecture:

1. **Public Customer Portal**:
   - **Real-Time Consignment Tracker**: Modal-based live lookup for tracking numbers with status badges (`Booked`, `Processing`, `In-Transit`, `Delivered`).
   - **Carrier Dispatch Gateway**: Automatically maps internal shipment numbers to partner carrier tracking portals (DHL Express, FedEx, UPS, SkyNET, DPD).
   - **Services & Company Showcases**: Dedicated modules for enterprise logistics solutions, air/sea freight, import/export, and company history.
   - **Instant WhatsApp Communication**: Floating quick-connect channel for direct quotes and live customer service.

2. **Internal Operations & Administration Portal**:
   - **Booking & Waybill Generator**: Multi-tier booking form with physical vs. volumetric weight calculation `(L × B × H / 5000)`, tax (GST), packaging charges, and insurance.
   - **Today's Bookings Manifest**: Real-time tabular ledger of daily shipments with payment status (Cash vs. Online, At Booking vs. 2Pay).
   - **Shipment Management & Cancellation**: Search, update carrier tracking numbers, append transit timestamps, or mark cancellations.
   - **User & Staff Provisioning**: Role-based access control (Admin vs. Operator) requiring verified CNIC and emergency contact details.

---

## 🚀 Key Features

- **Consignment Tracking Engine**: Async JSON API (`/api/track-shipment.php`) providing sub-second status retrieval with historical timestamp updates.
- **Volumetric Weight Calculation Engine**: Automatic volumetric vs. actual gross weight comparison to determine billable weight in compliance with IATA air cargo standards.
- **Dynamic Pricing & Tax Engine**: Automated line-item accounting:
  - Base Freight Charges
  - Packaging Fees
  - Insurance Coverage Percentage
  - General Sales Tax (GST)
  - Promotional Discounts with Net Payable calculation
- **International Country Code Directory**: Preloaded country dial codes and destination routing matrices (`country-codes.php`).
- **Secure Staff Authentication**: Session-based login with bcrypt password hashing (`password_verify`), preventing brute force and privilege escalation.
- **Responsive Mobile Navigation**: Slide-out mobile drawer menu, touch-friendly tracking inputs, and fluid viewport adjustments.

---

## 🌐 Multi-Carrier Telemetry & Tracking

When a shipment is processed and assigned an external master airway bill (MAWB), the tracking system automatically generates direct links to international carrier portals:

| Carrier | Tracking Route Pattern |
|---|---|
| **DHL Express** | `https://www.dhl.com/pk-en/home/tracking.html?tracking-id={original_id}` |
| **SkyNET Worldwide** | `https://www.skynet.net/tracking_public?skybill={original_id}` |
| **UPS** | `https://www.ups.com/track?loc=en_US&tracknum={original_id}&requester=ST/` |
| **FedEx** | `https://www.fedex.com/fedextrack/?trknbr={original_id}` |
| **DPD** | `https://www.dpd.com/tracking/?search={original_id}` |

---

## 🛠️ Technology Stack

| Layer | Technologies | Details |
|---|---|---|
| **Frontend** | HTML5, CSS3, JavaScript (ES6+ Fetch API) | Vanilla architecture without heavy frameworks for fast load times |
| **Icons & Typography** | Font Awesome 6.0 | Vector iconography for transport modes and navigation |
| **Backend** | PHP 7.4+ / PHP 8.x | Native PDO (PHP Data Objects) & MySQLi prepared statements |
| **Database** | MySQL 5.7+ / MariaDB (InnoDB) | Relational database with foreign key cascades and indexed tracking |
| **Security** | PDO parameter binding, bcrypt hashing, `htmlspecialchars` sanitization | Protection against SQL Injection and XSS |

---

## 📁 Project Directory Structure

```text
World-Wide-Rapid-Express/
├── admin/                              # Administration & staff management
│   ├── auth/                           # Admin authentication
│   │   ├── admin-login.css             # Login page styling
│   │   ├── admin-login.html            # Staff login interface
│   │   └── admin-login.php             # Session validation & credential checking
│   └── user-management/                # Staff onboarding
│       ├── create-user.css             # User creation form styles
│       ├── create-user.html            # Static user form template
│       └── create-user.php             # Account creation with CNIC verification
├── api/                                # Backend REST endpoints
│   └── track-shipment.php              # Real-time shipment tracking API
├── assets/                             # Static frontend assets
│   ├── css/
│   │   ├── global.css                  # Shared color tokens and typography
│   │   └── tracking.css                # Tracking widget, modal, and badge styles
│   ├── images/                         # Logos, logistics banners, and service icons
│   └── js/
│       └── tracking.js                 # Async tracking form handler & modal renderer
├── config/                             # Server environment configuration
│   └── database.php.example            # Sample PDO connection parameters
├── pages/                              # Public content pages
│   ├── about/
│   │   ├── company-overview.css        # About page styling
│   │   └── company-overview.html       # Company history, mission, and fleet
│   ├── contact/
│   │   └── support.css                 # Contact and help desk styling
│   └── services/
│       ├── solutions.css               # Services showcase styles
│       └── solutions.html              # Air, sea, and land cargo service catalog
├── shipments/                          # Core shipment booking & management
│   ├── cancel-shipment.css             # Shipment cancellation styling
│   ├── cancel-shipment.js              # Tracking search & update script
│   ├── cancel-shipment.php             # Search, edit carrier, and cancellation handler
│   ├── country-codes.php               # International dialing and country codes
│   ├── create-shipment.css             # Booking form layout & calculations
│   ├── create-shipment.js              # Real-time volumetric pricing calculation
│   ├── create-shipment.php             # Shipment entry engine & invoice generator
│   ├── get-today-bookings.php          # Daily booking data endpoint
│   ├── today-bookings.css              # Manifest table styling
│   └── today-bookings.php              # Daily bookings summary dashboard
├── uploads/                            # Uploaded CNIC scans & verification files
├── .env.example                        # Environment variables template
├── database.sql                        # Complete MySQL schema & seed data
├── index.html                          # Homepage & live tracking landing page
├── LICENSE                             # MIT License terms
└── README.md                           # Project documentation
```

---

## 💾 Database Schema & Structure

The system uses MySQL with three primary relational tables defined in [`database.sql`](database.sql):

### 1. `users` Table
Stores administrative and staff credentials:
- `user_id`: Primary Key (AUTO_INCREMENT).
- `username`: Unique login handle.
- `password`: Hashed password using bcrypt.
- `full_name`, `email`, `phone_number`, `cnic_number`: Identity records.
- `cnic_front`, `cnic_back`, `profile_picture`: Verification file paths.
- `emergency_contact_*`: Name, phone, relationship, and address.
- `is_admin`: Boolean flag (0 = Operator, 1 = Administrator).

### 2. `shipments` Table
Core ledger for all booked consignments (auto-increments from `10000000001`):
- **Routing & Package**: `consignment_type`, `shipment_type`, `product_type`, `destination`.
- **Dimensions & Weight**: `volumetric_weight`, `length`, `breadth`, `height`, `weight`, `pieces`.
- **Parties**: Shipper and Consignee names, cell numbers, CNIC, and street addresses.
- **Financials**: `charges`, `packaging_charges`, `insurance_charges`, `gst`, `discount_amount`, `total_charges`.
- **Payment & Terms**: `payment_method` (`Cash`, `Online`), `charges_mode` (`At Booking`, `2Pay`).

### 3. `shipment_tracking` Table
Chronological status logs for consignments:
- `id`: Primary Key.
- `shipment_id`: Foreign Key referencing `shipments(shipment_id)`.
- `original_id`: Partner carrier tracking number (e.g. DHL/FedEx Waybill).
- `shipment_company`: ENUM (`DHL`, `DPD`, `SkyNET`, `FedEx`, `UPS`).
- `status`: Multi-line timestamped checkpoint ledger.

---

## 💻 Local Development Setup

### Option 1: Quick Static Preview (Frontend Only)
You can serve the customer-facing frontend immediately using Node:

```bash
# Clone the repository
git clone https://github.com/crater-code/World-Wide-Rapid-Express.git
cd World-Wide-Rapid-Express

# Start local static server
npx serve . -l 3005
```
Open **`http://localhost:3005`** in your browser.

---

### Option 2: Full Stack Local Setup (PHP + MySQL)

1. **Install Prerequisites**:
   - Web server with PHP 7.4+ or 8.x (Apache, Nginx, or XAMPP / Laragon).
   - MySQL 5.7+ or MariaDB.

2. **Database Setup**:
   ```bash
   mysql -u root -p -e "CREATE DATABASE courier_services_portal;"
   mysql -u root -p courier_services_portal < database.sql
   ```

3. **Configure Database Connection**:
   Copy [`config/database.php.example`](config/database.php.example) to `config/database.php`:
   ```bash
   cp config/database.php.example config/database.php
   ```
   Edit `config/database.php` with your local database credentials:
   ```php
   return [
       'host' => 'localhost',
       'username' => 'root',
       'password' => 'your_password',
       'dbname' => 'courier_services_portal',
       'charset' => 'utf8mb4',
   ];
   ```

4. **Run via PHP Built-in Server**:
   ```bash
   php -S localhost:8000
   ```
   Navigate to `http://localhost:8000` in your browser.

---

## 🔐 Operations & Staff Workflow

### Default Administrative Credentials
- **Portal URL**: `/admin/auth/admin-login.html` (or `admin-login.php`)
- **Username**: `Rehman-Ali-Khan`
- **Default Password**: `Admin@123`

> [!WARNING]
> Change the default admin password immediately after first login in production!

### Booking a Shipment
1. Navigate to `/shipments/create-shipment.php`.
2. Fill in the Shipper and Consignee details with country code prefixes.
3. Input parcel length, breadth, height, and weight. The calculator computes:
   $$\text{Volumetric Weight} = \frac{\text{Length (cm)} \times \text{Breadth (cm)} \times \text{Height (cm)}}{5000}$$
4. Review calculated freight charges, GST, and net total.
5. Submit to generate the unique 11-digit consignment number (`10000000001+`).

### Updating Tracking & Dispatching
1. Navigate to `/shipments/cancel-shipment.php`.
2. Enter the 11-digit consignment number.
3. Select partner carrier (`DHL`, `FedEx`, `UPS`, `SkyNET`, `DPD`) and input the carrier tracking number.
4. Add status notes (e.g. `Departed sort facility`, `Out for Delivery`).

---

## 📡 API Reference

### Track Shipment
- **Endpoint**: `POST /api/track-shipment.php`
- **Content-Type**: `application/x-www-form-urlencoded`
- **Parameters**:
  - `tracking_number` *(string, required)*: The 11-digit internal consignment number.

#### Sample Response:
```json
{
  "success": true,
  "status": "tracking",
  "tracking_info": {
    "shipment_id": "10000000001",
    "original_id": "9400100000000000000000",
    "shipment_company": "DHL",
    "shipper_name": "John Doe",
    "destination": "United Kingdom",
    "pieces": 2
  },
  "status_updates": [
    "2024-05-10 10:30:00: Shipment picked up",
    "2024-05-10 14:15:00: In transit to sorting hub"
  ],
  "tracking_url": "https://www.dhl.com/pk-en/home/tracking.html?tracking-id=9400100000000000000000&submit=1"
}
```

---

## 👥 Contributors

- **Sole Developer**: [Muhammad Ali](https://www.linkedin.com/in/muhammad-ali-23828a2b0/)

---

## 📞 Organization & Support

- **Organization**: World Wide Rapid Express
- **Website**: [World Wide Rapid Express](https://www.worldwiderapidexpress.com/)
- **Phone**: [+92 313 0558314](tel:+923130558314)
- **WhatsApp**: [Direct Chat](https://wa.me/923130558314)
- **Support**: `info@worldwiderapidexpress.com`

---

## 📄 License

This project is licensed under the MIT License — see the [LICENSE](LICENSE) file for details.
