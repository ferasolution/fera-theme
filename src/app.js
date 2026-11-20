import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import Lenis from 'lenis';

let THREE, EffectComposer, RenderPass, UnrealBloomPass, OutputPass;
async function loadThree() {
  const m = await import('./gl-deps.js');
  THREE = m.THREE; EffectComposer = m.EffectComposer; RenderPass = m.RenderPass; UnrealBloomPass = m.UnrealBloomPass; OutputPass = m.OutputPass;
}
const FERA = window.FERA || { home: '/', urls: {}, svc: {}, rest: '/wp-json/' };

const reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;
const fine = matchMedia('(pointer: fine)').matches;
const mobile = () => innerWidth < 900;
const $ = (s, r = document) => r.querySelector(s);
const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));
gsap.registerPlugin(ScrollTrigger);
const menu = $('#menu'), menuBtn = $('#menuBtn'), drawer = $('#drawer'), scrim = $('#scrim');
const MARK = FERA.mark || $('#loader img').src;
const homeView = $('#top'), svcView = $('#svcPage'), curtain = $('#curtain');
const ROUTE = document.body.dataset.route || (homeView ? 'home' : 'page');
let view = homeView ? 'home' : ROUTE;
let routing = false;
let pendingIcon = null;

/* ---------- smooth scroll ---------- */
let lenis = null;
if (!reduced) {
  lenis = new Lenis({ lerp: 0.085 });
  lenis.stop();
  lenis.on('scroll', ScrollTrigger.update);
  gsap.ticker.add((t) => lenis.raf(t * 1000));
  gsap.ticker.lagSmoothing(0);
}
function scrollToEl(el) {
  if (!el) return;
  if (lenis) lenis.scrollTo(el, { duration: 1.8 });
  else el.scrollIntoView({ behavior: reduced ? 'auto' : 'smooth' });
}
document.addEventListener('click', (e) => {
  const a = e.target.closest('a[href]');
  if (!a || e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
  const href = a.getAttribute('href') || '';
  if (href.startsWith('#')) {
    const el = href.length > 1 && document.getElementById(href.slice(1));
    if (!el) return;
    e.preventDefault();
    const was = menu.classList.contains('open');
    closeMenu();
    setTimeout(() => scrollToEl(el), was ? 500 : 0);
    return;
  }
  if ((a.target && a.target !== '_self') || a.hasAttribute('download') || a.dataset.noTransition !== undefined) return;
  let u; try { u = new URL(a.href, location.href); } catch (err) { return; }
  if (u.origin !== location.origin || !/^https?:$/.test(u.protocol)) return;
  if (/\/wp-(admin|login)|\/feed\/?$|\.(pdf|zip|jpe?g|png|webp|gif|svg|mp4|docx?|xlsx?)$/i.test(u.pathname)) return;
  if (u.pathname === location.pathname && u.search === location.search) {
    e.preventDefault();
    const el = u.hash && document.getElementById(u.hash.slice(1));
    const was = menu.classList.contains('open');
    closeMenu();
    setTimeout(() => scrollToEl(el || document.body), was ? 500 : 0);
    return;
  }
  e.preventDefault();
  go(u.href);
});
function go(url) {
  if (routing) return;
  routing = true;
  closeMenu(); closeDrawer(); toggleChat(false); closeCmd(); sfx('whoosh');
  try { sessionStorage.setItem('fera-nav', '1'); } catch (err) {}
  const leave = () => { location.href = url; };
  if (reduced) { leave(); return; }
  if (lenis) lenis.stop();
  gsap.timeline().set(curtain, { y: 0, yPercent: 100, visibility: 'visible' })
    .to(curtain, { yPercent: 0, duration: 0.6, ease: 'expo.inOut', onComplete: leave });
  setTimeout(leave, 1400);
}
addEventListener('pageshow', (e) => {
  if (!e.persisted) return;
  routing = false;
  gsap.set(curtain, { visibility: 'hidden', yPercent: 100 });
  if (lenis) lenis.start();
});
/* ---------- clock (Herndon, VA) ---------- */
function tickClock() {
  let t = '';
  try { t = new Intl.DateTimeFormat('en-US', { hour: 'numeric', minute: '2-digit', timeZone: 'America/New_York' }).format(new Date()); } catch (e) { t = ''; }
  const c1 = $('#clock1'); if (c1) c1.textContent = 'Herndon, VA, USA · ' + t;
  const c3 = $('#clock3'); if (c3) c3.textContent = 'Local time in Herndon, ' + t + ' ET';
}
tickClock(); setInterval(tickClock, 15000);

/* ---------- pointer ---------- */
const pointer = { x: 0, y: 0, active: false };
addEventListener('pointermove', (e) => {
  pointer.x = (e.clientX / innerWidth) * 2 - 1;
  pointer.y = -((e.clientY / innerHeight) * 2 - 1);
  pointer.active = true;
}, { passive: true });

/* ================= WebGL: particle logo ================= */
const VERT = `
attribute vec3 aLogo;
attribute vec3 aSphere;
attribute vec3 aWave;
attribute vec3 aGalaxy;
attribute vec3 aIcon;
uniform float uIc;
attribute vec3 aColor;
attribute float aRand;
uniform vec4 uW;
uniform float uTime;
uniform float uSize;
uniform float uTurb;
uniform float uPR;
uniform vec3 uMouse;
uniform float uMouseR;
uniform vec3 uRed;
uniform vec3 uHot;
varying vec3 vColor;
varying float vAlpha;
mat2 rot(float a){ float c = cos(a), s = sin(a); return mat2(c, -s, s, c); }
void main(){
  vec3 s = aSphere; s.xz = rot(uTime * 0.12) * s.xz; s.xy = rot(uTime * 0.05) * s.xy;
  vec3 g = aGalaxy; g.xz = rot(uTime * 0.035) * g.xz;
  vec3 w = aWave; w.y += sin(w.x * 1.1 + uTime * 0.9) * 0.28 + cos(w.z * 1.6 + uTime * 0.7) * 0.18;
  vec3 l = aLogo; l.z += sin(uTime * 1.2 + aRand * 20.0) * 0.03;
  vec3 ic = aIcon; ic.z += sin(uTime * 1.1 + aRand * 17.0) * 0.05;
  vec3 p = l * uW.x + s * uW.y + w * uW.z + g * uW.w + ic * uIc;
  float tt = uTime * 0.6 + aRand * 6.28318;
  p += vec3(sin(tt + p.y * 2.0), cos(tt * 1.3 + p.x * 2.0), sin(tt * 0.7 + p.z * 2.0)) * uTurb * (0.3 + aRand * 0.7);
  vec4 wp = modelMatrix * vec4(p, 1.0);
  vec2 d = wp.xy - uMouse.xy;
  float dist = length(d);
  float f = 1.0 - smoothstep(0.0, uMouseR, dist);
  wp.xy += (d / max(dist, 1e-4)) * f * 0.5;
  wp.z += f * 0.4;
  vec4 mv = viewMatrix * wp;
  gl_Position = projectionMatrix * mv;
  gl_PointSize = uSize * (0.55 + aRand * 0.9) * uPR * (8.0 / max(-mv.z, 0.5));
  vec3 base = mix(uRed, uHot, step(0.94, aRand));
  vColor = mix(base, aColor, uW.x * 0.85) * (1.0 + f * 1.6);
  vAlpha = 0.45 + 0.55 * aRand;
}`;
const FRAG = `
uniform float uDim;
uniform float uFade;
varying vec3 vColor;
varying float vAlpha;
void main(){
  float d = length(gl_PointCoord - 0.5);
  if (d > 0.5) discard;
  float a = 1.0 - smoothstep(0.0, 0.5, d);
  gl_FragColor = vec4(vColor, a * vAlpha * uDim * uFade);
}`;

const SCENES = {
  hero:     { w: [1, 0, 0, 0], x: 3.1, y: 0.2, z: 0, s: 0.8, dim: 1, r: 0, anchor: '#stage' },
  about:    { w: [0, 0, 0, 1], x: 0, y: 0, z: -0.5, s: 1.0, dim: 0.55, r: 0 },
  services: { w: [0, 1, 0, 0], x: 2.9, y: 0, z: -1.2, s: 1.0, dim: 0.5, r: 0 },
  work:     { w: [0, 0, 1, 0], x: 0, y: -1.6, z: 0, s: 1.0, dim: 0.65, r: 0 },
  process:  { w: [0, 1, 0, 0], x: -2.6, y: -0.2, z: -0.6, s: 0.9, dim: 0.55, r: 0 },
  why:      { w: [0, 0, 0, 1], x: 0, y: 0, z: -1, s: 1.1, dim: 0.35, r: 0 },
  cta:      { w: [1, 0, 0, 0], x: 2.5, y: 0.0, z: -0.4, s: 1.1, dim: 0.75, r: 0 },
  'pg-logo':   { w: [1, 0, 0, 0], x: 2.7, y: 0.1, z: 0, s: 0.95, dim: 0.95 },
  'pg-sphere': { w: [0, 1, 0, 0], x: 2.8, y: 0.15, z: -0.3, s: 1.15, dim: 0.9 },
  'pg-wave':   { w: [0, 0, 1, 0], x: 0, y: -1.4, z: 0, s: 1.0, dim: 0.75 },
  'pg-galaxy': { w: [0, 0, 0, 1], x: 1.2, y: 0, z: -0.3, s: 1.0, dim: 0.85 },
  'pg-prose': { w: [0, 0, 0, 1], x: 1.6, y: 0, z: -1.6, s: 1.0, dim: 0.28 },
  svc:      { w: [0, 0, 0, 0], ic: 1, x: 2.75, y: 0.15, z: 0, s: 1.0, dim: 1 },
  svcbody:  { w: [0, 1, 0, 0], x: 3.0, y: 0.2, z: -1.6, s: 1.0, dim: 0.38 },
  svcwave:  { w: [0, 0, 1, 0], x: 0, y: -1.7, z: 0, s: 1.0, dim: 0.6 },
  svcfaq:   { w: [0, 0, 0, 1], x: 0, y: 0, z: -1, s: 1.1, dim: 0.35 }
};

async function loadImage(src) {
  const im = new Image();
  im.src = src;
  await im.decode();
  return im;
}

async function initGL() {
  await loadThree();
  const canvas = $('#gl');
  let renderer;
  try { renderer = new THREE.WebGLRenderer({ canvas, antialias: false, powerPreference: 'default', failIfMajorPerformanceCaveat: true }); }
  catch (e) { return null; }
  const small = mobile();
  const lowEnd = (navigator.hardwareConcurrency || 8) <= 4 || (navigator.deviceMemory || 8) <= 4;
  const PR = Math.min(devicePixelRatio, small || lowEnd ? 1.25 : 1.5);
  // If the GPU drops the context, fall back to the static logo instead of a blank page.
  canvas.addEventListener('webglcontextlost', (ev) => {
    ev.preventDefault();
    try { renderer.setAnimationLoop(null); } catch (err) {}
    document.documentElement.classList.remove('gl-on');
    document.documentElement.classList.add('no-gl');
    try { localStorage.setItem('fera-gl-off', String(Date.now())); } catch (err) {}
  }, false);
  renderer.setPixelRatio(PR);
  renderer.setSize(innerWidth, innerHeight);

  const scene = new THREE.Scene();
  scene.background = new THREE.Color(0x070607);
  const camera = new THREE.PerspectiveCamera(35, innerWidth / innerHeight, 0.1, 100);
  camera.position.set(0, 0, 9);

  // ---- sample logo ----
  const N = small ? 9000 : (lowEnd ? 16000 : 24000);
  const img = await loadImage(MARK);
  const cw = 360, ch = Math.round(360 * img.height / img.width);
  const cv = document.createElement('canvas'); cv.width = cw; cv.height = ch;
  const cx = cv.getContext('2d', { willReadFrequently: true });
  cx.drawImage(img, 0, 0, cw, ch);
  const data = cx.getImageData(0, 0, cw, ch).data;
  const pts = [];
  for (let y = 0; y < ch; y++) for (let x = 0; x < cw; x++) {
    const i = (y * cw + x) * 4;
    if (data[i + 3] > 150 && data[i] > 70) { const light = data[i + 1] > 140; const reps = 1; for (let q = 0; q < reps; q++) pts.push(x, y, data[i], data[i + 1], data[i + 2]); }
  }
  const P = pts.length / 5;
  const H = 3.6, Wd = H * cw / ch;
  const LOGO_H = H, LOGO_W = Wd;
  const aLogo = new Float32Array(N * 3), aColor = new Float32Array(N * 3), aSphere = new Float32Array(N * 3), aWave = new Float32Array(N * 3), aGalaxy = new Float32Array(N * 3), aRand = new Float32Array(N);
  const lin = (c) => Math.pow(c / 255, 2.2);
  const gold = Math.PI * (3 - Math.sqrt(5));
  const GW = Math.ceil(Math.sqrt(N * 1.7)), GH = Math.ceil(N / GW);
  for (let i = 0; i < N; i++) {
    const k = Math.floor(Math.random() * P) * 5;
    aLogo[i * 3] = ((pts[k] + Math.random()) / cw - 0.5) * Wd;
    aLogo[i * 3 + 1] = -((pts[k + 1] + Math.random()) / ch - 0.5) * H;
    aLogo[i * 3 + 2] = (Math.random() - 0.5) * 0.18;
    const bo = pts[k + 3] > 140 ? 0.62 : (pts[k + 3] > 50 ? 1.1 : 2.2);
    aColor[i * 3] = Math.min(1.6, lin(pts[k + 2]) * bo);
    aColor[i * 3 + 1] = lin(pts[k + 3]) * bo;
    aColor[i * 3 + 2] = lin(pts[k + 4]) * bo;
    // sphere (fibonacci)
    const yy = 1 - (i / (N - 1)) * 2, rr = Math.sqrt(1 - yy * yy), th = gold * i, R = 1.75 + (Math.random() - 0.5) * 0.12;
    aSphere[i * 3] = Math.cos(th) * rr * R; aSphere[i * 3 + 1] = yy * R; aSphere[i * 3 + 2] = Math.sin(th) * rr * R;
    // wave grid
    const gx = i % GW, gy = Math.floor(i / GW);
    aWave[i * 3] = (gx / GW - 0.5) * 13; aWave[i * 3 + 1] = 0; aWave[i * 3 + 2] = (gy / GH - 0.5) * 7 - 0.5;
    // galaxy
    const arm = i % 3, rad = Math.pow(Math.random(), 1.6) * 6.2, ang = rad * 1.15 + arm * 2.0944 + (Math.random() - 0.5) * 0.55;
    aGalaxy[i * 3] = Math.cos(ang) * rad * 1.35; aGalaxy[i * 3 + 1] = (Math.random() - 0.5) * 0.35 * (1.2 - rad / 6.2) + Math.sin(ang) * rad * 0.18; aGalaxy[i * 3 + 2] = Math.sin(ang) * rad * 0.7 - 1;
    aRand[i] = Math.random();
  }
  const geo = new THREE.BufferGeometry();
  geo.setAttribute('position', new THREE.BufferAttribute(aLogo, 3));
  geo.setAttribute('aLogo', new THREE.BufferAttribute(aLogo, 3));
  geo.setAttribute('aSphere', new THREE.BufferAttribute(aSphere, 3));
  geo.setAttribute('aWave', new THREE.BufferAttribute(aWave, 3));
  geo.setAttribute('aGalaxy', new THREE.BufferAttribute(aGalaxy, 3));
  geo.setAttribute('aColor', new THREE.BufferAttribute(aColor, 3));
  geo.setAttribute('aRand', new THREE.BufferAttribute(aRand, 1));
  const aIcon = new Float32Array(aSphere);
  const iconAttr = new THREE.BufferAttribute(aIcon, 3); iconAttr.setUsage(THREE.DynamicDrawUsage);
  geo.setAttribute('aIcon', iconAttr);
  const uniforms = {
    uW: { value: new THREE.Vector4(0, 0, 0, 1) }, uTime: { value: 0 }, uSize: { value: small ? 2.0 : 2.1 }, uTurb: { value: 0.32 }, uPR: { value: PR },
    uMouse: { value: new THREE.Vector3(99, 99, 0) }, uMouseR: { value: 0.7 },
    uIc: { value: 0 }, uFade: { value: 1 }, uRed: { value: new THREE.Color('#D6141A') }, uHot: { value: new THREE.Color('#FFF1E8') }, uDim: { value: 1 }
  };
  const mat = new THREE.ShaderMaterial({ uniforms, vertexShader: VERT, fragmentShader: FRAG, transparent: true, depthWrite: false, blending: THREE.AdditiveBlending });
  const points = new THREE.Points(geo, mat);
  points.frustumCulled = false;
  const group = new THREE.Group();
  const ltex = new THREE.Texture(img); ltex.colorSpace = THREE.SRGBColorSpace; ltex.anisotropy = 4; ltex.needsUpdate = true;
  const lmat = new THREE.MeshBasicMaterial({ map: ltex, transparent: true, depthWrite: false, opacity: 0, toneMapped: false });
  const lplane = new THREE.Mesh(new THREE.PlaneGeometry(Wd, H), lmat);
  lplane.position.z = -0.03;
  group.add(points);

  scene.add(group);

  // faint dust
  const D = small ? 500 : 1200, dpos = new Float32Array(D * 3);
  for (let i = 0; i < D; i++) { dpos[i * 3] = (Math.random() - 0.5) * 24; dpos[i * 3 + 1] = (Math.random() - 0.5) * 14; dpos[i * 3 + 2] = -Math.random() * 10 - 1; }
  const dgeo = new THREE.BufferGeometry(); dgeo.setAttribute('position', new THREE.BufferAttribute(dpos, 3));
  const dust = new THREE.Points(dgeo, new THREE.PointsMaterial({ size: 0.03, color: new THREE.Color('#6e1d1d'), transparent: true, opacity: 0.8, depthWrite: false, blending: THREE.AdditiveBlending }));
  scene.add(dust);

  const composer = new EffectComposer(renderer);
  composer.setPixelRatio(Math.min(PR, 1.25));
  composer.setSize(innerWidth, innerHeight);
  composer.addPass(new RenderPass(scene, camera));
  const bloom = new UnrealBloomPass(new THREE.Vector2(innerWidth, innerHeight), 0.55, 0.5, 0.2);
  if (!small) composer.addPass(bloom);
  composer.addPass(new OutputPass());

  let heroFly = 0;
  let dragY = 0, dragX = 0, velY = 0, velX = 0, dragging = false, charge = 0, charging = false;
  let anchorSel = null, anchorEl = null;
  const cur = { x: SCENES.hero.x, y: SCENES.hero.y, z: -0.8, s: 1, dim: 0.35 }, tgt = { x: SCENES.hero.x, y: SCENES.hero.y, z: 0, s: 1, dim: 1 };
  const sm = { x: 0, y: 0 };
  const ray = new THREE.Raycaster(), plane = new THREE.Plane(new THREE.Vector3(0, 0, 1), 0), hit = new THREE.Vector3(), ndc = new THREE.Vector2();
  let vel = 0;
  const clock = new THREE.Clock();

  function frame() {
    const dt = Math.min(clock.getDelta(), 0.05);
    const t = clock.elapsedTime * (reduced ? 0.3 : 1);
    const e = 1 - Math.pow(0.03, dt);
    let anchored = false;
    if (anchorSel) {
      const el = anchorEl || (anchorEl = document.querySelector(anchorSel));
      if (el && el.offsetParent !== null) {
        const r = el.getBoundingClientRect();
        const vh = 2 * Math.tan(THREE.MathUtils.degToRad(camera.fov / 2)) * camera.position.z, vw = vh * camera.aspect;
        tgt.x = ((r.left + r.width / 2) / innerWidth * 2 - 1) * vw / 2;
        tgt.y = -((r.top + r.height / 2) / innerHeight * 2 - 1) * vh / 2;
        tgt.s = Math.min((r.height / innerHeight) * vh * 0.98 / LOGO_H, (r.width / innerWidth) * vw * 0.98 / LOGO_W) * (1 - charge * 0.14);
        anchored = true;
      }
    }
    for (const k in tgt) cur[k] += (tgt[k] - cur[k]) * e;
    const pe = 1 - Math.pow(0.004, dt);
    sm.x += ((fine && pointer.active ? pointer.x : Math.sin(t * 0.3) * 0.4) - sm.x) * pe;
    sm.y += ((fine && pointer.active ? pointer.y : Math.cos(t * 0.23) * 0.25) - sm.y) * pe;
    const v = lenis ? Math.min(Math.abs(lenis.velocity) * 0.01, 0.4) : 0;
    vel += (v - vel) * 0.08;

    const small2 = mobile();
    const mx = small2 ? 0 : 1;
    if (anchored) group.position.set(cur.x, cur.y, cur.z);
    else group.position.set(cur.x * mx, cur.y + (small2 && tgt.x !== 0 ? 0.8 : 0), cur.z);
    group.scale.setScalar(cur.s * (anchored ? 1 : (small2 ? 0.7 : 1)));
    if (!dragging) { dragY += velY; dragX += velX; velY *= 0.93; velX *= 0.93; dragY *= 0.985; dragX *= 0.96; }
    charge += ((charging ? 1 : 0) - charge) * (charging ? 0.035 : 0.2);
    group.rotation.y = sm.x * 0.35 + dragY;
    group.rotation.x = -sm.y * 0.2 + dragX;
    if (charging) uniforms.uTurb.value = Math.max(uniforms.uTurb.value, charge * 0.12);
    camera.position.x += (sm.x * 0.3 - camera.position.x) * 0.04;
    camera.position.y += (sm.y * 0.2 - camera.position.y) * 0.04;
    camera.lookAt(0, 0, 0);
    camera.updateMatrixWorld();

    if (fine && pointer.active) {
      ndc.set(pointer.x, pointer.y);
      ray.setFromCamera(ndc, camera);
      if (ray.ray.intersectPlane(plane, hit)) uniforms.uMouse.value.copy(hit);
    }
    uniforms.uTime.value = t;
    uniforms.uTurb.value += ((uniforms.uTurb.base || 0.015) + vel - uniforms.uTurb.value) * (clock.elapsedTime < 1.2 ? 0.12 : 0.05);
    uniforms.uDim.value = cur.dim;
    { const wl = uniforms.uW.value.x, tb = Math.max(0, Math.min(1, 1 - (uniforms.uTurb.value - 0.03) * 3));
      const to = wl * wl * wl * tb * 0.82 * Math.min(1, cur.dim) * (1 - charge);
      void to; uniforms.uFade.value = 1;
      }
    dust.rotation.y = t * 0.01;
    if (last === 'hero' && heroFly > 0.001) {
      const f = heroFly * heroFly;
      group.position.z += f * 6.5;
      group.rotation.y += f * 0.9;
      uniforms.uTurb.value = Math.max(uniforms.uTurb.value, f * 0.55);
      uniforms.uDim.value = cur.dim * (1 - f * 0.55);
    }
    bloom.strength = 0.55 * cur.dim + vel * 0.5 + (last === 'hero' ? heroFly * 0.5 : 0);
    composer.render();
  }
  const safeFrame = () => { if (document.hidden || innerWidth < 2 || innerHeight < 2) return; frame(); };
  renderer.setAnimationLoop(safeFrame);
  document.addEventListener('visibilitychange', () => { renderer.setAnimationLoop(document.hidden ? null : safeFrame); if (!document.hidden) clock.getDelta(); });

  addEventListener('resize', () => {
    if (innerWidth < 2 || innerHeight < 2) return;
    camera.aspect = innerWidth / innerHeight;
    camera.updateProjectionMatrix();
    renderer.setSize(innerWidth, innerHeight);
    composer.setSize(innerWidth, innerHeight);
  });

  let last = '', iconD = '', iconBusy = false;
  function sampleIcon(d) {
    const S = 256, c = document.createElement('canvas'); c.width = c.height = S;
    const x = c.getContext('2d', { willReadFrequently: true });
    x.scale(S / 64, S / 64); x.lineWidth = 3.6; x.lineCap = 'round'; x.lineJoin = 'round'; x.strokeStyle = '#fff';
    x.stroke(new Path2D(d));
    const px = x.getImageData(0, 0, S, S).data, list = [];
    for (let yy = 0; yy < S; yy++) for (let xx = 0; xx < S; xx++) if (px[(yy * S + xx) * 4 + 3] > 110) list.push(xx, yy);
    const M = list.length / 2;
    for (let i = 0; i < N; i++) {
      const k = Math.floor(Math.random() * M) * 2;
      aIcon[i * 3] = ((list[k] + Math.random()) / S - 0.5) * 3.7;
      aIcon[i * 3 + 1] = -((list[k + 1] + Math.random()) / S - 0.5) * 3.7;
      aIcon[i * 3 + 2] = (Math.random() - 0.5) * 0.28;
    }
    iconAttr.needsUpdate = true;
  }
  const api = {
    scene(name, force) {
      const s = SCENES[name];
      if (!s || (name === last && !force)) return;
      last = name;
      Object.assign(tgt, { x: s.x, y: s.y, z: s.z, s: s.s, dim: s.dim });
      anchorSel = s.anchor || null; anchorEl = null;
      const d = reduced ? 0.01 : 2.0;
      gsap.to(uniforms.uW.value, { x: s.w[0], y: s.w[1], z: s.w[2], w: s.w[3], duration: d, ease: 'power3.inOut', overwrite: true });
      if (!(name === 'svc' && iconBusy)) gsap.to(uniforms.uIc, { value: s.ic || 0, duration: d, ease: 'power3.inOut', overwrite: true });
      if (!reduced) uniforms.uTurb.value = 0.6;
    },
    heroOut(p) { heroFly = p; },
    burst(k) { if (!reduced) uniforms.uTurb.value = 0.5 + (k || 0.4); },
    drag(dx, dy) { dragging = true; velY = dx * 0.012; velX = dy * 0.008; dragY += velY; dragX = Math.max(-0.8, Math.min(0.8, dragX + velX)); },
    release() { dragging = false; },
    charge(on) { charging = on; },
    chargeLevel() { return charge; },
    async setIcon(d) {
      if (d === iconD) return;
      iconD = d;
      if (uniforms.uIc.value > 0.02) {
        iconBusy = true;
        gsap.to(uniforms.uIc, { value: 0, duration: 0.45, overwrite: true });
        gsap.to(uniforms.uW.value, { x: 0, y: 1, z: 0, w: 0, duration: 0.45, overwrite: true });
        await new Promise((r) => setTimeout(r, 480));
        iconBusy = false;
      }
      sampleIcon(d);
      if (last === 'svc') api.scene('svc', true);
    }
  };
  return api;
}

let gl = null;
let pendingScene = homeView ? 'hero' : ((svcView && $('[data-scene]', svcView)) || { dataset: {} }).dataset.scene || 'pg-logo';
if (svcView && svcView.dataset.icon) pendingIcon = svcView.dataset.icon;
function softwareGL() {
  try {
    const c = document.createElement('canvas');
    const g = c.getContext('webgl2') || c.getContext('webgl');
    if (!g) return true;
    const ext = g.getExtension('WEBGL_debug_renderer_info');
    const r = ext ? String(g.getParameter(ext.UNMASKED_RENDERER_WEBGL)) : '';
    const lose = g.getExtension('WEBGL_lose_context'); if (lose) lose.loseContext();
    return /swiftshader|llvmpipe|softpipe|software|basic render/i.test(r);
  } catch (err) { return true; }
}
const noGL = () => { document.documentElement.classList.add('no-gl'); return Promise.resolve(null); };
const glRecentlyLost = () => { try { const t = +localStorage.getItem('fera-gl-off') || 0; return Date.now() - t < 864e5; } catch (err) { return false; } };
const startGL = () => (((softwareGL() || glRecentlyLost()) && !/[?&]fera-gl=force/.test(location.search)) ? noGL() : initGL()).then((api) => {
  gl = api;
  if (!gl) { document.documentElement.classList.add('no-gl'); return; }
  requestAnimationFrame(() => requestAnimationFrame(() => document.documentElement.classList.add('gl-on')));
  if (pendingIcon) gl.setIcon(pendingIcon);
  gl.scene(pendingScene, true);
}).catch(() => document.documentElement.classList.add('no-gl'));
const idle = window.requestIdleCallback || ((f) => setTimeout(f, 120));
startGL();
function setScene(n, force) { pendingScene = n; if (gl) gl.scene(n, force); }

/* ================= Work horizontal scroll (create pins first) ================= */
const mm = gsap.matchMedia();
mm.add({ desk: '(min-width: 900px) and (prefers-reduced-motion: no-preference)' }, (ctx) => {
  if (!ctx.conditions.desk) return;
  const track = $('#workTrack');
  if (!track) return;
  const dist = () => Math.max(0, track.scrollWidth - innerWidth);
  const tw = gsap.to(track, { x: () => -dist(), ease: 'none', scrollTrigger: { trigger: '#workPin', start: 'center center', end: () => '+=' + dist(), pin: '#workPin', pinSpacing: true, scrub: 0.8, invalidateOnRefresh: true } });
  $$('[data-par]', track).forEach((ui) => {
    gsap.fromTo(ui, { xPercent: 8 }, { xPercent: -8, ease: 'none', scrollTrigger: { trigger: ui.closest('.case'), containerAnimation: tw, start: 'left right', end: 'right left', scrub: true } });
  });
});

/* ================= scene triggers ================= */
if (homeView) $$('[data-scene]', homeView).forEach((sec) => ScrollTrigger.create({ trigger: sec, start: 'top 55%', end: 'bottom 45%', onToggle: (s) => s.isActive && view === 'home' && setScene(sec.dataset.scene) }));

/* ================= text effects ================= */
const st = $('#statement');
const parts = [];
if (st) st.childNodes.forEach((n) => {
  if (n.nodeType === 3) n.textContent.split(/(\s+)/).forEach((tok) => { if (!tok) return; parts.push(/^\s+$/.test(tok) ? ' ' : '<span class="w">' + tok + '</span>'); });
  else if (n.nodeType === 1) { n.classList.add('w'); parts.push(n.outerHTML); }
});
if (st) st.innerHTML = parts.join('');

function scramble(el) {
  const final = el.textContent, chars = '!<>-_\\/[]{}, =+*^?#ABCDEFX0123456789';
  let f = 0; const total = 28;
  const id = setInterval(() => {
    f++;
    el.textContent = final.split('').map((c, i) => (c === ' ' ? ' ' : i / final.length < f / total ? c : chars[(Math.random() * chars.length) | 0])).join('');
    if (f >= total) { clearInterval(id); el.textContent = final; }
  }, 32);
}

if (!reduced) {
  if (st) gsap.fromTo($$('.w', st), { opacity: 0.5 }, { opacity: 1, stagger: 0.05, ease: 'none', scrollTrigger: { trigger: st, start: 'top 80%', end: 'bottom 50%', scrub: true } });
  if (homeView) $$('[data-split]', homeView).forEach((h) => gsap.from($$('.line > span', h), { yPercent: 118, rotate: 2.5, duration: 1.4, ease: 'expo.out', stagger: 0.09, scrollTrigger: { trigger: h, start: 'top 86%' } }));
  if (homeView) $$('[data-reveal]', homeView).forEach((el) => gsap.from(el, { y: 60, opacity: 0, duration: 1.2, ease: 'power3.out', scrollTrigger: { trigger: el, start: 'top 90%', onEnter: () => el.classList.add('in') } }));
  if ($('#svcList')) gsap.from('.svc-row', { y: 40, opacity: 0, duration: 1, ease: 'power3.out', stagger: 0.07, scrollTrigger: { trigger: '#svcList', start: 'top 85%' } });
  gsap.to('#wmFill', { clipPath: 'inset(0 0% 0 0)', ease: 'none', scrollTrigger: { trigger: 'footer', start: 'top 70%', end: 'bottom bottom', scrub: true } });
  gsap.from('#wordmark', { yPercent: 40, ease: 'none', scrollTrigger: { trigger: 'footer', start: 'top bottom', end: 'bottom bottom', scrub: true } });
  if (lenis) {
    const skews = $$('.skew').map((el) => gsap.quickTo(el, 'skewX', { duration: 0.5, ease: 'power3' }));
    lenis.on('scroll', (e) => { const v = gsap.utils.clamp(-12, 12, e.velocity * -0.4); skews.forEach((f) => f(v)); });
  }
} else {
  $$('[data-reveal]').forEach((el) => el.classList.add('in'));
  $('#wmFill').style.clipPath = 'none';
}

/* process steps */
const steps = $$('.step'), procNum = $('#procNum'), procLine = $('#procLine');
steps.forEach((s, i) => ScrollTrigger.create({
  trigger: s, start: 'top 60%', end: 'bottom 40%',
  onToggle: (st2) => {
    if (!st2.isActive) return;
    steps.forEach((x, k) => x.classList.toggle('on', k === i));
    procNum.textContent = String(i + 1).padStart(2, '0');
    if (!reduced) gsap.fromTo(procNum, { yPercent: 100 }, { yPercent: 0, duration: 0.8, ease: 'expo.out' });
    procLine.style.transform = 'scaleX(' + (i + 1) / steps.length + ')';
  }
}));
if (steps[0]) steps[0].classList.add('on');

/* nav + progress */
const nav = $('#nav'), prog = $('#progress');
ScrollTrigger.create({
  start: 0, end: 'max',
  onUpdate: (s) => {
    const y = s.scroll();
    nav.classList.toggle('scrolled', y > 60);
    nav.classList.toggle('hide', y > 500 && s.direction === 1 && !menu.classList.contains('open'));
    prog.style.transform = 'scaleX(' + s.progress + ')';
  }
});

/* ================= services: accordion + floating preview ================= */
const rows = $$('.svc-row'), preview = $('#preview'), pvTrack = $('#pvTrack');
rows.forEach((row) => {
  const btn = $('.svc-btn', row);
  btn.addEventListener('click', () => {
    const open = !row.classList.contains('open');
    rows.forEach((r) => { r.classList.remove('open'); $('.svc-btn', r).setAttribute('aria-expanded', 'false'); });
    if (open) { row.classList.add('open'); btn.setAttribute('aria-expanded', 'true'); }
    setTimeout(() => ScrollTrigger.refresh(), 750);
  });
});
if (fine && !reduced) {
  let px = 0, py = 0, qx = 0, qy = 0, shown = false;
  const showPv = (on) => { if (on === shown) return; shown = on; gsap.to(preview, { scale: on ? 1 : 0, opacity: on ? 1 : 0, duration: 0.6, ease: 'expo.out' }); };
  addEventListener('pointermove', (e) => { px = e.clientX; py = e.clientY; }, { passive: true });
  gsap.ticker.add(() => {
    qx += (px - qx) * 0.12; qy += (py - qy) * 0.12;
    const rot = gsap.utils.clamp(-12, 12, (px - qx) * 0.08);
    preview.style.transform = `translate(${qx + 200}px, ${qy}px) rotate(${rot}deg) scale(${gsap.getProperty(preview, 'scale')})`;
  });
  rows.forEach((row) => {
    $('.svc-btn', row).addEventListener('pointerenter', () => { pvTrack.style.transform = `translateY(${-100 * +row.dataset.i}%)`; showPv(true); });
    $('.svc-btn', row).addEventListener('pointerleave', () => showPv(false));
  });
}

/* ================= cursor, magnetic, tilt ================= */
if (fine) {
  document.body.classList.add('has-cursor');
  const cEl = $('#cursor'), dot = $('.c-dot'), ring = $('.c-ring'), lbl = $('#cLabel');
  let mx = -100, my = -100, rx = -100, ry = -100;
  addEventListener('pointermove', (e) => { mx = e.clientX; my = e.clientY; dot.style.transform = `translate(${mx}px,${my}px)`; }, { passive: true });
  gsap.ticker.add(() => { rx += (mx - rx) * 0.16; ry += (my - ry) * 0.16; ring.style.transform = `translate(${rx}px,${ry}px)`; });
  document.addEventListener('pointerover', (e) => {
    const lab = e.target.closest('[data-cursor]');
    const hov = e.target.closest('a,button,label,input,textarea');
    const isLab = !!lab && !(e.target.closest('input,textarea'));
    cEl.classList.toggle('label', isLab);
    if (lab) lbl.textContent = lab.dataset.cursor;
    cEl.classList.toggle('hover', !!hov && !isLab);
  });
}
function bindInteractive(root) {
  if (!fine) return;
  if (!reduced) $$('.magnetic', root).forEach((el) => {
    if (el._mag) return; el._mag = 1;
    const k = parseFloat(el.dataset.strength || '0.3');
    el.addEventListener('pointermove', (e) => { const r = el.getBoundingClientRect(); gsap.to(el, { x: (e.clientX - r.left - r.width / 2) * k, y: (e.clientY - r.top - r.height / 2) * k, duration: 0.5, ease: 'power3.out' }); });
    el.addEventListener('pointerleave', () => gsap.to(el, { x: 0, y: 0, duration: 1, ease: 'elastic.out(1,0.4)' }));
  });
  $$('.tile, .inc', root).forEach((t) => t.addEventListener('pointermove', (e) => { const r = t.getBoundingClientRect(); t.style.setProperty('--mx', (e.clientX - r.left) + 'px'); t.style.setProperty('--my', (e.clientY - r.top) + 'px'); }));
}
bindInteractive(document);

/* ================= menu / drawer / chat ================= */
function openMenu() {
  menu.classList.add('open'); menuBtn.setAttribute('aria-expanded', 'true'); menuBtn.setAttribute('aria-label', 'Close menu');
  if (lenis) lenis.stop();
  if (!reduced) gsap.fromTo($$('.m-link > span'), { yPercent: 110 }, { yPercent: 0, duration: 1.1, ease: 'expo.out', stagger: 0.06, delay: 0.3 });
}
function closeMenu() {
  if (!menu.classList.contains('open')) return;
  menu.classList.remove('open'); menuBtn.setAttribute('aria-expanded', 'false'); menuBtn.setAttribute('aria-label', 'Open menu');
  if (lenis && !drawer.classList.contains('open')) lenis.start();
}
menuBtn.addEventListener('click', () => (menu.classList.contains('open') ? closeMenu() : openMenu()));
function openDrawer() {
  closeMenu();
  drawer.classList.add('open'); scrim.classList.add('open');
  if (lenis) lenis.stop();
  setTimeout(() => { const f = $('input[name="name"]', drawer); if (f) f.focus({ preventScroll: true }); }, 700);
}
function closeDrawer() { drawer.classList.remove('open'); scrim.classList.remove('open'); if (lenis && !menu.classList.contains('open')) lenis.start(); }
document.addEventListener('click', (e) => { if (e.target.closest('[data-open-contact]')) openDrawer(); });
$('#drClose').addEventListener('click', closeDrawer);
scrim.addEventListener('click', closeDrawer);

const chat = $('#chat'), fab = $('#chatFab'), msgs = $('#msgs');
function toggleChat(force) {
  const open = typeof force === 'boolean' ? force : !chat.classList.contains('open');
  chat.classList.toggle('open', open);
  fab.setAttribute('aria-expanded', String(open));
  if (open) setTimeout(() => $('#chatIn').focus({ preventScroll: true }), 300);
}
const hasTawk = () => window.Tawk_API && typeof window.Tawk_API.maximize === 'function';
fab.addEventListener('click', () => toggleChat());
function openChat() { toggleChat(true); }
function talkToAgent() {
  if (hasTawk()) {
    toggleChat(false);
    window.feraAgentOpen = true;
    try { window.Tawk_API.showWidget(); window.Tawk_API.maximize(); } catch (err) {}
    return;
  }
  addMsg('Our team is happy to help. Call 337-252-4040 or email sales@ferasolution.com and a person will get back to you within one business day.', 'bot');
}
document.addEventListener('click', (e) => { if (e.target.closest('[data-agent]')) { e.preventDefault(); talkToAgent(); } });
document.addEventListener('keydown', (e) => { if (e.key === 'Escape') { closeDrawer(); closeMenu(); toggleChat(false); } });
function addMsg(text, who, agentBtn) {
  const d = document.createElement('div'); d.className = 'msg ' + who; d.textContent = text;
  if (agentBtn) { const b = document.createElement('button'); b.type = 'button'; b.className = 'msg-agent'; b.setAttribute('data-agent', ''); b.textContent = 'Talk to an agent'; d.appendChild(document.createElement('br')); d.appendChild(b); }
  msgs.appendChild(d); msgs.scrollTop = msgs.scrollHeight; return d;
}
const REPLIES = [
  [/human|agent|person|someone|representative|real person|call me/i, 'Sure, tap the button below and a member of our team will take it from here.', true],
  [/secur|hack|breach|pen ?test|cyber/i, 'Security is where we started. We can begin with a risk assessment of your website and systems, then a remediation plan. Want our team to reach out?'],
  [/store|shop|e-?com|woo|product/i, 'We build premium, fast online stores on Shopify, WooCommerce or fully custom, payments and shipping included. Roughly how many products do you sell?'],
  [/social|instagram|facebook|tiktok|linkedin/i, 'Our social team handles posting, community replies and monthly reports. Which platforms are you on today?'],
  [/logo|brand|design|graphic/i, 'Our designers create logos, full brand identities and campaign visuals. Are we refreshing an existing brand or starting fresh?'],
  [/content|blog|copy|writ/i, 'We write website copy, SEO blogs and scripts in your voice. How many pieces a month are you thinking?'],
  [/seo|market|ads|google|lead/i, 'We run SEO plus Google and Meta campaigns with clear monthly reporting. Is your main goal leads, sales or awareness?'],
  [/app|software|saas|portal|system|dashboard|automat/i, 'We build custom software, dashboards and automation. Tell me a bit about the idea and who will use it.'],
  [/web|site|3d|landing|wordpress/i, 'A website like this one (3D, motion and fast) or a clean WordPress business site. Do you have a current site we should look at?'],
  [/price|cost|budget|quote|rate|pricing/i, 'Every project is scoped individually. Share a few details via "Start a project" or email sales@ferasolution.com and we will send a quote within one business day.']
];
const chatLog = [];
let aiBusy = false;
function localReply(text) {
  const hit = REPLIES.find(([re]) => re.test(text));
  return hit ? { text: hit[1], agent: !!hit[2] } : { text: 'Thanks! I can help with websites, software, security, marketing, design, content, e-commerce and social media. If you would rather speak to a person, tap “Talk to an agent”.', agent: true };
}
async function botReply(text) {
  if (aiBusy) return;
  aiBusy = true;
  chatLog.push({ role: 'user', content: text.slice(0, 1000) });
  const typing = document.createElement('div'); typing.className = 'msg bot typing'; typing.innerHTML = '<i></i><i></i><i></i>';
  msgs.appendChild(typing); msgs.scrollTop = msgs.scrollHeight;
  let out = null;
  if (FERA.ai) {
    try {
      const r = await fetch((FERA.rest || '/wp-json/') + 'fera/v1/chat', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ messages: chatLog.slice(-12), page: location.pathname }) });
      const j = await r.json();
      if (r.ok && j.ok && j.reply) out = { text: j.reply, agent: !!j.agent };
    } catch (err) {}
  }
  if (!out) { await new Promise((res) => setTimeout(res, 700 + Math.random() * 500)); out = localReply(text); }
  typing.remove();
  chatLog.push({ role: 'assistant', content: out.text });
  addMsg(out.text, 'bot', out.agent);
  aiBusy = false;
}
$('#chatForm').addEventListener('submit', (e) => { e.preventDefault(); const inp = $('#chatIn'); const t = inp.value.trim(); if (!t || aiBusy) return; addMsg(t, 'me'); inp.value = ''; botReply(t); });
$$('#quick button:not([data-agent])').forEach((b) => b.addEventListener('click', () => { if (aiBusy) return; addMsg(b.textContent, 'me'); botReply(b.textContent); }));


