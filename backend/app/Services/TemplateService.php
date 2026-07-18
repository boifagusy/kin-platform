<?php
namespace App\Services;
use App\Models\Template;

class TemplateService
{
    public function create(array $data): Template
    {
        return Template::create($data);
    }

    public function update(Template $template, array $data): Template
    {
        $data['version'] = $template->version + 1;
        $template->update($data);
        return $template;
    }

    public function delete(Template $template): void
    {
        $template->delete();
    }

    public function preview(string $channel, array $data = []): array
    {
        $templates = Template::where('status', 'published')
            ->whereJsonContains('channels->' . $channel, '')
            ->orWhereNotNull('channels')
            ->get();

        $previews = [];
        foreach ($templates as $t) {
            if (isset($t->channels[$channel]) && $t->channels[$channel]) {
                $previews[] = [
                    'id' => $t->id,
                    'name' => $t->name,
                    'rendered' => $t->render($channel, $data),
                ];
            }
        }
        return $previews;
    }
}
