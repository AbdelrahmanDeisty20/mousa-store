@php
    $pageModel = $page ?? null;
    $sectionsAr = old('sections_ar', $pageModel?->sections_ar ?? [['heading' => '', 'body' => '', 'points' => []]]);
    $sectionsEn = old('sections_en', $pageModel?->sections_en ?? [['heading' => '', 'body' => '', 'points' => []]]);
@endphp

@if ($errors->any())
    <div class="alert alert-danger text-white">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row">
    <div class="mb-3 col-md-6">
        <label class="form-label fw-bold">{{ __('Slug') }} *</label>
        <input type="text" name="slug" class="form-control border border-2 p-2"
            placeholder="privacy-policy"
            value="{{ old('slug', $pageModel?->slug) }}" required>
        <small class="text-muted">{{ __('Allowed characters: lowercase letters, numbers, and hyphens (-)') }}</small>
    </div>

    <div class="mb-3 col-md-6 d-flex align-items-center pt-4">
        <div class="form-check form-switch">
            <input type="hidden" name="is_active" value="0">
            <input class="form-check-input" type="checkbox" id="isActiveSwitch" name="is_active" value="1"
                {{ old('is_active', $pageModel ? ($pageModel->is_active ? '1' : '0') : '1') == '1' ? 'checked' : '' }}>
            <label class="form-check-label fw-bold" for="isActiveSwitch">{{ __('Active') }}</label>
        </div>
    </div>

    <div class="mb-3 col-md-6">
        <label class="form-label fw-bold">{{ __('Title (AR)') }} *</label>
        <input type="text" name="title_ar" class="form-control border border-2 p-2"
            value="{{ old('title_ar', $pageModel?->title_ar) }}" required>
    </div>

    <div class="mb-3 col-md-6">
        <label class="form-label fw-bold">{{ __('Title (EN)') }} *</label>
        <input type="text" name="title_en" class="form-control border border-2 p-2"
            value="{{ old('title_en', $pageModel?->title_en) }}" required>
    </div>

    <div class="mb-3 col-md-6">
        <label class="form-label fw-bold">{{ __('Subtitle (AR)') }}</label>
        <textarea name="subtitle_ar" class="form-control border border-2 p-2" rows="2">{{ old('subtitle_ar', $pageModel?->subtitle_ar) }}</textarea>
    </div>

    <div class="mb-3 col-md-6">
        <label class="form-label fw-bold">{{ __('Subtitle (EN)') }}</label>
        <textarea name="subtitle_en" class="form-control border border-2 p-2" rows="2">{{ old('subtitle_en', $pageModel?->subtitle_en) }}</textarea>
    </div>
</div>

<hr class="my-4">

@foreach (['ar' => ['label' => __('Sections (Arabic)'), 'rows' => $sectionsAr], 'en' => ['label' => __('Sections (English)'), 'rows' => $sectionsEn]] as $langKey => $cfg)
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 fw-bold">{{ $cfg['label'] }}</h6>
            <button type="button" class="btn btn-sm bg-gradient-info mb-0 add-section-btn" data-lang="{{ $langKey }}">
                <i class="material-icons text-sm">add</i> {{ __('Add Section') }}
            </button>
        </div>

        <div id="sections-container-{{ $langKey }}">
            @foreach ($cfg['rows'] as $idx => $sec)
                @php
                    $pointsText = $sec['points_text'] ?? (isset($sec['points']) && is_array($sec['points']) ? implode("\n", $sec['points']) : '');
                @endphp
                <div class="card border mb-3 section-card">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-gradient-secondary section-badge">#{{ $idx + 1 }}</span>
                            <button type="button" class="btn btn-sm btn-outline-danger mb-0 remove-section-btn">{{ __('Remove') }}</button>
                        </div>

                        <div class="mb-2">
                            <label class="form-label">{{ __('Heading') }}</label>
                            <input type="text" name="sections_{{ $langKey }}[{{ $idx }}][heading]"
                                class="form-control border border-2 p-2" value="{{ $sec['heading'] ?? '' }}">
                        </div>

                        <div class="mb-2">
                            <label class="form-label">{{ __('Body') }}</label>
                            <textarea name="sections_{{ $langKey }}[{{ $idx }}][body]"
                                class="form-control border border-2 p-2" rows="2">{{ $sec['body'] ?? '' }}</textarea>
                        </div>

                        <div>
                            <label class="form-label">{{ __('Bullet Points (one per line)') }}</label>
                            <textarea name="sections_{{ $langKey }}[{{ $idx }}][points_text]"
                                class="form-control border border-2 p-2" rows="3">{{ $pointsText }}</textarea>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endforeach

<button type="submit" class="btn bg-gradient-dark">{{ __('Save') }}</button>
<a href="{{ route('pages.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>

@push('js')
    <script>
        (function () {
            function reindexContainer(lang) {
                const container = document.getElementById('sections-container-' + lang);
                if (!container) return;
                container.querySelectorAll('.section-card').forEach((card, idx) => {
                    const badge = card.querySelector('.section-badge');
                    if (badge) badge.textContent = '#' + (idx + 1);
                    card.querySelectorAll('[name]').forEach((el) => {
                        el.name = el.name.replace(new RegExp('sections_' + lang + '\\[\\d+\\]'), 'sections_' + lang + '[' + idx + ']');
                    });
                });
            }

            document.querySelectorAll('.add-section-btn').forEach((btn) => {
                btn.addEventListener('click', function () {
                    const lang = this.dataset.lang;
                    const container = document.getElementById('sections-container-' + lang);
                    const idx = container.querySelectorAll('.section-card').length;
                    const html = `
                        <div class="card border mb-3 section-card">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-gradient-secondary section-badge">#${idx + 1}</span>
                                    <button type="button" class="btn btn-sm btn-outline-danger mb-0 remove-section-btn">{{ __('Remove') }}</button>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">{{ __('Heading') }}</label>
                                    <input type="text" name="sections_${lang}[${idx}][heading]" class="form-control border border-2 p-2">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">{{ __('Body') }}</label>
                                    <textarea name="sections_${lang}[${idx}][body]" class="form-control border border-2 p-2" rows="2"></textarea>
                                </div>
                                <div>
                                    <label class="form-label">{{ __('Bullet Points (one per line)') }}</label>
                                    <textarea name="sections_${lang}[${idx}][points_text]" class="form-control border border-2 p-2" rows="3"></textarea>
                                </div>
                            </div>
                        </div>`;
                    container.insertAdjacentHTML('beforeend', html);
                });
            });

            document.addEventListener('click', function (e) {
                if (e.target.classList.contains('remove-section-btn')) {
                    const card = e.target.closest('.section-card');
                    const container = card.parentElement;
                    const lang = container.id.replace('sections-container-', '');
                    card.remove();
                    reindexContainer(lang);
                }
            });
        })();
    </script>
@endpush
