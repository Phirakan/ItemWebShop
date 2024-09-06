<?php
session_start();
include '../../config/connect.php';

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

// Fetch games
$games = [];
$sql = "SELECT * FROM games";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $games[] = $row;
    }
}

// Fetch items
$items = [];
$sql = "SELECT i.*, g.name as game_name FROM products i JOIN games g ON i.game_id = g.id";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Convert image blob to base64
        if ($row['image']) {
            $row['image_base64'] = base64_encode($row['image']);
        } else {
            $row['image_base64'] = null;
        }
        $items[] = $row;
    }
}

// Delete game
if (isset($_GET['delete_game'])) {
    $game_id = $_GET['delete_game'];
    $sql = "DELETE FROM games WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $game_id);

    if ($stmt->execute()) {
        header("Location: manage_games_items.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Delete item
if (isset($_GET['delete_item'])) {
    $item_id = $_GET['delete_item'];
    $sql = "DELETE FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $item_id);

    if ($stmt->execute()) {
        header("Location: manage_games_items.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Games and Items</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../css/market.css">
    <style>
        img {
            max-width: 150px;
            height: auto;
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
    <div class="container mt-5">
        <h2>Manage Games</h2>
        <a href="add_game.php" class="btn btn-primary mb-3">Add New Game</a>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Game Name</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($games as $game): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($game['id']); ?></td>
                        <td><?php echo htmlspecialchars($game['name']); ?></td>
                        <td><?php echo htmlspecialchars($game['description']); ?></td>
                        <td>
                            <a href="edit_game.php?id=<?php echo htmlspecialchars($game['id']); ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="manage_games_items.php?delete_game=<?php echo htmlspecialchars($game['id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this game?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2 class="mt-5">Manage Items</h2>
        <a href="add_item.php" class="btn btn-primary mb-3">Add New Item</a>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Item Name</th>
                    <th>Game</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Image</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['id']); ?></td>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td><?php echo htmlspecialchars($item['game_name']); ?></td>
                        <td><?php echo htmlspecialchars($item['description']); ?></td>
                        <td><?php echo htmlspecialchars($item['price']); ?></td>
                        <td>
                         <?php if (!empty($item['image_base64'])): ?>
                                <img src="data:image/jpeg;base64,<?php echo htmlspecialchars($item['image_base64']); ?>" alt="Item Image" style="width: 150px;">
                         <?php else: ?>
                             No Image
                         <?php endif; ?>
                        </td>
                        <td>
                            <a href="edit_item.php?id=<?php echo htmlspecialchars($item['id']); ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="manage_games_items.php?delete_item=<?php echo htmlspecialchars($item['id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this item?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="admin_page.php" class="btn btn-danger mb-3">Back to Home</a>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
