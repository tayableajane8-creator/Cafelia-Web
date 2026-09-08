<?php
require_once "config/database.php";

$name = "Cafelia Admin";
$email = "adminlea@cafelia.com";
$password = password_hash("adminlea123", PASSWORD_DEFAULT);

$stmt = $conn->prepare(
    "INSERT INTO admin_users (name, email, password) VALUES (?, ?, ?)"
);

$stmt->bind_param("sss", $name, $email, $password);

if ($stmt->execute()) {
    echo "Admin created successfully!";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>