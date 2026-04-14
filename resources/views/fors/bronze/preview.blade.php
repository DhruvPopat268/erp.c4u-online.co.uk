@extends('layouts.contractheader')
@php
$SITE_RTL = !empty($settings['SITE_RTL'] ) ? $settings['SITE_RTL']  : 'off';

@endphp
@push('script-page')

<script>
    function closeScript() {
        setTimeout(function () {
            window.open(window.location, '_self').close();
        }, 1000);
    }


</script>

@endpush
@section('page-title')
    {{__('Contract')}}
@endsection
@section('title')

{{-- {{__('Contract')}} {{ '('. $contract->name .')' }} --}}

@endsection


@section('content')
<div class="mt-3">
    <div class="row justify-content-center mb-3">
        <div class="col-sm-9 text-end me-2">
            <div class="all-button-box ">
            {{--  @if(((\Auth::user()->type =='company') && ($forsBronze->driver_signature == ''))&&$forsBronze->status == 'Start')
                    <a href="#" class="btn btn-sm btn-primary btn-icon m-" data-bs-toggle="modal"
                        data-bs-target="#exampleModal" data-size="md" data-url="{{ route('Bronzesignature',$driverSignature->id) }}"
                        data-bs-whatever="{{__('signature')}}" > <span class="text-white"> <i
                                class="ti ti-pencil text-white" data-bs-toggle="tooltip" data-bs-original-title="{{__('signature')}}"></i></span></a>
                    </a>
                    @endif  --}}
                {{--  <a href="{{route('contract.download.pdf',\Crypt::encrypt($bronzeprint->id))}}" class="btn btn-sm btn-primary btn-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="{{__('Download')}}" target="_blanks">
                    <i class="ti ti-download"></i>
                </a>  --}}
                <a href="{{ route('forsBronze.download.pdf', $driver->id) }}" class="btn btn-sm btn-primary">
                    <i class="ti ti-download"></i>                </a>

            </div>
        </div>
    </div>


    <div class="row justify-content-center">
        <div class="row col-sm-9">
            <div class="card">
                <div class="card-body">
                    <div class="row invoice-title mt-2">
                        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-12 ">
                            <img src="{{ $img }}" style="max-width: 150px;" />
                        </div>
                        <div >
                        <p style="margin-top: -8%; text-align: end;"><b>Bronze Policy : {{ $forsBronze->bronze_policy_name }}</b></p>
                    </div>
                    </div>

                    <br>
                    <div class="text-md">{!! $forsBronze->bronze_policy_description !!}</div>
                    <div class="row">
                        <div class="col-6">
                            {{-- Optional placeholder for additional content --}}
                        </div>
                        <div class="col-6 text-end">

                            <div>
                                @if (isset($driverPolicy->driver_signature) && !empty($driverPolicy->driver_signature))
                                <img width="200px" src="{{$driverPolicy->driver_signature}}" >
                                @else
                                    <p>No signature available</p>
                                @endif
                            </div>
                            <div>
                                {{$driver->name}}
                                <h5 class="mt-auto">{{ __('Driver Signature') }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



</div>

@endsection
