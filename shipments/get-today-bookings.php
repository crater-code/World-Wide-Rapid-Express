<?php
$config = require __DIR__ . '/../config/database.php';
$conn = new mysqli($config['host'], $config['username'], $config['password'], $config['dbname']);

if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// Get filters from request
$userFilter = isset($_GET['user']) ? $_GET['user'] : 'all';
$range = isset($_GET['range']) ? $_GET['range'] : 'today';
$exactDate = isset($_GET['exact_date']) ? $_GET['exact_date'] : null;

// Base query
$sql = "SELECT s.*, u.full_name 
        FROM shipments s 
        JOIN users u ON s.username = u.username 
        WHERE 1=1";

// Add user filter if not 'all'
if ($userFilter !== 'all') {
    $sql .= " AND s.username = ?";
}

// Handle date filters
if ($exactDate) {
    $sql .= " AND DATE(s.created_at) = ?";
} else {
    switch ($range) {
        case 'week':
            $sql .= " AND DATE(s.created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
            break;
        case 'month':
            $sql .= " AND DATE(s.created_at) >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
            break;
        case 'year':
            $sql .= " AND DATE(s.created_at) >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
            break;
        case 'today':
            $sql .= " AND DATE(s.created_at) = CURDATE()";
            break;
    }
}

$sql .= " ORDER BY s.created_at DESC";

$stmt = $conn->prepare($sql);

// Bind parameters
if ($userFilter !== 'all' && $exactDate) {
    $stmt->bind_param("ss", $userFilter, $exactDate);
} elseif ($userFilter !== 'all') {
    $stmt->bind_param("s", $userFilter);
} elseif ($exactDate) {
    $stmt->bind_param("s", $exactDate);
}

$stmt->execute();
$result = $stmt->get_result();

$totalCharges = 0;
$totalWeight = 0;
$totalPieces = 0;
$html = '';
$count = $result->num_rows;

if ($count > 0) {
    while ($row = $result->fetch_assoc()) {
        $totalCharges += $row['total_charges'];
        $totalWeight += $row['weight'];
        $totalPieces += $row['pieces'];
        
        $html .= '
        <div class="booking-card">
            <div class="basic-info">
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
            </div>
            <button class="review-btn" onclick="showDetails('.$row['shipment_id'].')">
                Review Details
            </button>
        </div>';
    }
} else {
    $html = "<p class='no-bookings'>No bookings found for the selected filters.</p>";
}

// Return JSON with all data
echo json_encode([
    'html' => $html,
    'totalCharges' => $totalCharges,
    'totalWeight' => $totalWeight,
    'totalPieces' => $totalPieces,
    'count' => $count
]);

$conn->close();
?>