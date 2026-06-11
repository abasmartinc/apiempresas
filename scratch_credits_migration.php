<?php
$mysqli = new mysqli('217.61.210.127', 'apiempresas_user', 'WONwyjpsmx3h3$@2', 'reseller3537_apiempresas');
if ($mysqli->connect_error) die('Connection failed: ' . $mysqli->connect_error);

// 1. user_wallets
$mysqli->query("
CREATE TABLE IF NOT EXISTS user_wallets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    balance INT NOT NULL DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY user_idx (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");
echo "user_wallets checked/created.\n";

// 2. user_wallet_transactions
$mysqli->query("
CREATE TABLE IF NOT EXISTS user_wallet_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    amount INT NOT NULL,
    transaction_type ENUM('stripe_payment', 'admin_adjustment', 'promo', 'api_usage_batch') NOT NULL,
    reference_id VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY user_idx (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");
echo "user_wallet_transactions checked/created.\n";

// 3. Add credits_used to api_usage_daily
$check = $mysqli->query("SHOW COLUMNS FROM api_usage_daily LIKE 'credits_used'");
if ($check && $check->num_rows == 0) {
    if ($mysqli->query("ALTER TABLE api_usage_daily ADD COLUMN credits_used INT NOT NULL DEFAULT 0 AFTER requests_count")) {
        echo "Added credits_used to api_usage_daily.\n";
    } else {
        echo "Error altering api_usage_daily: " . $mysqli->error . "\n";
    }
} else {
    echo "Column credits_used already exists.\n";
}

echo "Migration completed.\n";
$mysqli->close();
