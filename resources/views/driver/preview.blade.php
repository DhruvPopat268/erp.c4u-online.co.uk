@extends('layouts.contractheader')
@php
$SITE_RTL = !empty($settings['SITE_RTL'] ) ? $settings['SITE_RTL']  : 'off';

@endphp
<style>
    .datetested {
   color: #505a5f;
   font-size: 1.1875rem;
   line-height: 1.3157894737;
   font-family: "GDS Transport", arial, sans-serif;
   -webkit-font-smoothing: antialiased;
   -moz-osx-font-smoothing: grayscale;
   font-weight: 400;
   display: block;
   text-align: left;
}

.datetestedvalue {
   margin-bottom: 5px !important;
   font-size: 1.1875rem;
   line-height: 1.3157894737;
   color: #0b0c0c;
   font-family: "GDS Transport", arial, sans-serif;
   -webkit-font-smoothing: antialiased;
   -moz-osx-font-smoothing: grayscale;
   font-weight: 700;
   display: block;
   margin-top: 0;
}

#pass-fail {
   color: #00703c;
   margin-bottom: 0 !important;
   font-size: 2rem;
   line-height: 1.0416666667;
   font-family: "GDS Transport", arial, sans-serif;
   -webkit-font-smoothing: antialiased;
   -moz-osx-font-smoothing: grayscale;
   font-weight: 700;
   display: block;
   margin-top: -20px;
}

.datetestedmileage {
   color: #505a5f;
   font-size: 1.1875rem;
   line-height: 1.3157894737;
   font-family: "GDS Transport", arial, sans-serif;
   -webkit-font-smoothing: antialiased;
   -moz-osx-font-smoothing: grayscale;
   font-weight: 400;
   display: block;
   text-align: left;
   margin-left: 135%;
   margin-top: -40%;
}

.datetestedvaluemileage {
   margin-bottom: 5px !important;
   font-size: 1.1875rem;
   line-height: 1.3157894737;
   color: #0b0c0c;
   font-family: "GDS Transport", arial, sans-serif;
   -webkit-font-smoothing: antialiased;
   -moz-osx-font-smoothing: grayscale;
   font-weight: 700;
   display: block;
   margin-top: -92px;
   margin-left: 135%;


}

.datetestedcertificate {
   color: #505a5f;
   font-size: 1.1875rem;
   line-height: 1.3157894737;
   font-family: "GDS Transport", arial, sans-serif;
   -webkit-font-smoothing: antialiased;
   -moz-osx-font-smoothing: grayscale;
   font-weight: 400;
   display: block;
   text-align: left;
   margin-left: 418%;
   margin-top: -76%;

}

.datetestedvaluecertificate {
   margin-bottom: 5px !important;
   font-size: 1.1875rem;
   line-height: 1.3157894737;
   color: #0b0c0c;
   font-family: "GDS Transport", arial, sans-serif;
   -webkit-font-smoothing: antialiased;
   -moz-osx-font-smoothing: grayscale;
   font-weight: 700;
   display: block;
   margin-top: -130px;
   margin-left: 419%;

}

.datetestedlocation {
   color: #505a5f;
   font-size: 1.1875rem;
   line-height: 1.3157894737;
   font-family: "GDS Transport", arial, sans-serif;
   -webkit-font-smoothing: antialiased;
   -moz-osx-font-smoothing: grayscale;
   font-weight: 400;
   display: block;
   text-align: left;
   margin-left: 136%;
   margin-top: 21%;

}

.datetestedvalueloction {
   margin-bottom: 5px !important;
   font-size: 1.1875rem;
   line-height: 1.3157894737;
   color: #0b0c0c;
   font-family: "GDS Transport", arial, sans-serif;
   -webkit-font-smoothing: antialiased;
   -moz-osx-font-smoothing: grayscale;
   font-weight: 700;
   display: block;
   margin-top: 23%;
   margin-left: 137%;
}

.datetestedexpirydate {
   color: #505a5f;
   font-size: 1.1875rem;
   line-height: 1.3157894737;
   font-family: "GDS Transport", arial, sans-serif;
   -webkit-font-smoothing: antialiased;
   -moz-osx-font-smoothing: grayscale;
   font-weight: 400;
   display: block;
   text-align: left;
   margin-left: 418%;
   margin-top: 26%;

}

.datetestedvalueexpirydate {
   margin-bottom: 5px !important;
   font-size: 1.1875rem;
   line-height: 1.3157894737;
   color: #0b0c0c;
   font-family: "GDS Transport", arial, sans-serif;
   -webkit-font-smoothing: antialiased;
   -moz-osx-font-smoothing: grayscale;
   font-weight: 700;
   display: block;
   margin-top: 56px;
   margin-left: 419%;

}

