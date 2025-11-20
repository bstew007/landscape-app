export function overheadEditor(root) {
  const toNum = (v, def = 0) => { const n = parseFloat(v); return Number.isFinite(n) ? n : def; };
  const rd = () => (root?.__x?.$data ? root.__x.$data : (window.__budgetRoot || root)); // Resolve Alpine root data when a DOM element is passed
  const expRows = () => (Array.isArray(rd()?.overheadExpensesRows) ? rd().overheadExpensesRows : (Array.isArray(window.__initialOverheadExpensesRows) ? window.__initialOverheadExpensesRows : []));
  const wageRows = () => (Array.isArray(rd()?.overheadWagesRows) ? rd().overheadWagesRows : (Array.isArray(window.__initialOverheadWagesRows) ? window.__initialOverheadWagesRows : []));
  const eqRows = () => (Array.isArray(rd()?.overheadEquipmentRows) ? rd().overheadEquipmentRows : (Array.isArray(window.__initialOverheadEquipmentRows) ? window.__initialOverheadEquipmentRows : []));
  const eqGen = () => (rd()?.overheadEquipmentGeneral || window.__initialOverheadEquipmentGeneral || { fuel:0, repairs:0, insurance_misc:0 });
  const setEqGen = (g) => { if (rd()) rd().overheadEquipmentGeneral = g; };
  const eqRentals = () => (typeof rd()?.overheadEquipmentRentals !== 'undefined' ? rd().overheadEquipmentRentals : Number(window.__initialOverheadEquipmentRentals || 0));
  const setEqRentals = (v) => { if (rd()) rd().overheadEquipmentRentals = Number(v || 0); };
  const indAvg = () => (typeof rd()?.overheadIndustryAvgRatio !== 'undefined' ? rd().overheadIndustryAvgRatio : Number(window.__initialOverheadIndustryAvg || 24.8));
  const setIndAvg = (v) => { if (rd()) rd().overheadIndustryAvgRatio = Number(v || 0); };
  const laborBurden = () => (typeof rd()?.overheadLaborBurdenPct !== 'undefined' ? rd().overheadLaborBurdenPct : Number(window.__initialOverheadLaborBurden || 0));
  const setLaborBurden = (v) => { if (rd()) rd().overheadLaborBurdenPct = Number(v || 0); };

  return {
    get overheadExpensesRows(){ return expRows(); }, set overheadExpensesRows(v){ if (Array.isArray(root?.overheadExpensesRows)) root.overheadExpensesRows = v; },
    get overheadWagesRows(){ return wageRows(); }, set overheadWagesRows(v){ if (Array.isArray(root?.overheadWagesRows)) root.overheadWagesRows = v; },
    get overheadEquipmentRows(){ return eqRows(); }, set overheadEquipmentRows(v){ if (Array.isArray(root?.overheadEquipmentRows)) root.overheadEquipmentRows = v; },

    get overheadEquipmentGeneral(){ return eqGen(); }, set overheadEquipmentGeneral(v){ setEqGen(v); },
    get overheadEquipmentRentals(){ return eqRentals(); }, set overheadEquipmentRentals(v){ setEqRentals(v); },
    get overheadIndustryAvgRatio(){ return indAvg(); }, set overheadIndustryAvgRatio(v){ setIndAvg(v); },
    get overheadLaborBurdenPct(){ return laborBurden(); }, set overheadLaborBurdenPct(v){ setLaborBurden(v); },

    // actions
    addOverheadExpenseRow(){ this.overheadExpensesRows.push({ account_id:'', expense:'', previous:'', current:'', comments:'' }); },
    removeOverheadExpenseRow(i){ this.overheadExpensesRows.splice(i,1); },
    addOverheadWageRow(){ this.overheadWagesRows.push({ title:'', previous:'', forecast:'', comments:'' }); },
    removeOverheadWageRow(i){ this.overheadWagesRows.splice(i,1); },
    addOverheadEquipmentRow(){ this.overheadEquipmentRows.push({ type:'', qty:'', class:'Custom', description:'', cost_per_year:'', _ownedOpen:false, _menuOpen:false, owned:{ replacement_value:'', fees:'', years:'', salvage_value:'', months_per_year:'', division_months:'', interest_rate_pct:'' }, leased:{ monthly_payment:'', payments_per_year:'', months_per_year:'', division_months:'' } }); },
    removeOverheadEquipmentRow(i){ this.overheadEquipmentRows.splice(i,1); },
    moveOverheadEquipmentToEquipment(i){
      const row = this.overheadEquipmentRows[i];
      if (!row || !Array.isArray(root?.equipmentRows)) return;
      const clone = JSON.parse(JSON.stringify(row));
      clone._ownedOpen = false; clone._menuOpen = false;
      root.equipmentRows.push(clone);
      this.removeOverheadEquipmentRow(i);
    },

    // calculators (reuse same as equipment)
    perUnitCost(row){ if (row.class==='Owned') return this.computeOwnedAnnual(row); if (row.class==='Leased') return this.computeLeasedAnnual(row); if (row.class==='Group') return this.computeGroupAnnual(row); return toNum(row.cost_per_year)||0; },
    computeOwnedAnnual(row){
      const cap = (toNum(row?.owned?.replacement_value) || 0) + (toNum(row?.owned?.fees) || 0);
      const years = Math.max(0.1, toNum(row?.owned?.years) || 0);
      const rate = Math.max(0, Math.min(100, toNum(row?.owned?.interest_rate_pct) || 0)) / 100;
      const salvage = Math.max(0, toNum(row?.owned?.salvage_value) || 0);
      const totalInflationLife = cap * (Math.pow(1 + rate, years) - 1);
      const numerator = (cap + totalInflationLife) - salvage;
      return Math.max(0, numerator / years);
    },
    computeOwnedMonthlyCalendar(row){ return this.computeOwnedAnnual(row) / 12; },
    computeOwnedMonthlyActive(row){ const ann = this.computeOwnedAnnual(row); const m = Math.max(1, parseInt(row?.owned?.months_per_year) || 12); return ann / m; },
    computeDivisionAnnual(row){ const ann = this.computeOwnedAnnual(row); const dm = Math.max(0, parseInt(row?.owned?.division_months) || 0); return ann * (dm / 12); },
    computeDivisionMonthlyActive(row){ const da = this.computeDivisionAnnual(row); const dm = Math.max(1, parseInt(row?.owned?.division_months) || 1); return da / dm; },
    computeOwnedInterestLifeCompounded(row){ if (row.class !== 'Owned') return 0; const cap = (toNum(row?.owned?.replacement_value)||0) + (toNum(row?.owned?.fees)||0); const years = Math.max(0.1, toNum(row?.owned?.years)||0); const rate = Math.max(0, Math.min(100, toNum(row?.owned?.interest_rate_pct)||0))/100; return Math.max(0, cap * (Math.pow(1 + rate, years) - 1)); },

    computeGroupItemAnnual(it){ const p = toNum(it?.purchase_price)||0; const r = toNum(it?.resale_value)||0; const y = Math.max(0.1, toNum(it?.years)||0); return Math.max(0, (p - r) / y); },
    computeGroupAnnual(row){ const items = (row?.group?.items || []); return items.reduce((s,it)=> s + ((toNum(it?.qty)||0) * this.computeGroupItemAnnual(it)), 0); },

    computeLeasedAnnual(row){ const pmt = Math.max(0, toNum(row?.leased?.monthly_payment)||0); const perYear = Math.max(1, parseInt(row?.leased?.payments_per_year) || 12); return pmt * perYear; },
    computeLeasedMonthlyCalendar(row){ return this.computeLeasedAnnual(row) / 12; },
    computeLeasedMonthlyActive(row){ const ann = this.computeLeasedAnnual(row); const m = Math.max(1, parseInt(row?.leased?.months_per_year) || 12); return ann / m; },
    computeLeasedDivisionAnnual(row){ const ann = this.computeLeasedAnnual(row); const dm = Math.max(0, parseInt(row?.leased?.division_months) || 0); return ann * (dm / 12); },
    computeLeasedDivisionMonthlyActive(row){ const da = this.computeLeasedDivisionAnnual(row); const dm = Math.max(1, parseInt(row?.leased?.division_months) || 1); return da / dm; },

    // totals/ratios
    overheadExpensesPrevTotal(){ return this.overheadExpensesRows.reduce((s,r)=> s + (toNum(r.previous)||0), 0); },
    overheadExpensesCurrentTotal(){ return this.overheadExpensesRows.reduce((s,r)=> s + (toNum(r.current)||0), 0); },
    overheadWagesPrevTotal(){ return this.overheadWagesRows.reduce((s,r)=> s + (toNum(r.previous)||0), 0); },
    overheadWagesForecastTotal(){ return this.overheadWagesRows.reduce((s,r)=> s + (toNum(r.forecast)||0), 0); },
    overheadEquipmentDisplayedListTotal(){ return this.overheadEquipmentRows.reduce((s,r)=> s + (this.perUnitCost(r)||0), 0); },
    overheadEquipmentRowTotal(row){ const qRaw = row?.qty; const q = (qRaw === '' || qRaw === null || qRaw === undefined) ? 1 : (toNum(qRaw)||0); const c = this.perUnitCost(row); return q * c; },
    overheadEquipmentTotal(){ return this.overheadEquipmentRows.reduce((s,r)=> s + this.overheadEquipmentRowTotal(r), 0); },
    overheadEquipmentExpensesTotal(){ const g = this.overheadEquipmentGeneral || {}; return (this.overheadEquipmentDisplayedListTotal() || 0) + (toNum(g.fuel)||0) + (toNum(g.repairs)||0) + (toNum(g.insurance_misc)||0); },
    overheadCurrentTotal(){ return this.overheadExpensesCurrentTotal() + this.overheadWagesForecastTotal() + this.overheadEquipmentTotal(); },
    overheadPrevTotal(){ return this.overheadExpensesPrevTotal() + this.overheadWagesPrevTotal(); },
    overheadRatio(){
      let sales = 0;
      const r = rd();
      if (r && typeof r.forecastTotal === 'function') sales = r.forecastTotal();
      else if (Array.isArray(r?.salesRows)) sales = r.salesRows.reduce((s,row)=> s + (toNum(row?.forecast)||0), 0);
      if (!sales) return 0;
      return (this.overheadCurrentTotal() / Math.abs(sales)) * 100;
    },
    within4(cur, avg){ const a = Number(cur)||0; const b = Number(avg)||0; return Math.abs(a - b) <= 4; },
    overheadPillClass(){ return this.within4(this.overheadRatio(), this.overheadIndustryAvgRatio) ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; },

    // shared UI helper
    formatMoney(n){ const v = parseFloat(n) || 0; return '$' + v.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }); },
  };
}
