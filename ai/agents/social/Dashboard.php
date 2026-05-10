<style>
    .agent-view { padding: 30px; max-width: 1200px; margin: 0 auto; color: white; }
    .view-header { display: flex; align-items: center; gap: 15px; margin-bottom: 30px; }
    .btn-back { background: none; border: none; color: #94a3b8; cursor: pointer; font-size: 0.85rem; display: flex; align-items: center; gap: 6px; }
    .agent-badge { width: 42px; height: 42px; background: rgba(114, 137, 218, 0.1); color: #7289da; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
    .agent-title h1 { margin: 0; font-size: 1.2rem; font-family: 'Montserrat', sans-serif; }
    .agent-title p { margin: 2px 0 0 0; color: #94a3b8; font-size: 0.8rem; }
    .view-tabs { display: flex; gap: 8px; margin-bottom: 25px; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 8px; }
    .tab-item { padding: 8px 16px; border-radius: 8px; font-size: 0.8rem; font-weight: 600; cursor: pointer; color: #94a3b8; }
    .tab-item.active { background: #1c1c20; color: white; }
    .view-content { display: grid; grid-template-columns: 1fr 400px; gap: 30px; }
    .stats-panel { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 25px; }
    .stat-box { background: #151518; border: 1px solid rgba(255,255,255,0.05); padding: 18px; border-radius: 14px; }
    .stat-box .label { font-size: 0.7rem; color: #94a3b8; margin-bottom: 8px; font-weight: 700; }
    .stat-box .value { font-size: 1.5rem; font-weight: 800; color: #7289da; }
    .stat-box .trend { font-size: 0.7rem; color: #7289da; margin-top: 4px; }
    .quick-tasks { margin-top: 20px; }
    .task-btn { display: block; width: 100%; background: rgba(114, 137, 218, 0.03); border: 1px solid rgba(114, 137, 218, 0.1); color: #7289da; padding: 12px 16px; border-radius: 10px; margin-bottom: 10px; text-align: left; font-size: 0.8rem; font-weight: 500; cursor: pointer; }
    .chat-panel { background: #151518; border: 1px solid rgba(255,255,255,0.05); border-radius: 18px; display: flex; flex-direction: column; height: 65vh; position: sticky; top: 30px; }
    .chat-header { padding: 15px 20px; border-bottom: 1px solid rgba(255,255,255,0.05); font-weight: 700; font-size: 0.75rem; letter-spacing: 1px; text-transform: uppercase; color: #94a3b8; }
    .chat-messages { flex: 1; padding: 20px; overflow-y: auto; }
    .chat-input-wrap { padding: 15px; border-top: 1px solid rgba(255,255,255,0.05); }
    .chat-input { width: 100%; background: #0a0a0b; border: 1px solid rgba(255,255,255,0.05); padding: 12px 15px; border-radius: 10px; color: white; outline: none; font-size: 0.85rem; }
</style>

<div class="agent-view">
    <div class="view-header">
        <button class="btn-back" onclick="closeWorkspace()"><i class="fas fa-arrow-left"></i> Back</button>
        <div class="agent-badge"><i class="fas fa-hashtag"></i></div>
        <div class="agent-title">
            <h1>Social Sphere</h1>
            <p>Social engagement, community sentiment, viral tracking</p>
        </div>
    </div>

    <div class="view-tabs">
        <div class="tab-item active">Engagement</div>
        <div class="tab-item">Sentiment</div>
        <div class="tab-item">Community</div>
    </div>

    <div class="view-content">
        <div class="left-col">
            <div class="stats-panel">
                <div class="stat-box">
                    <div class="label">Total Reach</div>
                    <div class="value">420K</div>
                    <div class="trend">+8.2% viral growth</div>
                </div>
                <div class="stat-box">
                    <div class="label">Sentiment</div>
                    <div class="value">94% Positive</div>
                    <div class="trend">Stable community mood</div>
                </div>
                <div class="stat-box" style="grid-column: span 2;">
                    <div class="label">Active Threads</div>
                    <div class="value" style="color: #ff00ff;">14 Trending</div>
                </div>
            </div>

            <div class="quick-tasks">
                <span class="small-label" style="color: #7289da;">Social Directives</span>
                <button class="task-btn">Analyze community sentiment for the last 24 hours</button>
                <button class="task-btn">Identify trending topics in our niche</button>
                <button class="task-btn">Draft a viral thread about the Phaze AI launch</button>
                <button class="task-btn">Moderate active community discussions</button>
            </div>
        </div>

        <div class="chat-panel">
            <div class="chat-header">Social Intelligence</div>
            <div class="chat-messages" id="chat-messages-social"></div>
            <div class="chat-input-wrap">
                <input type="text" class="chat-input" placeholder="Listen to the sphere..." onkeypress="if(event.key === 'Enter') sendSocialMsg(this)">
            </div>
        </div>
    </div>
</div>

<script>
function sendSocialMsg(input) {
    const val = input.value.trim();
    if(!val) return;
    const container = document.getElementById('chat-messages-social');
    container.innerHTML += `<div style="margin-bottom:15px; text-align:right; color:#7289da; font-size:0.8rem;"><strong>YOU:</strong> ${val}</div>`;
    input.value = '';
    setTimeout(() => {
        container.innerHTML += `<div style="margin-bottom:15px; background:rgba(255,255,255,0.03); padding:12px; border-radius:10px; font-size:0.8rem;"><strong>SOCIAL AI:</strong> Scanning social nodes for "${val}"... Sentiment is bullish. [SIMULATED]</div>`;
        container.scrollTop = container.scrollHeight;
    }, 800);
}
</script>
