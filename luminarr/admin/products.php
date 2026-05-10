<?php 
require_once '../api/config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$db = getDB();

// Handle Product Deletion
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: products.php?msg=Product deleted');
    exit;
}

// Handle Form Submission (Add/Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $name = sanitize($_POST['name']);
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $category_id = (int)$_POST['category_id'];
    $description = $_POST['description'];
    $featured = isset($_POST['featured']) ? 1 : 0;
    $slug = generateSlug($name);
    
    $image_name = $_POST['existing_image'] ?? '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image_name = time() . '_' . $slug . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], UPLOAD_PATH . $image_name);
    }

    if ($id) {
        $stmt = $db->prepare("UPDATE products SET name=?, slug=?, price=?, stock=?, category_id=?, description=?, image=?, featured=? WHERE id=?");
        $stmt->execute([$name, $slug, $price, $stock, $category_id, $description, $image_name, $featured, $id]);
    } else {
        $stmt = $db->prepare("INSERT INTO products (name, slug, price, stock, category_id, description, image, featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $slug, $price, $stock, $category_id, $description, $image_name, $featured]);
    }
    header('Location: products.php?msg=Product saved');
    exit;
}

$products = $db->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC")->fetchAll();
$categories = $db->query("SELECT * FROM categories")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management — LUMINARR</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .modal { display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); }
        .modal-content { background: var(--bg-secondary); margin: 5% auto; padding: 40px; border: 1px solid var(--border); width: 600px; border-radius: 8px; }
        .action-btn { padding: 5px 10px; border-radius: 4px; font-size: 0.8rem; }
        .btn-edit { background: var(--accent); color: #000; }
        .btn-delete { background: var(--error); color: #fff; }
    </style>
</head>
<body class="admin-layout">
    <aside class="admin-sidebar">
        <div style="padding: 0 30px 40px;">
            <a href="../index.php" class="logo" style="font-size: 1.5rem;">LUMINARR</a>
        </div>
        <nav>
            <a href="index.php" class="sidebar-link"><i class="fas fa-chart-line"></i> Dashboard</a>
            <a href="products.php" class="sidebar-link active"><i class="fas fa-tshirt"></i> Products</a>
            <a href="categories.php" class="sidebar-link"><i class="fas fa-tags"></i> Categories</a>
            <a href="orders.php" class="sidebar-link"><i class="fas fa-shopping-cart"></i> Orders</a>
            <a href="users.php" class="sidebar-link"><i class="fas fa-users"></i> Customers</a>
            <a href="sales.php" class="sidebar-link"><i class="fas fa-dollar-sign"></i> Sales Report</a>
        </nav>
    </aside>

    <main class="admin-main">
        <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px;">
            <h1 class="brand-font">Product Management</h1>
            <button class="btn-primary" onclick="openModal()">Add New Product</button>
        </header>

        <section style="background: var(--bg-secondary); padding: 30px; border: 1px solid var(--border); border-radius: 8px;">
            <table id="productTable">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): ?>
                        <tr>
                            <td><img src="<?php echo UPLOAD_URL . $p['image']; ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;"></td>
                            <td><?php echo $p['name']; ?></td>
                            <td><?php echo $p['category_name'] ?: 'Uncategorized'; ?></td>
                            <td><?php echo formatPrice($p['price']); ?></td>
                            <td><?php echo $p['stock']; ?></td>
                            <td>
                                <button class="action-btn btn-edit" onclick='editProduct(<?php echo json_encode($p); ?>)'>Edit</button>
                                <a href="products.php?delete=<?php echo $p['id']; ?>" class="action-btn btn-delete" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>

    <!-- Add/Edit Modal -->
    <div id="productModal" class="modal">
        <div class="modal-content">
            <h2 class="brand-font" id="modalTitle">Add Product</h2>
            <form action="products.php" method="POST" enctype="multipart/form-data" style="margin-top: 20px;">
                <input type="hidden" name="id" id="prodId">
                <input type="hidden" name="existing_image" id="prodExistingImage">
                
                <div class="form-group">
                    <label>Product Name</label>
                    <input type="text" name="name" id="prodName" required>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label>Price ($)</label>
                        <input type="number" step="0.01" name="price" id="prodPrice" required>
                    </div>
                    <div class="form-group">
                        <label>Stock</label>
                        <input type="number" name="stock" id="prodStock" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Category</label>
                    <select name="category_id" id="prodCategory" style="width: 100%; padding: 12px; background: var(--bg-tertiary); color: white; border: 1px solid var(--border);">
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?php echo $c['id']; ?>"><?php echo $c['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" id="prodDesc" rows="4" style="width: 100%; padding: 12px; background: var(--bg-tertiary); color: white; border: 1px solid var(--border);"></textarea>
                </div>

                <div class="form-group">
                    <label>Product Image</label>
                    <input type="file" name="image">
                </div>

                <div class="form-group">
                    <label><input type="checkbox" name="featured" id="prodFeatured"> Featured Product</label>
                </div>

                <div style="display: flex; gap: 15px; margin-top: 30px;">
                    <button type="submit" class="btn-primary">Save Product</button>
                    <button type="button" class="btn-primary" onclick="closeModal()" style="background: #333; color: #fff;">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('productModal');
        function openModal() {
            document.getElementById('modalTitle').innerText = 'Add Product';
            document.getElementById('prodId').value = '';
            document.getElementById('prodExistingImage').value = '';
            document.getElementById('prodName').value = '';
            document.getElementById('prodPrice').value = '';
            document.getElementById('prodStock').value = '';
            document.getElementById('prodCategory').value = '';
            document.getElementById('prodDesc').value = '';
            document.getElementById('prodFeatured').checked = false;
            modal.style.display = 'block';
        }
        function closeModal() { modal.style.display = 'none'; }
        function editProduct(p) {
            document.getElementById('modalTitle').innerText = 'Edit Product';
            document.getElementById('prodId').value = p.id;
            document.getElementById('prodExistingImage').value = p.image;
            document.getElementById('prodName').value = p.name;
            document.getElementById('prodPrice').value = p.price;
            document.getElementById('prodStock').value = p.stock;
            document.getElementById('prodCategory').value = p.category_id;
            document.getElementById('prodDesc').value = p.description;
            document.getElementById('prodFeatured').checked = p.featured == 1;
            modal.style.display = 'block';
        }
    </script>
</body>
</html>
