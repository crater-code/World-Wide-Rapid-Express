// Toggle weight fields function
function toggleWeightFields(useVolumetric) {
    const buttons = document.querySelectorAll('.toggle-group .toggle-btn');
    buttons.forEach(btn => btn.classList.remove('active'));
    buttons[useVolumetric ? 1 : 0].classList.add('active');

    document.querySelector('.volumetric-fields').style.display = useVolumetric ? 'block' : 'none';
    document.querySelector('.standard-weight').style.display = useVolumetric ? 'none' : 'block';
    
    // Update hidden field for volumetric weight
    let volumetricInput = document.querySelector('input[name="volumetricWeight"]');
    if (!volumetricInput) {
        volumetricInput = document.createElement('input');
        volumetricInput.type = 'hidden';
        volumetricInput.name = 'volumetricWeight';
        document.getElementById('shipmentForm').appendChild(volumetricInput);
    }
    volumetricInput.value = useVolumetric ? '1' : '0';
}

// Initialize all functionality on page load
document.addEventListener("DOMContentLoaded", function() {
    // Initialize volumetric weight toggle
    toggleWeightFields(false);
    
    // Add click handlers to volumetric weight toggle buttons
    document.querySelectorAll('.toggle-group .toggle-btn').forEach(button => {
        button.addEventListener('click', function() {
            const isYes = this.textContent.trim() === 'Yes';
            toggleWeightFields(isYes);
        });
    });

    // Pieces increment/decrement functionality
    let pieces = 1;
    const piecesInput = document.getElementById("pieces");
    const piecesLabel = document.querySelector(".pieces-label");

    function updatePiecesLabel() {
        piecesLabel.textContent = `Pieces* (${pieces})`;
        piecesInput.value = pieces;
    }

    document.querySelector(".piece-btn.plus")?.addEventListener("click", function() {
        pieces += 1;
        updatePiecesLabel();
    });

    document.querySelector(".piece-btn.minus")?.addEventListener("click", function() {
        if (pieces > 1) {
            pieces -= 1;
            updatePiecesLabel();
        }
    });

    // Initialize pieces label
    updatePiecesLabel();
    
    // Calculate rates functionality
    document.querySelector('.calculate-btn')?.addEventListener('click', function() {
        try {
            const baseCharges = parseFloat(document.getElementById('shipmentCharges').value) || 0;
            const discountPercent = parseFloat(document.getElementById('discountPercent').value) || 0;
            const gstPercent = parseFloat(document.getElementById('gstPercent').value) || 0;
            const insurancePercent = parseFloat(document.getElementById('insurancePercent').value) || 0;
            const packagingAmount = parseFloat(document.getElementById('packagingAmount').value) || 0;
            const parcelValue = parseFloat(document.getElementById('parcelValue').value) || 0;

            // Calculate values
            const discount = (discountPercent / 100) * baseCharges;
            const chargesWithDiscount = baseCharges - discount;
            const gst = (gstPercent / 100) * chargesWithDiscount;
            const insuranceCharges = (insurancePercent / 100) * parcelValue;
            const packagingCharges = packagingAmount;
            const totalCharges = chargesWithDiscount + gst + insuranceCharges + packagingCharges;

            // Update form fields with formatted values
            document.getElementById('charges').value = baseCharges.toFixed(2);
            document.getElementById('discount').value = discount.toFixed(2);
            document.getElementById('chargesWithDiscount').value = chargesWithDiscount.toFixed(2);
            document.getElementById('gst').value = gst.toFixed(2);
            document.getElementById('insuranceCharges').value = insuranceCharges.toFixed(2);
            document.getElementById('packagingCharges').value = packagingCharges.toFixed(2);
            document.getElementById('totalCharges').value = totalCharges.toFixed(2);
        } catch (error) {
            console.error("Calculation error:", error);
            alert("Error calculating charges. Please check your inputs.");
        }
    });
});

// Form submission handler
function handleSubmit(event) {
    event.preventDefault();

    try {
        // Get form data
        const form = document.getElementById('shipmentForm');
        const formData = new FormData(form);

        // Add volumetric weight value
        const isVolumetric = document.querySelector('.toggle-group .toggle-btn.active')?.textContent.trim() === 'Yes';
        formData.append('volumetricWeight', isVolumetric ? '1' : '0');

        // Submit the form
        fetch('shipping-form.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(html => {
                document.documentElement.innerHTML = html;
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while submitting the form.');
            });
    } catch (error) {
        console.error("Form submission error:", error);
        alert("Error submitting form. Please try again.");
    }
}