/* ================= service pages ================= */
const SERVICES = [
  { slug: 'cybersecurity', name: 'Cybersecurity', t1: 'Protect what', t2: "you've", acc: 'built.', tags: 'Assess · Protect · Remediate',
    icon: 'M32 6 10 14v16c0 13 9.4 24.6 22 28 12.6-3.4 22-15 22-28V14L32 6Z M27 28a5 5 0 1 0 10 0a5 5 0 1 0-10 0 M32 33v9',
    lead: 'Risk assessments, penetration testing and hardening that find the gaps before attackers do, explained in plain language, with a clear plan to fix them.',
    inc: [['Risk assessment', 'A clear picture of your exposure across website, cloud, devices and people.'], ['Vulnerability scanning', 'Automated and manual checks of your apps, servers and network.'], ['Penetration testing', 'Ethical, controlled attacks that prove what is really exploitable.'], ['Hardening & remediation', 'Prioritised fixes, configuration changes and patching guidance.'], ['Website security', 'SSL, security headers, firewall, backups and malware protection.'], ['Awareness training', 'Short, practical sessions so your team can spot phishing and scams.']],
    steps: [['Scope', 'We agree on the systems to test, the goals and the timing.'], ['Assess', 'We scan, test and probe for weaknesses, carefully and safely.'], ['Report', 'You get prioritised findings in plain language, not jargon.'], ['Remediate', 'We help fix every issue, then re-test to confirm it is closed.']],
    tools: ['OWASP Top 10', 'Nmap', 'Burp Suite', 'Cloudflare WAF', 'SSL / TLS', 'Wordfence', 'MFA', 'Backups'],
    faq: [['Is my business too small to be a target?', 'No. Small businesses are often targeted precisely because they have fewer defences. A short assessment shows exactly where you stand.'], ['Will testing disrupt my website?', 'We agree on scope and timing in advance and test carefully, so your normal operations continue.'], ['What do I get at the end?', 'A plain-language report with prioritised findings and a remediation plan, plus our help fixing them if you want it.']] },
  { slug: 'web-development', name: 'Website Development', t1: 'Websites that', t2: '', acc: 'perform.', tags: 'WordPress · React · 3D',
    icon: 'M11 12h42a5 5 0 0 1 5 5v28a5 5 0 0 1-5 5H11a5 5 0 0 1-5-5V17a5 5 0 0 1 5-5Z M6 21h52 M26 29l-7 7 7 7 M38 29l7 7-7 7',
    lead: 'Distinctive, fast websites, from WordPress business sites to immersive 3D experiences like this one, built to turn visitors into customers.',
    inc: [['Business websites', 'Professional sites that explain what you do and why customers should choose you.'], ['WordPress development', 'Custom themes your team can edit without touching code.'], ['3D & motion', 'Three.js, WebGL and GSAP experiences that make your brand unforgettable.'], ['Landing pages', 'Focused pages built for campaigns, launches and lead generation.'], ['Redesigns', 'A modern refresh of your existing site, keeping what already works.'], ['Speed & SEO', 'Core Web Vitals, clean structure and on-page SEO from day one.']],
    steps: [['Discover', 'Goals, audience, competitors and the content you need.'], ['Design', 'Interactive prototypes with real motion for you to approve.'], ['Develop', 'Clean, responsive build with weekly previews.'], ['Launch', 'Secure go-live, analytics, training and ongoing support.']],
    tools: ['WordPress', 'React', 'Next.js', 'Three.js', 'GSAP', 'Tailwind CSS', 'Figma', 'Cloudflare'],
    faq: [['Can I edit the website myself?', 'Yes. We build on WordPress or a headless CMS so your team can update text, images and pages without code.'], ['How long does a website take?', 'It depends on scope. Once we understand your needs, we give you a clear timeline before any work starts.'], ['Do 3D animations slow the site down?', 'We load heavy effects only when needed and optimise everything, so the site stays fast on phones and desktops.']] },
  { slug: 'software-development', name: 'Software Development', t1: 'Software built', t2: 'around your', acc: 'work.', tags: 'Apps · Dashboards · Automation',
    icon: 'M32 8 56 20 32 32 8 20Z M8 32l24 12 24-12 M8 44l24 12 24-12',
    lead: 'Custom software, dashboards, workflow automation and integrations, built around the way your business actually works, not the other way round.',
    inc: [['Custom web applications', 'Secure, scalable apps designed around your exact workflow.'], ['Dashboards & portals', 'Customer, staff and admin portals with the data you need at a glance.'], ['Workflow automation', 'Replace repetitive manual work with reliable automated processes.'], ['API integrations', 'Connect your CRM, payments, accounting and other tools.'], ['SaaS products', 'Multi-tenant platforms ready to sell to your own customers.'], ['Mobile apps', 'Cross-platform apps for iOS and Android with React Native.']],
    steps: [['Discovery', 'We map your processes, users and must-have features.'], ['Prototype', 'Clickable screens to validate the idea before building.'], ['Build', 'Short sprints with working software you can test every week.'], ['Launch & support', 'Deployment, documentation, training and continued improvements.']],
    tools: ['Laravel', 'Node.js', 'React', 'React Native', 'PostgreSQL', 'MySQL', 'REST APIs', 'GitHub Actions'],
    faq: [['Do I own the code?', 'Yes. You own the code and the data, with documentation so any developer can work on it.'], ['Can you connect our existing tools?', 'In most cases, yes, we integrate with CRMs, payment providers, accounting tools and other systems through their APIs.'], ['What happens after launch?', 'We offer ongoing support, updates and new features as your business grows.']] },
  { slug: 'digital-marketing', name: 'Digital Marketing', t1: 'Growth you', t2: 'can', acc: 'measure.', tags: 'SEO · Ads · Analytics',
    icon: 'M8 54h48 M12 44l12-12 10 8 18-20 M42 20h10v10',
    lead: 'SEO, paid campaigns and analytics working as one engine that turns traffic into qualified leads, with reporting you can actually read.',
    inc: [['Technical SEO', 'Site structure, speed and indexing fixed so search engines love you.'], ['Local SEO', 'Google Business Profile and local rankings for customers near you.'], ['Google Ads', 'Search and display campaigns focused on real enquiries.'], ['Meta Ads', 'Facebook and Instagram campaigns that reach the right people.'], ['Conversion tracking', 'Know exactly which channels bring calls, forms and sales.'], ['Monthly reporting', 'Clear reports with results and what we are doing next.']],
    steps: [['Audit', 'We review your site, rankings, ads and analytics.'], ['Strategy', 'Channels, budget split and goals agreed together.'], ['Launch', 'Campaigns and SEO work go live with full tracking.'], ['Optimise', 'We test, learn and improve every single month.']],
    tools: ['Google Analytics 4', 'Search Console', 'Google Ads', 'Meta Ads', 'Tag Manager', 'Semrush', 'Looker Studio', 'Hotjar'],
    faq: [['How soon will I see results?', 'Paid campaigns can bring traffic quickly; SEO builds steadily over months. We set realistic expectations up front.'], ['Who owns the ad accounts?', 'You do. Campaigns run in your own accounts, so you keep full access and history.'], ['What will I see each month?', 'A clear report showing traffic, leads, spend and what we are changing next.']] },
  { slug: 'graphic-design', name: 'Graphic Design', t1: 'Brands people', t2: '', acc: 'remember.', tags: 'Logos · Identity · Creatives',
    icon: 'M32 6 46 30 32 58 18 30Z M28 32a4 4 0 1 0 8 0a4 4 0 1 0-8 0 M32 6v22',
    lead: 'Logos, brand identities and campaign visuals with a clear point of view, designed to be remembered and built to work everywhere.',
    inc: [['Logo design', 'A distinctive mark that works from favicon to billboard.'], ['Brand identity', 'Colours, typography and visual language that feel like you.'], ['Brand guidelines', 'A simple guide so everyone uses your brand correctly.'], ['Social creatives', 'Post and story templates that stay on-brand.'], ['Ad creatives', 'Scroll-stopping visuals for Google, Meta and display ads.'], ['Print collateral', 'Business cards, brochures, flyers and signage.']],
    steps: [['Brief', 'Your story, audience, competitors and personality.'], ['Concepts', 'Several creative directions to choose from.'], ['Refine', 'We perfect the chosen direction with your feedback.'], ['Deliver', 'Final files in every format, plus guidelines.']],
    tools: ['Figma', 'Illustrator', 'Photoshop', 'InDesign', 'After Effects', 'Canva templates'],
    faq: [['How does the logo process work?', 'We explore several directions first, then refine the one you love until it is exactly right.'], ['What files will I receive?', 'Everything you need: vector and web formats, colour and single-colour versions, and guidelines for using them.'], ['Can you refresh my existing brand?', 'Yes. We can modernise what you have while keeping the recognition you have already built.']] },
  { slug: 'content-writing', name: 'Content Writing', t1: 'Words that', t2: '', acc: 'convert.', tags: 'Copy · Blogs · Scripts',
    icon: 'M16 6h22l10 10v42H16Z M38 6v10h10 M23 28h18 M23 36h18 M23 44h11',
    lead: 'Website copy, blogs and scripts that sound like you, speak to your customers and rank on search.',
    inc: [['Website copy', 'Clear, persuasive pages that explain your value in seconds.'], ['SEO blog articles', 'Researched articles that answer real questions and rank.'], ['Product descriptions', 'Benefit-led descriptions that help products sell.'], ['Email campaigns', 'Newsletters and sequences that people actually open.'], ['Video & ad scripts', 'Short, punchy scripts for reels, YouTube and ads.'], ['Case studies', 'Stories that prove your results to new customers.']],
    steps: [['Research', 'Your voice, audience, keywords and competitors.'], ['Outline', 'Structure and key messages agreed before writing.'], ['Write', 'Original drafts written for humans and search engines.'], ['Edit & optimise', 'Polished, proofread and optimised for SEO.']],
    tools: ['Search Console', 'Semrush', 'Surfer SEO', 'Grammarly', 'Google Docs', 'WordPress'],
    faq: [['Will the content sound like my brand?', 'Yes. We start by learning your voice, audience and goals, and every piece is edited to match.'], ['Is the content original?', 'Every piece is written specifically for you and checked before delivery.'], ['Do you handle SEO?', 'Yes, keyword research, structure, meta titles and internal links are part of every article.']] },
  { slug: 'ecommerce', name: 'E-Commerce Solutions', t1: 'Online stores', t2: 'that', acc: 'sell.', tags: 'Shopify · WooCommerce · Custom',
    icon: 'M13 22h38l-3 34H16Z M24 26v-8a8 8 0 0 1 16 0v8',
    lead: 'Online stores that look premium, load fast and convert, from product catalogue to checkout, payments and shipping.',
    inc: [['Shopify stores', 'Beautiful, reliable stores on the world’s leading platform.'], ['WooCommerce stores', 'Flexible stores on WordPress with full control.'], ['Custom storefronts', 'Bespoke shopping experiences for unique products.'], ['Payments & shipping', 'Stripe, PayPal, taxes, shipping rates and delivery set up.'], ['Product setup', 'Catalogue, variants, images and descriptions organised.'], ['Conversion optimisation', 'Faster pages and smoother checkout for more sales.']],
    steps: [['Plan', 'Products, platform, payments and shipping decided.'], ['Design', 'A storefront that reflects your brand and builds trust.'], ['Build', 'Store, integrations and products set up and tested.'], ['Launch & grow', 'Go live, then marketing, SEO and optimisation.']],
    tools: ['Shopify', 'WooCommerce', 'Stripe', 'PayPal', 'Klaviyo', 'Merchant Center', 'Meta Shop', 'Cloudflare'],
    faq: [['Shopify or WooCommerce?', 'It depends on your products, budget and how much control you want. We recommend the right fit for you.'], ['Can you move my existing store?', 'Yes. We migrate products, customers and orders carefully to protect your sales and SEO.'], ['Can I manage products myself?', 'Absolutely. We set everything up so you can add products, update prices and manage orders easily.']] },
  { slug: 'social-media', name: 'Social Account Management', t1: 'Social that', t2: '', acc: 'connects.', tags: 'Posting · Community · Reports',
    icon: 'M8 12h36v26H24l-10 9v-9H8Z M26 21c-2-3.5-7-3-7 1 0 3.5 7 8 7 8s7-4.5 7-8c0-4-5-4.5-7-1Z M50 22h6v26h-6v8l-9-8H30v-4',
    lead: 'Consistent posting, community care and clear reporting across every platform you are on, so your brand stays active while you run the business.',
    inc: [['Strategy & calendar', 'A monthly plan of themes, formats and posting times.'], ['Content creation', 'On-brand graphics, reels, captions and hashtags.'], ['Posting & scheduling', 'Consistent publishing across all your platforms.'], ['Community management', 'Replies to comments and messages in your voice.'], ['Profile optimisation', 'Bios, highlights and links set up to convert.'], ['Monthly reports', 'Reach, engagement and growth explained simply.']],
    steps: [['Audit', 'Your profiles, audience and competitors reviewed.'], ['Plan', 'A content calendar you approve before anything posts.'], ['Publish', 'Content created, scheduled and posted consistently.'], ['Report', 'Monthly results and next month’s improvements.']],
    tools: ['Instagram', 'Facebook', 'LinkedIn', 'TikTok', 'X', 'YouTube', 'Meta Business Suite', 'Canva'],
    faq: [['Which platforms do you manage?', 'Instagram, Facebook, LinkedIn, TikTok, X and YouTube, we focus on the ones where your customers are.'], ['Do I approve posts before they go live?', 'Yes. You review the monthly calendar and approve content before anything is published.'], ['Do you reply to comments and messages?', 'Yes, community management can be included so your audience always gets a response.']] }
];
const SVC = {};
SERVICES.forEach((s, i) => { s.i = i; SVC[s.slug] = s; });
const esc = (t) => String(t).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
const formStart = Date.now();
const labelOf = (inp) => { const s = inp.nextElementSibling; return (s ? s.textContent : inp.value).trim(); };
function markNav() { const k = document.body.dataset.nav || ROUTE; $$('.nav-links a[data-page]').forEach((a) => a.classList.toggle('on', a.dataset.page === k)); }
function setCap(f, j) {
  if (!f || !j) return;
  f.dataset.capT = j.t || '';
  const q = $('[data-cap-q]', f); if (q) q.textContent = j.q || '';
  if (f.elements.cap) f.elements.cap.value = '';
}
async function loadCap(f) {
  if (!f || !$('[data-captcha]', f)) return;
  try {
    const r = await fetch((FERA.rest || '/wp-json/') + 'fera/v1/captcha?_=' + Date.now(), { cache: 'no-store' });
    setCap(f, await r.json());
  } catch (err) { const q = $('[data-cap-q]', f); if (q) q.textContent = '-'; }
}
$$('[data-fera-form]').forEach((f) => {
  let done = false;
  const go = () => { if (done) return; done = true; loadCap(f); };
  f.addEventListener('focusin', go); f.addEventListener('pointerdown', go);
  if ('IntersectionObserver' in window) { const io = new IntersectionObserver((es) => { if (es.some((x) => x.isIntersecting)) { go(); io.disconnect(); } }); io.observe(f); } else go();
  f.addEventListener('input', (e) => { const b = e.target.closest('.field, fieldset'); if (b) b.classList.remove('bad'); });
  f.addEventListener('change', (e) => { const b = e.target.closest('.field, fieldset'); if (b) b.classList.remove('bad'); });
});
document.addEventListener('click', (e) => { const b = e.target.closest('[data-cap-new]'); if (b) { e.preventDefault(); loadCap(b.closest('form')); } });
document.addEventListener('change', (e) => {
  const t = e.target;
  if (t.matches && t.matches('input[data-other-svc]')) {
    const box = t.closest('fieldset').querySelector('.other-svc');
    if (box) { box.hidden = !t.checked; const i = box.querySelector('input'); if (t.checked && i) setTimeout(() => i.focus(), 30); if (!t.checked && i) i.value = ''; }
  }
  if (t.name === 'budget' && t.checked) { const own = t.form && t.form.elements.budget_own; if (own) own.value = ''; }
});
document.addEventListener('input', (e) => {
  const t = e.target;
  if (t.name === 'budget_own') { const d = t.value.replace(/[^0-9]/g, '').slice(0, 9); const f2 = d ? Number(d).toLocaleString('en-US') : ''; if (t.value !== f2) t.value = f2; }
  if (t.name === 'budget_own' && t.value.trim()) { $$('input[name="budget"]', t.form).forEach((r) => { r.checked = false; }); }
});
document.addEventListener('submit', async (e) => {
  const f = e.target.closest('[data-fera-form]');
  if (!f) return;
  e.preventDefault();
  const note = $('.form-note', f), btn = $('button[type="submit"]', f);
  const say = (msg, bad) => { if (!note) return; note.textContent = msg; note.classList.toggle('err', !!bad); note.classList.add('show'); };
  const val = (n) => ((f.elements[n] && f.elements[n].value) || '').trim();
  const boxOf = (n) => { if (n === 'services') { const i = $('input[name="svc"]', f); return i && i.closest('fieldset'); } if (n === 'budget') { const i = $('input[name="budget"]', f); return i && i.closest('fieldset'); } if (n === 'other_service') { const i = f.elements.other_service; return i && i.closest('.field'); } const el = f.elements[n === 'msg' ? 'msg' : n]; return el && (el.closest('.field') || el); };
  const flag = (list) => { $$('.bad', f).forEach((x) => x.classList.remove('bad')); list.forEach((n) => { const b = boxOf(n); if (b) b.classList.add('bad'); }); const first = list.length && boxOf(list[0]); if (first) { const inp = first.querySelector ? (first.querySelector('input, textarea') || first) : first; try { inp.focus({ preventScroll: false }); } catch (err) {} } };
  const hasBudget = !!$('input[name="budget"]', f);
  const data = {
    name: val('name'), email: val('email'), phone: val('phone'), company: val('company'), message: val('msg'),
    services: $$('input[name="svc"]:checked', f).map(labelOf),
    budget: (($('input[name="budget"]:checked', f)) ? labelOf($('input[name="budget"]:checked', f)) : ''),
    needs_budget: hasBudget, budget_own: val('budget_own'), other_service: val('other_service'), cap: val('cap'), cap_t: f.dataset.capT || '',
    website_hp: val('website_hp'), elapsed: Date.now() - formStart, page: location.href
  };
  const missing = [];
  if (!data.services.length) missing.push('services');
  const wantsOther = !!$('input[data-other-svc]:checked', f);
  if (wantsOther && !data.other_service) missing.push('other_service');
  if (hasBudget && !data.budget && !data.budget_own) missing.push('budget');
  if (!data.name) missing.push('name');
  if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(data.email)) missing.push('email');
  if (data.phone.replace(/\D/g, '').length < 7) missing.push('phone');
  if (data.message.length < 10) missing.push('msg');
  if (!data.cap) missing.push('cap');
  if (missing.length) { flag(missing); say('Please complete all required fields (marked *), including the quick check.', true); return; }
  if (!data.cap_t) { await loadCap(f); flag(['cap']); say('Please answer the quick check question.', true); return; }
  if (btn) btn.disabled = true;
  say('Sending…');
  try {
    const r = await fetch((FERA.rest || '/wp-json/') + 'fera/v1/enquiry', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) });
    const j = await r.json().catch(() => ({}));
    if (!r.ok || !j.ok) {
      if (j && (j.fields || j.captcha)) { if (j.captcha) setCap(f, j.captcha); flag(j.fields || []); say(j.message || 'Please check the highlighted fields.', true); return; }
      throw new Error(j.message || 'failed');
    }
    say(j.message || 'Thank you! Your enquiry has been sent, we\'ll reply within one business day.');
    f.reset(); $$('.bad', f).forEach((x) => x.classList.remove('bad')); $$('.other-svc', f).forEach((b) => { b.hidden = true; }); loadCap(f);
    if (window.fbq) try { window.fbq('track', 'Lead'); } catch (err) {}
    if (window.gtag) try { window.gtag('event', 'generate_lead'); } catch (err) {}
  } catch (err) {
    say('Sorry, that didn\'t go through. Please email sales@ferasolution.com or call 337-252-4040.', true);
  } finally { if (btn) btn.disabled = false; }
});
setInterval(() => { const el = $('.ct-time'); if (!el) return; try { el.textContent = new Intl.DateTimeFormat('en-US', { hour: 'numeric', minute: '2-digit', timeZone: 'America/New_York' }).format(new Date()) + ' ET'; } catch (e) {} }, 1000);

