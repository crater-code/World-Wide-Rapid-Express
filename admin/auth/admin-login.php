<?php
// Start with a clean session
session_start();
session_unset();    // Unset all session variables
session_destroy();  // Destroy the session
session_start();    // Start a new session

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$config = require __DIR__ . '/../../config/database.php';

try {
    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', $config['host'], $config['dbname'], $config['charset']);
    $pdo = new PDO($dsn, $config['username'], $config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Handle login form submission
$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password";
    } else {
        try {
            // Debug: Log the input
            error_log("Login attempt - Username: " . $username);
            
            // Fetch user
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Debug: Log if user was found
            error_log("User found: " . ($user ? "Yes" : "No"));
            
            if ($user) {
                // Debug: Log password verification result
                $passwordMatch = password_verify($password, $user['password']);
                error_log("Password verification: " . ($passwordMatch ? "Success" : "Failed"));
                error_log("Stored password hash: " . $user['password']);
                error_log("Input password: " . $password);
            }

            if ($user && password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['is_admin'] = $user['is_admin'];
                $_SESSION['logged_in'] = true;

                // Debug: Log successful login
                error_log("Login successful for user: " . $username);

                // Redirect based on user type
                if ($user['is_admin']) {
                    header("Location: ../user-management/create-user.php");
                } else {
                    header("Location: ../Shipment Form/shipping-form.php");
                }
                exit();
            } else {
                $error = "Invalid username or password. Please try again.";
                error_log("Login failed for user: " . $username);
            }
        } catch(PDOException $e) {
            $error = "Login failed. Please try again later.";
            error_log("Database error during login: " . $e->getMessage());
        }
    }
}

// No need to check for existing session since we're starting fresh
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Courier Services</title>
    <link rel="stylesheet" href="login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="login-header">
                <h2>Welcome Back!</h2>
                <p>Please login to your account</p>
                <?php if (!empty($error)): ?>
                    <div class="error-message" style="color: red; margin-bottom: 15px; padding: 10px; background-color: #ffebee; border-radius: 4px;">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <form id="loginForm" class="login-form" method="POST" action="">
                <div class="form-group">
                    <div class="input-group">
                        <span class="input-icon">
                            <i class="fas fa-user"></i>
                        </span>
                        <input type="text" id="username" name="username" placeholder="Username" required 
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-group">
                        <span class="input-icon">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" id="password" name="password" placeholder="Password" required>
                        <span class="password-toggle" onclick="togglePassword()">
                            <i class="far fa-eye"></i>
                        </span>
                    </div>
                </div>

                <button type="submit" class="login-button">
                    <span class="button-text">Login</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.querySelector('.password-toggle i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Add form submission debugging
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            console.log('Form submitted');
            console.log('Username:', document.getElementById('username').value);
            console.log('Password:', document.getElementById('password').value);
        });
    </script>
</body>
</html>

