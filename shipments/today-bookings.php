<?php
// Database connection
$config = require __DIR__ . '/../config/database.php';
$conn = new mysqli($config['host'], $config['username'], $config['password'], $config['dbname']);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courier Services - Bookings</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Base Styles */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
            padding-top: 70px;
        }
        
        /* Header Styles */
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
            left: 0;
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
            padding: 5px;
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
            font-size: 0.9rem;
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
            font-size: 0.9rem;
        }
        
        .sidebar a:hover {
            background: #f5f5f5;
        }

        /* Main Content Styles */
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Filters */
        .filter-container {
            display: flex;
            gap: 20px;
            margin-bottom: 25px;
            padding: 15px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            align-items: center;
        }
        
        .filter-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .filter-group label {
            font-weight: 500;
            color: #555;
        }
        
        .filter-group input,
        .filter-group select {
            padding: 8px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            min-width: 180px;
        }
        
        /* Summary Cards */
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .summary-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            text-align: center;
            transition: transform 0.3s ease;
        }
        
        .summary-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .summary-card h3 {
            margin-bottom: 10px;
            color: #666;
            font-size: 16px;
        }
        
        .summary-card .value {
            font-size: 28px;
            font-weight: 700;
            color: #333;
        }
        
        .summary-card .charges {
            color: #28a745;
        }
        
        /* Booking Cards */
        .booking-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
        }
        
        .booking-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .booking-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .booking-card .info-row {
            display: flex;
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .booking-card .info-row:last-child {
            border-bottom: none;
        }
        
        .booking-card .label {
            font-weight: 600;
            min-width: 120px;
            color: #666;
        }
        
        .booking-card .value {
            color: #333;
        }
        
        .review-btn {
            background: linear-gradient(to right, #fcd31b, #f9b115);
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            margin-top: 15px;
            width: 100%;
            transition: all 0.3s ease;
            color: #333;
        }
        
        .review-btn:hover {
            background: linear-gradient(to right, #f9b115, #fcd31b);
            box-shadow: 0 2px 10px rgba(252, 211, 27, 0.4);
        }
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 30px;
            border-radius: 8px;
            width: 80%;
            max-width: 700px;
            box-shadow: 0 5px 30px rgba(0,0,0,0.3);
            position: relative;
        }
        
        .close {
            position: absolute;
            top: 15px;
            right: 25px;
            font-size: 28px;
            font-weight: bold;
            color: #aaa;
            cursor: pointer;
        }
        
        .close:hover {
            color: #333;
        }
        
        /* Responsive Adjustments */
        @media (max-width: 1024px) {
            .summary-cards {
                grid-template-columns: repeat(2, 1fr);
            }
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
            
            .filter-container {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .summary-cards {
                grid-template-columns: 1fr;
            }
            
            .booking-cards {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Header/Navigation -->
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
            <a href="../Search/search.php">Cancel<br>Shipment</a>
            <a href="../Login/login.php" class="logout">Logout</a>
        </div>
    </div>

    <div id="sidebar" class="sidebar">
        <a href="../Shipment Form/shipping-form.php">New Shipment</a>
        <a href="../home.html">Track</a>
        <a href="../Today's Bookings/todays-booking.php">Today's Bookings</a>
        <a href="../Search/search.php">Cancel Shipment</a>
        <a href="../Login/login.php" class="logout">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="dashboard-container">
        <!-- Filters -->
        <div class="filter-container">
            <div class="filter-group">
                <label for="userFilter">User:</label>
                <select id="userFilter" onchange="loadBookings()">
                    <option value="all">All Users</option>
                    <?php
                    $users = $conn->query("SELECT username, full_name FROM users ORDER BY full_name");
                    while ($user = $users->fetch_assoc()) {
                        echo '<option value="'.$user['username'].'">'.htmlspecialchars($user['full_name']).'</option>';
                    }
                    ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="dateRange">Range:</label>
                <select id="dateRange" onchange="loadBookings()">
                    <option value="today">Today</option>
                    <option value="week">Last 7 Days</option>
                    <option value="month">This Month</option>
                    <option value="year">This Year</option>
                    <option value="all">All Dates</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="exactDate">Exact Date:</label>
                <input type="date" id="exactDate" onchange="loadExactDateBookings()">
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="summary-cards" id="summaryContainer">
            <div class="summary-card">
                <h3>Total Bookings</h3>
                <div class="value" id="totalBookings">0</div>
            </div>
            <div class="summary-card">
                <h3>Total Weight (kg)</h3>
                <div class="value" id="totalWeight">0</div>
            </div>
            <div class="summary-card">
                <h3>Total Pieces</h3>
                <div class="value" id="totalPieces">0</div>
            </div>
            <div class="summary-card">
                <h3>Total Charges</h3>
                <div class="value charges" id="totalCharges">Rs0</div>
            </div>
        </div>

        <!-- Booking Cards -->
        <div class="booking-cards" id="bookingsContainer">
            <?php
            $sql = "SELECT s.*, u.full_name 
                    FROM shipments s 
                    JOIN users u ON s.username = u.username 
                    WHERE DATE(s.created_at) = CURDATE()
                    ORDER BY s.created_at DESC";
            
            $result = $conn->query($sql);
            $totalCharges = 0;
            $totalWeight = 0;
            $totalPieces = 0;
            $count = $result->num_rows;
            
            if ($count > 0) {
                // Calculate totals
                while ($row = $result->fetch_assoc()) {
                    $totalCharges += $row['total_charges'];
                    $totalWeight += $row['weight'];
                    $totalPieces += $row['pieces'];
                }
                
                // Reset pointer
                $result->data_seek(0);
                
                // Update summary container
                echo '<script>
                    document.getElementById("totalBookings").textContent = "'.$count.'";
                    document.getElementById("totalWeight").textContent = "'.$totalWeight.'";
                    document.getElementById("totalPieces").textContent = "'.$totalPieces.'";
                    document.getElementById("totalCharges").textContent = "Rs'.$totalCharges.'";
                </script>';
                
                // Display bookings
                while ($row = $result->fetch_assoc()) {
                    echo '
                    <div class="booking-card">
                        <div class="info-row">
                            <span class="label">Tracking ID:</span>
                            <span class="value">'.$row['shipment_id'].'</span>
                        </div>
                        <div class="info-row">
                            <span class="label">Booked By:</span>
                            <span class="value">'.htmlspecialchars($row['full_name']).'</span>
                        </div>
                        <div class="info-row">
                            <span class="label">Destination:</span>
                            <span class="value">'.htmlspecialchars($row['destination']).'</span>
                        </div>
                        <div class="info-row">
                            <span class="label">Date:</span>
                            <span class="value">'.date('M d, Y', strtotime($row['created_at'])).'</span>
                        </div>
                        <div class="info-row">
                            <span class="label">Charges:</span>
                            <span class="value">Rs'.$row['total_charges'].'</span>
                        </div>
                        <button class="review-btn" onclick="showDetails('.$row['shipment_id'].')">
                            Review Details
                        </button>
                    </div>';
                }
            } else {
                echo '<div class="no-bookings" style="grid-column: 1/-1; text-align: center; padding: 40px; color: #666;">
                        <i class="fas fa-box-open" style="font-size: 50px; margin-bottom: 20px;"></i>
                        <h3>No bookings found</h3>
                        <p>There are no bookings matching your criteria</p>
                      </div>';
            }
            $conn->close();
            ?>
        </div>
    </div>

    <!-- Modal -->
    <div id="detailsModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div id="modalContent"></div>
        </div>
    </div>

    <script>
        // Toggle sidebar
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            if (window.getComputedStyle(sidebar).display === 'none') {
                sidebar.style.display = 'flex';
            } else {
                sidebar.style.display = 'none';
            }
        }

        // Close sidebar when clicking outside
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const menuToggle = document.querySelector('.menu-toggle');
            const navbar = document.querySelector('.navbar');
            
            if (!sidebar.contains(event.target) && 
                !menuToggle.contains(event.target) &&
                !navbar.contains(event.target)) {
                sidebar.style.display = 'none';
            }
        });

        // Load bookings with filters
        function loadBookings() {
            const userFilter = document.getElementById('userFilter').value;
            const dateRange = document.getElementById('dateRange').value;
            const container = document.getElementById('bookingsContainer');
            
            container.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 40px;"><i class="fas fa-spinner fa-spin" style="font-size: 30px;"></i><p>Loading bookings...</p></div>';
            
            fetch(`get_bookings.php?user=${userFilter}&range=${dateRange}`)
                .then(response => response.json())
                .then(data => {
                    updateUI(data);
                })
                .catch(error => {
                    container.innerHTML = `<div class="error" style="grid-column: 1/-1; text-align: center; padding: 40px; color: #dc3545;">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            <p>Error loading bookings: ${error}</p>
                                         </div>`;
                });
        }
        
        // Load bookings for exact date
        function loadExactDateBookings() {
            const userFilter = document.getElementById('userFilter').value;
            const exactDate = document.getElementById('exactDate').value;
            if (!exactDate) return;
            
            const container = document.getElementById('bookingsContainer');
            container.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 40px;"><i class="fas fa-spinner fa-spin" style="font-size: 30px;"></i><p>Loading bookings...</p></div>';
            
            fetch(`get_bookings.php?user=${userFilter}&exact_date=${exactDate}`)
                .then(response => response.json())
                .then(data => {
                    updateUI(data);
                })
                .catch(error => {
                    container.innerHTML = `<div class="error" style="grid-column: 1/-1; text-align: center; padding: 40px; color: #dc3545;">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            <p>Error loading bookings: ${error}</p>
                                         </div>`;
                });
        }
        
        // Update UI with fetched data
        function updateUI(data) {
            const container = document.getElementById('bookingsContainer');
            const summaryContainer = document.getElementById('summaryContainer');
            
            container.innerHTML = data.html;
            
            if (data.count > 0) {
                document.getElementById('totalBookings').textContent = data.count;
                document.getElementById('totalWeight').textContent = data.totalWeight;
                document.getElementById('totalPieces').textContent = data.totalPieces;
                document.getElementById('totalCharges').textContent = 'Rs' + data.totalCharges;
                summaryContainer.style.display = 'grid';
            } else {
                container.innerHTML = `<div class="no-bookings" style="grid-column: 1/-1; text-align: center; padding: 40px; color: #666;">
                                        <i class="fas fa-box-open" style="font-size: 50px; margin-bottom: 20px;"></i>
                                        <h3>No bookings found</h3>
                                        <p>There are no bookings matching your criteria</p>
                                      </div>`;
                summaryContainer.style.display = 'none';
            }
        }
        
        // Show shipment details modal
        function showDetails(shipmentId) {
            const modal = document.getElementById('detailsModal');
            const modalContent = document.getElementById('modalContent');
            
            modalContent.innerHTML = '<div style="text-align: center; padding: 20px;"><i class="fas fa-spinner fa-spin" style="font-size: 30px;"></i><p>Loading details...</p></div>';
            modal.style.display = "block";
            
            fetch(`get_shipment_details.php?shipment_id=${shipmentId}`)
                .then(response => response.json())
                .then(data => {
                    modalContent.innerHTML = `
                        <h2 style="margin-bottom: 20px; color: #333;">Shipment Details</h2>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                            <div style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
                                <h3 style="margin-bottom: 15px; color: #555; border-bottom: 1px solid #eee; padding-bottom: 10px;">Shipper Information</h3>
                                <p><strong>Name:</strong> ${data.shipper_name}</p>
                                <p><strong>Phone:</strong> ${data.shipper_cell}</p>
                                <p><strong>Address:</strong> ${data.shipper_address}</p>
                            </div>
                            <div style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
                                <h3 style="margin-bottom: 15px; color: #555; border-bottom: 1px solid #eee; padding-bottom: 10px;">Consignee Information</h3>
                                <p><strong>Name:</strong> ${data.consignee_name}</p>
                                <p><strong>Phone:</strong> ${data.consignee_cell}</p>
                                <p><strong>Address:</strong> ${data.consignee_address}</p>
                            </div>
                            <div style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
                                <h3 style="margin-bottom: 15px; color: #555; border-bottom: 1px solid #eee; padding-bottom: 10px;">Shipment Details</h3>
                                <p><strong>Type:</strong> ${data.shipment_type}</p>
                                <p><strong>Weight:</strong> ${data.weight} kg</p>
                                <p><strong>Pieces:</strong> ${data.pieces}</p>
                                <p><strong>Total Charges:</strong> Rs${data.total_charges}</p>
                            </div>
                        </div>
                    `;
                })
                .catch(error => {
                    modalContent.innerHTML = `<div style="text-align: center; padding: 20px; color: #dc3545;">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            <h3>Error loading details</h3>
                                            <p>${error}</p>
                                        </div>`;
                });
        }
        
        // Close modal
        document.querySelector('.close').onclick = function() {
            document.getElementById('detailsModal').style.display = "none";
        }
        
        window.onclick = function(event) {
            if (event.target == document.getElementById('detailsModal')) {
                document.getElementById('detailsModal').style.display = "none";
            }
        }
        
        // Initialize with today's bookings
        document.addEventListener('DOMContentLoaded', function() {
            loadBookings();
        });
    </script>
</body>
</html>