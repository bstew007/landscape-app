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
<div class="space-y-8">
    <!-- Header -->
    <section class="rounded-[20px] sm:rounded-[28px] lg:rounded-[32px] bg-gradient-to-br from-brand-900 via-brand-800 to-brand-700 text-white p-4 sm:p-6 lg:p-8 shadow-2xl border border-brand-800/40 relative overflow-hidden">
        <div class="flex flex-wrap items-start gap-4 sm:gap-6">
            <div class="space-y-2 sm:space-y-3 max-w-2xl">
                <p class="text-xs uppercase tracking-[0.3em] text-brand-200/80">Operations</p>
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-semibold">To-Do Command Center</h1>
                <p class="text-xs sm:text-sm text-brand-100/85">Track crews, client requests, and property tasks with filters, Kanban, and list views.</p>
            </div>
            <div class="flex flex-wrap gap-2 sm:gap-3 ml-auto w-full sm:w-auto">
                <x-brand-button href="{{ route('todos.create') }}" variant="muted">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 5v14M5 12h14"/></svg>
                    New To-Do
                </x-brand-button>
            </div>
        </div>
    </section>

    <!-- Filters Card -->
    <section class="rounded-[20px] sm:rounded-[28px] lg:rounded-[32px] bg-white shadow-2xl border border-brand-100/60 overflow-hidden">
        <div class="p-4 sm:p-5 lg:p-7 space-y-4 sm:space-y-6">
            <form method="GET" action="{{ route('todos.index') }}" class="grid gap-3 sm:gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4" id="todoFilters">
                <input type="hidden" name="view" value="{{ $viewMode }}">
                
                <div>
                    <label class="block text-xs uppercase tracking-wide text-brand-400 mb-1.5 font-medium">Priority</label>
                    <select name="priority" class="w-full rounded-full border-brand-200 bg-white text-sm px-4 py-2.5 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition" onchange="this.form.submit()">
                        <option value="">All Priorities</option>
                        @foreach (Todo::PRIORITIES as $p)
                            <option value="{{ $p }}" @selected(($selectedPriority ?? '') === $p)>{{ ucfirst($p) }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label class="block text-xs uppercase tracking-wide text-brand-400 mb-1.5 font-medium">Client</label>
                    <select name="client_id" class="w-full rounded-full border-brand-200 bg-white text-sm px-4 py-2.5 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition" onchange="this.form.submit()">
                        <option value="">All Clients</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}" @selected(($selectedClientId ?? '') == $client->id)>{{ $client->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="flex flex-col gap-2 justify-end">
                    <label class="inline-flex items-center gap-2 text-sm text-brand-700 cursor-pointer hover:text-brand-900 transition">
                        <input type="hidden" name="hide_future" value="0">
                        <input type="checkbox" name="hide_future" value="1" class="rounded border-brand-300 text-brand-600 focus:ring-brand-500 focus:ring-2" @checked($hideFuture) onchange="this.form.submit()">
                        <span class="font-medium">Hide Future</span>
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-brand-700 cursor-pointer hover:text-brand-900 transition">
                        <input type="hidden" name="hide_completed" value="0">
                        <input type="checkbox" name="hide_completed" value="1" class="rounded border-brand-300 text-brand-600 focus:ring-brand-500 focus:ring-2" @checked($hideCompleted) onchange="this.form.submit()">
                        <span class="font-medium">Hide Completed</span>
                    </label>
                </div>
                
                <div class="flex items-end gap-2">
                    <a href="{{ route('todos.index', ['view' => 'kanban']) }}" class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-full border text-sm font-medium transition {{ $viewMode === 'kanban' ? 'bg-brand-700 text-white border-brand-600' : 'bg-white text-brand-700 border-brand-200 hover:bg-brand-50' }}">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="5" height="18" rx="1"/><rect x="10" y="3" width="5" height="12" rx="1"/><rect x="17" y="3" width="5" height="8" rx="1"/></svg>
                        Kanban
                    </a>
                    <a href="{{ route('todos.index', ['view' => 'list', 'hide_future' => $hideFuture ? '1' : '0', 'hide_completed' => $hideCompleted ? '1' : '0', 'priority' => $selectedPriority, 'client_id' => $selectedClientId]) }}" class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-full border text-sm font-medium transition {{ $viewMode === 'list' ? 'bg-brand-700 text-white border-brand-600' : 'bg-white text-brand-700 border-brand-200 hover:bg-brand-50' }}">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
                        List
                    </a>
                </div>
            </form>
        </div>
    </section>

    <!-- Kanban View -->
    @if($viewMode === 'kanban')
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
            @foreach(['future', 'pending', 'in_progress', 'completed'] as $status)
                @php
                    $statusTodos = $todos->get($status, collect());
                    $show = true;
                    if ($status === 'future' && $hideFuture) $show = false;
                    if ($status === 'completed' && $hideCompleted) $show = false;
                @endphp
                @if($show)
                    <div class="rounded-2xl border-2 {{ $statusColors[$status] }} p-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-semibold text-gray-900">{{ $statusLabels[$status] }}</h3>
                            <span class="text-xs bg-white/80 px-2 py-1 rounded-full font-medium">{{ $statusTodos->count() }}</span>
                        </div>
                        <div class="space-y-3">
                            @forelse($statusTodos as $todo)
                                <div class="bg-white rounded-xl p-3 shadow-sm border border-gray-100 hover:shadow-md transition">
                                    <a href="{{ route('todos.edit', $todo) }}" class="font-medium text-gray-900 hover:text-brand-700 block">{{ $todo->title }}</a>
                                    <div class="flex items-center gap-2 mt-2 text-xs text-gray-500">
                                        @if($todo->priority)
                                            <span class="px-2 py-0.5 rounded-full font-medium border
                                                @switch($todo->priority)
                                                    @case('urgent') bg-red-50 text-red-700 border-red-200 @break
                                                    @case('high') bg-orange-50 text-orange-700 border-orange-200 @break
                                                    @case('low') bg-gray-50 text-gray-600 border-gray-200 @break
                                                    @default bg-blue-50 text-blue-700 border-blue-200
                                                @endswitch">
                                                {{ ucfirst($todo->priority) }}
                                            </span>
                                        @endif
                                        @if($todo->due_date)
                                            <span>Due {{ $todo->due_date->format('M j') }}</span>
                                        @endif
                                    </div>
                                    @if($todo->client)
                                        <p class="text-xs text-gray-400 mt-1">{{ $todo->client->name }}</p>
                                    @endif
                                </div>
                            @empty
                                <p class="text-sm text-gray-400 text-center py-4">No tasks</p>
                            @endforelse
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    @endif

    <!-- List View -->
    @if($viewMode === 'list')
        <section class="rounded-[20px] sm:rounded-[28px] lg:rounded-[32px] bg-white shadow-2xl border border-brand-100/60 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-brand-50/80 text-left text-[11px] uppercase tracking-wide text-brand-500">
                        <tr>
                            <th class="px-4 py-3">Title</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Priority</th>
                            <th class="px-4 py-3">Client</th>
                            <th class="px-4 py-3">Due Date</th>
                            <th class="px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-brand-50">
                        @forelse($todos->items() as $todo)
                            <tr class="hover:bg-brand-50/50 transition">
                                <td class="px-4 py-3">
                                    <a href="{{ route('todos.edit', $todo) }}" class="font-medium text-brand-900 hover:text-brand-700 hover:underline">{{ $todo->title }}</a>
                                    @if($todo->description)
                                        <p class="text-xs text-gray-500 mt-1 line-clamp-1">{{ Str::limit($todo->description, 60) }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold border {{ $statusColors[$todo->status] ?? 'bg-gray-50 border-gray-200' }}">
                                        {{ $statusLabels[$todo->status] ?? ucfirst($todo->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium border
                                        @switch($todo->priority)
                                            @case('urgent') bg-red-50 text-red-700 border-red-200 @break
                                            @case('high') bg-orange-50 text-orange-700 border-orange-200 @break
                                            @case('low') bg-gray-50 text-gray-600 border-gray-200 @break
                                            @default bg-blue-50 text-blue-700 border-blue-200
                                        @endswitch">
                                        {{ ucfirst($todo->priority) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ $todo->client?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $todo->due_date?->format('M j, Y') ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('todos.edit', $todo) }}" class="text-brand-600 hover:text-brand-800 text-sm">Edit</a>
                                        <form action="{{ route('todos.destroy', $todo) }}" method="POST" class="inline" onsubmit="return confirm('Delete this to-do?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800 text-sm">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500">No to-dos found matching your filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($todos->hasPages())
                <div class="px-4 py-4 border-t border-brand-100/60">
                    {{ $todos->links() }}
                </div>
            @endif
        </section>
    @endif
</div>
@endsection
