<?php
session_start();

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
$isLoggedIn = isset($_SESSION['logged_in']) ? (bool)$_SESSION['logged_in'] : false;
if (!isset($_SESSION['user_id']) || !$isLoggedIn) {
    header("Location: ../auth/admin-login.php");
    exit();
}

// Database connection
$config = require __DIR__ . '/../../config/database.php';

try {
    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', $config['host'], $config['dbname'], $config['charset']);
    $pdo = new PDO($dsn, $config['username'], $config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("POST request received");
    error_log("POST data: " . print_r($_POST, true));
    error_log("FILES data: " . print_r($_FILES, true));

    try {
        // Validate required fields
        $required_fields = [
            'fullName', 'username', 'email', 'phoneNumber', 
            'cnicNumber', 'address', 'emergencyContactName', 
            'emergencyContactPhone', 'relationshipToUser', 
            'password', 'confirmPassword'
        ];

        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Missing required field: " . $field);
            }
        }

        if ($_POST['password'] !== $_POST['confirmPassword']) {
            throw new Exception("Passwords do not match");
        }

        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }

        // Check if username or email already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$_POST['username'], $_POST['email']]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("Username or email already exists");
        }

        // Handle file uploads
        $upload_dir = '../uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $profile_picture = '';
        if (isset($_FILES['profilePicture']) && $_FILES['profilePicture']['error'] === UPLOAD_ERR_OK) {
            $file_extension = strtolower(pathinfo($_FILES['profilePicture']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png'];

            if (!in_array($file_extension, $allowed_extensions)) {
                throw new Exception("Invalid profile picture format. Only JPG, JPEG, and PNG are allowed.");
            }

            $profile_picture = $upload_dir . uniqid() . '.' . $file_extension;
            if (!move_uploaded_file($_FILES['profilePicture']['tmp_name'], $profile_picture)) {
                throw new Exception("Failed to upload profile picture");
            }
        }

        $cnic_front = '';
        $cnic_back = '';
        if (isset($_FILES['cnicPicture']) && is_array($_FILES['cnicPicture']['name'])) {
            $files = $_FILES['cnicPicture'];
            if (count($files['name']) < 2) {
                throw new Exception("Please upload both front and back of CNIC");
            }

            for ($i = 0; $i < 2; $i++) {
                $file_extension = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
                if (!in_array($file_extension, ['jpg', 'jpeg', 'png'])) {
                    throw new Exception("Invalid CNIC image format. Only JPG, JPEG, and PNG are allowed.");
                }

                $new_filename = $upload_dir . uniqid() . '.' . $file_extension;
                if (!move_uploaded_file($files['tmp_name'][$i], $new_filename)) {
                    throw new Exception("Failed to upload CNIC image");
                }

                if ($i === 0) {
                    $cnic_front = $new_filename;
                } else {
                    $cnic_back = $new_filename;
                }
            }
        }

        // Hash the password
        $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        error_log("Creating new user: " . $_POST['username']);
        error_log("Password hash: " . $hashed_password);

        // Insert the new user
        $stmt = $pdo->prepare("INSERT INTO users (
            username, password, full_name, email, phone_number, cnic_number, 
            address, profile_picture, cnic_front, cnic_back, 
            emergency_contact_name, emergency_contact_phone, 
            emergency_contact_relationship, emergency_contact_address
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $result = $stmt->execute([
            $_POST['username'],
            $hashed_password,
            $_POST['fullName'],
            $_POST['email'],
            $_POST['phoneNumber'],
            $_POST['cnicNumber'],
            $_POST['address'],
            $profile_picture,
            $cnic_front,
            $cnic_back,
            $_POST['emergencyContactName'],
            $_POST['emergencyContactPhone'],
            $_POST['relationshipToUser'],
            $_POST['emergencyContactAddress'] ?? ''
        ]);

        if ($result && $stmt->rowCount() > 0) {
            error_log("User created successfully: " . $_POST['username']);
            $success = "User created successfully!";
        } else {
            error_log("Failed to create user: " . $_POST['username']);
            throw new Exception("Failed to create user. Please try again.");
        }

    } catch (Exception $e) {
        error_log("Error creating user: " . $e->getMessage());
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New User - Courier Services</title>
    <link rel="stylesheet" href="add-user.css">

    <style>
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(to right, #fcd31b, #fef5c3);
            padding: 10px 20px;
            position: relative;
        }

        .brand {
            display: flex;
            align-items: center;
        }

        .brand img {
            height: 40px;
            margin-right: 10px;
        }

        .brand h1 {
            font-size: 1.5rem;
            margin: 0;
            color: #333;
            line-height: 1.2;
        }

        .menu-toggle {
            display: none;
            font-size: 1.5rem;
            cursor: pointer;
            background: none;
            border: none;
            color: #333;
        }

        .nav-links {
            display: flex;
            gap: 30px;
            align-items: center;
        }

        .nav-links a {
            text-decoration: none;
            color: #555;
            font-weight: 500;
        }

        .nav-links a:nth-child(1) {
            color: red;
        }

        .nav-links .logout {
            color: darkred;
            font-weight: bold;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: -250px;
            width: 250px;
            height: 100%;
            background-color: #fff8dc;
            box-shadow: 2px 0 5px rgba(0,0,0,0.5);
            padding-top: 60px;
            display: flex;
            flex-direction: column;
            gap: 20px;
            padding-left: 20px;
            transition: left 0.3s ease;
            z-index: 1001;
        }

        .sidebar.open {
            left: 0;
        }

        .sidebar a {
            text-decoration: none;
            color: #333;
            font-size: 16px;
        }

        .sidebar .logout {
            color: darkred;
            font-weight: bold;
        }

.menu-toggle {
    display: none;
    font-size: 1.5rem;
    cursor: pointer;
    background: none;
    border: none;
    color: #333;
}

@media (max-width: 768px) {
    .nav-links {
        display: none;
    }
    .menu-toggle {
        display: block;
    }
}

        </style>
</head>


<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
    <div class="nav-container">
        <div class="logo">
            <img src="../Photos/Screenshot 2025-05-10 035929.png" alt="Logo" />
            <i class="fas fa-shipping-fast"></i>
            Courier Services
        </div>

        <!-- Toggle button for mobile -->
        <button class="menu-toggle" onclick="toggleSidebar()">
            ☰
        </button>

        <ul class="nav-links">
            <li><a href="../Shipment Form/shipping-form.php">Book Shipment</a></li>
            <li><a href="../Today's Bookings/todays-booking.php">Today's Bookings</a></li>
            <li><a href="../Search/search.php">Cancel Shipment</a></li>
            <li><a href="#" class="active">Users</a></li>
            <li><a href="../Login/login.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a></li>
        </ul>

        <div id="sidebar" class="sidebar">
            <a href="../Shipment Form/shipping-form.php">Book Shipment</a>
            <a href="../home.html">Track</a>
            <a href="../Today's Bookings/todays-booking.php">Today's Bookings</a>
            <a href="../Search/search.php">Cancel Shipment</a>
            <a href="../Login/login.php" class="logout">Logout</a>
        </div>
    </div>
</nav>

    <div class="container">
        <div class="form-container">
            <div class="form-header">
                <h2>Add New User</h2>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form id="addUserForm" class="user-form" method="POST" action="" enctype="multipart/form-data">
                <div class="form-section">
                    <div class="profile-picture-section">
                        <div class="profile-picture-container">
                            <img id="profilePreview" src="https://via.placeholder.com/150" alt="Profile Picture">
                            <div class="upload-overlay">
                                <i class="fas fa-camera"></i>
                            </div>
                            <input type="file" 
                                id="profilePicture" 
                                name="profilePicture" 
                                accept="image/*"
                                required>
                        </div>
                        <p class="upload-text">Upload Profile Picture</p>
                    </div>

                    <div class="form-group">
                        <div class="input-group">
                            <i class="fas fa-user"></i>
                            <input type="text" 
                                id="fullName" 
                                name="fullName" 
                                placeholder="Full Name*"
                                required>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="input-group">
                            <i class="fas fa-at"></i>
                            <input type="text" 
                                id="username" 
                                name="username" 
                                placeholder="Username*"
                                required>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="input-group">
                            <i class="fas fa-envelope"></i>
                            <input type="email" 
                                id="email" 
                                name="email" 
                                placeholder="Email Address*"
                                required>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="input-group">
                            <i class="fas fa-phone"></i>
                            <input type="tel" 
                                id="phoneNumber" 
                                name="phoneNumber" 
                                placeholder="Phone Number*"
                                pattern="[0-9]{10,}"
                                title="Please enter at least 10 digits"
                                required>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="input-group">
                            <i class="fas fa-id-card"></i>
                            <input type="text" 
                                id="cnicNumber" 
                                name="cnicNumber" 
                                placeholder="CNIC Number (e.g., 12345-1234567-1)*"
                                pattern="[0-9]{5}-[0-9]{7}-[0-9]{1}"
                                title="Please enter CNIC in format: 12345-1234567-1"
                                required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="cnicPicture">CNIC Picture (Front & Back)*</label>
                        <div class="input-group file-input cnic-upload">
                            <input type="file" 
                                id="cnicPicture" 
                                name="cnicPicture" 
                                accept="image/*"
                                multiple
                                required>
                            <div class="file-info">No files chosen</div>
                        </div>
                        <div class="cnic-preview">
                            <div id="cnicFrontPreview" class="cnic-preview-box">
                                <span>Front Side</span>
                            </div>
                            <div id="cnicBackPreview" class="cnic-preview-box">
                                <span>Back Side</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="input-group">
                            <i class="fas fa-map-marker-alt"></i>
                            <textarea 
                                id="address" 
                                name="address" 
                                placeholder="Complete Address*"
                                required></textarea>
                        </div>
                    </div>

                    <!-- Emergency Contact Section -->
                    <div class="form-group section-divider">
                        <h3>Emergency Contact</h3>
                    </div>

                    <div class="form-group">
                        <div class="input-group">
                            <i class="fas fa-user-friends"></i>
                            <input type="text" 
                                id="emergencyContactName" 
                                name="emergencyContactName" 
                                placeholder="Emergency Contact Name*"
                                required>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="input-group">
                            <i class="fas fa-phone-alt"></i>
                            <input type="tel" 
                                id="emergencyContactPhone" 
                                name="emergencyContactPhone" 
                                placeholder="Emergency Contact Phone*"
                                pattern="[0-9]{10,}"
                                title="Please enter at least 10 digits"
                                required>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="input-group">
                            <i class="fas fa-user-circle"></i>
                            <input type="text" 
                                id="relationshipToUser" 
                                name="relationshipToUser" 
                                placeholder="Relationship to User*"
                                required>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="input-group">
                            <i class="fas fa-map-marker"></i>
                            <textarea 
                                id="emergencyContactAddress" 
                                name="emergencyContactAddress" 
                                placeholder="Emergency Contact Address"
                                ></textarea>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="input-group">
                            <!-- <i class="fas fa-lock"></i> -->
                            <input type="password" 
                                id="password" 
                                name="password" 
                                placeholder="Password*"
                                required>
                            <i class="fas fa-eye toggle-password"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="input-group">
                            <!-- <i class="fas fa-lock"></i> -->
                            <input type="password" 
                                id="confirmPassword" 
                                name="confirmPassword" 
                                placeholder="Confirm Password*"
                                required>
                            <i class="fas fa-eye toggle-password"></i>
                        </div>
                    </div>

                    <button type="submit" class="submit-button">
                        <span class="button-text">Create User</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
     function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('open');
    }
        document.getElementById('addUserForm').addEventListener('submit', function(e) {
            // Password validation
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            if (password !== confirmPassword) {
                e.preventDefault();
                alert("Passwords don't match!");
                return false;
            }

            const button = document.querySelector('.submit-button');
            const buttonText = document.querySelector('.button-text');

            button.classList.add('loading');
            buttonText.textContent = 'Creating...';
        });

        // Handle profile picture preview
        document.getElementById('profilePicture').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('profilePreview').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });

        // Handle password visibility toggle
        document.querySelectorAll('.toggle-password').forEach(icon => {
            icon.addEventListener('click', function() {
                const input = this.previousElementSibling;
                if (input.type === 'password') {
                    input.type = 'text';
                    this.classList.remove('fa-eye');
                    this.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    this.classList.remove('fa-eye-slash');
                    this.classList.add('fa-eye');
                }
            });
        });

        // Handle CNIC picture preview
        document.getElementById('cnicPicture').addEventListener('change', function(e) {
            const files = e.target.files;
            const fileInfo = e.target.parentElement.querySelector('.file-info');
            
            if (files.length > 0) {
                fileInfo.textContent = `${files.length} file(s) selected`;
                
                // Preview first image as front
                if (files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const frontPreview = document.getElementById('cnicFrontPreview');
                        frontPreview.style.backgroundImage = `url(${e.target.result})`;
                        frontPreview.classList.add('has-image');
                    }
                    reader.readAsDataURL(files[0]);
                }
                
                // Preview second image as back
                if (files[1]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const backPreview = document.getElementById('cnicBackPreview');
                        backPreview.style.backgroundImage = `url(${e.target.result})`;
                        backPreview.classList.add('has-image');
                    }
                    reader.readAsDataURL(files[1]);
                }
            } else {
                fileInfo.textContent = 'No files chosen';
                document.getElementById('cnicFrontPreview').style.backgroundImage = '';
                document.getElementById('cnicBackPreview').style.backgroundImage = '';
                document.getElementById('cnicFrontPreview').classList.remove('has-image');
                document.getElementById('cnicBackPreview').classList.remove('has-image');
            }
        });
    </script>

</body>

</html> 
