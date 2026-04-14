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
            filename: '{{App\Models\Utility::contractNumberFormat($contract->id)}}',
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

{{-- {{__('Contract')}} {{ '('. $contract->name .')' }} --}}

@endsection
@php
    use Carbon\Carbon;
@endphp

@section('content')
<div class="mt-3">
    <div class="row justify-content-center mb-3">
        <div class="col-sm-9 text-end me-2">
            <div class="all-button-box ">
            @if(((\Auth::user()->type =='company') && ($contract->company_signature == '')||(\Auth::user()->type =='client') && ($contract->client_signature == ''))&&$contract->status == 'Start')
                    <a href="#" class="btn btn-sm btn-primary btn-icon m-" data-bs-toggle="modal"
                        data-bs-target="#exampleModal" data-size="md" data-url="{{ route('signature',$contract->id) }}"
                        data-bs-whatever="{{__('signature')}}" > <span class="text-white"> <i
                                class="ti ti-pencil text-white" data-bs-toggle="tooltip" data-bs-original-title="{{__('signature')}}"></i></span></a>
                    </a>
                    @endif
                <a href="{{route('contract.download.pdf',\Crypt::encrypt($contract->id))}}" class="btn btn-sm btn-primary btn-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="{{__('Download')}}" target="_blanks">
                    <i class="ti ti-download"></i>
                </a>

            </div>
        </div>
    </div>


    <div class="row justify-content-center">
        <div class="row col-sm-9">
            <div class="card">
                <div class="card-body">
                    <div class="row invoice-title mt-2">
                        <div class="col-xs-12 col-sm-12 col-nd-6 col-lg-6 col-12 ">
                            <img  src="{{$img}}" style="max-width: 150px;"/>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-nd-6 col-lg-6 col-12 text-end">
                            <h3 class="invoice-number">{{\Auth::user()->contractNumberFormat($contract->id)}}</h3>
                        </div>
                    </div>
                    <div class="row align-items-center mb-4">
                        <div class="col-sm-6 mb-3 mb-sm-0 mt-3">
                            <div class="col-lg-12 col-md-8 mb-3">
                                <h6 class="d-inline-block m-0 d-print-none">{{__('Company Name  :')}}</h6>
                                <span class="col-md-8"><span class="text-md">{{ !empty($contract->types) ? ucwords(strtoupper($contract->types->name)) : '' }}</span></span>
                            </div>
                            <div class="col-lg-6 col-md-8">
                                <h6 class="d-inline-block m-0 d-print-none">{{__('Vehicle Registration Number   :')}}</h6>
                                <span class="col-md-8"><span class="text-md">{{ $contract->registrationNumber }}</span></span>
                            </div>
                            <div class="col-lg-6 col-md-8">
                                <h6 class="d-inline-block m-0 d-print-none">{{__('Tax Status   :')}}</h6>
                                <span class="col-md-8"><span class="text-md">{{ $contract->taxStatus }}</span></span>
                            </div>
                            <div class="">
    <h6 class="d-inline-block m-0 d-print-none">{{__('Tax Due Date   :')}}</h6>
    <span class="col-md-8">
        <span class="text-md">
            {{ $contract->taxDueDate ? Carbon::parse($contract->taxDueDate)->format('d/m/Y') : 'N/A' }}
        </span>
    </span>
