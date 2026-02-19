<?php

namespace App\Http\Controllers;

use App\Enums\ContactRole;
use App\Http\Requests\Contacts\StoreContactRequest;
use App\Http\Requests\Contacts\UpdateContactRequest;
use App\Models\Contact;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    public function index(): Response
    {
        $contacts = Contact::query()
            ->orderBy('name')
            ->get();

        return Inertia::render('contacts/Index', [
            'contacts' => $contacts,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('contacts/Create', [
            'roles' => ContactRole::cases(),
        ]);
    }

    public function store(StoreContactRequest $request): RedirectResponse
    {
        $contact = Contact::create($request->validated());

        return to_route('contacts.show', $contact)->with('status', 'Contact created.');
    }

    public function show(Contact $contact): Response
    {
        $contact->load(['cleaningTasks.property', 'properties']);

        return Inertia::render('contacts/Show', [
            'contact' => $contact,
        ]);
    }

    public function edit(Contact $contact): Response
    {
        return Inertia::render('contacts/Edit', [
            'contact' => $contact,
            'roles' => ContactRole::cases(),
        ]);
    }

    public function update(UpdateContactRequest $request, Contact $contact): RedirectResponse
    {
        $contact->update($request->validated());

        return to_route('contacts.show', $contact)->with('status', 'Contact updated.');
    }

    public function destroy(Contact $contact): RedirectResponse
    {
        $contact->delete();

        return to_route('contacts.index')->with('status', 'Contact deleted.');
    }
}
