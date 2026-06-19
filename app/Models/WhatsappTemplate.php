<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappTemplate extends Model
{
    protected $fillable = [
        'name', 'body', 'meta_template_name', 'language', 'category',
        'header_type', 'header_text', 'footer_text', 'buttons',
        'meta_template_id', 'meta_status', 'meta_rejected_reason',
    ];

    protected $casts = [
        'buttons' => 'array',
    ];

    /** Placeholders the admin can use in a template body. */
    public const VARIABLES = [
        'name', 'phone', 'service', 'package', 'date', 'time', 'location', 'specialist', 'address',
    ];

    /** Realistic example values Meta requires when submitting a template with variables. */
    public const SAMPLES = [
        'name' => 'Aisha',
        'phone' => '0123456789',
        'service' => 'Gel Manicure',
        'package' => 'Signature Glow',
        'date' => '25 Jun 2026',
        'time' => '2:30 PM',
        'location' => 'In-salon',
        'specialist' => 'Aisyah',
        'address' => '12 Jln Ampang',
    ];

    /** Render the body, replacing {{key}} with values from $data (for plain-text sends). */
    public function render(array $data): string
    {
        return preg_replace_callback('/\{\{\s*(\w+)\s*\}\}/', function ($m) use ($data) {
            return (string) ($data[$m[1]] ?? '');
        }, $this->body);
    }

    /** Body placeholder values in order of appearance (for Meta template body params). */
    public function orderedParams(array $data): array
    {
        preg_match_all('/\{\{\s*(\w+)\s*\}\}/', $this->body, $matches);

        return array_map(fn ($key) => (string) ($data[$key] ?? ''), $matches[1]);
    }

    public function metaName(): string
    {
        $source = filled($this->meta_template_name) ? $this->meta_template_name : $this->name;
        $slug = preg_replace('/[^a-z0-9]+/', '_', strtolower($source));

        return trim($slug, '_') ?: 'template';
    }

    /** Convert named {{x}} -> positional {{1}}, returning [text, varNames]. */
    protected function positionalize(string $text): array
    {
        preg_match_all('/\{\{\s*(\w+)\s*\}\}/', $text, $m);
        $i = 0;
        $out = preg_replace_callback('/\{\{\s*(\w+)\s*\}\}/', fn () => '{{' . (++$i) . '}}', $text);

        return [$out, $m[1]];
    }

    /** Build the full Meta `components` array (header, body, footer, buttons). */
    public function toMetaComponents(): array
    {
        $components = [];

        if ($this->header_type === 'text' && filled($this->header_text)) {
            $components[] = ['type' => 'HEADER', 'format' => 'TEXT', 'text' => $this->header_text];
        }

        [$bodyText, $bodyVars] = $this->positionalize($this->body);
        $body = ['type' => 'BODY', 'text' => $bodyText];
        if (! empty($bodyVars)) {
            $body['example'] = ['body_text' => [array_map(fn ($n) => self::SAMPLES[$n] ?? 'Sample', $bodyVars)]];
        }
        $components[] = $body;

        if (filled($this->footer_text)) {
            $components[] = ['type' => 'FOOTER', 'text' => $this->footer_text];
        }

        $buttons = collect($this->buttons ?? [])
            ->filter(fn ($b) => filled($b['text'] ?? null))
            ->map(function ($b) {
                $type = $b['type'] ?? 'QUICK_REPLY';
                if ($type === 'URL') {
                    return ['type' => 'URL', 'text' => $b['text'], 'url' => $b['url'] ?? ''];
                }
                if ($type === 'PHONE_NUMBER') {
                    return ['type' => 'PHONE_NUMBER', 'text' => $b['text'], 'phone_number' => $b['phone'] ?? ''];
                }

                return ['type' => 'QUICK_REPLY', 'text' => $b['text']];
            })
            ->values()
            ->all();

        if (! empty($buttons)) {
            $components[] = ['type' => 'BUTTONS', 'buttons' => $buttons];
        }

        return $components;
    }

    /** Only send via the official Meta template once it's approved. */
    public function usesMetaTemplate(): bool
    {
        return filled($this->meta_template_name) && $this->meta_status === 'approved';
    }
}
