import { initEstimateCalculatorDrawer } from './estimate-calculator';

const STORAGE_KEY = 'estimate:addDrawerState';
const DEFAULT_STATE = {
    tab: 'work',
    activeArea: 'all',
    showAddItems: false,
    addItemsTab: 'labor',
};

const safeJsonParse = (value, fallback) => {
    try {
        return value ? JSON.parse(value) : fallback;
    } catch (_) {
        return fallback;
    }
};

export function initEstimateShow() {
    window.estimateShowComponent = function (el) {
        const initial = safeJsonParse(el?.dataset.estimateShowInitial, DEFAULT_STATE);
        const setupAreas = window.__estimateSetup?.areas || [];
        const fallbackAreaId = setupAreas.length ? String(setupAreas[0].id) : '';
        const resolvedInitialArea =
            initial?.activeArea && initial.activeArea !== 'all'
                ? String(initial.activeArea)
                : fallbackAreaId;
        return {
            tab: initial?.tab || 'work',
            activeArea: initial?.activeArea || 'all',
            showAddItems: !!initial?.showAddItems,
            addItemsTab: initial?.addItemsTab || 'materials',
            addItemsArea: resolvedInitialArea,
            defaultAreaId: fallbackAreaId,
            init() {
                this.syncAddItemsAreaInputs();
            },
            openAddItems(tab = 'labor', areaId = undefined) {
                if (areaId !== undefined) {
                    this.setAddItemsArea(areaId);
                }
                this.addItemsTab = tab;
                this.showAddItems = true;
                window.dispatchEvent(new CustomEvent('set-calc-tab', { detail: tab }));
            },
            closeAddItems() {
                this.showAddItems = false;
            },
            handleAddItemsOpen(event) {
                const detail = event?.detail || {};
                this.openAddItems(detail.tab || 'labor', detail.areaId ?? '');
            },
            setAddItemsArea(areaId) {
                this.addItemsArea = this.normalizeAreaId(areaId);
                this.syncAddItemsAreaInputs();
            },
            normalizeAreaId(areaId) {
                if (areaId === null || areaId === undefined || areaId === '' || areaId === 'all') {
                    return this.defaultAreaId;
                }
                return String(areaId);
            },
            syncAddItemsAreaInputs() {
                const value = this.addItemsArea || this.defaultAreaId || '';
                document.querySelectorAll('[data-role="add-items-area-id"]').forEach((input) => {
                    input.value = value;
                });
            },
        };
    };

    const attach = () => {
        const root = document.querySelector('[data-estimate-show-root]');
        if (!root || root.__estimateShowController) {
            return;
        }
        root.__estimateShowController = new EstimateShowController(root);
        root.__estimateShowController.init();
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', attach, { once: true });
    } else {
        attach();
    }
}

class EstimateShowController {
    constructor(root) {
        this.root = root;
        this.overlay = document.getElementById('pageLoadingOverlay');
        this.csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        this.setup = window.__estimateSetup || {};
        this.calcDrawerApi = null;
        this.highlightItemId = root.dataset.highlightItem || '';
        this.areaReorderUrl = window.__estimateAreaReorderUrl || '';
        this.itemsBaseUrl = window.__estimateItemsBaseUrl || '';
    }

    init() {
        this.initCalcDrawer();
        this.initRefreshButton();
        this.wireAreaOrdering();
        this.wireWorkAreaForm();
        this.wireSaveAllButton();
        this.wireCatalogForms();
        this.wireDrawerAddButtons();
        this.restoreDrawerState();
        this.highlightRecentItem();
        this.exposeSummaryHelpers();
    }

