<?php 
session_start();
include('../config/connect.php');

$error = array();

if (isset($_POST['login_user'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    if (empty($username)) {
        array_push($error, "Username is required");
    }
    if (empty($password)) {
        array_push($error, "Password is required");
    }

    if (count($error) == 0) {
        $password = md5($password);
        $query = "SELECT * FROM user WHERE username = '$username' AND password = '$password'";
        $result = mysqli_query($conn, $query);
        
        if (mysqli_num_rows($result) == 1) {
            // Fetch user data
            $user = mysqli_fetch_assoc($result);
            $_SESSION['username'] = $username;
            $_SESSION['walletbalance'] = $user['walletbalance']; 
            $_SESSION['role'] = $user['role']; 
            $_SESSION['success'] = "Login successful!";

            
            if ($user['role'] == 1) {
                header("Location: ../Page/administrator/admin_page.php");
            } else {
                header("Location: ../Page/market.php");
            }
            exit();
        } else {
            array_push($error, "Invalid username or password");
            $_SESSION['error'] = "Invalid username or password";
            header("Location: ../Page/authentication/Login.php");
        }
    }
}
?>
