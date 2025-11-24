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
    <section class="rounded-[20px] sm:rounded-[28px] lg:rounded-[32px] bg-gradient-to-br from-brand-900 via-brand-800 to-brand-700 text-white p-4 sm:p-6 lg:p-8 shadow-2xl border border-brand-800/40 relative overflow-hidden">
        <div class="flex flex-wrap items-start gap-4 sm:gap-6">
            <div class="space-y-2 sm:space-y-3 max-w-2xl">
                <p class="text-xs uppercase tracking-[0.3em] text-brand-200/80">Operations</p>
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-semibold">To-Do Command Center</h1>
                <p class="text-xs sm:text-sm text-brand-100/85">Track crews, client requests, and property tasks with filters, Kanban, and list views that mirror the rest of the hub.</p>
            </div>
            <div class="flex flex-wrap gap-2 sm:gap-3 ml-auto w-full sm:w-auto">
                <x-secondary-button as="a" href="{{ route('todos.index', array_merge(request()->except('view'), ['view' => 'kanban'])) }}" class="bg-white/10 text-white border-white/40 hover:bg-white/20 text-xs sm:text-sm flex-1 sm:flex-none justify-center {{ $viewMode === 'kanban' ? 'ring-2 ring-white/50' : '' }}">
                    <svg class="h-4 w-4 mr-1.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    Kanban
                </x-secondary-button>
                <x-secondary-button as="a" href="{{ route('todos.index', array_merge(request()->except('view'), ['view' => 'list'])) }}" class="bg-white/10 text-white border-white/40 hover:bg-white/20 text-xs sm:text-sm flex-1 sm:flex-none justify-center {{ $viewMode === 'list' ? 'ring-2 ring-white/50' : '' }}">
                    <svg class="h-4 w-4 mr-1.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                    List
                </x-secondary-button>
                <x-brand-button href="{{ route('todos.create') }}" variant="muted" class="flex-1 sm:flex-none justify-center">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                    Add To-Do
                </x-brand-button>
            </div>
        </div>
        <dl class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 mt-6 sm:mt-8 text-sm text-brand-100">
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

    <section class="rounded-[20px] sm:rounded-[28px] lg:rounded-[32px] bg-white shadow-2xl border border-brand-100/60 overflow-hidden">
        <div class="p-4 sm:p-5 lg:p-7 space-y-4 sm:space-y-6">
            <form method="GET" action="{{ route('todos.index') }}" class="grid gap-3 sm:gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4" id="todoFilters">
                <input type="hidden" name="view" value="{{ $viewMode }}">
                <div>
                    <label class="block text-xs uppercase tracking-wide text-brand-400 mb-1.5 font-medium">Priority</label>
                    <select name="priority" class="w-full rounded-full border-brand-200 bg-white text-sm px-4 py-2.5 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition">
                        <option value="">All Priorities</option>
                        @foreach (Todo::PRIORITIES as $priority)
                            <option value="{{ $priority }}" @selected($selectedPriority === $priority)>{{ ucfirst($priority) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs uppercase tracking-wide text-brand-400 mb-1.5 font-medium">Client</label>
                    <select name="client_id" class="w-full rounded-full border-brand-200 bg-white text-sm px-4 py-2.5 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition">
                        <option value="">All Clients</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}" @selected($selectedClientId == $client->id)>{{ $client->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-col gap-2 justify-end">
                    <label class="inline-flex items-center gap-2 text-sm text-brand-700 cursor-pointer hover:text-brand-900 transition">
                        <input type="checkbox" name="hide_future" value="1" class="rounded border-brand-300 text-brand-600 focus:ring-brand-500 focus:ring-2" {{ request()->boolean('hide_future') ? 'checked' : '' }}>
                        <span class="font-medium">Hide Future</span>
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-brand-700 cursor-pointer hover:text-brand-900 transition">
                        <input type="checkbox" name="hide_completed" value="1" class="rounded border-brand-300 text-brand-600 focus:ring-brand-500 focus:ring-2" {{ request()->boolean('hide_completed') ? 'checked' : '' }}>
                        <span class="font-medium">Hide Completed</span>
                    </label>
                </div>
                <div class="flex items-end gap-2">
                    <x-brand-button type="submit" class="w-full justify-center" variant="outline">
                        <svg class="h-4 w-4 mr-1.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                        Apply Filters
                    </x-brand-button>
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
                <div class="p-4 bg-emerald-50 text-emerald-900 rounded-2xl border border-emerald-200 text-sm flex items-start gap-2">
                    <svg class="h-5 w-5 flex-shrink-0 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
        </div>

        <div class="border-t border-brand-100/60">
            @if ($viewMode === 'list')
                {{-- Mobile card view --}}
                <div class="md:hidden divide-y divide-brand-100">
                    @foreach ($todos as $todo)
                        <div class="p-4 hover:bg-brand-50/50 transition">
                            <div class="flex items-start gap-3">
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-brand-900">{{ $todo->title }}</p>
                                    <p class="text-sm text-brand-600 mt-1">{{ $todo->client->name ?? 'Unassigned' }}</p>
                                    @if($todo->description)
                                        <p class="text-xs text-brand-500 mt-2 line-clamp-2">{{ $todo->description }}</p>
                                    @endif
                                    <div class="flex flex-wrap items-center gap-2 mt-2">
                                        <span class="inline-flex rounded-full bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-700 border border-brand-200">
                                            {{ $statusLabels[$todo->status] ?? ucfirst($todo->status) }}
                                        </span>
                                        <span class="inline-flex rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700 border border-blue-200">
                                            {{ $priorityLabels[$todo->priority] ?? ucfirst($todo->priority) }}
                                        </span>
                                        <span class="text-xs text-brand-500">Due: {{ optional($todo->due_date)->format('M j') ?? 'TBD' }}</span>
                                    </div>
                                    <div class="flex gap-2 mt-3 text-sm">
                                        <a href="{{ route('todos.edit', $todo) }}" class="flex-1 text-center px-3 py-2 rounded-lg border-2 border-brand-600 text-brand-700 font-medium hover:bg-brand-50 transition">Edit</a>
                                        <form action="{{ route('todos.destroy', $todo) }}" method="POST" onsubmit="return confirm('Delete this to-do?');" class="flex-1">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-full px-3 py-2 rounded-lg border-2 border-red-600 text-red-600 font-medium hover:bg-red-50 transition">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Tablet/Desktop table view --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-brand-50/80 text-left text-[11px] uppercase tracking-wide text-brand-500">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Title</th>
                            <th class="px-4 py-3 font-semibold">Client / Property</th>
                            <th class="px-4 py-3 font-semibold">Status</th>
                            <th class="px-4 py-3 font-semibold">Priority</th>
                            <th class="px-4 py-3 font-semibold">Due</th>
                            <th class="px-4 py-3 text-right font-semibold">Actions</th>
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
                                    <span class="inline-flex rounded-full bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-700 border border-brand-200">
                                        {{ $statusLabels[$todo->status] ?? ucfirst($todo->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 align-top">
                                    <span class="inline-flex rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700 border border-blue-200">
                                        {{ $priorityLabels[$todo->priority] ?? ucfirst($todo->priority) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 align-top text-sm text-brand-700">
                                    {{ optional($todo->due_date)->format('M j') ?? 'TBD' }}
                                </td>
                                <td class="px-4 py-3 align-top text-sm text-right">
                                    <div class="flex justify-end gap-3">
                                        <a href="{{ route('todos.edit', $todo) }}" class="text-brand-700 hover:text-brand-900 font-medium transition">Edit</a>
                                        <form action="{{ route('todos.destroy', $todo) }}" method="POST" onsubmit="return confirm('Delete this to-do?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800 font-medium transition">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-4 sm:px-5 py-4 border-t border-brand-100/60">
                    {{ $todos->links() }}
                </div>
            @else
                {{-- Kanban view - single column on mobile, grid on tablet+ --}}
                <div class="grid gap-3 sm:gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 p-4 sm:p-5" id="kanban-board">
                    @php $columns = ['future','pending','in_progress','completed']; @endphp
                    @foreach ($columns as $status)
                        @php $cards = $todos->get($status, collect()); @endphp
                        <div class="rounded-2xl border border-brand-200 bg-brand-50/40 shadow-sm" data-status="{{ $status }}">
                            <div class="flex items-center justify-between px-4 py-3 border-b border-brand-200 bg-white/60">
                                <h2 class="text-sm font-bold text-brand-700 uppercase tracking-wide">{{ $statusLabels[$status] }}</h2>
                                <span class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-brand-600 text-white text-xs font-bold">{{ $cards->count() }}</span>
                            </div>
                            <div class="p-3 space-y-3 min-h-[200px] kanban-column" data-status="{{ $status }}">
                                @forelse ($cards as $todo)
                                    <div class="rounded-xl border-2 {{ $statusColors[$status] ?? 'border-brand-100 bg-white' }} p-3 shadow-sm hover:shadow-md transition-all cursor-move" data-todo-id="{{ $todo->id }}">
                                        <div class="flex items-start justify-between gap-2 mb-2">
                                            <p class="font-semibold text-brand-900 flex-1">{{ $todo->title }}</p>
                                            <span class="text-xs font-bold px-2 py-1 rounded-full flex-shrink-0
                                                @switch($todo->priority)
                                                    @case('urgent') bg-red-100 text-red-700 border border-red-300 @break
                                                    @case('high') bg-orange-100 text-orange-700 border border-orange-300 @break
                                                    @case('low') bg-gray-100 text-gray-600 border border-gray-300 @break
                                                    @default bg-blue-100 text-blue-700 border border-blue-300
                                                @endswitch">
                                                {{ ucfirst($todo->priority) }}
                                            </span>
                                        </div>
                                        <p class="text-sm text-brand-600 mb-2">
                                            {{ $todo->client->name ?? 'Unassigned' }}
                                            @if($todo->property)
                                                <span class="text-brand-400">·</span> {{ $todo->property->name }}
                                            @endif
                                        </p>
                                        @if(!empty($todo->description))
                                            <p class="text-xs text-brand-500 mb-2 line-clamp-2">{{ \Illuminate\Support\Str::limit($todo->description, 120) }}</p>
                                        @endif
                                        <div class="flex items-center justify-between pt-2 border-t border-brand-200/50">
                                            <p class="text-xs text-brand-400 font-medium">
                                                Due {{ optional($todo->due_date)->format('M j') ?? 'TBD' }}
                                            </p>
                                            <div class="flex gap-2 text-xs">
                                                <a href="{{ route('todos.edit', $todo) }}" class="text-brand-700 hover:text-brand-900 font-medium transition">Edit</a>
                                                <form action="{{ route('todos.destroy', $todo) }}" method="POST" onsubmit="return confirm('Delete this to-do?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-800 font-medium transition">Delete</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="flex items-center justify-center py-8">
                                        <p class="text-sm text-brand-300 font-medium">No tasks</p>
                                    </div>
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
                        ghostClass: 'opacity-50',
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
                                // Show success toast if available
                                if (window.showToast) window.showToast('Task moved successfully', 'success');
                            }).then(() => {
                                // Reload to update counts
                                setTimeout(() => window.location.reload(), 500);
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
