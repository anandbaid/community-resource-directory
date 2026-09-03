<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplates;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmailTemplateController extends Controller
{
    public function index()
    {
        $emailTemplates = EmailTemplates::orderBy('id', 'DESC')->get();

        return Inertia::render('EmailTemplates/Index', [
            'emailTemplates' => $emailTemplates->map(fn (EmailTemplates $template) => [
                'id' => $template->id,
                'name' => $template->name,
                'title' => $template->title,
                'status' => $template->status,
                'editUrl' => route('admin.emailtemplate.edit', $template->id),
                'statusUrl' => route('admin.emailtemplate.status', $template->id),
            ])->values(),
            'createUrl' => route('admin.emailtemplate.create'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('EmailTemplates/Form', [
            'type' => 'Create',
            'submitUrl' => route('admin.emailtemplate.store'),
            'indexUrl' => route('admin.emailtemplate.index'),
            'ckeditorUrl' => asset('plugins/ckeditor/ckeditor.js'),
            'values' => $this->formValues(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'template_name' => 'required',
            'template_title' => 'required',
            'ckeditor_data' => 'required',
            'template_data' => 'required',
        ]);

        try {
            $emailTemplate = new EmailTemplates();
            $emailTemplate->name = $request->template_name;
            $emailTemplate->title = $request->template_title;
            $emailTemplate->content = $request->ckeditor_data;
            $emailTemplate->data = $request->template_data;
            // The column default in the migration is 'Active', which is not one of
            // the enum's values ('active'/'inactive'), so set it explicitly.
            $emailTemplate->status = 'active';
            $emailTemplate->save();

            return to_route('admin.emailtemplate.index')->with('success', 'Email Template added successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'An unexpected error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Email templates have no read-only screen; keep the resource route pointing
     * somewhere useful.
     */
    public function show(string $id)
    {
        return to_route('admin.emailtemplate.edit', $id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $emailTemplate = EmailTemplates::findOrFail($id);

        return Inertia::render('EmailTemplates/Form', [
            'type' => 'Edit',
            'submitUrl' => route('admin.emailtemplate.update', $emailTemplate->id),
            'indexUrl' => route('admin.emailtemplate.index'),
            'ckeditorUrl' => asset('plugins/ckeditor/ckeditor.js'),
            'values' => $this->formValues($emailTemplate),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'template_name' => 'required',
            'template_title' => 'required',
            'ckeditor_data' => 'required',
        ]);

        try {
            $emailTemplate = EmailTemplates::findOrFail($id);
            $emailTemplate->name = $request->template_name;
            $emailTemplate->title = $request->template_title;
            $emailTemplate->content = $request->ckeditor_data;
            $emailTemplate->save();

            return to_route('admin.emailtemplate.index')->with('success', 'Email Template updated successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'An unexpected error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function updateStatus(Request $request, string $id)
    {
        $request->validate([
            'status' => 'required|in:active,inactive',
        ]);

        try {
            $template = EmailTemplates::findOrFail($id);
            $template->status = strtolower($request->status);
            $template->save();

            return back()->with('success', 'Status updated');
        } catch (\Exception $err) {
            return back()->with('error', $err->getMessage());
        }
    }

    /**
     * The form posts the legacy `template_*` field names the controller reads.
     *
     * @return array<string, mixed>
     */
    private function formValues(?EmailTemplates $template = null): array
    {
        return [
            'template_name' => $template->name ?? '',
            'template_title' => $template->title ?? '',
            'ckeditor_data' => $template->content ?? '',
            'template_data' => $template->data ?? '',
        ];
    }
}
