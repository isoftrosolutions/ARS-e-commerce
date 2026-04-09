<?php
$page_title    = 'Online Shopping in Nepal | Buy Electronics, Fashion & More';
$page_meta_desc= 'Easy Shopping A.R.S — Nepal\'s trusted online store. Shop electronics, fashion, home goods & more with fast delivery to Birgunj, Parsa and across Nepal. eSewa & COD accepted.';
require_once __DIR__ . '/includes/header-bootstrap.php';

try {
    $stmt = $pdo->query("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC LIMIT 8");
    $latest_products = $stmt->fetchAll();
    $categories = $pdo->query("SELECT * FROM categories LIMIT 6")->fetchAll();
} catch (PDOException $e) {
    $latest_products = [];
    $categories = [];
}
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,700;9..144,900&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">

<style>
/* ═══ CSS Variables — Light Theme ════════════════════════════ */
:root {
  --void:        #ffffff;
  --deep:        #f8fafc;
  --surface:     #ffffff;
  --glass:       rgba(255,255,255,0.85);
  --ember:       #ea6c00;
  --ember-glow:  rgba(234,108,0,0.10);
  --gold:        #d97706;
  --gold-glow:   rgba(217,119,6,0.10);
  --ice:         #0f172a;
  --muted:       #64748b;
  --edge:        rgba(15,23,42,0.08);
  --shadow-sm:   0 2px 8px rgba(15,23,42,0.06);
  --shadow-md:   0 8px 32px rgba(15,23,42,0.10);
  --shadow-lg:   0 24px 64px rgba(15,23,42,0.12);
  --font-d:      'Fraunces', Georgia, serif;
  --font-b:      'DM Sans', sans-serif;
  --ease-out:    cubic-bezier(0.22,1,0.36,1);
}

/* ═══ Base Overrides ══════════════════════════════════════════ */
body {
  background: #ffffff !important;
  font-family: var(--font-b);
  color: var(--ice);
  cursor: none !important;
}
* { cursor: none !important; box-sizing: border-box; }
a { color: inherit; }
img { display: block; }

/* ═══ Custom Cursor ═══════════════════════════════════════════ */
#ars-cursor-dot {
  width: 7px; height: 7px;
  background: var(--ember); border-radius: 50%;
  position: fixed; z-index: 99999; pointer-events: none;
  transform: translate(-50%,-50%);
  transition: background 0.2s;
  mix-blend-mode: normal;
}
#ars-cursor-ring {
  width: 38px; height: 38px;
  border: 1.5px solid rgba(249,115,22,0.55);
  border-radius: 50%;
  position: fixed; z-index: 99998; pointer-events: none;
  transform: translate(-50%,-50%);
  transition: width 0.25s var(--ease-out), height 0.25s var(--ease-out),
              border-color 0.25s, opacity 0.2s;
}
#ars-cursor-ring.active {
  width: 58px; height: 58px;
  border-color: var(--ember);
}

/* ═══ HERO ════════════════════════════════════════════════════ */
#hero {
  position: relative;
  height: 100vh; min-height: 620px;
  display: flex; align-items: center;
  overflow: hidden; background: var(--void);
}
#hero-canvas {
  position: absolute; inset: 0;
  width: 100%; height: 100%; z-index: 0;
}
/* Giant ghost brand watermark */
.hero-watermark {
  position: absolute;
  top: 50%; left: 50%;
  transform: translate(-50%, -50%);
  font-family: var(--font-d);
  font-size: clamp(120px, 22vw, 280px);
  font-weight: 900;
  color: transparent;
  -webkit-text-stroke: 1px rgba(15,23,42,0.04);
  white-space: nowrap;
  pointer-events: none; z-index: 1;
  letter-spacing: -8px;
  user-select: none;
}
/* Ambient glow orbs */
.hero-orb {
  position: absolute; border-radius: 50%;
  pointer-events: none; filter: blur(90px);
  z-index: 1;
}
.hero-orb-1 {
  width: 500px; height: 500px;
  background: radial-gradient(circle, rgba(234,108,0,0.09), transparent 70%);
  top: 10%; left: -5%;
  animation: orb-drift-1 12s ease-in-out infinite;
}
.hero-orb-2 {
  width: 400px; height: 400px;
  background: radial-gradient(circle, rgba(217,119,6,0.07), transparent 70%);
  bottom: 0; right: 10%;
  animation: orb-drift-2 15s ease-in-out infinite;
}
@keyframes orb-drift-1 {
  0%,100%{transform:translate(0,0) scale(1)}
  33%{transform:translate(40px,-30px) scale(1.1)}
  66%{transform:translate(-20px,40px) scale(0.95)}
}
@keyframes orb-drift-2 {
  0%,100%{transform:translate(0,0)}
  50%{transform:translate(-40px,-50px) scale(1.15)}
}

.hero-content {
  position: relative; z-index: 2; width: 100%;
}
#hero-stage {
  transform-style: preserve-3d;
  will-change: transform;
}

/* Live badge */
.live-badge {
  display: inline-flex; align-items: center; gap: 8px;
  padding: 6px 16px; border-radius: 100px;
  background: rgba(249,115,22,0.1);
  border: 1px solid rgba(249,115,22,0.28);
  font-size: 10px; font-weight: 700;
  letter-spacing: 2.5px; text-transform: uppercase;
  color: var(--ember); margin-bottom: 24px;
  backdrop-filter: blur(12px);
  animation: fade-up 0.8s var(--ease-out) both;
}
.live-dot {
  width: 6px; height: 6px; background: var(--ember);
  border-radius: 50%; animation: pulse-dot 1.6s infinite;
}
@keyframes pulse-dot {
  0%,100%{box-shadow:0 0 0 0 rgba(249,115,22,0.7)}
  50%{box-shadow:0 0 0 6px rgba(249,115,22,0)}
}

