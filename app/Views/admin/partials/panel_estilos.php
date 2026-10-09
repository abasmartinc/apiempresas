<?php
/**
 * Estilos de los paneles del admin con tarjetas, graficos SVG y ayudas "?" (gr-*): /admin/crecimiento y /admin/email-logs.
 * Los scripts de tooltips y ayudas estan en admin/partials/panel_js.
 */
?>
<style>
    .gr-root {
        --surface: #ffffff;
        --ink-1: #0b0b0b;
        --ink-2: #52514e;
        --ink-3: #8a8984;
        --rule: #ecebe7;
        --line: #e2e8f0;
        --api: #2a78d6;
        --api-tint: #b7d3f6;
        --risk: #eb6834;
        --risk-tint: #f8c9b4;
        --good: #0ca30c;
        --bad: #d03b3b;
        color: var(--ink-1);
    }
    .gr-head { display: flex; justify-content: space-between; align-items: flex-end; gap: 12px; flex-wrap: wrap; margin-bottom: 1.5rem; }
    .gr-head .subtitle { margin: 0; color: var(--ink-2); }
    .gr-box { background: var(--surface); border: 1px solid var(--line); border-radius: 16px; }
    .gr-tiles { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 14px; margin-bottom: 14px; }
    .gr-tile {
        position: relative; overflow: hidden; padding: 18px 18px 16px; display: flex; flex-direction: column;
        border: 1px solid #e6e9f0; border-radius: 18px;
        background: radial-gradient(120% 90% at 100% 0%, var(--acc-soft) 0%, rgba(255,255,255,0) 55%), #fff;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .04), 0 8px 24px -12px rgba(15, 23, 42, .12);
        transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
    }
    .gr-tile::before { content: ''; position: absolute; inset: 0 0 auto 0; height: 3px; background: linear-gradient(90deg, var(--acc), var(--acc-2)); }
    .gr-tile:hover { transform: translateY(-3px); border-color: var(--acc-line); box-shadow: 0 2px 4px rgba(15, 23, 42, .04), 0 18px 36px -16px var(--acc-shadow); }
    .gr-tile__head { display: flex; align-items: center; gap: 10px; }
    .gr-tile__icon { flex: none; width: 34px; height: 34px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; color: #fff;
        background: linear-gradient(135deg, var(--acc-2), var(--acc)); box-shadow: 0 6px 14px -6px var(--acc-shadow); }
    .gr-tile__icon svg { width: 18px; height: 18px; }
    .gr-tile__label { font-size: .74rem; font-weight: 800; color: var(--ink-2); text-transform: uppercase; letter-spacing: .05em; line-height: 1.3; }
    .gr-tile__value { font-size: 2.25rem; font-weight: 900; letter-spacing: -.03em; margin: 12px 0 6px; font-variant-numeric: tabular-nums; line-height: 1; color: #0f172a; display: flex; align-items: baseline; gap: 6px; flex-wrap: wrap; }
    .gr-tile__value .approx { color: var(--acc); font-weight: 800; font-size: 1.6rem; }
    .gr-tile__value small { font-size: .9rem; font-weight: 700; color: var(--ink-2); letter-spacing: 0; }
    .gr-tile__meta { font-size: .78rem; color: var(--ink-2); line-height: 1.5; display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
    .gr-tile__viz { margin-top: auto; padding-top: 14px; }
    .gr-tile__foot { display: flex; justify-content: space-between; gap: 8px; font-size: .74rem; color: var(--ink-2); margin-top: 6px; }
    .gr-tile__foot strong { color: #0f172a; font-variant-numeric: tabular-nums; }
    .gr-split { display: flex; height: 8px; border-radius: 99px; overflow: hidden; background: #eef1f6; gap: 2px; }
    .gr-split span { display: block; height: 100%; }
    .gr-prog { position: relative; height: 8px; border-radius: 99px; background: #eef1f6; }
    .gr-prog > span { position: absolute; inset: 0 auto 0 0; border-radius: 99px; background: linear-gradient(90deg, var(--acc-2), var(--acc)); }
    .gr-prog > i { position: absolute; top: -4px; bottom: -4px; width: 2px; border-radius: 2px; background: #0f172a; opacity: .55; }
    .gr-spark { display: block; width: 100%; height: 40px; overflow: visible; }
    .gr-ring { display: flex; align-items: center; gap: 12px; }
    .gr-ring svg { flex: none; }
    .gr-minibars { flex: 1; display: grid; gap: 7px; }
    .gr-minibars div { font-size: .7rem; white-space: nowrap; color: var(--ink-2); }
    .gr-minibars div > span { display: flex; justify-content: space-between; gap: 8px; margin-bottom: 3px; }
    .gr-minibars div > span strong { color: #0f172a; }
    .gr-minibars i { display: block; height: 5px; border-radius: 99px; background: #eef1f6; overflow: hidden; }
    .gr-minibars i b { display: block; height: 100%; border-radius: 99px; }
    .gr-chip { display: inline-flex; align-items: center; gap: 3px; font-size: .75rem; font-weight: 800; padding: 1px 7px; border-radius: 99px; white-space: nowrap; }
    .gr-chip--up { color: #0a7a0a; background: #e7f6e7; }
    .gr-chip--down { color: #b42f2f; background: #fbeaea; }
    .gr-chip--flat { color: var(--ink-2); background: #f1f5f9; }
    .gr-dot { display: inline-block; width: 9px; height: 9px; border-radius: 3px; margin-right: 4px; vertical-align: 0; }
    .gr-read { padding: 16px 20px; margin-bottom: 14px; }
    .gr-read h3, .gr-card h3 { margin: 0 0 10px; font-size: .98rem; font-weight: 800; }
    .gr-read ul { margin: 0; padding: 0; list-style: none; display: grid; gap: 8px; }
    .gr-read li { display: flex; gap: 10px; font-size: .9rem; line-height: 1.5; color: var(--ink-1); }
    .gr-read li span:first-child { flex: none; width: 20px; height: 20px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: .72rem; font-weight: 800; margin-top: 1px; }
    .gr-read .t-bien span:first-child { background: #e7f6e7; color: #0a7a0a; }
    .gr-read .t-mal span:first-child { background: #fbeaea; color: #b42f2f; }
    .gr-read .t-neutro span:first-child { background: #f1f5f9; color: var(--ink-2); }
    .gr-card { padding: 18px 20px; margin-bottom: 14px; }
    .gr-card__top { display: flex; justify-content: space-between; align-items: baseline; gap: 10px; flex-wrap: wrap; margin-bottom: 6px; }
    .gr-card__top h3 { margin: 0; }
    .gr-legend { display: flex; gap: 14px; flex-wrap: wrap; font-size: .8rem; color: var(--ink-2); }
    .gr-legend span { display: inline-flex; align-items: center; }
    .gr-note { font-size: .75rem; color: var(--ink-3); margin: 6px 0 0; line-height: 1.45; }
    .gr-two { display: grid; grid-template-columns: minmax(0, 1.7fr) minmax(260px, 1fr); gap: 14px; }
    @media (max-width: 960px) { .gr-two { grid-template-columns: 1fr; } }
    .gr-svg { width: 100%; height: auto; display: block; overflow: visible; }
    .gr-svg text { font-family: inherit; }
    .gr-hit { fill: transparent; cursor: default; }
    .gr-hit:hover { fill: rgba(15, 23, 42, .04); }
    .gr-tip { position: fixed; pointer-events: none; z-index: 1000; display: none; background: #0f172a; color: #fff; padding: 8px 11px; border-radius: 8px; font-size: .78rem; line-height: 1.5; white-space: pre-line; max-width: 280px; box-shadow: 0 6px 20px rgba(0,0,0,.18); }
    .gr-plans { list-style: none; margin: 0; padding: 0; display: grid; gap: 10px; }
    .gr-plans li { font-size: .86rem; }
    .gr-plans .row { display: flex; justify-content: space-between; gap: 8px; margin-bottom: 4px; }
    .gr-plans .row strong { font-variant-numeric: tabular-nums; }
    .gr-plans .bar { height: 6px; border-radius: 99px; background: #f1f5f9; overflow: hidden; }
    .gr-plans .bar span { display: block; height: 100%; border-radius: 99px; }
    .gr-kv { display: grid; grid-template-columns: 1fr auto; gap: 6px 12px; font-size: .86rem; margin-top: 16px; padding-top: 14px; border-top: 1px solid var(--rule); }
    .gr-kv span { color: var(--ink-2); }
    .gr-kv strong { text-align: right; font-variant-numeric: tabular-nums; }
    .gr-table-wrap { overflow-x: auto; }
    .gr-table { width: 100%; border-collapse: collapse; min-width: 920px; font-variant-numeric: tabular-nums; }
    .gr-table th { padding: 8px 10px; font-size: .74rem; color: var(--ink-2); text-align: right; border-bottom: 2px solid var(--rule); font-weight: 700; white-space: nowrap; }
    .gr-table th:first-child, .gr-table td:first-child { text-align: left; }
    .gr-table td { padding: 8px 10px; font-size: .85rem; text-align: right; border-bottom: 1px solid var(--rule); white-space: nowrap; }
    .gr-table tr.actual td { background: #f8fafc; }
    .gr-table .grp { border-left: 1px solid var(--rule); }
    .gr-muted { color: var(--ink-3); }
    .gr-help { display: inline-flex; align-items: center; justify-content: center; width: 16px; height: 16px; margin-left: 5px; padding: 0; border-radius: 50%;
        border: 1.5px solid #c3c8d2; background: #fff; color: #7b8494; font: 800 10px/1 inherit; cursor: help; vertical-align: 1px; flex: none;
        text-transform: none; letter-spacing: 0; transition: all .15s ease; }
    .gr-tile .gr-tile__label .gr-help { position: absolute; top: 14px; right: 14px; margin: 0; width: 18px; height: 18px; }
    .gr-tile__head { padding-right: 22px; }
    .gr-help:hover, .gr-help:focus-visible, .gr-help.on { border-color: #2152ff; color: #fff; background: #2152ff; outline: none; }
    .gr-pop { position: fixed; z-index: 1100; display: none; width: 340px; max-width: calc(100vw - 24px); background: #0f172a; color: #e2e8f0;
        padding: 14px 16px; border-radius: 12px; font-size: .82rem; line-height: 1.55; box-shadow: 0 16px 40px -12px rgba(15, 23, 42, .45);
        text-align: left; white-space: normal; font-weight: 400; text-transform: none; letter-spacing: 0; }
    .gr-pop strong { color: #fff; }
    .gr-pop code { background: rgba(255,255,255,.1); padding: 1px 5px; border-radius: 4px; font-size: .78rem; color: #c7d2fe; }
    .gr-pop .ej { display: block; margin-top: 8px; padding: 8px 10px; border-radius: 8px; background: rgba(255,255,255,.06); color: #cbd5e1; }
    .gr-alert { padding: 14px 16px; border-radius: 12px; background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
</style>
