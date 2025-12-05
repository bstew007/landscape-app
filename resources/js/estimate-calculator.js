export function initEstimateCalculatorDrawer({ estimateId, areas }) {
    const state = {
        showCalcPanel: false,
        calcTab: 'create',
        calcType: 'mulching',
        templates: [],
        loadingTemplates: false,
    };

    function calcHref(type) {
        const map = window.__calcRoutes || {};
        const base = map[type] || map.mulching;
        const url = new URL(base, window.location.origin);
        url.searchParams.set('mode','template');
        url.searchParams.set('estimate_id', String(estimateId));
        return url.toString();
    }

    async function fetchTemplates() {
        state.loadingTemplates = true;
        const resp = await fetch(`${window.__estimateTemplatesUrl}?type=${state.calcType}`);
        const json = await resp.json().catch(() => ({ templates: [] }));
        state.templates = json.templates || [];
        state.loadingTemplates = false;
        renderTemplates();
    }

    function renderTemplates() {
        const listContainer = document.getElementById('calcTplList');
        const loadingEl = document.getElementById('calcTplLoading');
        if (!listContainer || !loadingEl) return;
        loadingEl.style.display = state.loadingTemplates ? '' : 'none';
        listContainer.innerHTML = '';
        if (!state.loadingTemplates && (!state.templates || !state.templates.length)) {
            const empty = document.createElement('div');
            empty.className = 'text-sm text-gray-500';
            empty.textContent = 'No templates yet for this type.';
            listContainer.appendChild(empty);
            return;
        }
        state.templates.forEach(tpl => {
            const row = document.createElement('div');
            row.className = 'rounded border p-3 flex items-center justify-between';
            const left = document.createElement('div');
            const name = document.createElement('p');
            name.className = 'font-semibold';
            name.textContent = tpl.template_name || 'Untitled';
            const meta = document.createElement('p');
            meta.className = 'text-xs text-gray-500';
            const scope = (tpl.template_scope || 'global').toUpperCase();
            const date = tpl.created_at ? new Date(tpl.created_at).toLocaleDateString() : '';
            meta.textContent = `${scope} • ${date}`;
            left.appendChild(name);
            left.appendChild(meta);
            const right = document.createElement('div');
            right.className = 'flex items-center gap-2';
            const importBtn = document.createElement('button');
            importBtn.className = 'px-3 py-1 text-sm rounded bg-brand-700 text-white hover:bg-brand-800';
            importBtn.textContent = 'Import';
            importBtn.addEventListener('click', () => importTemplate(tpl.id, false));
            const replaceBtn = document.createElement('button');
            replaceBtn.className = 'px-3 py-1 text-sm rounded border border-brand-300 text-brand-700 hover:bg-brand-50';
            replaceBtn.textContent = 'Replace';
            replaceBtn.addEventListener('click', () => importTemplate(tpl.id, true));
            right.appendChild(importBtn);
            right.appendChild(replaceBtn);
            row.appendChild(left);
            row.appendChild(right);
            listContainer.appendChild(row);
        });
    }

    async function importTemplate(id, replace) {
        try {
            const resp = await fetch(window.__estimateImportUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ template_id: id, replace: !!replace })
            });
            if (!resp.ok) throw await resp.json().catch(()=>({}));
            const json = await resp.json();
            if (json.totals && window.updateSummary) window.updateSummary(json.totals);
            if (json.items && Array.isArray(json.items)) insertImportedRows(json, areas);
            
            // Track recently used template
            if (json.template_info) {
                const recent = JSON.parse(sessionStorage.getItem('recentTemplates') || '[]');
                const filtered = recent.filter(t => t.id !== json.template_info.id);
                const updated = [json.template_info, ...filtered].slice(0, 5);
                sessionStorage.setItem('recentTemplates', JSON.stringify(updated));
                // Trigger Alpine update
                window.dispatchEvent(new CustomEvent('template-used', { detail: json.template_info }));
            }
            
            state.showCalcPanel = false;
            if (window.showToast) window.showToast(replace ? 'Template imported (replaced)' : 'Template imported', 'success');
        } catch (e) {
            if (window.showToast) window.showToast('Failed to import template','error');
        }
    }

    function insertImportedRows(json, areas){
        const tbody = document.querySelector('table tbody');
        if (!tbody) return;
        // Header
        const header = document.createElement('tr');
        header.className = 'bg-gray-50';
        header.setAttribute('data-calculation-id', json.calculation_id);
        const titleTd = document.createElement('td');
        titleTd.colSpan = 7;
        titleTd.className = 'px-3 py-2 text-gray-700 font-semibold';
        const calcTitle = (json.calculation_type || '').replace(/_/g,' ').replace(/\b\w/g,s=>s.toUpperCase());
        titleTd.textContent = `${calcTitle} Calculation`;
        const subTd = document.createElement('td');
        subTd.className = 'px-3 py-2 text-right font-semibold text-gray-900';
        subTd.setAttribute('data-role','group-subtotal');
        subTd.textContent = '$0.00';
        const actionsTd = document.createElement('td');
        actionsTd.className = 'px-3 py-2 text-right space-x-2';
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'text-red-600 hover:underline text-sm';
        removeBtn.dataset.action = 'remove-group';
        removeBtn.dataset.calculationId = json.calculation_id;
        removeBtn.textContent = 'Remove Items';
        actionsTd.appendChild(removeBtn);
        header.appendChild(titleTd);
        header.appendChild(subTd);
        header.appendChild(actionsTd);
        tbody.appendChild(header);

        let groupSubtotal = 0;
        json.items.forEach(item => {
            const row = document.createElement('tr');
            row.className = 'border-t';
            row.setAttribute('data-item-id', item.id);
            row.setAttribute('data-calculation-id', item.calculation_id || '');
            row.setAttribute('draggable','true');
            row.dataset.name = item.name || 'Line Item';
            row.dataset.itemType = (item.item_type || 'item');
            row.dataset.quantity = item.quantity ?? 0;
            row.dataset.unit = item.unit || '';
            row.dataset.unitCost = item.unit_cost ?? 0;
            row.dataset.unitPrice = item.unit_price ?? item.unit_cost ?? 0;
            row.dataset.marginRate = item.margin_rate ?? 0;
            row.dataset.taxRate = item.tax_rate ?? 0;
            row.dataset.costTotal = item.cost_total ?? 0;
            row.dataset.marginTotal = item.margin_total ?? 0;

            // Cells
            const typeTd = document.createElement('td'); typeTd.className='px-3 py-2 text-gray-600 capitalize'; typeTd.textContent = item.item_type;
            const descTd = document.createElement('td'); descTd.className='px-3 py-2';
                const nameDiv = document.createElement('div'); nameDiv.className='font-semibold text-gray-900'; nameDiv.textContent = item.name || 'Line Item';
                descTd.appendChild(nameDiv);
                if (item.description) { const p = document.createElement('p'); p.className='text-xs text-gray-500'; p.textContent = item.description; descTd.appendChild(p); }
                const costP = document.createElement('p'); costP.className='mt-1 text-xs text-gray-500';
                    const costSpan = document.createElement('span'); costSpan.setAttribute('data-col','cost_total'); costSpan.textContent = money(item.cost_total);
                    costP.innerHTML = 'Cost total: ';
                    costP.appendChild(costSpan);
                descTd.appendChild(costP);
            const qtyTd = document.createElement('td'); qtyTd.className='px-3 py-2 text-center text-gray-700'; qtyTd.setAttribute('data-col','quantity'); qtyTd.textContent = qtyText(item.quantity, item.unit);
            const ucTd = document.createElement('td'); ucTd.className='px-3 py-2 text-center text-gray-700'; ucTd.setAttribute('data-col','unit_cost'); ucTd.textContent = money(item.unit_cost);
            const upTd = document.createElement('td'); upTd.className='px-3 py-2 text-center text-gray-700'; upTd.setAttribute('data-col','unit_price'); upTd.textContent = money(item.unit_price);
            const marginTd = document.createElement('td'); marginTd.className='px-3 py-2 text-center text-gray-700'; marginTd.setAttribute('data-col','margin');
                const mp = document.createElement('div'); mp.className = 'font-semibold text-gray-900'; mp.setAttribute('data-col','margin_percent'); mp.textContent = `${((item.margin_rate||0)*100).toFixed(2)}%`;
                const mt = document.createElement('div'); mt.className = 'text-xs text-gray-500'; mt.setAttribute('data-col','margin_total'); mt.textContent = money(item.margin_total);
                marginTd.appendChild(mp); marginTd.appendChild(mt);
            const taxTd = document.createElement('td'); taxTd.className='px-3 py-2 text-center text-gray-700'; taxTd.setAttribute('data-col','tax_rate'); taxTd.textContent = (Number(item.tax_rate||0)>0) ? `${(Number(item.tax_rate)*100).toFixed(2)}%` : '—';
            const totalTd = document.createElement('td'); totalTd.className='px-3 py-2 text-right font-semibold text-gray-900'; totalTd.setAttribute('data-col','line_total'); totalTd.textContent = money(item.line_total);
            const actTd = document.createElement('td'); actTd.className='px-3 py-2 text-right space-x-3'; actTd.setAttribute('data-col','actions');
                const label = document.createElement('label'); label.className='text-xs text-gray-500 mr-1'; label.textContent = 'Area';
                const areaSel = document.createElement('select'); areaSel.className='form-select text-xs'; areaSel.setAttribute('data-action','set-area'); areaSel.setAttribute('data-item-id', item.id);
                    const opt0 = document.createElement('option'); opt0.value=''; opt0.textContent='Unassigned'; areaSel.appendChild(opt0);
                    (areas||[]).forEach(a=>{ const o=document.createElement('option'); o.value=String(a.id); o.textContent=a.name; areaSel.appendChild(o); });
                const editBtn = document.createElement('button'); editBtn.type='button'; editBtn.className='text-blue-600 hover:underline text-sm ml-2'; editBtn.dataset.action='edit-item'; editBtn.dataset.itemId=String(item.id); editBtn.textContent='Edit';
                const delForm = document.createElement('form'); delForm.action = `${window.__estimateItemsBaseUrl}/${item.id}`; delForm.method='POST'; delForm.className='inline'; delForm.onsubmit = ()=> confirm('Remove this line item?');
                    const tkn = document.createElement('input'); tkn.type='hidden'; tkn.name='_token'; tkn.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const mth = document.createElement('input'); mth.type='hidden'; mth.name='_method'; mth.value='DELETE';
                    const delBtn = document.createElement('button'); delBtn.className='text-red-600 hover:underline text-sm'; delBtn.textContent='Delete';
                    delForm.appendChild(tkn); delForm.appendChild(mth); delForm.appendChild(delBtn);
                actTd.appendChild(label); actTd.appendChild(areaSel); actTd.appendChild(editBtn); actTd.appendChild(delForm);

            row.appendChild(typeTd); row.appendChild(descTd); row.appendChild(qtyTd); row.appendChild(ucTd); row.appendChild(upTd); row.appendChild(marginTd); row.appendChild(taxTd); row.appendChild(totalTd); row.appendChild(actTd);
            tbody.appendChild(row);
            groupSubtotal += Number(item.line_total||0);
        });
        const sub = header.querySelector('[data-role="group-subtotal"]'); if (sub) sub.textContent = money(groupSubtotal);

        function money(v){ return `$${Number(v||0).toFixed(2)}`; }
        function qtyText(q,u){ const n = Number(q||0).toFixed(2).replace(/\.00$/,''); return `${n} ${u||''}`.trim(); }
    }

    const api = { state, calcHref, fetchTemplates, importTemplate };
    
    // Make available globally for Alpine components
    window.estimateCalculator = api;
    
    return api;
}
