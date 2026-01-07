<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Ensure no output before headers
ob_start();

// Set JSON header
header('Content-Type: application/json');

// Database connection
$config = require __DIR__ . '/../config/database.php';
$host = $config['host'];
$username = $config['username'];
$password = $config['password'];
$dbname = $config['dbname'];
$charset = $config['charset'];

$response = ['success' => false, 'message' => ''];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tracking_number'])) {
        $trackingNumber = trim($_POST['tracking_number']);
        
        if (empty($trackingNumber)) {
            $response['message'] = 'Please enter a tracking number';
            echo json_encode($response);
            exit;
        }

        // Check if shipment exists
        $stmt = $pdo->prepare("SELECT * FROM shipments WHERE shipment_id = ?");
        $stmt->execute([$trackingNumber]);
        $shipmentExists = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$shipmentExists) {
            $response['message'] = 'This tracking ID has no data';
            echo json_encode($response);
            exit;
        }

        // Get tracking info
        $stmt = $pdo->prepare("SELECT st.*, s.* FROM shipment_tracking st 
                               JOIN shipments s ON st.shipment_id = s.shipment_id 
                               WHERE st.shipment_id = ?");
        $stmt->execute([$trackingNumber]);
        $trackingInfo = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($trackingInfo) {
            if (empty($trackingInfo['original_id']) || empty($trackingInfo['shipment_company']) || empty($trackingInfo['status'])) {
                $response = [
                    'success' => true,
                    'status' => 'processing',
                    'message' => 'Shipment is being processed'
                ];
            } else {
                // Process status updates - assuming format is "timestamp: update\ntimestamp: update"
                $statusUpdates = [];
                if (!empty($trackingInfo['status'])) {
                    // Split by new lines
                    $updates = explode("\n", $trackingInfo['status']);
                    
                    // Clean and filter updates
                    foreach ($updates as $update) {
                        $update = trim($update);
                        if (!empty($update)) {
                            $statusUpdates[] = $update;
                        }
                    }
                }
                
                // If no updates found, use current status with current time
                if (empty($statusUpdates)) {
                    $statusUpdates[] = date('Y-m-d H:i:s') . ": " . $trackingInfo['status'];
                }

                $response = [
                    'success' => true,
                    'status' => 'tracking',
                    'tracking_info' => $trackingInfo,
                    'status_updates' => $statusUpdates,
                    'tracking_url' => getTrackingUrl($trackingInfo['shipment_company'], $trackingInfo['original_id'])
                ];
            }
        } else {
            $response = [
                'success' => true,
                'status' => 'booked',
                'message' => 'Shipment has been booked but not yet dispatched'
            ];
        }
    } else {
        $response['message'] = 'Invalid request method or missing tracking number';
    }
} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

ob_end_clean();
echo json_encode($response);
exit;

function getTrackingUrl($company, $originalId) {
    if (empty($company) || empty($originalId)) return null;
    
    $company = strtoupper(trim($company));
    
    switch ($company) {
        case 'DHL':
            return "https://www.dhl.com/pk-en/home/tracking.html?tracking-id=" . urlencode($originalId) . "&submit=1";
        case 'SKYNET':
            return "https://www.skynet.net/tracking_public?skybill=" . urlencode($originalId);
        case 'UPS':
            return "https://www.ups.com/track?loc=en_US&tracknum=" . urlencode($originalId) . "&requester=ST/";
        case 'FEDEX':
            return "https://www.fedex.com/fedextrack/?trknbr=" . urlencode($originalId);
        case 'DPD':
            return "https://www.dpd.com/tracking/?search=" . urlencode($originalId);
        default:
            return null;
    }
}
?>