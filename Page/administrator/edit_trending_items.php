<?php
session_start();
include '../../config/connect.php';

if (!isset($_SESSION['username'])) {
    $_SESSION['error'] = "You must log in first";
    header('location: home.php');
    exit();
}
$username = $_SESSION['username'];
$query = "SELECT role FROM user WHERE username = '$username' AND role = 1";
$result = mysqli_query($conn, $query);
if (mysqli_num_rows($result) == 0) {
    $_SESSION['error'] = "You don't have permission to access this page";
    header('location: ../home.php');
    exit();
}
$walletBalance = isset($_SESSION['walletbalance']) ? $_SESSION['walletbalance'] : 0; 
// ตรวจสอบว่ามีการส่ง ID ของ item หรือไม่
if (isset($_GET['id'])) {
    $itemId = intval($_GET['id']);

    // ดึงข้อมูล item จาก database
    $sql = "SELECT * FROM trending_items WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $itemId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $item = $result->fetch_assoc();
    } else {
        echo "Item not found.";
        exit();
    }

    // เมื่อมีการกดปุ่มอัปเดต
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = $_POST['name'];

        // ตรวจสอบว่ามีการอัปโหลดรูปใหม่หรือไม่
        if (!empty($_FILES['image']['tmp_name'])) {
            $image = file_get_contents($_FILES['image']['tmp_name']);

            // อัปเดตชื่อและรูปภาพใน database
            $sql = "UPDATE trending_items SET name = ?, image = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sbi", $name, $image, $itemId);
            
            // ใช้ mysqli_stmt_send_long_data สำหรับจัดการข้อมูล blob ขนาดใหญ่
            $stmt->send_long_data(1, $image);
        } else {
            // อัปเดตเฉพาะชื่อเมื่อไม่มีการอัปโหลดรูปใหม่
            $sql = "UPDATE trending_items SET name = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $name, $itemId);
        }

        // ดำเนินการอัปเดต
        if ($stmt->execute()) {
            echo "Item updated successfully!";
            header("Location: trending_list.php"); // กลับไปยังหน้ารายการ
            exit();
        } else {
            echo "Error updating item.";
        }
    }
} else {
    echo "No item ID provided.";
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Trending Item</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../css/market.css">
</head>
<body>
<header>
    <img src="../../img/lnwlogo.png" alt="Logo" width="100">

    <nav>
        <a href="../home.php">หน้าแรก</a>
        <a href="../market.php">ตลาด</a>
        <a href="../topup.php">เติมเงิน</a>
        <a href="accept_topup.php">คำขอเติมเงิน</a>
        <a href="manage_games_items.php">จัดการ Games และ items</a>
        <a href="trending_list.php">Trending list</a>
    </nav>
    <div class="user-info">
    <img src="../../img/icon.png" alt="User Icon" width="30">
    <?php if ($username): ?>
        <span>ยินดีต้อนรับ, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
        <span>ยอดเงินของคุณ: ฿<?php echo number_format($walletBalance, 2); ?></span>
        <a href="../service/logout.php" class="btn btn-danger" style="margin-left: 10px;">Logout</a>
    <?php else: ?>
        <a href="./authentication/login.php" class="btn btn-success" style="margin-left: 10px;">Login</a>
    <?php endif; ?>
</div>
  </header>
<div class="container">
    <h1 class="mt-5">Edit Trending Item</h1>
    <form action="" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="name">Item Name:</label>
            <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($item['name']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="image">Item Image:</label>
            <?php if ($item['image']): ?>
                <img src="data:image/jpeg;base64,<?php echo base64_encode($item['image']); ?>" alt="Item Image" style="width: 150px;">
            <?php endif; ?>
            <input type="file" class="form-control" name="image">
        </div>

        <button type="submit" class="btn btn-primary">Update Item</button>
    </form>
</div>

</body>
</html>
