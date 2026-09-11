<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    public function index()
    {
        $templates = EmailTemplate::latest()->get();
        return view('admin.email.templates.index', compact('templates'));
    }

    public function create()
    {
        return view('admin.email.templates.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:150',
            'subject'      => 'required|string|max:250',
            'body'         => 'required|string',
            'type'         => 'required|in:newsletter,transactional,custom',
            'preview_text' => 'nullable|string|max:250',
        ]);

        EmailTemplate::create($validated);
        return redirect()->route('admin.email.templates.index')
            ->with('success', "Template '{$validated['name']}' created successfully.");
    }

    public function edit(EmailTemplate $template)
    {
        return view('admin.email.templates.edit', compact('template'));
    }

    public function update(Request $request, EmailTemplate $template)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:150',
            'subject'      => 'required|string|max:250',
            'body'         => 'required|string',
            'type'         => 'required|in:newsletter,transactional,custom',
            'preview_text' => 'nullable|string|max:250',
        ]);

        $template->update($validated);
        return redirect()->route('admin.email.templates.index')
            ->with('success', "Template '{$template->name}' updated.");
    }

    public function destroy(EmailTemplate $template)
    {
        $name = $template->name;
        $template->delete();
        return back()->with('success', "Template '{$name}' deleted.");
    }

    public function preview(EmailTemplate $template)
    {
        $rendered = $template->render([
            'name'  => 'John Doe',
            'email' => 'john@example.com',
        ]);
        return response($rendered);
    }
}
