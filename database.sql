-- Create the database
CREATE DATABASE IF NOT EXISTS courier_services_portal;
USE courier_services_portal;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone_number VARCHAR(20) NOT NULL,
    cnic_number VARCHAR(15) UNIQUE NOT NULL,
    address TEXT NOT NULL,
    profile_picture VARCHAR(255),
    cnic_front VARCHAR(255),
    cnic_back VARCHAR(255),
    emergency_contact_name VARCHAR(100) NOT NULL,
    emergency_contact_phone VARCHAR(20) NOT NULL,
    emergency_contact_relationship VARCHAR(50) NOT NULL,
    emergency_contact_address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_admin BOOLEAN DEFAULT FALSE
);

-- Create shipments table
CREATE TABLE IF NOT EXISTS shipments (
    shipment_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    consignment_type VARCHAR(50) NOT NULL,
    shipment_type VARCHAR(50) NOT NULL,
    product_type VARCHAR(50) NOT NULL,
    destination VARCHAR(100) NOT NULL,
    volumetric_weight BOOLEAN DEFAULT FALSE,
    length DECIMAL(10,2),
    breadth DECIMAL(10,2),
    height DECIMAL(10,2),
    weight DECIMAL(10,2),
    pieces INT NOT NULL DEFAULT 1,
    payment_method ENUM('Cash', 'Online') NOT NULL,
    charges_mode ENUM('At Booking', '2Pay') NOT NULL,
    shipper_name VARCHAR(100) NOT NULL,
    shipper_cell VARCHAR(20) NOT NULL,
    shipper_cnic VARCHAR(15),
    shipper_address TEXT NOT NULL,
    consignee_name VARCHAR(100) NOT NULL,
    consignee_cell VARCHAR(20) NOT NULL,
    consignee_cnic VARCHAR(15),
    consignee_address TEXT NOT NULL,
    packaging_amount DECIMAL(10,2) DEFAULT 0,
    parcel_value DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    special_instructions TEXT,
    discount_percentage DECIMAL(5,2) DEFAULT 0,
    charges DECIMAL(10,2) DEFAULT 0,
    discount_amount DECIMAL(10,2) DEFAULT 0,
    charges_with_discount DECIMAL(10,2) DEFAULT 0,
    gst DECIMAL(10,2) DEFAULT 0,
    gst_percentage DECIMAL(5,2) DEFAULT 0,
    insurance_percentage DECIMAL(5,2) DEFAULT 0,
    insurance_charges DECIMAL(10,2) DEFAULT 0,
    packaging_charges DECIMAL(10,2) DEFAULT 0,
    total_charges DECIMAL(10,2) DEFAULT 0,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (username) REFERENCES users(username)
) AUTO_INCREMENT = 10000000001;

-- Create shipment_tracking table
CREATE TABLE IF NOT EXISTS shipment_tracking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shipment_id BIGINT NOT NULL,
    original_id VARCHAR(50),
    shipment_company ENUM('DHL', 'DPD', 'SkyNET', 'FedEx', 'UPS') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (shipment_id) REFERENCES shipments(shipment_id) ON DELETE CASCADE
);

-- Insert default admin user (password: Admin@123)
INSERT INTO users (
    username, 
    password, 
    full_name, 
    email, 
    phone_number, 
    cnic_number, 
    address, 
    emergency_contact_name,
    emergency_contact_phone,
    emergency_contact_relationship,
    is_admin
) VALUES (
    'Rehman-Ali-Khan',
    '$2b$12$zRZHbYZvx.6ZVdNba7jeVOS2zjBAokHRZPb32Igr3XvOGBBz0psw2',
    'Rehman Ali Khan',
    'admin@worldwiderapidexpress.com',
    '+92 313 0558314',
    '12345-1234567-1',
    'System Address',
    'Emergency Contact',
    '+92 313 0558314',
    'Family',
    TRUE
);

-- Insert new user (password: 123)
INSERT INTO users (
    username, 
    password, 
    full_name, 
    email, 
    phone_number, 
    cnic_number, 
    address, 
    emergency_contact_name,
    emergency_contact_phone,
    emergency_contact_relationship
) VALUES (
    'id',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'New User',
    'id@example.com',
    '1234567890',
    '12345-1234567-2',
    'User Address',
    'Emergency Contact',
    '1234567890',
    'Family'
); 


  