</div>
                            <!--<div class="col-lg-6 col-md-8">-->
                            <!--    <h6 class="d-inline-block m-0 d-print-none">{{__('Tax Due Date   :')}}</h6>-->
                            <!--    <span class="col-md-8"><span class="text-md">{{ $contract->taxDueDate }}</span></span>-->
                            <!--</div>-->
                            <div class="col-lg-6 col-md-8">
                                <h6 class="d-inline-block m-0 d-print-none">{{__('MOT Status   :')}}</h6>
                                <span class="col-md-8"><span class="text-md">{{ $contract->motStatus }}</span></span>
                            </div>
                            <div class="col-lg-6 col-md-8">
                                <h6 class="d-inline-block m-0 d-print-none">{{__('Make   :')}}</h6>
                                <span class="col-md-8"><span class="text-md">{{ $contract->make }}</span></span>
                            </div>
                            <div class="col-lg-6 col-md-8">
                                <h6 class="d-inline-block m-0 d-print-none">{{__('Year Of Manufacture   :')}}</h6>
                                <span class="col-md-8"><span class="text-md">{{ $contract->yearOfManufacture }}</span></span>
                            </div>
                            <div class="col-lg-6 col-md-8">
                                <h6 class="d-inline-block m-0 d-print-none">{{__('Engine Capacity   :')}}</h6>
                                <span class="col-md-8"><span class="text-md">{{ $contract->engineCapacity }}</span></span>
                            </div>
                            <div class="col-lg-6 col-md-8">
                                <h6 class="d-inline-block m-0 d-print-none">{{__('CO2 Emissions   :')}}</h6>
                                <span class="col-md-8"><span class="text-md">{{ $contract->co2Emissions }}</span></span>
                            </div>
                            <div class="col-lg-6 col-md-8">
                                <h6 class="d-inline-block m-0 d-print-none">{{__('Fuel Type   :')}}</h6>
                                <span class="col-md-8"><span class="text-md">{{ $contract->fuelType }}</span></span>
                            </div>
 <div class="">
    <h6 class="d-inline-block m-0 d-print-none">{{__('Insurance   :')}}</h6>
    <span class="col-md-8">
        <span class="text-md">
            {{ $contract->insurance ? Carbon::parse($contract->insurance)->format('d/m/Y') : 'N/A' }}
        </span>
    </span>
</div>
<div class="">
    <h6 class="d-inline-block m-0 d-print-none">{{__('PMI Due   :')}}</h6>
    <span class="col-md-8">
        <span class="text-md">
            {{ $contract->PMI_due ? Carbon::parse($contract->PMI_due)->format('d/m/Y') : 'N/A' }}
        </span>
    </span>
</div>
<div class="">
    <h6 class="d-inline-block m-0 d-print-none">{{__('Brake test Due   :')}}</h6>
    <span class="col-md-8">
        <span class="text-md">
            {{ $contract->brake_test_due ? Carbon::parse($contract->brake_test_due)->format('d/m/Y') : 'N/A' }}
        </span>
    </span>
</div>
                        </div>
                        <div class="col-sm-6 mb-3 mb-sm-0 mt-3">
                            <div>
                                <div class="float-end">
                                    <div class="">
                                        <h6 class="d-inline-block m-0 d-print-none">{{__('Marked For Export   :')}}</h6>
                                        <span class="col-md-8"><span class="text-md">{{ $contract->markedForExport }}</span></span>
                                    </div>
                                    <div class="">
                                        <h6 class="d-inline-block m-0 d-print-none">{{__('Colour   :')}}</h6>
                                        <span class="col-md-8"><span class="text-md">{{ $contract->colour }}</span></span>
                                    </div>
                                    <div class="">
                                        <h6 class="d-inline-block m-0 d-print-none">{{__('Type Approval   :')}}</h6>
                                        <span class="col-md-8"><span class="text-md">{{ $contract->typeApproval }}</span></span>
                                    </div>
                                    <div class="">
                                        <h6 class="d-inline-block m-0 d-print-none">{{__('Revenue Weight   :')}}</h6>
                                        <span class="col-md-8"><span class="text-md">{{ $contract->revenueWeight }}</span></span>
                                    </div>
                                    <div class="">
                                        <h6 class="d-inline-block m-0 d-print-none">{{__('Euro Status   :')}}</h6>
                                        <span class="col-md-8"><span class="text-md">{{ $contract->euroStatus }}</span></span>
                                    </div>
                                    <div class="">
    <h6 class="d-inline-block m-0 d-print-none">{{__('Date Of Last V5C Issued   :')}}</h6>
    <span class="col-md-8">
        <span class="text-md">
            {{ $contract->dateOfLastV5CIssued ? Carbon::parse($contract->dateOfLastV5CIssued)->format('d/m/Y') : 'N/A' }}
        </span>
    </span>
