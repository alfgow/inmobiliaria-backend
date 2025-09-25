<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function index(): View
    {
        $contacts = Contact::query()
            ->orderByDesc('id')
            ->paginate(10);

        return view('contacts.index', [
            'contacts' => $contacts,
        ]);
    }
}
