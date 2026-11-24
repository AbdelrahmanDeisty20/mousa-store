<x-layout bodyClass="g-sidenav-show bg-gray-200">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notiflix@3.2.6/dist/notiflix-3.2.6.min.css" />

    <x-navbars.sidebar activePage="offers"></x-navbars.sidebar>

    <div class="main-content position-relative bg-gray-100 max-height-vh-100 h-100">
        <x-navbars.navs.auth titlePage="Edit Offer"></x-navbars.navs.auth>

        <div class="container-fluid px-2 px-md-4">
            <div class="page-header min-height-300 border-radius-xl mt-4"
                 style="background-image: url('https://images.unsplash.com/photo-1531512073830-ba890ca4eba2');">
                <span class="mask bg-gradient-primary opacity-6"></span>
            </div>

            <div class="card card-body mx-3 mx-md-4 mt-n6">
                <div class="card card-plain h-100">
                    <div class="card-header pb-0 p-3">
                        <h6 class="mb-3">{{ __('Edit Offer') }}</h6>
                    </div>

                    <div class="card-body p-3">
                        <form id="updateOfferForm">
                            @csrf
                            @method('PUT')

                            <div class="row">

                                {{-- Product Variant (Property) --}}
                                <div class="mb-3 col-md-6">
                                    <label class="form-label">{{ __('Product Variant') }}</label>
                                    <select name="property_id" class="form-control border border-2 p-2">
                                        <option value="">{{ __('-- Select Product Variant --') }}</option>
                                        @foreach ($properties as $property)
                                            <option value="{{ $property->id }}"
                                                data-price="{{ $property->price }}"
                                                {{ $offer->property_id == $property->id ? 'selected' : '' }}>
                                                {{ $property->product->name_en ?? 'N/A' }}
                                                @if($property->variantAttributes && $property->variantAttributes->count() > 0)
                                                    (
                                                    @foreach($property->variantAttributes ?? [] as $attr)
                                                        {{ $attr->key_en }}: {{ $attr->value_en }}{{ !$loop->last ? ', ' : '' }}
                                                    @endforeach
                                                    )
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="text-danger error-property_id"></span>
                                </div>

                                {{-- Discount --}}
                                <div class="mb-3 col-md-6" id="discount_container">
                                    <label class="form-label">{{ __('Discount (%)') }} <span id="price_info" class="text-xs text-primary"></span></label>
                                    <input type="number" step="0.01" name="discount_price"
                                           class="form-control border border-2 p-2"
                                           value="{{ old('discount_price', $offer->discount_price ?? $offer->discount) }}">
                                    <span class="text-danger error-discount_price"></span>
                                </div>

                                {{-- Dates --}}
                                <div class="mb-3 col-md-6">
                                    <label class="form-label">{{ __('Start Date') }}</label>
                                    <input type="datetime-local" name="start"
                                           class="form-control border border-2 p-2"
                                           value="{{ old('start', \Carbon\Carbon::parse($offer->start)->format('Y-m-d\TH:i')) }}">
                                    <span class="text-danger error-start"></span>
                                </div>

                                <div class="mb-3 col-md-6">
                                    <label class="form-label">{{ __('End Date') }}</label>
                                    <input type="datetime-local" name="end"
                                           class="form-control border border-2 p-2"
                                           value="{{ old('end', \Carbon\Carbon::parse($offer->end)->format('Y-m-d\TH:i')) }}">
                                    <span class="text-danger error-end"></span>
                                </div>

                            </div>

                            <button type="submit" class="btn bg-gradient-dark mt-3">
                                {{ __('Update') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <x-footers.auth></x-footers.auth>
    </div>

    <x-plugins></x-plugins>

    @push('js')
        <script src="https://cdn.jsdelivr.net/npm/notiflix@3.2.6/dist/notiflix-3.2.6.min.js"></script>

        <script>
            $(document).ready(function () {
                function updatePriceInfo() {
                    let selected = $('select[name="property_id"] option:selected');
                    let price = selected.data('price');

                    if (price !== undefined && price !== '') {
                        $('#price_info').text('({{ __('Price') }}: ' + price + ' {{ __('EGP') }})');
                    } else {
                        $('#price_info').text('');
                    }
                }

                $('select[name="property_id"]').on('change', updatePriceInfo);
                updatePriceInfo();

                $("#updateOfferForm").validate({
                    errorElement: "span",
                    rules: {
                        property_id: { required: true },
                        discount_price: { required: true, number: true },
                        start: { required: true },
                        end: { required: true }
                    },
                    messages: {
                        property_id: "{{ __('Product variant is required') }}",
                        discount_price: {
                            required: "{{ __('Discount is required') }}",
                            number: "{{ __('Must be a number') }}"
                        },
                        start: "{{ __('Start date is required') }}",
                        end: "{{ __('End date is required') }}"
                    },
                    errorPlacement: function(error, element) {
                        let name = element.attr("name");
                        $(".error-" + name).html(error);
                    },
                    submitHandler: function(form) {
                        $('span[class^="error-"]').html('');
                        let btn = $(form).find('button[type="submit"]');
                        btn.prop('disabled', true).text('Updating...');

                        $.ajax({
                            url: "{{ route('offers.update', $offer->id) }}",
                            type: "POST",
                            data: new FormData(form),
                            processData: false,
                            contentType: false,
                            headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() },
                            success: function(res) {
                                Notiflix.Notify.success(res.message || 'Offer updated successfully');
                                setTimeout(() => {
                                    window.location.href = "{{ route('offers.index') }}";
                                }, 700);
                            },
                            error: function(xhr) {
                                if (xhr.status === 422) {
                                    $.each(xhr.responseJSON.errors, function(key, val) {
                                        $('.error-' + key).text(val[0]);
                                    });
                                    Notiflix.Notify.failure('Please fix validation errors.');
                                } else {
                                    Notiflix.Notify.failure('Something went wrong');
                                }
                            },
                            complete: function() {
                                btn.prop('disabled', false).text('Update');
                            }
                        });
                    }
                });
            });
        </script>
    @endpush
</x-layout>
