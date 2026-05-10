<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CryptoBank Advanced Integration Nexus</title>
  
  <script src="https://unpkg.com/react@18/umd/react.production.min.js" crossorigin></script>
  <script src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js" crossorigin></script>
  <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

  <style>
    @import url('https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=Inter:wght@300;400;500;600;700&display=swap');
    
    :root {
        --bg-main: #050810;
        --bg-panel: rgba(15, 20, 35, 0.7);
        --border-color: rgba(255, 255, 255, 0.08);
        --color-bank: #00aaff;
        --color-crypto: #00ff88;
        --color-text: #e0e5ff;
        --color-text-muted: #8a94b5;
    }

    * { box-sizing: border-box; }

    body {
        margin: 0; padding: 0;
        background: var(--bg-main);
        color: var(--color-text);
        font-family: 'Inter', sans-serif;
        background-image: 
            radial-gradient(circle at 15% 50%, rgba(0, 170, 255, 0.05), transparent 25%),
            radial-gradient(circle at 85% 30%, rgba(0, 255, 136, 0.05), transparent 25%);
        min-height: 100vh;
    }
    
    .layout {
        display: flex;
        min-height: 100vh;
    }

    /* Sidebar */
    .sidebar {
        width: 280px;
        background: rgba(10, 15, 25, 0.8);
        border-right: 1px solid var(--border-color);
        padding: 30px 20px;
        backdrop-filter: blur(10px);
        display: flex;
        flex-direction: column;
    }

    .brand {
        font-family: 'Space Mono', monospace;
        font-size: 1.5rem;
        font-weight: 700;
        background: linear-gradient(90deg, var(--color-bank), var(--color-crypto));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 40px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .nav-item {
        padding: 14px 16px;
        border-radius: 12px;
        color: var(--color-text-muted);
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 8px;
        transition: all 0.2s;
        font-weight: 500;
    }

    .nav-item:hover {
        background: rgba(255,255,255,0.05);
        color: var(--color-text);
    }

    .nav-item.active {
        background: linear-gradient(90deg, rgba(0, 170, 255, 0.1), rgba(0, 255, 136, 0.1));
        color: #fff;
        border-left: 3px solid var(--color-crypto);
    }

    /* Main Content */
    .main-content {
        flex: 1;
        padding: 40px;
        overflow-y: auto;
    }

    .top-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 40px;
    }

    .page-title {
        font-size: 2rem;
        font-weight: 700;
        margin: 0;
        font-family: 'Space Mono', monospace;
    }

    /* Connection Banner */
    .connection-banner {
        background: var(--bg-panel);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 30px;
        display: flex;
        gap: 20px;
        backdrop-filter: blur(10px);
    }

    .account-selector {
        flex: 1;
    }

    .account-selector label {
        display: block;
        font-size: 0.85rem;
        color: var(--color-text-muted);
        margin-bottom: 8px;
        font-family: 'Space Mono', monospace;
    }

    .select-input {
        width: 100%;
        padding: 14px;
        background: rgba(0,0,0,0.4);
        border: 1px solid rgba(255,255,255,0.1);
        color: #fff;
        border-radius: 10px;
        font-family: 'Inter', sans-serif;
        font-size: 1rem;
        outline: none;
        transition: border-color 0.2s;
    }

    .select-input:focus {
        border-color: var(--color-crypto);
    }

    /* Cards Grid */
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px; }
    .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 24px; margin-bottom: 24px; }

    .stat-card {
        background: var(--bg-panel);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 24px;
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; height: 3px;
    }
    .stat-bank::before { background: var(--color-bank); }
    .stat-crypto::before { background: var(--color-crypto); }
    .stat-networth::before { background: linear-gradient(90deg, var(--color-bank), var(--color-crypto)); }

    .stat-label {
        color: var(--color-text-muted);
        font-size: 0.9rem;
        margin-bottom: 10px;
        display: flex;
        justify-content: space-between;
    }

    .stat-value {
        font-size: 2.5rem;
        font-weight: 700;
        font-family: 'Space Mono', monospace;
        color: #fff;
    }

    /* Transfer Module */
    .transfer-module {
        background: linear-gradient(145deg, rgba(15, 20, 35, 0.9), rgba(10, 12, 20, 0.9));
        border: 1px solid var(--border-color);
        border-radius: 20px;
        padding: 40px;
        text-align: center;
        position: relative;
        overflow: hidden;
        box-shadow: 0 20px 40px rgba(0,0,0,0.3);
    }

    .transfer-flow {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 30px;
        margin-bottom: 40px;
    }

    .flow-node {
        background: rgba(0,0,0,0.3);
        border: 1px solid rgba(255,255,255,0.1);
        padding: 20px;
        border-radius: 16px;
        width: 220px;
    }
    .flow-node.active-sender { border-color: var(--color-text); box-shadow: 0 0 20px rgba(255,255,255,0.05); }

    .direction-btn {
        background: rgba(255,255,255,0.1);
        border: 1px solid rgba(255,255,255,0.2);
        color: #fff;
        width: 60px; height: 60px;
        border-radius: 50%;
        cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        transition: all 0.3s;
    }
    .direction-btn:hover { background: rgba(255,255,255,0.2); transform: scale(1.1); }

    .amount-wrapper {
        position: relative;
        display: inline-block;
        margin-bottom: 30px;
    }

    .amount-input {
        background: transparent;
        border: none;
        border-bottom: 2px solid rgba(255,255,255,0.2);
        color: #fff;
        font-size: 4rem;
        padding: 10px 0;
        width: 300px;
        text-align: center;
        font-family: 'Space Mono', monospace;
        outline: none;
        transition: border-color 0.3s;
    }
    .amount-input:focus { border-color: var(--color-crypto); }

    .btn-execute {
        background: linear-gradient(90deg, var(--color-bank), var(--color-crypto));
        color: #000;
        border: none;
        padding: 18px 40px;
        font-size: 1.2rem;
        font-weight: 700;
        border-radius: 12px;
        cursor: pointer;
        font-family: 'Inter', sans-serif;
        transition: all 0.2s;
        box-shadow: 0 10px 20px rgba(0, 255, 136, 0.2);
    }
    .btn-execute:hover:not(:disabled) { transform: translateY(-3px); box-shadow: 0 15px 25px rgba(0, 255, 136, 0.3); }
    .btn-execute:disabled { background: #222; color: #555; box-shadow: none; cursor: not-allowed; }

    /* Tables */
    .data-table {
        width: 100%;
        border-collapse: collapse;
    }
    .data-table th {
        text-align: left;
        padding: 16px;
        color: var(--color-text-muted);
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        border-bottom: 1px solid var(--border-color);
    }
    .data-table td {
        padding: 16px;
        border-bottom: 1px solid rgba(255,255,255,0.02);
        font-family: 'Space Mono', monospace;
        font-size: 0.95rem;
    }
    .data-table tr:hover td { background: rgba(255,255,255,0.02); }

    .badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        font-family: 'Inter', sans-serif;
    }
    .badge-success { background: rgba(0, 255, 136, 0.1); color: var(--color-crypto); }
    .badge-pending { background: rgba(255, 170, 0, 0.1); color: #ffaa00; }

    .notification { padding: 16px; border-radius: 12px; margin-bottom: 24px; text-align: center; font-weight: 500; animation: slideDown 0.3s ease-out; }
    .notif-success { background: rgba(0,255,136,0.1); border: 1px solid rgba(0,255,136,0.3); color: var(--color-crypto); }
    .notif-error { background: rgba(255,68,102,0.1); border: 1px solid rgba(255,68,102,0.3); color: #ff4466; }

    @keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

    /* Floating Chat Widget */
    .chat-widget { position: fixed; bottom: 20px; right: 20px; width: 300px; background: #0d1421; border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.5); transition: 0.3s; z-index: 1000; }
    .chat-widget.collapsed { height: 50px; }
    .chat-widget.expanded { height: 400px; }
    .chat-header { background: #1a2333; padding: 15px; font-weight: bold; cursor: pointer; display: flex; justify-content: space-between; align-items: center; }
    .chat-messages { flex: 1; padding: 15px; overflow-y: auto; display: flex; flex-direction: column; gap: 10px; font-size: 0.9rem; }
    .chat-msg { background: rgba(255,255,255,0.05); padding: 10px; border-radius: 8px; }
    .chat-msg.system { color: #00ff88; font-family: 'Space Mono', monospace; font-size: 0.8rem; }
    .chat-input-wrap { padding: 10px; border-top: 1px solid rgba(255,255,255,0.1); display: flex; gap: 10px; }
    .chat-input { flex: 1; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.2); color: #fff; padding: 8px; border-radius: 4px; outline: none; }
    .chat-send { background: #00aaff; color: #000; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer; font-weight: bold; }
  </style>
</head>
<body>
  <div id="root"></div>
  
  <script type="text/babel">
    const { useState, useEffect } = React;

    // SVG Icons
    const Icons = {
        Layout: () => <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>,
        Transfer: () => <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M17 3v18"/><path d="M10 14l7 7 7-7"/><path d="M7 21V3"/><path d="M14 10L7 3 0 10"/></svg>,
        Wallet: () => <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"/><path d="M3 5v14a2 2 0 0 0 2 2h16v-5"/><path d="M18 12a2 2 0 0 0 0 4h4v-4Z"/></svg>,
        Link: () => <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>,
    };

    function App() {
      const [view, setView] = useState('auth');
      const [data, setData] = useState({ crypto_users: [], bank_users: [] });
      const [details, setDetails] = useState({ portfolio: [], transactions: [] });
      const [loading, setLoading] = useState(false);
      
      const [bankEmail, setBankEmail] = useState("");
      const [bankPass, setBankPass] = useState("");
      const [cryptoEmail, setCryptoEmail] = useState("");
      const [cryptoPass, setCryptoPass] = useState("");
      
      const [selectedCryptoId, setSelectedCryptoId] = useState("");
      const [selectedBankId, setSelectedBankId] = useState("");
      
      const [amount, setAmount] = useState("");
      const [direction, setDirection] = useState("bank_to_crypto");
      
      const [notification, setNotification] = useState(null);
      const [authError, setAuthError] = useState("");
      const [transferring, setTransferring] = useState(false);
      
      const [chatOpen, setChatOpen] = useState(false);
      const [chatMessages, setChatMessages] = useState([
          { text: "System: Welcome to the Integration Hub secure channel.", type: "system" }
      ]);
      const [chatInput, setChatInput] = useState("");

      const handleConnect = async (e) => {
          e.preventDefault();
          if ((!bankEmail || !bankPass) && (!cryptoEmail || !cryptoPass)) {
              setAuthError("You must provide credentials for at least one platform.");
              return;
          }
          
          setLoading(true);
          setAuthError("");
          
          try {
              const res = await fetch('api.php?action=connect_identity', {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/json' },
                  body: JSON.stringify({ 
                      bank_email: bankEmail, bank_password: bankPass,
                      crypto_email: cryptoEmail, crypto_password: cryptoPass
                  })
              });
              const json = await res.json();
              
              if (res.ok) {
                  setSelectedCryptoId(json.crypto_user_id.toString());
                  setSelectedBankId(json.bank_user_id.toString());
                  
                  // Now fetch accounts so we have full user data
                  const accRes = await fetch('api.php?action=get_accounts');
                  const accJson = await accRes.json();
                  setData(accJson);
                  
                  setView('dashboard');
              } else {
                  setAuthError(json.error || "Failed to verify identities.");
              }
          } catch (err) {
              setAuthError("Network error occurred.");
          }
          setLoading(false);
      };
      
      const fetchDetails = async (cId, bId) => {
          try {
              const res = await fetch(`api.php?action=get_user_details&crypto_id=${cId}&bank_id=${bId}`);
              const json = await res.json();
              setDetails(json);
          } catch(e) { console.error(e); }
      };

      useEffect(() => {
          if (selectedCryptoId && selectedBankId) {
              fetchDetails(selectedCryptoId, selectedBankId);
          }
      }, [selectedCryptoId, selectedBankId]);

      const activeCrypto = data.crypto_users.find(u => u.id.toString() === selectedCryptoId);
      const activeBank = data.bank_users.find(u => u.id.toString() === selectedBankId);
      
      const getBankBalance = (user) => {
          if (!user || !user.wallets) return 0;
          const usdWallet = user.wallets.find(w => w.currency === 'USD');
          return usdWallet ? parseFloat(usdWallet.balance) : 0;
      };

      const handleTransfer = async () => {
          if (!activeCrypto || !activeBank || !amount || isNaN(amount) || amount <= 0) return;
          
          setTransferring(true); setNotification(null);
          
          try {
              const res = await fetch('api.php?action=transfer', {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/json' },
                  body: JSON.stringify({
                      crypto_user_id: activeCrypto.id,
                      bank_user_id: activeBank.id,
                      amount: parseFloat(amount),
                      direction
                  })
              });
              const json = await res.json();
              if (res.ok) {
                  setNotification({ type: 'success', message: `Transfer of $${amount} completed successfully.` });
                  setAmount("");
                  
                  // Refresh balances by fetching details again
                  const accRes = await fetch('api.php?action=get_accounts');
                  const accJson = await accRes.json();
                  setData(accJson);
                  fetchDetails(selectedCryptoId, selectedBankId);
              } else {
                  setNotification({ type: 'error', message: json.error || 'Transfer failed' });
              }
          } catch (e) {
              setNotification({ type: 'error', message: 'Network error occurred' });
          } finally {
              setTransferring(false);
          }
      };
      
      const bankBal = getBankBalance(activeBank);
      const cryptoBal = activeCrypto ? parseFloat(activeCrypto.balance) : 0;
      const portValue = details.portfolio.reduce((sum, p) => sum + (parseFloat(p.amount) * parseFloat(p.avg_buy_price)), 0);
      const netWorth = bankBal + cryptoBal + portValue;

      const sendChatMessage = (e) => {
          e.preventDefault();
          if(!chatInput.trim()) return;
          const newMsg = { text: "You: " + chatInput, type: "user" };
          setChatMessages(prev => [...prev, newMsg]);
          setChatInput("");
          setTimeout(() => {
              setChatMessages(prev => [...prev, { text: "Support: How can we assist you with the integration today?", type: "system" }]);
          }, 1000);
      };

      if (view === 'auth') {
          return (
              <div style={{display:'flex', minHeight:'100vh', alignItems:'center', justifyContent:'center', flexDirection:'column', padding: '20px'}}>
                  <div style={{marginBottom:'40px', display:'flex', alignItems:'center', gap:'15px'}}>
                      <div className="brand" style={{marginBottom: 0, fontSize: '2.5rem'}}><Icons.Link /> Nexus Hub</div>
                  </div>
                  
                  <div className="transfer-module" style={{width: '100%', maxWidth: '800px', padding: '40px'}}>
                      <h2 style={{fontFamily:"'Space Mono', monospace", marginBottom:'10px', fontSize:'1.5rem'}}>Secure Identity Bridge</h2>
                      <p style={{color:'var(--color-text-muted)', marginBottom:'30px', fontSize:'0.9rem'}}>
                          First time? Enter credentials for BOTH platforms to link them. <br/>
                          Returning user? Just log into ONE of your linked platforms below.
                      </p>
                      
                      {authError && <div className="notification notif-error">{authError}</div>}
                      
                      <form onSubmit={handleConnect}>
                          <div className="grid-2">
                              {/* Neobank Side */}
                              <div style={{background: 'rgba(0, 170, 255, 0.05)', padding: '20px', borderRadius: '12px', border: '1px solid rgba(0, 170, 255, 0.2)'}}>
                                  <h3 style={{color: 'var(--color-bank)', marginTop: 0, display: 'flex', alignItems: 'center', gap: '8px'}}><span style={{fontSize:'1.2rem'}}>🏦</span> Global Neobank</h3>
                                  <input 
                                      type="email" className="select-input" placeholder="Bank Email"
                                      style={{marginBottom: '10px'}} value={bankEmail} onChange={e => setBankEmail(e.target.value)}
                                  />
                                  <input 
                                      type="password" className="select-input" placeholder="Bank Password"
                                      value={bankPass} onChange={e => setBankPass(e.target.value)}
                                  />
                              </div>
                              
                              {/* CryptoMind Side */}
                              <div style={{background: 'rgba(0, 255, 136, 0.05)', padding: '20px', borderRadius: '12px', border: '1px solid rgba(0, 255, 136, 0.2)'}}>
                                  <h3 style={{color: 'var(--color-crypto)', marginTop: 0, display: 'flex', alignItems: 'center', gap: '8px'}}><span style={{fontSize:'1.2rem'}}>⚡</span> CryptoMind</h3>
                                  <input 
                                      type="email" className="select-input" placeholder="CryptoMind Email"
                                      style={{marginBottom: '10px'}} value={cryptoEmail} onChange={e => setCryptoEmail(e.target.value)}
                                  />
                                  <input 
                                      type="password" className="select-input" placeholder="CryptoMind Password"
                                      value={cryptoPass} onChange={e => setCryptoPass(e.target.value)}
                                  />
                              </div>
                          </div>
                          
                          <button type="submit" className="btn-execute" style={{width:'100%', marginTop: '20px'}} disabled={loading}>
                              {loading ? 'Verifying & Fetching Connection...' : 'Login / Link Accounts'}
                          </button>
                      </form>
                      
                      <div style={{marginTop: '20px', fontSize: '0.8rem', color: 'var(--color-text-muted)'}}>
                          All verified linkages are securely saved in the isolated Nexus Hub database.
                      </div>
                  </div>
                  
                  {/* Floating Chat Widget */}
                  <div className={`chat-widget ${chatOpen ? 'expanded' : 'collapsed'}`}>
                      <div className="chat-header" onClick={() => setChatOpen(!chatOpen)}>
                          <span>💬 Live Support Chat</span>
                          <span>{chatOpen ? '▼' : '▲'}</span>
                      </div>
                      <div className="chat-messages">
                          {chatMessages.map((m, i) => (
                              <div key={i} className={`chat-msg ${m.type}`}>{m.text}</div>
                          ))}
                      </div>
                      <form className="chat-input-wrap" onSubmit={sendChatMessage}>
                          <input type="text" className="chat-input" placeholder="Type a message..." value={chatInput} onChange={e => setChatInput(e.target.value)} />
                          <button type="submit" className="chat-send">Send</button>
                      </form>
                  </div>

              </div>
          );
      }

      return (
        <div className="layout">
            <div className="sidebar">
                <div className="brand">
                    <Icons.Link />
                    Nexus Hub
                </div>
                
                <div style={{fontSize: '0.75rem', color: 'var(--color-text-muted)', marginBottom: '10px', textTransform:'uppercase', letterSpacing:'1px', marginLeft:'16px'}}>Menu</div>
                
                <div className={`nav-item ${view === 'dashboard' ? 'active' : ''}`} onClick={() => setView('dashboard')}>
                    <Icons.Layout /> Dashboard
                </div>
                <div className={`nav-item ${view === 'transfer' ? 'active' : ''}`} onClick={() => setView('transfer')}>
                    <Icons.Transfer /> Transfer Center
                </div>
                <div className={`nav-item ${view === 'assets' ? 'active' : ''}`} onClick={() => setView('assets')}>
                    <Icons.Wallet /> Asset Overview
                </div>
                
                <div style={{marginTop: 'auto', padding: '20px', background: 'rgba(255,255,255,0.02)', borderRadius: '12px', border: '1px solid var(--border-color)', fontSize: '0.8rem', color: 'var(--color-text-muted)'}}>
                    <div style={{display:'flex', alignItems:'center', gap:'8px', marginBottom:'8px'}}>
                        <div style={{width:'8px', height:'8px', borderRadius:'50%', background:'var(--color-crypto)', boxShadow:'0 0 10px var(--color-crypto)'}}></div>
                        System Secure
                    </div>
                    Encrypted dual-tunnel connection active.
                </div>
            </div>
            
            <div className="main-content">
                <div className="top-bar">
                    <h1 className="page-title">
                        {view === 'dashboard' && 'Integration Dashboard'}
                        {view === 'transfer' && 'Transfer Center'}
                        {view === 'assets' && 'Asset & Portfolio Overview'}
                    </h1>
                    <div style={{display:'flex', gap:'10px'}}>
                        <div style={{background:'var(--bg-panel)', border:'1px solid var(--border-color)', padding:'8px 16px', borderRadius:'20px', fontSize:'0.9rem'}}>
                            <span style={{color:'var(--color-bank)'}}>Bank: {activeBank ? 'Connected' : 'Offline'}</span>
                        </div>
                        <div style={{background:'var(--bg-panel)', border:'1px solid var(--border-color)', padding:'8px 16px', borderRadius:'20px', fontSize:'0.9rem'}}>
                            <span style={{color:'var(--color-crypto)'}}>Crypto: {activeCrypto ? 'Connected' : 'Offline'}</span>
                        </div>
                    </div>
                </div>

                <div className="connection-banner">
                    <div className="account-selector">
                        <label>Global Neobank Identity</label>
                        <div style={{fontSize: '1.2rem', color: '#fff', fontFamily: "'Space Mono', monospace"}}>
                            {activeBank ? `${activeBank.first_name} ${activeBank.last_name}` : 'Not Connected'}
                        </div>
                        <div style={{fontSize: '0.8rem', color: 'var(--color-text-muted)'}}>{activeBank ? activeBank.email : ''}</div>
                    </div>
                    <div style={{display:'flex', alignItems:'center', padding:'0 20px', color:'var(--color-crypto)'}}>
                        <Icons.Link />
                    </div>
                    <div className="account-selector">
                        <label>CryptoMind Identity</label>
                        <div style={{fontSize: '1.2rem', color: '#fff', fontFamily: "'Space Mono', monospace"}}>
                            {activeCrypto ? activeCrypto.username : 'Not Connected'}
                        </div>
                        <div style={{fontSize: '0.8rem', color: 'var(--color-text-muted)'}}>{activeCrypto ? activeCrypto.email : ''}</div>
                    </div>
                    <div style={{display:'flex', alignItems:'center'}}>
                        <button onClick={() => setView('auth')} style={{background:'rgba(255,255,255,0.1)', border:'none', color:'#fff', padding:'8px 16px', borderRadius:'8px', cursor:'pointer'}}>Disconnect</button>
                    </div>
                </div>

                {view === 'dashboard' && (
                    <div>
                        <div className="grid-3">
                            <div className="stat-card stat-bank">
                                <div className="stat-label">Neobank Liquid USD <span>🏦</span></div>
                                <div className="stat-value">${bankBal.toLocaleString('en-US', {minimumFractionDigits:2})}</div>
                            </div>
                            <div className="stat-card stat-crypto">
                                <div className="stat-label">Crypto Trading USD <span>⚡</span></div>
                                <div className="stat-value">${cryptoBal.toLocaleString('en-US', {minimumFractionDigits:2})}</div>
                            </div>
                            <div className="stat-card stat-networth">
                                <div className="stat-label">Total Connected Net Worth <span>💎</span></div>
                                <div className="stat-value">${netWorth.toLocaleString('en-US', {minimumFractionDigits:2})}</div>
                            </div>
                        </div>
                        
                        <div className="grid-2">
                            <div className="stat-card">
                                <h3 style={{marginTop:0, fontFamily:"'Space Mono', monospace"}}>Recent Bridge Activity</h3>
                                {details.transactions.length > 0 ? (
                                    <div style={{display:'flex', flexDirection:'column', gap:'12px', marginTop:'20px'}}>
                                        {details.transactions.slice(0, 4).map((t, i) => (
                                            <div key={i} style={{display:'flex', justifyContent:'space-between', paddingBottom:'12px', borderBottom:'1px solid rgba(255,255,255,0.05)'}}>
                                                <div>
                                                    <div style={{fontSize:'0.9rem', marginBottom:'4px'}}>{t.description}</div>
                                                    <div style={{fontSize:'0.75rem', color:'var(--color-text-muted)'}}>{t.created_at}</div>
                                                </div>
                                                <div style={{fontFamily:"'Space Mono', monospace", fontWeight:'bold'}}>
                                                    ${parseFloat(t.amount).toLocaleString()}
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                ) : (
                                    <div style={{color:'var(--color-text-muted)'}}>No recent transactions found.</div>
                                )}
                            </div>
                            <div className="stat-card">
                                <h3 style={{marginTop:0, fontFamily:"'Space Mono', monospace"}}>Quick Action</h3>
                                <p style={{color:'var(--color-text-muted)', lineHeight:'1.6'}}>
                                    Your accounts are fully synchronized. Use the Transfer Center to move liquidity between Global Neobank and CryptoMind with zero fees and instant settlement.
                                </p>
                                <button onClick={() => setView('transfer')} style={{background:'rgba(255,255,255,0.05)', border:'1px solid var(--border-color)', color:'#fff', padding:'10px 20px', borderRadius:'8px', cursor:'pointer', marginTop:'10px'}}>
                                    Go to Transfer Center &rarr;
                                </button>
                            </div>
                        </div>
                    </div>
                )}

                {view === 'transfer' && (
                    <div>
                        <div className="transfer-module">
                            <h2 style={{fontFamily:"'Space Mono', monospace", marginBottom:'40px', fontSize:'1.8rem'}}>Liquidity Bridge</h2>
                            
                            {notification && (
                                <div className={`notification notif-${notification.type}`}>
                                    {notification.message}
                                </div>
                            )}

                            <div className="transfer-flow">
                                <div className={`flow-node ${direction === 'bank_to_crypto' ? 'active-sender' : ''}`}>
                                    <div style={{fontSize:'0.85rem', color:'var(--color-text-muted)', marginBottom:'10px', textTransform:'uppercase'}}>Source</div>
                                    <div style={{fontFamily:"'Space Mono', monospace", fontSize:'1.2rem', color: direction === 'bank_to_crypto' ? 'var(--color-bank)' : '#fff'}}>
                                        {direction === 'bank_to_crypto' ? 'Global Neobank' : 'CryptoMind'}
                                    </div>
                                    <div style={{marginTop:'10px', fontSize:'0.9rem'}}>
                                        Bal: ${direction === 'bank_to_crypto' ? bankBal.toLocaleString() : cryptoBal.toLocaleString()}
                                    </div>
                                </div>
                                
                                <button className="direction-btn" onClick={() => setDirection(d => d === 'bank_to_crypto' ? 'crypto_to_bank' : 'bank_to_crypto')}>
                                    <Icons.Transfer />
                                </button>

                                <div className={`flow-node ${direction === 'crypto_to_bank' ? 'active-sender' : ''}`}>
                                    <div style={{fontSize:'0.85rem', color:'var(--color-text-muted)', marginBottom:'10px', textTransform:'uppercase'}}>Destination</div>
                                    <div style={{fontFamily:"'Space Mono', monospace", fontSize:'1.2rem', color: direction === 'crypto_to_bank' ? 'var(--color-bank)' : '#fff'}}>
                                        {direction === 'crypto_to_bank' ? 'Global Neobank' : 'CryptoMind'}
                                    </div>
                                    <div style={{marginTop:'10px', fontSize:'0.9rem'}}>
                                        Bal: ${direction === 'crypto_to_bank' ? bankBal.toLocaleString() : cryptoBal.toLocaleString()}
                                    </div>
                                </div>
                            </div>

                            <div className="amount-wrapper">
                                <div style={{position:'absolute', left:'20px', top:'25px', fontSize:'2rem', color:'rgba(255,255,255,0.5)'}}>$</div>
                                <input 
                                    type="number" className="amount-input" placeholder="0.00" 
                                    value={amount} onChange={e => setAmount(e.target.value)}
                                />
                            </div>
                            
                            <div>
                                <button className="btn-execute" onClick={handleTransfer} disabled={transferring || !amount || amount <= 0}>
                                    {transferring ? 'Processing Transaction...' : 'Initialize Transfer'}
                                </button>
                            </div>
                        </div>

                        <div className="stat-card" style={{marginTop: '30px'}}>
                            <h3 style={{marginTop:0, fontFamily:"'Space Mono', monospace"}}>Transaction Ledger</h3>
                            {details.transactions.length > 0 ? (
                                <table className="data-table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Description</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {details.transactions.map((t, i) => (
                                            <tr key={i}>
                                                <td style={{fontSize:'0.85rem', color:'var(--color-text-muted)'}}>{t.created_at}</td>
                                                <td>{t.description}</td>
                                                <td style={{color: t.description.includes('Deposit') ? 'var(--color-crypto)' : '#fff'}}>
                                                    ${parseFloat(t.amount).toLocaleString(undefined, {minimumFractionDigits:2})}
                                                </td>
                                                <td><span className={`badge ${t.status === 'completed' ? 'badge-success' : 'badge-pending'}`}>{t.status}</span></td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            ) : (
                                <div style={{color:'var(--color-text-muted)', padding:'20px 0'}}>No ledger records available.</div>
                            )}
                        </div>
                    </div>
                )}

                {view === 'assets' && (
                    <div>
                        <div className="stat-card" style={{marginBottom:'24px', background:'linear-gradient(135deg, rgba(0, 255, 136, 0.1), rgba(15, 20, 35, 0.8))'}}>
                            <div className="stat-label">Crypto Portfolio Value</div>
                            <div className="stat-value">${portValue.toLocaleString('en-US', {minimumFractionDigits:2})}</div>
                        </div>

                        <div className="stat-card">
                            <h3 style={{marginTop:0, fontFamily:"'Space Mono', monospace"}}>Held Assets</h3>
                            {details.portfolio.length > 0 ? (
                                <table className="data-table">
                                    <thead>
                                        <tr>
                                            <th>Asset</th>
                                            <th>Amount</th>
                                            <th>Avg Buy Price</th>
                                            <th>Total Value</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {details.portfolio.map((p, i) => {
                                            const val = parseFloat(p.amount) * parseFloat(p.avg_buy_price);
                                            return (
                                                <tr key={i}>
                                                    <td>
                                                        <div style={{display:'flex', alignItems:'center', gap:'10px'}}>
                                                            <div style={{width:'30px', height:'30px', background:'rgba(255,255,255,0.1)', borderRadius:'50%', display:'flex', alignItems:'center', justifyContent:'center', fontSize:'0.8rem', fontWeight:'bold'}}>
                                                                {p.coin_symbol}
                                                            </div>
                                                            <div>
                                                                <div style={{fontWeight:'bold'}}>{p.coin_name}</div>
                                                                <div style={{fontSize:'0.8rem', color:'var(--color-text-muted)'}}>{p.coin_symbol}</div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>{parseFloat(p.amount).toLocaleString(undefined, {maximumFractionDigits:6})}</td>
                                                    <td>${parseFloat(p.avg_buy_price).toLocaleString()}</td>
                                                    <td style={{color:'var(--color-crypto)', fontWeight:'bold'}}>${val.toLocaleString(undefined, {minimumFractionDigits:2})}</td>
                                                </tr>
                                            );
                                        })}
                                    </tbody>
                                </table>
                            ) : (
                                <div style={{color:'var(--color-text-muted)', padding:'20px 0'}}>No assets currently held in portfolio. Use your connected Neobank balance to buy crypto!</div>
                            )}
                        </div>
                    </div>
                )}
            </div>
        </div>
          
        {/* Floating Chat Widget */}
        <div className={`chat-widget ${chatOpen ? 'expanded' : 'collapsed'}`}>
            <div className="chat-header" onClick={() => setChatOpen(!chatOpen)}>
                <span>💬 Live Support Chat</span>
                <span>{chatOpen ? '▼' : '▲'}</span>
            </div>
            <div className="chat-messages">
                {chatMessages.map((m, i) => (
                    <div key={i} className={`chat-msg ${m.type}`}>{m.text}</div>
                ))}
            </div>
            <form className="chat-input-wrap" onSubmit={sendChatMessage}>
                <input type="text" className="chat-input" placeholder="Type a message..." value={chatInput} onChange={e => setChatInput(e.target.value)} />
                <button type="submit" className="chat-send">Send</button>
            </form>
        </div>
      );
    }

    const root = ReactDOM.createRoot(document.getElementById('root'));
    root.render(<App />);
  </script>
</body>
</html>
