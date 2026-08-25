<x-layouts.app title="أنشئ لعبتك الخاصة — سوالف">
@php
  $classifications = $classifications ?? \App\Models\Classification::query()->where('is_active', true)->orderBy('sort_order')->get();
@endphp
<div class="swalif-custom-create-page">

  <style>
  /* ══════════════════════════════════════════════════════════
     Custom Game Creation Page - Premium Design & Dark Mode
  ══════════════════════════════════════════════════════════ */
  .swalif-custom-create-page {
    direction: rtl;
    min-height: 100vh;
    padding-bottom: 110px;
    background: var(--bg-main, #F8FAFC);
    color: var(--text-main, #0F172A);
  }

  body.dark .swalif-custom-create-page,
  html.dark .swalif-custom-create-page {
    background: #0B1020 !important;
    color: #F8FAFC !important;
  }

  /* Curved Hero Header */
  .custom-hero-banner {
    background: linear-gradient(135deg, #FF6D00 0%, #FF1744 60%, #E64A19 100%);
    padding: 44px 16px 68px;
    text-align: center;
    color: #fff;
    border-bottom-left-radius: 50% 30px;
    border-bottom-right-radius: 50% 30px;
    box-shadow: 0 12px 32px rgba(255,109,0,.25);
    margin-bottom: 30px;
  }

  body.dark .custom-hero-banner,
  html.dark .custom-hero-banner {
    background: linear-gradient(135deg, #E65100 0%, #D50000 50%, #7C3AED 100%);
    box-shadow: 0 12px 36px rgba(0,0,0,.5);
  }

  .custom-hero-banner h1 {
    font-size: clamp(2rem, 4.5vw, 3rem);
    font-weight: 900;
    margin-bottom: 12px;
    color: #fff !important;
    text-shadow: 0 3px 12px rgba(0,0,0,.2);
  }

  .custom-hero-banner p {
    font-size: 1.05rem;
    opacity: .95;
    color: #fff !important;
    max-width: 620px;
    margin: 0 auto 28px;
    line-height: 1.6;
  }

  /* Nav Pills Header */
  .top-pills-row {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    flex-wrap: wrap;
    max-width: 880px;
    margin: 0 auto;
    width: 100%;
  }

  @media (max-width: 768px) {
    .custom-hero-banner {
      padding: 28px 12px 48px !important;
      overflow: visible !important;
    }
    .custom-hero-banner .container {
      padding-left: 8px !important;
      padding-right: 8px !important;
      width: 100% !important;
      max-width: 100% !important;
      box-sizing: border-box !important;
    }
    .custom-hero-banner h1 {
      font-size: 1.85rem !important;
      margin-bottom: 8px !important;
    }
    .custom-hero-banner p {
      font-size: 0.92rem !important;
      margin-bottom: 20px !important;
      line-height: 1.5 !important;
    }
    .top-pills-row {
      display: flex !important;
      flex-wrap: wrap !important;
      justify-content: center !important;
      align-items: center !important;
      gap: 8px 6px !important;
      padding: 0 !important;
      margin: 0 auto !important;
      width: 100% !important;
      max-width: 440px !important;
      overflow: visible !important;
    }
    .header-pill {
      flex: 0 1 auto !important;
      white-space: nowrap !important;
      padding: 8px 14px !important;
      font-size: 0.86rem !important;
      box-shadow: 0 3px 10px rgba(0,0,0,.22) !important;
      gap: 4px !important;
    }
    .fav-pill-count {
      padding: 1px 6px !important;
      font-size: 0.78rem !important;
    }
  }

  @media (max-width: 380px) {
    .top-pills-row {
      gap: 6px 4px !important;
    }
    .header-pill {
      padding: 7px 10px !important;
      font-size: 0.78rem !important;
    }
  }

  .header-pill {
    padding: 10px 24px;
    border-radius: 50px;
    font-size: .95rem;
    font-weight: 800;
    color: #fff !important;
    text-decoration: none;
    border: 2px solid rgba(255,255,255,.35);
    cursor: pointer;
    font-family: inherit;
    box-shadow: 0 4px 14px rgba(0,0,0,.18);
    transition: transform .2s, box-shadow .2s, border-color .2s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    white-space: nowrap;
    flex-shrink: 0;
  }

  .header-pill:hover {
    transform: translateY(-2px) scale(1.03);
    box-shadow: 0 6px 20px rgba(0,0,0,.28);
    border-color: #fff;
  }

  .header-pill--blue { background: #00BCD4; }
  .header-pill--grey { background: #546E7A; }
  .header-pill--green { background: #00C853; }
  .header-pill--red { background: #FF1744; }
  .header-pill--orange { background: #FF6D00; }
  .header-pill--purple { background: #7C3AED; }

  .is-active-pill {
    border-color: #fff !important;
    box-shadow: 0 0 0 3px rgba(255,255,255,.6), 0 6px 20px rgba(0,0,0,.3) !important;
    transform: scale(1.05);
  }

  .fav-pill-count {
    font-size: .85rem;
    opacity: .9;
    background: rgba(0,0,0,.2);
    padding: 2px 8px;
    border-radius: 20px;
  }

  /* Selection Heading & Counter */
  .selection-heading-wrap {
    text-align: center;
    margin-bottom: 24px;
  }

  .selection-heading-wrap h2 {
    font-size: 1.8rem;
    font-weight: 900;
    color: inherit;
    margin-bottom: 6px;
  }

  .selection-heading-wrap p {
    color: #64748B;
    font-size: .95rem;
    margin-bottom: 16px;
  }

  body.dark .selection-heading-wrap p,
  html.dark .selection-heading-wrap p {
    color: #94A3B8;
  }

  .selection-counter-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 28px;
    border-radius: 50px;
    background: linear-gradient(135deg, #FF6D00, #FF1744);
    color: #fff;
    font-weight: 900;
    font-size: 1.15rem;
    box-shadow: 0 6px 22px rgba(255,109,0,.38);
    transition: all .3s;
  }

  .selection-counter-badge.is-ready {
    background: linear-gradient(135deg, #00C853, #00897B);
    box-shadow: 0 6px 22px rgba(0,200,83,.38);
  }

  /* Controls Row */
  .controls-bar {
    display: flex;
    flex-direction: column;
    gap: 14px;
    margin-bottom: 30px;
    align-items: center;
    width: 100%;
  }

  .search-box {
    width: 100%;
    max-width: 580px;
    position: relative;
  }

  .search-box input {
    width: 100%;
    padding: 14px 44px 14px 18px;
    border-radius: 50px;
    border: 2px solid #FF6D00;
    font-family: inherit;
    font-size: .95rem;
    outline: none;
    background: #FFFFFF;
    color: #0F172A;
    box-shadow: 0 4px 16px rgba(255,109,0,.1);
    box-sizing: border-box;
  }

  body.dark .search-box input, html.dark .search-box input {
    background: rgba(255,255,255,.07);
    border-color: #FF6D00;
    color: #FFFFFF;
    box-shadow: 0 4px 20px rgba(0,0,0,.3);
  }

  .search-box svg {
    position: absolute;
    right: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: #FF6D00;
  }

  .filter-actions-cluster {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    justify-content: center;
    width: 100%;
  }

  .filters-pills-group {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    justify-content: center;
  }

  .pill-btn {
    padding: 8px 18px;
    border-radius: 50px;
    border: 1.5px solid #CBD5E1;
    background: #FFFFFF;
    color: #334155;
    font-size: .88rem;
    font-weight: 700;
    cursor: pointer;
    transition: all .2s;
  }

  body.dark .pill-btn, html.dark .pill-btn {
    background: rgba(255,255,255,.06);
    border-color: rgba(255,255,255,.14);
    color: #E2E8F0;
  }

  .pill-btn.active, .pill-btn:hover {
    background: #FF6D00 !important;
    color: #fff !important;
    border-color: #FF6D00 !important;
  }

  /* Accordions */
  .accordion-categories {
    display: flex;
    flex-direction: column;
    gap: 28px;
    margin-bottom: 44px;
  }

  .accordion-section {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    width: 100%;
    background: transparent;
  }

  .accordion-header {
    width: auto;
    max-width: calc(100% - 16px);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    padding: 10px 12px 10px 18px;
    background: linear-gradient(135deg, #FF6D00 0%, #FF8F00 100%);
    color: #fff;
    border: none;
    border-radius: 999px;
    cursor: pointer;
    font-family: inherit;
    box-shadow: 0 4px 14px rgba(255,109,0,.22);
    transition: transform .2s, box-shadow .2s;
    margin: 0;
  }

  body.dark .accordion-header, html.dark .accordion-header {
    background: linear-gradient(135deg, #E65100 0%, #F57C00 100%);
    box-shadow: 0 6px 20px rgba(0,0,0,.4);
  }

  .accordion-header:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(255,109,0,.32);
  }

  .accordion-header-title-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 1.05rem;
    font-weight: 800;
    white-space: nowrap;
  }

  .accordion-toggle-circle {
    flex-shrink: 0;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: rgba(255,255,255,.25);
    color: #fff;
    font-size: 17px;
    font-weight: 900;
    line-height: 1;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .accordion-header.is-open .accordion-toggle-circle {
    background: rgba(0,0,0,.18);
  }

  .accordion-body {
    display: none;
    width: 100%;
    border: 2px solid rgba(255,109,0,.22);
    border-radius: 20px;
    background: rgba(255,255,255,.78);
    padding: 14px 14px 6px;
    box-shadow: 0 4px 18px rgba(15,23,42,.06);
  }

  body.dark .accordion-body, html.dark .accordion-body {
    background: rgba(255,255,255,.04);
    border-color: rgba(255,109,0,.28);
    box-shadow: 0 6px 22px rgba(0,0,0,.28);
  }

  .accordion-body.is-open {
    display: block;
    animation: accFadeIn .25s ease;
  }

  @keyframes accFadeIn {
    from { opacity: 0; transform: translateY(-8px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  /* Cards Grid */
  .custom-cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)) !important;
    gap: 20px !important;
    padding: 4px 0 12px !important;
  }

  .card-item-box {
    position: relative;
    border-radius: 20px;
    overflow: hidden;
    background: #FFFFFF;
    box-shadow: 0 6px 20px rgba(0,0,0,.08);
    border: 3px solid transparent;
    transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
    display: flex;
    flex-direction: column;
    cursor: pointer;
    user-select: none;
  }

  body.dark .card-item-box, html.dark .card-item-box {
    background: rgba(255,255,255,.05);
    box-shadow: 0 6px 24px rgba(0,0,0,.4);
    border-color: rgba(255,255,255,.08);
  }

  .card-item-box:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 32px rgba(255,109,0,.3);
    border-color: #FF6D00;
  }

  .card-item-box.is-selected {
    border-color: #00C853 !important;
    box-shadow: 0 0 0 4px rgba(0,200,83,.4), 0 12px 32px rgba(0,200,83,.3) !important;
    transform: scale(1.03);
  }

  /* Green Checkmark Badge ✔ for Selected Category Cards */
  .selected-check-badge {
    display: none;
    position: absolute;
    top: 10px;
    right: 10px;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: linear-gradient(135deg, #00C853, #00E676);
    color: #ffffff;
    font-size: 18px;
    font-weight: 900;
    align-items: center;
    justify-content: center;
    z-index: 30;
    box-shadow: 0 4px 14px rgba(0,200,83,.5);
    animation: checkBadgePop .28s ease;
  }

  .card-item-box.is-selected .selected-check-badge {
    display: flex !important;
  }

  .card-item-box.is-selected .card-item-badge {
    right: 8px !important;
  }

  @keyframes checkBadgePop {
    0% { transform: scale(0) rotate(-45deg); }
    70% { transform: scale(1.2) rotate(10deg); }
    100% { transform: scale(1) rotate(0deg); }
  }

  .card-item-box.is-disabled {
    opacity: 0.45;
    cursor: not-allowed;
    pointer-events: none;
  }

  /* Favorite Direct Heart Button */
  .card-item-fav-direct {
    position: absolute;
    top: 10px;
    left: 10px;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: rgba(255,255,255,.92);
    color: #FF1744;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    cursor: pointer;
    z-index: 25;
    box-shadow: 0 3px 10px rgba(0,0,0,.2);
    transition: transform .2s, background .2s;
    outline: none;
  }

  .card-item-fav-direct:hover { transform: scale(1.18); background: #ffffff; }
  .card-item-fav-direct.is-fav { background: #FF1744; color: #ffffff; box-shadow: 0 4px 14px rgba(255,23,68,.4); }

  .card-item-image-wrap {
    width: 100%;
    height: 180px;
    background: linear-gradient(180deg, #F0F4F8 0%, #E2E8F0 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    position: relative;
  }

  body.dark .card-item-image-wrap, html.dark .card-item-image-wrap {
    background: rgba(255,255,255,.03);
  }

  .card-item-image-wrap img { width: 100%; height: 100%; object-fit: cover; transition: transform .3s ease; }
  .card-item-box:hover .card-item-image-wrap img { transform: scale(1.06); }
  .card-item-fallback-icon { font-size: 3.8rem; line-height: 1; }

  .card-item-footer-bar {
    background: linear-gradient(135deg, #FF6D00 0%, #FF5722 100%);
    color: #ffffff;
    padding: 12px 10px;
    text-align: center;
    font-weight: 900;
    font-size: 1.05rem;
    line-height: 1.25;
    border-bottom-left-radius: 17px;
    border-bottom-right-radius: 17px;
    min-height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .card-item-name {
    color: #ffffff !important;
    font-weight: 900;
    font-size: 1.05rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 100%;
  }

  .card-item-info {
    position: absolute;
    top: 8px;
    left: 44px;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #0288D1;
    color: #ffffff;
    font-family: serif;
    font-weight: bold;
    font-style: italic;
    font-size: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 20;
    box-shadow: 0 3px 8px rgba(0,0,0,.3);
    border: none;
    cursor: pointer;
    outline: none;
    transition: transform .18s, background .18s;
  }

  .card-item-badge {
    position: absolute;
    top: 8px;
    left: 50%;
    right: auto;
    bottom: auto !important;
    transform: translateX(-50%);
    background: linear-gradient(135deg, #FF1744, #D50000);
    color: #ffffff;
    font-size: .78rem;
    font-weight: 800;
    padding: 4px 12px;
    border-radius: 10px;
    z-index: 15;
    box-shadow: 0 3px 10px rgba(213,0,0,.4);
    pointer-events: none;
    white-space: nowrap;
  }

  .card-info-popover {
    position: absolute;
    top: 46px;
    right: 8px;
    left: 8px;
    background: #181E2B;
    color: #ffffff;
    border-radius: 16px;
    padding: 16px 14px;
    z-index: 80;
    box-shadow: 0 12px 32px rgba(0,0,0,.6), 0 0 0 1px rgba(255,255,255,.1);
    text-align: center;
    direction: rtl;
  }

  .popover-arrow {
    position: absolute;
    top: -8px;
    left: 18px;
    width: 0; height: 0;
    border-left: 8px solid transparent;
    border-right: 8px solid transparent;
    border-bottom: 8px solid #181E2B;
  }

  .popover-desc { font-size: .88rem; font-weight: 700; line-height: 1.45; color: #E2E8F0; margin: 0 0 14px; }
  .popover-actions { display: flex; flex-direction: column; gap: 8px; }
  .popover-btn { display: flex; align-items: center; justify-content: center; width: 100%; padding: 10px 14px; border-radius: 50px; font-family: inherit; font-size: .88rem; font-weight: 800; border: none; cursor: pointer; text-decoration: none; transition: transform .18s; box-sizing: border-box; }
  .popover-btn--fav { background: linear-gradient(135deg, #FF1744, #D50000); color: #fff !important; }

  /* Teams Section */
  .teams-info-card {
    background: #FFFFFF;
    border-radius: 28px;
    padding: 40px 28px;
    border: 2px solid #ECE6DE;
    box-shadow: 0 12px 36px rgba(0,0,0,.06);
    max-width: 780px;
    margin: 0 auto 40px;
    text-align: center;
  }

  body.dark .teams-info-card, html.dark .teams-info-card {
    background: rgba(255,255,255,.05);
    border-color: rgba(255,255,255,.1);
    box-shadow: 0 12px 40px rgba(0,0,0,.4);
  }

  .teams-info-title {
    font-size: 1.9rem;
    font-weight: 900;
    color: inherit;
    margin-bottom: 24px;
  }

  .game-title-field {
    max-width: 420px;
    margin: 0 auto 28px;
  }

  .pill-input {
    width: 100%;
    padding: 14px 26px;
    border-radius: 50px;
    border: 2px solid #DCD6CD;
    font-family: inherit;
    font-size: 1rem;
    font-weight: 700;
    text-align: center;
    background: #FFFFFF;
    color: #0F172A;
    outline: none;
    transition: border-color .2s, box-shadow .2s;
    box-sizing: border-box;
  }

  body.dark .pill-input, html.dark .pill-input {
    background: rgba(255,255,255,.07);
    border-color: rgba(255,255,255,.16);
    color: #FFFFFF;
  }

  .pill-input:focus {
    border-color: #FF6D00;
    box-shadow: 0 0 0 4px rgba(255,109,0,.14);
  }

  .teams-two-col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
    margin-bottom: 28px;
  }

  @media (max-width: 580px) {
    .teams-two-col { grid-template-columns: 1fr; gap: 18px; }
  }

  .team-block-title { font-size: 1.2rem; font-weight: 800; margin-bottom: 10px; color: inherit; }
  .helpers-sub-title { font-size: .85rem; font-weight: 700; color: #64748B; margin-bottom: 10px; }
  body.dark .helpers-sub-title, html.dark .helpers-sub-title { color: #94A3B8; }
  .helpers-pills-row { display: flex; align-items: center; justify-content: center; gap: 8px; flex-wrap: wrap; }
  .helper-mini-badge { padding: 6px 14px; border-radius: 50px; font-size: .8rem; font-weight: 800; background: rgba(255,109,0,.1); color: #FF6D00; border: 1px solid rgba(255,109,0,.2); display: inline-flex; align-items: center; gap: 4px; }

  /* Submit Action Button */
  .start-game-btn {
    display: block;
    margin: 0 auto;
    width: 100%;
    max-width: 300px;
    padding: 16px 36px;
    border-radius: 50px;
    border: none;
    font-family: inherit;
    font-size: 1.2rem;
    font-weight: 900;
    cursor: pointer;
    transition: all .25s;
    background: linear-gradient(135deg, #FF3D00 0%, #FF9100 100%);
    color: #fff;
    box-shadow: 0 8px 28px rgba(255,61,0,.4);
  }

  .start-game-btn:hover:not(:disabled) {
    transform: translateY(-3px) scale(1.02);
    box-shadow: 0 12px 34px rgba(255,61,0,.5);
  }

  .start-game-btn:disabled {
    background: #ccc !important;
    color: #888 !important;
    box-shadow: none !important;
    cursor: not-allowed !important;
    transform: none !important;
  }

  .validation-hint { text-align: center; font-size: .88rem; color: #94A3B8; margin-top: 12px; }
  .validation-hint.is-ready { color: #00C853; font-weight: 700; }

  /* Mobile Floating Sticky Bar */
  .mobile-sticky-action-bar {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: #181E2B;
    color: #fff;
    padding: 12px 20px;
    z-index: 990;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 -8px 28px rgba(0,0,0,.4);
    border-top: 2px solid rgba(255,255,255,.1);
    transform: translateY(100%);
    transition: transform .3s ease;
  }

  .mobile-sticky-action-bar.is-visible { transform: translateY(0); }
  .sticky-bar-info { display: flex; align-items: center; gap: 8px; font-size: .95rem; font-weight: 800; }
  .sticky-bar-info strong { color: #FF6D00; font-size: 1.1rem; }
  .sticky-bar-btn { padding: 10px 20px; border-radius: 50px; font-weight: 900; font-size: .95rem; background: linear-gradient(135deg, #FF6D00, #FF1744); color: #fff; border: none; cursor: pointer; }
  .sticky-bar-btn:disabled { opacity: 0.5; cursor: not-allowed; }

  /* Random Picker Modal */
  .random-picker-modal { position: fixed; inset: 0; z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 16px; }
  .random-picker-modal[hidden] { display: none !important; }
  .random-picker-backdrop { position: absolute; inset: 0; background: rgba(15,17,23,.75); backdrop-filter: blur(8px); }
  .random-picker-dialog { position: relative; z-index: 10; width: 100%; max-width: 480px; background: #181E2B; color: #fff; border-radius: 28px; padding: 32px 24px; text-align: center; box-shadow: 0 20px 50px rgba(0,0,0,.5), 0 0 0 2px rgba(255,255,255,.1); animation: modalPop .3s ease; }
  .random-picker-close { position: absolute; top: 16px; left: 16px; width: 34px; height: 34px; border-radius: 50%; background: rgba(255,255,255,.1); color: #fff; border: none; font-size: 20px; cursor: pointer; }
  .spinner-dice-anim { font-size: 4rem; animation: diceSpin 1s infinite linear; margin-bottom: 14px; }
  .spinner-title { font-size: 1.4rem; font-weight: 900; margin-bottom: 4px; color: #fff; }
  .spinner-sub { color: #94A3B8; font-size: .95rem; }
  .result-badge-icon { font-size: 4.2rem; margin-bottom: 8px; }
  .result-title { font-size: 1.7rem; font-weight: 900; color: #FF6D00; margin-bottom: 6px; }
  .result-desc { color: #CBD5E1; font-size: .95rem; margin-bottom: 24px; }
  .result-actions { display: flex; flex-direction: column; gap: 12px; }

  #pickOneRandomBtn {
    background: rgba(255, 255, 255, 0.08) !important;
    color: #FFFFFF !important;
    border: 2px solid rgba(255, 255, 255, 0.25) !important;
    border-radius: 50px !important;
    padding: 14px 22px !important;
    font-weight: 800 !important;
    font-size: 1rem !important;
    width: 100% !important;
    box-sizing: border-box !important;
    transition: all 0.25s ease !important;
    cursor: pointer !important;
  }

  #pickOneRandomBtn:hover {
    background: #FF6D00 !important;
    color: #FFFFFF !important;
    border-color: #FF6D00 !important;
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(255, 109, 0, 0.4) !important;
  }

  #pickSixRandomBtn {
    background: linear-gradient(135deg, #FF1744, #FF6D00) !important;
    color: #FFFFFF !important;
    border: none !important;
    border-radius: 50px !important;
    padding: 15px 22px !important;
    font-weight: 900 !important;
    font-size: 1.05rem !important;
    width: 100% !important;
    box-sizing: border-box !important;
    box-shadow: 0 8px 24px rgba(255, 23, 68, 0.4) !important;
    transition: all 0.25s ease !important;
    cursor: pointer !important;
  }

  #pickSixRandomBtn:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 32px rgba(255, 23, 68, 0.6) !important;
  }

  @media (max-width: 600px) {
    .custom-cards-grid { grid-template-columns: repeat(2, 1fr) !important; gap: 14px !important; }
    .card-item-image-wrap { height: 145px !important; }
    .card-item-footer-bar { padding: 10px 6px !important; font-size: .92rem !important; min-height: 44px !important; }
    .card-item-name { font-size: .92rem !important; }
    .card-item-fav-direct { width: 28px !important; height: 28px !important; font-size: 14px !important; top: 6px !important; left: 6px !important; }
    .card-item-info { width: 28px !important; height: 28px !important; font-size: 14px !important; top: 6px !important; left: 38px !important; }
    .selected-check-badge { width: 28px !important; height: 28px !important; font-size: 15px !important; top: 6px !important; right: 6px !important; }
    .card-item-badge { bottom: 6px !important; right: 6px !important; font-size: .7rem !important; padding: 2px 8px !important; }
  }
  </style>

  <!-- Curved Header Banner -->
  <section class="custom-hero-banner">
    <div class="container">
      <h1>إنشاء لعبة</h1>
      <p>أنشئ لعبتك الخاصة بنفسك واختبر معلوماتك واستمتع مع أصحابك<br>اختر من 4 إلى 6 فئات وحدد الفرق لتبدأ اللعب</p>

      <!-- Horizontal Pills Row -->
      <div class="top-pills-row" id="topPillsRow">
        <button type="button" class="header-pill header-pill--red is-active-pill" data-top-filter="custom">🚀 إنشاء لعبة</button>
        <button type="button" class="header-pill header-pill--grey" data-top-filter="favorites">❤️ المفضلة <span class="fav-pill-count" id="topFavCount">(0)</span></button>
        <a href="{{ route('categories.index') }}" class="header-pill header-pill--blue" data-top-filter="all">🎯 الألعاب</a>
        <button type="button" class="header-pill header-pill--green" data-top-filter="فكر">🎲 فكروابدأ</button>
        <button type="button" class="header-pill header-pill--orange" data-top-filter="صممت لك">✨ صممت لك</button>
        <button type="button" class="header-pill header-pill--purple" data-top-filter="إمارات">🇦🇪 إمارات</button>
      </div>
    </div>
  </section>

  <main class="container">
    <x-back-button :href="route('categories.index')" label="رجوع للفئات" />

    <form method="POST" action="{{ route('custom-game.store') }}" id="customGameForm">
      @csrf

      <!-- Selection Heading & Counter -->
      <div class="selection-heading-wrap">
        <h2>اختر الفئات</h2>
        <p>اختر من 4 إلى 6 فئات لإنشاء لعبتك المخصصة</p>

        <div class="selection-counter-badge" id="selectionBadge">
          🎯 اختياراتك: <span id="selCount">0</span> / 6
        </div>
      </div>

      <!-- Controls Row -->
      <div class="controls-bar">
        <div class="search-box">
          <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
          <input type="text" id="categorySearch" placeholder="ابحث عن فئة… مثلاً: تاريخ، رياضة، سياحة" />
        </div>

        <div class="filter-actions-cluster">
          <div class="filters-pills-group" id="categoryFilters">
            <button type="button" class="pill-btn active" data-filter="all">الكل</button>
            <button type="button" class="pill-btn" data-filter="favorites" id="favFilterBtn">❤️ المفضلة (<span id="favCount">0</span>)</button>
            @foreach($classifications as $classification)
              <button type="button" class="pill-btn" data-filter="c{{ $classification->id }}">
                {{ $classification->icon ? $classification->icon.' ' : '' }}{{ $classification->name_ar }}
              </button>
            @endforeach
          </div>
        </div>
      </div>

      <!-- Hidden Category Inputs -->
      <div id="hiddenInputs"></div>

      <!-- Categories Accordion List -->
      <div class="accordion-categories" id="accordionCategories">

        @foreach($classifications as $classification)
          @php
            $classCats = $categories->where('classification_id', $classification->id);
            if ($classCats->isEmpty()) continue;
          @endphp

          <div class="accordion-section" data-classification-id="{{ $classification->id }}" data-classification-name="{{ $classification->name_ar }}">
            <!-- Accordion Header Pill -->
            <button
              type="button"
              class="accordion-header is-open"
              data-target="acc-cg-{{ $classification->id }}"
              aria-expanded="true"
            >
              <div class="accordion-header-title-wrap">
                @if($classification->icon)
                  <span class="accordion-icon">{{ $classification->icon }}</span>
                @endif
                <span class="accordion-title">{{ $classification->name_ar }}</span>
                <span class="accordion-count">({{ $classCats->count() }})</span>
              </div>
              <span class="accordion-toggle-circle" aria-hidden="true">−</span>
            </button>

            <!-- Accordion Body -->
            <div class="accordion-body is-open" id="acc-cg-{{ $classification->id }}">
              <div class="custom-cards-grid">
                @foreach($classCats as $category)
                  @php
                    $isSelected = in_array((int) $category->id, array_map('intval', old('category_ids', [])), true);
                    $filterKey  = 'c'.$classification->id;
                  @endphp
                  <div
                    class="card-item-box {{ $isSelected ? 'is-selected' : '' }}"
                    data-id="{{ $category->id }}"
                    data-category-id="{{ $category->id }}"
                    data-filter="{{ $filterKey }}"
                    data-group="{{ $filterKey }}"
                    data-name="{{ $category->name_ar }}"
                     data-questions="{{ $category->remaining_questions ?? $category->questions_count }}"
                  >
                    <!-- Green Checkmark Badge ✔ for Selected Category Cards -->
                    <div class="selected-check-badge">✔</div>

                    <!-- Favorite Direct Heart Button -->
                    <button type="button" class="card-item-fav-direct" data-fav-card-btn data-category-id="{{ $category->id }}" data-category-name="{{ $category->name_ar }}" title="إضافة للمفضلة">
                      <span class="fav-heart-icon">🤍</span>
                    </button>

                    <!-- Image Section -->
                    <div class="card-item-image-wrap">
                      <span class="card-item-badge card-item-badge--remaining">
                        {{ $category->remaining_badge ?? ($category->questions_count ? $category->questions_count.' سؤال' : 'قريبًا') }}
                      </span>
                      @if($category->imageUrl())
                        <img src="{{ $category->imageUrl() }}" alt="{{ $category->name_ar }}" loading="lazy" decoding="async">
                      @else
                        <div class="card-item-fallback-icon">{{ $category->icon ?: '🎯' }}</div>
                      @endif
                    </div>

                    <!-- Bottom Solid Orange Name Bar -->
                    <div class="card-item-footer-bar">
                      <span class="card-item-name">{{ $category->name_ar }}</span>
                    </div>
                  </div>
                @endforeach
              </div>
            </div>
          </div>
        @endforeach

        {{-- فئات بدون تصنيف --}}
        @php
          $validClassIds = $classifications->pluck('id')->all();
          $uncategorized = $categories->filter(function ($cat) use ($validClassIds) {
              return empty($cat->classification_id) || !in_array($cat->classification_id, $validClassIds);
          });
        @endphp
        @if($uncategorized->isNotEmpty())
          <div class="accordion-section" data-classification-id="0" data-classification-name="فئات متنوعة">
            <button
              type="button"
              class="accordion-header is-open"
              data-target="acc-cg-0"
              aria-expanded="true"
            >
              <div class="accordion-header-title-wrap">
                <span class="accordion-icon">🎯</span>
                <span class="accordion-title">فئات متنوعة</span>
                <span class="accordion-count">({{ $uncategorized->count() }})</span>
              </div>
              <span class="accordion-toggle-circle" aria-hidden="true">−</span>
            </button>

            <div class="accordion-body is-open" id="acc-cg-0">
              <div class="custom-cards-grid">
                @foreach($uncategorized as $category)
                  @php
                    $isSelected = in_array((int) $category->id, array_map('intval', old('category_ids', [])), true);
                  @endphp
                  <div
                    class="card-item-box {{ $isSelected ? 'is-selected' : '' }}"
                    data-id="{{ $category->id }}"
                    data-category-id="{{ $category->id }}"
                    data-filter="general"
                    data-group="general"
                    data-name="{{ $category->name_ar }}"
                     data-questions="{{ $category->remaining_questions ?? $category->questions_count }}"
                  >
                    <div class="selected-check-badge">✔</div>

                    <button type="button" class="card-item-fav-direct" data-fav-card-btn data-category-id="{{ $category->id }}" data-category-name="{{ $category->name_ar }}" title="إضافة للمفضلة">
                      <span class="fav-heart-icon">🤍</span>
                    </button>

                    <button type="button" class="card-item-info" data-info-toggle title="معلومات الفئة">i</button>

                    <div class="card-info-popover" data-info-popover hidden>
                      <div class="popover-arrow"></div>
                      <p class="popover-desc">
                        {{ $category->description ?: 'اختبر معلوماتك في فئة '.$category->name_ar.' واسمح لإجاباتك بالتألق مع أصحابك!' }}
                      </p>
                    </div>

                    <div class="card-item-image-wrap">
                      <span class="card-item-badge card-item-badge--remaining">
                        {{ $category->remaining_badge ?? ($category->questions_count ? $category->questions_count.' سؤال' : 'قريبًا') }}
                      </span>
                      @if($category->imageUrl())
                        <img src="{{ $category->imageUrl() }}" alt="{{ $category->name_ar }}" loading="lazy" decoding="async">
                      @else
                        <div class="card-item-fallback-icon">{{ $category->icon ?: '🎯' }}</div>
                      @endif
                    </div>

                    <div class="card-item-footer-bar">
                      <span class="card-item-name">{{ $category->name_ar }}</span>
                    </div>
                  </div>
                @endforeach
              </div>
            </div>
          </div>
        @endif

      </div>

      <!-- Teams Section ("حدد معلومات الفرق") -->
      <section class="teams-info-card">
        <h3 class="teams-info-title">حدد معلومات الفرق</h3>

        <div class="game-title-field">
          <input
            type="text"
            name="title"
            class="pill-input"
            placeholder="تحدي سوالف الخاص"
            value="{{ old('title') }}"
          >
        </div>

        <div class="teams-two-col">
          <!-- Team 1 -->
          <div>
            <div class="team-block-title">الفريق 1</div>
            <input
              type="text"
              name="team_names[0]"
              class="pill-input"
              placeholder="الفريق الأول"
              value="{{ old('team_names.0') }}"
            >
          </div>

          <!-- Team 2 -->
          <div>
            <div class="team-block-title">الفريق 2</div>
            <input
              type="text"
              name="team_names[1]"
              class="pill-input"
              placeholder="الفريق الثاني"
              value="{{ old('team_names.1') }}"
            >
          </div>
        </div>

        <div style="margin-bottom:28px">
          <div class="helpers-sub-title">وسائل المساعدة المتاحة لكل فريق:</div>
          <div class="helpers-pills-row">
            <span class="helper-mini-badge">🔄 تبديل السؤال (مرة واحدة)</span>
            <span class="helper-mini-badge">📞 اتصل بصديق (مرة واحدة)</span>
            <span class="helper-mini-badge">✌️ خيارين (مرة واحدة)</span>
          </div>
        </div>

        <button
          type="submit"
          class="start-game-btn"
          id="submitBtn"
          disabled
        >
          🚀 ابدأ اللعب الآن
        </button>

        <div class="validation-hint" id="submitHint">
          اختر 4 فئات على الأقل لتفعيل زر بدء اللعب
        </div>
      </section>
    </form>
  </main>

  <!-- Mobile Floating Sticky Bottom Action Bar -->
  <div class="mobile-sticky-action-bar" id="mobileStickyBar">
    <div class="sticky-bar-info">
      <span>🎯 اختياراتك:</span>
      <strong id="stickyCount">0 / 6</strong>
    </div>
    <button type="submit" form="customGameForm" class="sticky-bar-btn" id="stickySubmitBtn" disabled>
      🚀 أنشئ اللعبة الآن
    </button>
  </div>
</div>

<!-- Modal: Random Category Spinner (فكر وابدأ) -->
<div class="random-picker-modal" id="randomPickerModal" hidden>
  <div class="random-picker-backdrop" id="randomPickerBackdrop"></div>
  <div class="random-picker-dialog">
    <button type="button" class="random-picker-close" id="closeRandomModal">&times;</button>
    <div class="random-picker-body">
      <div class="random-spinner-wrap" id="randomSpinnerWrap">
        <div class="spinner-dice-anim">🎲</div>
        <h3 class="spinner-title">جاري تدوير الاختيارات العشوائية…</h3>
        <p class="spinner-sub">استعد لبدء التحدي الحماسي!</p>
      </div>
      <div class="random-result-wrap" id="randomResultWrap" style="display:none">
        <div class="result-badge-icon" id="randomResultIcon">🎲</div>
        <h2 class="result-title" id="randomResultTitle">اختر طريقة التحديد العشوائي</h2>
        <p class="result-desc" id="randomResultDesc">يمكنك اختيار 6 فئات كاملة تلقائياً أو اختيار فئة واحدة في كل مرة!</p>
        <div class="result-actions">
          <button type="button" class="btn btn--fire btn--lg" id="pickSixRandomBtn">🔥 اختار 6 فئات عشوائية فوراً (فكروابدأ)</button>
          <button type="button" class="btn btn--outline" id="pickOneRandomBtn">🎯 اختار فئة عشوائية واحدة</button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var MIN = 4;
  var MAX = 6;
  var selected = new Set();

  var selCount     = document.getElementById('selCount');
  var stickyCount  = document.getElementById('stickyCount');
  var badge        = document.getElementById('selectionBadge');
  var submitBtn    = document.getElementById('submitBtn');
  var stickySubmit = document.getElementById('stickySubmitBtn');
  var submitHint   = document.getElementById('submitHint');
  var hiddenInputs = document.getElementById('hiddenInputs');
  var stickyBar    = document.getElementById('mobileStickyBar');

  /* ── Audio Helper (Intact & Preserved) ─────────────────── */
  function playSound(type) {
    try {
      var ctx = new (window.AudioContext || window.webkitAudioContext)();
      var osc = ctx.createOscillator();
      var gain = ctx.createGain();
      osc.connect(gain);
      gain.connect(ctx.destination);
      if (type === 'fav') {
        osc.frequency.setValueAtTime(523.25, ctx.currentTime);
        osc.frequency.exponentialRampToValueAtTime(659.25, ctx.currentTime + 0.15);
        gain.gain.setValueAtTime(0.2, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.25);
        osc.start(); osc.stop(ctx.currentTime + 0.25);
      } else if (type === 'select') {
        osc.frequency.setValueAtTime(440, ctx.currentTime);
        osc.frequency.exponentialRampToValueAtTime(880, ctx.currentTime + 0.15);
        gain.gain.setValueAtTime(0.2, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.15);
        osc.start(); osc.stop(ctx.currentTime + 0.15);
      } else if (type === 'deselect') {
        osc.frequency.setValueAtTime(660, ctx.currentTime);
        osc.frequency.exponentialRampToValueAtTime(330, ctx.currentTime + 0.15);
        gain.gain.setValueAtTime(0.18, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.15);
        osc.start(); osc.stop(ctx.currentTime + 0.15);
      } else if (type === 'spin') {
        osc.frequency.setValueAtTime(300, ctx.currentTime);
        osc.frequency.exponentialRampToValueAtTime(800, ctx.currentTime + 0.35);
        gain.gain.setValueAtTime(0.25, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.35);
        osc.start(); osc.stop(ctx.currentTime + 0.35);
      } else if (type === 'fanfare') {
        var notes = [523.25, 659.25, 783.99, 1046.50];
        notes.forEach(function (freq, i) {
          var o = ctx.createOscillator();
          var g = ctx.createGain();
          o.connect(g); g.connect(ctx.destination);
          o.frequency.setValueAtTime(freq, ctx.currentTime + i * 0.08);
          g.gain.setValueAtTime(0.2, ctx.currentTime + i * 0.08);
          g.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + i * 0.08 + 0.2);
          o.start(ctx.currentTime + i * 0.08);
          o.stop(ctx.currentTime + i * 0.08 + 0.2);
        });
      }
    } catch(e) {}
  }

  /* ── Favorites Manager (LocalStorage) ───────────────────────── */
  function getFavorites() {
    try {
      return JSON.parse(localStorage.getItem('swalif_favorites') || '[]');
    } catch(e) { return []; }
  }

  function saveFavorites(favs) {
    try {
      localStorage.setItem('swalif_favorites', JSON.stringify(favs));
    } catch(e) {}
    updateFavUI();
  }

  function toggleFavorite(catId, catName) {
    catId = String(catId);
    var favs = getFavorites();
    var idx = favs.indexOf(catId);
    if (idx >= 0) {
      favs.splice(idx, 1);
      showToast('تمت إزالة ' + catName + ' من المفضلة', 'info');
    } else {
      favs.push(catId);
      playSound('fav');
      showToast('تمت إضافة ' + catName + ' إلى المفضلة ❤️', 'success');
    }
    saveFavorites(favs);
  }

  function updateFavUI() {
    var favs = getFavorites();
    var topCountEl = document.getElementById('topFavCount');
    var favCountEl = document.getElementById('favCount');
    if (topCountEl) topCountEl.textContent = '(' + favs.length + ')';
    if (favCountEl) favCountEl.textContent = favs.length;

    document.querySelectorAll('[data-fav-card-btn]').forEach(function (btn) {
      var catId = String(btn.dataset.categoryId || '');
      var isFav = favs.includes(catId);
      btn.classList.toggle('is-fav', isFav);
      btn.querySelector('.fav-heart-icon').textContent = isFav ? '❤️' : '🤍';
    });
  }

  updateFavUI();

  // Heart button direct
  document.querySelectorAll('[data-fav-card-btn]').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      e.stopPropagation();
      e.preventDefault();
      var catId = btn.dataset.categoryId;
      var catName = btn.dataset.categoryName || 'الفئة';
      if (catId) toggleFavorite(catId, catName);
    });
  });

  /* ── Accordion toggle ───────────────────────────── */
  document.querySelectorAll('.accordion-header').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var targetId = btn.dataset.target;
      var body     = document.getElementById(targetId);
      var toggle   = btn.querySelector('.accordion-toggle-circle');
      var isOpen   = btn.classList.contains('is-open');

      btn.classList.toggle('is-open', !isOpen);
      btn.setAttribute('aria-expanded', String(!isOpen));
      body.classList.toggle('is-open', !isOpen);
      if (toggle) toggle.textContent = isOpen ? '+' : '−';
    });
  });

  /* ── Filter Functionality ────────────────────────── */
  function applyFilter(filterVal) {
    document.querySelectorAll('.pill-btn').forEach(function (p) {
      p.classList.toggle('active', p.dataset.filter === filterVal);
    });

    document.querySelectorAll('[data-top-filter]').forEach(function (p) {
      p.classList.toggle('is-active-pill', p.dataset.topFilter === filterVal);
    });

    if (filterVal === 'all') {
      document.querySelectorAll('.card-item-box').forEach(function (c) { c.style.display = ''; });
      document.querySelectorAll('.accordion-section').forEach(function (sec) { sec.style.display = ''; });
      return;
    }

    if (filterVal === 'favorites' || filterVal === 'المفضلة') {
      var favs = getFavorites();
      if (favs.length === 0) {
        showToast('لم تُضف أي فئة للمفضلة بعد. اضغط ❤️ على أي فئة لإضافتها!', 'info');
      }
      document.querySelectorAll('.accordion-section').forEach(function (sec) {
        var hasVisible = false;
        sec.querySelectorAll('.card-item-box').forEach(function (card) {
          var cid = String(card.dataset.categoryId || '');
          var matches = favs.includes(cid);
          card.style.display = matches ? '' : 'none';
          if (matches) hasVisible = true;
        });
        sec.style.display = hasVisible ? '' : 'none';
      });
      return;
    }

    if (filterVal === 'صممت لك') {
      document.querySelectorAll('.accordion-section').forEach(function (sec) {
        var hasVisible = false;
        sec.querySelectorAll('.card-item-box').forEach(function (card) {
          var qCount = parseInt(card.dataset.questions || '0', 10);
          var matches = qCount >= 10 || card.dataset.name.includes('أشعار') || card.dataset.name.includes('أمثال');
          card.style.display = matches ? '' : 'none';
          if (matches) hasVisible = true;
        });
        sec.style.display = hasVisible ? '' : 'none';
      });
      return;
    }

    if (filterVal === 'إمارات') {
      document.querySelectorAll('.accordion-section').forEach(function (sec) {
        var secName = (sec.dataset.classificationName || '').toLowerCase();
        var hasVisible = false;
        sec.querySelectorAll('.card-item-box').forEach(function (card) {
          var name = (card.dataset.name || '').toLowerCase();
          var matches = name.includes('إمارات') || name.includes('امارات') || name.includes('دبي') || name.includes('أبوظبي') || name.includes('تاريخ') || name.includes('ثقافة') || secName.includes('إمارات');
          card.style.display = matches ? '' : 'none';
          if (matches) hasVisible = true;
        });
        sec.style.display = hasVisible ? '' : 'none';
      });
      return;
    }

    document.querySelectorAll('.accordion-section').forEach(function (sec) {
      var secName = (sec.dataset.classificationName || '').toLowerCase();
      var hasVisible = false;

      sec.querySelectorAll('.card-item-box').forEach(function (card) {
        var cardFilter = card.dataset.filter || '';
        var cardGroup  = card.dataset.group || '';
        var cardName   = (card.dataset.name || '').toLowerCase();

        var matches = (cardFilter === filterVal || cardGroup === filterVal || cardName.includes(filterVal.toLowerCase()) || secName.includes(filterVal.toLowerCase()));
        card.style.display = matches ? '' : 'none';
        if (matches) hasVisible = true;
      });

      sec.style.display = hasVisible ? '' : 'none';
    });
  }

  document.querySelectorAll('[data-top-filter]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var filterVal = btn.dataset.topFilter;
      if (filterVal === 'فكر') {
        openRandomPickerModal();
        return;
      }
      if (filterVal === 'custom') {
        var heading = document.querySelector('.selection-heading-wrap');
        if (heading) {
          heading.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
        applyFilter('all');
        btn.classList.add('is-active-pill');
        return;
      }
      applyFilter(filterVal);
    });
  });

  document.querySelectorAll('.pill-btn').forEach(function (pill) {
    pill.addEventListener('click', function () {
      applyFilter(pill.dataset.filter);
    });
  });

  /* ── Card Info Popover Toggle ───────────────── */
  document.querySelectorAll('[data-info-toggle]').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      e.stopPropagation();
      e.preventDefault();
      var card = btn.closest('.card-item-box');
      var popover = card.querySelector('[data-info-popover]');
      var isHidden = popover.hidden;
      document.querySelectorAll('[data-info-popover]').forEach(function (p) { p.hidden = true; });
      if (isHidden) popover.hidden = false;
    });
  });

  document.addEventListener('click', function (e) {
    if (!e.target.closest('[data-info-popover]') && !e.target.closest('[data-info-toggle]')) {
      document.querySelectorAll('[data-info-popover]').forEach(function (p) { p.hidden = true; });
    }
  });

  // Restore old selections
  document.querySelectorAll('.card-item-box.is-selected').forEach(function (card) {
    selected.add(parseInt(card.dataset.id));
  });

  function updateUI() {
    var count = selected.size;
    selCount.textContent = count;
    if (stickyCount) stickyCount.textContent = count + ' / 6';

    badge.classList.toggle('is-ready', count >= MIN && count <= MAX);

    document.querySelectorAll('.card-item-box').forEach(function (card) {
      var id = parseInt(card.dataset.id);
      if (selected.has(id)) {
        card.classList.add('is-selected');
      } else {
        card.classList.remove('is-selected');
      }

      if (count >= MAX && !selected.has(id)) {
        card.classList.add('is-disabled');
      } else {
        card.classList.remove('is-disabled');
      }
    });

    submitBtn.disabled = count < MIN || count > MAX;
    if (stickySubmit) stickySubmit.disabled = count < MIN || count > MAX;

    if (stickyBar) {
      stickyBar.classList.toggle('is-visible', count > 0);
    }

    if (count < MIN) {
      submitHint.textContent = 'اختر ' + (MIN - count) + ' فئة إضافية على الأقل لتفعيل الزر';
      submitHint.className = 'validation-hint';
    } else {
      submitHint.textContent = '✓ جاهز لبدء اللعب!';
      submitHint.className = 'validation-hint is-ready';
    }

    hiddenInputs.innerHTML = '';
    selected.forEach(function (id) {
      var inp = document.createElement('input');
      inp.type = 'hidden';
      inp.name = 'category_ids[]';
      inp.value = id;
      hiddenInputs.appendChild(inp);
    });
  }

  function toggleCard(card) {
    var id = parseInt(card.dataset.id);
    if (selected.has(id)) {
      selected.delete(id);
      card.classList.remove('is-selected');
      playSound('deselect');
    } else {
      if (selected.size >= MAX) {
        showToast('اللعبة الخاصة لازم تكون من 4 إلى 6 فئات فقط 🎯', 'error');
        return;
      }
      selected.add(id);
      card.classList.add('is-selected');
      playSound('select');
    }
    updateUI();
  }

  document.querySelectorAll('.card-item-box').forEach(function (card) {
    card.addEventListener('click', function (e) {
      if (e.target.closest('[data-info-toggle]') || e.target.closest('[data-info-popover]') || e.target.closest('[data-fav-card-btn]')) {
        return;
      }
      if (card.classList.contains('is-disabled')) return;
      toggleCard(card);
    });
  });

  var searchInput = document.getElementById('categorySearch');
  if (searchInput) {
    searchInput.addEventListener('input', function () {
      var q = searchInput.value.trim().toLowerCase();
      if (!q) {
        applyFilter('all');
        return;
      }
      document.querySelectorAll('.accordion-section').forEach(function (sec) {
        var hasVis = false;
        sec.querySelectorAll('.card-item-box').forEach(function (card) {
          var name = (card.dataset.name || '').toLowerCase();
          var show = name.includes(q);
          card.style.display = show ? '' : 'none';
          if (show) hasVis = true;
        });
        sec.style.display = hasVis ? '' : 'none';
      });
    });
  }

  document.getElementById('customGameForm').addEventListener('submit', function (e) {
    if (selected.size < MIN) {
      e.preventDefault();
      showToast('اختار 4 فئات على الأقل عشان تقدر تنشئ لعبتك 🎮', 'error');
    }
  });

  /* ── Random Picker Modal (فكر وابدأ — تختار 6 فئات) ──────────────── */
  var randomModal = document.getElementById('randomPickerModal');
  var closeRandomBtn = document.getElementById('closeRandomModal');
  var backdrop = document.getElementById('randomPickerBackdrop');
  var spinnerWrap = document.getElementById('randomSpinnerWrap');
  var resultWrap = document.getElementById('randomResultWrap');
  var pickSixBtn = document.getElementById('pickSixRandomBtn');
  var pickOneBtn = document.getElementById('pickOneRandomBtn');

  function openRandomPickerModal() {
    randomModal.hidden = false;
    spinnerWrap.style.display = 'block';
    resultWrap.style.display = 'none';

    playSound('spin');

    setTimeout(function () {
      spinnerWrap.style.display = 'none';
      resultWrap.style.display = 'block';
    }, 1000);
  }

  // 🎲 اختار 6 فئات عشوائية فوراً
  if (pickSixBtn) {
    pickSixBtn.addEventListener('click', function () {
      var allCards = Array.from(document.querySelectorAll('.card-item-box'));
      if (allCards.length < 6) {
        showToast('لا تتوفر 6 فئات متكاملة في الموقع!', 'error');
        randomModal.hidden = true;
        return;
      }

      // Shuffle and pick 6
      selected.clear();
      var shuffled = allCards.sort(function() { return 0.5 - Math.random(); });
      var pickedSix = shuffled.slice(0, 6);

      pickedSix.forEach(function(card) {
        var id = parseInt(card.dataset.id);
        selected.add(id);
      });

      updateUI();
      playSound('fanfare');
      randomModal.hidden = true;
      showToast('تم اختيار 6 فئات عشوائية بنجاح! جاهز لبدء اللعب 🚀', 'success');
    });
  }

  // 🎯 اختار فئة واحدة عشوائية
  if (pickOneBtn) {
    pickOneBtn.addEventListener('click', function () {
      var unselectedCards = Array.from(document.querySelectorAll('.card-item-box')).filter(function(card) {
        var id = parseInt(card.dataset.id);
        return !selected.has(id);
      });

      if (unselectedCards.length === 0 || selected.size >= MAX) {
        showToast('وصلت للحد الأقصى من الفئات (6 فئات)!', 'info');
        randomModal.hidden = true;
        return;
      }

      var pickedOne = unselectedCards[Math.floor(Math.random() * unselectedCards.length)];
      var id = parseInt(pickedOne.dataset.id);
      selected.add(id);
      updateUI();
      playSound('select');
      pickedOne.scrollIntoView({ behavior: 'smooth', block: 'center' });
      randomModal.hidden = true;
      showToast('تمت إضافة فئة "' + pickedOne.dataset.name + '" بنجاح! 🎯', 'success');
    });
  }

  if (closeRandomBtn) closeRandomBtn.addEventListener('click', function() { randomModal.hidden = true; });
  if (backdrop) backdrop.addEventListener('click', function() { randomModal.hidden = true; });

  function showToast(msg, type) {
    var stack = document.getElementById('toastStack');
    if (!stack) {
      stack = document.createElement('div');
      stack.id = 'toastStack';
      stack.className = 'toast-stack';
      document.body.appendChild(stack);
    }
    var toast = document.createElement('div');
    toast.className = 'toast toast--' + (type || 'error');
    toast.innerHTML = '<span class="toast__icon">' + (type === 'success' ? '✅' : '⚠️') + '</span><span class="toast__msg">' + msg + '</span><button type="button" class="toast__close">&times;</button>';
    stack.appendChild(toast);
    setTimeout(function () { toast.classList.add('is-visible'); }, 60);
    setTimeout(function () { toast.remove(); }, 4200);
  }

  updateUI();
});
</script>

</div>
</x-layouts.app>