/* Hero title */
.hero-h1 {
  font-family: var(--font-d);
  font-size: clamp(3.2rem, 9vw, 7.5rem);
  font-weight: 900; line-height: 0.92;
  letter-spacing: -3px; margin-bottom: 20px;
  animation: fade-up 0.8s 0.15s var(--ease-out) both;
}
.hero-h1 .line-outline {
  color: transparent;
  -webkit-text-stroke: 1.5px rgba(15,23,42,0.12);
  display: block;
}
.hero-h1 .line-grad {
  background: linear-gradient(120deg, var(--ember) 0%, var(--gold) 60%);
  -webkit-background-clip: text; -webkit-text-fill-color: transparent;
  background-clip: text; display: block;
}
.hero-h1 .line-solid { color: #0f172a; display: block; }

.hero-desc {
  font-size: 15px; font-weight: 300;
  color: rgba(15,23,42,0.50);
  max-width: 340px; line-height: 1.75;
  margin-bottom: 38px;
  animation: fade-up 0.8s 0.28s var(--ease-out) both;
}

/* CTA Buttons */
.hero-cta-wrap {
  display: flex; flex-wrap: wrap; gap: 14px;
  animation: fade-up 0.8s 0.38s var(--ease-out) both;
}
.btn-fire {
  position: relative;
  display: inline-flex; align-items: center; gap: 10px;
  padding: 14px 30px; border-radius: 10px; border: none;
  background: linear-gradient(135deg, var(--ember) 0%, var(--gold) 100%);
  color: #fff; font-weight: 700; font-size: 14px;
  letter-spacing: 0.3px; text-decoration: none;
  overflow: hidden;
  transition: transform 0.25s var(--ease-out), box-shadow 0.25s;
}
.btn-fire::before {
  content: '';
  position: absolute; inset: 0;
  background: linear-gradient(135deg, rgba(255,255,255,0.18), transparent 60%);
  border-radius: 10px;
}
.btn-fire:hover {
  transform: translateY(-4px) scale(1.02);
  box-shadow: 0 24px 60px rgba(249,115,22,0.45),
              0 0 0 1px rgba(249,115,22,0.35);
  color: #fff; text-decoration: none;
}
.btn-void {
  display: inline-flex; align-items: center; gap: 10px;
  padding: 14px 28px; border-radius: 10px;
  background: transparent;
  border: 1px solid rgba(15,23,42,0.18);
  color: #0f172a; font-size: 14px; font-weight: 500;
  text-decoration: none;
  transition: all 0.25s;
}
.btn-void:hover {
  background: rgba(15,23,42,0.05);
  border-color: rgba(15,23,42,0.30);
  color: #0f172a; text-decoration: none;
  transform: translateY(-3px);
}

/* Mini stats row */
.hero-stats {
  display: flex; gap: 28px; margin-top: 48px;
  animation: fade-up 0.8s 0.5s var(--ease-out) both;
}
.hs-sep { width: 1px; background: rgba(15,23,42,0.10); }
.hs-val {
  font-family: var(--font-d); font-size: 26px; font-weight: 900;
  color: var(--gold); line-height: 1;
}
.hs-lbl { font-size: 11px; color: var(--muted); margin-top: 3px; }

/* Hero right — floating 3D card */
.hero-3d-scene {
  position: relative;
  height: 480px;
  display: flex; align-items: center; justify-content: center;
}
.float-card-main {
  width: 240px;
  background: #ffffff;
  border: 1px solid rgba(15,23,42,0.09);
  border-radius: 22px;
  padding: 22px;
  box-shadow: 0 30px 80px rgba(15,23,42,0.14),
              0 0 0 1px rgba(234,108,0,0.08);
  animation: card-float 7s ease-in-out infinite;
}
@keyframes card-float {
  0%,100%{transform:translateY(0px) rotateX(3deg) rotateY(-6deg)}
  50%{transform:translateY(-22px) rotateX(-3deg) rotateY(6deg)}
}
.fcard-label {
  font-size: 9px; letter-spacing: 2.5px; text-transform: uppercase;
  color: var(--ember); font-weight: 700; margin-bottom: 14px;
}
.fcard-img {
  width: 100%; aspect-ratio: 1;
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
  border-radius: 14px; margin-bottom: 16px;
  display: flex; align-items: center; justify-content: center;
  overflow: hidden;
}
.fcard-img svg { opacity: 0.5; }
.fcard-name {
  font-size: 14px; font-weight: 600; color: #0f172a; margin-bottom: 4px;
}
.fcard-sub { font-size: 11px; color: var(--muted); margin-bottom: 14px; }
.fcard-footer {
  display: flex; align-items: center; justify-content: space-between;
  padding-top: 14px; border-top: 1px solid rgba(15,23,42,0.08);
}
.fcard-price {
  font-family: var(--font-d); font-size: 20px; font-weight: 700; color: var(--gold);
}
.fcard-btn {
  width: 30px; height: 30px; border-radius: 8px;
  background: linear-gradient(135deg, var(--ember), var(--gold));
  display: flex; align-items: center; justify-content: center;
  color: #fff; font-size: 15px; font-weight: 700;
}

/* Floating info chips */
.info-chip {
  position: absolute;
  background: #ffffff;
  border: 1px solid rgba(15,23,42,0.09);
  border-radius: 14px;
  padding: 12px 16px;
  box-shadow: 0 16px 48px rgba(15,23,42,0.12);
  white-space: nowrap;
}
.chip-top { top: 60px; right: 10px; animation: chip-bob-1 5.5s ease-in-out infinite; }
.chip-bot { bottom: 80px; left: 0; animation: chip-bob-2 7s ease-in-out infinite; }
@keyframes chip-bob-1 { 0%,100%{transform:translateY(0) rotate(2deg)} 50%{transform:translateY(-14px) rotate(-2deg)} }
@keyframes chip-bob-2 { 0%,100%{transform:translateY(0) rotate(-1.5deg)} 50%{transform:translateY(-18px) rotate(1.5deg)} }
.chip-val {
  font-family: var(--font-d); font-size: 20px; font-weight: 700;
  color: var(--gold); display: block; line-height: 1.1;
}
.chip-lbl { font-size: 10px; color: var(--muted); }

/* Scroll hint */
.hero-scroll {
  position: absolute; bottom: 36px; left: 50%;
  transform: translateX(-50%);
  display: flex; flex-direction: column; align-items: center; gap: 8px;
  color: rgba(15,23,42,0.22); font-size: 9px; letter-spacing: 3px;
  text-transform: uppercase; z-index: 3; user-select: none;
}
.scroll-bar {
  width: 1px; height: 44px;
  background: linear-gradient(to bottom, rgba(249,115,22,0.7), transparent);
  animation: scroll-anim 2s ease-in-out infinite;
}
@keyframes scroll-anim {
  0%{transform:scaleY(0);transform-origin:top;opacity:0}
  40%{opacity:1}
  50%{transform:scaleY(1);transform-origin:top}
  51%{transform:scaleY(1);transform-origin:bottom}
  100%{transform:scaleY(0);transform-origin:bottom;opacity:0}
}

/* ═══ Animations ══════════════════════════════════════════════ */
@keyframes fade-up {
  from{opacity:0;transform:translateY(30px)}
  to{opacity:1;transform:translateY(0)}
}
.reveal {
  opacity: 0; transform: translateY(36px);
  transition: opacity 0.75s var(--ease-out), transform 0.75s var(--ease-out);
}
.reveal.visible { opacity: 1; transform: none; }
.d1{transition-delay:0.08s}.d2{transition-delay:0.16s}
.d3{transition-delay:0.24s}.d4{transition-delay:0.32s}
.d5{transition-delay:0.40s}.d6{transition-delay:0.48s}
.d7{transition-delay:0.56s}.d8{transition-delay:0.64s}

/* ═══ Features Strip ══════════════════════════════════════════ */
#features {
  background: #f8fafc;
  border-top: 1px solid rgba(15,23,42,0.07);
  border-bottom: 1px solid rgba(15,23,42,0.07);
  padding: 26px 0;
}
.feat-row { display: flex; }
.feat-item {
  display: flex; align-items: center; gap: 13px;
  flex: 1; padding: 8px 20px;
  border-right: 1px solid var(--edge);
}
.feat-item:last-child { border-right: none; }
.feat-ico {
  width: 42px; height: 42px; border-radius: 12px; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  font-size: 17px;
}
.feat-title { font-size: 13px; font-weight: 600; color: var(--ice); line-height: 1.2; }
.feat-sub { font-size: 11px; color: var(--muted); margin-top: 1px; }

