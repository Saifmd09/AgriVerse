let map, marker;

// ✅ Initialize Google Map
function initMap() {
  const defaultLoc = { lat: 20.5937, lng: 78.9629 }; // India center
  map = new google.maps.Map(document.getElementById("map"), {
    center: defaultLoc,
    zoom: 5,
  });

  map.addListener("click", (e) => {
    placeMarker(e.latLng);
  });
}

function placeMarker(location) {
  if (marker) marker.setMap(null);
  marker = new google.maps.Marker({
    position: location,
    map: map,
  });

  document.getElementById("latitude").value = location.lat().toFixed(6);
  document.getElementById("longitude").value = location.lng().toFixed(6);
  document.getElementById("locationResult").innerHTML = `✅ Location Selected: ${location.lat().toFixed(6)}, ${location.lng().toFixed(6)}`;
}

// ✅ Show map when button clicked
document.getElementById("openMapBtn").addEventListener("click", () => {
  document.getElementById("map").style.display = "block";
  setTimeout(() => {
    google.maps.event.trigger(map, "resize");
  }, 200);
});

// ✅ Handle Form Submission
document.getElementById("verifyFarmForm").addEventListener("submit", (e) => {
  e.preventDefault();

  const msg = document.getElementById("msg");
  msg.style.color = "#1d6f42";
  msg.innerHTML = "⏳ Uploading your documents... Please wait.";

  const fd = new FormData(document.getElementById("verifyFarmForm"));

  fetch("../backend/verify-farm.php", {
    method: "POST",
    body: fd
  })
  .then(res => res.text())
  .then(data => {
    if (data.toUpperCase().includes("SUCCESS")) {
      msg.style.color = "green";
      msg.innerHTML = "✅ Verification submitted successfully! Our team will review it soon.";
      setTimeout(() => {
        window.location.href = "under_verification.html";
      }, 3000);
    } else {
      msg.style.color = "red";
      msg.innerHTML = data;
    }
  })
  .catch(err => {
    msg.style.color = "red";
    msg.innerHTML = "❌ Something went wrong.";
    console.error(err);
  });
});
