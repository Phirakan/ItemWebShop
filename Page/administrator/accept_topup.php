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

// Handle approval or rejection
if (isset($_POST['action'])) {
    $requestId = intval($_POST['request_id']);
    $action = $_POST['action'];

    // Fetch the credit request
    $sql = "SELECT * FROM credit_requests WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $requestId);
    $stmt->execute();
    $result = $stmt->get_result();
    $request = $result->fetch_assoc();

    if ($request) {
        $username = $request['username'];
        $amount = $request['amount'];

        if ($action === 'approve') {
            // Update user's wallet balance
            $sql = "UPDATE user SET walletbalance = walletbalance + ? WHERE username = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ds", $amount, $username);
            $stmt->execute();

            // Update the request status
            $sql = "UPDATE credit_requests SET status = 'approved' WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $requestId);
            $stmt->execute();
            echo "<div class='alert alert-success'>Request approved and wallet balance updated.</div>";
        } elseif ($action === 'reject') {
            // Update the request status to rejected
            $sql = "UPDATE credit_requests SET status = 'rejected' WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $requestId);
            $stmt->execute();
            echo "<div class='alert alert-danger'>Request rejected.</div>";
        }
    }
}

// Fetch all pending credit requests
$sql = "SELECT * FROM credit_requests WHERE status = 'pending'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Credit Requests</title>
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
    <h2 class="mb-4">Pending Credit Requests</h2>

    <?php if ($result->num_rows > 0): ?>
        <table class="table table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>Username</th>
                    <th>Amount (฿)</th>
                    <th>Slip</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td>฿<?php echo number_format($row['amount'], 2); ?></td>
                        <td>
                            <a href="../../uploads/<?php echo htmlspecialchars($row['slip']); ?>" target="_blank">
                                <img src="../../uploads/<?php echo htmlspecialchars($row['slip']); ?>" width="100" class="img-thumbnail">
                            </a>
                        </td>
                        <td>
                            <form action="accept_topup.php" method="post">
                                <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">Approve</button>
                                <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm">Reject</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">No pending credit requests found.</div>
    <?php endif; ?>
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
