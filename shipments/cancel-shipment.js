// Toggle sidebar functionality
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    sidebar.style.display = sidebar.style.display === 'none' ? 'flex' : 'none';
}

// Print label functionality
function printLabel(data) {
    try {
        const labelWindow = window.open('', '_blank');
        if (!labelWindow) {
            alert("Popup blocked. Please allow popups for this site to print labels.");
            return;
        }

        // Calculate total charges
        const totalCharges = (parseFloat(data.charges) || 0) - 
                           (parseFloat(data.discount_amount) || 0) + 
                           (parseFloat(data.gst) || 0) + 
                           (parseFloat(data.insurance_charges) || 0) + 
                           (parseFloat(data.packaging_charges) || 0);

        // Helper function to display value or N/A
        const displayValue = (value) => {
            return (value && parseFloat(value) !== 0) ? value : 'N/A';
        };

        labelWindow.document.write(`
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
                    window.onload = function() {
                        setTimeout(function() {
                            window.print();
                            window.close();
                        }, 200);
                    };
                </script>
            </body>
            </html>
        `);
        labelWindow.document.close();
    } catch (error) {
        console.error("Label printing error:", error);
        alert("Error generating label. Please try again.");
    }
}