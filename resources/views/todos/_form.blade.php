<form action="{{ $route }}" method="POST" class="space-y-6 bg-white p-6 rounded shadow">
    @csrf
    @if ($method === 'PUT')
        @method('PUT')
    @endif

    <div>
        <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
        <input type="text" id="title" name="title" class="mt-1 form-input w-full"
               value="{{ old('title', $todo->title ?? '') }}" required>
    </div>

    <div>
        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
        <textarea id="description" name="description" rows="4" class="mt-1 form-textarea w-full"
                  placeholder="Add details, materials needed, crew notes, etc.">{{ old('description', $todo->description ?? '') }}</textarea>
    </div>

    <div class="grid md:grid-cols-2 gap-4">
        <div>
            <label for="client_id" class="block text-sm font-medium text-gray-700">Client</label>
            <select id="client_id" name="client_id" class="form-select w-full">
                <option value="">Unassigned</option>
                @foreach ($clients as $client)
                    <option value="{{ $client->id }}" @selected(old('client_id', $todo->client_id ?? '') == $client->id)>
                        {{ $client->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="property_id" class="block text-sm font-medium text-gray-700">Property</label>
            <select id="property_id" name="property_id" class="form-select w-full">
                <option value="">Unassigned</option>
                @foreach ($properties as $property)
                    <option value="{{ $property->id }}" @selected(old('property_id', $todo->property_id ?? '') == $property->id)>
                        {{ $property->name }} @if($property->client) ({{ $property->client->name }}) @endif
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="grid md:grid-cols-3 gap-4">
        <div>
            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
            <select id="status" name="status" class="form-select w-full">
                @foreach ($statuses as $status)
                    <option value="{{ $status }}" @selected(old('status', $todo->status ?? 'pending') === $status)>
                        {{ ucwords(str_replace('_', ' ', $status)) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="priority" class="block text-sm font-medium text-gray-700">Priority</label>
            <select id="priority" name="priority" class="form-select w-full">
                @foreach ($priorities as $priority)
                    <option value="{{ $priority }}" @selected(old('priority', $todo->priority ?? 'normal') === $priority)>
                        {{ ucfirst($priority) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="due_date" class="block text-sm font-medium text-gray-700">Due Date</label>
            <input type="date" id="due_date" name="due_date" class="form-input w-full"
                   value="{{ old('due_date', optional($todo->due_date ?? null)->format('Y-m-d')) }}">
        </div>
    </div>

    <div class="flex items-center justify-between">
        <button type="submit"
                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
            Save
        </button>

        <a href="{{ route('todos.index') }}" class="text-gray-600 hover:text-gray-900">Cancel</a>
    </div>
</form>
