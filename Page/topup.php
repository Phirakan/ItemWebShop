<?php
session_start();
include '../config/connect.php';

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: ../Page/authentication/Login.php");
    exit();
}

// Handle the credit request form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_SESSION['username'];
    $amount = $_POST['amount'];
    $slip = $_FILES['slip']['name'];
    $target_dir = "../uploads/";
    $target_file = $target_dir . basename($slip);

    // Save the uploaded file
    if (move_uploaded_file($_FILES['slip']['tmp_name'], $target_file)) {
        // Insert the credit request into the database
        $sql = "INSERT INTO credit_requests (username, amount, slip) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sds", $username, $amount, $slip);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $_SESSION['topup_success'] = true; // Set success flag
            header("Location: topup.php");
            exit();
        } else {
            echo "Failed to submit credit request.";
        }
    } else {
        echo "Error uploading the file.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Top-Up Credit</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/market.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .container-fluid {
            margin-top: 20px;
        }
        .form-container {
            max-width: 600px;
            margin: auto;
            background-color: white;
            padding: 20px;
            border-radius: 10px;
        }
        .form-container img {
            display: block;
            margin: 20px auto;
        }
        .form-container h2 {
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
        <h2>Top-Up Credit</h2>
        <form action="topup.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="amount">Amount (฿):</label>
                <input type="number" name="amount" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="slip">Upload Slip:</label>
                <input type="file" name="slip" class="form-control-file" accept="image/*" required>
            </div>
            <input type="submit" value="Submit" class="btn btn-primary btn-block">
            <img src="../img/qrcode.png" alt="QR Code for Payment" width="200">
            <p class="text-center">Scan this QR code to make a payment, then upload your payment slip below.</p>
        </form>
    </div>
</div>
<!-- success popup -->
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="successModalLabel">Top-Up Successful</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <i class="fas fa-check-circle success-icon"></i> <!-- Green check mark icon -->
                <p>Your credit request has been successfully submitted!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script> <!-- Font Awesome JS -->

<script>
    $(document).ready(function() {
        <?php if (isset($_SESSION['topup_success'])): ?>

            $('#successModal').modal('show');
            <?php unset($_SESSION['topup_success']); ?>
        <?php endif; ?>
    });
</script>
</body>
</html>
