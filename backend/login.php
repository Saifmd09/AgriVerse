<?php
// --- Session Settings ---
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',      // allow login to work for all folders
    'secure' => false,  // true if you have HTTPS
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();
require "db.php";

// --- JSON Output Helper ---
function response($msg) {
    echo $msg;
    exit;
}

// --- Validate Required Fields ---
$user_type   = $_POST["user_type"] ?? "";
$email_phone = trim($_POST["email_phone"] ?? "");
$password    = $_POST["password"] ?? "";

if (!$user_type || !$email_phone || !$password) {
    response("MISSING_FIELDS");
}

// --- FARMER LOGIN ---
if ($user_type === "farmer") {

    $stmt = $conn->prepare("
        SELECT id, name, farming_type, password_hash
        FROM farmers
        WHERE email=? OR phone=?
    ");
    $stmt->bind_param("ss", $email_phone, $email_phone);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) response("FARMER_NOT_FOUND");

    $stmt->bind_result($id, $name, $farming_type, $hash);
    $stmt->fetch();

    if (!password_verify($password, $hash)) response("WRONG_PASSWORD");

    $_SESSION["user_type"] = "farmer";
    $_SESSION["farmer_id"] = $id;
    $_SESSION["farmer_name"] = $name;

    response("FARMER_" . strtoupper($farming_type));
}


// --- INVESTOR LOGIN ---
if ($user_type === "investor") {

    $stmt = $conn->prepare("
        SELECT id, name, password_hash
        FROM investors
        WHERE email=?
    ");
    $stmt->bind_param("s", $email_phone);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) response("INVESTOR_NOT_FOUND");

    $stmt->bind_result($id, $name, $hash);
    $stmt->fetch();

    if (!password_verify($password, $hash)) response("WRONG_PASSWORD");

    $_SESSION["user_type"] = "investor";
    $_SESSION["investor_id"] = $id;
    $_SESSION["investor_name"] = $name;

    response("INVESTOR_OK");
}


// --- SUPPLIER LOGIN ---
if ($user_type === "supplier") {

    $stmt = $conn->prepare("
        SELECT id, business_name, password_hash
        FROM suppliers
        WHERE email=? OR phone=?
    ");
    $stmt->bind_param("ss", $email_phone, $email_phone);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) response("SUPPLIER_NOT_FOUND");

    $stmt->bind_result($id, $business_name, $hash);
    $stmt->fetch();

    if (!password_verify($password, $hash)) response("WRONG_PASSWORD");

    $_SESSION["user_type"] = "supplier";
    $_SESSION["supplier_id"] = $id;
    $_SESSION["supplier_name"] = $business_name;

    response("SUPPLIER_OK");
}

response("INVALID_ROLE");
?>
