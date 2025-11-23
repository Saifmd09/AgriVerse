// ------------------------------
// INITIAL STATE
// ------------------------------
document.addEventListener("DOMContentLoaded", () => {
    document.getElementById("otpSection").style.display = "none";
});

let emailVerified = false;
let resendTimer = null;
let countdown = 30;

// ------------------------------
// RESEND COUNTDOWN
// ------------------------------
function startResendCountdown() {
    const resendBtn = document.getElementById("resendOtpBtn");
    document.getElementById("resendArea").style.display = "block";

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

// ------------------------------
// SEND OTP FUNCTION
// ------------------------------
function sendOtp(isResend = false) {
    const email = document.getElementById("email").value.trim();
    const otpSection = document.getElementById("otpSection");
    const otpMsg = document.getElementById("otpMsg");

    if (!email) {
        alert("Enter your email first.");
        return;
    }

    const fd = new FormData();
    fd.append("email", email);

    fetch("../backend/send-investor-otp.php", {
        method: "POST",
        body: fd
    })
        .then(res => res.text())
        .then(data => {
            otpSection.style.display = "block";

            if (data.toUpperCase().includes("OTP_SENT")) {
                otpMsg.style.color = "green";
                otpMsg.innerHTML = isResend
                    ? "🔁 New OTP sent. Check your email again."
                    : "✅ OTP sent successfully. Check your email.";
                startResendCountdown();
            } else {
                otpMsg.style.color = "red";
                otpMsg.innerHTML = "⚠️ OTP sending failed. Try again.";
                console.log("DEBUG OTP RESPONSE:", data);
            }
        });
}

document.getElementById("sendOtpBtn").addEventListener("click", () => sendOtp(false));
document.getElementById("resendOtpBtn").addEventListener("click", () => sendOtp(true));

// ------------------------------
// VERIFY OTP
// ------------------------------
document.getElementById("verifyOtpBtn").addEventListener("click", () => {
    const otp = document.getElementById("email_otp").value.trim();
    const otpMsg = document.getElementById("otpMsg");

    if (!otp) {
        alert("Enter OTP first.");
        return;
    }

    const fd = new FormData();
    fd.append("otp", otp);

    fetch("../backend/verify-investor-otp.php", {
        method: "POST",
        body: fd
    })
        .then(res => res.text())
        .then(data => {
            if (data === "VERIFIED") {
                emailVerified = true;
                otpMsg.style.color = "green";
                otpMsg.innerHTML = "✅ Email verified.";

                clearInterval(resendTimer);

                document.getElementById("email").disabled = true;
                document.getElementById("sendOtpBtn").disabled = true;
                document.getElementById("resendOtpBtn").disabled = true;

                document.getElementById("submitBtn").disabled = false;
                document.getElementById("submitBtn").classList.remove("disabled");
            }
            else if (data === "INVALID_OTP") {
                otpMsg.style.color = "red";
                otpMsg.innerHTML = "❌ Incorrect OTP.";
            }
            else if (data === "OTP_EXPIRED") {
                otpMsg.style.color = "red";
                otpMsg.innerHTML = "⚠️ OTP expired. Please resend.";
            }
            else {
                otpMsg.style.color = "red";
                otpMsg.innerHTML = data;
            }
        });
});

// ------------------------------
// SUBMIT INVESTOR SIGNUP FORM
// ------------------------------
document.getElementById("investorSignupForm").addEventListener("submit", (e) => {
    e.preventDefault();

    if (!emailVerified) {
        alert("Please verify your email first.");
        return;
    }

    const msg = document.getElementById("msg");

    const fd = new FormData();
    const fields = [
        "name", "email", "phone", "password", "confirm_password",
        "org_name", "designation", "country", "state", "city", "address",
        "account_holder", "bank_account", "bank_ifsc", "pan"
    ];

    fields.forEach(id =>
        fd.append(id, document.getElementById(id).value)
    );

    // Agri-focus (multiple checkboxes)
    let focusArr = [];
    document.querySelectorAll(".focus:checked").forEach(cb => {
        focusArr.push(cb.value);
    });
    fd.append("focus", JSON.stringify(focusArr));

    // File uploads
    fd.append("citizen_proof", document.getElementById("citizen_proof").files[0]);
    fd.append("bank_proof", document.getElementById("bank_proof").files[0]);

    fetch("../backend/register-investor.php", {
        method: "POST",
        body: fd
    })
        .then(res => res.text())
        .then(data => {
            if (data.trim() === "SUCCESS") {
                msg.style.color = "green";
                msg.innerHTML = "✅ Account created! Redirecting...";

                setTimeout(() => {
                    window.location.href = "verify-investor.html";
                }, 1500);
            } else {
                msg.style.color = "red";
                msg.innerHTML = data;
            }
        });
});
