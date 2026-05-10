<div class="agent-chat-wrap" style="background: rgba(0,0,0,0.4); border: 1px solid var(--glass-border); border-radius: 16px; display: flex; flex-direction: column; height: 100%;">
    <div class="chat-header" style="padding: 15px; border-bottom: 1px solid var(--glass-border); display: flex; justify-content: space-between; align-items: center;">
        <span style="font-size: 0.7rem; font-weight: 800; letter-spacing: 1px; color: var(--nexus-primary);">AI INTERACTION NODE</span>
        <i class="fas fa-circle-notch fa-spin" style="font-size: 0.6rem; color: var(--nexus-success);"></i>
    </div>
    
    <div class="chat-messages" id="chat-messages-<?php echo $this->role; ?>" style="flex: 1; padding: 15px; overflow-y: auto; font-size: 0.8rem; line-height: 1.5;">
        <div class="msg-bot" style="margin-bottom: 15px; background: rgba(255,255,255,0.03); padding: 10px; border-radius: 10px; border-left: 3px solid var(--nexus-primary);">
            Operational matrix ready. How should I proceed with the <strong><?php echo strtoupper($this->role); ?></strong> agenda?
        </div>
    </div>

    <div class="chat-input-area" style="padding: 15px; border-top: 1px solid var(--glass-border);">
        <div style="position: relative;">
            <input type="text" 
                   class="chat-input" 
                   placeholder="Ask the <?php echo $this->role; ?> AI..." 
                   onkeypress="if(event.key === 'Enter') sendAgentMessage(this, '<?php echo $this->role; ?>')"
                   style="width: 100%; background: rgba(0,0,0,0.3); border: 1px solid var(--glass-border); padding: 12px 15px; border-radius: 10px; color: #fff; font-size: 0.85rem; outline: none;">
            <i class="fas fa-paper-plane" style="position: absolute; right: 15px; top: 13px; color: var(--nexus-primary); cursor: pointer;"></i>
        </div>
    </div>
</div>

<script>
function sendAgentMessage(input, role) {
    const msg = input.value.trim();
    if(!msg) return;
    
    const container = document.getElementById('chat-messages-' + role);
    
    // User Message
    const userDiv = document.createElement('div');
    userDiv.className = 'msg-user';
    userDiv.style = 'margin-bottom: 15px; text-align: right; padding-right: 10px; color: var(--nexus-primary);';
    userDiv.innerHTML = `<strong>YOU:</strong> ${msg}`;
    container.appendChild(userDiv);
    
    input.value = '';
    container.scrollTop = container.scrollHeight;

    // Trigger AI Process
    App.handleCommand(msg); // Reuses the global command handler
}
</script>
