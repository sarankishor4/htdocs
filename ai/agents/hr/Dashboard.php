<style>
    .agent-view { padding: 40px; max-width: 1400px; margin: 0 auto; color: white; }
    .view-header { display: flex; align-items: center; gap: 20px; margin-bottom: 40px; }
    .btn-back { background: none; border: none; color: var(--text-dim); cursor: pointer; font-size: 0.9rem; display: flex; align-items: center; gap: 8px; }
    .agent-badge { width: 50px; height: 50px; background: rgba(116, 120, 246, 0.1); color: #7478f6; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
    .agent-title h1 { margin: 0; font-size: 1.4rem; font-family: 'Montserrat', sans-serif; }
    .agent-title p { margin: 5px 0 0 0; color: var(--text-dim); font-size: 0.85rem; }
    .view-tabs { display: flex; gap: 10px; margin-bottom: 30px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px; }
    .tab-item { padding: 10px 20px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; cursor: pointer; color: var(--text-dim); transition: 0.2s; }
    .tab-item.active { background: rgba(255, 255, 255, 0.05); color: white; }
    .view-content { display: grid; grid-template-columns: 1fr 450px; gap: 40px; }
    .stats-panel { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
    .stat-box { background: var(--bg-card); border: 1px solid var(--border-color); padding: 20px; border-radius: 16px; }
    .stat-box .label { font-size: 0.75rem; color: var(--text-dim); margin-bottom: 10px; text-transform: uppercase; font-weight: 700; }
    .stat-box .value { font-size: 1.8rem; font-weight: 800; color: #7478f6; }
    .stat-box .trend { font-size: 0.75rem; color: #7478f6; margin-top: 5px; }
    .quick-tasks { margin-top: 30px; }
    .task-btn { display: block; width: 100%; background: rgba(116, 120, 246, 0.05); border: 1px solid rgba(116, 120, 246, 0.2); color: #7478f6; padding: 14px 20px; border-radius: 12px; margin-bottom: 12px; text-align: left; font-size: 0.85rem; font-weight: 500; cursor: pointer; transition: 0.2s; }
    .task-btn:hover { background: rgba(116, 120, 246, 0.1); transform: translateX(5px); }
    .chat-panel { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 20px; display: flex; flex-direction: column; height: 70vh; position: sticky; top: 40px; }
    .chat-header { padding: 20px; border-bottom: 1px solid var(--border-color); font-weight: 700; font-size: 0.8rem; letter-spacing: 1px; text-transform: uppercase; }
    .chat-messages { flex: 1; padding: 20px; overflow-y: auto; }
    .chat-input-wrap { padding: 20px; border-top: 1px solid var(--border-color); }
    .chat-input { width: 100%; background: rgba(0, 0, 0, 0.2); border: 1px solid var(--border-color); padding: 15px; border-radius: 12px; color: white; outline: none; }
</style>

<div class="agent-view">
    <div class="view-header">
        <button class="btn-back" onclick="closeWorkspace()"><i class="fas fa-arrow-left"></i> Back</button>
        <div class="agent-badge"><i class="fas fa-users"></i></div>
        <div class="agent-title">
            <h1>HR Department</h1>
            <p>Team management, performance, tasks</p>
        </div>
    </div>

    <div class="view-tabs">
        <div class="tab-item active">Team</div>
        <div class="tab-item">Performance</div>
        <div class="tab-item">Hiring</div>
    </div>

    <div class="view-content">
        <div class="left-col">
            <div class="stats-panel">
                <div class="stat-box">
                    <div class="label">Team Size</div>
                    <div class="value">12 Ops</div>
                    <div class="trend">1 onboarding</div>
                </div>
                <div class="stat-box">
                    <div class="label">Efficiency</div>
                    <div class="value">92%</div>
                    <div class="trend">+4% this month</div>
                </div>
                <div class="stat-box" style="grid-column: span 2;">
                    <div class="label">Active Roles</div>
                    <div class="value" style="color: #ffa502;">3 Open</div>
                </div>
            </div>

            <div class="quick-tasks">
                <span class="section-label" style="color: #7478f6;">Quick Tasks</span>
                <button class="task-btn">Review the latest performance logs</button>
                <button class="task-btn">Create a job description for Senior AI Dev</button>
                <button class="task-btn">Analyze team workload and burnout risk</button>
                <button class="task-btn">Draft a team meeting agenda for Monday</button>
            </div>
        </div>

        <div class="chat-panel">
            <div class="chat-header">Chat with HR AI</div>
            <div class="chat-messages" id="chat-messages-hr"></div>
            <div class="chat-input-wrap">
                <input type="text" class="chat-input" placeholder="Ask HR anything..." onkeypress="if(event.key === 'Enter') sendHRMsg(this)">
            </div>
        </div>
    </div>
</div>

<script>
function sendHRMsg(input) {
    const val = input.value.trim();
    if(!val) return;
    const container = document.getElementById('chat-messages-hr');
    container.innerHTML += `<div style="margin-bottom:15px; text-align:right; color:#7478f6; font-size:0.9rem;"><strong>YOU:</strong> ${val}</div>`;
    input.value = '';
    setTimeout(() => {
        container.innerHTML += `<div style="margin-bottom:15px; background:rgba(255,255,255,0.03); padding:15px; border-radius:12px; font-size:0.9rem;"><strong>HR AI:</strong> Checking team matrix for "${val}"... Analysis complete. [SIMULATED]</div>`;
        container.scrollTop = container.scrollHeight;
    }, 1000);
}
</script>
