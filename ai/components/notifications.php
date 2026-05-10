<div id="nexus-notifications" class="notif-area"></div>

<style>
.notif-area {
    position: fixed;
    top: 30px;
    right: 30px;
    z-index: 10000;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.notif-toast {
    background: #050505;
    border: 1px solid var(--border-highlight);
    border-left: 4px solid var(--nexus-primary);
    padding: 15px 20px;
    border-radius: 4px;
    min-width: 250px;
    color: #fff;
    font-size: var(--font-small);
    box-shadow: 0 10px 30px rgba(0,0,0,0.5);
    animation: slideIn 0.3s ease forwards;
}

@keyframes slideIn {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

.notif-toast.success { border-left-color: var(--nexus-success); }
.notif-toast.warning { border-left-color: var(--nexus-warning); }
.notif-toast.danger { border-left-color: var(--nexus-danger); }
</style>

<script>
const Notif = {
    show(msg, type = 'info') {
        const area = document.getElementById('nexus-notifications');
        const toast = document.createElement('div');
        toast.className = `notif-toast ${type}`;
        toast.innerHTML = `
            <div style="font-weight: 700; font-size: 0.6rem; color: #444; margin-bottom: 2px;">MATRIX ALERT</div>
            <div>${msg}</div>
        `;
        area.appendChild(toast);
        
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }
};
</script>
