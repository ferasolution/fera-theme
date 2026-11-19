/* Fera ambient backgrounds, adapted from the supplied design reference. */
function startAtmosphere(){
  const presets={home:'net',cybersecurity:'scan','web-development':'code','software-development':'globe','digital-marketing':'rise','spectrum-business':'wave'};
  const host=document.querySelector('[data-fera-background]');
  if(!host)return;
  const mode=host?.dataset.feraBackground||presets[document.body.dataset.page]||'net';
  if(mode==='off')return;
  const canvas=document.createElement('canvas');canvas.id='fera-background';canvas.dataset.mode=mode;canvas.setAttribute('aria-hidden','true');document.body.prepend(canvas);
  const ctx=canvas.getContext('2d');if(!ctx)return;
  const reduce=matchMedia('(prefers-reduced-motion: reduce)');let color='#ff3c43',width=0,height=0,points=[],columns=[],frame=0,last=0,t=0,globe=[],mouse={x:-999,y:-999};
  function size(){color=getComputedStyle(document.body).getPropertyValue('--service-accent').trim()||getComputedStyle(document.documentElement).getPropertyValue('--red').trim()||'#ff3c43';width=innerWidth;height=innerHeight;const ratio=Math.min(devicePixelRatio||1,1.5);canvas.width=Math.round(width*ratio);canvas.height=Math.round(height*ratio);ctx.setTransform(ratio,0,0,ratio,0,0);points=Array.from({length:Math.min(width<720?35:65,Math.floor(width/18))},()=>({x:Math.random()*width,y:Math.random()*height,vx:(Math.random()-.5)*.45,vy:(Math.random()-.5)*.45}));columns=Array.from({length:Math.ceil(width/32)},()=>({y:Math.random()*height,v:.6+Math.random()*1.4}));globe=Array.from({length:230},(_,i)=>{const u=i/230*Math.PI*26,v=Math.acos(1-2*(i+.5)/230);return {x:Math.sin(v)*Math.cos(u),y:Math.cos(v),z:Math.sin(v)*Math.sin(u)};});draw(false);}
  function draw(move){
    if(move)t++;ctx.clearRect(0,0,width,height);ctx.fillStyle=color;ctx.strokeStyle=color;ctx.lineWidth=1;
    if(mode==='net'||mode==='scan'){
      for(const a of points){if(move){a.x+=a.vx;a.y+=a.vy;if(a.x<0||a.x>width)a.vx*=-1;if(a.y<0||a.y>height)a.vy*=-1;const dx=a.x-mouse.x,dy=a.y-mouse.y,d2=dx*dx+dy*dy;if(d2>0&&d2<14400){const d=Math.sqrt(d2);a.x+=dx/d*.7;a.y+=dy/d*.7;}}ctx.globalAlpha=.45;ctx.beginPath();ctx.arc(a.x,a.y,1.8,0,Math.PI*2);ctx.fill();}
      for(let i=0;i<points.length;i++)for(let j=i+1;j<points.length;j++){const a=points[i],b=points[j],dx=a.x-b.x,dy=a.y-b.y,d2=dx*dx+dy*dy;if(d2<22500){ctx.globalAlpha=(1-Math.sqrt(d2)/150)*.23;ctx.beginPath();ctx.moveTo(a.x,a.y);ctx.lineTo(b.x,b.y);ctx.stroke();}}
      if(mode==='scan'){const max=Math.max(width,height)*.8,r=(t*2)%max;ctx.globalAlpha=.4*(1-r/max);ctx.lineWidth=1.5;ctx.beginPath();ctx.arc(width*.73,height*.46,r,0,Math.PI*2);ctx.stroke();}
    }else if(mode==='code'){
      const glyphs=['</>','{ }','( )','=>','01',';','[]'];ctx.font='12px ui-monospace,monospace';columns.forEach((c,i)=>{if(move){c.y+=c.v;if(c.y>height+30)c.y=-30;}ctx.globalAlpha=.22+(i%3)*.06;ctx.fillText(glyphs[(i+Math.floor(t/45))%glyphs.length],i*32,c.y);ctx.globalAlpha=.07;ctx.fillText(glyphs[(i+2)%glyphs.length],i*32,c.y-36);});
    }else if(mode==='globe'){
      const radius=Math.min(width,height)*.43,cx=width*.71,cy=height*.5;const cosine=Math.cos(t*.004),sine=Math.sin(t*.004);for(const point of globe){const x=point.x*cosine-point.z*sine,y=point.y,z=point.x*sine+point.z*cosine;ctx.globalAlpha=.08+.4*(z+1)/2;ctx.beginPath();ctx.arc(cx+x*radius,cy+y*radius,1+(z+1)*.6,0,Math.PI*2);ctx.fill();}
    }else if(mode==='rise'){
      for(let i=0;i<45;i++){const x=(i*97)%width,y=height-((t*(.5+i%3)+i*53)%height);ctx.globalAlpha=.25*(y/height);ctx.fillRect(x,y,2,12+i%20);}ctx.globalAlpha=.45;ctx.lineWidth=1.5;ctx.beginPath();for(let x=0;x<=width;x+=12){const y=height*.83-x/width*height*.5-Math.sin(x/100+t/50)*15;x?ctx.lineTo(x,y):ctx.moveTo(x,y);}ctx.stroke();
    }else if(mode==='wave'){
      ctx.lineWidth=1.5;for(let k=0;k<6;k++){ctx.globalAlpha=.06+k*.045;ctx.beginPath();for(let x=0;x<=width;x+=10){const y=height*.6+Math.sin(x/(140+k*25)+t/(65+k*8))*(35+k*16);x?ctx.lineTo(x,y):ctx.moveTo(x,y);}ctx.stroke();}
    }ctx.globalAlpha=1;
  }
  function tick(now){frame=0;if(document.hidden||reduce.matches)return;if(now-last>(width<720?48:32)){draw(true);last=now;}frame=requestAnimationFrame(tick);}
  function resume(){cancelAnimationFrame(frame);frame=0;if(!document.hidden&&!reduce.matches)frame=requestAnimationFrame(tick);else globe=Array.from({length:230},(_,i)=>{const u=i/230*Math.PI*26,v=Math.acos(1-2*(i+.5)/230);return {x:Math.sin(v)*Math.cos(u),y:Math.cos(v),z:Math.sin(v)*Math.sin(u)};});draw(false);}
  let resizing;addEventListener('resize',()=>{clearTimeout(resizing);resizing=setTimeout(size,120);},{passive:true});addEventListener('pointermove',e=>{mouse={x:e.clientX,y:e.clientY};},{passive:true});addEventListener('pointerleave',()=>{mouse={x:-999,y:-999};},{passive:true});document.addEventListener('visibilitychange',resume);reduce.addEventListener('change',resume);size();resume();
}
if('requestIdleCallback'in window)requestIdleCallback(startAtmosphere,{timeout:1800});else setTimeout(startAtmosphere,180);
