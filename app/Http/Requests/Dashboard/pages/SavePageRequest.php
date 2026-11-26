<?php

namespace App\Http\Requests\Dashboard\pages;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SavePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $pageId = $this->route('page')?->id;

        return [
            'slug'                     => ['required', 'string', 'max:100', 'alpha_dash', Rule::unique('pages', 'slug')->ignore($pageId)],
            'title_ar'                 => ['required', 'string', 'max:255'],
            'title_en'                 => ['required', 'string', 'max:255'],
            'subtitle_ar'              => ['nullable', 'string'],
            'subtitle_en'              => ['nullable', 'string'],
            'is_active'                => ['nullable', 'boolean'],
            'sections_ar'              => ['nullable', 'array'],
            'sections_ar.*.heading'    => ['nullable', 'string', 'max:255'],
            'sections_ar.*.body'       => ['nullable', 'string'],
            'sections_ar.*.points_text'=> ['nullable', 'string'],
            'sections_ar.*.points'     => ['nullable', 'array'],
            'sections_en'              => ['nullable', 'array'],
            'sections_en.*.heading'    => ['nullable', 'string', 'max:255'],
            'sections_en.*.body'       => ['nullable', 'string'],
            'sections_en.*.points_text'=> ['nullable', 'string'],
            'sections_en.*.points'     => ['nullable', 'array'],
        ];
    }

    /**
     * Build clean attributes ready for Page::create() / Page::update().
     *
     * @return array<string, mixed>
     */
    public function normalizedData(): array
    {
        return [
            'slug'        => strtolower(trim((string) $this->input('slug'))),
            'title_ar'    => trim((string) $this->input('title_ar')),
            'title_en'    => trim((string) $this->input('title_en')),
            'subtitle_ar' => $this->input('subtitle_ar'),
            'subtitle_en' => $this->input('subtitle_en'),
            'is_active'   => $this->boolean('is_active'),
            'sections_ar' => $this->normalizeSections($this->input('sections_ar', [])),
            'sections_en' => $this->normalizeSections($this->input('sections_en', [])),
        ];
    }

    /**
     * Normalize dynamic sections and split line-separated points into clean arrays.
     *
     * @param  array<int|string, mixed>  $rawSections
     * @return array<int, array{heading: string, body: string, points: array<int, string>}>
     */
    private function normalizeSections(array $rawSections): array
    {
        $normalized = [];

        foreach ($rawSections as $section) {
            if (! is_array($section)) {
                continue;
            }

            $heading = trim((string) ($section['heading'] ?? ''));
            $body = trim((string) ($section['body'] ?? ''));

            if (isset($section['points']) && is_array($section['points'])) {
                $points = array_values(array_filter(
                    array_map(fn ($p) => trim((string) $p), $section['points']),
                    fn ($p) => $p !== ''
                ));
            } else {
                $rawLines = preg_split('/\r\n|\r|\n/', (string) ($section['points_text'] ?? '')) ?: [];
                $points = array_values(array_filter(
                    array_map('trim', $rawLines),
                    fn ($line) => $line !== ''
                ));
            }

            if ($heading === '' && $body === '' && $points === []) {
                continue;
            }

            $normalized[] = [
                'heading' => $heading,
                'body'    => $body,
                'points'  => $points,
            ];
        }

        return $normalized;
    }
}