let svcTriggers = [];
function setupServiceAnimations() {
  svcTriggers.forEach((t) => t.kill()); svcTriggers = [];
  const add = (tw) => { if (tw && tw.scrollTrigger) svcTriggers.push(tw.scrollTrigger); else if (tw) svcTriggers.push(tw); };
  $$('[data-scene]', svcView).forEach((sec) => add(ScrollTrigger.create({ trigger: sec, start: 'top 55%', end: 'bottom 45%', onToggle: (s) => s.isActive && setScene(sec.dataset.scene) })));
  const fill = $('#tlFill');
  if (fill) add(gsap.to(fill, { scaleX: 1, ease: 'none', scrollTrigger: { trigger: '#tl', start: 'top 80%', end: 'bottom 50%', scrub: true } }));
  if (!reduced) {
    $$('[data-split]', svcView).forEach((h) => add(gsap.from($$('.line > span', h), { yPercent: 118, rotate: 2.5, duration: 1.4, ease: 'expo.out', stagger: 0.09, scrollTrigger: { trigger: h, start: 'top 86%' } })));
    $$('[data-reveal]', svcView).forEach((el) => add(gsap.from(el, { y: 50, opacity: 0, duration: 1.1, ease: 'power3.out', scrollTrigger: { trigger: el, start: 'top 92%' } })));
    if (lenis) {
      const skews = $$('.skew', svcView).map((el) => gsap.quickTo(el, 'skewX', { duration: 0.5, ease: 'power3' }));
      svcSkews = skews;
    }
  }
  bindInteractive(svcView);
}
let svcSkews = [];
if (lenis) lenis.on('scroll', (e) => { if (view === 'home') return; const v = gsap.utils.clamp(-12, 12, e.velocity * -0.4); svcSkews.forEach((f) => f(v)); });

