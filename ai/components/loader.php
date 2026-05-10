<div id="nexus-loader" class="loader-wrap">
    <div class="loader-content">
        <div class="loader-ring"></div>
        <div class="loader-text">SYNCHRONIZING MATRIX</div>
    </div>
</div>

<style>
.loader-wrap {
    position: fixed;
    inset: 0;
    background: #050505;
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: opacity 0.8s ease-out, visibility 0.8s;
}

.loader-wrap.hidden {
    opacity: 0;
    visibility: hidden;
}

.loader-content {
    text-align: center;
}

.loader-ring {
    width: 80px;
    height: 80px;
    border: 3px solid transparent;
    border-top-color: var(--nexus-primary);
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 20px;
    box-shadow: 0 0 20px rgba(0, 210, 255, 0.2);
}

.loader-text {
    font-family: 'Orbitron', sans-serif;
    color: var(--nexus-primary);
    letter-spacing: 4px;
    font-size: 0.8rem;
    animation: pulse 2s infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}
</style>

<script>
window.addEventListener('load', () => {
    const loader = document.getElementById('nexus-loader');
    setTimeout(() => {
        loader.classList.add('hidden');
    }, 1500);
});
</script>