/* ═══ Section Labels ══════════════════════════════════════════ */
.sec-eyebrow {
  font-size: 10px; letter-spacing: 3.5px; text-transform: uppercase;
  color: var(--ember); font-weight: 700; display: block; margin-bottom: 10px;
}
.sec-h2 {
  font-family: var(--font-d);
  font-size: clamp(2rem, 4.5vw, 3.2rem);
  font-weight: 900; color: var(--ice);
  line-height: 1.0; margin-bottom: 0;
  letter-spacing: -1px;
}
.sec-sub { color: var(--muted); font-size: 14px; font-weight: 300; margin-top: 10px; }

/* ═══ Categories ══════════════════════════════════════════════ */
#categories { background: #f1f5f9; padding: 90px 0; }
.cat-tile {
  position: relative;
  background: #ffffff;
  border: 1px solid rgba(15,23,42,0.07);
  border-radius: 20px; padding: 26px 14px 22px;
  text-align: center; text-decoration: none !important;
  display: block; overflow: hidden;
  will-change: transform;
  transition: border-color 0.3s, box-shadow 0.3s;
}
/* Glow border on hover via pseudo */
.cat-tile::before {
  content: '';
  position: absolute; inset: 0; border-radius: 20px; z-index: 0;
  background: linear-gradient(135deg, var(--ember), var(--gold));
  opacity: 0; transition: opacity 0.3s; padding: 1px;
  -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
  -webkit-mask-composite: xor; mask-composite: exclude;
}
.cat-tile:hover::before { opacity: 1; }
.cat-tile:hover {
  box-shadow: 0 20px 60px rgba(249,115,22,0.15);
}
.cat-tile > * { position: relative; z-index: 1; }
.cat-ico {
  width: 54px; height: 54px; border-radius: 16px; margin: 0 auto 14px;
  display: flex; align-items: center; justify-content: center;
  font-size: 20px; transition: transform 0.3s var(--ease-out), background 0.3s;
}
.cat-tile:hover .cat-ico { transform: translateY(-5px) scale(1.1); }
.cat-nm { font-size: 12px; font-weight: 600; color: #0f172a; line-height: 1.3; }

/* ═══ Products ════════════════════════════════════════════════ */
#products { background: #ffffff; padding: 90px 0; }
.prod-card {
  background: #ffffff;
  border: 1px solid rgba(15,23,42,0.08);
  border-radius: 20px; overflow: hidden;
  display: flex; flex-direction: column;
  height: 100%;
  will-change: transform;
  transition: border-color 0.35s, box-shadow 0.35s;
}
.prod-card:hover {
  border-color: rgba(234,108,0,0.25);
  box-shadow: 0 20px 60px rgba(15,23,42,0.12), 0 0 0 1px rgba(234,108,0,0.10);
}
/* Shimmer sweep */
.prod-card::after {
  content: '';
  position: absolute; top: 0; left: -100%; width: 55%; height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.55), transparent);
  transition: left 0.55s ease;
  pointer-events: none;
}
.prod-card:hover::after { left: 160%; }
.prod-card { position: relative; }

