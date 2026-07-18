<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Template extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'category', 'description', 'channels', 'variables', 'status', 'version',
    ];

    protected $casts = [
        'channels' => 'array',
        'variables' => 'array',
    ];

    public function render(string $channel, array $data = []): string
    {
        $content = $this->channels[$channel] ?? $this->channels['sms'] ?? '';
        
        foreach ($data as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
        }
        
        return $content;
    }

    public function validateVariables(array $data): array
    {
        $missing = [];
        foreach ($this->variables ?? [] as $var) {
            if (!isset($data[$var])) {
                $missing[] = $var;
            }
        }
        return $missing;
    }
}