function playIntro(root) {
  if (reduced) { heroSignatureIntro(root, 0); return; }
  if ($$('h1 .line > span', root).length) gsap.from($$('h1 .line > span', root), { yPercent: 118, rotate: 3, duration: 1.6, ease: 'expo.out', stagger: 0.12, delay: 0.15 });
  if ($$('[data-fade]', root).length) gsap.from($$('[data-fade]', root), { y: 30, opacity: 0, duration: 1.2, ease: 'power3.out', stagger: 0.1, delay: 0.4 });
  heroSignatureIntro(root, 0.15);
}

/* ================= hero signature interactions ================= */
(function () {
  const t = $('#rotT');
  if (t) {
    const n = t.children.length - 1; let i = 0;
    setInterval(() => {
      if (reduced) return;
      i++;
      t.style.transition = 'transform .9s cubic-bezier(.2,.7,.2,1)';
      t.style.transform = 'translateY(' + (-i * 100 / (n + 1)) + '%)';
      if (i === n) setTimeout(() => { t.style.transition = 'none'; t.style.transform = 'translateY(0)'; i = 0; }, 950);
    }, 2400);
  }
  if (fine && !reduced) {
    let tx = 0, ty = 0, cx = 0, cy = 0;
    addEventListener('pointermove', (e) => { tx = (e.clientX / innerWidth) * 2 - 1; ty = (e.clientY / innerHeight) * 2 - 1; }, { passive: true });
    gsap.ticker.add(() => {
      cx += (tx - cx) * 0.08; cy += (ty - cy) * 0.08;
      $$('.hl-wrap').forEach((w) => {
        w.style.transform = `perspective(900px) rotateY(${cx * 14}deg) rotateX(${-cy * 10}deg)`;
        w.style.setProperty('--dx', (-cx * 0.025).toFixed(4) + 'em');
        w.style.setProperty('--dy', (-cy * 0.02).toFixed(4) + 'em');
      });
    });
  }
})();
function heroSignatureIntro(root, delay) {
  const rule = $('.hx-rule i', root);
  if (reduced) { if (rule) rule.style.transform = 'scaleX(1)'; return; }
  if (rule) gsap.to(rule, { scaleX: 1, duration: 1.4, ease: 'expo.inOut', delay: delay + 0.5 });
  const w = $('.hx-with', root);
  if (w) gsap.from($$('.hx-w, .hx-note', w), { opacity: 0, y: 20, duration: 1, ease: 'power3.out', stagger: 0.1, delay: delay + 0.3 });
  $$('.hl-wrap', root).forEach((el) => gsap.fromTo(el, { clipPath: 'inset(0 100% 0 0)' }, { clipPath: 'inset(0 -10% -20% 0)', duration: 1.6, ease: 'expo.inOut', delay: delay + 0.35 }));
}
/* variable-weight proximity on "Technology," */
(function () {
  const word = $('#vfWord');
  if (!word) return;
  word.innerHTML = word.textContent.split('').map((c) => '<span>' + c + '</span>').join('');
  if (!fine || reduced) return;
  const letters = $$('span', word);
  const cur = letters.map(() => 600);
  let mx = -9999, my = -9999;
  addEventListener('pointermove', (e) => { mx = e.clientX; my = e.clientY; }, { passive: true });
  gsap.ticker.add(() => {
    if (view !== 'home') return;
    const r0 = word.getBoundingClientRect();
    if (r0.bottom < 0 || r0.top > innerHeight) return;
    letters.forEach((l, i) => {
      const r = l.getBoundingClientRect();
      const d = Math.hypot(mx - (r.left + r.width / 2), my - (r.top + r.height / 2));
      const f = Math.max(0, 1 - d / 280);
      const t = 600 + 300 * f * f * (3 - 2 * f);
      cur[i] += (t - cur[i]) * 0.14;
      l.style.fontWeight = Math.round(cur[i]);
    });
  });
})();
/* stage HUD: live coords + click burst */
(function () {
  const st = $('#stage'), xy = $('#stXY');
  if (!st) return;
  const f = (v) => (v >= 0 ? '+' : '−') + Math.abs(v).toFixed(3);
  addEventListener('pointermove', () => { if (xy) xy.textContent = 'X ' + f(pointer.x) + ' · Y ' + f(pointer.y); }, { passive: true });
  let down = false, moved = 0, lx = 0, ly = 0, holdT = 0;
  st.addEventListener('pointerdown', (e) => {
    down = true; moved = 0; lx = e.clientX; ly = e.clientY; st.style.cursor = 'grabbing';
    if (st.setPointerCapture && e.pointerType === 'mouse') st.setPointerCapture(e.pointerId);
    holdT = setTimeout(() => { if (down && moved < 6 && gl) { gl.charge(true); sfx('charge'); } }, 260);
  });
  st.addEventListener('pointermove', (e) => {
    if (!down || !gl) return;
    const dx = e.clientX - lx, dy = e.clientY - ly; lx = e.clientX; ly = e.clientY;
    moved += Math.abs(dx) + Math.abs(dy);
    if (moved > 6) { clearTimeout(holdT); gl.charge(false); gl.drag(dx, dy); }
  });
  const up = () => {
    if (!down) return; down = false; clearTimeout(holdT); st.style.cursor = '';
    if (!gl) return;
    const c = gl.chargeLevel();
    gl.charge(false); gl.release();
    if (moved <= 6) { gl.burst(0.4 + c * 1.4); sfx(c > 0.3 ? 'boom' : 'click'); }
  };
  st.addEventListener('pointerup', up); st.addEventListener('pointercancel', up); st.addEventListener('pointerleave', up);
})();

