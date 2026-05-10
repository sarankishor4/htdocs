<?php 
require_once '../api/config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$db = getDB();

// Handle Category Deletion
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $db->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: categories.php?msg=Category deleted');
    exit;
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $slug = generateSlug($name);

    if ($id) {
        $stmt = $db->prepare("UPDATE categories SET name=?, slug=?, description=? WHERE id=?");
        $stmt->execute([$name, $slug, $description, $id]);
    } else {
        $stmt = $db->prepare("INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)");
        $stmt->execute([$name, $slug, $description]);
    }
    header('Location: categories.php?msg=Category saved');
    exit;
}

$categories = $db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category Management — LUMINARR</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .modal { display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); }
        .modal-content { background: var(--bg-secondary); margin: 10% auto; padding: 40px; border: 1px solid var(--border); width: 500px; border-radius: 8px; }
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
            <a href="products.php" class="sidebar-link"><i class="fas fa-tshirt"></i> Products</a>
            <a href="categories.php" class="sidebar-link active"><i class="fas fa-tags"></i> Categories</a>
            <a href="orders.php" class="sidebar-link"><i class="fas fa-shopping-cart"></i> Orders</a>
            <a href="users.php" class="sidebar-link"><i class="fas fa-users"></i> Customers</a>
        </nav>
    </aside>

    <main class="admin-main">
        <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px;">
            <h1 class="brand-font">Category Management</h1>
            <button class="btn-primary" onclick="openModal()">Add New Category</button>
        </header>

        <section style="background: var(--bg-secondary); padding: 30px; border: 1px solid var(--border); border-radius: 8px;">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $c): ?>
                        <tr>
                            <td>#<?php echo $c['id']; ?></td>
                            <td><?php echo $c['name']; ?></td>
                            <td><?php echo $c['slug']; ?></td>
                            <td><?php echo $c['description']; ?></td>
                            <td>
                                <button class="action-btn btn-edit" onclick='editCategory(<?php echo json_encode($c); ?>)'>Edit</button>
                                <a href="categories.php?delete=<?php echo $c['id']; ?>" class="action-btn btn-delete" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>

    <div id="catModal" class="modal">
        <div class="modal-content">
            <h2 class="brand-font" id="modalTitle">Add Category</h2>
            <form action="categories.php" method="POST" style="margin-top: 20px;">
                <input type="hidden" name="id" id="catId">
                <div class="form-group">
                    <label>Category Name</label>
                    <input type="text" name="name" id="catName" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" id="catDesc" rows="3" style="width: 100%; padding: 12px; background: var(--bg-tertiary); color: white; border: 1px solid var(--border);"></textarea>
                </div>
                <div style="display: flex; gap: 15px; margin-top: 30px;">
                    <button type="submit" class="btn-primary">Save Category</button>
                    <button type="button" class="btn-primary" onclick="closeModal()" style="background: #333; color: #fff;">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('catModal');
        function openModal() {
            document.getElementById('modalTitle').innerText = 'Add Category';
            document.getElementById('catId').value = '';
            document.getElementById('catName').value = '';
            document.getElementById('catDesc').value = '';
            modal.style.display = 'block';
        }
        function closeModal() { modal.style.display = 'none'; }
        function editCategory(c) {
            document.getElementById('modalTitle').innerText = 'Edit Category';
            document.getElementById('catId').value = c.id;
            document.getElementById('catName').value = c.name;
            document.getElementById('catDesc').value = c.description;
            modal.style.display = 'block';
        }
    </script>
</body>
</html>
