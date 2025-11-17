@extends('layouts.sidebar')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
  <x-page-header title="Edit Cost Code" eyebrow="Settings & Configurations" />

  <form method="POST" action="{{ route('admin.cost-codes.update', $code) }}" class="bg-white rounded shadow p-4 space-y-4">
    @csrf @method('PUT')
    <div class="grid sm:grid-cols-2 gap-3">
      <div>
        <label class="block text-sm font-medium">Code</label>
        <input type="text" name="code" class="form-input w-full" value="{{ old('code', $code->code) }}" required />
      </div>
      <div>
        <label class="block text-sm font-medium">Name</label>
        <input type="text" name="name" class="form-input w-full" value="{{ old('name', $code->name) }}" required />
      </div>
    </div>

    <div class="grid gap-3">
      <div>
        <label class="block text-sm font-medium">QBO Service Item</label>
        <div class="flex gap-2 items-center">
          <select id="qboItemSelect" class="form-select w-full">
            <option value="">— Select a Service Item —</option>
          </select>
          <button type="button" id="qboItemClear" class="px-3 py-2 border rounded">Clear</button>
        </div>
        <input type="hidden" name="qbo_item_id" id="qbo_item_id" value="{{ old('qbo_item_id', $code->qbo_item_id) }}" />
        <input type="hidden" name="qbo_item_name" id="qbo_item_name" value="{{ old('qbo_item_name', $code->qbo_item_name) }}" />
        <p class="text-xs text-gray-500 mt-1">Only QBO Service Items are shown.</p>
        <div id="qboItemSelected" class="text-sm mt-2 {{ $code->qbo_item_name ? '' : 'hidden' }}">Selected: <span class="font-medium">{{ $code->qbo_item_name }}</span></div>
      </div>
    </div>

    @push('scripts')
    <script>
      (function(){
        const select = document.getElementById('qboItemSelect');
        const idInput = document.getElementById('qbo_item_id');
        const nameInput = document.getElementById('qbo_item_name');
        const selected = document.getElementById('qboItemSelected');
        const clearBtn = document.getElementById('qboItemClear');
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
          const opt = select.options[select.selectedIndex];
          const txt = opt?.text || '';
          idInput.value = select.value || '';
          nameInput.value = select.value ? (opt?.dataset.fullName || txt) : '';
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
              opt.text = (i.full_name || i.name || '');
              opt.dataset.fullName = i.full_name || i.name || '';
              select.appendChild(opt);
            });
            // Preselect existing mapping if present
            const existingId = idInput.value;
            const existingName = nameInput.value;
            if (existingId) {
              const match = Array.from(select.options).find(o => o.value === existingId);
              if (match) {
                match.selected = true;
              } else {
                const opt = document.createElement('option');
                opt.value = existingId;
                opt.text = (existingName || 'Item');
                opt.dataset.fullName = existingName || '';
                select.appendChild(opt);
                opt.selected = true;
              }
            }
            setSelectedDisplay();
          } catch(e) {
            // disable select on error
            select.disabled = true;
          }
        }

        select.addEventListener('change', onChange);
        if (clearBtn){ clearBtn.addEventListener('click', ()=>{ select.value=''; onChange(); }); }
        loadAll();
      })();
    </script>
    @endpush
    <div class="flex items-center gap-2">
      <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $code->is_active)) />
      <span class="text-sm">Active</span>
    </div>
    <div class="flex justify-end gap-2">
      <x-secondary-button as="a" href="{{ route('admin.cost-codes.index') }}">Cancel</x-secondary-button>
      <x-brand-button type="submit">Update</x-brand-button>
    </div>
  </form>
</div>
@endsection
