<?php
session_start();
require_once 'country-codes.php';

// Database connection
$config = require __DIR__ . '/../config/database.php';
$host = $config['host'];
$username = $config['username'];
$password = $config['password'];
$dbname = $config['dbname'];
$charset = $config['charset'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Volumetric weight logic
        $volumetricWeight = isset($_POST['volumetricWeight']) ? 1 : 0;

        // Concatenate country code with phone numbers
        $shipperPhone = $_POST['shipperCountryCode'] . $_POST['shipperCellNumber'];
        $consigneePhone = $_POST['consigneeCountryCode'] . $_POST['consigneeCellNumber'];

        $stmt = $pdo->prepare("INSERT INTO shipments (
            username,
            shipment_through,
            shipment_type,
            product_type,
            destination,
            volumetric_weight,
            length,
            breadth,
            height,
            weight,
            pieces,
            payment_method,
            shipper_name,
            shipper_cell,
            shipper_cnic,
            shipper_address,
            consignee_name,
            consignee_cell,
            consignee_cnic,
            consignee_address,
            packaging_amount,
            parcel_value,
            quantity,
            special_instructions,
            discount_percentage,
            charges,
            discount_amount,
            charges_with_discount,
            gst,
            gst_percentage,
            insurance_percentage,
            insurance_charges,
            packaging_charges,
            total_charges,
            timestamp
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $params = [
            $_SESSION['username'],
            $_POST['selectShipment'],
            $_POST['international'],
            $_POST['selectProduct'],
            $_POST['destination'],
            $volumetricWeight,
            floatval($_POST['length'] ?? null),
            floatval($_POST['breadth'] ?? null),
            floatval($_POST['height'] ?? null),
            floatval($_POST['weight'] ?? null),
            $_POST['pieces'] ?? 1,
            $_POST['cash'] === '1' ? 'Cash' : 'Online',
            $_POST['shipperName'],
            $shipperPhone,
            $_POST['shipperCNIC'] ?? '',
            $_POST['shipperAddress'],
            $_POST['consigneeName'],
            $consigneePhone,
            $_POST['consigneeCNIC'] ?? '',
            $_POST['consigneeAddress'],
            floatval($_POST['packagingAmount'] ?? 0),
            floatval($_POST['parcelValue']),
            intval($_POST['quantity']),
            $_POST['specialInstructions'] ?? '',
            floatval($_POST['discountPercent'] ?? 0),
            floatval($_POST['shipmentCharges'] ?? 0),
            floatval($_POST['discount'] ?? 0),
            floatval($_POST['chargesWithDiscount'] ?? 0),
            floatval($_POST['gst'] ?? 0),
            floatval($_POST['gstPercent'] ?? 0),
            floatval($_POST['insurancePercent'] ?? 0),
            floatval($_POST['insuranceCharges'] ?? 0),
            floatval($_POST['packagingCharges'] ?? 0),
            floatval($_POST['totalCharges'] ?? 0),
            date('Y-m-d H:i:s')
        ];

        $stmt->execute($params);
        $shipment_id = $pdo->lastInsertId();
        $success = "Shipment created successfully! Shipment ID: " . $shipment_id;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<script>
// The printLabel function (simplified to show critical parts)
function printLabel(data) {
    const labelWindow = window.open('', '_blank');
    
    // Critical - Verify the data is received correctly
    console.log("Print Label Data:", data);
    
    labelWindow.document.write(`
        <!-- Your HTML template -->
        <div class="section-title">Shipment Details</div>
        <table>
            <tr>
                <th>Shipment Type</th>
                <th>Product Type</th>
                <th>Shipment Through</th>
                <th>Payment</th>
            </tr>
            <tr>
                <td>${data.shipment_type || 'N/A'}</td>
                <td>${data.product_type || 'N/A'}</td>
                <td>${data.shipment_through || 'N/A'}</td>
                <td>${data.payment_method || 'N/A'}</td>
            </tr>
        </table>

        <div class="section-title">Charges Summary</div>
        <table>
            <tr>
                <th>Base Charges</th>
                <th>Discount</th>
                <th>GST</th>
                <th>Insurance</th>
                <th>Packaging</th>
                <th>Total</th>
            </tr>
            <tr>
                <td>${data.charges || '0.00'}</td>
                <td>${data.discount_amount || '0.00'}</td>
                <td>${data.gst || '0.00'}</td>
                <td>${data.insurance_charges || '0.00'}</td>
                <td>${data.packaging_charges || '0.00'}</td>
                <td>${data.total_charges || '0.00'}</td>
            </tr>
        </table>

        <div class="instructions">
            <span class="label">Special Instructions:</span><br>
            ${data.special_instructions || 'N/A'}
        </div>
    `);
    labelWindow.document.close();
}
</script>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Shipment - Courier Services</title>
    <link rel="stylesheet" href="shipping-form.css">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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

        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }
            .menu-toggle {
                display: block;
            }
        }

        .country-search-container {
            position: relative;
            margin-bottom: 5px;
        }
        
        .country-search {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 5px;
        }
        
        .country-select {
            width: 100%;
            max-height: 200px;
            overflow-y: auto;
        }
        
        .country-select option {
            padding: 8px;
        }
        
        .country-select option:hover {
            background-color: #f0f0f0;
        }
        @media screen and (max-width: 768px) {
  form {
    padding: 10px;
  }

  .form-group,
  .form-control,
  select,
  input,
  textarea {
    width: 100% !important;
    display: block;
    margin-bottom: 10px;
  }

  label {
    display: block;
    margin-bottom: 5px;
  }

  .row, .col-md-6, .col-sm-12 {
    width: 100% !important;
    padding: 5px 0;
  }

  .navbar-toggle {
    float: right;
  }

  .navbar-brand img {
    height: 30px;
  }
}

    </style>
