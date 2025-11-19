export function fieldLaborEditor(root) {
  const toNum = (v, def = 0) => {
    const n = parseFloat(v);
    return Number.isFinite(n) ? n : def;
  };
  return {
    laborTab: 'hourly',
    hourlyRows: Array.isArray(window.__initialHourlyRows) ? window.__initialHourlyRows : [],
    salaryRows: Array.isArray(window.__initialSalaryRows) ? window.__initialSalaryRows : [],
    burdenPct: (window.__initialLaborBurdenPct !== null && window.__initialLaborBurdenPct !== undefined && window.__initialLaborBurdenPct !== '') ? Number(window.__initialLaborBurdenPct) : 0,
    otMultiplier: (window.__initialOtMultiplier !== null && window.__initialOtMultiplier !== undefined && window.__initialOtMultiplier !== '') ? Number(window.__initialOtMultiplier) : 1.5,
    industryAvgRatio: (window.__initialIndustryAvgRatio !== null && window.__initialIndustryAvgRatio !== undefined && window.__initialIndustryAvgRatio !== '') ? Number(window.__initialIndustryAvgRatio) : 26.6,
    // actions
    addHourlyRow(){ this.hourlyRows.push({ type:'', staff:'', hrs:'', ot_hrs:'', avg_wage:'', bonus:'' }); },
    removeHourlyRow(i){ this.hourlyRows.splice(i,1); },
    addSalaryRow(){ this.salaryRows.push({ type:'', staff:'', ann_hrs:'', ann_salary:'', bonus:'' }); },
    removeSalaryRow(i){ this.salaryRows.splice(i,1); },
    // helpers
    wagesHourlyPerEmp(row){
      const hrs = toNum(row.hrs);
      const ot = toNum(row.ot_hrs);
      const wage = toNum(row.avg_wage);
      const bonus = toNum(row.bonus);
      const mult = toNum(this.otMultiplier, 1.5);
      return (hrs * wage) + (ot * wage * mult) + bonus;
    },
    wagesHourlyRow(row){
      const staff = toNum(row.staff);
      return staff * this.wagesHourlyPerEmp(row);
    },
    wagesSalaryPerEmp(row){
      const sal = toNum(row.ann_salary);
      const bonus = toNum(row.bonus);
      return sal + bonus;
    },
    wagesSalaryRow(row){
      const staff = toNum(row.staff);
      return staff * this.wagesSalaryPerEmp(row);
    },
    totalHours(){
      const mult = toNum(this.otMultiplier, 1.5);
      const h = this.hourlyRows.reduce((sum, r) => {
        const staff = toNum(r.staff);
        const hrs = toNum(r.hrs);
        const ot = toNum(r.ot_hrs);
        return sum + (staff * (hrs + (ot * mult)));
      }, 0);
      const s = this.salaryRows.reduce((t, r) => t + ( toNum(r.staff) * toNum(r.ann_hrs) ), 0);
      return Math.round(h + s);
    },
    totalWages(){
      const h = this.hourlyRows.reduce((s, r) => s + this.wagesHourlyRow(r), 0);
      const s = this.salaryRows.reduce((t, r) => t + this.wagesSalaryRow(r), 0);
      return h + s;
    },
    totalBurden(){
      const pct = (toNum(this.burdenPct) || 0) / 100;
      return this.totalWages() * pct;
    },
    fieldPayroll(){
      return this.totalWages() + this.totalBurden();
    },
    laborRatio(){
      const sales = typeof root?.forecastTotal === 'function' ? root.forecastTotal() : 0;
      if (!sales) return 0;
      return (this.fieldPayroll() / Math.abs(sales)) * 100;
    },
    formatMoney(n){ const v = parseFloat(n) || 0; return '$' + v.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }); },
    within4(cur, avg){ const a = Number(cur)||0; const b = Number(avg)||0; return Math.abs(a - b) <= 4; },
    laborPillClass(){ return this.within4(this.laborRatio(), this.industryAvgRatio) ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; },
    overtimeOptions(){ const out = []; for (let v = 1.25; v <= 3.0001; v += 0.25) out.push(Number(v.toFixed(2))); return out; },
  };
}
