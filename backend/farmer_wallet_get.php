<?php
require 'db.php';
require 'auth_guard.php';
require 'json.php';

$farmer_id = $FARMER_ID;

// ensure wallet row exists
$conn->query("INSERT IGNORE INTO farmer_wallet (farmer_id,balance_tokens) VALUES ($farmer_id,0)");

$w = $conn->prepare("SELECT balance_tokens FROM farmer_wallet WHERE farmer_id=?");
$w->bind_param("i", $farmer_id);
$w->execute();
$balance = 0;
$w->bind_result($balance);
$w->fetch();
$w->close();

$t = $conn->prepare("SELECT created_at, description, 
    CASE WHEN txn_type='credit' THEN amount ELSE -amount END AS signed_amount
  FROM farmer_transactions
  WHERE farmer_id=?
  ORDER BY id DESC
  LIMIT 50");
$t->bind_param("i", $farmer_id);
$t->execute();
$res = $t->get_result();
$txns = [];
while ($row = $res->fetch_assoc()) { $txns[] = $row; }
$t->close();

json_ok(['balance' => intval($balance), 'transactions' => $txns]);
