export function salesEditor(root) {
  const toNum = (v, def = 0) => {
    const n = parseFloat(v);
    return Number.isFinite(n) ? n : def;
  };
  const rows = () => (Array.isArray(root?.salesRows) ? root.salesRows : (Array.isArray(window.__initialSalesRows) ? window.__initialSalesRows : []));
  const setRows = (v) => { if (Array.isArray(root?.salesRows)) root.salesRows = v; };
  return {
    get salesRows(){ return rows(); },
    set salesRows(v){ setRows(v); },
    // actions
    addSalesRow() { this.salesRows.push({ account_id: '', division: '', previous: '', forecast: '', comments: '' }); },
    removeSalesRow(i) { this.salesRows.splice(i, 1); },
    // helpers
    formatMoney(n) { const v = parseFloat(n) || 0; return '$' + v.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }); },
    computeDiff(row) {
      const p = toNum(row.previous);
      const f = toNum(row.forecast);
      if (!isFinite(p) || p === 0) return '0%';
      const pct = (((isFinite(f) ? f : 0) - p) / Math.abs(p)) * 100;
      return pct.toFixed(1) + '%';
    },
    prevTotal(){ return this.salesRows.reduce((s, r) => s + (toNum(r.previous) || 0), 0); },
    forecastTotal(){ return this.salesRows.reduce((s, r) => s + (toNum(r.forecast) || 0), 0); },
    barWidth(val){ const max = Math.max(this.prevTotal(), this.forecastTotal(), 1); return Math.round((Math.max(0, val) / max) * 100) + '%'; },
    // pie segments by division
    divisionSegments(){
      const map = new Map();
      this.salesRows.forEach(r => {
        const key = (r.division || '').trim() || 'Unassigned';
        const v = toNum(r.forecast);
        map.set(key, (map.get(key) || 0) + v);
      });
      const total = Array.from(map.values()).reduce((a,b)=>a+b,0);
      const palette = ['#2563eb','#16a34a','#f59e0b','#dc2626','#7c3aed','#0ea5e9','#ea580c','#22c55e','#e11d48'];
      let i = 0;
      return Array.from(map.entries()).map(([label, value]) => ({
        label,
        value,
        percent: total > 0 ? (value / total) * 100 : 0,
        color: palette[i++ % palette.length],
      }));
    },
    pieGradient(){
      const segs = this.divisionSegments();
      if (!segs.length) return 'conic-gradient(#e5e7eb 0 360deg)';
      let acc = 0;
      const parts = segs.map(seg => {
        const start = acc;
        const sweep = (seg.percent / 100) * 360;
        const end = start + sweep;
        acc = end;
        return `${seg.color} ${start}deg ${end}deg`;
      });
      if (acc < 360) parts.push(`#e5e7eb ${acc}deg 360deg`);
      return `conic-gradient(${parts.join(',')})`;
    },
    changePercent(){
      const p = this.prevTotal();
      const f = this.forecastTotal();
      if (!p) return f === 0 ? 0 : 100;
      return ((f - p) / Math.abs(p)) * 100;
    },
    changeRing(){
      const c = this.changePercent();
      const pct = Math.max(0, Math.min(100, Math.abs(c)));
      const color = c >= 0 ? '#16a34a' : '#dc2626';
      return `conic-gradient(${color} 0 ${pct}%, #e5e7eb ${pct}%)`;
    },
  };
}
