// Elements - will be initialized after DOM loads
let video, canvas, captureBtn, statusText, step1, step2, classificationText, pointsBadge, qrStatus;
let stream;
let currentSubmissionId = null;
let qrScanner = null;
let qrScanTimeout = null;
let capturedImageBase64 = null; // Store captured image until QR is scanned
let classificationResult = null; // Store classification result

// Initialize elements after DOM loads
function initElements() {
    video = document.getElementById('camera-feed');
    canvas = document.getElementById('photo-canvas');
    captureBtn = document.getElementById('capture-btn');
    statusText = document.getElementById('status-text');
    step1 = document.getElementById('step-1');
    step2 = document.getElementById('step-2');
    classificationText = document.getElementById('classification-text');
    pointsBadge = document.getElementById('points-badge');
    qrStatus = document.getElementById('qr-status');
}

// Start camera for waste scanning
function startCamera() {
    console.log('Starting camera...');

    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        console.error('getUserMedia not supported');
        statusText.textContent = '❌ Camera not supported on this browser/connection.';
        captureBtn.disabled = false;
        captureBtn.textContent = 'Camera Not Available';
        return;
    }

    navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
        .then(function (s) {
            console.log('Camera started successfully');
            stream = s;
            video.srcObject = stream;
            video.play();
            captureBtn.disabled = false;
            captureBtn.textContent = 'Capture Waste Item';
            statusText.textContent = '✅ Camera ready! Position your waste item and click capture.';
        })
        .catch(function (err) {
            console.error('Camera error:', err);
            statusText.textContent = '❌ Cannot access camera. Please allow camera permission.';
            captureBtn.disabled = false;
            captureBtn.textContent = 'Camera Error - Try Again';
        });
}


// Send image to classification API (classification only, no DB save)
function sendToClassify(base64Image) {
    // Store the image for later submission
    capturedImageBase64 = base64Image;

    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'classify-only.php', true);
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.timeout = 60000; // 60 second timeout

    xhr.ontimeout = function () {
        captureBtn.disabled = false;
        captureBtn.textContent = 'Capture Waste Item';
        statusText.textContent = '⏱️ Request timed out. Please try again.';
    };

    xhr.onload = function () {
        if (xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                console.log('Classification Response:', response);

                if (response.status === 'success') {
                    // Store classification result (don't save to DB yet)
                    classificationResult = {
                        classification: response.classification,
                        confidence: response.confidence
                    };

                    // Show results
                    classificationText.textContent = `✅ ${response.classification} (${Math.round(response.confidence * 100)}% confidence)`;
                    pointsBadge.textContent = `15 points`; // Always show potential points

                    // Move to QR scanning
                    showQRScanner();
                } else {
                    captureBtn.disabled = false;
                    captureBtn.textContent = 'Capture Waste Item';
                    statusText.textContent = `❌ ${response.message || 'Classification failed'}`;
                }
            } catch (e) {
                console.error('Parse error:', e);
                captureBtn.disabled = false;
                captureBtn.textContent = 'Capture Waste Item';
                statusText.textContent = '❌ Server error. Please try again.';
            }
        } else {
            captureBtn.disabled = false;
            captureBtn.textContent = 'Capture Waste Item';
            statusText.textContent = `❌ Server error (${xhr.status})`;
        }
    };

    xhr.onerror = function () {
        captureBtn.disabled = false;
        captureBtn.textContent = 'Capture Waste Item';
        statusText.textContent = '❌ Connection failed. Check server.';
    };

    xhr.send(JSON.stringify({ image: base64Image }));
}

// Show QR scanner
function showQRScanner() {
    // Stop waste camera
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
    }

    // Hide step 1, show step 2
    step1.classList.add('hidden');
    step2.classList.remove('hidden');

    // Set 15 second timeout
    qrScanTimeout = setTimeout(() => {
        qrStatus.textContent = '⏱️ Time out! Returning to waste scanning...';
        setTimeout(() => {
            resetToStart();
        }, 2000);
    }, 15000);

    // Start QR scanner
    qrScanner = new Html5Qrcode("qr-reader");

    qrScanner.start(
        { facingMode: "environment" },
        {
            fps: 10,
            qrbox: { width: 250, height: 250 }
        },
        (decodedText) => {
            // QR code detected - clear timeout
            if (qrScanTimeout) {
                clearTimeout(qrScanTimeout);
                qrScanTimeout = null;
            }

            console.log('QR Code Scanned:', decodedText);
            console.log('QR Code Length:', decodedText.length);
            console.log('QR Code (trimmed):', decodedText.trim());
            qrStatus.textContent = '🔍 Verifying QR code...';
            qrScanner.stop();

            // Verify QR code
            verifyQR(decodedText);
        },
        (errorMessage) => {
            // Scanning... (ignore errors)
        }
    ).catch(err => {
        console.error('QR Scanner error:', err);
        qrStatus.textContent = '❌ Cannot start QR scanner. Please refresh.';
    });
}

// Verify QR code with server
function verifyQR(qrCode) {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'scan-user-qr.php', true);
    xhr.setRequestHeader('Content-Type', 'application/json');

    xhr.onload = function () {
        if (xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                console.log('Server Response:', response);

                if (response.status === 'success') {
                    qrStatus.innerHTML = `✅ <strong>SUCCESS!</strong> Welcome ${response.username}!<br>+${response.points_awarded} points awarded! 🎉`;

                    // Reset after 5 seconds
                    setTimeout(() => {
                        resetToStart();
                    }, 5000);
                } else {
                    qrStatus.textContent = `❌ ${response.message}`;
                    // Restart QR scanner
                    setTimeout(() => showQRScanner(), 2000);
                }
            } catch (e) {
                qrStatus.textContent = '❌ Server error';
            }
        } else {
            qrStatus.textContent = '❌ Connection error';
        }
    };

    xhr.send(JSON.stringify({
        qr_code: qrCode,
        image: capturedImageBase64,
        classification: classificationResult.classification,
        confidence: classificationResult.confidence
    }));
}

// Reset to beginning
function resetToStart() {
    // Stop QR scanner
    if (qrScanner) {
        qrScanner.stop().catch(err => console.log(err));
        qrScanner = null;
    }

    // Reset UI
    step2.classList.add('hidden');
    step1.classList.remove('hidden');

    captureBtn.disabled = false;
    captureBtn.textContent = 'Capture Waste Item';
    statusText.textContent = 'Ready for next item!';
    currentSubmissionId = null;

    // Restart camera
    startCamera();
}

// Start on load
window.onload = function () {
    initElements();

    // Setup capture button listener
    captureBtn.addEventListener('click', function () {
        captureBtn.disabled = true;
        captureBtn.textContent = 'Processing...';
        statusText.textContent = '📸 Capturing and analyzing...';

        // Capture image
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0);

        // Convert to base64
        const dataUrl = canvas.toDataURL('image/jpeg', 0.9);
        const base64Image = dataUrl.split(',')[1];

        // Send to server
        sendToClassify(base64Image);
    });

    startCamera();
};
