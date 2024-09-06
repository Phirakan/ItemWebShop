<?php
session_start();
include '../../config/connect.php';

$message = '';

if (!isset($_SESSION['username'])) {
    $_SESSION['error'] = "You must log in first";
    header('location: home.php');
    exit();
}
$walletBalance = isset($_SESSION['walletbalance']) ? $_SESSION['walletbalance'] : 0; 
$username = $_SESSION['username'];
$query = "SELECT role FROM user WHERE username = '$username' AND role = 1";
$result = mysqli_query($conn, $query);
if (mysqli_num_rows($result) == 0) {
    $_SESSION['error'] = "You don't have permission to access this page";
    header('location: ../home.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $gameName = $_POST['game_name'];
    $gameDescription = $_POST['game_description'];

    if (!empty($gameName) && !empty($gameDescription)) {
        $sql = "INSERT INTO games (name, description) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $gameName, $gameDescription);

        if ($stmt->execute()) {
            $message = "New game added successfully!";
        } else {
            $message = "Error: " . $conn->error;
        }

        $stmt->close();
    } else {
        $message = "Please fill in all fields.";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Game</title>
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
    <div class="container mt-5">
        <h2>Add New Game</h2>
        <?php if (!empty($message)): ?>
            <div class="alert alert-info">
                <?= $message ?>
            </div>
        <?php endif; ?>
        <form action="add_game.php" method="POST">
            <div class="form-group">
                <label for="game_name">Game Name</label>
                <input type="text" name="game_name" id="game_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="game_description">Game Description</label>
                <textarea name="game_description" id="game_description" class="form-control" rows="4" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Add Game</button>
            <a href="admin_page.php" class="btn btn-danger">Back</a>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
