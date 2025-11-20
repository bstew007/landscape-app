export function ohRecoveryEditor(root) {
  const toNum = (v, d = 0) => {
    const n = parseFloat(v);
    return Number.isFinite(n) ? n : d;
  };
  const rd = () => (root?.__x?.$data ? root.__x.$data : (window.__budgetRoot || root));
  return {
    laborHourActivated: false,
    // Derived from root store
    forecastOverhead(){
      const r = rd();
      if (!r) return 0;
      if (typeof r.overheadCurrentTotal === 'function') return toNum(r.overheadCurrentTotal());
      // Fallback if method isn't available
      const exp = Array.isArray(r.overheadExpensesRows) ? r.overheadExpensesRows.reduce((s,row)=> s + (toNum(row?.current)||0), 0) : 0;
      const wages = Array.isArray(r.overheadWagesRows) ? r.overheadWagesRows.reduce((s,row)=> s + (toNum(row?.forecast)||0), 0) : 0;
      const eq = Array.isArray(r.overheadEquipmentRows) ? r.overheadEquipmentRows.reduce((s,row)=> s + (toNum(row?.qty)||0) * (toNum(row?.cost_per_year)||0), 0) : 0;
      return exp + wages + eq;
    },
    forecastLaborHours(){
      const r = rd();
      if (!r) return 0;
      if (typeof r.totalHours === 'function') return toNum(r.totalHours());
      const mult = toNum(r.otMultiplier, 1.5);
      const h = Array.isArray(r.hourlyRows) ? r.hourlyRows.reduce((sum, row) => sum + (toNum(row?.staff) * (toNum(row?.hrs) + (toNum(row?.ot_hrs) * mult))), 0) : 0;
      const s = Array.isArray(r.salaryRows) ? r.salaryRows.reduce((sum, row) => sum + (toNum(row?.staff) * toNum(row?.ann_hrs)), 0) : 0;
      return Math.round(h + s);
    },
    markupPerHour(){
      const oh = toNum(this.forecastOverhead());
      const hrs = Math.max(0, toNum(this.forecastLaborHours()));
      if (!hrs) return 0;
      return oh / hrs;
    },
    formatMoney(n){ const v = toNum(n); return '$' + v.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }); },
  };
}