.prod-img-box {
  position: relative;
  background: linear-gradient(145deg, #f8fafc, #f1f5f9);
  aspect-ratio: 1; display: flex; align-items: center; justify-content: center;
  overflow: hidden;
}
.prod-img-box img {
  width: 78%; height: 78%; object-fit: contain;
  filter: drop-shadow(0 8px 20px rgba(15,23,42,0.12));
  transition: transform 0.55s cubic-bezier(0.34,1.56,0.64,1);
}
.prod-card:hover .prod-img-box img {
  transform: scale(1.14) translateY(-5px);
}
.disc-badge {
  position: absolute; top: 11px; left: 11px;
  background: linear-gradient(135deg, #ef4444, #dc2626);
  color: #fff; font-size: 9px; font-weight: 800;
  padding: 3px 9px; border-radius: 100px; letter-spacing: 0.5px;
  box-shadow: 0 4px 12px rgba(239,68,68,0.4);
}
.fav-btn {
  position: absolute; top: 11px; right: 11px;
  width: 31px; height: 31px; border-radius: 50%; border: none;
  background: rgba(255,255,255,0.9);
  border: 1px solid rgba(15,23,42,0.10);
  color: var(--muted); font-size: 12px;
  display: flex; align-items: center; justify-content: center;
  transition: all 0.2s;
}
.fav-btn:hover {
  background: rgba(239,68,68,0.15);
  border-color: rgba(239,68,68,0.4); color: #ef4444;
  transform: scale(1.12);
}
.prod-body { padding: 16px 17px 17px; flex: 1; display: flex; flex-direction: column; }
.p-cat { font-size: 9px; letter-spacing: 2px; text-transform: uppercase; color: var(--ember); font-weight: 700; margin-bottom: 5px; }
.p-name {
  font-size: 13px; font-weight: 600; color: #0f172a;
  line-height: 1.4; margin-bottom: 9px; flex: 1;
  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
.p-stars { color: var(--gold); font-size: 10px; margin-bottom: 11px; }
.p-stars span { color: var(--muted); font-size: 9px; }
.p-footer { display: flex; align-items: flex-end; justify-content: space-between; margin-top: auto; }
.p-price {
  font-family: var(--font-d); font-size: 19px; font-weight: 700; color: #0f172a; line-height: 1;
}
.p-old { font-size: 11px; color: var(--muted); text-decoration: line-through; margin-top: 2px; }
.p-add {
  width: 34px; height: 34px; border-radius: 10px; border: none;
  background: linear-gradient(135deg, var(--ember), var(--gold));
  color: #fff; font-size: 18px; font-weight: 700;
  display: flex; align-items: center; justify-content: center;
  transition: transform 0.25s var(--ease-out), box-shadow 0.25s;
  flex-shrink: 0;
}
.p-add:hover {
  transform: scale(1.18) rotate(90deg);
  box-shadow: 0 8px 28px rgba(249,115,22,0.55);
}

/* View all link */
.view-all-row { text-align: center; margin-top: 52px; }

/* ═══ CTA ═════════════════════════════════════════════════════ */
#cta {
  position: relative;
  background: #fffbf5;
  border-top: 1px solid rgba(234,108,0,0.10);
  padding: 100px 0; overflow: hidden;
}
#cta-canvas {
  position: absolute; inset: 0;
  width: 100%; height: 100%; z-index: 0;
  opacity: 0.55;
}
.cta-body { position: relative; z-index: 1; }
.cta-h2 {
  font-family: var(--font-d);
  font-size: clamp(2.4rem, 5.5vw, 4.2rem);
  font-weight: 900; color: #0f172a;
  line-height: 1.0; margin-bottom: 18px;
  letter-spacing: -1.5px;
}
.cta-h2 em {
  font-style: normal;
  background: linear-gradient(120deg, var(--ember), var(--gold));
  -webkit-background-clip: text; -webkit-text-fill-color: transparent;
  background-clip: text;
}
.cta-body p { color: #64748b; }

/* Stats row */
.stat-row { display: flex; flex-wrap: wrap; margin-top: 56px; padding-top: 40px; border-top: 1px solid rgba(15,23,42,0.08); }
.stat-cell { flex: 1; min-width: 120px; text-align: center; padding: 16px 12px; border-right: 1px solid rgba(15,23,42,0.08); }
.stat-cell:last-child { border-right: none; }
.stat-num {
  font-family: var(--font-d); font-size: clamp(2rem, 4vw, 3rem); font-weight: 900;
  background: linear-gradient(120deg, var(--ember), var(--gold));
  -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
  line-height: 1;
}
.stat-lbl { font-size: 12px; color: var(--muted); margin-top: 4px; }

/* ═══ Responsive ══════════════════════════════════════════════ */
@media (max-width: 991px) {
  .hero-3d-scene { display: none !important; }
  .hero-stats { gap: 18px; }
  .feat-row { flex-wrap: wrap; }
  .feat-item { width: 50%; flex: none; border-right: none; border-bottom: 1px solid var(--edge); }
  .feat-item:nth-child(odd) { border-right: 1px solid var(--edge); }
  .feat-item:last-child, .feat-item:nth-last-child(2):nth-child(odd) { border-bottom: none; }
  .stat-cell { border-right: none; border-bottom: 1px solid rgba(15,23,42,0.08); }
  .stat-cell:last-child { border-bottom: none; }
}
@media (max-width: 575px) {
  .hero-h1 { letter-spacing: -2px; }
  .hero-stats { gap: 14px; }
  .hs-val { font-size: 22px; }
  .feat-item { width: 100%; border-right: none; }
  .feat-item:nth-child(odd) { border-right: none; }
  .feat-item:last-child { border-bottom: none; }
}

/* #hero bg set inline above via background: #ffffff in rule */
</style>

<!-- ════ CURSOR ════════════════════════════════════════════════ -->
<div id="ars-cursor-dot"></div>
<div id="ars-cursor-ring"></div>

<!-- ════ HERO ══════════════════════════════════════════════════ -->
<section id="hero">
  <canvas id="hero-canvas"></canvas>
  <div class="hero-orb hero-orb-1"></div>
  <div class="hero-orb hero-orb-2"></div>
  <div class="hero-watermark" aria-hidden="true">ARS</div>

  <div class="container hero-content">
    <div class="row align-items-center">

      <!-- Left: Copy -->
      <div class="col-lg-6">
        <div id="hero-stage">

          <div class="live-badge">
            <span class="live-dot"></span>
            Nepal's #1 Online Store
          </div>

          <h1 class="hero-h1">
            <span class="line-outline">Shop</span>
            <span class="line-grad">Beyond</span>
            <span class="line-solid">Limits.</span>
          </h1>

          <p class="hero-desc">
            Your destination for curated products delivered across Nepal — Birgunj, Parsa and beyond. eSewa &amp; COD accepted.
          </p>

          <div class="hero-cta-wrap">
            <a href="shop.php" class="btn-fire">
              Explore Shop
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
            </a>
            <a href="shop.php?q=" class="btn-void">
              View Deals
            </a>
          </div>

          <div class="hero-stats">
            <div>
              <div class="hs-val" id="stat-prod">500+</div>
              <div class="hs-lbl">Products</div>
            </div>
            <div class="hs-sep"></div>
            <div>
              <div class="hs-val" id="stat-cust">10K+</div>
              <div class="hs-lbl">Customers</div>
            </div>
            <div class="hs-sep"></div>
            <div>
              <div class="hs-val">24/7</div>
              <div class="hs-lbl">Support</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right: 3D floating card -->
      <div class="col-lg-6 hero-3d-scene">
        <!-- Main card -->
        <div class="float-card-main">
          <div class="fcard-label">Featured Drop</div>
          <div class="fcard-img">
            <svg width="90" height="90" viewBox="0 0 90 90" fill="none" xmlns="http://www.w3.org/2000/svg">
              <rect x="15" y="22" width="60" height="46" rx="6" stroke="#f97316" stroke-width="1.5" stroke-dasharray="4 3"/>
              <circle cx="45" cy="45" r="14" stroke="#f59e0b" stroke-width="1.5"/>
              <path d="M30 62 L45 32 L60 62" stroke="#f97316" stroke-width="1.5" stroke-linejoin="round"/>
              <circle cx="45" cy="45" r="4" fill="#f59e0b" fill-opacity="0.4"/>
            </svg>
          </div>
          <div class="fcard-name">Premium Products</div>
          <div class="fcard-sub">Curated for you</div>
          <div class="fcard-footer">
            <div class="fcard-price">Rs. 999+</div>
            <a href="shop.php" class="fcard-btn text-decoration-none" aria-label="Browse shop">→</a>
          </div>
        </div>

        <!-- Chip: delivery -->
        <div class="info-chip chip-top">
          <span class="chip-lbl">Free Delivery</span>
          <span class="chip-val">Rs 1K+</span>
        </div>
        <!-- Chip: secure -->
        <div class="info-chip chip-bot">
          <span class="chip-lbl">Secure Payment</span>
          <span class="chip-val">100%</span>
        </div>
      </div>

    </div>
  </div>

  <div class="hero-scroll" aria-hidden="true">
    <div class="scroll-bar"></div>
    <span>scroll</span>
  </div>
</section>

<!-- ════ FEATURES ══════════════════════════════════════════════ -->
<section id="features">
  <div class="container">
    <div class="feat-row">
      <div class="feat-item reveal">
        <div class="feat-ico" style="background:rgba(59,130,246,0.1);color:#3b82f6;"><i class="bi bi-truck"></i></div>
        <div>
          <div class="feat-title">Free Shipping</div>
          <div class="feat-sub">Orders over Rs. 1,000</div>
        </div>
      </div>
      <div class="feat-item reveal d1">
        <div class="feat-ico" style="background:rgba(34,197,94,0.1);color:#22c55e;"><i class="bi bi-shield-check"></i></div>
        <div>
          <div class="feat-title">Secure Payment</div>
          <div class="feat-sub">100% Protected</div>
        </div>
      </div>
      <div class="feat-item reveal d2">
        <div class="feat-ico" style="background:rgba(249,115,22,0.1);color:var(--ember);"><i class="bi bi-arrow-repeat"></i></div>
        <div>
          <div class="feat-title">Easy Returns</div>
          <div class="feat-sub">7-Day Replacement</div>
        </div>
      </div>
      <div class="feat-item reveal d3">
        <div class="feat-ico" style="background:rgba(168,85,247,0.1);color:#a855f7;"><i class="bi bi-headset"></i></div>
        <div>
          <div class="feat-title">24/7 Support</div>
          <div class="feat-sub">Expert Help</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ════ CATEGORIES ════════════════════════════════════════════ -->
<section id="categories">
  <div class="container">
    <div class="row align-items-end mb-5">
      <div class="col-lg-7 reveal">
        <span class="sec-eyebrow">Discover</span>
        <h2 class="sec-h2">Shop by<br>Category</h2>
        <p class="sec-sub">Every need, one place</p>
      </div>
      <div class="col-lg-5 text-lg-end mt-3 mt-lg-0 reveal d2">
        <a href="shop.php" class="btn-void d-inline-flex">
          All Categories
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
        </a>
      </div>
    </div>

    <?php
    $cat_icons  = ['Electronics'=>'bi-cpu-fill','Fashion'=>'bi-bag-fill','Home'=>'bi-house-fill','Beauty'=>'bi-stars','Sports'=>'bi-bicycle','Books'=>'bi-book-fill','Toys'=>'bi-controller','Food'=>'bi-basket-fill','Groceries'=>'bi-cart-fill','Clothing'=>'bi-person-fill'];
    $cat_bgs    = ['rgba(249,115,22,0.12)','rgba(168,85,247,0.12)','rgba(59,130,246,0.12)','rgba(236,72,153,0.12)','rgba(34,197,94,0.12)','rgba(245,158,11,0.12)'];
    $cat_fgs    = ['#f97316','#a855f7','#3b82f6','#ec4899','#22c55e','#f59e0b'];
    $ci = 0;
    ?>
    <div class="row g-3 g-md-4">
      <?php foreach($categories as $cat):
        $ico = $cat_icons[$cat['name']] ?? 'bi-grid-3x3-gap-fill';
        $bg  = $cat_bgs[$ci % count($cat_bgs)];
        $fg  = $cat_fgs[$ci % count($cat_fgs)];
        $del = round($ci * 0.08, 2);
        $ci++;
      ?>
        <div class="col-4 col-md-2 reveal" style="transition-delay:<?= $del ?>s;">
          <a href="shop.php?category=<?= $cat['id'] ?>" class="cat-tile js-tilt">
            <div class="cat-ico" style="background:<?= $bg ?>;color:<?= $fg ?>;">
              <i class="bi <?= $ico ?>"></i>
            </div>
            <div class="cat-nm"><?= htmlspecialchars($cat['name']) ?></div>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ════ PRODUCTS ══════════════════════════════════════════════ -->
<section id="products">
  <div class="container">
    <div class="row align-items-end mb-5">
      <div class="col-lg-8 reveal">
        <span class="sec-eyebrow">Trending Now</span>
        <h2 class="sec-h2">Featured<br>Products</h2>
        <p class="sec-sub">The hottest items people are buying right now</p>
      </div>
      <div class="col-lg-4 text-lg-end mt-3 mt-lg-0 reveal d2">
        <a href="shop.php" class="btn-void d-inline-flex">
          All Products
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
        </a>
      </div>
    </div>

    <div class="row g-3 g-md-4">
      <?php foreach($latest_products as $i => $p):
        $delay = round(($i % 4) * 0.1, 1);
      ?>
        <div class="col-6 col-lg-3 reveal" style="transition-delay:<?= $delay ?>s;">
          <div class="prod-card js-tilt h-100">
            <div class="prod-img-box">
              <?php if($p['discount_price']): ?>
                <span class="disc-badge">-<?= round((($p['price'] - $p['discount_price']) / $p['price']) * 100) ?>%</span>
              <?php endif; ?>
              <button class="fav-btn" onclick="event.stopPropagation();" aria-label="Add to wishlist">
                <i class="bi bi-heart"></i>
              </button>
              <a href="product.php?slug=<?= $p['slug'] ?>">
                <img src="<?= getProductImage($p['image']) ?>"
                     alt="<?= htmlspecialchars($p['name']) ?>"
                     loading="lazy">
              </a>
            </div>
            <div class="prod-body">
              <div class="p-cat"><?= htmlspecialchars($p['cat_name'] ?? 'General') ?></div>
              <a href="product.php?slug=<?= $p['slug'] ?>" class="text-decoration-none">
                <div class="p-name"><?= htmlspecialchars($p['name']) ?></div>
              </a>
              <div class="p-stars">
                <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                <i class="bi bi-star-half"></i>
                <span>(24)</span>
              </div>
              <div class="p-footer">
                <div>
                  <div class="p-price"><?= formatPrice($p['discount_price'] ?: $p['price']) ?></div>
                  <?php if($p['discount_price']): ?>
                    <div class="p-old"><?= formatPrice($p['price']) ?></div>
                  <?php endif; ?>
                </div>
                <button class="p-add" onclick="addToCart(<?= $p['id'] ?>)" aria-label="Add to cart">
                  <i class="bi bi-plus"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="view-all-row reveal">
      <a href="shop.php" class="btn-void d-inline-flex" style="padding:15px 40px;font-size:14px;">
        View All Products &nbsp;
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
      </a>
    </div>
  </div>
</section>

<!-- ════ CTA ════════════════════════════════════════════════════ -->
<section id="cta">
  <canvas id="cta-canvas"></canvas>
  <div class="container cta-body">
    <div class="row align-items-center gy-5">
      <div class="col-lg-7 reveal">
        <span class="sec-eyebrow">Join the Community</span>
        <h2 class="cta-h2">
          Experience<br>
          <em>Premium</em><br>
          Shopping in Nepal.
        </h2>
        <p style="color:var(--muted);font-size:15px;font-weight:300;max-width:400px;line-height:1.75;margin-top:16px;">
          Join 10,000+ satisfied customers. Exclusive deals, fast delivery to Birgunj and across Nepal, with 24/7 support.
        </p>
      </div>
      <div class="col-lg-4 offset-lg-1 text-center reveal d3">
        <a href="auth/signup.php" class="btn-fire d-inline-flex" style="padding:17px 40px;font-size:15px;">
          <i class="bi bi-person-plus"></i>
          Create Account
        </a>
        <p style="color:var(--muted);font-size:11px;margin-top:12px;">No Credit Card Required</p>
      </div>
    </div>

    <!-- Stats -->
    <div class="stat-row reveal">
      <div class="stat-cell">
        <div class="stat-num">500+</div>
        <div class="stat-lbl">Products</div>
      </div>
      <div class="stat-cell">
        <div class="stat-num">10K+</div>
        <div class="stat-lbl">Customers</div>
      </div>
      <div class="stat-cell">
        <div class="stat-num">7</div>
        <div class="stat-lbl">Day Returns</div>
      </div>
      <div class="stat-cell">
        <div class="stat-num">24/7</div>
        <div class="stat-lbl">Support</div>
      </div>
    </div>
  </div>
</section>

<!-- ════ SCRIPTS ════════════════════════════════════════════════ -->
<script>
/* ────────────────────────────────────────────────────────────
   ARS 3D Immersive Landing — Interactive Engine
   ──────────────────────────────────────────────────────────── */

// ── Custom Cursor ──────────────────────────────────────────
(function(){
  const dot  = document.getElementById('ars-cursor-dot');
  const ring = document.getElementById('ars-cursor-ring');
  if (!dot || !ring) return;
  let mx = -200, my = -200, rx = -200, ry = -200;
  document.addEventListener('mousemove', e => { mx = e.clientX; my = e.clientY; }, { passive: true });
  (function loop() {
    rx += (mx - rx) * 0.15;
    ry += (my - ry) * 0.15;
    dot.style.left  = mx + 'px'; dot.style.top  = my + 'px';
    ring.style.left = rx + 'px'; ring.style.top = ry + 'px';
    requestAnimationFrame(loop);
  })();
  // Expand ring on interactive elements
  const targets = 'a,button,.cat-tile,.prod-card,.js-tilt';
  document.querySelectorAll(targets).forEach(el => {
    el.addEventListener('mouseenter', () => ring.classList.add('active'));
    el.addEventListener('mouseleave', () => ring.classList.remove('active'));
  });
  document.addEventListener('mouseleave', () => { dot.style.opacity='0'; ring.style.opacity='0'; });
  document.addEventListener('mouseenter', () => { dot.style.opacity='1'; ring.style.opacity='1'; });
})();

// ── Hero Particle Constellation ────────────────────────────
(function(){
  const canvas = document.getElementById('hero-canvas');
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  let W, H, animId;
  const COUNT = 90, LINK = 130;
  const mouse = { x: -9999, y: -9999 };
  let pts = [];

  function resize(){
    W = canvas.width  = canvas.offsetWidth;
    H = canvas.height = canvas.offsetHeight;
    // Reset particles on resize
    pts = Array.from({ length: COUNT }, () => ({
      x: Math.random() * W,
      y: Math.random() * H,
      vx: (Math.random() - 0.5) * 0.45,
      vy: (Math.random() - 0.5) * 0.45,
      r: Math.random() * 1.8 + 0.4,
      a: Math.random() * 0.45 + 0.08
    }));
  }
  resize();
  window.addEventListener('resize', resize, { passive: true });
  canvas.addEventListener('mousemove', e => {
    const r = canvas.getBoundingClientRect();
    mouse.x = e.clientX - r.left;
    mouse.y = e.clientY - r.top;
  }, { passive: true });
  canvas.addEventListener('mouseleave', () => { mouse.x = -9999; mouse.y = -9999; });

  function frame(){
    ctx.clearRect(0, 0, W, H);
    for (let i = 0; i < pts.length; i++){
      const p = pts[i];
      // Mouse repulsion
      const dx = p.x - mouse.x, dy = p.y - mouse.y;
      const d2 = dx*dx + dy*dy;
      if (d2 < 9000) {
        const f = 2.5 / Math.sqrt(d2);
        p.vx += dx * f * 0.012;
        p.vy += dy * f * 0.012;
      }
      p.vx *= 0.988; p.vy *= 0.988;
      p.x += p.vx; p.y += p.vy;
      if (p.x < 0) { p.x = 0; p.vx *= -1; }
      if (p.x > W) { p.x = W; p.vx *= -1; }
      if (p.y < 0) { p.y = 0; p.vy *= -1; }
      if (p.y > H) { p.y = H; p.vy *= -1; }

      // Draw node
      ctx.beginPath();
      ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
      ctx.fillStyle = 'rgba(200,100,30,' + (p.a * 0.55) + ')';
      ctx.fill();

      // Draw connections
      for (let j = i + 1; j < pts.length; j++){
        const q = pts[j];
        const ex = p.x - q.x, ey = p.y - q.y;
        const ed = Math.sqrt(ex*ex + ey*ey);
        if (ed < LINK){
          const alpha = 0.07 * (1 - ed / LINK);
          ctx.beginPath();
          ctx.moveTo(p.x, p.y);
          ctx.lineTo(q.x, q.y);
          ctx.strokeStyle = 'rgba(180,90,20,' + alpha + ')';
          ctx.lineWidth = 0.5;
          ctx.stroke();
        }
      }
    }
    animId = requestAnimationFrame(frame);
  }
  frame();
})();

// ── Hero 3D Mouse Parallax ─────────────────────────────────
(function(){
  const hero  = document.getElementById('hero');
  const stage = document.getElementById('hero-stage');
  if (!hero || !stage) return;
  let ticking = false;
  hero.addEventListener('mousemove', e => {
    if (ticking) return; ticking = true;
    requestAnimationFrame(() => {
      const r = hero.getBoundingClientRect();
      const x = ((e.clientX - r.left) / r.width  - 0.5);
      const y = ((e.clientY - r.top)  / r.height - 0.5);
      stage.style.transform = `perspective(1100px) rotateY(${x * 7}deg) rotateX(${-y * 5}deg)`;
      stage.style.transition = 'transform 0.04s linear';
      ticking = false;
    });
  }, { passive: true });
  hero.addEventListener('mouseleave', () => {
    stage.style.transition = 'transform 0.9s cubic-bezier(0.22,1,0.36,1)';
    stage.style.transform = 'perspective(1100px) rotateY(0deg) rotateX(0deg)';
  });
})();

// ── 3D Tilt — Cards ────────────────────────────────────────
(function(){
  document.querySelectorAll('.js-tilt').forEach(el => {
    let raf;
    el.addEventListener('mousemove', e => {
      cancelAnimationFrame(raf);
      raf = requestAnimationFrame(() => {
        const r  = el.getBoundingClientRect();
        const x  = (e.clientX - r.left) / r.width  - 0.5;
        const y  = (e.clientY - r.top)  / r.height - 0.5;
        el.style.transform  = `perspective(700px) rotateY(${x * 14}deg) rotateX(${-y * 11}deg) scale(1.03)`;
        el.style.transition = 'transform 0.04s linear';
      });
    }, { passive: true });
    el.addEventListener('mouseleave', () => {
      cancelAnimationFrame(raf);
      el.style.transition = 'transform 0.55s cubic-bezier(0.22,1,0.36,1)';
      el.style.transform  = 'perspective(700px) rotateY(0deg) rotateX(0deg) scale(1)';
    });
  });
})();

// ── CTA Wave Canvas ─────────────────────────────────────────
(function(){
  const canvas = document.getElementById('cta-canvas');
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  let W, H, t = 0;
  function resize(){ W = canvas.width = canvas.offsetWidth; H = canvas.height = canvas.offsetHeight; }
  resize();
  window.addEventListener('resize', resize, { passive: true });
  function draw(){
    ctx.clearRect(0, 0, W, H);
    for (let i = 0; i < 6; i++){
      ctx.beginPath();
      const baseY = H * (0.15 + i * 0.14);
      ctx.moveTo(0, baseY);
      for (let x = 0; x <= W; x += 8){
        const y = baseY
          + Math.sin((x / W * Math.PI * 2.5) + t + i * 0.7) * 45
          + Math.sin((x / W * Math.PI * 1.2) + t * 0.5 + i) * 20;
        ctx.lineTo(x, y);
      }
      const alpha = 0.025 + i * 0.012;
      ctx.strokeStyle = i % 2 === 0
        ? `rgba(234,108,0,${alpha})`
        : `rgba(217,119,6,${alpha})`;
      ctx.lineWidth = 1.5;
      ctx.stroke();
    }
    t += 0.0035;
    requestAnimationFrame(draw);
  }
  draw();
})();

// ── Scroll Reveal ────────────────────────────────────────────
(function(){
  const observer = new IntersectionObserver(entries => {
    entries.forEach(e => {
      if (e.isIntersecting){ e.target.classList.add('visible'); observer.unobserve(e.target); }
    });
  }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
  document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
})();

// ── Cart ──────────────────────────────────────────────────────
function addToCart(productId) {
  window.location.href = 'cart-action.php?action=add&id=' + productId;
}
</script>

<?php require_once __DIR__ . '/includes/footer-bootstrap.php'; ?>