.heading h3 {
   font-size: 1.1875rem;
   line-height: 1.3157894737;
   font-family: "GDS Transport", arial, sans-serif;
   -webkit-font-smoothing: antialiased;
   -moz-osx-font-smoothing: grayscale;
   font-weight: 400;
   display: block;
   color: #505a5f;
   margin-left: 35%;
}

.heading li {
   font-weight: 700 !important;
   text-align: -webkit-match-parent;
   list-style-type: disc;
   font-size: 1.1875rem;
   line-height: 1.3157894737;
   font-family: "GDS Transport", arial, sans-serif;
   -webkit-font-smoothing: antialiased;
   color: #0b0c0c;
   margin-top: -17px;
   margin-bottom: 15px;
   padding-left: 0;
   margin-left: 35%;
   width: 60ch;
}

.heading h1 {
   text-decoration: underline;
   color: #1d70b8;
   cursor: pointer;
   list-style: inside disclosure-closed;
   font-size: 1.1875rem;
   line-height: 1.3157894737;
   font-family: "GDS Transport", arial, sans-serif;
   -webkit-font-smoothing: antialiased;
   -moz-osx-font-smoothing: grayscale;
   font-weight: 400;
   margin-bottom: 20px;
   display: block;
   margin-left: 27%;
}

.heading p {
   display: block;
   margin-top: 0;
   margin-bottom: 20px;
   font-size: 1.1875rem;
   line-height: 1.3157894737;
   font-family: "GDS Transport", arial, sans-serif;
   -webkit-font-smoothing: antialiased;
   -moz-osx-font-smoothing: grayscale;
   font-weight: 400;
   color: #0b0c0c;
   margin-bottom: 20px;
   margin-left: 27%;

}

.govuk-details[open]>.govuk-details__summary:before {
   display: block;
   width: 0;
   height: 0;
   border-style: solid;
   border-color: rgba(0, 0, 0, 0);
   -webkit-clip-path: polygon(0% 0%, 50% 100%, 100% 0%);
   clip-path: polygon(0% 0%, 50% 100%, 100% 0%);
   border-width: 12.124px 7px 0 7px;
   border-top-color: inherit;
}

.govuk-details__summary:before {
   content: "";
   position: absolute;
   top: -1px;
   bottom: 0;
   left: 0;
   margin: auto;
   display: block;
   width: 0;
   height: 0;
   border-style: solid;
   border-color: rgba(0, 0, 0, 0);
   -webkit-clip-path: polygon(0% 0%, 100% 50%, 0% 100%);
   clip-path: polygon(0% 0%, 100% 50%, 0% 100%);
   border-width: 7px 0 7px 12.124px;
   border-left-color: inherit;
}

.govuk-details__summary {
   display: inline-block;
   position: relative;
   margin-bottom: 5px;
   padding-left: 25px;
   color: #1d70b8;
   cursor: pointer;
}

details[open]>summary:first-of-type {
   list-style-type: disclosure-open;
}

details>summary:first-of-type {
   list-style: inside disclosure-closed;
}

.govuk-details {
   font-family: "GDS Transport", arial, sans-serif;
   -webkit-font-smoothing: antialiased;
   -moz-osx-font-smoothing: grayscale;
   font-weight: 400;
   font-size: 1.1875rem;
   line-height: 1.3157894737;
   line-height: 1.25;
   color: #0b0c0c;
   margin-bottom: 20px;
   display: block;
   margin-left: 3%;
}

.govuk-details__text {
   padding-top: 15px;
   padding-bottom: 15px;
   padding-left: 20px;
   border-left: 5px solid #b1b4b6;
}

.govuk-details__summary-text {
   text-decoration: underline;
}

.dvsa-vrm {
   display: inline-block;
   min-width: 150px;
   font: 30px UK-VRM, Verdana, sans-serif;
   padding: .4em .2em;
   text-align: center;
   background-color: #fd0;
   border-radius: .25em;
   text-transform: uppercase;
}

.govuk-\!-margin-bottom-1 {
   margin-bottom: 5px !important;
}

.govuk-heading-xl {
   color: #0b0c0c;
   font-family: "GDS Transport", arial, sans-serif;
   -webkit-font-smoothing: antialiased;
   -moz-osx-font-smoothing: grayscale;
   font-weight: 700;
   font-size: 32px;
   font-size: 2rem;
   line-height: 1.09375;
   display: block;
   margin-top: 0;
   margin-bottom: 30px;
}