// Sidebar functionality
function toggleSidebar() {
    const sidebar = document.getElementById("sidebar");
    if (sidebar) {
        sidebar.classList.toggle("open");
    }
}

// Close sidebar when clicking outside
document.addEventListener('click', function(event) {
    const sidebar = document.getElementById('sidebar');
    const menuToggle = document.querySelector('.menu-toggle');
    
    if (sidebar && menuToggle && !sidebar.contains(event.target) && !menuToggle.contains(event.target)) {
        sidebar.classList.remove('open');
    }
});

// Label printing functionality
function printLabel(data) {
    const labelWindow = window.open('', '_blank');

    const content = `
         <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <title>Shipment Label - ${data.shipment_id || ''}</title>
                <style>
                    body { 
                        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
                        font-size: 13px; 
                        background: #fff; 
                        margin: 0; 
                        padding: 20px; 
                    }
                    .container { 
                        background: #fff; 
                        border: 1px solid #ccc; 
                        padding: 20px; 
                        max-width: 900px; 
                        margin: auto; 
                        box-shadow: none; 
                        page-break-inside: avoid; 
                    }
                    .header { 
                        display: flex; 
                        justify-content: space-between; 
                        align-items: flex-start; 
                        margin-bottom: 20px; 
                    }
                    .logo-space { 
                        width: 200px; 
                        height: 80px; 
                        display: flex;
                        justify-content: center;
                        margin-right: 50px; 
                        overflow: hidden;
                    }
                    .logo-space img {
                        max-width: 100%;
                        max-height: 100%;
                    }
                    .section-title { 
                        font-size: 16px; 
                        font-weight: bold; 
                        border-bottom: 1px solid #ddd; 
                        margin-top: 20px; 
                        padding-bottom: 5px; 
                        color: #333; 
                    }
                    table { 
                        width: 100%; 
                        border-collapse: collapse; 
                        margin-top: 10px; 
                    }
                    th, td { 
                        border: 1px solid #ccc; 
                        padding: 6px 10px; 
                        text-align: left; 
                    }
                    th { 
                        background: #f0f0f0; 
                        font-weight: bold; 
                        color: #444; 
                    }
                    .label { 
                        font-weight: 600; 
                        display: inline-block; 
                        min-width: 120px; 
                        color: #555; 
                    }
                    .value { 
                        display: inline-block; 
                        color: #000; 
                    }
                    .signature-area { 
                        display: flex; 
                        justify-content: space-between; 
                        gap: 10px; 
                        margin-top: 30px; 
                    }
                    .sign-box { 
                        border: 1px solid #ccc; 
                        width: 48%; 
                        height: 80px; 
                        padding: 10px; 
                        background: #fdfdfd; 
                    }
                    .sign-box .label { 
                        display: block; 
                        margin-bottom: 20px; 
                    }
                    .instructions { 
                        background: #fefefe; 
                        border: 1px solid #ddd; 
                        padding: 10px; 
                        margin-top: 20px; 
                    }
                    @media print {
                        body { 
                            margin: 0; 
                            padding: 0; 
                        }
                        .container { 
                            border: none; 
                            box-shadow: none; 
                        }
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <div class="logo-space">
                            <img src="../Photos/Screenshot 2025-05-10 035929.png" alt="Logo" style="max-width: 100%; max-height: 100%">
                        </div>
                        <div>
                            <div style="font-size: 22px; font-weight: bold; color: #444;">Courier Services</div>
                            <div><span class="label">Shipment ID:</span> <span class="value">${data.shipment_id || 'N/A'}</span></div>
                            <div><span class="label">Origin:</span> <span class="value">Pakistan</span></div>
                            <div><span class="label">Destination:</span> <span class="value">${data.destination || 'N/A'}</span></div>
                        </div>
                    </div>

                    <div class="section-title">Sender Information</div>
                    <div>
                        <div><span class="label">Name:</span> <span class="value">${data.shipper_name || 'N/A'}</span></div>
                        <div><span class="label">Address:</span> <span class="value">${data.shipper_address || 'N/A'}</span></div>
                        <div><span class="label">Phone:</span> <span class="value">${data.shipper_cell || 'N/A'}</span></div>
                        <div><span class="label">CNIC:</span> <span class="value">${data.shipper_cnic || 'N/A'}</span></div>
                    </div>

                    <div class="section-title">Receiver Information</div>
                    <div>
                        <div><span class="label">Name:</span> <span class="value">${data.consignee_name || 'N/A'}</span></div>
                        <div><span class="label">Address:</span> <span class="value">${data.consignee_address || 'N/A'}</span></div>
                        <div><span class="label">Phone:</span> <span class="value">${data.consignee_cell || 'N/A'}</span></div>
                        <div><span class="label">CNIC:</span> <span class="value">${data.consignee_cnic || 'N/A'}</span></div>
                    </div>

                    <div class="section-title">Shipment Details</div>
                    <table>
                        <tr>
                            <th>Shipment Type</th>
                            <th>Product Type</th>
                            <th>Shipping Company</th>
                            <th>Payment</th>
                            <th>Quantity</th>
                        </tr>
                        <tr>
                            <td>${data.shipment_type || 'N/A'}</td>
                            <td>${data.product_type || 'N/A'}</td>
                            <td>${data.shipment_through || 'N/A'}</td>
                            <td>${data.payment_method || 'N/A'}</td>
                            <td>${data.quantity || 'N/A'}</td>
                        </tr>
                    </table>

                    <div class="section-title">Package Dimensions</div>
                    <table>
                        <tr>
                            <th>Length (cm)</th>
                            <th>Breadth (cm)</th>
                            <th>Height (cm)</th>
                            <th>Weight (kg)</th>
                            <th>Pieces</th>
                        </tr>
                        <tr>
                            <td>${displayValue(data.length)}</td>
                            <td>${displayValue(data.breadth)}</td>
                            <td>${displayValue(data.height)}</td>
                            <td>${displayValue(data.weight)}</td>
                            <td>${data.pieces || '1'}</td>
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
                            <td>${parseFloat(data.charges || 0).toFixed(2)}</td>
                            <td>${parseFloat(data.discount_amount || 0).toFixed(2)}</td>
                            <td>${parseFloat(data.gst || 0).toFixed(2)}</td>
                            <td>${parseFloat(data.insurance_charges || 0).toFixed(2)}</td>
                            <td>${parseFloat(data.packaging_charges || 0).toFixed(2)}</td>
                            <td>${totalCharges.toFixed(2)}</td>
                        </tr>
                    </table>

                    ${data.tracking_info ? `
                    <div class="section-title">Tracking Information</div>
                    <table>
                        <tr>
                            <th>Original ID</th>
                            <th>Shipment Company</th>
                            <th>Status</th>
                        </tr>
                        <tr>
                            <td>${data.tracking_info.original_id || 'N/A'}</td>
                            <td>${data.tracking_info.shipment_company || 'N/A'}</td>
                            <td>${data.tracking_info.status || 'No status available'}</td>
                        </tr>
                    </table>
                    ` : ''}

                    <div class="instructions">
                        <span class="label">Special Instructions:</span><br>
                        ${data.special_instructions || 'N/A'}
                    </div>

                    <div class="signature-area">
                        <div class="sign-box">
                            <div class="label">Sender's Signature</div>
                            <div style="margin-top: 20px; border-top: 1px solid #000; padding-top: 5px;">Date: ${new Date(data.created_at).toLocaleDateString() || ''}</div>
                        </div>
                    </div>
                </div>

            <script>
                window.onload = function () {
                    setTimeout(function () {
                        window.focus();
                        window.print();
                        window.onafterprint = function () {
                            window.close();
                        };
                    }, 200); // adjust delay if needed
                };
            </script>
        </body>
        </html>
    `;

    labelWindow.document.write(content);
    labelWindow.document.close();
    labelWindow.focus();
}



