export function equipmentEditor(root) {
  const toNum = (v, def = 0) => {
    const n = parseFloat(v);
    return Number.isFinite(n) ? n : def;
  };
  const rd = () => (root?.__x?.$data ? root.__x.$data : (window.__budgetRoot || root));
  const rowsRef = () => (Array.isArray(rd()?.equipmentRows) ? rd().equipmentRows : (Array.isArray(window.__initialEquipmentRows) ? window.__initialEquipmentRows : []));
  const genRef = () => (rd()?.equipmentGeneral || window.__initialEquipmentGeneral || { fuel: 0, repairs: 0, insurance_misc: 0 });
  const setGen = (g) => { if (rd()) rd().equipmentGeneral = g; };
  const rentalsRef = () => (typeof rd()?.equipmentRentals !== 'undefined' ? rd().equipmentRentals : Number(window.__initialEquipmentRentals || 0));
  const setRentals = (v) => { if (rd()) rd().equipmentRentals = Number(v || 0); };
  const avgRef = () => (typeof rd()?.equipmentIndustryAvgRatio !== 'undefined' ? rd().equipmentIndustryAvgRatio : Number(window.__initialEquipmentIndustryAvgRatio || 13.7));
  const setAvg = (v) => { if (rd()) rd().equipmentIndustryAvgRatio = Number(v || 0); };

  return {
    get equipmentRows(){ return rowsRef(); },
    set equipmentRows(v){ if (Array.isArray(root?.equipmentRows)) root.equipmentRows = v; },

    get equipmentGeneral(){ return genRef(); },
    set equipmentGeneral(v){ setGen(v); },

    get equipmentRentals(){ return rentalsRef(); },
    set equipmentRentals(v){ setRentals(v); },

    get equipmentIndustryAvgRatio(){ return avgRef(); },
    set equipmentIndustryAvgRatio(v){ setAvg(v); },

    // actions
    addEquipmentRow(){
      this.equipmentRows.push({ type:'', qty:'', class:'Custom', description:'', cost_per_year:'', _ownedOpen:false, _menuOpen:false, owned:{ replacement_value:'', fees:'', years:'', salvage_value:'', months_per_year:'', division_months:'', interest_rate_pct:'' }, leased:{ monthly_payment:'', payments_per_year:'', months_per_year:'', division_months:'' }, group: { items: [] } });
    },
    removeEquipmentRow(i){ this.equipmentRows.splice(i,1); },
    addGroupItem(row){ if (!row.group) row.group = { items: [] }; row.group.items.push({ name:'', qty:'', purchase_price:'', resale_value:'', years:'' }); },
    removeGroupItem(row, i){ try { row.group.items.splice(i,1); } catch(_) {} },

    // owned/leased/group calculators
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
    perUnitCost(row){ if (row.class==='Owned') return this.computeOwnedAnnual(row); if (row.class==='Leased') return this.computeLeasedAnnual(row); if (row.class==='Group') return this.computeGroupAnnual(row); return toNum(row.cost_per_year)||0; },
    equipmentRowTotal(row){ const q = toNum(row.qty)||0; const c = this.perUnitCost(row); return q * c; },
    equipmentTotal(){ return this.equipmentRows.reduce((s,r)=> s + this.equipmentRowTotal(r), 0); },
    equipmentDisplayedListTotal(){ return this.equipmentRows.reduce((s,r)=> s + (this.perUnitCost(r)||0), 0); },
    generalExpensesTotal(){ const g = this.equipmentGeneral || {}; return (toNum(g.fuel)||0) + (toNum(g.repairs)||0) + (toNum(g.insurance_misc)||0); },
    equipmentExpensesTotal(){ return (this.equipmentDisplayedListTotal() || 0) + (this.generalExpensesTotal() || 0); },
    equipmentGrandTotal(){ return this.equipmentExpensesTotal() + (toNum(this.equipmentRentals)||0); },
    equipmentRatio(){
      let sales = 0;
      const r = rd();
      if (r && typeof r.forecastTotal === 'function') sales = r.forecastTotal();
      else if (Array.isArray(r?.salesRows)) sales = r.salesRows.reduce((s,row)=> s + (toNum(row?.forecast)||0), 0);
      if (!sales) return 0;
      return (this.equipmentGrandTotal() / Math.abs(sales)) * 100;
    },
    within4(cur, avg){ const a = Number(cur)||0; const b = Number(avg)||0; return Math.abs(a - b) <= 4; },
    equipmentPillClass(){ return this.within4(this.equipmentRatio(), this.equipmentIndustryAvgRatio) ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; },

    // shared UI helpers
    formatMoney(n){ const v = parseFloat(n) || 0; return '$' + v.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }); },
    moveEquipmentToOverhead(i){
      const row = this.equipmentRows[i];
      if (!row || !Array.isArray(root?.overheadEquipmentRows)) return;
      const clone = JSON.parse(JSON.stringify(row));
      clone._ownedOpen = false; clone._menuOpen = false;
      root.overheadEquipmentRows.push(clone);
      this.removeEquipmentRow(i);
    },
  };
}
