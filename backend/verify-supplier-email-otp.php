<?php
session_start();

$userOtp = $_POST["otp"] ?? '';

if (!isset($_SESSION["supplier_email_otp"])) {
    exit("OTP_NOT_SENT");
}

if ($userOtp != $_SESSION["supplier_email_otp"]) {
    exit("INVALID_OTP");
}

if (time() > $_SESSION["supplier_email_expires"]) {
    exit("OTP_EXPIRED");
}

// mark as verified
$_SESSION["supplier_email_verified"] = $_SESSION["supplier_email_pending"];

// cleanup
unset($_SESSION["supplier_email_otp"]);
unset($_SESSION["supplier_email_expires"]);

echo "VERIFIED";
?>
