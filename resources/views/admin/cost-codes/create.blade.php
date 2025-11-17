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
        <div class="flex gap-2">
          <input type="text" id="qboItemSearchNew" class="form-input w-full" placeholder="Search QBO service items…" />
          <button type="button" id="qboItemClearNew" class="px-3 py-2 border rounded">Clear</button>
        </div>
        <div id="qboItemResultsNew" class="mt-2 max-h-48 overflow-auto border rounded hidden"></div>
        <input type="hidden" name="qbo_item_id" id="qbo_item_id_new" value="{{ old('qbo_item_id') }}" />
        <input type="hidden" name="qbo_item_name" id="qbo_item_name_new" value="{{ old('qbo_item_name') }}" />
        <p class="text-xs text-gray-500 mt-1">Only QBO Service Items are shown. Your selection will be saved to this cost code.</p>
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
        const search = document.getElementById('qboItemSearchNew');
        const results = document.getElementById('qboItemResultsNew');
        const idInput = document.getElementById('qbo_item_id_new');
        const nameInput = document.getElementById('qbo_item_name_new');
        const selected = document.getElementById('qboItemSelectedNew');
        const clearBtn = document.getElementById('qboItemClearNew');
        if (!search || !results) return;

        let timer = null;
        function render(list){
          results.innerHTML = '';
          if (!list || !list.length) { results.classList.add('hidden'); return; }
          list.forEach(item => {
            const row = document.createElement('button');
            row.type = 'button';
            row.className = 'w-full text-left px-3 py-2 hover:bg-gray-50 border-b';
            row.textContent = item.full_name || item.name || '(unnamed)';
            row.addEventListener('click', ()=>{
              idInput.value = item.id || '';
              nameInput.value = item.full_name || item.name || '';
              results.classList.add('hidden');
              if (selected){ selected.classList.remove('hidden'); selected.querySelector('span').textContent = nameInput.value; }
            });
            results.appendChild(row);
          });
          results.classList.remove('hidden');
        }
        async function doSearch(q){
          if (!q || q.length < 2) { results.classList.add('hidden'); return; }
          try{
            const url = '{{ route('admin.qbo.items.search') }}' + '?q=' + encodeURIComponent(q);
            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
            const json = await res.json();
            render(json.items || []);
          }catch(err){ results.classList.add('hidden'); }
        }
        search.addEventListener('input', ()=>{
          clearTimeout(timer);
          timer = setTimeout(()=> doSearch(search.value.trim()), 250);
        });
        if (clearBtn){ clearBtn.addEventListener('click', ()=>{
          if (idInput) idInput.value = '';
          if (nameInput) nameInput.value = '';
          if (selected){ selected.classList.add('hidden'); selected.querySelector('span').textContent = ''; }
          search.value=''; results.classList.add('hidden');
        }); }
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
