<?php
session_start();
include '../config/connect.php';

// ตรวจสอบว่าผู้ใช้ได้ล็อกอินหรือไม่
if (!isset($_SESSION['username'])) {
    header("Location: ../Page/authentication/Login.php");
    exit();
}

// ตรวจสอบการลบสินค้า
if (isset($_GET['action']) && $_GET['action'] == "remove" && isset($_GET['product_id'])) {
    $productId = intval($_GET['product_id']);
    
    if (isset($_SESSION['cart'][$productId])) {
        unset($_SESSION['cart'][$productId]);
    }
    
    echo "success";
    exit();
}

// ตรวจสอบการเพิ่มสินค้า
if (isset($_GET['action']) && $_GET['action'] == "add" && isset($_GET['product_id'])) {
    $productId = intval($_GET['product_id']);
    
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }
    
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]['quantity']++;
    } else {
        $sql = "SELECT * FROM products WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $product = $result->fetch_assoc();
            $_SESSION['cart'][$productId] = array(
                "name" => $product['name'],
                "price" => $product['price'],
                "quantity" => 1
            );
        }
    }
    
    echo "success";
    exit();
}

// ตรวจสอบการชำระเงิน
if (isset($_POST['checkout'])) {
    $total = 0;

    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $id => $product) {
            $subtotal = $product['price'] * $product['quantity'];
            $total += $subtotal;
        }
    }

    $username = $_SESSION['username'];
    $sql = "SELECT walletbalance FROM user WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if ($user) {
        $walletbalance = $user['walletbalance'];

        if ($walletbalance >= $total) {
            $new_balance = $walletbalance - $total;
            $sql_update = "UPDATE user SET walletbalance = ? WHERE username = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("ds", $new_balance, $username);
            $stmt_update->execute();

            foreach ($_SESSION['cart'] as $productId => $product) {
                $sql_delete_product = "DELETE FROM products WHERE id = ?";
                $stmt_delete_product = $conn->prepare($sql_delete_product);
                $stmt_delete_product->bind_param("i", $productId);
                $stmt_delete_product->execute();
            }

            $_SESSION['walletbalance'] = $new_balance;
            unset($_SESSION['cart']);
            
            echo "success"; // สำเร็จ
        } else {
            echo "insufficient_funds";
        }
    } else {
        echo "user_not_found";
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตะกร้าสินค้า</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/market.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .container-fluid {
            margin-top: 20px;
        }
        .form-container {
            max-width: 800px;
            margin: auto;
            background-color: white;
            padding: 20px;
            border-radius: 10px;
        }
        .form-container table {
            width: 100%;
        }
        .form-container h2, .form-container h3 {
            text-align: center;
            margin-bottom: 20px;
        }
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
            position: absolute;
            bottom: 0;
            width: 100%;
        }
        .contact-info span {
            font-weight: bold;
            color: #8BA4DD; 
        }
        .success-icon {
            color: #28a745; /* Green color */
            font-size: 50px;
            display: block;
            margin: 0 auto 20px;
            text-align: center;
        }
        footer p {
            margin: 0; 
        }
    </style>
</head>
<body>

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
        <?php if (isset($_SESSION['username'])): ?>
            <span>ยินดีต้อนรับ, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <span>ยอดเงินของคุณ: ฿<?php echo number_format($_SESSION['walletbalance'], 2); ?></span>
            <a href="../service/logout.php" class="btn btn-danger" style="margin-left: 10px;">Logout</a>
        <?php else: ?>
            <a href="./authentication/login.php" class="btn btn-success" style="margin-left: 10px;">Login</a>
        <?php endif; ?>
    </div>
</header>

<div class="container-fluid">
    <div class="form-container">
        <h2>ตะกร้าสินค้าของคุณ</h2>
        <p>ยอดเงินคงเหลือในกระเป๋า: ฿<?php echo number_format($_SESSION['walletbalance'], 2); ?></p>
        <table class="table">
            <thead>
                <tr>
                    <th>ชื่อสินค้า</th>
                    <th>ราคา</th>
                    <th>จำนวน</th>
                    <th>ราคารวม</th>
                    <th>จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total = 0;
                if (!empty($_SESSION['cart'])) {
                    foreach ($_SESSION['cart'] as $id => $product) {
                        $subtotal = $product['price'] * $product['quantity'];
                        $total += $subtotal;
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($product['name']) . '</td>';
                        echo '<td>฿' . number_format($product['price'], 2) . '</td>';
                        echo '<td>' . $product['quantity'] . '</td>';
                        echo '<td>฿' . number_format($subtotal, 2) . '</td>';
                        echo '<td><button class="btn btn-danger remove-btn" data-id="' . $id . '">ลบ</button></td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="5">ไม่มีสินค้าในตะกร้า</td></tr>';
                }
                ?>
            </tbody>
        </table>

        <h3>ยอดรวมทั้งหมด: ฿<?php echo number_format($total, 2); ?></h3>

        <a href="market.php" class="btn btn-primary">เลือกซื้อสินค้าเพิ่มเติม</a>
        <button id="checkout-btn" class="btn btn-success">ชำระเงิน</button>
    </div>
</div>

<!-- success popup -->
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="successModalLabel">ชำระเงินสำเร็จ</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <i class="fas fa-check-circle success-icon"></i>
                <p>การชำระเงินของคุณเสร็จสมบูรณ์แล้ว!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
            </div>
        </div>
    </div>
</div>

<footer class="text-center mt-auto">
    <div class="contact-info">
        <p>Contact us at: <span>info@smarthometech.com</span></p>
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>

<script>
    $(document).ready(function() {
        $('.remove-btn').click(function(e) {
            e.preventDefault();
            var button = $(this);
            var productId = button.data('id');
            
            if (confirm('คุณแน่ใจหรือไม่ว่าต้องการลบสินค้านี้ออกจากตะกร้า?')) {
                $.ajax({
                    url: 'cart.php',
                    type: 'GET',
                    data: { action: 'remove', product_id: productId },
                    success: function(response) {
                        if (response.trim() === 'success') {
                            button.closest('tr').remove();
                            alert('ลบสินค้าสำเร็จ');
                            location.reload(); // Reload the page to update the total
                        } else {
                            alert('เกิดข้อผิดพลาดในการลบสินค้า');
                        }
                    },
                    error: function() {
                        alert('เกิดข้อผิดพลาดในการลบสินค้า');
                    }
                });
            }
        });

        $('#checkout-btn').click(function(e) {
            e.preventDefault();
            $.ajax({
                url: 'cart.php',
                type: 'POST',
                data: { checkout: true },
                success: function(response) {
                    if (response.trim() === 'success') {
                        $('#successModal').modal('show');
                        setTimeout(function() {
                            window.location.reload();
                        }, 2000);
                    } else if (response.trim() === 'insufficient_funds') {
                        alert('ยอดเงินไม่เพียงพอ กรุณาเติมเงิน');
                    } else if (response.trim() === 'user_not_found') {
                        alert('ไม่พบข้อมูลผู้ใช้');
                    } else {
                        alert('เกิดข้อผิดพลาดในการชำระเงิน');
                    }
                },
                error: function() {
                    alert('เกิดข้อผิดพลาดในการชำระเงิน');
                }
            });
        });

        <?php if (isset($_SESSION['checkout_success'])): ?>
            $('#successModal').modal('show');
            <?php unset($_SESSION['checkout_success']); ?>
        <?php endif; ?>
    });
    </script>
</body>
</html>
