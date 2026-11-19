/* Foundry owns navigation, forms, chat and demo embedding. No legacy interaction script is loaded. */
(() => {
'use strict';
const config=window.FERA_FOUNDRY||{},motion=matchMedia('(prefers-reduced-motion:reduce)');
document.documentElement.classList.add('ff-js');
if(!motion.matches)document.body?.classList.add('page-arriving');
if(typeof MutationObserver!=='undefined'){
 const fix=()=>document.querySelectorAll('.elementor-accordion [role="button"][aria-selected]').forEach(el=>el.removeAttribute('aria-selected'));
 fix();document.querySelectorAll('.elementor-accordion').forEach(el=>new MutationObserver(fix).observe(el,{subtree:true,attributes:true,attributeFilter:['aria-selected']}));
}
// Measure the existing sticky header; also works with Canvas/optional headers.
const stickyHeader=document.querySelector('.ff-header');
if(stickyHeader){
 const updateHeaderOffset=()=>document.documentElement.style.setProperty('--ff-header-offset',Math.ceil(stickyHeader.getBoundingClientRect().height+16)+'px');
 updateHeaderOffset();
 if(typeof ResizeObserver!=='undefined')new ResizeObserver(updateHeaderOffset).observe(stickyHeader);
 else window.addEventListener('resize',updateHeaderOffset,{passive:true});
}
const menu=document.querySelector('.menu'),nav=document.querySelector('#navigation');
function closeSubmenus(){nav?.querySelectorAll('.ff-submenu-toggle').forEach(toggle=>{toggle.setAttribute('aria-expanded','false');toggle.parentElement.classList.remove('ff-submenu-open');});}
function closeMenu(){nav?.classList.remove('open');menu?.setAttribute('aria-expanded','false');closeSubmenus();}
menu?.addEventListener('click',()=>{if(!nav)return;const open=nav.classList.toggle('open');menu.setAttribute('aria-expanded',String(open));});
nav?.querySelectorAll('a').forEach(a=>a.addEventListener('click',closeMenu));
document.addEventListener('keydown',e=>{if(e.key==='Escape'&&nav?.classList.contains('open')){closeMenu();menu?.focus();}});
// Keep parent links intact; touch users get a separate native disclosure button.
nav?.querySelectorAll('.menu-item-has-children').forEach((item,index)=>{
 const child=[...item.children].find(el=>el.classList.contains('sub-menu'));
 const link=[...item.children].find(el=>el.tagName==='A');
 if(!child||!link||item.querySelector(':scope > .ff-submenu-toggle'))return;
 if(!child.id)child.id='ff-submenu-'+index;
 const toggle=document.createElement('button');toggle.type='button';toggle.className='ff-submenu-toggle';toggle.textContent='⌄';
 toggle.setAttribute('aria-label','Toggle submenu for '+link.textContent.trim());toggle.setAttribute('aria-controls',child.id);toggle.setAttribute('aria-expanded','false');
 toggle.addEventListener('click',()=>{const open=item.classList.toggle('ff-submenu-open');toggle.setAttribute('aria-expanded',String(open));});
 item.insertBefore(toggle,child);
});
matchMedia('(max-width:1190px), (hover:none), (pointer:coarse)').addEventListener('change',closeSubmenus);
async function nonce(){const url=new URL(config.bootstrap,location.href);url.searchParams.set('_fresh',String(Date.now()));const r=await fetch(url,{credentials:'same-origin',cache:'no-store'});const data=await r.json();if(!r.ok||!data.nonce)throw Error('Refresh the page and try again.');return data.nonce;}
const form=document.querySelector('#inquiry');
form?.addEventListener('submit',async e=>{
 e.preventDefault();if(!form.reportValidity())return;const status=form.querySelector('[role=status]'),button=form.querySelector('[type=submit]'),label=button.textContent;
 button.disabled=true;status.dataset.state='pending';status.textContent='Sending…';
 try{const r=await fetch(config.inquiry,{method:'POST',headers:{'Content-Type':'application/json'},credentials:'same-origin',body:JSON.stringify({...Object.fromEntries(new FormData(form)),nonce:await nonce()})});const data=await r.json();if(!r.ok)throw Error(data.message||'Unable to send. Please contact us directly.');status.dataset.state='success';status.textContent=data.message;form.reset();if(data.redirect){const destination=new URL(data.redirect,location.href);if(destination.origin===location.origin){location.assign(destination.href);return;}}}
 catch(err){status.dataset.state='error';status.textContent=err.message||'Unable to send. Your entered details remain here; please retry or contact us directly.';}
 finally{button.disabled=false;button.textContent=label;}
});
document.querySelectorAll('[data-showcase]').forEach(root=>{
 const stage=root.querySelector('.demo-stage'),frame=root.querySelector('iframe');if(!stage||!frame)return;let opener;
 root.querySelectorAll('[data-filter]').forEach(button=>button.addEventListener('click',()=>{root.querySelectorAll('[data-filter]').forEach(b=>b.setAttribute('aria-pressed',String(b===button)));root.querySelectorAll('[data-category]').forEach(card=>card.hidden=button.dataset.filter!=='all'&&card.dataset.category!==button.dataset.filter);}));
 root.querySelectorAll('.demo-open').forEach(button=>button.addEventListener('click',()=>{opener=button;frame.src=button.dataset.src;frame.title=button.dataset.title;root.querySelector('.demo-title').textContent=button.dataset.title;stage.hidden=false;stage.scrollIntoView({behavior:motion.matches?'auto':'smooth',block:'start'});root.querySelector('.demo-close')?.focus({preventScroll:true});}));
 root.querySelector('.demo-close')?.addEventListener('click',()=>{stage.hidden=true;frame.removeAttribute('src');opener?.focus();});
 root.querySelectorAll('[data-device]').forEach(button=>button.addEventListener('click',()=>{stage.classList.toggle('is-mobile',button.dataset.device==='mobile');root.querySelector('.demo-frame-wrap').style.maxWidth=button.dataset.device==='mobile'?'390px':'';root.querySelectorAll('[data-device]').forEach(b=>b.setAttribute('aria-pressed',String(b===button)));}));
});
document.querySelectorAll('a[href="#inquiry"]').forEach(link=>link.addEventListener('click',()=>{const target=document.querySelector('#inquiry');if(target){target.tabIndex=-1;target.focus({preventScroll:true});}}));
const intro=document.querySelector('#welcome-intro');let introTimer,previousFocus;
function endIntro(){if(!intro)return;clearTimeout(introTimer);intro.hidden=true;document.body.classList.remove('intro-active');document.querySelectorAll('body>header,body>main,body>footer,.chat-launcher,.chat-panel').forEach(el=>el.inert=false);previousFocus?.focus?.();}
function replay(){if(!intro||motion.matches)return;previousFocus=document.activeElement;if(intro.parentElement!==document.body)document.body.append(intro);intro.hidden=true;document.body.classList.remove('intro-active');
// Commit the hidden state before revealing, so every original CSS timeline restarts.
void intro.offsetWidth;
intro.hidden=false;document.body.classList.add('intro-active');
intro.getAnimations?.({subtree:true}).forEach(animation=>{animation.cancel();animation.play();});document.querySelectorAll('body>header,body>main,body>footer,.chat-launcher,.chat-panel').forEach(el=>el.inert=true);intro.querySelector('.intro-skip')?.focus();clearTimeout(introTimer);introTimer=setTimeout(endIntro,3800);}
intro?.querySelector('.intro-skip')?.addEventListener('click',endIntro);
const replayButtons=document.querySelectorAll('.replay-intro,.watch-service-intro');
function updateReplayControls(){replayButtons.forEach(b=>{b.disabled=!intro||motion.matches;b.title=motion.matches?'Opening animations are disabled by your reduced-motion preference.':!intro?'This page has its opening disabled.':'';});}
replayButtons.forEach(b=>b.addEventListener('click',replay));updateReplayControls();
document.addEventListener('keydown',e=>{if(intro&&!intro.hidden&&e.key==='Escape')endIntro();if(intro&&!intro.hidden&&e.key==='Tab'){e.preventDefault();intro.querySelector('.intro-skip')?.focus();}});
motion.addEventListener('change',()=>{if(motion.matches)endIntro();updateReplayControls();});
if(!motion.matches&&'IntersectionObserver' in window){const io=new IntersectionObserver(entries=>entries.forEach(e=>{if(e.isIntersecting){e.target.classList.add('visible');io.unobserve(e.target);}}),{threshold:.08});document.querySelectorAll('.studio-card,.studio-step,.security-service').forEach(el=>{el.classList.add('ff-reveal');io.observe(el);});}
const launcher=document.querySelector('.chat-launcher'),panel=document.querySelector('#fera-chat'),messages=panel?.querySelector('.chat-messages');
function openChat(open){if(!panel||!launcher)return;panel.hidden=!open;launcher.setAttribute('aria-expanded',String(open));if(open)panel.querySelector('.chat-close')?.focus();else launcher.focus();}
launcher?.addEventListener('click',()=>openChat(panel?.hidden));panel?.querySelector('.chat-close')?.addEventListener('click',()=>openChat(false));
document.addEventListener('keydown',e=>{if(e.key==='Escape'&&panel&&!panel.hidden)openChat(false);});
function addMessage(text,customer=false){if(!messages)return null;const el=document.createElement('div');el.className='chat-message'+(customer?' customer':'');el.textContent=text;messages.append(el);while(messages.children.length>20)messages.firstElementChild.remove();messages.scrollTop=messages.scrollHeight;return el;}
let busy=false;const history=[];
panel?.querySelector('.chat-composer')?.addEventListener('submit',async e=>{
 e.preventDefault();if(busy)return;const input=panel.querySelector('input'),text=input.value.trim();if(!text)return;input.value='';addMessage(text,true);
 if(!config.aiEnabled){addMessage('Fera Solution builds tailored software, websites and automations with security-minded engineering. This is a scripted guide, not connected AI. Share your requirements through the project inquiry form or speak with our team.');return;}
 busy=true;const reply=addMessage('Thinking…');
 try{const r=await fetch(config.assistant,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json'},body:JSON.stringify({message:text,history:history.slice(-6),nonce:await nonce()})});const data=await r.json();if(!r.ok)throw Error(data.message||'Assistant unavailable.');reply.textContent=data.reply;history.push({role:'user',content:text},{role:'assistant',content:data.reply});}
 catch(err){if(reply)reply.textContent=err.message||'Please contact our team directly.';}finally{busy=false;}
});
let tawkAttached=false,handoff=false,scriptRequested=false;
function ready(){return window.Tawk_API&&typeof window.Tawk_API.maximize==='function';}
function bindTawk(){
 if(!ready()||tawkAttached)return;tawkAttached=true;const api=window.Tawk_API;
 const wrap=(key,handler)=>{const old=api[key];api[key]=(...args)=>{if(typeof old==='function')old(...args);handler(...args);};};
 wrap('onChatMaximized',()=>{if(launcher)launcher.hidden=true;if(panel)panel.hidden=true;});
 wrap('onChatMinimized',()=>{handoff=false;api.hideWidget?.();if(launcher)launcher.hidden=false;});
 wrap('onChatHidden',()=>{if(!handoff&&launcher)launcher.hidden=false;});
 if(handoff){api.showWidget?.();api.maximize();if(launcher)launcher.hidden=true;if(panel)panel.hidden=true;}
 else api.hideWidget?.();
}
function handoffChat(){
 if(document.documentElement.getAttribute('data-fera-consent')==='required'&&(!window.FeraConsent||!window.FeraConsent.allowed('functional'))){addMessage('Live chat needs optional Functional permission. Enable it in Cookie Preferences, save, then choose Speak with our team again. You can also use the inquiry form or call 337-252-4040.');window.FeraConsent?.open();return;}

 handoff=true;if(ready()){bindTawk();window.Tawk_API.showWidget?.();window.Tawk_API.maximize();if(launcher)launcher.hidden=true;if(panel)panel.hidden=true;return;}
 const existing=[...document.scripts].some(s=>s.src.includes('embed.tawk.to/'));
 if(config.tawkOwner==='theme'&&!existing&&!scriptRequested&&/^[a-f0-9]{24}$/.test(config.tawkProperty||'')&&/^[A-Za-z0-9_-]{1,80}$/.test(config.tawkWidget||'')){
  scriptRequested=true;window.Tawk_API=window.Tawk_API||{};const prior=window.Tawk_API.onLoad;
  window.Tawk_API.onLoad=()=>{if(typeof prior==='function')prior();bindTawk();};
  const script=document.createElement('script');script.async=true;script.src='https://embed.tawk.to/'+config.tawkProperty+'/'+config.tawkWidget;script.onerror=()=>{handoff=false;scriptRequested=false;addMessage('Live chat unavailable. Call 337-252-4040 or use the inquiry form.');};document.head.append(script);
 }else if(config.tawkOwner!=='external'&&!existing){handoff=false;addMessage('Live agents are not connected here. Call 337-252-4040 or use the inquiry form.');return;}
 addMessage('Connecting to live chat. If it does not open, call 337-252-4040 or use the inquiry form.');
}
panel?.querySelector('.live-handoff')?.addEventListener('click',handoffChat);
if(launcher){let attempts=0;const poll=setInterval(()=>{bindTawk();if(tawkAttached||++attempts>30)clearInterval(poll);},500);bindTawk();}
function initHeroNetwork(){
 const canvas=document.querySelector('#network');if(!canvas||motion.matches)return;const ctx=canvas.getContext('2d');if(!ctx)return;
 let w=0,h=0,last=0,active=true,frame=0,points=[],resizeTimer;
 const draw=now=>{frame=0;if(!active||document.hidden||motion.matches)return;if(now-last>=40){last=now;ctx.clearRect(0,0,w,h);ctx.strokeStyle='rgba(238,130,140,.24)';ctx.fillStyle='#ee8290';for(let i=0;i<points.length;i++){const p=points[i];p.x=(p.x+p.dx+w)%w;p.y=(p.y+p.dy+h)%h;ctx.fillRect(p.x,p.y,2,2);for(let j=i+1;j<points.length;j++){const q=points[j],dx=p.x-q.x,dy=p.y-q.y;if(dx*dx+dy*dy<12000){ctx.beginPath();ctx.moveTo(p.x,p.y);ctx.lineTo(q.x,q.y);ctx.stroke();}}}}frame=requestAnimationFrame(draw);};
 const resume=()=>{if(frame)cancelAnimationFrame(frame);frame=0;if(active&&!document.hidden&&!motion.matches)frame=requestAnimationFrame(draw);};
 const resize=()=>{const bounds=canvas.getBoundingClientRect();w=Math.round(bounds.width)||500;h=Math.round(bounds.height)||420;canvas.width=w;canvas.height=h;points=Array.from({length:innerWidth<700?20:35},()=>({x:Math.random()*w,y:Math.random()*h,dx:(Math.random()-.5)*.25,dy:(Math.random()-.5)*.25}));resume();};
 resize();addEventListener('resize',()=>{clearTimeout(resizeTimer);resizeTimer=setTimeout(resize,120);},{passive:true});document.addEventListener('visibilitychange',resume);motion.addEventListener('change',resume);
 if('IntersectionObserver'in window)new IntersectionObserver(entries=>{active=entries[0].isIntersecting;resume();}).observe(canvas);
}
if('requestIdleCallback'in window)requestIdleCallback(initHeroNetwork,{timeout:1500});else setTimeout(initHeroNetwork,180);
})();