/* ================= v4: sound ================= */
let soundOn = false, actx = null;
try { soundOn = localStorage.getItem('fera-sound') === '1'; } catch (e) {}
const sndBtn = $('#sndBtn');
function ensureAudio() { if (!actx) { const C = window.AudioContext || window.webkitAudioContext; if (C) actx = new C(); } if (actx && actx.state === 'suspended') actx.resume(); }
function tone(f1, f2, dur, vol, type) {
  if (!actx) return;
  const t = actx.currentTime, o = actx.createOscillator(), g = actx.createGain();
  o.type = type || 'sine'; o.frequency.setValueAtTime(f1, t); o.frequency.exponentialRampToValueAtTime(Math.max(f2, 1), t + dur);
  g.gain.setValueAtTime(0.0001, t); g.gain.exponentialRampToValueAtTime(vol, t + 0.008); g.gain.exponentialRampToValueAtTime(0.0001, t + dur);
  o.connect(g).connect(actx.destination); o.start(t); o.stop(t + dur + 0.02);
}
function noise(dur, vol, from, to) {
  if (!actx) return;
  const t = actx.currentTime, n = actx.sampleRate * dur, b = actx.createBuffer(1, n, actx.sampleRate), d = b.getChannelData(0);
  for (let i = 0; i < n; i++) d[i] = (Math.random() * 2 - 1) * (1 - i / n);
  const src = actx.createBufferSource(), f = actx.createBiquadFilter(), g = actx.createGain();
  src.buffer = b; f.type = 'bandpass'; f.Q.value = 1.2; f.frequency.setValueAtTime(from, t); f.frequency.exponentialRampToValueAtTime(to, t + dur);
  g.gain.value = vol; src.connect(f).connect(g).connect(actx.destination); src.start(t);
}
function sfx(kind) {
  if (!soundOn || !actx) return;
  try {
    if (kind === 'hover') tone(2400, 1900, 0.03, 0.012);
    else if (kind === 'click') tone(700, 380, 0.09, 0.05, 'triangle');
    else if (kind === 'charge') tone(120, 520, 1.2, 0.03, 'sawtooth');
    else if (kind === 'boom') { tone(160, 40, 0.5, 0.12); noise(0.45, 0.25, 3000, 200); }
    else if (kind === 'whoosh') noise(0.7, 0.18, 300, 4000);
    else if (kind === 'open') tone(520, 880, 0.12, 0.04, 'triangle');
  } catch (e) {}
}
function setSound(on) {
  soundOn = on; sndBtn.setAttribute('aria-pressed', String(on));
  sndBtn.setAttribute('aria-label', on ? 'Turn interface sound off' : 'Turn interface sound on');
  try { localStorage.setItem('fera-sound', on ? '1' : '0'); } catch (e) {}
  if (on) { ensureAudio(); sfx('open'); }
}
sndBtn.addEventListener('click', () => setSound(!soundOn));
sndBtn.setAttribute('aria-pressed', String(soundOn));
document.addEventListener('pointerdown', () => { if (soundOn) ensureAudio(); }, { once: true });
let lastHover = null;
document.addEventListener('pointerover', (e) => {
  const t = e.target.closest('a,button,.svc-btn,summary');
  if (t && t !== lastHover) { lastHover = t; sfx('hover'); }
  if (!t) lastHover = null;
});
document.addEventListener('click', (e) => { if (e.target.closest('a,button,summary') && !e.target.closest('#sndBtn,#stage')) sfx('click'); });