// Helper function to generate label content
function generateLabelContent(data, copyType) {
    // Calculate total charges if not provided
    const totalCharges = data.total_charges || 
        ((parseFloat(data.charges) || 0) - 
         (parseFloat(data.discount_amount) || 0) + 
         (parseFloat(data.gst) || 0) + 
         (parseFloat(data.insurance_charges) || 0) + 
         (parseFloat(data.packaging_charges) || 0)).toFixed(2);

    return `
        <div class="container">
            <div class="header">
                <div class="logo-space">
                    <img src="../Photos/Screenshot 2025-05-10 035929.png" alt="Logo" style="max-width: 100%; max-height: 100%"/>
                </div>
                <div>
                    <div style="font-size: 22px; font-weight: bold; color: #444;">Courier Services</div>
                    <div><span class="label">Shipment ID:</span> <span class="value">${data.shipment_id || 'N/A'}</span></div>
                    <div><span class="label">Origin:</span> <span class="value">'Pakistan' || 'N/A'}</span></div>
                    <div><span class="label">Destination:</span> <span class="value">${data.destination || 'N/A'}</span></div>
                </div>
            </div>

            <div style="text-align: right; font-size: 13px; font-weight: bold; margin-bottom: 10px;">Copy: ${copyType}</div>

            <div class="section-title">Sender Information</div>
            <div class="col">
                <div><span class="label">Name:</span> <span class="value">${data.shipper_name || 'N/A'}</span></div>
                <div><span class="label">Address:</span> <span class="value">${data.shipper_address || 'N/A'}</span></div>
                <div><span class="label">Mobile:</span> <span class="value">${data.shipper_cell || 'N/A'}</span></div>
                <div><span class="label">CNIC:</span> <span class="value">${data.shipper_cnic || 'N/A'}</span></div>
            </div>

            <div class="section-title">Receiver Information</div>
            <div class="col">
                <div><span class="label">Name:</span> <span class="value">${data.consignee_name || 'N/A'}</span></div>
                <div><span class="label">Address:</span> <span class="value">${data.consignee_address || 'N/A'}</span></div>
                <div><span class="label">Mobile:</span> <span class="value">${data.consignee_cell || 'N/A'}</span></div>
                <div><span class="label">CNIC:</span> <span class="value">${data.consignee_cnic || 'N/A'}</span></div>
            </div>

            <div class="section-title">Shipment Details</div>
            <table>
                <tr>
                    <th>Shipment Type</th>
                    <th>Product Type</th>
                    <th>Shipping Company Name</th>
                    <th>Payment</th>
                    <th>Quantity</th>
                </tr>
                <tr>
                    <td>${data.shipment_type || data.international || 'N/A'}</td>
                    <td>${data.product_type || data.selectProduct || 'N/A'}</td>
                    <td>${data.shipment_through || data.selectShipment || 'N/A'}</td>
                    <td>${data.payment_method || (data.cash === '1' ? 'Cash' : 'Online')}</td>
                    <td>${data.quantity || '1'}</td>
                </tr>
            </table>

            <div class="section-title">Package Dimensions</div>
            <table>
                <tr>
                    <th>Length (cm)</th>
                    <th>Breadth (cm)</th>
                    <th>Height (cm)</th>
                    <th>Weight (kg)</th>
                    <th>Pieces</th>
                </tr>
                <tr>
                    <td>${data.length || 'N/A'}</td>
                    <td>${data.breadth || 'N/A'}</td>
                    <td>${data.height || 'N/A'}</td>
                    <td>${data.weight || 'N/A'}</td>
                    <td>${data.pieces || '1'}</td>
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
                    <td>${data.charges || data.shipmentCharges || '0.00'}</td>
                    <td>${data.discount_amount || data.discount || '0.00'}</td>
                    <td>${data.gst || '0.00'}</td>
                    <td>${data.insurance_charges || '0.00'}</td>
                    <td>${data.packaging_charges || '0.00'}</td>
                    <td>${totalCharges}</td>
                </tr>
            </table>

            <div class="instructions">
                <span class="label">Special Instructions:</span><br>
                ${data.special_instructions || data.specialInstructions || 'N/A'}
            </div>

            <div class="signature-area">
                <div class="sign-box">
                    <div class="label">Sender's Signature</div>
                    <div style="margin-top: 20px; border-top: 1px solid #000; padding-top: 5px;">Date: ${data.date || new Date().toLocaleDateString()}</div>
                </div>
            </div>
        </div>
    `;
}