    initCalcDrawer() {
        const drawer = document.getElementById('calcDrawer');
        if (!drawer || !this.setup.estimateId) {
            return;
        }

        try {
            this.calcDrawerApi = initEstimateCalculatorDrawer({
                estimateId: this.setup.estimateId,
                areas: this.setup.areas || [],
            });
        } catch (error) {
            console.error('Estimate calculator init error', error);
        }

        const overlayEl = document.getElementById('calcDrawerOverlay');
        const closeBtn = document.getElementById('calcDrawerCloseBtn');
        const openBtn = document.getElementById('openCalcDrawerBtn');
        const tabCreateBtn = document.getElementById('calcTabCreateBtn');
        const tabTemplatesBtn = document.getElementById('calcTabTemplatesBtn');
        const createPane = document.getElementById('calcCreatePane');
        const templatesPane = document.getElementById('calcTemplatesPane');
        const typeSelectCreate = document.getElementById('calcTypeSelect');
        const typeSelectTpl = document.getElementById('calcTypeSelectTpl');
        const openTemplateModeLink = document.getElementById('openTemplateModeLink');
        const tplRefreshBtn = document.getElementById('calcTplRefresh');

        const activateCreate = () => {
            if (createPane) createPane.style.display = '';
            if (templatesPane) templatesPane.style.display = 'none';
            tabCreateBtn?.classList.add('bg-brand-50', 'text-brand-700');
            tabTemplatesBtn?.classList.remove('bg-brand-50', 'text-brand-700');
        };

        const activateTemplates = () => {
            if (createPane) createPane.style.display = 'none';
            if (templatesPane) templatesPane.style.display = '';
            tabTemplatesBtn?.classList.add('bg-brand-50', 'text-brand-700');
            tabCreateBtn?.classList.remove('bg-brand-50', 'text-brand-700');
            this.calcDrawerApi?.fetchTemplates?.();
        };

        const showDrawer = (tab = 'templates') => {
            drawer.style.display = 'block';
            if (tab === 'create') {
                activateCreate();
                this.dispatchCalcTab('labor');
            } else {
                activateTemplates();
                this.dispatchCalcTab(tab);
            }
            this.syncTemplateLink(typeSelectCreate, openTemplateModeLink);
        };

        const hideDrawer = () => {
            drawer.style.display = 'none';
        };

        if (openBtn) openBtn.addEventListener('click', () => showDrawer('create'));
        if (overlayEl) overlayEl.addEventListener('click', hideDrawer);
        if (closeBtn) closeBtn.addEventListener('click', hideDrawer);
        if (tabCreateBtn) tabCreateBtn.addEventListener('click', activateCreate);
        if (tabTemplatesBtn) tabTemplatesBtn.addEventListener('click', activateTemplates);
        if (tplRefreshBtn) tplRefreshBtn.addEventListener('click', () => this.calcDrawerApi?.fetchTemplates?.());

        if (typeSelectCreate) {
            typeSelectCreate.addEventListener('change', () => {
                this.calcDrawerApi && (this.calcDrawerApi.state.calcType = typeSelectCreate.value || 'mulching');
                this.syncTemplateLink(typeSelectCreate, openTemplateModeLink);
            });
            this.syncTemplateLink(typeSelectCreate, openTemplateModeLink);
        }

        if (typeSelectTpl) {
            typeSelectTpl.addEventListener('change', () => {
                if (this.calcDrawerApi) {
                    this.calcDrawerApi.state.calcType = typeSelectTpl.value || 'mulching';
                }
                this.calcDrawerApi?.fetchTemplates?.();
            });
        }

        window.__showCalcDrawerPanel = (tab = 'labor') => {
            showDrawer('templates');
            this.dispatchCalcTab(tab);
        };
    }

    dispatchCalcTab(tab) {
        window.dispatchEvent(new CustomEvent('set-calc-tab', { detail: tab }));
    }

    syncTemplateLink(selectEl, linkEl) {
        if (!linkEl) return;
        const type = selectEl?.value || 'mulching';
        const href = this.calcDrawerApi?.calcHref?.(type);
        if (href) {
            linkEl.href = href;
            linkEl.classList.remove('opacity-50', 'pointer-events-none');
            linkEl.setAttribute('aria-disabled', 'false');
        } else {
            linkEl.href = '#';
            linkEl.classList.add('opacity-50', 'pointer-events-none');
            linkEl.setAttribute('aria-disabled', 'true');
        }
    }

    initRefreshButton() {
        const refreshBtn = document.getElementById('estimateRefreshBtn');
        if (!refreshBtn) return;
        refreshBtn.addEventListener('click', () => this.autoRefresh());
    }

    autoRefresh(delay = 150) {
        this.showSpinner();
        setTimeout(() => window.location.reload(), delay);
    }

    showSpinner() {
        this.overlay?.classList.remove('hidden');
    }

    hideSpinner() {
        this.overlay?.classList.add('hidden');
    }

