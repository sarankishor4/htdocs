async function loadLoans() {
    try {
        const res = await fetch('api/loans.php?action=list');
        const data = await res.json();
        if(data.success) {
            const container = document.getElementById('loansList');
            if(data.data.length === 0) {
                container.innerHTML = '<p style="font-size:12px; color:var(--muted);">No loans currently active.</p>';
            } else {
                container.innerHTML = data.data.map(l => {
                    const pct = Math.min(100, (l.repaid_amount / l.amount) * 100);
                    let col = 'var(--green)';
                    if(l.status === 'defaulted') col = 'var(--red)';
                    if(l.status === 'pending') col = 'var(--gold)';
                    return `
                    <div style="background:var(--surface); padding:16px; border:1px solid var(--border); border-radius:4px;">
                        <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                            <span style="font-weight:600; font-size:14px;">Loan #${l.id} &mdash; $${l.amount}</span>
                            <span style="font-size:10px; padding:2px 6px; border:1px solid ${col}; color:${col}; text-transform:uppercase;">${l.status}</span>
                        </div>
                        <div style="height:4px; background:var(--border); border-radius:2px; overflow:hidden; margin-bottom:8px;">
                            <div style="height:100%; width:${pct}%; background:${col};"></div>
                        </div>
                        <div style="display:flex; justify-content:space-between; font-size:11px; color:var(--muted);">
                            <span>$${l.repaid_amount} repaid</span>
                            <span>Interest: ${l.interest_rate}%</span>
                        </div>
                    </div>`;
                }).join('');
            }
        }
    } catch(e) { console.error('Failed to load loans', e); }
}

async function applyLoan() {
    const amt = document.getElementById('loanAmount').value;
    const msg = document.getElementById('loanMsg');
    
    const formData = new FormData();
    formData.append('amount', amt);
    
    try {
        const res = await fetch('api/loans.php?action=apply', { method: 'POST', body: formData });
        const data = await res.json();
        
        if(data.success) {
            msg.style.color = 'var(--green)';
            msg.innerText = 'Loan approved and deposited to your wallet!';
            document.getElementById('loanAmount').value = '';
            loadLoans();
        } else {
            msg.style.color = 'var(--red)';
            msg.innerText = data.error;
        }
    } catch(e) {
        msg.style.color = 'var(--red)';
        msg.innerText = 'Network error.';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    loadLoans();
});
