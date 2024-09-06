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
// Fetch the item to be edited
if (isset($_GET['id'])) {
    $item_id = $_GET['id'];
    $sql = "SELECT * FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();

    if (!$item) {
        echo "Item not found!";
        exit();
    }

    // Convert the image to base64
    if (isset($item['image'])) {
        $item['image_base64'] = base64_encode($item['image']);
    }

    $stmt->close();
}

// Fetch all games for the dropdown
$games = [];
$sql = "SELECT * FROM games";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $games[] = $row;
    }
}

// Handle form submission for editing the item
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $game_id = $_POST['game_id'];
    $image = $item['image']; // Default to current image

    // Handle image upload if a file is uploaded
    if (isset($_FILES['image']['tmp_name']) && $_FILES['image']['tmp_name'] != '') {
        $image = file_get_contents($_FILES['image']['tmp_name']); // Get the image content as binary
    }

    // Update item in the database
    $sql = "UPDATE products SET name = ?, description = ?, price = ?, image = ?, game_id = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdsii", $name, $description, $price, $image, $game_id, $item_id);

    if ($stmt->execute()) {
        header("Location: manage_games_items.php");
        exit();
    } else {
        echo "Error updating item: " . $stmt->error;
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
    <title>Edit Item</title>
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
        <h2 class="mt-5">Edit Item</h2>
        <form method="POST" action="edit_item.php?id=<?php echo $item['id']; ?>" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Item Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo $item['name']; ?>" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3" required><?php echo $item['description']; ?></textarea>
            </div>
            <div class="form-group">
                <label for="price">Price</label>
                <input type="number" step="1" class="form-control" id="price" name="price" value="<?php echo $item['price']; ?>" required>
            </div>
            <div class="form-group">
                <label for="image">Current Image</label><br>
                <?php if (!empty($item['image_base64'])): ?>
                    <img src="data:image/jpeg;base64,<?php echo $item['image_base64']; ?>" alt="Item Image" style="width: 150px;">
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="image">Upload New Image</label>
                <input type="file" class="form-control" id="image" name="image">
            </div>
            <div class="form-group">
                <label for="game_id">Game</label>
                <select class="form-control" id="game_id" name="game_id" required>
                    <?php foreach ($games as $game): ?>
                        <option value="<?php echo $game['id']; ?>" <?php if ($game['id'] == $item['game_id']) echo 'selected'; ?>>
                            <?php echo $game['name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
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