/* ================= v4: section navigator ================= */
const NAV_LABELS = { 'pg-logo': 'Overview', 'pg-sphere': 'Overview', 'pg-wave': 'Overview', 'pg-galaxy': 'Overview', 'pg-prose': 'Read', hero: 'Intro', about: 'Studio', services: 'Services', work: 'Work', process: 'Process', why: 'Why Fera', cta: 'Contact', svc: 'Overview', svcbody: 'Included', svcwave: 'Process', svcfaq: 'FAQ' };
const secnav = $('#secnav');
let navSecs = [];
function buildSecNav() {
  const root = view === 'home' ? homeView : svcView;
  navSecs = $$('[data-scene]', root).filter((x) => NAV_LABELS[x.dataset.scene]);
  secnav.innerHTML = navSecs.map((x, i) => '<a href="#" data-i="' + i + '" data-cursor="Go"><span>' + NAV_LABELS[x.dataset.scene] + '</span><b></b></a>').join('');
  $$('a', secnav).forEach((a) => a.addEventListener('click', (e) => { e.preventDefault(); e.stopPropagation(); scrollToEl(navSecs[+a.dataset.i]); }));
}
let navTick = 0;
gsap.ticker.add(() => {
  if (++navTick % 8) return;
  let on = 0;
  navSecs.forEach((x, i) => { if (x.getBoundingClientRect().top < innerHeight * 0.5) on = i; });
  $$('a', secnav).forEach((a, i) => a.classList.toggle('on', i === on));
});

