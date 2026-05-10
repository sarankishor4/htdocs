<style>
    .agent-view { padding: 40px; max-width: 1400px; margin: 0 auto; color: white; }
    .view-header { display: flex; align-items: center; gap: 20px; margin-bottom: 40px; }
    .btn-back { background: none; border: none; color: var(--text-dim); cursor: pointer; font-size: 0.9rem; display: flex; align-items: center; gap: 8px; }
    .agent-badge { width: 50px; height: 50px; background: rgba(46, 213, 115, 0.1); color: #2ed573; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
    .agent-title h1 { margin: 0; font-size: 1.4rem; font-family: 'Montserrat', sans-serif; }
    .agent-title p { margin: 5px 0 0 0; color: var(--text-dim); font-size: 0.85rem; }
    .view-tabs { display: flex; gap: 10px; margin-bottom: 30px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px; }
    .tab-item { padding: 10px 20px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; cursor: pointer; color: var(--text-dim); transition: 0.2s; }
    .tab-item.active { background: rgba(255, 255, 255, 0.05); color: white; }
    .view-content { display: grid; grid-template-columns: 1fr 450px; gap: 40px; }
    .stats-panel { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
    .stat-box { background: var(--bg-card); border: 1px solid var(--border-color); padding: 20px; border-radius: 16px; }
    .stat-box .label { font-size: 0.75rem; color: var(--text-dim); margin-bottom: 10px; text-transform: uppercase; font-weight: 700; }
    .stat-box .value { font-size: 1.8rem; font-weight: 800; color: #2ed573; }
    .stat-box .trend { font-size: 0.75rem; color: #2ed573; margin-top: 5px; }
    .quick-tasks { margin-top: 30px; }
    .task-btn { display: block; width: 100%; background: rgba(46, 213, 115, 0.05); border: 1px solid rgba(46, 213, 115, 0.2); color: #2ed573; padding: 14px 20px; border-radius: 12px; margin-bottom: 12px; text-align: left; font-size: 0.85rem; font-weight: 500; cursor: pointer; transition: 0.2s; }
    .task-btn:hover { background: rgba(46, 213, 115, 0.1); transform: translateX(5px); }
    .chat-panel { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 20px; display: flex; flex-direction: column; height: 70vh; position: sticky; top: 40px; }
    .chat-header { padding: 20px; border-bottom: 1px solid var(--border-color); font-weight: 700; font-size: 0.8rem; letter-spacing: 1px; text-transform: uppercase; }
    .chat-messages { flex: 1; padding: 20px; overflow-y: auto; }
    .chat-input-wrap { padding: 20px; border-top: 1px solid var(--border-color); }
    .chat-input { width: 100%; background: rgba(0, 0, 0, 0.2); border: 1px solid var(--border-color); padding: 15px; border-radius: 12px; color: white; outline: none; }
</style>

<div class="agent-view">
    <div class="view-header">
        <button class="btn-back" onclick="closeWorkspace()"><i class="fas fa-arrow-left"></i> Back</button>
        <div class="agent-badge"><i class="fas fa-funnel-dollar"></i></div>
        <div class="agent-title">
            <h1>Sales</h1>
            <p>Leads pipeline, proposals, deal tracking</p>
        </div>
    </div>

    <div class="view-tabs">
        <div class="tab-item active">Pipeline</div>
        <div class="tab-item">Deals</div>
        <div class="tab-item">Proposals</div>
    </div>

    <div class="view-content">
        <div class="left-col">
            <div class="stats-panel">
                <div class="stat-box">
                    <div class="label">Active Leads</div>
                    <div class="value">42</div>
                    <div class="trend">+5 today</div>
                </div>
                <div class="stat-box">
                    <div class="label">Closing Rate</div>
                    <div class="value">24.5%</div>
                    <div class="trend">Up 2% from last week</div>
                </div>
                <div class="stat-box" style="grid-column: span 2;">
                    <div class="label">Pipeline Value</div>
                    <div class="value" style="color: #ffa502;">$124.5K</div>
                </div>
            </div>

            <div class="quick-tasks">
                <span class="section-label" style="color: #2ed573;">Quick Tasks</span>
                <button class="task-btn">Summarize my active deals for the week</button>
                <button class="task-btn">Draft a follow-up email for the Phaze proposal</button>
                <button class="task-btn">What are the high-priority leads today?</button>
                <button class="task-btn">Generate a weekly sales report</button>
            </div>
        </div>

        <div class="chat-panel">
            <div class="chat-header">Chat with Sales AI</div>
            <div class="chat-messages" id="chat-messages-sales"></div>
            <div class="chat-input-wrap">
                <input type="text" class="chat-input" placeholder="Ask Sales anything..." onkeypress="if(event.key === 'Enter') sendSalesMsg(this)">
            </div>
        </div>
    </div>
</div>

<script>
function sendSalesMsg(input) {
    const val = input.value.trim();
    if(!val) return;
    const container = document.getElementById('chat-messages-sales');
    container.innerHTML += `<div style="margin-bottom:15px; text-align:right; color:#2ed573; font-size:0.9rem;"><strong>YOU:</strong> ${val}</div>`;
    input.value = '';
    setTimeout(() => {
        container.innerHTML += `<div style="margin-bottom:15px; background:rgba(255,255,255,0.03); padding:15px; border-radius:12px; font-size:0.9rem;"><strong>SALES AI:</strong> Scanning pipeline for "${val}"... I've identified 3 key opportunities. [SIMULATED]</div>`;
        container.scrollTop = container.scrollHeight;
    }, 1000);
}
</script>
