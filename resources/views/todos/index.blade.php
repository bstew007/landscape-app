@extends('layouts.sidebar')

@php
    use App\Models\Todo;
    $statusLabels = [
        'future' => 'Future',
        'pending' => 'Pending',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
    ];
    $statusColors = [
        'future' => 'border-violet-200 bg-violet-50',
        'pending' => 'border-amber-200 bg-amber-50',
        'in_progress' => 'border-blue-200 bg-blue-50',
        'completed' => 'border-emerald-200 bg-emerald-50',
    ];
    $priorityLabels = [
        'low' => 'Low',
        'normal' => 'Normal',
        'high' => 'High',
        'urgent' => 'Urgent',
    ];
@endphp

@section('content')
@php
    $listMode = $viewMode === 'list';
    $pageTodos = $listMode ? collect($todos->items()) : collect();
    $pageCount = $listMode ? $pageTodos->count() : $todos->flatten()->count();
    $highPriorityCount = $listMode
        ? $pageTodos->where('priority', 'high')->count()
        : ($todos->get('high')?->count() ?? 0);
    $urgentCount = $listMode
        ? $pageTodos->where('priority', 'urgent')->count()
        : ($todos->get('urgent')?->count() ?? 0);
    $clientCount = $listMode
        ? $pageTodos->pluck('client_id')->filter()->unique()->count()
        : $todos->flatten()->pluck('client_id')->filter()->unique()->count();
@endphp

