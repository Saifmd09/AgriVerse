document.addEventListener("DOMContentLoaded", () => {
  document.getElementById("otpSection").style.display = "none";
});

let emailVerified = false;
let resendTimer = null;
let countdown = 30;

// ✅ Common function to start countdown
function startResendCountdown() {
  const resendBtn = document.getElementById("resendOtpBtn");
  const resendArea = document.getElementById("resendArea");
  
  resendArea.style.display = "block";
  resendBtn.disabled = true;
  countdown = 30;
  resendBtn.innerText = `Resend OTP (${countdown}s)`;

  resendTimer = setInterval(() => {
    countdown--;
    resendBtn.innerText = `Resend OTP (${countdown}s)`;
    if (countdown <= 0) {
      clearInterval(resendTimer);
      resendBtn.disabled = false;
      resendBtn.innerText = "Resend OTP";
    }
  }, 1000);
}

// ✅ SEND OTP / RESEND OTP (merged logic)
function sendOtp(isResend = false) {
  const email = document.getElementById("email").value.trim();
  const otpSection = document.getElementById("otpSection");
  const otpMsg = document.getElementById("otpMsg");

  if (!email) {
    alert("Please enter your email first.");
    return;
  }

  const fd = new FormData();
  fd.append("email", email);

  fetch("../backend/send-email-otp.php", { method: "POST", body: fd })
    .then(res => res.text())
    .then(data => {
      otpSection.style.display = "block";
      if (data.toUpperCase().includes("OTP_SENT")) {
        otpMsg.style.color = "green";
        otpMsg.innerHTML = isResend
          ? "🔁 New OTP sent! Please check your email again."
          : "✅ OTP sent successfully! Please check your email.";
        startResendCountdown();
      } else {
        otpMsg.style.color = "red";
        otpMsg.innerHTML = "⚠️ Unable to send OTP right now. Please try again.";
        console.log("DEBUG OTP RESPONSE:", data);
      }
    });
}

// ✅ Event listeners
document.getElementById("sendOtpBtn").addEventListener("click", () => sendOtp(false));
document.getElementById("resendOtpBtn").addEventListener("click", () => sendOtp(true));

// ✅ VERIFY OTP
document.getElementById("verifyOtpBtn").addEventListener("click", () => {
  const otp = document.getElementById("email_otp").value.trim();
  const otpMsg = document.getElementById("otpMsg");

  if (!otp) {
    alert("Enter OTP first.");
    return;
  }

  const fd = new FormData();
  fd.append("otp", otp);

  fetch("../backend/verify-email-otp.php", { method: "POST", body: fd })
    .then(res => res.text())
    .then(data => {
      if (data === "VERIFIED") {
        emailVerified = true;
        otpMsg.style.color = "green";
        otpMsg.innerHTML = "✅ Email Verified Successfully!";

        document.getElementById("email").disabled = true;
        document.getElementById("sendOtpBtn").disabled = true;
        document.getElementById("resendOtpBtn").disabled = true;
        document.getElementById("submitBtn").disabled = false;
        document.getElementById("submitBtn").classList.remove("disabled-btn");
        clearInterval(resendTimer);
      } else if (data === "INVALID_OTP") {
        otpMsg.style.color = "red";
        otpMsg.innerHTML = "❌ Incorrect OTP. Try again.";
      } else if (data === "OTP_EXPIRED") {
        otpMsg.style.color = "red";
        otpMsg.innerHTML = "⚠️ OTP expired. Please resend.";
      } else {
        otpMsg.style.color = "red";
        otpMsg.innerHTML = data;
      }
    });
});

// ✅ FINAL FORM SUBMISSION
document.getElementById("signupForm").addEventListener("submit", (e) => {
  e.preventDefault();

  if (!emailVerified) {
    alert("Please verify your email first.");
    return;
  }

  const fd = new FormData();
  [
    "name", "email", "phone", "password", "confirm_password", "gender", "dob",
    "state", "district", "village", "address", "land_area_acres", "farming_type",
    "aadhaar_last4", "bank_account", "bank_ifsc"
  ].forEach(id => fd.append(id, document.getElementById(id).value));

  fetch("../backend/register-farmer.php", { method: "POST", body: fd })
  .then(res => res.text())
  .then(data => {
      const msg = document.getElementById("msg");

      if (data.trim() === "SUCCESS") {
          msg.style.color = "green";
          msg.innerHTML = "✅ Account created successfully! Redirecting...";
          setTimeout(() => {
              window.location.href = "verify-farm.html";
          }, 2000);
      } else {
          msg.style.color = "red";
          msg.innerHTML = data;
      }
  });
});
