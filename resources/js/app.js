import './bootstrap';
import initEstimateShow from './estimate-show';

import Alpine from 'alpinejs';
import { salesEditor } from './budget/sales';
import { fieldLaborEditor } from './budget/fieldLabor';
import { materialsEditor } from './budget/materials';
import { subcontractingEditor } from './budget/subcontracting';
import { equipmentEditor } from './budget/equipment';
import { overheadEditor } from './budget/overhead';
import { ohRecoveryEditor } from './budget/ohRecovery';

window.Alpine = Alpine;
window.salesEditor = salesEditor;
window.fieldLaborEditor = fieldLaborEditor;
window.materialsEditor = materialsEditor;
window.subcontractingEditor = subcontractingEditor;
window.equipmentEditor = equipmentEditor;
window.overheadEditor = overheadEditor;
window.ohRecoveryEditor = ohRecoveryEditor;

initEstimateShow();

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

      // Allow alternate IDs via data-attributes on the original input for flexibility
      const inputId = input?.id || '';
      const cityEl = document.getElementById(input?.dataset.cityId || 'city');
      const stateEl = document.getElementById(input?.dataset.stateId || 'state');
      const zipEl = document.getElementById(input?.dataset.zipId || 'postal_code');

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
      // Match app font sizing
      el.style.width = '100%';
      el.style.minHeight = '48px';
      el.style.fontSize = '1rem'; // ~16px
      el.style.fontFamily = 'Figtree, ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", "Apple Color Emoji", "Segoe UI Emoji"';
      el.className = 'w-full';
      el.setAttribute('placeholder', input.getAttribute('placeholder') || 'Start typing an address...');
      const countries = (window.PLACES_COUNTRIES || ['us']);
      el.componentRestrictions = { country: countries };
      parent.insertBefore(wrapper, input);
      wrapper.appendChild(el);
      input.dataset.placesElementBound = '1';
      // Hide original input only if element is connected/rendered
      setTimeout(() => {
        if (el.isConnected) {
          input.type = 'hidden';
        }
      }, 0);
      el.addEventListener('gmp-placeselect', async (e) => {
        // New API provides placePrediction; convert to Place
        let place;
        try {
          const pp = e.detail?.placePrediction;
          if (pp && typeof pp.toPlace === 'function') {
            place = pp.toPlace();
          } else if (e.detail?.place) {
            place = e.detail.place;
          }
          if (!place) return;
          await place.fetchFields({ fields: ['addressComponents','formattedAddress'] });
        } catch(err) { console.warn('fetchFields failed', err); return; }
        fillFromAddressComponents(place, extra);
        if (input) {
          input.value = place.formattedAddress || place.formatted_address || input.value;
          input.dispatchEvent(new Event('input', { bubbles: true }));
          input.dispatchEvent(new Event('change', { bubbles: true }));
        }
      });
    } catch(e) { /* ignore */ }
  }

  function attachIfPresent(){
    // If using Extended Component Library place pickers, skip classic/new element init to avoid conflicts
    if (document.querySelector('gmpx-place-picker')) return;
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
  // Expose callback for script tag: simply attach now that Places is loaded via importLibrary in layout
  window.__initPlaces = function(){
    try { attachIfPresent(); } catch(e) { console.error(e); }
  };

  // If maps already present (hot reload/cached), attach on DOM ready
  window.addEventListener('DOMContentLoaded', () => {
    if (window.google?.maps?.places) {
      try { attachIfPresent(); } catch(e) { console.error(e); }
    }
  });
  window.__fillFromAddressComponents = fillFromAddressComponents;
})();

  // Wire Extended Component Library place pickers
  window.addEventListener('DOMContentLoaded', () => {
    try {
      const cp = document.getElementById('contact_place_picker');
      const addr = document.getElementById('address');
      if (cp && addr) {
        // On edit forms, show existing address; hide only when there's no current value
        try {
          if (!addr.value) {
            addr.type = 'hidden';
          } else {
            addr.readOnly = true;
          }
        } catch(_) {}
        cp.addEventListener('gmpx-placechange', async (ev) => {
          try {
            let place = ev?.detail?.place || ev?.target?.value?.place || ev?.target?.value || cp.value?.place || cp.value;
            if (place && typeof place.toPlace === 'function') {
              place = place.toPlace();
            }
            // fetch fields for UI kit Place objects
            if (place && typeof place.fetchFields === 'function') {
              await place.fetchFields({ fields: ['addressComponents','formattedAddress'] });
            }
            if (place) {
              window.__fillFromAddressComponents && window.__fillFromAddressComponents(place, { input: addr });
              if (addr && (place.formattedAddress || place.formatted_address)) {
                addr.value = place.formattedAddress || place.formatted_address;
                addr.dispatchEvent(new Event('input', { bubbles: true }));
                addr.dispatchEvent(new Event('change', { bubbles: true }));
              }
            }
          } catch(e) { console.warn('contact picker fill failed', e); }
        });
      }
      const pp = document.getElementById('property_place_picker');
      const line1 = document.getElementById('address_line1');
      const line2 = document.getElementById('address_line2');
      if (pp && line1) {
        // On edit forms, show existing address; hide only when there's no current value
        try {
          if (!line1.value) {
            line1.type = 'hidden';
          } else {
            line1.readOnly = true;
          }
        } catch(_) {}
        pp.addEventListener('gmpx-placechange', async (ev) => {
          try {
            let place = ev?.detail?.place || ev?.target?.value?.place || ev?.target?.value || pp.value?.place || pp.value;
            if (place && typeof place.toPlace === 'function') {
              place = place.toPlace();
            }
            if (place && typeof place.fetchFields === 'function') {
              await place.fetchFields({ fields: ['addressComponents','formattedAddress'] });
            }
            if (place) {
              window.__fillFromAddressComponents && window.__fillFromAddressComponents(place, { input: line1, line2 });
              if (line1 && (place.formattedAddress || place.formatted_address)) {
                line1.value = place.formattedAddress || place.formatted_address;
                line1.dispatchEvent(new Event('input', { bubbles: true }));
                line1.dispatchEvent(new Event('change', { bubbles: true }));
              }
            }
          } catch(e) { console.warn('property picker fill failed', e); }
        });
      }
    } catch(e) { /* ignore */ }
  });