<div class="space-y-8">
    <section class="rounded-[32px] bg-gradient-to-br from-brand-900 via-brand-800 to-brand-700 text-white p-6 sm:p-8 shadow-2xl border border-brand-800/40 relative overflow-hidden">
        <div class="flex flex-wrap items-start gap-6">
            <div class="space-y-3 max-w-2xl">
                <p class="text-xs uppercase tracking-[0.3em] text-brand-200/80">Operations</p>
                <h1 class="text-3xl sm:text-4xl font-semibold">To-Do Command Center</h1>
                <p class="text-sm text-brand-100/85">Track crews, client requests, and property tasks with filters, Kanban, and list views that mirror the rest of the hub.</p>
            </div>
            <div class="flex flex-wrap gap-3 ml-auto">
                <x-secondary-button as="a" href="{{ route('todos.index', array_merge(request()->except('view'), ['view' => 'kanban'])) }}" class="bg-white/10 text-white border-white/40 hover:bg-white/20">
                    Kanban
                </x-secondary-button>
                <x-secondary-button as="a" href="{{ route('todos.index', array_merge(request()->except('view'), ['view' => 'list'])) }}" class="bg-white/10 text-white border-white/40 hover:bg-white/20">
                    List
                </x-secondary-button>
                <x-brand-button href="{{ route('todos.create') }}" variant="muted">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                    Add To-Do
                </x-brand-button>
            </div>
        </div>
        <dl class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-8 text-sm text-brand-100">
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">On This Page</dt>
                <dd class="text-2xl font-semibold text-white mt-2">{{ number_format($pageCount) }}</dd>
            </div>
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">High Priority</dt>
                <dd class="text-2xl font-semibold text-white mt-2">{{ number_format($highPriorityCount) }}</dd>
            </div>
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">Urgent</dt>
                <dd class="text-2xl font-semibold text-white mt-2">{{ number_format($urgentCount) }}</dd>
            </div>
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">Clients</dt>
                <dd class="text-2xl font-semibold text-white mt-2">{{ number_format($clientCount) }}</dd>
            </div>
        </dl>
    </section>

    <section class="rounded-[32px] bg-white shadow-2xl border border-brand-100/60 overflow-hidden">
        <div class="p-5 sm:p-7 space-y-6">
            <form method="GET" action="{{ route('todos.index') }}" class="grid gap-4 md:grid-cols-4" id="todoFilters">
                <div>
                    <label class="block text-xs uppercase tracking-wide text-brand-400 mb-1">Priority</label>
                    <select name="priority" class="w-full rounded-full border border-brand-200 px-4 py-2 text-sm focus:ring-brand-500 focus:border-brand-500">
                        <option value="">All</option>
                        @foreach (Todo::PRIORITIES as $priority)
                            <option value="{{ $priority }}" @selected($selectedPriority === $priority)>{{ ucfirst($priority) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs uppercase tracking-wide text-brand-400 mb-1">Client</label>
                    <select name="client_id" class="w-full rounded-full border border-brand-200 px-4 py-2 text-sm focus:ring-brand-500 focus:border-brand-500">
                        <option value="">All</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}" @selected($selectedClientId == $client->id)>{{ $client->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-col gap-2">
                    <label class="block text-xs uppercase tracking-wide text-brand-400">Visibility</label>
                    <label class="inline-flex items-center gap-2 text-sm text-brand-600">
                        <input type="checkbox" name="hide_future" value="1" class="rounded border-brand-300 text-brand-600 focus:ring-brand-500" {{ request()->boolean('hide_future') ? 'checked' : '' }}>
                        Hide Future
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-brand-600">
                        <input type="checkbox" name="hide_completed" value="1" class="rounded border-brand-300 text-brand-600 focus:ring-brand-500" {{ request()->boolean('hide_completed') ? 'checked' : '' }}>
                        Hide Completed
                    </label>
                </div>
                <div class="flex items-end">
                    <x-brand-button type="submit" class="w-full justify-center rounded-full">Apply Filters</x-brand-button>
                </div>
            </form>
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const form = document.getElementById('todoFilters');
                    if (!form) return;
                    ['hide_future','hide_completed'].forEach(name => {
                        const el = form.querySelector(`input[name="${name}"]`);
                        if (el) el.addEventListener('change', () => form.submit());
                    });
                });
            </script>

            @if (session('success'))
                <div class="p-4 bg-emerald-50 text-emerald-900 rounded-2xl border border-emerald-200 text-sm">
                    {{ session('success') }}
                </div>
            @endif
        </div>

        <div class="border-t border-brand-100/60">
            @if ($viewMode === 'list')
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-brand-50/80 text-left text-[11px] uppercase tracking-wide text-brand-500">
                        <tr>
                            <th class="px-4 py-3">Title</th>
                            <th class="px-4 py-3">Client / Property</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Priority</th>
                            <th class="px-4 py-3">Due</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-brand-50 text-brand-900 text-sm">
                        @foreach ($todos as $todo)
                            <tr class="transition hover:bg-brand-50/70">
                                <td class="px-4 py-3 align-top">
                                    <p class="font-semibold">{{ $todo->title }}</p>
                                    <p class="text-xs text-brand-400">{{ \Illuminate\Support\Str::limit($todo->description, 80) }}</p>
                                </td>
                                <td class="px-4 py-3 align-top text-sm text-brand-700">
                                    {{ $todo->client->name ?? 'Unassigned' }}<br>
                                    <span class="text-xs text-brand-400">{{ $todo->property->name ?? '' }}</span>
                                </td>
                                <td class="px-4 py-3 align-top">
                                    <span class="inline-flex rounded-full bg-brand-50 px-2 py-0.5 text-xs font-semibold text-brand-700 border border-brand-200">
                                        {{ $statusLabels[$todo->status] ?? ucfirst($todo->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 align-top">
                                    <span class="inline-flex rounded-full bg-blue-50 px-2 py-0.5 text-xs font-semibold text-blue-700 border border-blue-200">
                                        {{ $priorityLabels[$todo->priority] ?? ucfirst($todo->priority) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 align-top text-sm text-brand-700">
                                    {{ optional($todo->due_date)->format('M j') ?? 'TBD' }}
                                </td>
                                <td class="px-4 py-3 align-top text-sm text-right">
                                    <div class="flex justify-end gap-3">
                                        <a href="{{ route('todos.edit', $todo) }}" class="text-brand-700 hover:text-brand-900">Edit</a>
                                        <form action="{{ route('todos.destroy', $todo) }}" method="POST" onsubmit="return confirm('Delete this to-do?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-5 py-4 border-t border-brand-100/60">
                    {{ $todos->links() }}
                </div>
            @else
                <div class="grid gap-4 lg:grid-cols-4 p-5" id="kanban-board">
                    @php $columns = ['future','pending','in_progress','completed']; @endphp
                    @foreach ($columns as $status)
                        @php $cards = $todos->get($status, collect()); @endphp
                        <div class="rounded-2xl border border-brand-100 bg-brand-50/40" data-status="{{ $status }}">
                            <div class="flex items-center justify-between px-4 py-3 border-b border-brand-100">
                                <h2 class="text-sm font-semibold text-brand-500">{{ $statusLabels[$status] }}</h2>
                                <span class="text-xs text-brand-400">{{ $cards->count() }}</span>
                            </div>
                            <div class="p-3 space-y-3 min-h-[200px] kanban-column" data-status="{{ $status }}">
                                @forelse ($cards as $todo)
                                    <div class="rounded-2xl border {{ $statusColors[$status] ?? 'border-brand-100 bg-white' }} p-3 shadow-sm cursor-move" data-todo-id="{{ $todo->id }}">
                                        <div class="flex items-center justify-between">
                                            <p class="font-semibold text-brand-900">{{ $todo->title }}</p>
                                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full
                                                @switch($todo->priority)
                                                    @case('urgent') bg-red-100 text-red-700 border border-red-200 @break
                                                    @case('high') bg-orange-100 text-orange-700 border border-orange-200 @break
                                                    @case('low') bg-gray-100 text-gray-600 border border-gray-200 @break
                                                    @default bg-blue-100 text-blue-700 border border-blue-200
                                                @endswitch">
                                                {{ ucfirst($todo->priority) }}
                                            </span>
                                        </div>
                                        <p class="text-sm text-brand-600">
                                            {{ $todo->client->name ?? 'Unassigned' }}
                                            @if($todo->property)
                                                · {{ $todo->property->name }}
                                            @endif
                                        </p>
                                        @if(!empty($todo->description))
                                            <p class="text-xs text-brand-500 mt-1">{{ \Illuminate\Support\Str::limit($todo->description, 120) }}</p>
                                        @endif
                                        <p class="text-xs text-brand-400 mt-1">
                                            Due {{ optional($todo->due_date)->format('M j') ?? 'TBD' }}
                                        </p>
                                        <div class="mt-2 flex gap-3 text-xs">
                                            <a href="{{ route('todos.edit', $todo) }}" class="text-brand-700 hover:text-brand-900">Edit</a>
                                            <form action="{{ route('todos.destroy', $todo) }}" method="POST" onsubmit="return confirm('Delete this to-do?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                                            </form>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-sm text-brand-300 text-center py-4">No tasks.</p>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</div>

@if ($viewMode === 'kanban')
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const token = document.querySelector('meta[name=\"csrf-token\"]').getAttribute('content');
                document.querySelectorAll('.kanban-column').forEach(column => {
                    new Sortable(column, {
                        group: 'todos',
                        animation: 150,
                        handle: '.cursor-move',
                        onEnd: function (evt) {
                            const item = evt.item;
                            const todoId = item.dataset.todoId;
                            const newStatus = evt.to.dataset.status;

                            if (!todoId || !newStatus) return;

                            fetch(`{{ url('todos') }}/${todoId}/status`, {
                                method: 'PATCH',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': token,
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify({ status: newStatus }),
                            }).then(response => {
                                if (!response.ok) throw new Error('Unable to update status');
                            }).then(() => {
                                const form = document.getElementById('todoFilters');
                                if (form) form.submit();
                            }).catch(() => {
                                alert('Failed to update task status. Please refresh.');
                                window.location.reload();
                            });
                        },
                    });
                });
            });
        </script>
    @endpush
@endif
@endsection
