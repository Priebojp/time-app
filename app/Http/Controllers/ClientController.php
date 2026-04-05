<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request, string $current_company): Response
    {
        $this->authorize('viewAny', Client::class);

        return Inertia::render('Clients/Index', [
            'clients' => $request->user()->currentCompany->clients()->get(),
        ]);
    }

    public function store(Request $request, string $current_company)
    {
        $this->authorize('create', Client::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
        ]);

        $request->user()->currentCompany->clients()->create($validated);

        return back()->with('flash', ['message' => 'Client created successfully.']);
    }

    public function update(Request $request, string $current_company, Client $client)
    {
        $this->authorize('update', $client);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
        ]);

        $client->update($validated);

        return back()->with('flash', ['message' => 'Client updated successfully.']);
    }

    public function destroy(string $current_company, Client $client)
    {
        $this->authorize('delete', $client);

        $client->delete();

        return back()->with('flash', ['message' => 'Client deleted successfully.']);
    }
}
