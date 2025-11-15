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
        'pending' => 'border-yellow-200 bg-yellow-50',
        'in_progress' => 'border-blue-200 bg-blue-50',
        'completed' => 'border-green-200 bg-green-50',
    ];
    $priorityLabels = [
        'low' => 'Low',
        'normal' => 'Normal',
        'high' => 'High',
        'urgent' => 'Urgent',
    ];
@endphp

@section('content')
    <div class="space-y-6">
        <x-page-header title="To-Do Board" eyebrow="Operations" subtitle="Track landscaping tasks by client/property.">
            <x-slot:actions>
                <x-brand-button href="{{ route('todos.create') }}">+ Add To-Do</x-brand-button>
                <x-brand-button href="{{ route('todos.index', array_merge(request()->except('view'), ['view' => 'kanban'])) }}" variant="outline">Kanban</x-brand-button>
                <x-brand-button href="{{ route('todos.index', array_merge(request()->except('view'), ['view' => 'list'])) }}" variant="outline">List</x-brand-button>
            </x-slot:actions>
        </x-page-header>

        <form method="GET" action="{{ route('todos.index') }}" class="bg-white rounded-lg shadow p-4 grid gap-4 md:grid-cols-4 mt-6" id="todoFilters">
            <div>
                <label class="block text-sm font-medium text-gray-700">Priority</label>
                <select name="priority" class="form-select w-full mt-1">
                    <option value="">All</option>
                    @foreach (Todo::PRIORITIES as $priority)
                        <option value="{{ $priority }}" @selected($selectedPriority === $priority)>{{ ucfirst($priority) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Client</label>
                <select name="client_id" class="form-select w-full mt-1">
                    <option value="">All</option>
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}" @selected($selectedClientId == $client->id)>{{ $client->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Visibility</label>
                <div class="mt-2 flex flex-col gap-2">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="hide_future" value="1" class="form-checkbox" {{ request()->boolean('hide_future') ? 'checked' : '' }}>
                        Hide Future
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="hide_completed" value="1" class="form-checkbox" {{ request()->boolean('hide_completed') ? 'checked' : '' }}>
                        Hide Completed
                    </label>
                </div>
            </div>
            <div class="flex items-end">
                <x-brand-button type="submit" class="w-full justify-center">Apply Filters</x-brand-button>
                <script>
                    // Auto-submit on toggle for the two checkboxes
                    document.addEventListener('DOMContentLoaded', () => {
                        const form = document.getElementById('todoFilters');
                        if (!form) return;
                        ['hide_future','hide_completed'].forEach(name => {
                            const el = form.querySelector(`input[name="${name}"]`);
                            if (el) el.addEventListener('change', () => form.submit());
                        });
                    });
                </script>
            </div>
        </form>

        @if ($viewMode === 'list')
            <div class="bg-white rounded-lg shadow overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-gray-50 text-gray-600 uppercase text-xs tracking-wide">
                        <tr>
                            <th class="px-4 py-3">Title</th>
                            <th class="px-4 py-3">Client / Property</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Priority</th>
                            <th class="px-4 py-3">Due</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach ($todos as $todo)
                            <tr>
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-gray-900">{{ $todo->title }}</p>
                                    <p class="text-xs text-gray-500">{{ Str::limit($todo->description, 80) }}</p>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700">
                                    {{ $todo->client->name ?? 'Unassigned' }}
                                    <br>
                                    <span class="text-xs text-gray-500">{{ $todo->property->name ?? '' }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full bg-gray-100 px-2 text-xs font-semibold text-gray-700">
                                        {{ $statusLabels[$todo->status] ?? ucfirst($todo->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full bg-blue-100 px-2 text-xs font-semibold text-blue-700">
                                        {{ $priorityLabels[$todo->priority] ?? ucfirst($todo->priority) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ optional($todo->due_date)->format('M j') ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('todos.edit', $todo) }}" class="text-blue-600 hover:text-blue-800">Edit</a>
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
                <div class="px-4 py-3">
                    {{ $todos->links() }}
                </div>
            </div>
        @else
            <div class="grid gap-4 lg:grid-cols-4" id="kanban-board">
                @php
                    // Reordered columns: Future, Pending, In Progress, Completed
                    $columns = ['future','pending','in_progress','completed'];
                @endphp
                @foreach ($columns as $status)
                    @php
                        $cards = $todos->get($status, collect());
                    @endphp
                    <div class="rounded-lg border bg-gray-50" data-status="{{ $status }}">
                        <div class="flex items-center justify-between px-4 py-3 border-b">
                            <h2 class="text-sm font-semibold text-gray-600">{{ $statusLabels[$status] }}</h2>
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-gray-500">{{ $cards->count() }}</span>
                            </div>
                        </div>
                        <div class="p-3 space-y-3 min-h-[200px] kanban-column" data-status="{{ $status }}">
                            @forelse ($cards as $todo)
                                <div class="rounded border {{ $statusColors[$status] ?? 'border-gray-200 bg-white' }} p-3 shadow-sm cursor-move" data-todo-id="{{ $todo->id }}">
                                    <div class="flex items-center justify-between">
                                        <p class="font-semibold text-gray-900">{{ $todo->title }}</p>
                                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full
                                            @switch($todo->priority)
                                                @case('urgent') bg-red-100 text-red-700 @break
                                                @case('high') bg-orange-100 text-orange-700 @break
                                                @case('low') bg-gray-100 text-gray-600 @break
                                                @default bg-blue-100 text-blue-700
                                            @endswitch">
                                            {{ ucfirst($todo->priority) }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600">
                                        {{ $todo->client->name ?? 'Unassigned' }}
                                        @if($todo->property)
                                            · {{ $todo->property->name }}
                                        @endif
                                    </p>
                                    @if(!empty($todo->description))
                                        <p class="text-xs text-gray-700 mt-1">{{ \Illuminate\Support\Str::limit($todo->description, 120) }}</p>
                                    @endif
                                    <p class="text-xs text-gray-500 mt-1">
                                        Due {{ optional($todo->due_date)->format('M j') ?? 'TBD' }}
                                    </p>
                                    <div class="mt-2 flex gap-3 text-xs">
                                        <a href="{{ route('todos.edit', $todo) }}" class="text-blue-700 hover:text-blue-900">Edit</a>
                                        <form action="{{ route('todos.destroy', $todo) }}" method="POST" onsubmit="return confirm('Delete this to-do?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-gray-400 text-center py-4">No tasks.</p>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    @if ($viewMode === 'kanban')
        @push('scripts')
            <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    document.querySelectorAll('.kanban-column').forEach(column => {
                        new Sortable(column, {
                            group: 'todos',
                            animation: 150,
                            handle: '.cursor-move',
                            onEnd: function (evt) {
                                const item = evt.item;
                                const todoId = item.dataset.todoId;
                                const newStatus = evt.to.dataset.status;

                                if (!todoId || !newStatus) {
                                    return;
                                }

                                fetch(`{{ url('todos') }}/${todoId}/status`, {
                                    method: 'PATCH',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': token,
                                        'Accept': 'application/json',
                                    },
                                    body: JSON.stringify({ status: newStatus }),
                                }).then(response => {
                                    if (!response.ok) {
                                        throw new Error('Unable to update status');
                                    }
                                }).then(() => {
                                    // After moving into a hidden column (e.g., completed when hide_completed is on), reload to respect filters
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
