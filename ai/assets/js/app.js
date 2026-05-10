/**
 * AI NEXUS: MASTER CONTROLLER
 * Handles Matrix switching, Command routing, and UI Animations
 */

const App = {
    activeDept: 'home',
    
    init() {
        console.log("Matrix Initialized...");
        this.bindEvents();
        this.initCharts();
    },

    bindEvents() {
        // Command Input
        const mainInput = document.getElementById('main-input');
        if (mainInput) {
            mainInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    this.handleCommand(mainInput.value);
                    mainInput.value = '';
                }
            });
        }
    },

    showDept(deptId) {
        console.log(`Switching to Matrix: ${deptId}`);
        this.activeDept = deptId;

        // Hide main grid
        const mainGrid = document.getElementById('main-grid');
        if (mainGrid) mainGrid.style.display = 'none';

        // Hide all workspaces
        document.querySelectorAll('.dept-workspace').forEach(ws => {
            ws.style.display = 'none';
        });

        // Show console
        const consoleWrap = document.getElementById('console-wrap');
        if (consoleWrap) {
            consoleWrap.style.display = 'block';
            document.getElementById('console-title').innerText = `${deptId.toUpperCase()} CONTROL MATRIX`;
        }

        // Show target workspace
        const targetWs = document.getElementById(`workspace-${deptId}`);
        if (targetWs) {
            targetWs.style.display = 'block';
            anime({
                targets: targetWs,
                opacity: [0, 1],
                translateY: [20, 0],
                duration: 800,
                easing: 'easeOutExpo'
            });
        }

        // Update active nav
        document.querySelectorAll('.agent-item').forEach(item => {
            item.classList.remove('active');
            if (item.getAttribute('onclick')?.includes(deptId)) {
                item.classList.add('active');
            }
        });

        this.log(`Matrix connection established: ${deptId.toUpperCase()}`, 'success');
    },

    closeConsole() {
        const consoleWrap = document.getElementById('console-wrap');
        const mainGrid = document.getElementById('main-grid');
        
        if (consoleWrap) consoleWrap.style.display = 'none';
        if (mainGrid) mainGrid.style.display = 'grid';

        document.querySelectorAll('.dept-workspace').forEach(ws => {
            ws.style.display = 'none';
        });

        document.querySelectorAll('.agent-item').forEach(item => item.classList.remove('active'));
        this.activeDept = 'home';
    },

    async handleCommand(cmd) {
        if (!cmd.trim()) return;

        this.log(`EXECUTING: ${cmd}`, 'info');

        try {
            const response = await fetch('nexus_api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    command: cmd,
                    department: this.activeDept
                })
            });

            const data = await response.json();
            
            if (data.status === 'success') {
                this.log(`[${data.agent}] ${data.response}`, 'agent');
                if (window.Notif) Notif.show(`Agent ${data.agent} processed command.`, 'success');
            } else {
                this.log(`[ERROR] ${data.message}`, 'error');
            }
        } catch (err) {
            this.log(`[CRITICAL] Connection lost to Nexus Core.`, 'error');
        }
    },

    log(msg, type = 'info') {
        const output = document.getElementById('console-output');
        if (!output) return;

        const div = document.createElement('div');
        div.className = `log-line log-${type}`;
        
        let prefix = '[SYSTEM]';
        if (type === 'agent') prefix = '[MATRIX]';
        if (type === 'error') prefix = '[ALERT]';
        
        div.innerHTML = `<span style="opacity: 0.5;">${new Date().toLocaleTimeString()}</span> <span class="log-prefix">${prefix}</span> ${msg}`;
        output.appendChild(div);
        output.scrollTop = output.scrollHeight;

        anime({
            targets: div,
            translateX: [-10, 0],
            opacity: [0, 1],
            duration: 400
        });
    },

    initCharts() {
        // Initializer for Chart.js instances in workspaces
        // Will be called when workspaces are loaded
    }
};

// Global shorthand for inline onclicks
function showDept(id) { App.showDept(id); }
function closeConsole() { App.closeConsole(); }

// Boot
window.onload = () => App.init();
