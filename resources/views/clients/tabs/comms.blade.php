<section class="bg-white rounded-lg shadow p-6 space-y-4">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-900">Communications</h2>
        <div class="flex items-center gap-2">
            <a href="{{ route('todos.create', ['client_id' => $contact->id]) }}" class="rounded border border-emerald-300 px-4 py-2 text-sm text-emerald-700 hover:bg-emerald-50">+ New To‑Do</a>
            <button type="button" id="toggleQuickTodo" class="rounded border px-3 py-1 text-sm">Quick add</button>
        </div>
    </div>

    <div id="quickTodoWrap" class="hidden border rounded p-3">
        <form method="POST" action="{{ route('todos.store') }}" class="grid gap-3 sm:grid-cols-2">
            @csrf
            <input type="hidden" name="client_id" value="{{ $contact->id }}">
            <input type="hidden" name="status" value="pending">
            <input type="hidden" name="priority" value="normal">
            <input type="hidden" name="redirect_to" value="{{ request()->fullUrl() }}">
            <div class="sm:col-span-2">
                <label class="block text-xs text-gray-500">Title</label>
                <input type="text" name="title" class="form-input w-full" required>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs text-gray-500">Description</label>
                <textarea name="description" rows="3" class="form-textarea w-full"></textarea>
            </div>
            <div>
                <label class="block text-xs text-gray-500">Due Date</label>
                <input type="date" name="due_date" class="form-input w-full">
            </div>
            <div class="sm:col-span-2 flex items-center justify-end gap-2">
                <button class="px-3 py-2 rounded border" type="button" id="quickTodoCancel">Cancel</button>
                <button class="px-3 py-2 rounded bg-emerald-600 text-white">Save To‑Do</button>
            </div>
        </form>
    </div>

    @php
        $type = request('type');
        $todoStatus = request('todo_status');
        $from = request('from');
        $to = request('to');
    @endphp

    <form method="GET" action="{{ request()->url() }}" class="flex flex-wrap items-end gap-3 text-sm">
        <div>
            <label class="block text-xs text-gray-500">Type</label>
            <select name="type" class="form-select">
                <option value="">All</option>
                <option value="todos" {{ $type==='todos' ? 'selected' : '' }}>To‑Dos</option>
                <option value="emails" {{ $type==='emails' ? 'selected' : '' }}>Emails</option>
                <option value="visits" {{ $type==='visits' ? 'selected' : '' }}>Site Visits</option>
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500">To‑Do Status</label>
            <select name="todo_status" class="form-select">
                <option value="">All</option>
                @foreach(['future','pending','in_progress','completed'] as $s)
                    <option value="{{ $s }}" {{ $todoStatus===$s ? 'selected' : '' }}>{{ ucwords(str_replace('_',' ', $s)) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500">From</label>
            <input type="date" name="from" class="form-input" value="{{ $from }}">
        </div>
        <div>
            <label class="block text-xs text-gray-500">To</label>
            <input type="date" name="to" class="form-input" value="{{ $to }}">
        </div>
        <div class="ml-auto">
            <button class="px-3 py-2 rounded bg-gray-900 text-white">Apply</button>
        </div>
        <input type="hidden" name="tab" value="comms">
    </form>

    @php
        $rows = collect();
        if ($type === null || $type === '' || $type === 'todos') {
            $rows = $rows->merge($todos->map(function($t){
                return [
                    'kind' => 'todo',
                    'date' => $t->updated_at ?? $t->created_at,
                    'payload' => $t,
                ];
            }));
        }
        if ($type === null || $type === '' || $type === 'emails') {
            $rows = $rows->merge(($emailEvents ?? collect())->map(function($e){
                return [
                    'kind' => 'email',
                    'date' => $e->email_last_sent_at ?? $e->email_sent_at ?? $e->created_at,
                    'payload' => $e,
                ];
            }));
        }
        if ($type === null || $type === '' || $type === 'visits') {
            $rows = $rows->merge(($siteVisits ?? collect())->map(function($v){
                return [
                    'kind' => 'visit',
                    'date' => $v->visit_date ?? $v->created_at,
                    'payload' => $v,
                ];
            }));
        }
        // Filter by todo status
        if (!empty($todoStatus)) {
            $rows = $rows->filter(function($r) use ($todoStatus){
                return $r['kind'] !== 'todo' || ($r['payload']->status === $todoStatus);
            });
        }
        // Filter by date range
        if (!empty($from)) {
            $rows = $rows->filter(function($r) use ($from){ return optional($r['date'])->toDateString() >= $from; });
        }
        if (!empty($to)) {
            $rows = $rows->filter(function($r) use ($to){ return optional($r['date'])->toDateString() <= $to; });
        }
        $rows = $rows->sortByDesc(fn($r)=>$r['date'])->values();
        $grouped = $rows->groupBy(fn($r)=> optional($r['date'])->toDateString());
    @endphp

    @if($rows->isEmpty())
        <p class="text-sm text-gray-500">No communications match your filters.</p>
    @else
        <div class="space-y-6">
            @foreach($grouped as $date => $list)
                <div>
                    <h3 class="text-xs uppercase tracking-wide text-gray-500 mb-2">{{ \Carbon\Carbon::parse($date)->isToday() ? 'Today' : (\Carbon\Carbon::parse($date)->isYesterday() ? 'Yesterday' : \Carbon\Carbon::parse($date)->format('M j, Y')) }}</h3>
                    <div class="space-y-3">
                        @foreach($list as $row)
                            @if($row['kind']==='todo')
                                @php $t = $row['payload']; @endphp
                                <div class="border rounded p-3">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="font-semibold text-gray-900">To‑Do: {{ $t->title }}</p>
                                            <p class="text-xs text-gray-500">Status: {{ ucwords(str_replace('_',' ', $t->status)) }} · Priority: {{ ucfirst($t->priority) }} @if($t->property) · Property: {{ $t->property->name }} @endif</p>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <form method="POST" action="{{ route('todos.updateStatus', $t) }}" onsubmit="return true;">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="redirect_to" value="{{ request()->fullUrl() }}">
                                                <select name="status" class="form-select text-sm" onchange="this.form.submit()">
                                                    @foreach(['future','pending','in_progress','completed'] as $s)
                                                        <option value="{{ $s }}" {{ $t->status===$s ? 'selected' : '' }}>{{ ucwords(str_replace('_',' ', $s)) }}</option>
                                                    @endforeach
                                                </select>
                                            </form>
                                            <a href="{{ route('todos.edit', $t) }}" class="text-blue-600 hover:underline text-sm">Edit</a>
                                        </div>
                                    </div>
                                    @if($t->description)
                                        <p class="text-sm text-gray-700 mt-2">{{ $t->description }}</p>
                                    @endif
                                </div>
                            @elseif($row['kind']==='email')
                                @php $e = $row['payload']; @endphp
                                <div class="border rounded p-3">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="font-semibold text-gray-900">Estimate Emailed: #{{ $e->id }} — {{ $e->title ?? 'Untitled' }}</p>
                                            <p class="text-xs text-gray-500">{{ optional($e->email_last_sent_at)->format('M j, Y g:ia') }} · {{ $e->email_send_count }} {{ Str::plural('send', (int) $e->email_send_count) }} @if($e->emailSender) · by {{ $e->emailSender->name }} @endif @if($e->property) · {{ $e->property->name }} @endif</p>
                                        </div>
                                        <a href="{{ route('estimates.show', $e) }}" class="text-blue-600 hover:underline text-sm">Open</a>
                                    </div>
                                </div>
                            @elseif($row['kind']==='visit')
                                @php $v = $row['payload']; @endphp
                                <div class="border rounded p-3">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="font-semibold text-gray-900">Site Visit: {{ optional($v->visit_date)->format('M j, Y') ?? ('#'.$v->id) }}</p>
                                            <p class="text-xs text-gray-500">@if($v->property) {{ $v->property->name }} @endif</p>
                                        </div>
                                        <a href="{{ route('contacts.site-visits.show', [$contact, $v]) }}" class="text-blue-600 hover:underline text-sm">Open</a>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</section>