</head>

<body>
    <!-- Navigation Bar -->
    <div class="navbar">
        <div class="brand">
            <img src="../Photos/Screenshot 2025-05-10 035929.png" alt="Logo" />
            <h1>Courier<br>Services</h1>
        </div>
        <button class="menu-toggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>
        <div class="nav-links">
            <a href="#" class="active">New<br>Shipment</a>
            <a href="../home.html">Track</a>
            <a href="../Today's Bookings/todays-booking.php">Today's<br>Bookings</a>
            <a href="../Search/search.php">Cancel<br>Shipment</a>
            <a href="../Login/login.php" class="logout">Logout</a>
        </div>
    </div>

    <div id="sidebar" class="sidebar">
        <a href="#" class="active">New Shipment</a>
        <a href="../home.html">Track</a>
        <a href="../Today's Bookings/todays-booking.php">Today's Bookings</a>
        <a href="../Search/search.php">Cancel Shipment</a>
        <a href="../Login/login.php" class="logout">Logout</a>
    </div>

    <div class="container">
        <div class="form-container">
            <div class="form-header">
                <h2>New Shipment</h2>
               <?php if ($success): ?>
    <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php
        // Calculate all charges properly
        $charges = floatval($_POST['shipmentCharges'] ?? 0);
        $discountPercent = floatval($_POST['discountPercent'] ?? 0);
        $discountAmount = ($charges * $discountPercent) / 100;
        $chargesWithDiscount = $charges - $discountAmount;
        $gstPercent = floatval($_POST['gstPercent'] ?? 0);
        $gstAmount = ($chargesWithDiscount * $gstPercent) / 100;
        $insurancePercent = floatval($_POST['insurancePercent'] ?? 0);
        $parcelValue = floatval($_POST['parcelValue'] ?? 0);
        $insuranceCharges = ($parcelValue * $insurancePercent) / 100;
        $packagingCharges = floatval($_POST['packagingAmount'] ?? 0);
        $totalCharges = $chargesWithDiscount + $gstAmount + $insuranceCharges + $packagingCharges;
        ?>

        const labelData = {
            shipment_id: '<?php echo $shipment_id; ?>',
            shipper_name: '<?php echo addslashes($_POST['shipperName']); ?>',
            shipper_address: '<?php echo addslashes($_POST['shipperAddress']); ?>',
            shipper_cell: '<?php echo addslashes($_POST['shipperCountryCode'].$_POST['shipperCellNumber']); ?>',
            shipper_cnic: '<?php echo addslashes($_POST['shipperCNIC'] ?? ''); ?>',
            consignee_name: '<?php echo addslashes($_POST['consigneeName']); ?>',
            consignee_address: '<?php echo addslashes($_POST['consigneeAddress']); ?>',
            consignee_cell: '<?php echo addslashes($_POST['consigneeCountryCode'].$_POST['consigneeCellNumber']); ?>',
            consignee_cnic: '<?php echo addslashes($_POST['consigneeCNIC'] ?? ''); ?>',
            destination: '<?php echo addslashes($_POST['destination']); ?>',
            product_type: '<?php echo addslashes($_POST['selectProduct']); ?>',
            shipment_type: '<?php echo addslashes($_POST['international']); ?>',
            shipment_through: '<?php echo addslashes($_POST['selectShipment']); ?>',
            payment_method: '<?php echo ($_POST['cash'] === '1' ? 'Cash' : 'Online'); ?>',
            pieces: '<?php echo addslashes($_POST['pieces'] ?? '1'); ?>',
            weight: '<?php echo addslashes($_POST['weight'] ?? ''); ?>',
            length: '<?php echo addslashes($_POST['length'] ?? ''); ?>',
            breadth: '<?php echo addslashes($_POST['breadth'] ?? ''); ?>',
            height: '<?php echo addslashes($_POST['height'] ?? ''); ?>',
            quantity: '<?php echo addslashes($_POST['quantity']); ?>',
            special_instructions: '<?php echo addslashes($_POST['specialInstructions'] ?? ''); ?>',
            charges: '<?php echo number_format($charges, 2); ?>',
            discount_percentage: '<?php echo $discountPercent; ?>',
            discount_amount: '<?php echo number_format($discountAmount, 2); ?>',
            charges_with_discount: '<?php echo number_format($chargesWithDiscount, 2); ?>',
            gst_percentage: '<?php echo $gstPercent; ?>',
            gst: '<?php echo number_format($gstAmount, 2); ?>',
            insurance_percentage: '<?php echo $insurancePercent; ?>',
            insurance_charges: '<?php echo number_format($insuranceCharges, 2); ?>',
            packaging_charges: '<?php echo number_format($packagingCharges, 2); ?>',
            total_charges: '<?php echo number_format($totalCharges, 2); ?>',
            date: '<?php echo date('d M y H:i:s'); ?>',
            cash: '<?php echo $_POST['cash']; ?>',
            volumetricWeight: '<?php echo $_POST['volumetricWeight'] ?? '0'; ?>',
            packaging_amount: '<?php echo number_format($packagingCharges, 2); ?>',
            parcel_value: '<?php echo number_format($parcelValue, 2); ?>'
        };
        printLabel(labelData);
    });
    </script>
