<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Property;
use App\Models\Todo;
use Illuminate\Http\Request;

class TodoController extends Controller
{
    public function index(Request $request)
    {
        $viewMode = $request->get('view', 'kanban');
        $priority = $request->get('priority');
        $clientId = $request->get('client_id');

        $query = Todo::with(['client', 'property'])
            ->when($priority && in_array($priority, Todo::PRIORITIES, true), fn ($q) => $q->where('priority', $priority))
            ->when($clientId, fn ($q) => $q->where('client_id', $clientId))
            ->orderBy('due_date')
            ->latest('updated_at');

        if ($viewMode === 'list') {
            $todos = $query->paginate(15)->withQueryString();
        } else {
            $todos = $query->get()->groupBy('status');
        }

        $clients = Client::orderBy('company_name')->orderBy('last_name')->get();

        return view('todos.index', [
            'viewMode' => $viewMode,
            'todos' => $todos,
            'clients' => $clients,
            'selectedPriority' => $priority,
            'selectedClientId' => $clientId,
        ]);
    }

    public function create()
    {
        return view('todos.create', $this->formData());
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $todo = Todo::create($data);

        if ($todo->status === 'completed' && ! $todo->completed_at) {
            $todo->update(['completed_at' => now()]);
        }

        return redirect()->route('todos.index')->with('success', 'To-do created.');
    }

    public function edit(Todo $todo)
    {
        return view('todos.edit', array_merge(['todo' => $todo], $this->formData()));
    }

    public function update(Request $request, Todo $todo)
    {
        $data = $this->validateData($request);

        if ($data['status'] === 'completed' && ! $todo->completed_at) {
            $data['completed_at'] = now();
        } elseif ($data['status'] !== 'completed') {
            $data['completed_at'] = null;
        }

        $todo->update($data);

        return redirect()->route('todos.index')->with('success', 'To-do updated.');
    }

    public function destroy(Todo $todo)
    {
        $todo->delete();

        return redirect()->route('todos.index')->with('success', 'To-do deleted.');
    }

    public function updateStatus(Request $request, Todo $todo)
    {
        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', Todo::STATUSES),
        ]);

        $todo->status = $validated['status'];
        $todo->completed_at = $validated['status'] === 'completed' ? now() : null;
        $todo->save();

        return response()->json([
            'message' => 'Status updated.',
            'todo' => [
                'id' => $todo->id,
                'status' => $todo->status,
            ],
        ]);
    }

    protected function formData(): array
    {
        $clients = Client::with('properties')->orderBy('company_name')->orderBy('last_name')->get();
        $properties = Property::with('client')->orderBy('name')->get();

        return [
            'clients' => $clients,
            'properties' => $properties,
            'statuses' => Todo::STATUSES,
            'priorities' => Todo::PRIORITIES,
        ];
    }

    protected function validateData(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'client_id' => 'nullable|exists:clients,id',
            'property_id' => 'nullable|exists:properties,id',
            'status' => 'required|in:' . implode(',', Todo::STATUSES),
            'priority' => 'required|in:' . implode(',', Todo::PRIORITIES),
            'due_date' => 'nullable|date',
        ]);
    }
}
