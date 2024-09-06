<?php
session_start();
include '../config/connect.php';

// ตรวจสอบว่ามีการส่ง product_id มาหรือไม่
if (!isset($_GET['product_id'])) {
    die('Product ID not specified.');
}

$product_id = intval($_GET['product_id']);

// ดึงข้อมูลสินค้าจากฐานข้อมูล
$sql = "SELECT * FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die('Product not found.');
}

$product = $result->fetch_assoc();


$image_base64 = '';
if ($product['image']) {
    $image_base64 = base64_encode($product['image']);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Product Details</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1><?php echo htmlspecialchars($product['name']); ?></h1>
        <?php if (!empty($image_base64)): ?>
            <img src="data:image/jpeg;base64,<?php echo htmlspecialchars($image_base64); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="width: 300px;">
        <?php else: ?>
            <p>No image available</p>
        <?php endif; ?>
        <p>Price: ฿<?php echo number_format($product['price'], 2); ?></p>
        <p><?php echo htmlspecialchars($product['description']); ?></p>
        <a href="market.php" class="btn btn-primary">Back to Market</a>
        <a href="#" class="btn btn-warning">Add to Cart</a>
    </div>
</body>
</html>