.govuk-grid-column-one-third {
   box-sizing: border-box;
   width: 100%;
   padding: 0 15px;
}

.govuk-grid-column-one-third2 {
   box-sizing: border-box;
   width: 100%;
   padding: 0 15px;
}

.govuk-caption-m {
   font-family: "GDS Transport", arial, sans-serif;
   -webkit-font-smoothing: antialiased;
   -moz-osx-font-smoothing: grayscale;
   font-weight: 400;
   font-size: 16px;
   font-size: 1rem;
   line-height: 1.25;
   display: block;
   color: #505a5f;
}

.govuk-caption-m2 {
   font-family: "GDS Transport", arial, sans-serif;
   -webkit-font-smoothing: antialiased;
   -moz-osx-font-smoothing: grayscale;
   font-weight: 400;
   font-size: 1.1875rem;
   line-height: 1.3157894737;
   display: block;
   color: #505a5f;
}

.govuk-caption-m3 {
   font-family: "GDS Transport", arial, sans-serif;
   -webkit-font-smoothing: antialiased;
   -moz-osx-font-smoothing: grayscale;
   font-weight: 400;
   font-size: 1.1875rem;
   line-height: 1.3157894737;
   display: block;
   color: #505a5f;
}

.govuk-heading-m {
   color: #0b0c0c;
   font-family: "GDS Transport", arial, sans-serif;
   -webkit-font-smoothing: antialiased;
   -moz-osx-font-smoothing: grayscale;
   font-weight: 700;
   font-size: 18px;
   font-size: 1.125rem;
   line-height: 1.1111111111;
   display: block;
   margin-top: 0;
   margin-bottom: 15px;
}

.govuk-heading-m2 {
   color: #0b0c0c;
   font-family: "GDS Transport", arial, sans-serif;
   -webkit-font-smoothing: antialiased;
   -moz-osx-font-smoothing: grayscale;
   font-weight: 700;
   font-size: 1.1875rem;
   line-height: 1.3157894737;
   display: block;
   margin-top: 0;
   margin-bottom: 15px;
}

.govuk-heading-m3 {
   color: #0b0c0c;
   font-family: "GDS Transport", arial, sans-serif;
   -webkit-font-smoothing: antialiased;
   -moz-osx-font-smoothing: grayscale;
   font-weight: 700;
   font-size: 1.1875rem;
   line-height: 1.3157894737;
   display: block;
   margin-top: 0;
   margin-bottom: 15px;
}

.govuk-caption-l {
   font-family: "GDS Transport", arial, sans-serif;
   -webkit-font-smoothing: antialiased;
   -moz-osx-font-smoothing: grayscale;
   font-weight: 400;
   font-size: 18px;
   font-size: 1.125rem;
   line-height: 1.1111111111;
   display: block;
   margin-bottom: 5px;
   color: #505a5f;
}

.govuk-heading-l {
   color: #0b0c0c;
   font-family: "GDS Transport", arial, sans-serif;
   -webkit-font-smoothing: antialiased;
   -moz-osx-font-smoothing: grayscale;
   font-weight: 700;
   font-size: 24px;
   font-size: 1.5rem;
   line-height: 1.0416666667;
   display: block;
   margin-top: 0;
   margin-bottom: 20px;
}

@media (min-width: 40.0625em) {
   .govuk-heading-xl {
       margin-bottom: 50px;
       font-size: 3rem;
       line-height: 1.0416666667;
   }

   .govuk-grid-column-one-third {
       width: 33.3333333333%;
       float: left;
   }

   .govuk-caption-m {
       font-size: 19px;
       font-size: 1.0875rem;
       line-height: 1.3157894737;
   }

   .govuk-caption-m2 {
       font-size: 1.1875rem;
       line-height: 1.3157894737;
   }

   .govuk-heading-m {
       margin-bottom: 20px;
       font-size: 1.1875rem;
       line-height: 1.25;
   }

   .govuk-heading-m2 {
       margin-bottom: 20px;
       font-size: 1.1875rem;
       line-height: 1.3157894737;
   }

   .govuk-caption-l {
       margin-bottom: 0;
       font-size: 1.5rem;
       line-height: 1.25;
   }

   .govuk-heading-l {
       margin-bottom: 30px;
       font-size: 1.25rem;
       line-height: 1.1111111111;
   }
}
</style>
@push('script-page')

