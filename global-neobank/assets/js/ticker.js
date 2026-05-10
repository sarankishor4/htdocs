// Ticker and Crypto Lists JS
const ticks=[
    {s:'BTC',v:'$67,240',c:'+2.34%',up:true},
    {s:'ETH',v:'$3,520',c:'+1.82%',up:true},
    {s:'SOL',v:'$178.40',c:'-0.95%',up:false},
    {s:'EUR/USD',v:'1.0842',c:'+0.12%',up:true},
];
const tr=document.getElementById('ticker');
if(tr) {
    tr.innerHTML=[...ticks,...ticks,...ticks].map(t=>`
    <div class="tick">
        <span class="tick-sym">${t.s}</span>
        <span class="tick-val">${t.v}</span>
        <span class="${t.up?'tick-up':'tick-dn'}">${t.c}</span>
    </div>`).join('');
}

const cryptos=[
    {icon:'₿',name:'Bitcoin',sym:'BTC',price:'$67,240',chg:'+2.34%',up:true,bg:'#f7931a'},
    {icon:'⟠',name:'Ethereum',sym:'ETH',price:'$3,520',chg:'+1.82%',up:true,bg:'#627eea'},
];
const cl = document.getElementById('cryptoList');
if(cl) {
    cl.innerHTML=cryptos.map(c=>`
    <div style="display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid #131d2a;">
        <div style="display:flex; align-items:center; gap:10px;">
            <div style="width:30px;height:30px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:${c.bg}25;color:${c.bg}">${c.icon}</div>
            <div><div>${c.sym}</div><div style="font-size:10px;color:#5a7080;">${c.name}</div></div>
        </div>
        <div style="text-align:right;">
            <div>${c.price}</div>
            <div style="font-size:10px;color:${c.up?'#00e87a':'#ff4560'}">${c.chg}</div>
        </div>
    </div>`).join('');
}