</div>
                                    <!--<div class="">-->
                                    <!--    <h6 class="d-inline-block m-0 d-print-none">{{__('Date Of Last V5C Issued   :')}}</h6>-->
                                    <!--    <span class="col-md-8"><span class="text-md">{{ $contract->dateOfLastV5CIssued }}</span></span>-->
                                    <!--</div>-->
                                    <div class="">
                                        <h6 class="d-inline-block m-0 d-print-none">{{__('MOT Expiry Date   :')}}</h6>
                                        <span class="col-md-8"><span class="text-md">{{ $contract->motExpiryDate }}</span></span>
                                    </div>
                                    <div class="">
                                        <h6 class="d-inline-block m-0 d-print-none">{{__('Wheelplan   :')}}</h6>
                                        <span class="col-md-8"><span class="text-md">{{ $contract->wheelplan }}</span></span>
                                    </div>
                                    <div class="">
                                        <h6 class="d-inline-block m-0 d-print-none">{{__('Month Of First Registration   :')}}</h6>
                                        <span class="col-md-8"><span class="text-md">{{ $contract->monthOfFirstRegistration }}</span></span>
                                    </div>
                                    <div class="">
                                        <h6 class="d-inline-block m-0 d-print-none">{{__('Tacho Calibration   :')}}</h6>
                                        <span class="col-md-8"><span class="text-md">{{ $contract->tacho_calibration }}</span></span>
                                    </div>
                                    <div class="">
                                        <h6 class="d-inline-block m-0 d-print-none">{{__('DVS/PSS Permit Expiry  :')}}</h6>
                                        <span class="col-md-8"><span class="text-md">{{ $contract->dvs_pss_permit_expiry }}</span></span>
                                    </div>
                                    <div class="">
                                        <h6 class="d-inline-block m-0 d-print-none">{{__('Date Of Inspection  :')}}</h6>
                                        <span class="col-md-8"><span class="text-md">{{ $contract->date_of_inspection ? Carbon::parse($contract->date_of_inspection)->format('d/m/Y') : 'N/A' }}</span></span>
                                    </div>
                                    <div class="">
                                        <h6 class="d-inline-block m-0 d-print-none">{{__('Odometer Reading  :')}}</h6>
                                        <span class="col-md-8"><span class="text-md">{{ $contract->odometer_reading }}</span></span>
                                    </div>
                                    <div class="">
                                        <h6 class="d-inline-block m-0 d-print-none">{{__('Fridge Service  :')}}</h6>
                                        <span class="col-md-8"><span class="text-md">{{ $contract->fridge_service ? Carbon::parse($contract->fridge_service)->format('d/m/Y') : 'N/A' }}</span></span>
                                    </div>
                                    <div class="">
                                        <h6 class="d-inline-block m-0 d-print-none">{{__('Fridge Calibration  :')}}</h6>
                                        <span class="col-md-8"><span class="text-md">{{ $contract->fridge_calibration ? Carbon::parse($contract->fridge_calibration)->format('d/m/Y') : 'N/A' }}</span></span>
                                    </div>
                                    <div class="">
                                        <h6 class="d-inline-block m-0 d-print-none">{{__('Tail lift Loler  :')}}</h6>
                                        <span class="col-md-8"><span class="text-md">{{ $contract->tail_lift_loler ? Carbon::parse($contract->tail_lift_loler)->format('d/m/Y') : 'N/A' }}</span></span>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="col-xxl-5" style="width: 100%;">
                        <div class="card report_card total_amount_card">
                            <div class="card-body pt-0" style="margin-bottom: -30px; margin-top: 10px;">
                                 <h5>{{ __('Vehicle Annual Test') }}</h5>
                        <div style="margin-top:3%">
                                <div class="dvsa-vrm govuk-!-margin-bottom-1" data-test-id="vehicle-registration" style="font: 24px UK-VRM, Verdana, sans-serif;">
                                   {{$vehicle->registrations }}
                                </div>
                                <h1 class="govuk-heading-xl" data-test-id="vehicle-make-model" style="font-size:2rem;">{{$vehicle->make ?? null}} {{$vehicle->model ?? null}}</h1>
                                <div class="govuk-grid-column-one-third" >
                                    <span class="govuk-caption-m" style="display:none;">Colour</span>
                                    <div class="govuk-heading-m" data-test-id="vehicle-colour" style="font-size:1.1875rem;"></div>
                                </div>
                                <div class="govuk-grid-column-one-third">
                                    <span class="govuk-caption-m" style="display:none;">Fuel type</span>
                                    <div class="govuk-heading-m" data-test-id="vehicle-fuel-type" style="font-size:1.1875rem;"></div>
                                </div>
                                <div class="govuk-grid-column-one-third">
                                    <span class="govuk-caption-m">Date registered</span>
                                    <div class="govuk-heading-m" data-test-id="vehicle-date-registered" style="font-size:1.1875rem;">{{ $vehicle->registration_date ? Carbon::parse($vehicle->registration_date)->format('d/m/Y') : 'N/A' }}</div>
                                </div>
                                <div>
                                    <span class="govuk-caption-l" data-test-id="mot-expiry-text">Annual test valid until</span>
                                    <div class="govuk-heading-l" data-test-id="mot-due-date" style="font-size:1.2575rem;">{{ $vehicle->annual_test_expiry_date ? Carbon::parse($vehicle->annual_test_expiry_date)->format('d/m/Y') : 'N/A' }}</div>
                                </div>
                            @foreach($annualTests as $test)
                                <div class="govuk-grid-column-one-third">
                                    <span class="govuk-caption-m">Date tested</span>
                                    <div class="govuk-heading-m" data-test-id="vehicle-colour" style="font-size: 1.1875rem;">{{ \Carbon\Carbon::createFromFormat('Y.m.d', $test->test_date)->format('d/m/Y') ?? null }}</div>
                    <div id="pass-fail" style="font-size: 2rem; margin-top: -20px; color: {{ $test->test_result === 'PASSED' ? '#00703c' : '#ff0000' }}">
                        {{ $test->test_result ?? null }}
                    </div>
                                </div>
                                <!-- Start of second column -->
                                <div class="govuk-grid-column-one-third">
                                    <span class="govuk-caption-m" style="display:none;">Mileage</span>
                                    <div class="govuk-heading-m" data-test-id="vehicle-fuel-type" style="font-size: 1.1875rem;"></div>
                                </div>
                                <div class="govuk-grid-column-one-third">
                                    <span class="govuk-caption-m">Test certificate number</span>
                                    <div class="govuk-heading-m" data-test-id="vehicle-date-registered" style="font-size: 1.1875rem;">{{ $test->test_certificate_number ?? null }}</div>
                                </div>
                                <div class="govuk-grid-column-one-third">
                                    <span class="govuk-caption-m" style="display:none;">Test location</span>
                                    <div class="govuk-heading-m" data-test-id="vehicle-date-registered" style="font-size: 1.1875rem;"></div>
                                </div>
                                <div class="govuk-grid-column-one-third">
                            <span class="govuk-caption-m">Expiry date</span>
                            <div class="govuk-heading-m" data-test-id="vehicle-date-registered" style="font-size: 1.1875rem;">
                                @if($test->expiry_date)
                                    {{ \Carbon\Carbon::createFromFormat('Y.m.d', $test->expiry_date)->format('d/m/Y') }}
                                @else
                                    NULL
                                @endif
                            </div>
                        </div>
                             @php
                        $minorDefects = [];
                        $majorDefects = [];
                        $advisoryDefects = [];
                    @endphp

                    @foreach($test->defects as $defect)
                        @switch($defect->severity_description)
                            @case('MINOR')
                                @php $minorDefects[] = $defect->failure_reason; @endphp
                                @break
                            @case('PRS')
                            @case('FAIL')
                                @php $majorDefects[] = $defect->failure_reason; @endphp
                                @break
                            @case('ADVISORY')
                                @php $advisoryDefects[] = $defect->failure_reason; @endphp
                                @break
                        @endswitch
                    @endforeach

                    @if(!empty($minorDefects))
                        <div class="govuk-grid-column-one-third2">
                            <span class="govuk-caption-m" style="width: max-content;">Repair as soon as possible (minor defects):</span>
                            <div class="govuk-heading-m" data-test-id="vehicle-date-registered" style="font-size: 1.0875rem; width: 60ch; display:block;">
                                <ul>
                                    @foreach($minorDefects as $defect)
                                        <li>{{$defect}}</li>
                                    @endforeach
                                </ul>
                            </div>

                        </div>
                    @endif

                    @if(!empty($majorDefects))
                        <div class="govuk-grid-column-one-third2">
                            <span class="govuk-caption-m" style="width: max-content;">Repair immediately (major defects):</span>
                            <div class="govuk-heading-m" data-test-id="vehicle-date-registered" style="font-size: 1.0875rem; width: 60ch; display:block;">
                                <ul>
                                    @foreach($majorDefects as $defect)
                                        <li>{{$defect}}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    @if(!empty($advisoryDefects))
                        <div class="govuk-grid-column-one-third2">
                            <span class="govuk-caption-m" style="width: max-content;">Monitor and repair if necessary (advisories):</span>
                            <div class="govuk-heading-m" data-test-id="vehicle-date-registered" style="font-size: 1.0875rem; width: 60ch; display:block;">
                                <ul>
                                    @foreach($advisoryDefects as $defect)
                                        <li>{{$defect}}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif




                                 <!--<details class="govuk-details" data-module="govuk-details" data-test-id="defect-category-definition-container"-->
                                 <!--       open="" style=" margin-left: 3%;">-->
                                 <!--       <summary class="govuk-details__summary">-->
                                 <!--           <span class="govuk-details__summary-text">-->
                                 <!--               What are advisories?-->
                                 <!--           </span>-->
                                 <!--       </summary>-->
                                 <!--       <div class="govuk-details__text">-->
                                 <!--           <p data-test-id="minor-defect-definition">Minor defects have no significant effect on the safety of the-->
                                 <!--               vehicle or <br> impact on the environment. A vehicle with only minor defects will pass the test.</p>-->
                                 <!--           <p data-test-id="advisory-comment-definition">Advisories are given for guidance. Some of these may need-->
                                 <!--               to be <br> monitored in case they become more serious and need immediate repairs.</p>-->
                                 <!--       </div>-->
                                 <!--   </details>-->

                                 @endforeach
                                <!--<div class="govuk-grid-column-one-third">-->
                                <!--    <span class="govuk-caption-m"></span>-->
                                <!--    <div class="govuk-heading-m" data-test-id="vehicle-date-registered"></div>-->
                                <!--</div>-->
                                <!--<div class="govuk-grid-column-one-third2">-->
                                <!--    <span class="govuk-caption-m" style="width: max-content;">Repair as soon as possible (minor defects)</span>-->
                                <!--    <div class="govuk-heading-m" data-test-id="vehicle-date-registered" style="font-size: 1.1875rem; width: 60ch; display:block;">-->
                                <!--        <ul>-->
                                <!--            <li>-->
                                <!--                A speedometer or tachograph (where required): incomplete or dial glass broken without affecting-->
                                <!--                the operation. (26.1.b.i)-->
                                <!--            </li>-->
                                <!--        </ul>-->
                                <!--    </div>-->
                                <!--</div>-->
                                <!--<div class="govuk-grid-column-one-third2">-->
                                <!--    <span class="govuk-caption-m" style="width: max-content;">Monitor and repair if necessary (advisories)</span>-->
                                <!--    <div class="govuk-heading-m" data-test-id="vehicle-date-registered" style="font-size: 1.1875rem; width: 60ch; display:block;">-->
                                <!--        <ul>-->
                                <!--            <li>-->
                                <!--               Actuators, hydraulic master & wheel cylinders, valves and servos: N/S AXLE 2 minor air leak. (59.5)-->
                                <!--            </li>-->
                                <!--        </ul>-->
                                <!--    </div>-->
                                <!--</div>-->


                                    <!--<section class="heading">-->
                                    <!--<h3>Monitor and repair if necessary (advisories)</h3>-->
                                    <!--<ul>-->
                                    <!--    <li>Actuators, hydraulic master & wheel cylinders, valves and servos: N/S AXLE 2 minor air leak. (59.5)</li>-->
                                    <!--</ul>-->




                                <!--</section>-->
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="row">
                        @foreach($contract->files as $file)
                        <div class="row justify-content-center col-6">
                            <div class="col-6">
                            <div class="col">
                                <img src="{{ asset('storage/image_attechment/' . $file->files) }}" alt="{{ $file->files }} image" style="width: 100%;">
                            </div>
                          </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
