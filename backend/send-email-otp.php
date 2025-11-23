<?php
session_start();
require "db.php";
require "mail_helper.php";

$email = trim($_POST["email"] ?? '');

if ($email == '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    exit("Invalid Email");
}

// ✅ Check if email already exists
$chk = $conn->prepare("SELECT id FROM farmers WHERE email = ?");
$chk->bind_param("s", $email);
$chk->execute();
$chk->store_result();
if ($chk->num_rows > 0) {
    exit("Email already registered");
}
$chk->close();

// ✅ Generate OTP
$otp = rand(100000, 999999);
$expires = (new DateTime("+10 minutes"))->format("Y-m-d H:i:s");

// ✅ Store OTP temporarily in session (before actual registration)
$_SESSION["pending_email"] = $email;
$_SESSION["email_otp"] = $otp;
$_SESSION["email_otp_expires"] = $expires;

// ✅ Send OTP Email
if (sendOTPEmail($email, "User", $otp)) {
    echo "OTP_SENT";
} else {
    echo "Failed to send OTP";
}
?>
