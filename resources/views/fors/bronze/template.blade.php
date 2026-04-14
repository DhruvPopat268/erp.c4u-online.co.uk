@extends('layouts.contractheader')

@php
$SITE_RTL = !empty($settings['SITE_RTL']) ? $settings['SITE_RTL'] : 'off';
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
    {{ __('Contract') }}
@endsection

@section('title')
    {{-- {{ __('Contract') }} {{ '('. $contract->name .')' }} --}}
@endsection

@section('content')
<div class="mt-3">
    <div class="row justify-content-center">
        <div class="row col-sm-9">
            <div class="card">
                <div class="card-body">
                    <div class="row invoice-title mt-2">
                        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-12">
                            <img src="{{ $img }}" style="max-width: 150px;" />
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-12 text-end">
                            <p><b>Bronze Policy : {{ $forsBronze->bronze_policy_name }} </b></p>
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
                                <img width="100px" src="{{ $driverPolicy->driver_signature }}" style="margin-left:80%; height: auto;" />
                                @else
                                    <p>No signature available</p>
                                @endif
                            </div>
                            <div style="margin-left:80%">
                                {{ $driver->name }}
                                <h5 class="mt-auto" >{{ __('Driver Signature') }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
