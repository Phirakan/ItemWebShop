<?php
session_start();
include '../config/connect.php';

if (!isset($_POST['product_id']) || !isset($_POST['quantity'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing product ID or quantity']);
    exit;
}

$product_id = intval($_POST['product_id']);
$quantity = intval($_POST['quantity']);

// Fetch product from the database to check stock
$sql = "SELECT * FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Product not found']);
    exit;
}

$product = $result->fetch_assoc();

if ($product['quantity'] < $quantity) {
    echo json_encode(['status' => 'error', 'message' => 'Not enough stock']);
    exit;
}

// Add product to cart (Session-based cart for simplicity)
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (isset($_SESSION['cart'][$product_id])) {
    $_SESSION['cart'][$product_id] += $quantity;
} else {
    $_SESSION['cart'][$product_id] = $quantity;
}

// Update product quantity in database
$new_quantity = $product['quantity'] - $quantity;
$sql = "UPDATE products SET quantity = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $new_quantity, $product_id);
$stmt->execute();

echo json_encode(['status' => 'success', 'message' => 'Product added to cart']);
?>
