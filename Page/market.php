<?php
session_start();
include '../config/connect.php';

if (!isset($_SESSION['username'])) {
    $_SESSION['error'] = "You must log in first";
    header('location: home.php');
    exit();
}

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['username']);

// Initialize wallet balance
$walletBalance = isset($_SESSION['walletbalance']) ? $_SESSION['walletbalance'] : 0; 

// Fetch trending items
$trendingItems = [];
$sql = "SELECT * FROM trending_items";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        if ($row['image']) {
            $row['image_base64'] = base64_encode($row['image']);
        }
        $trendingItems[] = $row;
    }
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

// Fetch selected game's products
$selectedGameId = isset($_GET['game_id']) ? intval($_GET['game_id']) : null;
$products = [];
if ($selectedGameId) {
    $sql = "SELECT * FROM products WHERE game_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $selectedGameId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            if ($row['image']) {
                $row['image_base64'] = base64_encode($row['image']);
            }
            $products[] = $row;
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Market</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/market.css">
    <style>
        .cart-icon {
            position: relative;
            display: inline-block;
        }
        .cart-icon .badge {
            position: absolute;
            top: -10px;
            right: -10px;
            background-color: red;
            color: white;
            border-radius: 50%;
            padding: 5px 10px;
            font-size: 12px;
        }
          footer {
         background-color: white; 
         color: #8BA4DD; 
         padding: 10px;
         position: static; 
         width: 100%;
         bottom: 0;
         margin-top: auto; 
        }

        .contact-info span {
            font-weight: bold;
            color: #8BA4DD; 
        }

        footer p {
            margin: 0; 
        }
    </style>
</head>
<body>

  <!-- Start navbar -->
  <header>
    <img src="../img/lnwlogo.png" alt="Logo" width="100">

    <nav>
        <a href="home.php">หน้าแรก</a>
        <a href="market.php">ตลาด</a>
        <a href="topup.php">เติมเงิน</a>
        <div class="cart-icon">
            <a href="cart.php">
                <img src="../img/cart_icon.png" alt="Cart Icon" width="30">
                <span class="badge"><?php echo count($_SESSION['cart'] ?? []); ?></span>
            </a>
        </div>
    </nav>
    <div class="user-info">
        <img src="../img/icon.png" alt="User Icon" width="30">
        <?php if ($isLoggedIn): ?>
            <span>ยินดีต้อนรับ, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <span>ยอดเงินของคุณ: ฿<?php echo number_format($walletBalance, 2); ?></span>
            <?php if ($_SESSION['role'] == 1): ?>
                
                <a href="./administrator/admin_page.php" class="btn btn-info" style="margin-left: 10px;">Admin Page</a>
            <?php endif; ?>
            <a href="../service/logout.php" class="btn btn-danger" style="margin-left: 10px;">Logout</a>
        <?php else: ?>
            <a href="./authentication/login.php" class="btn btn-success" style="margin-left: 10px;">Login</a>
        <?php endif; ?>
    </div>
