let stream;

const video = document.getElementById('camera-feed');
const button = document.getElementById('capture-button');
const dataStatus = document.getElementById('data-status');
const canvas = document.getElementById('photo-canvas');
const capturedImage = document.getElementById('captured-image');

function startCamera() {
    // navigator.mediaDevices - Brower's access point to media hardware (camera/mic).
    // getUsermedia - The specific function to request access.
    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {

        // True then the browser will show a prompt to the user.
        navigator.mediaDevices.getUserMedia({ video: true})
        .then(function(s) {
            stream = s;

            video.srcObject = stream; // Connect the live camera data to the HTML <video> element.
            video.play();

            button.disabled = false;
            button.textContent = "Capture Waste";
            dataStatus.textContent = "✅ Camera Ready. Show the waste item.";
        })
        .catch(function(err) {
            console.err("Error accessing camera: ", err);
            dataStatus.textContent = "❌ Error: Could not access camera. Check permissions or if another app is using it."
        })
    } else {
        dataStatus.textContent = "❌ Error: Your browser does not support camera access.";
    }
}

window.onload = startCamera;

button.addEventListener('click', function() {
    button.disabled = true;
    button.textContent = "Processing...";
    dataStatus.textContent = "Capturing image and preparing for submission..."

    // Configure the Canvas for Snapshot
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight; 
    const context = canvas.getContext('2d');

    // Take the Snapshot!
    context.drawImage(video, 0, 0, canvas.width, canvas.height);

    // Convert the image to a data URL (Base64)
    const dataUrl = canvas.toDataURL('image/jpeg', 0.9);

    // Update UI (Show the captured image)
    capturedImage.src = dataUrl;
    capturedImage.style.display = 'block';

    // Prepare Data for Transmission
    const base64Image = dataUrl.split(',')[1];
    
    sendForClassification(base64Image);
})

function sendForClassification(base64Image) {
    const xhr = new XMLHttpRequest();

    xhr.open('POST', 'classify_waste.php', true);

    xhr.setRequestHeader('Content-Type', 'application/json');

    xhr.onload = function() {
        button.disabled = false;
        button.textContent = "Capture Waste Again";

        if (xhr.status === 200) { 
            try {
                const response = JSON.parse(xhr.responseText); // Convert the JSON string into a JS object
                
                // Check the custom status from the PHP script
                if (response.status === 'success') {
                     dataStatus.innerHTML = `✅ SUCCESS - Server Received Data and Processed.<br>Server Message: ${response.message}`;
                } else {
                     dataStatus.innerHTML = `❌ ERROR - Server responded with an issue.<br>Server Message: ${response.message}`;
                }
            } catch (e) {
                // This catches errors if the server response wasn't valid JSON
                dataStatus.innerHTML = `❌ ERROR - Invalid JSON response from server. Raw response: ${xhr.responseText}`;
                console.error("JSON Parse Error:", e);
            }
        } else {
            // This catches network errors - server down, file not found
            dataStatus.innerHTML = `❌ NETWORK ERROR - Could not reach server. Status: ${xhr.status} (${xhr.statusText}). Make sure your PHP server is running.`;
        }
    };

    xhr.onerror = function() {
        button.disabled = false;
        button.textContent = "Capture Waste Again";
        dataStatus.innerHTML = `❌ CONNECTION FAILED - Check your server setup.`;
    };

    // Send the Base64 image data wrapped in a JSON object
    const dataToSend = JSON.stringify({ image: base64Image });
    xhr.send(dataToSend);
    dataStatus.innerHTML += "<br>Sending data to `classify_waste.php`...";
}