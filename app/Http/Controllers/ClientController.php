<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = Client::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('mobile_number', 'like', "%{$search}%")
                  ->orWhere('gst_number', 'like', "%{$search}%");
            });
        }

        $clients = $query->latest()->paginate(10)->withQueryString();

        return view('clients.index', compact('clients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                    => ['required', 'string', 'max:150'],
            'address'                 => ['nullable', 'string'],
            'mobile_number'           => ['nullable', 'string', 'max:20'],
            'gst_number'              => ['nullable', 'string', 'max:30'],
            'notes'                   => ['nullable', 'string'],
            'is_active'               => ['boolean'],
            'menu_labels'             => ['nullable', 'array'],
            'session_timeout_minutes' => ['nullable', 'integer', 'min:5', 'max:1440'],
        ]);

        $validated['is_active'] = $request->has('is_active');
        // Only SuperAdmin can set session timeout (default 120 if not provided)
        $validated['session_timeout_minutes'] = $request->input('session_timeout_minutes', 120);

        // Process dynamic menu keys and values
        $menuLabels = [];
        if ($request->has('menu_keys') && $request->has('menu_values')) {
            foreach ($request->menu_keys as $idx => $key) {
                if (!empty($key) && isset($request->menu_values[$idx]) && trim($request->menu_values[$idx]) !== '') {
                    $menuLabels[$key] = trim($request->menu_values[$idx]);
                }
            }
        }
        $validated['menu_labels'] = $menuLabels;

        Client::create($validated);

        return redirect()->route('clients.index')->with('success', 'Client created successfully.');
    }

    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name'                    => ['required', 'string', 'max:150'],
            'address'                 => ['nullable', 'string'],
            'mobile_number'           => ['nullable', 'string', 'max:20'],
            'gst_number'              => ['nullable', 'string', 'max:30'],
            'notes'                   => ['nullable', 'string'],
            'is_active'               => ['boolean'],
            'menu_keys'               => ['nullable', 'array'],
            'menu_values'             => ['nullable', 'array'],
            'session_timeout_minutes' => ['nullable', 'integer', 'min:5', 'max:1440'],
        ]);

        $validated['is_active'] = $request->has('is_active');
        // Only SuperAdmin can change session timeout
        $validated['session_timeout_minutes'] = $request->input('session_timeout_minutes', $client->session_timeout_minutes ?? 120);

        // Process dynamic menu keys and values
        $menuLabels = [];
        if ($request->has('menu_keys') && $request->has('menu_values')) {
            foreach ($request->menu_keys as $idx => $key) {
                if (!empty($key) && isset($request->menu_values[$idx]) && trim($request->menu_values[$idx]) !== '') {
                    $menuLabels[$key] = trim($request->menu_values[$idx]);
                }
            }
        }
        $validated['menu_labels'] = $menuLabels;

        $client->update($validated);

        return redirect()->route('clients.index')->with('success', 'Client updated successfully.');
    }
}
