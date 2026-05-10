<?php 
require_once '../api/config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$db = getDB();

// Handle User Deletion
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Prevent admin from deleting themselves
    if ($id === $_SESSION['user_id']) {
        header('Location: users.php?error=You cannot delete your own account.');
        exit;
    }
    
    $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: users.php?msg=User deleted');
    exit;
}

// Handle Form Submission (Edit Role)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id'];
    $role = $_POST['role'];
    
    // Prevent admin from changing their own role to 'user'
    if ($id === $_SESSION['user_id'] && $role === 'user') {
         header('Location: users.php?error=You cannot demote yourself.');
         exit;
    }

    $stmt = $db->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->execute([$role, $id]);
    header('Location: users.php?msg=User role updated');
    exit;
}

$users = $db->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Management — LUMINARR</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .modal { display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); }
        .modal-content { background: var(--bg-secondary); margin: 10% auto; padding: 40px; border: 1px solid var(--border); width: 400px; border-radius: 8px; }
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
            <a href="categories.php" class="sidebar-link"><i class="fas fa-tags"></i> Categories</a>
            <a href="orders.php" class="sidebar-link"><i class="fas fa-shopping-cart"></i> Orders</a>
            <a href="users.php" class="sidebar-link active"><i class="fas fa-users"></i> Customers</a>
            <a href="sales.php" class="sidebar-link"><i class="fas fa-chart-bar"></i> Sales Analytics</a>
        </nav>
    </aside>

    <main class="admin-main">
        <header style="margin-bottom: 40px;">
            <h1 class="brand-font">Customer Management</h1>
        </header>
        
        <?php if (isset($_GET['msg'])): ?>
            <div style="background: rgba(0, 230, 118, 0.1); border: 1px solid var(--success); color: var(--success); padding: 15px; margin-bottom: 20px; border-radius: 4px;">
                <?php echo sanitize($_GET['msg']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <div style="background: rgba(255, 77, 77, 0.1); border: 1px solid var(--error); color: var(--error); padding: 15px; margin-bottom: 20px; border-radius: 4px;">
                <?php echo sanitize($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <section style="background: var(--bg-secondary); padding: 30px; border: 1px solid var(--border); border-radius: 8px;">
            <table style="width: 100%;">
                <thead>
                    <tr>
                        <th style="text-align: left; padding: 10px;">ID</th>
                        <th style="text-align: left; padding: 10px;">Name</th>
                        <th style="text-align: left; padding: 10px;">Email</th>
                        <th style="text-align: left; padding: 10px;">Role</th>
                        <th style="text-align: left; padding: 10px;">Registered</th>
                        <th style="text-align: left; padding: 10px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td style="padding: 10px;">#<?php echo $u['id']; ?></td>
                            <td style="padding: 10px;">
                                <?php echo $u['name']; ?>
                                <?php if($u['id'] === $_SESSION['user_id']) echo ' <span style="font-size:0.7rem; color:var(--accent);">(You)</span>'; ?>
                            </td>
                            <td style="padding: 10px;"><?php echo $u['email']; ?></td>
                            <td style="padding: 10px;">
                                <span style="padding: 4px 10px; border-radius: 12px; font-size: 0.8rem; background: <?php echo $u['role'] === 'admin' ? 'rgba(212, 165, 116, 0.2)' : 'rgba(255,255,255,0.1)'; ?>; color: <?php echo $u['role'] === 'admin' ? 'var(--accent)' : 'inherit'; ?>;">
                                    <?php echo ucfirst($u['role']); ?>
                                </span>
                            </td>
                            <td style="padding: 10px;"><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                            <td style="padding: 10px;">
                                <button class="action-btn btn-edit" onclick='editUser(<?php echo json_encode($u); ?>)'>Edit Role</button>
                                <?php if($u['id'] !== $_SESSION['user_id']): ?>
                                    <a href="users.php?delete=<?php echo $u['id']; ?>" class="action-btn btn-delete" onclick="return confirm('Delete this user? This will also delete their cart, but keep their orders (set to NULL).')">Delete</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>

    <!-- Edit User Modal -->
    <div id="userModal" class="modal">
        <div class="modal-content">
            <h2 class="brand-font" style="margin-bottom: 20px;">Edit User Role</h2>
            <form action="users.php" method="POST">
                <input type="hidden" name="id" id="userId">
                
                <div class="form-group">
                    <label>User Name</label>
                    <input type="text" id="userName" disabled style="background: var(--bg-tertiary); opacity: 0.7;">
                </div>

                <div class="form-group">
                    <label>Role</label>
                    <select name="role" id="userRole" style="width: 100%; padding: 12px; background: var(--bg-tertiary); color: white; border: 1px solid var(--border);">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                
                <div style="display: flex; gap: 15px; margin-top: 30px;">
                    <button type="submit" class="btn-primary">Update Role</button>
                    <button type="button" class="btn-primary" onclick="closeModal()" style="background: #333; color: #fff;">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('userModal');
        function editUser(u) {
            document.getElementById('userId').value = u.id;
            document.getElementById('userName').value = u.name;
            document.getElementById('userRole').value = u.role;
            modal.style.display = 'block';
        }
        function closeModal() { modal.style.display = 'none'; }
    </script>
</body>
</html>
