<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Database connection
$config = require __DIR__ . '/../config/database.php';
$host = $config['host'];
$username = $config['username'];
$password = $config['password'];
$dbname = $config['dbname'];
$charset = $config['charset'];

$record = null;
$tracking_info = null;
$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = new mysqli($host, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    if (isset($_POST["search"])) {
        $searchId = $conn->real_escape_string($_POST["search_number"]);

        $sql = "SELECT * FROM shipments WHERE shipment_id = '$searchId' LIMIT 1";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            $record = $result->fetch_assoc();

            $tracking_sql = "SELECT * FROM shipment_tracking WHERE shipment_id = '$searchId' LIMIT 1";
            $tracking_result = $conn->query($tracking_sql);
            if ($tracking_result && $tracking_result->num_rows > 0) {
                $tracking_info = $tracking_result->fetch_assoc();
            }
        } else {
            $record = "not_found";
        }
    } elseif (isset($_POST["add_tracking"])) {
        $shipment_id = $conn->real_escape_string($_POST["shipment_id"]);
        $original_id = $conn->real_escape_string($_POST["original_id"]);
        $shipment_company = $conn->real_escape_string($_POST["shipment_company"]);
        $status = date('Y-m-d H:i:s') . ": " . $conn->real_escape_string($_POST["status"]);

        $sql = "INSERT INTO shipment_tracking (shipment_id, original_id, shipment_company, status) 
                VALUES ('$shipment_id', '$original_id', '$shipment_company', '$status')";

        if ($conn->query($sql)) {
            $message = "Tracking information added successfully!";
            $tracking_sql = "SELECT * FROM shipment_tracking WHERE shipment_id = '$shipment_id' LIMIT 1";
            $tracking_result = $conn->query($tracking_sql);
            if ($tracking_result && $tracking_result->num_rows > 0) {
                $tracking_info = $tracking_result->fetch_assoc();
            }
        } else {
            $message = "Error adding tracking information: " . $conn->error;
        }
    } elseif (isset($_POST["update_status"])) {
        $shipment_id = $conn->real_escape_string($_POST["shipment_id"]);
        $new_status = $conn->real_escape_string($_POST["status"]);
        
        // Get current status
        $current_sql = "SELECT status FROM shipment_tracking WHERE shipment_id = '$shipment_id' LIMIT 1";
        $current_result = $conn->query($current_sql);
        
        if ($current_result && $current_result->num_rows > 0) {
            $current_row = $current_result->fetch_assoc();
            $updated_status = $current_row['status'] . "\n\n" . date('Y-m-d H:i:s') . ": " . $new_status;
            
            $sql = "UPDATE shipment_tracking SET status = '$updated_status' WHERE shipment_id = '$shipment_id'";
            
            if ($conn->query($sql)) {
                $message = "Status updated successfully!";
                $tracking_sql = "SELECT * FROM shipment_tracking WHERE shipment_id = '$shipment_id' LIMIT 1";
                $tracking_result = $conn->query($tracking_sql);
                if ($tracking_result && $tracking_result->num_rows > 0) {
                    $tracking_info = $tracking_result->fetch_assoc();
                }
            } else {
                $message = "Error updating status: " . $conn->error;
            }
        } else {
            $message = "Error: Could not find current status";
        }
    } elseif (isset($_POST["delete_shipment"])) {
        $shipment_id = $conn->real_escape_string($_POST["shipment_id"]);

        // Delete from both tables
        $conn->query("DELETE FROM shipment_tracking WHERE shipment_id = '$shipment_id'");
        
        $sql = "DELETE FROM shipments WHERE shipment_id = '$shipment_id'";

        if ($conn->query($sql)) {
            $message = "Shipment deleted successfully!";
            $record = null;
            $tracking_info = null;
        } else {
            $message = "Error deleting shipment: " . $conn->error;
        }
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Track Shipment</title>
    <link rel="stylesheet" href="./search.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(to right, #fcd31b, #fef5c3);
            padding: 10px 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
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
        }
        .menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
        }
        .nav-links {
            display: flex;
            gap: 15px;
        }
        .nav-links a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            text-align: center;
            padding: 5px 10px;
        }
        .nav-links a.active {
            font-weight: bold;
        }
        .nav-links a.logout {
            color: #f44336;
        }
        .sidebar {
            display: none;
            position: fixed;
            top: 70px;
            left: 0;
            width: 200px;
            background: white;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            z-index: 999;
            flex-direction: column;
            padding: 10px 0;
        }
        .sidebar a {
            padding: 10px 20px;
            text-decoration: none;
            color: #333;
        }
        .sidebar a:hover {
            background: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 100px auto 20px;
            padding: 20px;
            background: white;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .login-container {
            max-width: 600px;
            margin: 0 auto;
        }
        .login-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .login-form {
            background: white;
            padding: 20px;
            border-radius: 5px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .input-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        .login-button {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .login-button:hover {
            background-color: #45a049;
        }
        .result-container {
            margin-top: 20px;
            background: white;
            padding: 20px;
            border-radius: 5px;
        }
        .result-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .result-table th, .result-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .result-table th {
            background-color: #f2f2f2;
        }
        .buttons {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }
        .delete-button {
            padding: 10px 15px;
            background-color: #f44336;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .print-button {
            padding: 10px 15px;
            background-color: #2196F3;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .success {
            background-color: #dff0d8;
            color: #3c763d;
        }
        .error {
            background-color: #f2dede;
            color: #a94442;
        }
        .status-history {
            white-space: pre-wrap;
            font-family: monospace;
            background: #f8f8f8;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #ddd;
            margin-top: 10px;
        }
        .status-update {
            margin-bottom: 15px;
        }
        .status-update textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            resize: vertical;
            min-height: 100px;
        }

        @media (max-width: 768px) {
            .menu-toggle {
                display: block;
            }
            .nav-links {
                display: none;
            }
            .sidebar {
                display: flex;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="brand">
            <img src="../Photos/Screenshot 2025-05-10 035929.png" alt="Logo" />
            <h1>Courier<br>Services</h1>
        </div>
        <button class="menu-toggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>
        <div class="nav-links">
            <a href="../Shipment Form/shipping-form.php">New<br>Shipment</a>
            <a href="../home.html">Track</a>
            <a href="../Today's Bookings/todays-booking.php">Today's<br>Bookings</a>
            <a href="#" class="active">Cancel<br>Shipment</a>
            <a href="../Login/login.php" class="logout">Logout</a>
        </div>
    </div>

    <div id="sidebar" class="sidebar">
        <a href="../Shipment Form/shipping-form.php">New Shipment</a>
        <a href="../home.html">Track</a>
        <a href="../Today's Bookings/todays-booking.php">Today's Bookings</a>
        <a href="#" class="active">Cancel Shipment</a>
        <a href="../Login/login.php" class="logout">Logout</a>
    </div>

    <div class="container">
        <div class="login-container">
            <div class="login-header">
                <h2>Track Your Shipment</h2>
                <p>Enter the 11-digit shipment ID to view details.</p>
            </div>

            <?php if ($message): ?>
                <div class="message <?php echo strpos($message, 'Error') !== false ? 'error' : 'success'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="login-form">
                <div class="form-group">
                    <div class="input-group">
                        <input type="text" name="search_number" maxlength="11" pattern="\d{11}" required 
                               placeholder="Enter 11-digit shipment ID" value="<?php echo isset($_POST['search_number']) ? htmlspecialchars($_POST['search_number']) : ''; ?>">
                    </div>
                </div>
                <button type="submit" name="search" class="login-button">Search</button>
            </form>

            <?php if ($record && $record !== "not_found"): ?>
                <div class="result-container">
                    <table class="result-table">
                        <thead>
                            <tr>
                                <th>Field</th>
                                <th>Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($record as $key => $value): ?>
                                <tr>
                                    <td><?= htmlspecialchars(ucwords(str_replace("_", " ", $key))) ?></td>
                                    <td><?= htmlspecialchars($value) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <h3>Tracking Information:</h3>
                    <?php if ($tracking_info): ?>
                        <table class="result-table">
                            <thead>
                                <tr>
                                    <th>Original ID</th>
                                    <th>Shipment Company</th>
                                    <th>Status History</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><?= htmlspecialchars($tracking_info['original_id']) ?></td>
                                    <td><?= htmlspecialchars($tracking_info['shipment_company']) ?></td>
                                    <td>
                                        <div class="status-history"><?= htmlspecialchars($tracking_info['status']) ?></div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <div class="status-update">
                            <form method="POST">
                                <input type="hidden" name="shipment_id" value="<?= $record['shipment_id'] ?>">
                                <div class="form-group">
                                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">Add Status Update</label>
                                    <textarea name="status" rows="4" maxlength="500" placeholder="Enter new status update (max 500 characters)" required></textarea>
                                </div>
                                <button type="submit" name="update_status" class="login-button">Add Update</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <form method="POST">
                            <input type="hidden" name="shipment_id" value="<?= $record['shipment_id'] ?>">
                            <div class="form-group" style="margin-bottom: 20px;">
                                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Original ID</label>
                                <input type="text" name="original_id" placeholder="Enter Original ID" required
                                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                            </div>
                            
                            <div class="form-group" style="margin-bottom: 20px;">
                                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Shipment Company</label>
                                <select name="shipment_company" required 
                                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; background: white;">
                                    <option value="">Select Shipment Company</option>
                                    <option value="DHL">DHL</option>
                                    <option value="DPD">DPD</option>
                                    <option value="SkyNET">SkyNET</option>
                                    <option value="FedEx">FedEx</option>
                                    <option value="UPS">UPS</option>
                                </select>
                            </div>

                            <div class="form-group" style="margin-bottom: 20px;">
                                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Initial Status</label>
                                <textarea name="status" rows="4" maxlength="500" placeholder="Enter initial shipment status (max 500 characters)" required
                                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; resize: vertical;"></textarea>
                            </div>
                            <button type="submit" name="add_tracking" class="login-button">Create Tracking</button>
                        </form>
                    <?php endif; ?>

                    <div class="buttons">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="shipment_id" value="<?= $record['shipment_id'] ?>">
                            <button type="submit" name="delete_shipment" class="delete-button" onclick="return confirm('Are you sure you want to delete this shipment?')">Delete Shipment</button>
                        </form>
                        <button onclick="printLabel(<?= htmlspecialchars(json_encode(array_merge($record, ['tracking_info' => $tracking_info])), ENT_QUOTES, 'UTF-8') ?>)" class="print-button">Print Slip</button>
                    </div>
                </div>
            <?php elseif ($record === "not_found"): ?>
                <div class="result-container">
                    <p>No shipment found for this ID.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="./search.js"></script>
</body>
</html>