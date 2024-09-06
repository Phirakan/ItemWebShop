<?php
session_start();
include '../config/connect.php';

if (!isset($_SESSION['username'])) {
    $_SESSION['error'] = "You must log in first";
    header('location: home.php');
    exit();
}

if (isset($_GET['id'])) {
    $itemId = intval($_GET['id']);

    // ลบ item ออกจาก database
    $sql = "DELETE FROM trending_items WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $itemId);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Item deleted successfully!";
    } else {
        $_SESSION['error'] = "Error deleting item.";
    }
    
    header('Location: trending_list.php');
    exit();
}