<script>
    function closeScript() {
        setTimeout(function () {
            window.open(window.location, '_self').close();
        }, 1000);
    }

    $(window).on('load', function () {
        var element = document.getElementById('boxes');
        var opt = {
            filename: '{{App\Models\Utility::contractNumberFormat($driver->id)}}',
            image: {type: 'jpeg', quality: 1},
            html2canvas: {scale: 4, dpi: 72, letterRendering: true},
            jsPDF: {unit: 'in', format: 'A4'}
        };

        html2pdf().set(opt).from(element).save().then(closeScript);
    });

</script>

@endpush
@section('page-title')
    {{__('Contract')}}
@endsection
@section('title')

{{-- {{__('Contract')}} {{ '('. $driver->name .')' }} --}}

@endsection
@php
    use Carbon\Carbon;
@endphp

@section('content')
<div class="mt-3">
   <div class="row justify-content-center mb-3">
        <div class="col-sm-9 text-end me-2">
            <div class="all-button-box ">
                    <a href="{{ route('driver.download.allimages', ['driverId' => $driver->id]) }}" class="btn btn-sm btn-primary btn-icon" title="{{__('Download')}}" target="_blanks">
                        <i class="ti ti-download"></i>
                    </a>
            </div>
        </div>
    </div>


    <div class="row justify-content-center">
        <div class="col-sm-9">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <h5>Driving Licence</h5>
                        @foreach($driver->attachments ?? [] as $attachment)
                            @if((!empty($attachment->license_front) && file_exists(storage_path($attachment->license_front))) || (!empty($attachment->license_back) && file_exists(storage_path($attachment->license_back))))
                                <div class="card mb-5 shadow-none" style="border: 1px solid">
                                    <div class="px-3 py-3">
                                        <div class="row align-items-center">
                                            @if(!empty($attachment->license_front) && file_exists(storage_path($attachment->license_front)))
                                                <div class="col">
                                                    <img src="{{ asset('storage/' . $attachment->license_front) }}" alt="Front Image" class="img-fluid mb-2" style="max-width: 300px; height: auto;">
                                                </div>
                                            @endif
                                            @if(!empty($attachment->license_back) && file_exists(storage_path($attachment->license_back)))
                                                <div class="col">
                                                    <img src="{{ asset('storage/' . $attachment->license_back) }}" alt="Back Image" class="img-fluid mb-2" style="max-width: 300px; height: auto;">
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                    <div class="row">
                        <h5>CPC Card</h5>
                        @foreach($driver->attachments ?? [] as $attachment)
                            @if((!empty($attachment->cpc_card_front) && file_exists(storage_path($attachment->cpc_card_front))) || (!empty($attachment->cpc_card_back) && file_exists(storage_path($attachment->cpc_card_back))))
                                <div class="card mb-5 shadow-none" style="border: 1px solid">
                                    <div class="px-3 py-3">
                                        <div class="row align-items-center">
                                            @if(!empty($attachment->cpc_card_front) && file_exists(storage_path($attachment->cpc_card_front)))
                                                <div class="col">
                                                    <img src="{{ asset('storage/' . $attachment->cpc_card_front) }}" alt="Front Image" class="img-fluid mb-2" style="max-width: 300px; height: auto;">
                                                </div>
                                            @endif
                                            @if(!empty($attachment->cpc_card_back) && file_exists(storage_path($attachment->cpc_card_back)))
                                                <div class="col">
                                                    <img src="{{ asset('storage/' . $attachment->cpc_card_back) }}" alt="Back Image" class="img-fluid mb-2" style="max-width: 300px; height: auto;">
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                    <div class="row">
                        <h5>Tacho Card</h5>
                        @foreach($driver->attachments ?? [] as $attachment)
                            @if((!empty($attachment->tacho_card_front) && file_exists(storage_path($attachment->tacho_card_front))) || (!empty($attachment->tacho_card_back) && file_exists(storage_path($attachment->tacho_card_back))))
                                <div class="card mb-5 shadow-none" style="border: 1px solid">
                                    <div class="px-3 py-3">
                                        <div class="row align-items-center">
                                            @if(!empty($attachment->tacho_card_front) && file_exists(storage_path($attachment->tacho_card_front)))
                                                <div class="col">
                                                    <img src="{{ asset('storage/' . $attachment->tacho_card_front) }}" alt="Front Image" class="img-fluid mb-2" style="max-width: 300px; height: auto;">
                                                </div>
                                            @endif
                                            @if(!empty($attachment->tacho_card_back) && file_exists(storage_path($attachment->tacho_card_back)))
                                                <div class="col">
                                                    <img src="{{ asset('storage/' . $attachment->tacho_card_back) }}" alt="Back Image" class="img-fluid mb-2" style="max-width: 300px; height: auto;">
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                    <div class="row">
                        <h5>MPQC Card</h5>
                        @foreach($driver->attachments ?? [] as $attachment)
                            @if((!empty($attachment->mpqc_card_front) && file_exists(storage_path($attachment->mpqc_card_front))) || (!empty($attachment->mpqc_card_back) && file_exists(storage_path($attachment->mpqc_card_back))))
                                <div class="card mb-5 shadow-none" style="border: 1px solid">
                                    <div class="px-3 py-3">
                                        <div class="row align-items-center">
                                            @if(!empty($attachment->mpqc_card_front) && file_exists(storage_path($attachment->mpqc_card_front)))
                                                <div class="col">
                                                    <img src="{{ asset('storage/' . $attachment->mpqc_card_front) }}" alt="Front Image" class="img-fluid mb-2" style="max-width: 300px; height: auto;">
                                                </div>
                                            @endif
                                            @if(!empty($attachment->mpqc_card_back) && file_exists(storage_path($attachment->mpqc_card_back)))
                                                <div class="col">
                                                    <img src="{{ asset('storage/' . $attachment->mpqc_card_back) }}" alt="Back Image" class="img-fluid mb-2" style="max-width: 300px; height: auto;">
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                    <div class="row">
                        <h5>Level D / Cargo Operative Card</h5>
                        @foreach($driver->attachments ?? [] as $attachment)
                            @if((!empty($attachment->levelD_card_front) && file_exists(storage_path($attachment->levelD_card_front))) || (!empty($attachment->levelD_card_back) && file_exists(storage_path($attachment->levelD_card_back))))
                                <div class="card mb-5 shadow-none" style="border: 1px solid">
                                    <div class="px-3 py-3">
                                        <div class="row align-items-center">
                                            @if(!empty($attachment->levelD_card_front) && file_exists(storage_path($attachment->levelD_card_front)))
                                                <div class="col">
                                                    <img src="{{ asset('storage/' . $attachment->levelD_card_front) }}" alt="Front Image" class="img-fluid mb-2" style="max-width: 300px; height: auto;">
                                                </div>
                                            @endif
                                            @if(!empty($attachment->levelD_card_back) && file_exists(storage_path($attachment->levelD_card_back)))
                                                <div class="col">
                                                    <img src="{{ asset('storage/' . $attachment->levelD_card_back) }}" alt="Back Image" class="img-fluid mb-2" style="max-width: 300px; height: auto;">
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                    <div class="row">
                        <h5>One Card</h5>
                        @foreach($driver->attachments ?? [] as $attachment)
                            @if((!empty($attachment->one_card_front) && file_exists(storage_path($attachment->one_card_front))) || (!empty($attachment->one_card_back) && file_exists(storage_path($attachment->one_card_back))))
                                <div class="card mb-5  shadow-none" style="border: 1px solid">
                                    <div class="px-3 py-3">
                                        <div class="row align-items-center">
                                            @if(!empty($attachment->one_card_front) && file_exists(storage_path($attachment->one_card_front)))
                                                <div class="col">
                                                    <img src="{{ asset('storage/' . $attachment->one_card_front) }}" alt="Front Image" class="img-fluid mb-2" style="max-width: 300px; height: auto;">
                                                </div>
                                            @endif
                                            @if(!empty($attachment->one_card_back) && file_exists(storage_path($attachment->one_card_back)))
                                                <div class="col">
                                                    <img src="{{ asset('storage/' . $attachment->one_card_back) }}" alt="Back Image" class="img-fluid mb-2" style="max-width: 300px; height: auto;">
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                    <div class="row">
                        <h5>Additional Card</h5>
                        @if(!empty($driver->attachments))
                        @foreach($driver->attachments as $attachment)
                            @if(!empty($attachment->additional_cards))
                                @php
                                    $additionalCards = json_decode($attachment->additional_cards, true);
                                @endphp
                                @if(is_array($additionalCards))
                                    <div class="row">
                                        @foreach($additionalCards as $card)
                                            <div class="col-md-3"> <!-- Adjust the column size as needed -->
                                                <div class="card mb-5 shadow-none" style="border: 1px solid">
                                                    <div class="px-1 py-1">
                                                        <div class="row align-items-center">
                                                            <div class="col">
                                                                <img src="{{ asset('storage/' . $card) }}" alt="Card Image" class="img-fluid mb-2" style="max-width: 300px; height: auto;">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            @endif
                        @endforeach
                    @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection
