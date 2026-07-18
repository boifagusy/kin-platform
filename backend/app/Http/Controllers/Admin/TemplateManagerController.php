<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Template;
use App\Services\TemplateService;
use Illuminate\Http\Request;

class TemplateManagerController extends Controller
{
    public function __construct(private TemplateService $service) {}

    public function index()
    {
        $templates = Template::orderBy('created_at', 'desc')->paginate(15);
        return view('admin.platform.templates.index', compact('templates'));
    }

    public function create()
    {
        return view('admin.platform.templates.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:200',
            'category' => 'required|in:system,security,marketing,transactional',
            'description' => 'nullable|string',
            'sms_content' => 'nullable|string',
            'email_content' => 'nullable|string',
            'whatsapp_content' => 'nullable|string',
            'push_content' => 'nullable|string',
            'variables' => 'nullable|array',
            'status' => 'required|in:draft,published',
        ]);

        $data['channels'] = [
            'sms' => $data['sms_content'] ?? null,
            'email' => $data['email_content'] ?? null,
            'whatsapp' => $data['whatsapp_content'] ?? null,
            'push' => $data['push_content'] ?? null,
        ];
        unset($data['sms_content'], $data['email_content'], $data['whatsapp_content'], $data['push_content']);

        $this->service->create($data);
        return redirect()->route('admin.templates.index')->with('success', 'Template created.');
    }

    public function edit(Template $template)
    {
        return view('admin.platform.templates.edit', compact('template'));
    }

    public function update(Request $request, Template $template)
    {
        $data = $request->validate([
            'name' => 'required|string|max:200',
            'category' => 'required|in:system,security,marketing,transactional',
            'description' => 'nullable|string',
            'sms_content' => 'nullable|string',
            'email_content' => 'nullable|string',
            'whatsapp_content' => 'nullable|string',
            'push_content' => 'nullable|string',
            'variables' => 'nullable|array',
            'status' => 'required|in:draft,published',
        ]);

        $data['channels'] = [
            'sms' => $data['sms_content'] ?? null,
            'email' => $data['email_content'] ?? null,
            'whatsapp' => $data['whatsapp_content'] ?? null,
            'push' => $data['push_content'] ?? null,
        ];
        unset($data['sms_content'], $data['email_content'], $data['whatsapp_content'], $data['push_content']);

        $this->service->update($template, $data);
        return redirect()->route('admin.templates.index')->with('success', 'Template updated.');
    }

    public function destroy(Template $template)
    {
        $this->service->delete($template);
        return redirect()->route('admin.templates.index')->with('success', 'Template deleted.');
    }
}
