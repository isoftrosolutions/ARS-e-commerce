<?php
// admin/category-action.php
require_once __DIR__ . '/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('categories.php');
}

$action = $_POST['action'] ?? '';

if ($action === 'create' || $action === 'update') {
    $name = $_POST['name'];
    $slug = !empty($_POST['slug']) ? strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $_POST['slug']))) : strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    $id = isset($_POST['id']) ? (int)$_POST['id'] : null;

    if ($action === 'create') {
        $stmt = $pdo->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
        if ($stmt->execute([$name, $slug])) {
            redirect('categories.php', 'Category created successfully.');
        } else {
            redirect('categories.php', 'Failed to create category.', 'danger');
        }
    } else {
        $stmt = $pdo->prepare("UPDATE categories SET name = ?, slug = ? WHERE id = ?");
        if ($stmt->execute([$name, $slug, $id])) {
            redirect('categories.php', 'Category updated successfully.');
        } else {
            redirect('categories.php', 'Failed to update category.', 'danger');
        }
    }
}

if ($action === 'delete') {
    $id = (int)$_POST['id'];
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    if ($stmt->execute([$id])) {
        redirect('categories.php', 'Category deleted successfully.');
    } else {
        redirect('categories.php', 'Failed to delete category.', 'danger');
    }
}
