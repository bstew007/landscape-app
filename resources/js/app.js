import './bootstrap';
import { initEstimateCalculatorDrawer } from './estimate-calculator';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Initialize Estimate Calculator drawer once DOM is ready
window.addEventListener('DOMContentLoaded', () => {
  try {
    const setup = window.__estimateSetup || {};
    const { estimateId, areas } = setup;
    if (!estimateId) return; // Only initialize on estimate show page

    const api = initEstimateCalculatorDrawer({ estimateId, areas: areas || [] });

    // Elements
    const drawer = document.getElementById('calcDrawer');
    const overlay = document.getElementById('calcDrawerOverlay');
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

    // Helpers
    const showDrawer = () => { if (drawer) drawer.style.display = ''; };
    const hideDrawer = () => { if (drawer) drawer.style.display = 'none'; };

    const activateCreate = () => {
      if (createPane) createPane.style.display = '';
      if (templatesPane) templatesPane.style.display = 'none';
      if (tabCreateBtn) tabCreateBtn.classList.add('bg-brand-50','text-brand-700');
      if (tabTemplatesBtn) tabTemplatesBtn.classList.remove('bg-brand-50','text-brand-700');
    };
    const activateTemplates = () => {
      if (createPane) createPane.style.display = 'none';
      if (templatesPane) templatesPane.style.display = '';
      if (tabTemplatesBtn) tabTemplatesBtn.classList.add('bg-brand-50','text-brand-700');
      if (tabCreateBtn) tabCreateBtn.classList.remove('bg-brand-50','text-brand-700');
      api.fetchTemplates();
    };

    const syncOpenLink = () => {
      if (!openTemplateModeLink) return;
      const type = (typeSelectCreate && typeSelectCreate.value) || 'mulching';
      api.state.calcType = type;
      openTemplateModeLink.href = api.calcHref(type);
    };

    // Wire open/close
    if (openBtn) openBtn.addEventListener('click', () => { activateCreate(); showDrawer(); syncOpenLink(); });
    if (overlay) overlay.addEventListener('click', hideDrawer);
    if (closeBtn) closeBtn.addEventListener('click', hideDrawer);

    // Wire tabs
    if (tabCreateBtn) tabCreateBtn.addEventListener('click', activateCreate);
    if (tabTemplatesBtn) tabTemplatesBtn.addEventListener('click', activateTemplates);

    // Type selects
    if (typeSelectCreate) {
      api.state.calcType = typeSelectCreate.value || 'mulching';
      typeSelectCreate.addEventListener('change', () => {
        api.state.calcType = typeSelectCreate.value || 'mulching';
        syncOpenLink();
      });
      syncOpenLink();
    }

    if (typeSelectTpl) {
      typeSelectTpl.addEventListener('change', () => {
        api.state.calcType = typeSelectTpl.value || 'mulching';
        api.fetchTemplates();
      });
    }

    // Refresh button
    if (tplRefreshBtn) tplRefreshBtn.addEventListener('click', () => api.fetchTemplates());

    // Open link click -> navigate
    if (openTemplateModeLink) {
      openTemplateModeLink.addEventListener('click', (e) => {
        e.preventDefault();
        window.location.href = openTemplateModeLink.href;
      });
    }
  } catch (e) {
    // Fail silently to avoid blocking Alpine or the rest of the page
    console.error('Estimate calculator init error', e);
  }
});