    wireAreaOrdering() {
        const container = document.getElementById('areasContainer');
        if (!container || !this.areaReorderUrl || !this.csrf) return;

        const readRows = () => Array.from(container.querySelectorAll('.work-area'));
        const getOrderFromRow = (row) => {
            const input = row.querySelector('input[name="sort_order"]');
            const value = input ? parseInt(input.value, 10) : NaN;
            return Number.isFinite(value) ? value : parseInt(row.dataset.sortOrder || '0', 10);
        };
        const applyDomOrder = () => {
            const rows = readRows().sort((a, b) => getOrderFromRow(a) - getOrderFromRow(b));
            rows.forEach((row) => container.appendChild(row));
        };
        const payload = () => readRows().map((row) => ({
            id: row.getAttribute('data-area-id'),
            sort_order: getOrderFromRow(row),
        }));

        const persist = async () => {
            try {
                await fetch(this.areaReorderUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrf,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ areas: payload() }),
                });
            } catch (_) {
                /* non-blocking */
            }
        };

        container.addEventListener('change', (event) => {
            if (event.target?.name !== 'sort_order') return;
            applyDomOrder();
            persist();
        });
    }

    wireWorkAreaForm() {
        const form = document.getElementById('addWorkAreaForm');
        if (!form || !this.csrf) return;

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-50');
            }

            try {
                const formData = new FormData(form);
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.csrf,
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                if (!response.ok) {
                    throw new Error('Failed to create work area');
                }

                const json = await response.json();
                
                // Close modal
                window.dispatchEvent(new CustomEvent('close-modal', { detail: 'add-work-area' }));
                
                // Insert the new area HTML
                if (json.area_html) {
                    const container = document.getElementById('areasContainer');
                    const emptyMsg = document.getElementById('workAreasEmpty');
                    const wrapper = document.getElementById('workAreasListWrapper');
                    
                    if (container) {
                        // Create a temporary div to parse the HTML
                        const temp = document.createElement('div');
                        temp.innerHTML = json.area_html;
                        const newArea = temp.firstElementChild;
                        
                        if (newArea) {
                            container.appendChild(newArea);
                            
                            // Show wrapper, hide empty message
                            if (emptyMsg) emptyMsg.classList.add('hidden');
                            if (wrapper) wrapper.classList.remove('hidden');
                            
                            // Trigger exclusive open for new area
                            if (json.recent_area_id) {
                                setTimeout(() => {
                                    window.dispatchEvent(new CustomEvent('work-area-exclusive-open', {
                                        detail: { areaId: json.recent_area_id }
                                    }));
                                }, 100);
                            }
                            
                            // Scroll to new area
                            newArea.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    }
                }
                
                // Reset form
                form.reset();
                this.toast('Work area added', 'success');
                
            } catch (error) {
                console.error(error);
                this.toast('Failed to add work area', 'error');
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-50');
                }
            }
        });
    }

    wireSaveAllButton() {
        const saveAllBtn = document.getElementById('saveAllBtn');
        if (!saveAllBtn || !this.csrf) return;

        saveAllBtn.addEventListener('click', async () => {
            try {
                this.showSpinner();
                const forms = new Set();
                document.querySelectorAll('form[action*="/areas/"] input[name="_method"][value="PATCH"]').forEach((input) => forms.add(input.closest('form')));
                document.querySelectorAll('form[action*="/items/"] input[name="_method"][value="PATCH"]').forEach((input) => forms.add(input.closest('form')));
                for (const form of forms) {
                    if (!form) continue;
                    const action = form.getAttribute('action');
                    if (!action) continue;
                    const fd = new FormData(form);
                    await fetch(action, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': this.csrf, 'Accept': 'application/json' },
                        body: fd,
                    });
                }
                this.toast('All changes saved', 'success');
                this.autoRefresh(200);
            } catch (error) {
                console.error(error);
                this.hideSpinner();
                this.toast('Save failed', 'error');
            }
        });
    }

    wireCatalogForms() {
        const forms = ['#materialCatalogForm', '#laborCatalogForm', '#customItemForm']
            .map((selector) => document.querySelector(selector))
            .filter(Boolean);
        forms.forEach((form) => {
            this.setInitialFinancialState(form);
            form.querySelectorAll('input, select, textarea').forEach((el) => {
                el.addEventListener('input', () => this.handleFormChange(form, el));
                el.addEventListener('change', () => this.handleFormChange(form, el));
            });
            form.addEventListener('submit', (event) => {
                const submit = form.querySelector('button[type="submit"]');
                if (submit) {
                    submit.disabled = true;
                    submit.classList.add('opacity-50');
                }
                if (!form.dataset.allowAsync && !form.dataset.skipNative) {
                    return;
                }
                event.preventDefault();
            });
            this.updateFormState(form);
        });

        this.wireCatalogForm('#materialCatalogForm', '[data-role="material-select"]', '[data-role="material-unit"]', '[data-role="material-cost"]', '[data-role="material-tax"]');
        this.wireCatalogForm('#laborCatalogForm', '[data-role="labor-select"]', '[data-role="labor-unit"]', '[data-role="labor-cost"]');
    }

    wireCatalogForm(formSelector, selectSelector, unitSelector, costSelector, taxSelector) {
        const form = document.querySelector(formSelector);
        if (!form) return;
        const select = form.querySelector(selectSelector);
        const unitInput = unitSelector ? form.querySelector(unitSelector) : null;
        const costInput = costSelector ? form.querySelector(costSelector) : null;
        const taxInput = taxSelector ? form.querySelector(taxSelector) : null;

        if (select) {
            select.addEventListener('change', () => {
                const option = select.options[select.selectedIndex];
                if (!option) return;
                if (unitInput) unitInput.value = option.dataset.unit || '';
                if (costInput) {
                    costInput.value = option.dataset.cost || 0;
                    const unitPriceInput = form.querySelector('[data-role="unit-price"]');
                    if (unitPriceInput && unitPriceInput.dataset.manualOverride !== '1') {
                        unitPriceInput.value = option.dataset.cost || 0;
                    }
                }
                if (taxInput) taxInput.value = option.dataset.tax || 0;
                this.updateFormState(form);
            });
        }

        const filterInput = form.querySelector('[data-role="filter"]');
        if (filterInput && select) {
            filterInput.addEventListener('input', () => {
                const query = filterInput.value.toLowerCase().trim();
                Array.from(select.options).forEach((opt, idx) => {
                    if (idx === 0) return;
                    const match = (opt.textContent || '').toLowerCase().includes(query);
                    opt.hidden = !!query && !match;
                });
            });
        }
    }

    setInitialFinancialState(form) {
        const unitPriceInput = form.querySelector('[data-role="unit-price"]');
        if (unitPriceInput && !unitPriceInput.dataset.manualOverride) {
            unitPriceInput.dataset.manualOverride = '0';
        }
    }

    handleFormChange(form, el) {
        if (el.matches('[data-role="unit-price"]')) {
            el.dataset.manualOverride = '1';
        }
        if (el.matches('[data-role="margin-percent"]')) {
            const priceInput = form.querySelector('[data-role="unit-price"]');
            if (priceInput) priceInput.dataset.manualOverride = '0';
        }
        this.updateFormState(form);
    }

    updateFormState(form) {
        const quantityInput = form.querySelector('input[name="quantity"]');
        const unitPriceInput = form.querySelector('[data-role="unit-price"]');
        const marginPercentInput = form.querySelector('[data-role="margin-percent"]');
        const marginRateInput = form.querySelector('[data-role="margin-rate"]');
        const catalogSelect = form.querySelector('select[name="catalog_id"]');
        const submitBtn = form.querySelector('button[type="submit"]');
        const previewEl = form.querySelector('[data-role="preview-total"]');
        const costInput = form.querySelector('[data-role="material-cost"], [data-role="labor-cost"], input[name="unit_cost"]');

        if (marginPercentInput && marginRateInput) {
            const marginPercent = this.parseNumber(marginPercentInput.value, 0);
            const clamped = Math.max(-99, Math.min(99, marginPercent));
            marginRateInput.value = (clamped / 100).toFixed(4);
            if (costInput && unitPriceInput && unitPriceInput.dataset.manualOverride !== '1') {
                const cost = this.parseNumber(costInput.value, 0);
                const rate = clamped / 100;
                const price = rate >= 1 ? cost : cost / (1 - rate);
                unitPriceInput.value = isFinite(price) ? price.toFixed(2) : cost.toFixed(2);
            }
        }

        const qty = this.parseNumber(quantityInput?.value, 0);
        const price = this.parseNumber(unitPriceInput?.value, 0);
        if (previewEl) {
            previewEl.textContent = `Line total: $${(qty * price).toFixed(2)}`;
        }
        if (submitBtn) {
            const canSubmit = (!catalogSelect || !!catalogSelect.value) && qty > 0;
            submitBtn.disabled = !canSubmit;
        }
    }

    wireDrawerAddButtons() {
        // Use event delegation to handle dynamically added buttons
        if (!this.itemsBaseUrl || !this.csrf) return;
        
        document.addEventListener('click', async (event) => {
            const btn = event.target.closest('[data-action="drawer-add"]');
            if (!btn) return;
            
            if (btn.disabled) return;
            const catalogId = btn.dataset.catalogId;
            const itemType = btn.dataset.itemType || 'labor';
            if (!catalogId) return;
            
            btn.disabled = true;
            btn.classList.add('opacity-50');
            
            try {
                // Get area_id from the add-items panel or fallback to first area
                const areaInput = document.querySelector('[data-role="add-items-area-id"]');
                const areaId = areaInput?.value || this.setup.areas?.[0]?.id || null;
                
                const formData = new FormData();
                formData.append('item_type', itemType);
                formData.append('catalog_type', itemType);
                formData.append('catalog_id', catalogId);
                formData.append('quantity', '1');
                formData.append('source', 'drawer');
                if (areaId) {
                    formData.append('area_id', areaId);
                }
                
                const response = await fetch(this.itemsBaseUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.csrf,
                        'Accept': 'application/json',
                    },
                    body: formData,
                });
                const json = await response.json().catch(() => ({}));
                if (!response.ok) throw json;
                sessionStorage.setItem(STORAGE_KEY, JSON.stringify({
                    tab: itemType,
                    highlightItemId: json?.item?.id || null,
                }));
                this.showSpinner();
                window.location.reload();
            } catch (error) {
                console.error(error);
                this.toast('Unable to add item. Please try again.', 'error');
            } finally {
                btn.disabled = false;
                btn.classList.remove('opacity-50');
            }
        });
    }

    restoreDrawerState() {
        const stored = sessionStorage.getItem(STORAGE_KEY);
        if (!stored) return;
        try {
            const data = JSON.parse(stored) || {};
            sessionStorage.removeItem(STORAGE_KEY);
            if (data.tab && window.__showCalcDrawerPanel) {
                window.__showCalcDrawerPanel(data.tab);
                window.dispatchEvent(new CustomEvent('set-calc-tab', { detail: data.tab }));
            }
            if (data.highlightItemId) {
                requestAnimationFrame(() => this.highlightRow(data.highlightItemId));
            }
        } catch (_) {
            sessionStorage.removeItem(STORAGE_KEY);
        }
    }

    highlightRecentItem() {
        if (!this.highlightItemId) return;
        requestAnimationFrame(() => this.highlightRow(this.highlightItemId));
    }

    highlightRow(itemId) {
        const row = document.querySelector(`[data-item-id="${itemId}"]`);
        if (!row) return;
        const areaId = row.dataset.areaId;
        if (areaId) {
            window.dispatchEvent(new CustomEvent('force-open-area', { detail: { areaId: Number(areaId) } }));
        }
        row.classList.add('estimate-highlight');
        row.scrollIntoView({ behavior: 'smooth', block: 'center' });
        setTimeout(() => row.classList.remove('estimate-highlight'), 2200);
    }

    exposeSummaryHelpers() {
        window.updateSummary = (totals) => this.updateSummary(totals);
    }

    updateSummary(totals) {
        if (!totals) return;
        const materialRevenue = this.parseNumber(totals.material_subtotal);
        const materialCost = this.parseNumber(totals.material_cost_total);
        const materialProfit = this.parseNumber(totals.material_profit_total);
        const laborRevenue = this.parseNumber(totals.labor_subtotal);
        const laborCost = this.parseNumber(totals.labor_cost_total);
        const laborProfit = this.parseNumber(totals.labor_profit_total);
        const feeRevenue = this.parseNumber(totals.fee_total);
        const feeCost = this.parseNumber(totals.fee_cost_total);
        const feeProfit = this.parseNumber(totals.fee_profit_total);
        const discountRevenue = this.parseNumber(totals.discount_total);
        const discountCost = this.parseNumber(totals.discount_cost_total);
        const discountProfit = this.parseNumber(totals.discount_profit_total);
        const revenue = this.parseNumber(totals.revenue_total);
        const costs = this.parseNumber(totals.cost_total);
        const grossProfit = this.parseNumber(totals.profit_total);
        const netProfit = this.parseNumber(totals.net_profit_total);
        const grossMargin = this.parseNumber(totals.profit_margin);
        const netMargin = this.parseNumber(totals.net_margin);
        const taxTotal = this.parseNumber(totals.tax_total);
        const grandTotal = this.parseNumber(totals.grand_total);

        this.setText('summary-material', this.formatMoney(materialRevenue));
        this.setText('summary-material-cost', this.formatMoney(materialCost));
        this.setText('summary-labor', this.formatMoney(laborRevenue));
        this.setText('summary-labor-cost', this.formatMoney(laborCost));
        this.setText('summary-fees', this.formatMoney(feeRevenue - discountRevenue));
        this.setText('summary-tax', this.formatMoney(taxTotal));
        this.setText('summary-revenue', this.formatMoney(revenue));
        this.setText('summary-cost', this.formatMoney(costs));
        this.setText('summary-profit', this.formatMoney(grossProfit));
        this.setText('summary-net', this.formatMoney(netProfit));
        this.setText('summary-profit-margin', grossMargin.toFixed(2));
        this.setText('summary-net-margin', netMargin.toFixed(2));
        this.setText('summary-grand', this.formatMoney(grandTotal));

        this.setText('work-total-cost', this.formatMoney(costs));
        this.setText('work-subtotal', this.formatMoney(revenue));
        this.setText('work-total-price', this.formatMoney(grandTotal));
        this.setText('work-net-profit', this.formatMoney(netProfit));
        this.setText('work-net-margin', netMargin.toFixed(2));
        this.setText('work-gross-profit', this.formatMoney(grossProfit));
        this.setText('work-gross-margin', grossMargin.toFixed(2));
        this.setText('work-breakeven', this.formatMoney(Math.max(0, grandTotal - netProfit)));

        const costPercent = revenue > 0 ? this.clamp((costs / revenue) * 100) : 0;
        const grossPercent = revenue > 0 ? this.clamp((grossProfit / revenue) * 100) : 0;
        const netPercent = revenue > 0 ? this.clamp((netProfit / revenue) * 100) : 0;
        this.setText('snapshot-revenue', this.formatMoney(revenue));
        this.setText('snapshot-costs', this.formatMoney(costs));
        this.setText('snapshot-cost-percent', costPercent.toFixed(1));
        this.setText('snapshot-cost-percent-inline', costPercent.toFixed(1));
        this.setText('snapshot-gross-profit', this.formatMoney(grossProfit));
        this.setText('snapshot-net-profit', this.formatMoney(netProfit));
        this.setText('snapshot-gross-percent', `${grossMargin.toFixed(2)}% margin`);
        this.setText('snapshot-net-percent', `${netMargin.toFixed(2)}% margin`);
        this.setText('snapshot-gross-margin', `${grossMargin.toFixed(2)}%`);
        this.setText('snapshot-net-margin', `${netMargin.toFixed(2)}%`);
        this.setText('snapshot-gross-percent-inline', grossPercent.toFixed(1));
        this.setText('snapshot-net-percent-inline', netPercent.toFixed(1));
        this.setBarWidth('snapshot-cost-bar', costPercent);
        this.setBarWidth('snapshot-gross-bar', grossPercent);
        this.setBarWidth('snapshot-net-bar', netPercent);

        [
            { key: 'material', revenue: materialRevenue, cost: materialCost, profit: materialProfit },
            { key: 'labor', revenue: laborRevenue, cost: laborCost, profit: laborProfit },
            { key: 'fee', revenue: feeRevenue, cost: feeCost, profit: feeProfit },
            { key: 'discount', revenue: discountRevenue, cost: discountCost, profit: discountProfit },
        ].forEach((entry) => {
            const margin = entry.revenue !== 0 ? ((entry.profit / Math.abs(entry.revenue)) * 100) : 0;
            this.setText(`breakdown-${entry.key}-revenue`, this.formatMoney(entry.revenue));
            this.setText(`breakdown-${entry.key}-cost`, this.formatMoney(entry.cost));
            this.setText(`breakdown-${entry.key}-profit`, this.formatMoney(entry.profit));
            this.setText(`breakdown-${entry.key}-margin`, margin.toFixed(1));
        });

        this.computeManHours();
    }

    computeManHours() {
        const rows = document.querySelectorAll('tr[data-item-id]');
        let hours = 0;
        rows.forEach((row) => {
            if ((row.dataset.itemType || '').toLowerCase() === 'labor') {
                hours += this.parseNumber(row.dataset.quantity, 0);
            }
        });
        this.setText('work-man-hours', hours.toFixed(2));
    }

    parseNumber(value, fallback = 0) {
        if (value === null || value === undefined) return fallback;
        const num = Number(String(value).replace(/[^0-9.\-]/g, ''));
        return Number.isFinite(num) ? num : fallback;
    }

    formatMoney(value) {
        return `$${this.parseNumber(value, 0).toFixed(2)}`;
    }

    setText(target, value) {
        const element = typeof target === 'string' ? document.getElementById(target) : target;
        if (element) element.textContent = value;
    }

    setBarWidth(id, value) {
        const el = document.getElementById(id);
        if (el) el.style.width = `${this.clamp(value, 0, 100)}%`;
    }

    clamp(value, min = 0, max = 100) {
        return Math.min(Math.max(value, min), max);
    }

    toast(message, type = 'success') {
        if (window.showToast) {
            window.showToast(message, type);
        }
    }
}

