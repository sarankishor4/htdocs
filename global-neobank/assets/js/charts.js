// Balance Mini Chart
(function(){
    const c=document.getElementById('balChart');
    if(!c)return;
    c.width=220;c.height=80;
    const ctx=c.getContext('2d');
    const pts=[55,42,58,48,62,52,68,58,72,65,80,70,78,74,80];
    const g=ctx.createLinearGradient(0,0,0,80);
    g.addColorStop(0,'rgba(0,232,122,0.3)');g.addColorStop(1,'rgba(0,232,122,0)');
    ctx.beginPath();
    pts.forEach((p,i)=>i?ctx.lineTo(i*(220/(pts.length-1)),80-p):ctx.moveTo(0,80-p));
    ctx.lineTo(220,80);ctx.lineTo(0,80);ctx.closePath();
    ctx.fillStyle=g;ctx.fill();
    ctx.beginPath();
    pts.forEach((p,i)=>i?ctx.lineTo(i*(220/(pts.length-1)),80-p):ctx.moveTo(0,80-p));
    ctx.strokeStyle='#00e87a';ctx.lineWidth=2;ctx.stroke();
})();