</header>

  <!-- End of navbar -->

    <div class="container-fluid">
        <div class="row">
            <!-- Left Sidebar -->
            <div class="col-md-2 sidebar">
                <h3>กำลังมาแรง <span>🔥</span></h3>
                <div class="hot-items">
                    <?php
                    foreach ($trendingItems as $item) {
                        echo '<div class="card mb-2">';
                        if (!empty($item['image_base64'])) {
                            echo '<img src="data:image/jpeg;base64,' . htmlspecialchars($item['image_base64']) . '" alt="Item Image" style="width: 150px;">';
                        }
                        echo '<div class="card-body">';
                        echo '<p class="card-text">' . htmlspecialchars($item['name']) . '</p>';
                        echo '</div>';
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>

            <!-- Main Content -->
            <div class="container-fluid col-md-8">
                <div class="game-selector d-flex justify-content-between align-items-center">
                    <h4>เลือกเกมของคุณ</h4>
                    <form method="GET" action="market.php">
                        <select class="form-control" name="game_id" style="width: 200px;">
                            <option value="">--กรุณาเลือกเกม--</option>
                            <!-- Populate games dynamically -->
                            <?php
                            foreach ($games as $game) {
                                $selected = ($selectedGameId == $game['id']) ? 'selected' : '';
                                echo '<option value="' . $game['id'] . '" ' . $selected . '>' . htmlspecialchars($game['name']) . '</option>';
                            }
                            ?>
                        </select>
                        <button type="submit" class="btn btn-success">ตกลง</button>
                    </form>
                </div>

                <div class="row">
                    <?php
                    if (!empty($products)) {
                        foreach ($products as $product) {
                            echo '<div class="col-md-4 mb-4">';
                            echo '<div class="card">';
                            if (!empty($product['image_base64'])) {
                                echo '<img src="data:image/jpeg;base64,' . htmlspecialchars($product['image_base64']) . '" alt="Product Image" style="width: 150px;">';
                            }
                            
                            echo '<div class="card-body">';
                            echo '<h5 class="card-title">' . htmlspecialchars($product['name']) . '</h5>';
                            echo '<p class="card-text">฿' . number_format($product['price'], 2) . '</p>';
                            echo '<button class="btn btn-warning add-to-cart" data-product-id="' . htmlspecialchars($product['id']) . '">Add to cart</button>';
                            echo '<a href="ViewProduct.php?product_id=' . htmlspecialchars($product['id']) . '" class="btn btn-primary">Detail</a>';
                            echo '</div>';
                            echo '</div>';
                            echo '</div>';
                        }
                    } else {
                        echo '<div class="col-12">';
                        echo '<p>No products available for the selected game.</p>';
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>

            <!-- Right Sidebar -->
            <div class="col-md-2 sidebar">
                <h3>กำลังมาแรง <span>🔥</span></h3>
                <div class="hot-items">
                    <?php
                    foreach ($trendingItems as $item) {
                        echo '<div class="card mb-2">';
                        if (!empty($item['image_base64'])) {
                            echo '<img src="data:image/jpeg;base64,' . htmlspecialchars($item['image_base64']) . '" alt="Item Image" style="width: 150px;">';
                        }
                        echo '<div class="card-body">';
                        echo '<p class="card-text">' . htmlspecialchars($item['name']) . '</p>';
                        echo '</div>';
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
      <!-- footer -->
  <footer class="text-center mt-auto">
    <div class="contact-info">
        <p>Contact us at: <span>info@smarthometech.com</span></p>
    </div>
    
</footer>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- Popup -->
<div id="cart-popup" class="modal" style="display:none; position:fixed; z-index:9999; background:rgba(0,0,0,0.5); top:0; left:0; right:0; bottom:0;">
    <div class="modal-content" style="background:#fff; margin:auto; padding:20px; width:300px; text-align:center;">
        <p>เพิ่มสินค้าลงในตะกร้าเรียบร้อยแล้ว!</p>
        <button id="close-popup" class="btn btn-success">ปิด</button>
    </div>
</div>

<script>
$(document).ready(function() {
    // เมื่อคลิกปุ่ม Add to cart
    $('.add-to-cart').on('click', function() {
        var productId = $(this).data('product-id'); // ดึง product_id
        
        $.ajax({
            url: 'cart.php',
            type: 'GET',
            data: { action: 'add', product_id: productId },
            success: function(response) {
                // แสดง popup เมื่อเพิ่มสินค้าลงตะกร้าเรียบร้อยแล้ว
                $('#cart-popup').fadeIn();
                // อัปเดตจำนวนสินค้าตะกร้า
                var cartItemCount = parseInt($('.cart-icon .badge').text()) || 0;
                $('.cart-icon .badge').text(cartItemCount + 1);
            }
        });
    });
    
    // เมื่อคลิกปุ่มปิด popup
    $('#close-popup').on('click', function() {
        $('#cart-popup').fadeOut();
    });
});
</script>
</body>
</html>
