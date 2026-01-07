function trackOrder() {
  const trackingNumber = document.getElementById("trackingNumber").value.trim();
  const resultDiv = document.getElementById("result");
  const popup = document.getElementById("popup");

  if (!trackingNumber) {
    resultDiv.innerHTML = `<div class="error-message">Please enter a tracking number</div>`;
    popup.style.display = "block";
    return;
  }

  resultDiv.innerHTML = `
    <div class="loading-spinner">
      <div class="spinner"></div>
      <p>Searching for tracking information...</p>
    </div>
  `;
  popup.style.display = "block";

  fetch("./api/track-shipment.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded"
    },
    body: "tracking_number=" + encodeURIComponent(trackingNumber)
  })
    .then(response => {
      if (!response.ok) throw new Error("Network response was not ok");
      return response.json();
    })
    .then(data => {
      if (!data.success) {
        throw new Error(data.message || "Unknown error occurred");
      }

      // Handle different status cases
      switch(data.status) {
        case 'booked':
          resultDiv.innerHTML = `
            <div class="tracking-container">
              <div class="tracking-header">
                <h3>Tracking Information</h3>
              </div>
              <div class="tracking-details">
                <p><strong>Shipment ID:</strong> ${trackingNumber}</p>
                <div class="status-container">
                  <p><strong>Status:</strong> <span class="status-badge status-booked">Booked</span></p>
                </div>
                <p class="processing-message">Your shipment has been booked but not yet dispatched. Please check back later for updates.</p>
              </div>
            </div>
          `;
          break;

        case 'processing':
          resultDiv.innerHTML = `
            <div class="tracking-container">
              <div class="tracking-header">
                <h3>Tracking Information</h3>
              </div>
              <div class="tracking-details">
                <p><strong>Shipment ID:</strong> ${trackingNumber}</p>
                <div class="status-container">
                  <p><strong>Status:</strong> <span class="status-badge status-pending">Processing</span></p>
                </div>
                <p class="processing-message">Your shipment is being processed. Please check back later for updates.</p>
              </div>
            </div>
          `;
          break;

        case 'tracking':
          const trackingInfo = data.tracking_info;
          const trackingUrl = data.tracking_url;

          let html = `
            <div class="tracking-container">
              <div class="tracking-header">
                <h3>Tracking Information</h3>
              </div>
              <div class="tracking-details">
                <p><strong>Shipment ID:</strong> ${trackingInfo.shipment_id || 'Not available'}</p>
                <p><strong>Original ID:</strong> ${trackingInfo.original_id || 'Not available'}</p>
                <p><strong>Shipping Company:</strong> ${trackingInfo.shipment_company || 'Not specified'}</p>
                <div class="status-container">
                  <p><strong>Status:</strong> <span class="status-badge ${getStatusClass(trackingInfo.status)}">${trackingInfo.status || 'No status available'}</span></p>
                </div>
              </div>
              <div class="tracking-actions">
          `;

          if (trackingUrl) {
            if (trackingInfo.shipment_company && trackingInfo.shipment_company.toUpperCase() === 'DPD') {
              html += `
                <button onclick="handleDpdTracking('${trackingInfo.original_id || trackingInfo.shipment_id}', '${trackingUrl}')" class="track-button dpd-button">
                  Copy & Open DPD Link
                </button>
              `;
            } else {
              html += `
                <a href="${trackingUrl}" target="_blank" class="track-button">
                  Track on ${trackingInfo.shipment_company || 'carrier website'}
                </a>
              `;
            }
          } else {
            html += `
              <button onclick="copyTrackingId('${trackingInfo.original_id || trackingInfo.shipment_id}')" class="copy-button">
                Copy Tracking ID
              </button>
              <p class="tracking-note">
                Please visit ${trackingInfo.shipment_company || 'the carrier'}'s website and paste the tracking ID.
              </p>
            `;
          }

          html += `</div></div>`;
          resultDiv.innerHTML = html;
          break;

        default:
          throw new Error(data.message || "Unknown status received");
      }
    })
    .catch(err => {
      console.error("Error details:", err);
      resultDiv.innerHTML = `
        <div class="error-message">
          <p>${err.message}</p>
        </div>
      `;
    });
}

function copyTrackingId(id) {
  navigator.clipboard.writeText(id)
    .then(() => alert("Tracking ID copied to clipboard"))
    .catch(() => alert("Failed to copy"));
}

function closePopup() {
  document.getElementById("popup").style.display = "none";
}

function getStatusClass(status) {
  if (!status) return 'status-unknown';
  status = status.toLowerCase();
  if (status.includes('delivered')) return 'status-delivered';
  if (status.includes('in transit') || status.includes('shipped')) return 'status-in-transit';
  if (status.includes('pending') || status.includes('processing')) return 'status-pending';
  if (status.includes('failed') || status.includes('error')) return 'status-error';
  if (status.includes('booked')) return 'status-booked';
  return 'status-unknown';
}

function handleDpdTracking(id, url) {
  navigator.clipboard.writeText(id)
    .then(() => {
      window.open(url, '_blank');
      alert("Tracking ID copied and DPD tracking page opened!");
    })
    .catch(() => alert("Failed to copy tracking ID"));
}