<?php
session_start();
include '../config/connect.php';

if (!isset($_SESSION['username'])) {
    $_SESSION['error'] = "You must log in first";
    header('location: home.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_SESSION['username'];
    $amount = $_POST['amount'];

    // Handle file upload
    $targetDir = "../slips/";  
    $targetFile = $targetDir . basename($_FILES["slip"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Check if image file is an actual image or fake image
    $check = getimagesize($_FILES["slip"]["tmp_name"]);
    if ($check !== false) {
        $uploadOk = 1;
    } else {
        $_SESSION['error'] = "File is not an image.";
        $uploadOk = 0;
    }

    // Check file size (limit to 5MB)
    if ($_FILES["slip"]["size"] > 5000000) {
        $_SESSION['error'] = "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
        $_SESSION['error'] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }

    if ($uploadOk == 0) {
        $_SESSION['error'] = "Sorry, your file was not uploaded.";
    } else {
        if (move_uploaded_file($_FILES["slip"]["tmp_name"], $targetFile)) {
            // Save request to the database
            $sql = "INSERT INTO credit_requests (username, amount, slip, status) VALUES (?, ?, ?, 'pending')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sds", $username, $amount, $targetFile);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Request submitted successfully!";
            } else {
                $_SESSION['error'] = "Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $_SESSION['error'] = "Sorry, there was an error uploading your file.";
        }
    }

    $conn->close();
    header('location: ../Page/topup.php');
    exit();
}
?>