/* ================= v4: command palette ================= */
const cmd = $('#cmd'), cmdIn = $('#cmdIn'), cmdList = $('#cmdList');
const isMac = /Mac|iPhone|iPad/.test(navigator.platform || navigator.userAgent);

const CMDS = [
  ...[['Home', 'home'], ['About', 'about'], ['Services', 'services'], ['Work', 'work'], ['Contact', 'contact']].map((x) => ({ g: 'Pages', t: x[0], k: 'Page', run: () => go(x[1] === 'home' ? FERA.home : (FERA.urls[x[1]] || FERA.home)) })),
  ...SERVICES.map((sv) => ({ g: 'Services', t: sv.name, k: String(sv.i + 1).padStart(2, '0'), run: () => go(FERA.svc[sv.slug] || FERA.home) })),
  { g: 'Actions', t: 'Start a project', k: 'Form', run: () => openDrawer() },
  { g: 'Actions', t: 'Chat with us', k: 'Chat', run: () => openChat() },
  { g: 'Actions', t: 'Email sales@ferasolution.com', k: 'Email', run: () => { location.href = 'mailto:sales@ferasolution.com'; } },
  { g: 'Actions', t: 'Call 337-252-4040', k: 'Phone', run: () => { location.href = 'tel:+13372524040'; } },
  { g: 'Pages', t: 'Blog', k: 'Page', run: () => go((FERA.urls && FERA.urls.blog) || (FERA.home + 'blog/')) },
  { g: 'Pages', t: 'Live demos', k: 'Page', run: () => go((FERA.urls && FERA.urls.experience) || (FERA.home + 'experience/')) },
  { g: 'Actions', t: 'WhatsApp 337-445-5717', k: 'WhatsApp', run: () => { window.open('https://wa.me/13374455717', '_blank', 'noopener'); } },
  { g: 'Actions', t: 'Toggle interface sound', k: 'Sound', run: () => setSound(!soundOn) }
];
let cmdItems = [], cmdSel = 0;
function renderCmd() {
  const q = cmdIn.value.trim().toLowerCase();
  const raw = cmdIn.value.trim();
  cmdItems = CMDS.filter((c) => !q || (c.t + ' ' + c.k).toLowerCase().includes(q));
  if (raw) cmdItems.unshift({ g: 'Search', t: 'Search the website for “' + raw + '”', k: 'Enter', run: () => go(FERA.home + '?s=' + encodeURIComponent(raw)) });
  cmdSel = Math.min(cmdSel, Math.max(0, cmdItems.length - 1));
  let g = '', html = '';
  cmdItems.forEach((c, i) => {
    if (c.g !== g) { g = c.g; html += '<li class="cmd-grp" role="presentation">' + g + '</li>'; }
    html += '<li class="cmd-it" role="option" id="cmd-' + i + '" data-i="' + i + '" aria-selected="' + (i === cmdSel) + '"><span>' + esc(c.t) + '</span><small>' + esc(c.k) + '</small></li>';
  });
  cmdList.innerHTML = html || '<li class="cmd-grp">No matches</li>';
  cmdIn.setAttribute('aria-activedescendant', cmdItems.length ? 'cmd-' + cmdSel : '');
  const sel = $('#cmd-' + cmdSel); if (sel) sel.scrollIntoView({ block: 'nearest' });
}
function openCmd() { closeMenu(); cmd.classList.add('open'); cmdIn.value = ''; cmdSel = 0; renderCmd(); if (lenis) lenis.stop(); setTimeout(() => cmdIn.focus(), 30); sfx('open'); }
function closeCmd() { if (!cmd.classList.contains('open')) return; cmd.classList.remove('open'); if (lenis && !drawer.classList.contains('open')) lenis.start(); }
function runCmd(i) { const c = cmdItems[i]; if (!c) return; closeCmd(); setTimeout(c.run, 80); }
$('#cmdBtn').addEventListener('click', openCmd);
cmd.addEventListener('click', (e) => { if (e.target === cmd) closeCmd(); const it = e.target.closest('.cmd-it'); if (it) runCmd(+it.dataset.i); });
cmdList.addEventListener('pointermove', (e) => { const it = e.target.closest('.cmd-it'); if (it && +it.dataset.i !== cmdSel) { cmdSel = +it.dataset.i; $$('.cmd-it', cmdList).forEach((x) => x.setAttribute('aria-selected', String(+x.dataset.i === cmdSel))); } });
cmdIn.addEventListener('input', () => { cmdSel = 0; renderCmd(); });
cmdIn.addEventListener('keydown', (e) => {
  if (e.key === 'ArrowDown') { e.preventDefault(); cmdSel = (cmdSel + 1) % Math.max(1, cmdItems.length); renderCmd(); }
  else if (e.key === 'ArrowUp') { e.preventDefault(); cmdSel = (cmdSel - 1 + cmdItems.length) % Math.max(1, cmdItems.length); renderCmd(); }
  else if (e.key === 'Enter') { e.preventDefault(); runCmd(cmdSel); }
  else if (e.key === 'Escape') { e.preventDefault(); closeCmd(); }
});
document.addEventListener('keydown', (e) => {
  if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') { e.preventDefault(); cmd.classList.contains('open') ? closeCmd() : openCmd(); }
  else if (e.key === 'Escape') closeCmd();
});


