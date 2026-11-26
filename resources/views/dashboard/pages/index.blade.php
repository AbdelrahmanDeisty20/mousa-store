<x-layout bodyClass="g-sidenav-show bg-gray-200">
    <x-navbars.sidebar activePage="pages"></x-navbars.sidebar>

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <x-navbars.navs.auth titlePage="{{ __('Policy Pages') }}"></x-navbars.navs.auth>

        <div class="container-fluid py-4">
            @if (session('success'))
                <div class="alert alert-success text-white alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="row">
                <div class="col-12">
                    <div class="card my-4">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2 d-flex justify-content-between align-items-center flex-wrap">
                            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 flex-grow-1">
                                <h6 class="text-white text-capitalize ps-3">{{ __('Policy Pages Table') }}</h6>
                            </div>
                        </div>

                        <div class="me-3 ms-3 my-3 text-end">
                            <a class="btn bg-gradient-dark mb-0" href="{{ route('pages.create') }}">
                                <i class="material-icons text-sm">add</i>&nbsp;&nbsp;{{ __('Add New Page') }}
                            </a>
                        </div>

                        <div class="card-body px-0 pb-2">
                            <div class="table-responsive p-0">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-4">{{ __('Slug') }}</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">{{ __('Title (AR)') }}</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">{{ __('Title (EN)') }}</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">{{ __('Status') }}</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">{{ __('Updated At') }}</th>
                                            <th class="text-secondary opacity-7"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($pages as $page)
                                            <tr>
                                                <td class="ps-4"><code>{{ $page->slug }}</code></td>
                                                <td>{{ $page->title_ar }}</td>
                                                <td>{{ $page->title_en }}</td>
                                                <td class="text-center">
                                                    @if ($page->is_active)
                                                        <span class="badge bg-gradient-success">{{ __('Active') }}</span>
                                                    @else
                                                        <span class="badge bg-gradient-secondary">{{ __('Inactive') }}</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">{{ $page->updated_at?->format('Y-m-d') }}</td>
                                                <td class="align-middle text-nowrap">
                                                    <a class="btn btn-success btn-link mb-0" href="{{ route('pages.edit', $page->id) }}" title="{{ __('Edit') }}">
                                                        <i class="material-icons">edit</i>
                                                    </a>
                                                    <form action="{{ route('pages.destroy', $page->id) }}" method="POST" class="d-inline delete-page-form">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-link mb-0" title="{{ __('Delete') }}">
                                                            <i class="material-icons">delete</i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-4">{{ __('No policy pages found.') }}</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="px-4 mt-3">
                                {{ $pages->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <x-footers.auth></x-footers.auth>
        </div>
    </main>

    @push('js')
        <script>
            document.querySelectorAll('.delete-page-form').forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    Swal.fire({
                        title: "{{ __('Are you sure?') }}",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: "{{ __('Delete') }}",
                        cancelButtonText: "{{ __('Cancel') }}"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        </script>
    @endpush
</x-layout>
