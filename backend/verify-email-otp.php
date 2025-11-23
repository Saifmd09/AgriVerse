<?php
session_start();

$userOtp = $_POST["otp"] ?? '';

if (!isset($_SESSION["email_otp"])) {
    exit("OTP_NOT_SENT");
}

if ($userOtp != $_SESSION["email_otp"]) {
    exit("INVALID_OTP");
}

$now = new DateTime();
$exp = new DateTime($_SESSION["email_otp_expires"]);

if ($now > $exp) {
    exit("OTP_EXPIRED");
}

// ✅ OTP Verified Successfully
$_SESSION["email_verified"] = true;

echo "VERIFIED";
?>