/* ================= v7: hero fly-through + depth parallax ================= */
(function () {
  const hero = $('.hero');
  if (!hero) return;
  if (!reduced) {
    const tl = gsap.timeline({ scrollTrigger: { trigger: hero, start: 'top top', end: 'bottom top', scrub: 0.6, onUpdate: (st) => { if (gl && gl.heroOut) gl.heroOut(st.progress); } } });
    tl.to('.hx-l1', { xPercent: -18, opacity: 0.15, ease: 'none' }, 0)
      .to('.hx-with', { xPercent: -30, opacity: 0, ease: 'none' }, 0)
      .to('.hx-l3', { xPercent: 14, opacity: 0.2, ease: 'none' }, 0)
      .to('.hx-bottom', { yPercent: -60, opacity: 0, ease: 'none' }, 0);
  }
  if (fine && !reduced) {
    const layers = [['.hx-l1', 10, 6], ['.hx-with', 22, 10], ['.hx-l3', 34, 14]].map(([sel, kx, ky]) => ({ el: $(sel, hero), kx, ky, x: 0, y: 0 }));
    gsap.ticker.add(() => {
      if (view !== 'home') return;
      layers.forEach((L) => {
        if (!L.el) return;
        L.x += (-pointer.x * L.kx - L.x) * 0.06; L.y += (pointer.y * L.ky - L.y) * 0.06;
        L.el.style.translate = L.x.toFixed(2) + 'px ' + L.y.toFixed(2) + 'px';
      });
    });
  }
})();

/* ================= loader & intro ================= */
const ldCount = $('#ldCount'), ldBar = $('#ldBar');
const counter = { v: 0 };
gsap.to('#loader img', { opacity: 1, scale: 1, duration: 1.2, ease: 'expo.out' });
const quick = document.documentElement.classList.contains('fera-quick');
const navIn = document.documentElement.classList.contains('fera-nav-in');
const loadAnim = gsap.to(counter, { v: 100, duration: (reduced || quick) ? 0.01 : 1.1, ease: 'power2.inOut', onUpdate: () => { ldCount.textContent = Math.round(counter.v); ldBar.style.transform = 'scaleX(' + counter.v / 100 + ')'; } });
try { sessionStorage.setItem('fera-v', '1'); sessionStorage.removeItem('fera-nav'); } catch (err) {}
const fontsReady = Promise.race([document.fonts.ready, new Promise((r) => setTimeout(r, quick ? 400 : 1600))]);
Promise.all([fontsReady, loadAnim.then()]).then(() => {
  if (navIn) {
    gsap.set(curtain, { y: 0, yPercent: 0, visibility: 'visible' });
    document.documentElement.classList.remove('fera-nav-in');
    gsap.to(curtain, { yPercent: -100, duration: 0.85, ease: 'expo.inOut', delay: 0.05, onComplete: () => gsap.set(curtain, { visibility: 'hidden' }) });
  }
  const tl = gsap.timeline({ onComplete: () => { $('#loader').style.display = 'none'; } });
  if (!quick) tl.to('.ld-inner, .ld-count, .ld-bar', { opacity: 0, y: -30, duration: 0.5, ease: 'power2.in' })
    .to('.ld-half.top', { yPercent: -100, duration: 1.2, ease: 'expo.inOut' }, '-=0.05')
    .to('.ld-half.bot', { yPercent: 100, duration: 1.2, ease: 'expo.inOut' }, '<');
  document.body.classList.remove('loading');
  if (lenis) lenis.start();
  markNav();
  if (svcView) setupServiceAnimations();
  const root = homeView ? $('.hero') : svcView;
  const d0 = quick ? 0.15 : 0.75;
  if (!reduced && root && false) {
    if ($$('h1 .line > span', root).length) gsap.from($$('h1 .line > span', root), { yPercent: 118, rotate: 3, duration: 1.6, ease: 'expo.out', stagger: 0.12, delay: d0 });
    if ($$('[data-fade]', root).length) gsap.from($$('[data-fade]', root), { y: 30, opacity: 0, duration: 1.2, ease: 'power3.out', stagger: 0.1, delay: d0 + 0.25 });
    if (!quick) gsap.from('#nav > *', { y: -40, opacity: 0, duration: 1.2, ease: 'power3.out', stagger: 0.08, delay: 0.9 });
    setTimeout(() => $$('[data-scramble]').forEach(scramble), 1100);
  }
  // First paint is final (fast LCP / Speed Index); entrance motion only plays behind the page-transition curtain.
  // Entrance motion is CSS-driven (starts at first paint; see main.css feraUp/feraFade/feraClip).
  ScrollTrigger.refresh();
  buildSecNav();
});

/* ================= live demo gallery ================= */
$$('[data-demo-gallery]').forEach((root) => {
  const stage = $('.demo-stage', root), frame = stage && $('iframe', stage);
  if (!stage || !frame) return;
  let opener = null;
  $$('.demo-filters button', root).forEach((b) => b.addEventListener('click', () => {
    const f = b.dataset.filter;
    $$('.demo-filters button', root).forEach((x) => x.setAttribute('aria-pressed', String(x === b)));
    $$('.demo-card', root).forEach((c) => { c.hidden = !(f === 'all' || c.dataset.category === f); });
    ScrollTrigger.refresh();
  }));
  $$('.demo-open', root).forEach((b) => b.addEventListener('click', () => {
    opener = b;
    frame.src = b.dataset.src; frame.title = b.dataset.title;
    $('.demo-title', stage).textContent = b.dataset.title;
    stage.hidden = false;
    ScrollTrigger.refresh();
    scrollToEl(stage);
    $('.demo-close', stage).focus({ preventScroll: true });
  }));
  $$('[data-device]', stage).forEach((b) => b.addEventListener('click', () => {
    $$('[data-device]', stage).forEach((x) => x.setAttribute('aria-pressed', String(x === b)));
    stage.classList.toggle('mobile', b.dataset.device === 'mobile');
  }));
  $('.demo-close', stage).addEventListener('click', () => {
    stage.hidden = true; frame.src = 'about:blank'; ScrollTrigger.refresh();
    if (opener) opener.focus({ preventScroll: true });
  });
});

/* CSS loop animations start on the first interaction (keeps the first paint calm and fast) */
(function () {
  const ev = ['pointerdown', 'pointermove', 'keydown', 'touchstart', 'wheel', 'scroll'];
  const wake = () => { document.documentElement.classList.remove('fera-idle'); ev.forEach((n) => removeEventListener(n, wake, true)); };
  ev.forEach((n) => addEventListener(n, wake, { capture: true, passive: true }));
  if (document.readyState === "complete") setTimeout(wake, 3500); else addEventListener("load", () => setTimeout(wake, 3500), { once: true });
})();

/* ================= Why Fera tabs ================= */
(() => {
  const wx = $('[data-wx]');
  if (!wx) return;
  const tabs = $$('.wx-tab', wx), panels = $$('.wx-panel', wx), stage = $('.wx-stage', wx);
  const DUR = 7000, reducedM = matchMedia('(prefers-reduced-motion: reduce)').matches;
  let cur = 0, timer = null, inView = false, hover = false;
  wx.style.setProperty('--wx-dur', DUR + 'ms');
  const show = (i, focus) => {
    cur = (i + tabs.length) % tabs.length;
    tabs.forEach((t, k) => { const on = k === cur; t.classList.toggle('on', on); t.setAttribute('aria-selected', String(on)); t.tabIndex = on ? 0 : -1; });
    panels.forEach((p, k) => p.classList.toggle('on', k === cur));
    const t = tabs[cur];
    if (t && wx.querySelector('.wx-list').scrollWidth > wx.querySelector('.wx-list').clientWidth) t.scrollIntoView({ block: 'nearest', inline: 'center', behavior: 'smooth' });
    if (focus) t.focus();
    restart();
  };
  const stop = () => { clearTimeout(timer); timer = null; };
  const restart = () => {
    stop();
    const auto = !reducedM && inView && !hover;
    wx.classList.toggle('auto', auto); wx.classList.toggle('paused', !auto);
    if (!auto) return;
    const t = tabs[cur]; const bar = t && t.querySelector('.wx-prog i');
    if (bar) { bar.style.animation = 'none'; void bar.offsetWidth; bar.style.animation = ''; }
    timer = setTimeout(() => show(cur + 1), DUR);
  };
  tabs.forEach((t, k) => {
    t.addEventListener('click', () => show(k));
    t.addEventListener('keydown', (e) => {
      const next = { ArrowDown: 1, ArrowRight: 1, ArrowUp: -1, ArrowLeft: -1 }[e.key];
      if (next) { e.preventDefault(); show(cur + next, true); }
      else if (e.key === 'Home') { e.preventDefault(); show(0, true); }
      else if (e.key === 'End') { e.preventDefault(); show(tabs.length - 1, true); }
    });
  });
  wx.addEventListener('pointerenter', () => { hover = true; restart(); });
  wx.addEventListener('pointerleave', () => { hover = false; restart(); });
  wx.addEventListener('focusin', () => { hover = true; restart(); });
  wx.addEventListener('focusout', (e) => { if (!wx.contains(e.relatedTarget)) { hover = false; restart(); } });
  if (stage) stage.addEventListener('pointermove', (e) => { const r = stage.getBoundingClientRect(); stage.style.setProperty('--mx', ((e.clientX - r.left) / r.width * 100) + '%'); stage.style.setProperty('--my', ((e.clientY - r.top) / r.height * 100) + '%'); });
  if ('IntersectionObserver' in window) new IntersectionObserver((es) => { inView = es.some((x) => x.isIntersecting); restart(); }, { threshold: 0.35 }).observe(wx);
  /* live clocks */
  const fmt = {};
  const tick = () => {
    $$('[data-tz]', wx).forEach((b) => {
      const z = b.dataset.tz;
      try { fmt[z] = fmt[z] || new Intl.DateTimeFormat('en-US', { timeZone: z, hour: 'numeric', minute: '2-digit' }); b.textContent = fmt[z].format(new Date()); } catch (err) {}
    });
  };
  tick(); setInterval(tick, 30000);
  document.addEventListener('visibilitychange', () => { if (document.hidden) stop(); else restart(); });
})();
