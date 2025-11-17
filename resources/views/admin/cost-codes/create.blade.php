@extends('layouts.sidebar')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
  <x-page-header title="New Cost Code" eyebrow="Settings & Configurations" />

  <form method="POST" action="{{ route('admin.cost-codes.store') }}" class="bg-white rounded shadow p-4 space-y-4" id="costCodeCreateForm">
    @csrf
    <div class="grid sm:grid-cols-2 gap-3">
      <div>
        <label class="block text-sm font-medium">Code</label>
        <input type="text" name="code" class="form-input w-full" required />
      </div>
      <div>
        <label class="block text-sm font-medium">Name</label>
        <input type="text" name="name" class="form-input w-full" required />
      </div>
    </div>

    <div class="grid gap-3">
      <div>
        <label class="block text-sm font-medium">QBO Service Item</label>
        <div class="flex gap-2 items-center">
          <select id="qboItemSelectNew" class="form-select w-full">
            <option value="">— Select a Service Item —</option>
          </select>
          <button type="button" id="qboItemClearNew" class="px-3 py-2 border rounded">Clear</button>
        </div>
        <input type="hidden" name="qbo_item_id" id="qbo_item_id_new" value="{{ old('qbo_item_id') }}" />
        <input type="hidden" name="qbo_item_name" id="qbo_item_name_new" value="{{ old('qbo_item_name') }}" />
        <p class="text-xs text-gray-500 mt-1">Only QBO Service Items are shown.</p>
        <div id="qboItemSelectedNew" class="text-sm mt-2 hidden">Selected: <span class="font-medium"></span></div>
      </div>
    </div>

    <div class="flex items-center gap-2">
      <input type="checkbox" name="is_active" value="1" checked />
      <span class="text-sm">Active</span>
    </div>

    @push('scripts')
    <script>
      (function(){
        const select = document.getElementById('qboItemSelectNew');
        const idInput = document.getElementById('qbo_item_id_new');
        const nameInput = document.getElementById('qbo_item_name_new');
        const selected = document.getElementById('qboItemSelectedNew');
        const clearBtn = document.getElementById('qboItemClearNew');
        if (!select) return;

        function setSelectedDisplay(){
          const txt = select.options[select.selectedIndex]?.text || '';
          if (select.value) {
            if (selected){ selected.classList.remove('hidden'); selected.querySelector('span').textContent = txt; }
          } else {
            if (selected){ selected.classList.add('hidden'); selected.querySelector('span').textContent = ''; }
          }
        }

        function onChange(){
          const txt = select.options[select.selectedIndex]?.text || '';
          idInput.value = select.value || '';
          nameInput.value = select.value ? txt : '';
          setSelectedDisplay();
        }

        async function loadAll(){
          try{
            const url = '{{ route('admin.qbo.items.search') }}' + '?limit=500';
            const res = await fetch(url, { headers: { 'Accept':'application/json' } });
            const json = await res.json();
            const items = json.items || [];
            // Clear existing (keep placeholder)
            select.options.length = 1;
            items.forEach(i => {
              const opt = document.createElement('option');
              opt.value = i.id || '';
              opt.text = i.full_name || i.name || '';
              select.appendChild(opt);
            });
            setSelectedDisplay();
          } catch(e) {
            select.disabled = true;
          }
        }

        select.addEventListener('change', onChange);
        if (clearBtn){ clearBtn.addEventListener('click', ()=>{ select.value=''; onChange(); }); }
        loadAll();
      })();
    </script>
    @endpush
    <div class="flex justify-end gap-2">
      <x-secondary-button as="a" href="{{ route('admin.cost-codes.index') }}">Cancel</x-secondary-button>
      <x-brand-button type="submit">Save</x-brand-button>
    </div>
  </form>
</div>
@endsection
