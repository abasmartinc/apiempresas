<style>
    .et-head-row { display: flex; justify-content: space-between; align-items: flex-end; gap: 12px; flex-wrap: wrap; margin-bottom: 1.5rem; }
    .et-head-row .subtitle { margin: 0; color: #64748b; }
    .et-box { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; }
    .et-kpis { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; margin-bottom: 1.25rem; }
    .et-kpi { padding: 18px; text-decoration: none; color: inherit; display: block; transition: border-color .15s, transform .15s; }
    a.et-kpi:hover { border-color: #93c5fd; transform: translateY(-2px); }
    .et-kpi__label { font-size: .75rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: .05em; }
    .et-kpi__value { font-size: 1.8rem; font-weight: 900; color: #0f172a; margin-top: 4px; font-variant-numeric: tabular-nums; }
    .et-filters { padding: 16px 18px; margin-bottom: 14px; display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap; }
    .et-field { display: flex; flex-direction: column; gap: 4px; }
    .et-field.grow { flex: 1; min-width: 200px; }
    .et-field > label { font-size: .72rem; text-transform: uppercase; font-weight: 700; color: #64748b; }
    .et-input { border: 1px solid #cbd5e1; border-radius: 8px; padding: 7px 10px; font-size: .85rem; height: 36px; background: #fff; color: #0f172a; font-family: inherit; }
    textarea.et-input { height: auto; }
    .et-checks { display: flex; gap: 10px; flex-wrap: wrap; height: 36px; align-items: center; }
    .et-checks label { font-size: .85rem; display: flex; align-items: center; gap: 4px; cursor: pointer; }
    .et-btn { display: inline-flex; align-items: center; gap: 6px; border: 1px solid #cbd5e1; background: #fff; color: #0f172a; border-radius: 8px; padding: 7px 14px; font-size: .85rem; font-weight: 700; cursor: pointer; text-decoration: none; height: 36px; font-family: inherit; }
    .et-btn:hover { border-color: #2152ff; color: #2152ff; }
    .et-btn--primary { background: #2152ff; border-color: #2152ff; color: #fff; }
    .et-btn--primary:hover { background: #1a43d6; color: #fff; }
    .et-btn--sm { height: 30px; padding: 4px 10px; font-size: .8rem; }
    .et-bulk { display: none; align-items: center; gap: 12px; padding: 10px 16px; margin-bottom: 12px; background: #0f172a; color: #fff; border-radius: 12px; flex-wrap: wrap; }
    .et-bulk.show { display: flex; }
    .et-table-wrap { overflow-x: auto; }
    .et-table { width: 100%; border-collapse: collapse; min-width: 860px; }
    .et-table th { padding: 10px 12px; color: #64748b; font-size: .78rem; text-align: left; border-bottom: 2px solid #f1f5f9; text-transform: uppercase; letter-spacing: .03em; }
    .et-table td { padding: 10px 12px; border-bottom: 1px solid #f1f5f9; font-size: .87rem; vertical-align: top; }
    .et-table tr:last-child td { border-bottom: none; }
    .et-issue { max-width: 620px; }
    .et-msg { font-weight: 700; color: #0f172a; text-decoration: none; word-break: break-word; }
    .et-msg:hover { text-decoration: underline; color: #2152ff; }
    .et-sub { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; margin-top: 4px; font-size: .78rem; color: #64748b; }
    .et-file, .et-class, .et-mono { font-family: ui-monospace, Consolas, monospace; font-size: .78rem; word-break: break-all; }
    .et-type { font-size: .66rem; font-weight: 800; text-transform: uppercase; padding: 1px 7px; border-radius: 10px; background: #f1f5f9; color: #475569; }
    .et-type-exception { background: #fee2e2; color: #991b1b; }
    .et-type-js { background: #fef9c3; color: #854d0e; }
    .et-status { display: inline-flex; align-items: center; gap: 5px; font-size: .68rem; font-weight: 800; padding: 3px 9px; border-radius: 20px; white-space: nowrap; }
    .et-date { white-space: nowrap; font-size: .82rem; color: #64748b; }
    .et-num { font-variant-numeric: tabular-nums; text-align: right; }
    .et-empty { text-align: center; color: #64748b; padding: 32px !important; }
    .et-pager { display: flex; gap: 12px; align-items: center; justify-content: center; margin-top: 16px; font-size: .85rem; color: #64748b; }
    .et-alert { padding: 12px 16px; border-radius: 12px; margin-bottom: 14px; font-size: .88rem; }
    .et-alert--error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
</style>
