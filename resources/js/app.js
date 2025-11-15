import './bootstrap';
import { initEstimateCalculatorDrawer } from './estimate-calculator';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Global Google Places helpers (no loader)
(function(){
  window.initPlacesAutocomplete = function(inputEl, options){
    try {
      if (!inputEl || !window.google?.maps?.places) return null;
      if (inputEl.dataset.placesBound === '1') return null; // already bound
      const countries = options?.countries || (window.PLACES_COUNTRIES || ['us']);
      const restrict = Array.isArray(countries) ? countries : String(countries).split(',');
      const ac = new google.maps.places.Autocomplete(inputEl, {
        fields: ['address_components','formatted_address'],
        types: ['address'],
        componentRestrictions: { country: restrict },
      });
      inputEl.dataset.placesBound = '1';
      return ac;
    } catch(e) {
      console.warn('Places init failed', e);
      return null;
    }
  };

  let placesImportPromise = null;

  function ensurePlacesLibrary(){
    if (placesImportPromise) return placesImportPromise;
    const maxChecks = 50;
    let attempts = 0;
    placesImportPromise = new Promise((resolve, reject) => {
      const check = () => {
        const mapsObj = window.google?.maps;
        if (mapsObj?.importLibrary) {
          mapsObj.importLibrary('places').then(() => resolve(true)).catch(reject);
          return;
        }
        if (mapsObj?.places) {
          resolve(true);
          return;
        }
        if (attempts >= maxChecks) {
          reject(new Error('Google Maps Places library not available'));
          return;
        }
        attempts += 1;
        setTimeout(check, 100);
      };
      check();
    }).catch((err) => {
      placesImportPromise = null;
      throw err;
    });
    return placesImportPromise;
  }

  function fillFromAddressComponents(place, targets, options = {}){
    const { input, line2 } = targets;
    try {
      // New Places uses camelCase names
      const comps = (place.addressComponents || place.address_components || []);
      const arr = Array.isArray(comps) ? comps : (comps.toArray?.() || []);
      const byType = (type) => arr.find(c => (c.types || c.type || []).includes ? (c.types || []).includes(type) : (c.types || []).indexOf(type) >= 0) || {};
      const getLong = (type) => (byType(type).longText || byType(type).long_name || '') || '';
      const getShort = (type) => (byType(type).shortText || byType(type).short_name || '') || '';

      if (input && place.formattedAddress) input.value = place.formattedAddress;
      if (input && place.formatted_address && !place.formattedAddress) input.value = place.formatted_address;

      const cityEl = document.getElementById('city');
      const stateEl = document.getElementById('state');
      const zipEl = document.getElementById('postal_code');

      const cityVal = getLong('locality') || getLong('sublocality') || getLong('postal_town');
      const stateVal = getShort('administrative_area_level_1') || getLong('administrative_area_level_1');
      const zipVal = getLong('postal_code');

      if (cityEl) cityEl.value = cityVal || cityEl.value;
      if (stateEl) stateEl.value = stateVal || stateEl.value;
      if (zipEl) zipEl.value = zipVal || zipEl.value;

      if (line2 && !line2.value) line2.value = getLong('subpremise') || '';
    } catch(e) { /* ignore */ }
  }

  function attachClassicAutocomplete(input, extra){
    const ac = window.initPlacesAutocomplete(input);
    if (!ac) return;
    ac.addListener('place_changed', () => {
      const place = ac.getPlace();
      if (!place) return;
      fillFromAddressComponents(place, extra);
    });
  }

  function attachElementAutocomplete(input, extra){
    try {
      if (input.dataset.placesElementBound === '1') return;
      const parent = input.parentNode;
      const wrapper = document.createElement('div');
      wrapper.className = 'mb-2';
      const el = new google.maps.places.PlaceAutocompleteElement();
      el.className = 'w-full';
      el.setAttribute('placeholder', input.getAttribute('placeholder') || 'Start typing an address...');
      const countries = (window.PLACES_COUNTRIES || ['us']);
      el.componentRestrictions = { country: countries };
      parent.insertBefore(wrapper, input);
      wrapper.appendChild(el);
      // Hide original input, keep for form submission
      input.type = 'hidden';
      input.dataset.placesElementBound = '1';
      el.addEventListener('gmp-placeselect', async (e) => {
        const place = e.detail.place;
        if (!place) return;
        try {
          await place.fetchFields({ fields: ['addressComponents','formattedAddress'] });
        } catch(_) {}
        fillFromAddressComponents(place, extra);
        // Also set the original input to formatted address text
        if (input && (place.formattedAddress || place.formatted_address)) {
          input.value = place.formattedAddress || place.formatted_address;
        }
      });
    } catch(e) { /* ignore */ }
  }

  function attachIfPresent(){
    if (!window.google?.maps?.places) return; // wait for callback
    const hasContact = document.getElementById('address') && document.getElementById('city') && document.getElementById('state') && document.getElementById('postal_code');
    const hasProperty = document.getElementById('address_line1') && document.getElementById('city') && document.getElementById('state') && document.getElementById('postal_code');
    if (!hasContact && !hasProperty) return;

    const supportsNew = typeof google.maps.places.PlaceAutocompleteElement === 'function';

    if (hasContact) {
      const input = document.getElementById('address');
      const extra = { input };
      if (supportsNew) attachElementAutocomplete(input, extra); else attachClassicAutocomplete(input, extra);
    }

    if (hasProperty) {
      const input = document.getElementById('address_line1');
      const line2 = document.getElementById('address_line2');
      const extra = { input, line2 };
      if (supportsNew) attachElementAutocomplete(input, extra); else attachClassicAutocomplete(input, extra);
    }
  }
  // Expose callback for script tag
  window.__initPlaces = function(){
    if (!window.__gmapsLoader && !window.google?.maps?.places && !window.google?.maps?.importLibrary) return;
    ensurePlacesLibrary()
      .then(() => { try { attachIfPresent(); } catch(e) { console.error(e); } })
      .catch((err) => console.error('Places loader error', err));
  };

  // If maps already present (cached), attach on DOM ready
  window.addEventListener('DOMContentLoaded', () => { if (window.__initPlaces) window.__initPlaces(); });
})();

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
