<?php
require 'db.php';
require 'includes/header.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$profiles = $conn->query("SELECT p.*, (SELECT COUNT(*) FROM media_faces WHERE profile_id=p.id) as media_count FROM profiles p ORDER BY media_count DESC");
?>

<div class="people-container" style="max-width:1100px; margin:40px auto; color:white;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:40px;">
        <div>
            <h1 style="color:var(--gold); font-size:2.5rem; margin:0; letter-spacing:-1px;">👥 Crevix Intelligence</h1>
            <p style="color:var(--muted);">AI-Powered Person Detection & Face Matching.</p>
        </div>
        <?php if($_SESSION['role'] == 'admin'): ?>
            <a href="admin_people.php" class="btn-primary" style="padding:12px 24px;">⚙️ Admin Manager</a>
        <?php endif; ?>
    </div>

    <?php if($profiles->num_rows == 0): ?>
        <div class="glass-card" style="padding:50px; text-align:center; border-radius:20px; background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.05);">
            <div style="font-size:3rem; margin-bottom:20px;">🔍</div>
            <h3>No people identified yet.</h3>
            <p style="color:var(--muted);">Run an AI Scan on your library to start identifying faces.</p>
            <br>
            <a href="ai_scan.php" class="btn-primary">🚀 Start Global AI Scan</a>
        </div>
    <?php else: ?>
        <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap:25px;">
            <?php while($p = $profiles->fetch_assoc()): ?>
                <a href="profile_view.php?id=<?php echo $p['id']; ?>" class="profile-card">
                    <div class="avatar-wrap">
                        <img src="<?php echo $p['thumbnail'] ?: 'assets/no-avatar.jpg'; ?>" alt="<?php echo htmlspecialchars($p['name']); ?>">
                    </div>
                    <div class="info">
                        <h3><?php echo htmlspecialchars($p['name']); ?></h3>
                        <p><?php echo $p['media_count']; ?> Media Appearances</p>
                    </div>
                </a>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.profile-card {
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 20px;
    padding: 20px;
    text-align: center;
    text-decoration: none;
    color: white;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    backdrop-filter: blur(10px);
}
.profile-card:hover {
    background: rgba(255,215,0,0.05);
    border-color: var(--gold);
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.5);
}
.avatar-wrap {
    width: 120px;
    height: 120px;
    margin: 0 auto 15px;
    border-radius: 50%;
    overflow: hidden;
    border: 3px solid rgba(255,215,0,0.2);
}
.avatar-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.profile-card h3 {
    margin: 0;
    font-size: 1.1rem;
    color: var(--gold);
}
.profile-card p {
    margin: 5px 0 0;
    font-size: 0.85rem;
    color: var(--muted);
}
</style>

<?php require 'includes/footer.php'; ?>
