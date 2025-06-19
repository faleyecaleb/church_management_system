<?php

namespace App\Http\Controllers;

use App\Models\SmsTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SmsTemplateController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:communication.view')->only(['index', 'show']);
        $this->middleware('permission:communication.create')->only(['create', 'store']);
        $this->middleware('permission:communication.update')->only(['edit', 'update', 'toggle']);
        $this->middleware('permission:communication.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $query = SmsTemplate::query();

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $templates = $query->orderBy('name')->paginate(15);

        return view('sms.templates.index', compact('templates'));
    }

    public function create()
    {
        return view('sms.templates.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:sms_templates',
            'content' => 'required|string|max:1000',
            'description' => 'nullable|string|max:1000',
            'variables' => 'nullable|array',
            'variables.*' => 'string|max:50',
            'is_active' => 'boolean'
        ]);

        $template = SmsTemplate::create($validated);

        return redirect()->route('sms.templates.show', $template)
            ->with('success', 'SMS template created successfully.');
    }

    public function show(SmsTemplate $template)
    {
        // Get usage statistics
        $usageStats = DB::table('sms_messages')
            ->where('template_id', $template->id)
            ->select(
                DB::raw('COUNT(*) as total_uses'),
                DB::raw('COUNT(DISTINCT CASE WHEN status = "sent" THEN id END) as successful_sends'),
                DB::raw('MAX(created_at) as last_used')
            )
            ->first();

        return view('sms.templates.show', compact('template', 'usageStats'));
    }

    public function edit(SmsTemplate $template)
    {
        return view('sms.templates.edit', compact('template'));
    }

    public function update(Request $request, SmsTemplate $template)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:sms_templates,name,' . $template->id,
            'content' => 'required|string|max:1000',
            'description' => 'nullable|string|max:1000',
            'variables' => 'nullable|array',
            'variables.*' => 'string|max:50',
            'is_active' => 'boolean'
        ]);

        $template->update($validated);

        return redirect()->route('sms.templates.show', $template)
            ->with('success', 'SMS template updated successfully.');
    }

    public function destroy(SmsTemplate $template)
    {
        // Check if template is in use
        if ($template->messages()->exists()) {
            return redirect()->route('sms.templates.index')
                ->with('error', 'Cannot delete template that has been used in messages.');
        }

        $template->delete();

        return redirect()->route('sms.templates.index')
            ->with('success', 'SMS template deleted successfully.');
    }

    public function toggle(SmsTemplate $template)
    {
        $template->update(['is_active' => !$template->is_active]);

        $status = $template->is_active ? 'activated' : 'deactivated';
        return redirect()->route('sms.templates.show', $template)
            ->with('success', "SMS template {$status} successfully.");
    }

    public function preview(Request $request, SmsTemplate $template)
    {
        $variables = $request->input('variables', []);
        $preview = $template->render($variables);

        return response()->json(['preview' => $preview]);
    }

    public function duplicate(SmsTemplate $template)
    {
        $newTemplate = $template->replicate();
        $newTemplate->name = "Copy of {$template->name}";
        $newTemplate->save();

        return redirect()->route('sms.templates.edit', $newTemplate)
            ->with('success', 'SMS template duplicated successfully.');
    }

    public function export()
    {
        $templates = SmsTemplate::all(['name', 'content', 'description', 'variables']);
        $filename = 'sms_templates_' . now()->format('Y-m-d_His') . '.json';

        return response()->json($templates)
            ->header('Content-Disposition', "attachment; filename={$filename}");
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:json|max:2048'
        ]);

        try {
            $content = json_decode(file_get_contents($request->file('file')), true);

            if (!is_array($content)) {
                throw new \Exception('Invalid file format');
            }

            DB::beginTransaction();

            foreach ($content as $templateData) {
                $templateData['is_active'] = false; // Default to inactive for safety
                SmsTemplate::create($templateData);
            }

            DB::commit();

            return redirect()->route('sms.templates.index')
                ->with('success', count($content) . ' templates imported successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('sms.templates.index')
                ->with('error', 'Failed to import templates: ' . $e->getMessage());
        }
    }
}