// Alpine.js component for line item reactive calculations
window.lineItemCalculator = function(config) {
    return {
        itemType: config.itemType || 'material',
        unitCost: config.unitCost || 0,
        unitPrice: config.unitPrice || 0,
        quantity: config.quantity || 0,
        overheadRate: config.overheadRate || 0,
        taxRate: config.taxRate || 0,
        breakeven: config.breakeven || 0,
        profitPercent: 0,
        totalProfit: 0,
        catalogType: null,
        catalogId: null,
        
        init() {
            // Store catalog info from the row's data attributes
            const row = this.$el;
            this.catalogType = row.dataset.catalogType || null;
            this.catalogId = row.dataset.catalogId || null;
            
            // Log if catalog values are suspicious
            if (this.catalogType === 'null' || this.catalogType === 'undefined') {
                console.warn('Suspicious catalogType detected:', this.catalogType);
                this.catalogType = null;
            }
            if (this.catalogId === 'null' || this.catalogId === 'undefined' || this.catalogId === '0') {
                console.warn('Suspicious catalogId detected:', this.catalogId);
                this.catalogId = null;
            }
            
            console.log('Line item initialized:', {
                itemId: row.dataset.itemId,
                catalogType: this.catalogType,
                catalogId: this.catalogId,
                itemType: this.itemType,
                rawCatalogType: row.dataset.catalogType,
                rawCatalogId: row.dataset.catalogId
            });
            
            // Calculate initial values
            this.calculateBreakeven();
            this.calculateProfitFromPrice();
        },
        
        calculateBreakeven() {
            if (this.itemType === 'labor') {
                this.breakeven = this.unitCost + this.overheadRate;
            } else if (this.itemType === 'material' && this.taxRate > 0) {
                this.breakeven = this.unitCost * (1 + this.taxRate);
            } else {
                this.breakeven = this.unitCost;
            }
        },
        
        calculateProfitFromPrice() {
            if (this.unitPrice <= 0) {
                this.profitPercent = 0;
                return;
            }
            const profit = ((this.unitPrice - this.breakeven) / this.unitPrice) * 100;
            // Round to 1 decimal place
            this.profitPercent = Math.round(profit * 10) / 10;
        },

        calculatePriceFromProfit() {
            // Ensure profit percent is within valid range and rounded to 1 decimal
            let pp = Math.round(this.profitPercent * 10) / 10;
            if (pp >= 100) pp = 99.9;
            if (pp <= -100) pp = -99.9;
            this.profitPercent = pp;
            
            const marginRate = pp / 100;
            if (marginRate < 1) {
                this.unitPrice = this.breakeven / (1 - marginRate);
            } else {
                this.unitPrice = this.breakeven;
            }
        },

        recalculateFromCost() {
            this.breakeven = this.calculateBreakeven();
            this.calculateProfitFromPrice();
        },

        recalculateFromPrice() {
            this.breakeven = this.calculateBreakeven();
            this.calculateProfitFromPrice();
            // Auto-focus the input when entering edit mode
            this.$nextTick(() => {
                const priceInput = this.$refs.priceInput;
                if (priceInput) {
                    priceInput.focus();
                    priceInput.select();
                }
            });
        },

        recalculateFromProfit() {
            this.profitPercent = Math.round(this.profitPercent * 10) / 10;
            this.breakeven = this.calculateBreakeven();
            this.calculatePriceFromProfit();
            // Auto-focus the input when entering edit mode
            this.$nextTick(() => {
                const profitInput = this.$refs.profitInput;
                if (profitInput) {
                    profitInput.focus();
                    profitInput.select();
                }
            });
        },
        
        async resetToCatalogDefaults() {
            console.log('Reset button clicked. Checking catalog linkage:', {
                catalogType: this.catalogType,
                catalogId: this.catalogId,
                rawType: this.$el.dataset.catalogType,
                rawId: this.$el.dataset.catalogId
            });
            
            // Check if values are empty strings ("" or "null" as string)
            const hasValidCatalogType = this.catalogType && this.catalogType !== '' && this.catalogType !== 'null';
            const hasValidCatalogId = this.catalogId && this.catalogId !== '' && this.catalogId !== 'null' && this.catalogId !== '0';
            
            if (!hasValidCatalogType || !hasValidCatalogId) {
                const itemName = this.$el.querySelector('input[name="name"]')?.value || 'This item';
                alert(`${itemName} is not linked to a catalog item. Cannot reset.\n\nCatalog Type: ${this.catalogType || '(none)'}\nCatalog ID: ${this.catalogId || '(none)'}\n\nTo link this item to the catalog, delete it and re-add from the Add Items panel.`);
                return;
            }
            
            // Show confirmation
            if (!confirm(`Reset this ${this.catalogType} item to catalog defaults?`)) {
                return;
            }
            
            try {
                // Fetch the catalog item defaults - use absolute URL to avoid routing issues
                const baseUrl = window.location.origin;
                const url = `${baseUrl}/api/catalog/${this.catalogType}/${this.catalogId}`;
                console.log('Fetching catalog defaults from:', url);
                
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                console.log('Response status:', response.status);
                
                if (!response.ok) {
                    let errorMessage = `Server error (${response.status})`;
                    
                    // Clone the response so we can read it multiple times if needed
                    const responseClone = response.clone();
                    
                    try {
                        const errorData = await response.json();
                        console.error('API Error:', errorData);
                        
                        if (response.status === 404) {
                            // Show detailed message from API
                            errorMessage = errorData.message || `The catalog ${this.catalogType} item (ID: ${this.catalogId}) no longer exists.\n\nIt may have been deleted.`;
                            
                            // Add debug info if available
                            if (errorData.debug) {
                                console.log('Debug info:', errorData.debug);
                                errorMessage += `\n\nPlease check:\n• Database table: ${errorData.debug.table || 'unknown'}\n• Item ID: ${errorData.debug.requested_id || this.catalogId}\n\nYou may need to delete this line item and re-add it from the catalog.`;
                            }
                        } else {
                            errorMessage = errorData.message || errorMessage;
                        }
                    } catch (parseError) {
                        // If JSON parsing fails, try to get text from the clone
                        try {
                            const errorText = await responseClone.text();
                            console.error('API Error (text):', errorText);
                            errorMessage += '. Check console for details.';
                        } catch (textError) {
                            console.error('Could not read error response:', textError);
                        }
                    }
                    throw new Error(errorMessage);
                }
                
                const data = await response.json();
                console.log('Catalog data received:', data);
                
                // Update the values
                if (this.catalogType === 'labor') {
                    this.unitCost = parseFloat(data.unit_cost || 0);
                    this.unitPrice = parseFloat(data.unit_price || data.unit_cost || 0);
                } else if (this.catalogType === 'material') {
                    this.unitCost = parseFloat(data.unit_cost || 0);
                    this.unitPrice = parseFloat(data.unit_price || data.unit_cost || 0);
                }
                
                // Recalculate
                this.recalculateFromCost();
                
                alert(`✓ Reset to catalog defaults:\nCost: $${this.unitCost.toFixed(2)}\nPrice: $${this.unitPrice.toFixed(2)}`);
            } catch (error) {
                console.error('Reset error:', error);
                alert(error.message);
            }
        },
        
        showToast(message, type) {
            if (window.showToast) {
                window.showToast(message, type);
            } else {
                alert(message);
            }
        }
    };
};

initEstimateShow();

export default initEstimateShow;
