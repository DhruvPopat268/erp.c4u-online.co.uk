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

@section('title')
    {{-- {{ __('Contract') }} {{ '('. $contract->name .')' }} --}}
@endsection

@section('content')
<!-- Header Section with Logo -->
<div class="header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <!-- Add your logo here -->
                <img src="{{ $img }}" style="max-width: 150px;" />
            </div>
        </div>
    </div>
</div>

<!-- Existing Content -->
<div class="row">
    <div class="col-xl-9">
        <div id="useradd-1">
            <div class="row">
                <div class="col-xxl-5" style="width: 100%;">
                    <div class="card report_card total_amount_card">
                        <div class="card-body pt-0">
                            <!-- Driver Information Container with Sky Blue Background and Border -->
                            <div class="driver-info-box" style="margin-top:30%; border: 1px solid #000; background-color: #eef1f1; padding: 15px; border-radius: 10px; width: 95%; position: absolute; left: 0; top: 0; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);">
                                <h3>{{ __('Driver Information') }}</h3>
                                <br>
                                <div class="row" style="font-size: 0.85rem !important;">
                                    <div class="col-sm-6">
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <dt class="h6 text-sm">
                                                    <span style="font-weight: bold; font-size: 0.85rem !important;">{{ __('Driver Licence No') }}:</span>
                                                    <span class="text-sm">{{ $driver->driver_licence_no }}</span>
                                                </dt>
                                            </div>
                                            <div class="col-sm-6">
                                                <dt class="h6 text-sm">
                                                    <span style="font-weight: bold; font-size: 0.85rem !important;">{{ __('Issue Number') }}:</span>
                                                    <span class="text-sm">{{ $driver->token_issue_number }}</span>
                                                </dt>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <dt class="h6 text-sm">
                                                    <span style="font-weight: bold; font-size: 0.85rem !important;">{{ __('Licence Valid From') }}:</span>
                                                    <span class="text-sm">{{ $driver->token_valid_from_date }}</span>
                                                </dt>
                                            </div>
                                            <div class="col-sm-6">
                                                <dt class="h6 text-sm">
                                                    <span style="font-weight: bold; font-size: 0.85rem !important;">{{ __('Licence Valid To') }}:</span>
                                                    <span class="text-sm">{{ $driver->driver_licence_expiry }}</span>
                                                </dt>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <dt class="h6 text-sm">
                                                    <span style="font-weight: bold; font-size: 0.85rem !important;">{{ __('Driver Name') }}:</span>
                                                    <span class="text-sm">{{ $driver->name }}</span>
                                                </dt>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <dt class="h6 text-sm">
                                                    <span style="font-weight: bold; font-size: 0.85rem !important;">{{ __('Gender') }}:</span>
                                                    <span class="text-sm">{{ $driver->gender }}</span>
                                                </dt>
                                            </div>
                                            <div class="col-sm-6">
                                                <dt class="h6 text-sm">
                                                    <span style="font-weight: bold; font-size: 0.85rem !important;">{{ __('Date Of Birth') }}:</span>
                                                    <span class="text-sm">{{ $driver->driver_dob }}</span>
                                                </dt>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <dt class="h6 text-sm">
                                                    <span style="font-weight: bold; font-size: 0.85rem !important;">{{ __('Address') }}:</span>
                                                    <span class="text-sm">{{ Str::limit($driver->driver_address, 45) }}</span>
                                                </dt>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                @if (strlen($driver->driver_address) > 45)
                                                    <span class="text-sm">{{ substr($driver->driver_address, 45) }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Existing Boxes -->
                                <div class="col-sm-6">
                                    <div class="col-xl-13">
                                        <div class="row">
                                            <div class="col-lg-2 col-6" style="margin-left: 62%; margin-top:-25%">
                                                <div class="card">
                                                    <div class="card-body" style="min-height: 20px; background-color: #acc5a4; border-radius: 10px; width:40%">
                                                        <h4 class="mb-3 mt-4 text-center">{{ __('Licence Information') }}</h4>
                                                        <div style="border-top: 2px solid #000; margin: 10px 0;"></div>
                                                        <div class="text-center">
                                                            <div style="font-size: 1.5rem; font-weight: bold;">{{ $driver->licence_type }}</div>
                                                            <div style="border-bottom: 1px solid #000; margin: 10px 0;"></div>
                                                            <div style="font-size: 1.5rem; font-weight: bold;">{{ $driver->driver_licence_status }}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-3 col-6" style="margin-left: 83%; margin-top:-35%">
                                                <div class="card">
                                                    <div class="card-body" style="min-height: 170px; background-color: #acc5a4; border-radius: 10px;width:75%">
                                                        <h4 class="mb-3 mt-4 text-center">{{ __(' Endorsements') }}</h4>
                                                        <div style="border-top: 2px solid #000; margin: 10px 0;"></div>
                                                        <div class="text-center">
                                                            <div style="font-size: 1.5rem; font-weight: bold;">{{ $driver->endorsement_penalty_points }}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End of Driver Information Container -->
                        </div>
                    </div>
                </div>

                <!-- End of Existing Boxes -->
            </div>
        </div>
    </div>
</div>
@endsection
