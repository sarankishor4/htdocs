<style>
    .agent-view {
        padding: 30px;
        max-width: 1200px;
        margin: 0 auto;
        color: white;
    }

    .view-header {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 30px;
    }

    .btn-back {
        background: none;
        border: none;
        color: #94a3b8;
        cursor: pointer;
        font-size: 0.85rem;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .agent-badge {
        width: 42px;
        height: 42px;
        background: rgba(255, 107, 129, 0.1);
        color: #ff6b81;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }

    .agent-title h1 {
        margin: 0;
        font-size: 1.2rem;
        font-family: 'Montserrat', sans-serif;
    }

    .agent-title p {
        margin: 2px 0 0 0;
        color: #94a3b8;
        font-size: 0.8rem;
    }

    /* TABS */
    .view-tabs {
        display: flex;
        gap: 8px;
        margin-bottom: 25px;
        border-bottom: 1px solid rgba(255,255,255,0.05);
        padding-bottom: 8px;
    }

    .tab-item {
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
        cursor: pointer;
        color: #94a3b8;
    }

    .tab-item.active {
        background: #1c1c20;
        color: white;
    }

    /* GRID */
    .view-content {
        display: grid;
        grid-template-columns: 1fr 400px;
        gap: 30px;
    }

    .stats-panel {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
        margin-bottom: 25px;
    }

    .stat-box {
        background: #151518;
        border: 1px solid rgba(255,255,255,0.05);
        padding: 18px;
        border-radius: 14px;
    }

    .stat-box .label {
        font-size: 0.7rem;
        color: #94a3b8;
        margin-bottom: 8px;
        font-weight: 700;
    }

    .stat-box .value {
        font-size: 1.5rem;
        font-weight: 800;
        color: #ff6b81;
    }

    .stat-box .trend {
        font-size: 0.7rem;
        color: #ff6b81;
        margin-top: 4px;
    }

    /* QUICK TASKS */
    .quick-tasks {
        margin-top: 20px;
    }

    .task-btn {
        display: block;
        width: 100%;
        background: rgba(255, 107, 129, 0.03);
        border: 1px solid rgba(255, 107, 129, 0.1);
        color: #ff6b81;
        padding: 12px 16px;
        border-radius: 10px;
        margin-bottom: 10px;
        text-align: left;
        font-size: 0.8rem;
        font-weight: 500;
        cursor: pointer;
        transition: 0.2s;
    }

    .task-btn:hover {
        background: rgba(255, 107, 129, 0.08);
        transform: translateX(4px);
    }

    /* CHAT */
    .chat-panel {
        background: #151518;
        border: 1px solid rgba(255,255,255,0.05);
        border-radius: 18px;
        display: flex;
        flex-direction: column;
        height: 65vh;
        position: sticky;
        top: 30px;
    }

    .chat-header {
        padding: 15px 20px;
        border-bottom: 1px solid rgba(255,255,255,0.05);
        font-weight: 700;
        font-size: 0.75rem;
        letter-spacing: 1px;
        text-transform: uppercase;
        color: #94a3b8;
    }

    .chat-messages {
        flex: 1;
        padding: 20px;
        overflow-y: auto;
    }

    .chat-input-wrap {
        padding: 15px;
        border-top: 1px solid rgba(255,255,255,0.05);
    }

    .chat-input {
        width: 100%;
        background: #0a0a0b;
        border: 1px solid rgba(255,255,255,0.05);
        padding: 12px 15px;
        border-radius: 10px;
        color: white;
        outline: none;
        font-size: 0.85rem;
    }
</style>

<div class="agent-view">
    <div class="view-header">
        <button class="btn-back" onclick="closeWorkspace()"><i class="fas fa-arrow-left"></i> Back</button>
        <div class="agent-badge"><i class="fas fa-hashtag"></i></div>
        <div class="agent-title">
            <h1>Marketing</h1>
            <p>Instagram, content strategy, growth</p>
        </div>
    </div>

    <div class="view-tabs">
        <div class="tab-item active">Overview</div>
        <div class="tab-item">Reels</div>
        <div class="tab-item">Ideas</div>
    </div>

    <div class="view-content">
        <div class="left-col">
            <div class="stats-panel">
                <div class="stat-box">
                    <div class="label">Followers</div>
                    <div class="value">21K+</div>
                    <div class="trend">+420 this month</div>
                </div>
                <div class="stat-box">
                    <div class="label">Avg Views</div>
                    <div class="value">96.5K</div>
                    <div class="trend">23 reels tracked</div>
                </div>
                <div class="stat-box" style="grid-column: span 2;">
                    <div class="label">Total Likes</div>
                    <div class="value" style="color: #ffa502;">39.5K</div>
                </div>
            </div>

            <div class="quick-tasks">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <span class="small-label" style="color: #ff6b81;">Instagram Reels</span>
                    <span style="font-size: 0.65rem; color: #ff6b81; background: rgba(255,107,129,0.1); padding: 2px 8px; border-radius: 20px;">23 cached · Refresh</span>
                </div>
                
                <button class="task-btn">Give me 5 reel ideas for this week based on what's working</button>
                <button class="task-btn">Write 3 hooks for an AI automation reel</button>
                <button class="task-btn">What topics are performing best on my account?</button>
                <button class="task-btn">Create a content calendar for the next 7 days</button>
                <button class="task-btn">Write a caption for a before/after AI automation reel</button>
            </div>
        </div>

        <div class="chat-panel">
            <div class="chat-header">Chat with Marketing</div>
            <div class="chat-messages" id="chat-messages-marketing">
                <div style="margin-bottom:15px; background:rgba(255,255,255,0.02); padding:12px; border-radius:10px; font-size:0.8rem; border-left: 2px solid #ff6b81;">
                    Ready to scale your reach. Send me a directive or use the quick tasks.
                </div>
            </div>
            <div class="chat-input-wrap">
                <input type="text" class="chat-input" placeholder="Ask Marketing anything..." onkeypress="if(event.key === 'Enter') sendMarketingMsg(this)">
            </div>
        </div>
    </div>
</div>

<script>
function sendMarketingMsg(input) {
    const val = input.value.trim();
    if(!val) return;
    
    const container = document.getElementById('chat-messages-marketing');
    container.innerHTML += `<div style="margin-bottom:15px; text-align:right; color:#ff6b81; font-size:0.8rem;"><strong>YOU:</strong> ${val}</div>`;
    input.value = '';
    container.scrollTop = container.scrollHeight;
    
    setTimeout(() => {
        container.innerHTML += `<div style="margin-bottom:15px; background:rgba(255,255,255,0.03); padding:12px; border-radius:10px; font-size:0.8rem;"><strong>MARKETING AI:</strong> Analyzing matrix for "${val}"... I recommend focusing on automation workflows for your next campaign. [SIMULATED]</div>`;
        container.scrollTop = container.scrollHeight;
    }, 800);
}
</script>
