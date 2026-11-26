<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'title_ar',
        'title_en',
        'subtitle_ar',
        'subtitle_en',
        'sections_ar',
        'sections_en',
        'is_active',
    ];

    protected $casts = [
        'sections_ar' => 'array',
        'sections_en' => 'array',
        'is_active'   => 'boolean',
    ];

    /**
     * Format localized page payload matching frontend CMS contract.
     *
     * @return array<string, mixed>
     */
    public function toLocalizedPayload(?string $locale = null, bool $includeSections = true): array
    {
        $lang = ($locale ?? app()->getLocale()) === 'ar' ? 'ar' : 'en';
        $fallback = $lang === 'ar' ? 'en' : 'ar';

        $title = $this->{"title_{$lang}"} ?: $this->{"title_{$fallback}"};
        $subtitle = $this->{"subtitle_{$lang}"} ?: ($this->{"subtitle_{$fallback}"} ?? '');

        $payload = [
            'slug'       => $this->slug,
            'title'      => $title,
            'subtitle'   => $subtitle,
            'updated_at' => $this->updated_at?->toDateString(),
        ];

        if ($includeSections) {
            $rawSections = $this->{"sections_{$lang}"};
            if (empty($rawSections)) {
                $rawSections = $this->{"sections_{$fallback}"} ?? [];
            }

            $payload['sections'] = collect($rawSections)
                ->map(function ($section) {
                    $points = $section['points'] ?? [];
                    if (! is_array($points)) {
                        $points = [];
                    }

                    return [
                        'heading' => (string) ($section['heading'] ?? ''),
                        'body'    => (string) ($section['body'] ?? ''),
                        'points'  => array_values(array_map('strval', array_filter($points, fn ($p) => trim((string) $p) !== ''))),
                    ];
                })
                ->values()
                ->all();
        }

        return $payload;
    }
}
