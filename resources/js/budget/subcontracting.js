export function subcontractingEditor(root) {
  const toNum = (v, def = 0) => {
    const n = parseFloat(v);
    return Number.isFinite(n) ? n : def;
  };
  const rowsRef = () => (Array.isArray(root?.subcontractingRows) ? root.subcontractingRows : (Array.isArray(window.__initialSubcontractingRows) ? window.__initialSubcontractingRows : []));
  return {
    get subcontractingRows(){ return rowsRef(); },
    set subcontractingRows(v){ if (Array.isArray(root?.subcontractingRows)) root.subcontractingRows = v; },

    // actions
    addSubcontractingRow(){ this.subcontractingRows.push({ account_id:'', expense:'', previous:'', current:'', comments:'' }); },
    removeSubcontractingRow(i){ this.subcontractingRows.splice(i,1); },

    // helpers
    formatMoney(n){ const v = parseFloat(n) || 0; return '$' + v.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }); },

    // totals/ratios sourced from root sales
    subcPrevTotal(){ return this.subcontractingRows.reduce((s,r)=> s + (toNum(r.previous)||0), 0); },
    subcCurrentTotal(){ return this.subcontractingRows.reduce((s,r)=> s + (toNum(r.current)||0), 0); },
    subcPrevRatio(){ const sales = typeof root?.forecastTotal === 'function' ? root.forecastTotal() : 0; if (!sales) return 0; return (this.subcPrevTotal() / Math.abs(sales)) * 100; },
    subcRatio(){ const sales = typeof root?.forecastTotal === 'function' ? root.forecastTotal() : 0; if (!sales) return 0; return (this.subcCurrentTotal() / Math.abs(sales)) * 100; },
  };
}
