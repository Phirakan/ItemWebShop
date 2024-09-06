<?php
session_start();
include '../../config/connect.php';

$username = $_SESSION['username'];
$query = "SELECT role FROM user WHERE username = '$username' AND role = 1";
$result = mysqli_query($conn, $query);
if (mysqli_num_rows($result) == 0) {
    $_SESSION['error'] = "You don't have permission to access this page";
    header('location: ../home.php');
    exit();
}
$walletBalance = isset($_SESSION['walletbalance']) ? $_SESSION['walletbalance'] : 0; 
// Fetch the game to be edited
if (isset($_GET['id'])) {
    $game_id = $_GET['id'];
    $sql = "SELECT * FROM games WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $game_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $game = $result->fetch_assoc();
    
    if (!$game) {
        echo "Game not found!";
        exit();
    }

    $stmt->close();
}

// Handle form submission for editing the game
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $game_name = $_POST['name'];
    $description = $_POST['description'];

    // Update game in the database
    $sql = "UPDATE games SET name = ?, description = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $game_name, $description, $game_id);

    if ($stmt->execute()) {
        header("Location: manage_games_items.php");
        exit();
    } else {
        echo "Error updating game: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Game</title>
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
        <h2 class="mt-5">Edit Game</h2>
        <form method="POST" action="edit_game.php?id=<?php echo $game['id']; ?>">
            <div class="form-group">
                <label for="name">Game Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo $game['name']; ?>" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3" required><?php echo $game['description']; ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="manage_games_items.php" class="btn btn-danger">Cancel</a>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
