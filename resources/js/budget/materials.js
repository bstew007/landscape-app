export function materialsEditor(root) {
  const toNum = (v, def = 0) => {
    const n = parseFloat(v);
    return Number.isFinite(n) ? n : def;
  };
  const rd = () => (root?.__x?.$data ? root.__x.$data : (window.__budgetRoot || root));
  // Map child state to root state so sidebar pills remain in sync
  const rowsRef = () => (Array.isArray(rd()?.materialsRows) ? rd().materialsRows : (Array.isArray(window.__initialMaterialsRows) ? window.__initialMaterialsRows : []));
  const getTaxPct = () => (typeof rd()?.materialsTaxPct !== 'undefined' ? rd().materialsTaxPct : Number(window.__initialMaterialsTaxPct || 0));
  const setTaxPct = (v) => { if (typeof rd()?.materialsTaxPct !== 'undefined') rd().materialsTaxPct = Number(v || 0); };
  const getIndustryAvg = () => (typeof rd()?.materialsIndustryAvgRatio !== 'undefined' ? rd().materialsIndustryAvgRatio : Number(window.__initialMaterialsIndustryAvg || 22.3));
  const setIndustryAvg = (v) => { if (typeof rd()?.materialsIndustryAvgRatio !== 'undefined') rd().materialsIndustryAvgRatio = Number(v || 0); };

  return {
    // rows are proxied to root to keep overall totals in sync
    get materialsRows() { return rowsRef(); },
    set materialsRows(v) { if (Array.isArray(root?.materialsRows)) root.materialsRows = v; },

    get materialsTaxPct() { return getTaxPct(); },
    set materialsTaxPct(v) { setTaxPct(v); },

    get materialsIndustryAvgRatio() { return getIndustryAvg(); },
    set materialsIndustryAvgRatio(v) { setIndustryAvg(v); },

    // actions
    addMaterialsRow(){ this.materialsRows.push({ account_id:'', expense:'', previous:'', current:'', comments:'' }); },
    removeMaterialsRow(i){ this.materialsRows.splice(i,1); },

    // helpers
    formatMoney(n){ const v = parseFloat(n) || 0; return '$' + v.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }); },
    within4(cur, avg){ const a = Number(cur)||0; const b = Number(avg)||0; return Math.abs(a - b) <= 4; },
    materialsPillClass(){ return this.within4(this.materialsRatio(), this.materialsIndustryAvgRatio) ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; },
    materialsPillClassFor(val){ return this.within4(val, this.materialsIndustryAvgRatio) ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; },

    // totals/ratios
    materialsPrevTotal(){ const sum = this.materialsRows.reduce((s,r)=> s + (toNum(r.previous)||0), 0); const t = (toNum(this.materialsTaxPct)||0)/100; return sum * (1 + Math.max(0,t)); },
    materialsCurrentTotal(){ const sum = this.materialsRows.reduce((s,r)=> s + (toNum(r.current)||0), 0); const t = (toNum(this.materialsTaxPct)||0)/100; return sum * (1 + Math.max(0,t)); },
    materialsPrevRatio(){
      let sales = 0;
      const r = rd();
      if (r && typeof r.forecastTotal === 'function') sales = r.forecastTotal();
      else if (Array.isArray(r?.salesRows)) sales = r.salesRows.reduce((s,row)=> s + (toNum(row?.forecast)||0), 0);
      if (!sales) return 0;
      return (this.materialsPrevTotal() / Math.abs(sales)) * 100;
    },
    materialsRatio(){
      let sales = 0;
      const r = rd();
      if (r && typeof r.forecastTotal === 'function') sales = r.forecastTotal();
      else if (Array.isArray(r?.salesRows)) sales = r.salesRows.reduce((s,row)=> s + (toNum(row?.forecast)||0), 0);
      if (!sales) return 0;
      return (this.materialsCurrentTotal() / Math.abs(sales)) * 100;
    },
  };
}
