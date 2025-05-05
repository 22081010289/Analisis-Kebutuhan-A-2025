<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Handle file upload
function uploadImage($file) {
    $target_dir = "../uploads/products/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    // Check if image file is a actual image
    $check = getimagesize($file["tmp_name"]);
    if($check === false) {
        return false;
    }
    
    // Check file size (5MB max)
    if ($file["size"] > 5000000) {
        return false;
    }
    
    // Allow certain file formats
    if($file_extension != "jpg" && $file_extension != "png" && $file_extension != "jpeg" && $file_extension != "gif" ) {
        return false;
    }
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return "uploads/products/" . $new_filename;
    }
    return false;
}

// Handle adding new product
if (isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $category_id = $_POST['category_id'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $featured = isset($_POST['featured']) ? 1 : 0;
    
    // Upload image
    $image_url = uploadImage($_FILES['image']);
    if (!$image_url) {
        $_SESSION['error'] = "Failed to upload image. Please try again.";
        header("Location: products.php");
        exit();
    }
    
    $query = "INSERT INTO products (name, category_id, price, stock, image_url, featured) 
              VALUES (:name, :category_id, :price, :stock, :image_url, :featured)";
    $stmt = $db->prepare($query);
    
    $stmt->bindParam(":name", $name);
    $stmt->bindParam(":category_id", $category_id);
    $stmt->bindParam(":price", $price);
    $stmt->bindParam(":stock", $stock);
    $stmt->bindParam(":image_url", $image_url);
    $stmt->bindParam(":featured", $featured);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Product added successfully!";
    } else {
        $_SESSION['error'] = "Failed to add product. Please try again.";
    }
    
    header("Location: products.php");
    exit();
}

// Handle updating product
if (isset($_POST['update_product'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $category_id = $_POST['category_id'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $featured = isset($_POST['featured']) ? 1 : 0;
    
    // If new image is uploaded
    if ($_FILES['image']['size'] > 0) {
        $image_url = uploadImage($_FILES['image']);
        if (!$image_url) {
            $_SESSION['error'] = "Failed to upload image. Please try again.";
            header("Location: products.php");
            exit();
        }
        
        $query = "UPDATE products SET 
                  name = :name, 
                  category_id = :category_id, 
                  price = :price, 
                  stock = :stock, 
                  image_url = :image_url, 
                  featured = :featured 
                  WHERE id = :id";
    } else {
        $query = "UPDATE products SET 
                  name = :name, 
                  category_id = :category_id, 
                  price = :price, 
                  stock = :stock, 
                  featured = :featured 
                  WHERE id = :id";
    }
    
    $stmt = $db->prepare($query);
    
    $stmt->bindParam(":id", $id);
    $stmt->bindParam(":name", $name);
    $stmt->bindParam(":category_id", $category_id);
    $stmt->bindParam(":price", $price);
    $stmt->bindParam(":stock", $stock);
    $stmt->bindParam(":featured", $featured);
    
    if (isset($image_url)) {
        $stmt->bindParam(":image_url", $image_url);
    }
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Product updated successfully!";
    } else {
        $_SESSION['error'] = "Failed to update product. Please try again.";
    }
    
    header("Location: products.php");
    exit();
}
?> 