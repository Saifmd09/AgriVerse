/* -----------------------------
   Supplier Signup – AgriVerse
------------------------------*/

// Hide OTP boxes initially
document.addEventListener("DOMContentLoaded", () => {
    document.getElementById("emailOtpSection").style.display = "none";
});

// GLOBAL STATES
let emailVerified = false;
let resendTimer = null;
let countdown = 30;

/* ==========================================================
   ✅ CATEGORY HANDLER (Show custom category when "Other")
========================================================== */
const otherCheckbox = document.getElementById("otherCategory");
const otherBox = document.getElementById("otherCategoryBox");

otherCheckbox.addEventListener("change", () => {
    if (otherCheckbox.checked) {
        otherBox.classList.remove("hidden");
        document.getElementById("other_text").setAttribute("required", "true");
    } else {
        otherBox.classList.add("hidden");
        document.getElementById("other_text").removeAttribute("required");
    }
});

/* ==========================================================
   ✅ FUNCTION: START RESEND TIMER
========================================================== */
function startResendCountdown() {
    const resendBtn = document.getElementById("resendEmailOtpBtn");
    const resendArea = document.getElementById("resendEmailArea");

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

/* ==========================================================
   ✅ SEND EMAIL OTP
========================================================== */
function sendEmailOtp(isResend = false) {
    const email = document.getElementById("email").value.trim();
    const otpMsg = document.getElementById("emailOtpMsg");
    const otpSection = document.getElementById("emailOtpSection");

    if (!email) {
        alert("Please enter your email.");
        return;
    }

    const fd = new FormData();
    fd.append("email", email);

    fetch("../backend/send-supplier-email-otp.php", {
        method: "POST",
        body: fd
    })
    .then(res => res.text())
    .then(data => {
        otpSection.style.display = "block";

        if (data.includes("OTP_SENT")) {
            otpMsg.style.color = "green";
            otpMsg.innerHTML = isResend
                ? "🔁 New OTP sent! Please check your email."
                : "✅ OTP sent! Please check your inbox.";

            startResendCountdown();
        } else {
            otpMsg.style.color = "red";
            otpMsg.innerHTML = "⚠️ Unable to send OTP. Try again.";
        }
    })
    .catch(err => {
        otpMsg.style.color = "red";
        otpMsg.innerHTML = "⚠️ Server Error.";
        console.error(err);
    });
}

// Buttons
document.getElementById("sendEmailOtpBtn").addEventListener("click", () => sendEmailOtp(false));
document.getElementById("resendEmailOtpBtn").addEventListener("click", () => sendEmailOtp(true));

/* ==========================================================
   ✅ VERIFY EMAIL OTP
========================================================== */
document.getElementById("verifyEmailOtpBtn").addEventListener("click", () => {
    const otp = document.getElementById("email_otp").value.trim();
    const otpMsg = document.getElementById("emailOtpMsg");

    if (!otp) {
        alert("Enter OTP.");
        return;
    }

    const fd = new FormData();
    fd.append("otp", otp);

    fetch("../backend/verify-supplier-email-otp.php", {
        method: "POST",
        body: fd
    })
    .then(res => res.text())
    .then(data => {
        if (data === "VERIFIED") {
            emailVerified = true;
            otpMsg.style.color = "green";
            otpMsg.innerHTML = "✅ Email Verified Successfully";

            clearInterval(resendTimer);

            // Disable fields after success
            document.getElementById("email").disabled = true;
            document.getElementById("sendEmailOtpBtn").disabled = true;
            document.getElementById("resendEmailOtpBtn").disabled = true;

            // Enable Submit Button
            document.getElementById("submitBtn").disabled = false;
            document.getElementById("submitBtn").classList.remove("disabled-btn");

        } else if (data === "INVALID_OTP") {
            otpMsg.style.color = "red";
            otpMsg.innerHTML = "❌ Invalid OTP.";
        } else if (data === "OTP_EXPIRED") {
            otpMsg.style.color = "red";
            otpMsg.innerHTML = "⚠️ OTP expired. Resend OTP.";
        } else {
            otpMsg.style.color = "red";
            otpMsg.innerHTML = data;
        }
    });
});

/* ==========================================================
   ✅ SUBMIT SUPPLIER SIGNUP
========================================================== */
document.getElementById("supplierSignupForm").addEventListener("submit", (e) => {
    e.preventDefault();

    if (!emailVerified) {
        alert("Please verify your email before submitting.");
        return;
    }

    const msg = document.getElementById("msg");
    msg.style.color = "green";
    msg.innerHTML = "Processing... Please wait.";

    const fd = new FormData();

    // Basic fields
    [
        "business_name", "phone", "password", "confirm_password",
        "address", "state", "district", "pincode",
        "gst_number", "pan_number", "business_license",
        "bank_account", "bank_name", "bank_ifsc", "account_holder",
        "other_text"
    ].forEach(id => fd.append(id, document.getElementById(id).value));

    // Categories (multiple)
    const selectedCategories = [];
    document.querySelectorAll("input[name='category']:checked").forEach(cb => {
        selectedCategories.push(cb.value);
    });
    fd.append("category", JSON.stringify(selectedCategories));

    // Files
    fd.append("gst_certificate", document.getElementById("gst_certificate").files[0]);
    fd.append("id_proof", document.getElementById("id_proof").files[0]);
    fd.append("warehouse_photo", document.getElementById("warehouse_photo").files[0]);
    fd.append("business_license_doc", document.getElementById("business_license_doc").files[0]);

    // Submit to backend
    fetch("../backend/register-supplier.php", {
        method: "POST",
        body: fd
    })
    .then(res => res.text())
    .then(data => {
        if (data === "SUCCESS") {
            msg.style.color = "green";
            msg.innerHTML = "✅ Supplier Registered Successfully! Redirecting...";

            setTimeout(() => {
                window.location.href = "supplier-dashboard.html";
            }, 2000);

        } else {
            msg.style.color = "red";
            msg.innerHTML = data;
        }
    })
    .catch(err => {
        msg.style.color = "red";
        msg.innerHTML = "⚠️ Server Error!";
        console.error(err);
    });

});
