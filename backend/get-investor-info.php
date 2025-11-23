<?php
session_start();
require "db.php";

if (!isset($_SESSION["investor_id"])) {
    echo json_encode(["status" => "NO_LOGIN"]);
    exit;
}

$investor_id = $_SESSION["investor_id"];

// Get investor details
$inv = $conn->prepare("SELECT name FROM investors WHERE id=? LIMIT 1");
$inv->bind_param("i", $investor_id);
$inv->execute();
$inv_result = $inv->get_result()->fetch_assoc();
$investor_name = $inv_result["name"] ?? "Investor";

// Get total invested amount
$q1 = $conn->prepare("
    SELECT COALESCE(SUM(amount),0) AS total_invested 
    FROM farmer_transactions 
    WHERE investor_id=? AND txn_type='credit'
");
$q1->bind_param("i", $investor_id);
$q1->execute();
$total_invest = $q1->get_result()->fetch_assoc()["total_invested"];

// Expected returns = 12% (temporary logic)
$expected_return = $total_invest * 0.12;

// Count active farms
$q2 = $conn->prepare("
    SELECT COUNT(DISTINCT farmer_id) AS active_farms 
    FROM farmer_transactions 
    WHERE investor_id=?
");
$q2->bind_param("i", $investor_id);
$q2->execute();
$active_farms = $q2->get_result()->fetch_assoc()["active_farms"];

// Response
echo json_encode([
    "status" => "OK",
    "name" => $investor_name,
    "total_invested" => $total_invest,
    "expected_return" => $expected_return,
    "active_farms" => $active_farms
]);
exit;
?>
