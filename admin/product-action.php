<?php
// admin/product-action.php
require_once __DIR__ . '/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('products.php');
}

$action = $_POST['action'] ?? '';

if ($action === 'delete') {
    $id = (int)$_POST['id'];
    // Get image path to delete file
    $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $img = $stmt->fetchColumn();
    
    if ($img && file_exists(__DIR__ . '/../' . $img)) {
        unlink(__DIR__ . '/../' . $img);
    }
    
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    if ($stmt->execute([$id])) {
        redirect('products.php', 'Product deleted successfully.');
    } else {
        redirect('products.php', 'Failed to delete product.', 'danger');
    }
}

if ($action === 'create' || $action === 'update') {
    $name = $_POST['name'];
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    $description = $_POST['description'];
    $price = (float)$_POST['price'];
    $discount_price = !empty($_POST['discount_price']) ? (float)$_POST['discount_price'] : null;
    $category_id = (int)$_POST['category_id'];
    $stock = (int)$_POST['stock'];
    $sku = $_POST['sku'];
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $id = isset($_POST['id']) ? (int)$_POST['id'] : null;

    // Handle Image Upload
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $new_name = uniqid('prod_') . '.' . $ext;
            $dest = 'uploads/products/' . $new_name;
            
            if (!is_dir(__DIR__ . '/../uploads/products/')) {
                mkdir(__DIR__ . '/../uploads/products/', 0777, true);
            }
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../' . $dest)) {
                $image_path = $dest;
                
                // If update, delete old image
                if ($id) {
                    $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
                    $stmt->execute([$id]);
                    $old_img = $stmt->fetchColumn();
                    if ($old_img && file_exists(__DIR__ . '/../' . $old_img)) {
                        unlink(__DIR__ . '/../' . $old_img);
                    }
                }
            }
        }
    }

    if ($action === 'create') {
        $stmt = $pdo->prepare("INSERT INTO products (name, slug, description, price, discount_price, category_id, stock, sku, is_featured, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$name, $slug, $description, $price, $discount_price, $category_id, $stock, $sku, $is_featured, $image_path])) {
            redirect('products.php', 'Product created successfully.');
        } else {
            redirect('product-add.php', 'Failed to create product.', 'danger');
        }
    } else {
        // Update logic
        $sql = "UPDATE products SET name = ?, slug = ?, description = ?, price = ?, discount_price = ?, category_id = ?, stock = ?, sku = ?, is_featured = ?";
        $params = [$name, $slug, $description, $price, $discount_price, $category_id, $stock, $sku, $is_featured];
        
        if ($image_path) {
            $sql .= ", image = ?";
            $params[] = $image_path;
        } elseif (isset($_POST['remove_image']) && $_POST['remove_image'] == '1') {
             // Handle image removal if requested
             $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
             $stmt->execute([$id]);
             $old_img = $stmt->fetchColumn();
             if ($old_img && file_exists(__DIR__ . '/../' . $old_img)) {
                 unlink(__DIR__ . '/../' . $old_img);
             }
             $sql .= ", image = NULL";
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $id;
        
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute($params)) {
            redirect('products.php', 'Product updated successfully.');
        } else {
            redirect("product-edit.php?id=$id", 'Failed to update product.', 'danger');
        }
    }
}
