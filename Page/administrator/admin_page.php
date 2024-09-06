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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../css/market.css">
    <style>
        .admin-option {
            margin: 20px 0;
        }

        .admin-option a {
            display: block;
            padding: 15px;
            background-color: #007bff;
            color: white;
            text-align: center;
            border-radius: 5px;
            text-decoration: none;
            font-size: 18px;
        }

        .admin-option a:hover {
            background-color: #0056b3;
        }

        .container {
            margin-top: 50px;
        }

        h1 {
            margin-bottom: 30px;
        }
    </style>
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
    <h1 class="text-center">Admin Dashboard</h1>
    
    <div class="row">
        <!-- Option to accept top-up requests -->
        <div class="col-md-4 admin-option">
            <a href="accept_topup.php">Accept Top-up Requests</a>
        </div>

        <!-- Option to manage games and items -->
        <div class="col-md-4 admin-option">
            <a href="manage_games_items.php">Manage Games & Items</a>
        </div>

        <!-- Option to manage trending items -->
        <div class="col-md-4 admin-option">
            <a href="trending_list.php">Manage Trending Items</a>
        </div>
    </div>
    
    <!-- Logout button -->
    <div class="row">
        <div class="col-md-12 text-center ">
            <a href="../home.php"  class="btn btn-danger">Back to home</a>
        </div>
    </div>
</div>

</body>
</html>