<?php endif; ?>
                <?php if ($error): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
            </div>

            <form id="shipmentForm" class="shipment-form" method="POST" enctype="multipart/form-data">
                <!-- Consignment Info Section -->
                <div class="form-section">
                    <h3>Consignment Info</h3>
                    <div class="form-group">
                        <div class="input-group">
                            <select id="selectShipment" name="selectShipment" required>
                                <option value="SKYNET">SKYNET</option>
                                <option value="DPD">DPD</option>
                                <option value="DHL">DHL</option>
                                <option value="UPS">UPS</option>
                                <option value="FEDEX">FEDEX</option> 
                                <option value="ARAMEX">ARAMEX</option> 
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="input-group">
                            <select id="international" name="international" required>
                                <option value="International">International</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="input-group">
                            <select id="selectProduct" name="selectProduct" required>
                                <option value="DOC">DOC</option>
                                <option value="Non-DOC">Non-DOC</option>
                                <option value="Box">Box</option>
                                <option value="International-Economy">International-Economy</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="input-group">
                            <select id="destination" name="destination">
                                <!-- All countries below -->
                                <option value="Afghanistan">Afghanistan</option>
                                <option value="Albania">Albania</option>
                                <option value="Algeria">Algeria</option>
                                <option value="Andorra">Andorra</option>
                                <option value="Angola">Angola</option>
                                <option value="Antigua and Barbuda">Antigua and Barbuda</option>
                                <option value="Argentina">Argentina</option>
                                <option value="Armenia">Armenia</option>
                                <option value="Australia">Australia</option>
                                <option value="Austria">Austria</option>
                                <option value="Azerbaijan">Azerbaijan</option>
                                <option value="Bahamas">Bahamas</option>
                                <option value="Bahrain">Bahrain</option>
                                <option value="Bangladesh">Bangladesh</option>
                                <option value="Barbados">Barbados</option>
                                <option value="Belarus">Belarus</option>
                                <option value="Belgium">Belgium</option>
                                <option value="Belize">Belize</option>
                                <option value="Benin">Benin</option>
                                <option value="Bhutan">Bhutan</option>
                                <option value="Bolivia">Bolivia</option>
                                <option value="Bosnia and Herzegovina">Bosnia and Herzegovina</option>
                                <option value="Botswana">Botswana</option>
                                <option value="Brazil">Brazil</option>
                                <option value="Brunei">Brunei</option>
                                <option value="Bulgaria">Bulgaria</option>
                                <option value="Burkina Faso">Burkina Faso</option>
                                <option value="Burundi">Burundi</option>
                                <option value="Cabo Verde">Cabo Verde</option>
                                <option value="Cambodia">Cambodia</option>
                                <option value="Cameroon">Cameroon</option>
                                <option value="Canada">Canada</option>
                                <option value="Central African Republic">Central African Republic</option>
                                <option value="Chad">Chad</option>
                                <option value="Chile">Chile</option>
                                <option value="China">China</option>
                                <option value="Colombia">Colombia</option>
                                <option value="Comoros">Comoros</option>
                                <option value="Congo (Brazzaville)">Congo (Brazzaville)</option>
                                <option value="Congo (Kinshasa)">Congo (Kinshasa)</option>
                                <option value="Costa Rica">Costa Rica</option>
                                <option value="Croatia">Croatia</option>
                                <option value="Cuba">Cuba</option>
                                <option value="Cyprus">Cyprus</option>
                                <option value="Czech Republic">Czech Republic</option>
                                <option value="Denmark">Denmark</option>
                                <option value="Djibouti">Djibouti</option>
                                <option value="Dominica">Dominica</option>
                                <option value="Dominican Republic">Dominican Republic</option>
                                <option value="Ecuador">Ecuador</option>
                                <option value="Egypt">Egypt</option>
                                <option value="El Salvador">El Salvador</option>
                                <option value="Equatorial Guinea">Equatorial Guinea</option>
                                <option value="Eritrea">Eritrea</option>
                                <option value="Estonia">Estonia</option>
                                <option value="Eswatini">Eswatini</option>
                                <option value="Ethiopia">Ethiopia</option>
                                <option value="Fiji">Fiji</option>
                                <option value="Finland">Finland</option>
                                <option value="France">France</option>
                                <option value="Gabon">Gabon</option>
                                <option value="Gambia">Gambia</option>
                                <option value="Georgia">Georgia</option>
                                <option value="Germany">Germany</option>
                                <option value="Ghana">Ghana</option>
                                <option value="Greece">Greece</option>
                                <option value="Grenada">Grenada</option>
                                <option value="Guatemala">Guatemala</option>
                                <option value="Guinea">Guinea</option>
                                <option value="Guinea-Bissau">Guinea-Bissau</option>
                                <option value="Guyana">Guyana</option>
                                <option value="Haiti">Haiti</option>
                                <option value="Honduras">Honduras</option>
                                <option value="Hungary">Hungary</option>
                                <option value="Iceland">Iceland</option>
                                <option value="India">India</option>
                                <option value="Indonesia">Indonesia</option>
                                <option value="Iran">Iran</option>
                                <option value="Iraq">Iraq</option>
                                <option value="Ireland">Ireland</option>
                                <option value="Italy">Italy</option>
                                <option value="Jamaica">Jamaica</option>
                                <option value="Japan">Japan</option>
                                <option value="Jordan">Jordan</option>
                                <option value="Kazakhstan">Kazakhstan</option>
                                <option value="Kenya">Kenya</option>
                                <option value="Kiribati">Kiribati</option>
                                <option value="Kuwait">Kuwait</option>
                                <option value="Kyrgyzstan">Kyrgyzstan</option>
                                <option value="Laos">Laos</option>
                                <option value="Latvia">Latvia</option>
                                <option value="Lebanon">Lebanon</option>
                                <option value="Lesotho">Lesotho</option>
                                <option value="Liberia">Liberia</option>
                                <option value="Libya">Libya</option>
                                <option value="Liechtenstein">Liechtenstein</option>
                                <option value="Lithuania">Lithuania</option>
                                <option value="Luxembourg">Luxembourg</option>
                                <option value="Madagascar">Madagascar</option>
                                <option value="Malawi">Malawi</option>
                                <option value="Malaysia">Malaysia</option>
                                <option value="Maldives">Maldives</option>
                                <option value="Mali">Mali</option>
                                <option value="Malta">Malta</option>
                                <option value="Marshall Islands">Marshall Islands</option>
                                <option value="Mauritania">Mauritania</option>
                                <option value="Mauritius">Mauritius</option>
                                <option value="Mexico">Mexico</option>
                                <option value="Micronesia">Micronesia</option>
                                <option value="Moldova">Moldova</option>
                                <option value="Monaco">Monaco</option>
                                <option value="Mongolia">Mongolia</option>
                                <option value="Montenegro">Montenegro</option>
                                <option value="Morocco">Morocco</option>
                                <option value="Mozambique">Mozambique</option>
                                <option value="Myanmar">Myanmar</option>
                                <option value="Namibia">Namibia</option>
                                <option value="Nauru">Nauru</option>
                                <option value="Nepal">Nepal</option>
                                <option value="Netherlands">Netherlands</option>
                                <option value="New Zealand">New Zealand</option>
                                <option value="Nicaragua">Nicaragua</option>
                                <option value="Niger">Niger</option>
                                <option value="Nigeria">Nigeria</option>
                                <option value="North Korea">North Korea</option>
                                <option value="North Macedonia">North Macedonia</option>
                                <option value="Norway">Norway</option>
                                <option value="Oman">Oman</option>
                                <option value="Pakistan">Pakistan</option>
                                <option value="Palau">Palau</option>
                                <option value="Palestine">Palestine</option>
                                <option value="Panama">Panama</option>
                                <option value="Papua New Guinea">Papua New Guinea</option>
                                <option value="Paraguay">Paraguay</option>
                                <option value="Peru">Peru</option>
                                <option value="Philippines">Philippines</option>
                                <option value="Poland">Poland</option>
                                <option value="Portugal">Portugal</option>
                                <option value="Qatar">Qatar</option>
                                <option value="Romania">Romania</option>
                                <option value="Russia">Russia</option>
                                <option value="Rwanda">Rwanda</option>
                                <option value="Saint Kitts and Nevis">Saint Kitts and Nevis</option>
                                <option value="Saint Lucia">Saint Lucia</option>
                                <option value="Saint Vincent and the Grenadines">Saint Vincent and the Grenadines</option>
                                <option value="Samoa">Samoa</option>
                                <option value="San Marino">San Marino</option>
                                <option value="Sao Tome and Principe">Sao Tome and Principe</option>
                                <option value="Saudi Arabia">Saudi Arabia</option>
                                <option value="Senegal">Senegal</option>
                                <option value="Serbia">Serbia</option>
                                <option value="Seychelles">Seychelles</option>
                                <option value="Sierra Leone">Sierra Leone</option>
                                <option value="Singapore">Singapore</option>
                                <option value="Slovakia">Slovakia</option>
                                <option value="Slovenia">Slovenia</option>
                                <option value="Solomon Islands">Solomon Islands</option>
                                <option value="Somalia">Somalia</option>
                                <option value="South Africa">South Africa</option>
                                <option value="South Korea">South Korea</option>
                                <option value="South Sudan">South Sudan</option>
                                <option value="Spain">Spain</option>
                                <option value="Sri Lanka">Sri Lanka</option>
                                <option value="Sudan">Sudan</option>
                                <option value="Suriname">Suriname</option>
                                <option value="Sweden">Sweden</option>
                                <option value="Switzerland">Switzerland</option>
                                <option value="Syria">Syria</option>
                                <option value="Taiwan">Taiwan</option>
                                <option value="Tajikistan">Tajikistan</option>
                                <option value="Tanzania">Tanzania</option>
                                <option value="Thailand">Thailand</option>
                                <option value="Timor-Leste">Timor-Leste</option>
                                <option value="Togo">Togo</option>
                                <option value="Tonga">Tonga</option>
                                <option value="Trinidad and Tobago">Trinidad and Tobago</option>
                                <option value="Tunisia">Tunisia</option>
                                <option value="Turkey">Turkey</option>
                                <option value="Turkmenistan">Turkmenistan</option>
                                <option value="Tuvalu">Tuvalu</option>
                                <option value="Uganda">Uganda</option>
                                <option value="Ukraine">Ukraine</option>
                                <option value="United Arab Emirates">United Arab Emirates</option>
                                <option value="United Kingdom">United Kingdom</option>
                                <option value="United States">United States</option>
                                <option value="Uruguay">Uruguay</option>
                                <option value="Uzbekistan">Uzbekistan</option>
                                <option value="Vanuatu">Vanuatu</option>
                                <option value="Vatican City">Vatican City</option>
                                <option value="Venezuela">Venezuela</option>
                                <option value="Vietnam">Vietnam</option>
                                <option value="Yemen">Yemen</option>
                                <option value="Zambia">Zambia</option>
                                <option value="Zimbabwe">Zimbabwe</option>
                            </select>
                        </div>
                        <!-- Volumetric Dimensions -->

                        <div class="form-group">
                            <label class="volumetric-label">Volumetric Weight</label>
                            <div class="toggle-group">
                                <button type="button" class="toggle-btn active" onclick="toggleWeightFields(false)">No</button>
                                <button type="button" class="toggle-btn" onclick="toggleWeightFields(true)">Yes</button>
                            </div>
                        </div>
                        <div class="form-group volumetric-fields" style="display:none;">
                            <div class="input-group">
                                <input type="number" id="length" name="length" placeholder="Length (cm)*" step="0.01">
                            </div>
                            <div class="input-group">
                                <input type="number" id="breadth" name="breadth" placeholder="Breadth (cm)*" step="0.01">
                            </div>
                            <div class="input-group">
                                <input type="number" id="height" name="height" placeholder="Height (cm)*" step="0.01">
                            </div>
                        </div>

                        <!-- Standard Weight -->
                        <div class="form-group standard-weight">
                            <div class="input-group">
                                <input type="number" id="weight" name="weight" placeholder="Weight (kg)*" step="0.01">
                            </div>
                        </div>
                    </div>

                    <div class="pieces-group">
                        <button type="button" class="piece-btn minus">-</button>
                        <span class="pieces-label">Pieces*</span>
                        <button type="button" class="piece-btn plus">+</button>
                    </div>
                    <input type="hidden" id="pieces" name="pieces" value="1" required>

                    <div class="form-group">
                        <div class="input-group">
                            <select id="cash" name="cash">
                                <option value="1">Cash</option>
                                <option value="2">Online</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3>Shipper Info</h3>
                    <!-- Shipper Details -->
                    <div class="form-row">
                        <div class="form-group half">
                            <div class="input-group">
                                <div class="country-search-container">
                                    <input type="text" class="country-search" id="shipperCountrySearch" placeholder="Search country..." onkeyup="filterCountries('shipperCountrySearch', 'shipperCountryCode')">
                                    <select id="shipperCountryCode" name="shipperCountryCode" class="country-select" required>
                                        <?php foreach ($country_codes as $country): ?>
                                            <option value="<?php echo $country['code']; ?>" <?php echo $country['code'] === '+92' ? 'selected' : ''; ?>>
                                                <?php echo $country['code'] . ' ' . $country['name']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group half">
                            <div class="input-group">
                                <input type="text" id="shipperCellNumber" name="shipperCellNumber" placeholder="Shipper Cell Number*" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="input-group">
                            <input type="text" id="shipperName" name="shipperName" placeholder="Shipper Name*" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="input-group">
                            <input type="text" id="shipperCNIC" name="shipperCNIC" placeholder="Shipper CNIC">
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="input-group">
                            <textarea id="shipperAddress" name="shipperAddress" placeholder="Shipper Address*" required></textarea>
                        </div>
                    </div>

                    <!-- Consignee Details -->
                    <h3>Consignee Info</h3>
                    <div class="form-row">
                        <div class="form-group half">
                            <div class="input-group">
                                <div class="country-search-container">
                                    <input type="text" class="country-search" id="consigneeCountrySearch" placeholder="Search country..." onkeyup="filterCountries('consigneeCountrySearch', 'consigneeCountryCode')">
                                    <select id="consigneeCountryCode" name="consigneeCountryCode" class="country-select" required>
                                        <?php foreach ($country_codes as $country): ?>
                                            <option value="<?php echo $country['code']; ?>" <?php echo $country['code'] === '+92' ? 'selected' : ''; ?>>
                                                <?php echo $country['code'] . ' ' . $country['name']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group half">
                            <div class="input-group">
                                <input type="text" id="consigneeCellNumber" name="consigneeCellNumber" placeholder="Consignee Cell Number*" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="input-group">
                            <input type="text" id="consigneeName" name="consigneeName" placeholder="Consignee Name*" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="input-group">
                            <input type="text" id="consigneeCNIC" name="consigneeCNIC" placeholder="Consignee CNIC">
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="input-group">
                            <textarea id="consigneeAddress" name="consigneeAddress" placeholder="Consignee Address*" required></textarea>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <!-- Package Details -->
                    <h3>Special Instructions</h3>
                    <div class="form-group">
                        <div class="input-group">
                            <textarea id="specialInstructions" name="specialInstructions" placeholder="Special Instructions"></textarea>
                        </div>
                    </div>
                    <h3>Charges</h3>
                    <div class="form-row">
                        <div class="form-group half">
                            <div class="input-group">
                                <input type="number" id="packagingAmount" name="packagingAmount" placeholder="Packaging Amount" step="0.01">
                            </div>
                        </div>
                        <div class="form-group half">
                            <div class="input-group">
                                <input type="number" id="shipmentCharges" name="shipmentCharges" placeholder="Shipment Charges*" required step="0.01">
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group half">
                            <div class="input-group">
                                <input type="number" id="quantity" name="quantity" placeholder="Quantity*" required>
                            </div>
                        </div>
                        <div class="form-group half">
                            <div class="input-group">
                                <input type="number" id="parcelValue" name="parcelValue" placeholder="Parcel Value*" required step="0.01">
                            </div>
                        </div>
                    </div>
                    

                    <div class="form-group">
                        <div class="input-group">
                            <div class="admin-discount">
                                <div class="discount-input" id="discountField" style="display: none;">
                                    <input type="text" id="adminDiscount" name="adminDiscount" placeholder="Admin discount %">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- User input fields (Shipping Charges, Discount %, GST %, Insurance %, Packaging Amount, etc.) -->
                    <div class="form-group half">
                        <label for="discountPercent">Discount (%)</label>
                        <div class="input-group">
                            <input type="number" id="discountPercent" name="discountPercent" placeholder="Discount %" step="0.01">
                        </div>
                    </div>
                    <div class="form-group half">
                        <label for="gstPercent">GST (%)</label>
                        <div class="input-group">
                            <input type="number" id="gstPercent" name="gstPercent" placeholder="GST %" step="0.01">
                        </div>
                    </div>
                    <div class="form-group half">
                        <label for="insurancePercent">Insurance (%)</label>
                        <div class="input-group">
                            <input type="number" id="insurancePercent" name="insurancePercent" placeholder="Insurance %" step="0.01">
                        </div>
                    </div>

                    <!-- Move all calculated fields to the bottom, only one box per field -->
                    <h3>Calculated Charges</h3>
                    <div class="form-group">
                        <label for="charges">Shipment Charges</label>
                        <div class="input-group">
                            <input type="number" id="charges" name="charges" placeholder="Shipment Charges" readonly step="0.01">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="discount">Discount</label>
                        <div class="input-group">
                            <input type="number" id="discount" name="discount" placeholder="Discount" readonly step="0.01">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="chargesWithDiscount">Charges With Discount</label>
                        <div class="input-group">
                            <input type="number" id="chargesWithDiscount" name="chargesWithDiscount" placeholder="Charges With Discount" readonly step="0.01">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="gst">GST</label>
                        <div class="input-group">
                            <input type="number" id="gst" name="gst" placeholder="GST" readonly step="0.01">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="insuranceCharges">Insurance Charges</label>
                        <div class="input-group">
                            <input type="number" id="insuranceCharges" name="insuranceCharges" placeholder="Insurance Charges" readonly step="0.01">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="packagingCharges">Packaging Charges</label>
                        <div class="input-group">
                            <input type="number" id="packagingCharges" name="packagingCharges" placeholder="Packaging Charges" readonly step="0.01">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="totalCharges">Total Charges</label>
                        <div class="input-group">
                            <input type="number" id="totalCharges" name="totalCharges" placeholder="Total Charges" readonly step="0.01">
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="calculate-btn">Calculate Rates</button>
                    </div>

                    <button type="submit" class="submit-button">
                        <span class="button-text">Book & Print</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>
            </form>
        </div>
    </div>

    <!-- Include the external JavaScript file -->
    <script src="shipping-form.js"></script>
    <script>
    function filterCountries(searchInputId, selectId) {
        const searchInput = document.getElementById(searchInputId);
        const select = document.getElementById(selectId);
        const filter = searchInput.value.toLowerCase();
        
        // Get all options
        const options = select.getElementsByTagName('option');
        
        // Loop through all options and hide those that don't match the search text
        for (let i = 0; i < options.length; i++) {
            const optionText = options[i].text.toLowerCase();
            if (optionText.includes(filter)) {
                options[i].style.display = "";
            } else {
                options[i].style.display = "none";
            }
        }
        
        // If the search is empty, show all options
        if (filter === "") {
            for (let i = 0; i < options.length; i++) {
                options[i].style.display = "";
            }
        }
    }

    // Add event listeners to reset search when select changes
    document.getElementById('shipperCountryCode').addEventListener('change', function() {
        document.getElementById('shipperCountrySearch').value = '';
        filterCountries('shipperCountrySearch', 'shipperCountryCode');
    });

    document.getElementById('consigneeCountryCode').addEventListener('change', function() {
        document.getElementById('consigneeCountrySearch').value = '';
        filterCountries('consigneeCountrySearch', 'consigneeCountryCode');
    });
    </script>
</body>

</html>