<?php
session_start();

$otp = trim($_POST["otp"] ?? '');

if (!isset($_SESSION["investor_email_otp"])) {
    exit("OTP_NOT_SENT");
}

if ($otp != $_SESSION["investor_email_otp"]) {
    exit("INVALID_OTP");
}

if (time() > $_SESSION["investor_email_otp_expires"]) {
    exit("OTP_EXPIRED");
}

// ✅ Mark investor email verified
$_SESSION["investor_verified_email"] = $_SESSION["investor_pending_email"];

// ✅ Cleanup
unset($_SESSION["investor_email_otp"]);
unset($_SESSION["investor_email_otp_expires"]);

echo "VERIFIED";
?>
