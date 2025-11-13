@extends('layouts.sidebar')

@php
    use App\Models\Todo;
    $statusLabels = [
        'pending' => 'Pending',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
    ];
    $statusColors = [
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
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-bold">To-Do Board</h1>
                <p class="text-gray-600">Track landscaping tasks by client/property.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('todos.create') }}"
                   class="inline-flex items-center rounded-md bg-brand-700 px-4 py-2 text-white hover:bg-brand-800">
                    + Add To-Do
                </a>
                <a href="{{ route('todos.index', array_merge(request()->except('view'), ['view' => 'kanban'])) }}"
                   class="inline-flex items-center rounded-md border px-4 py-2 text-sm {{ $viewMode === 'kanban' ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-700 border-gray-300' }}">
                    Kanban
                </a>
                <a href="{{ route('todos.index', array_merge(request()->except('view'), ['view' => 'list'])) }}"
                   class="inline-flex items-center rounded-md border px-4 py-2 text-sm {{ $viewMode === 'list' ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-700 border-gray-300' }}">
                    List
                </a>
            </div>
        </div>

        <form method="GET" class="bg-white rounded-lg shadow p-4 grid gap-4 md:grid-cols-3">
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
            <div class="flex items-end">
                <button type="submit" class="w-full bg-gray-900 text-white rounded py-2 hover:bg-black">
                    Filter
                </button>
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
            <div class="grid gap-4 lg:grid-cols-3" id="kanban-board">
                @foreach (Todo::STATUSES as $status)
                    @php
                        $cards = $todos->get($status, collect());
                    @endphp
                    <div class="rounded-lg border bg-gray-50" data-status="{{ $status }}">
                        <div class="flex items-center justify-between px-4 py-3 border-b">
                            <h2 class="text-sm font-semibold text-gray-600">{{ $statusLabels[$status] }}</h2>
                            <span class="text-xs text-gray-500">{{ $cards->count() }}</span>
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
