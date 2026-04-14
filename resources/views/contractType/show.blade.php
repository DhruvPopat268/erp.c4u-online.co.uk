@extends('layouts.admin')
@php
$attachments=\App\Models\Utility::get_file('contract_attechment');
@endphp
@push('css-page')
<link rel="stylesheet" href="{{asset('css/summernote/summernote-bs4.css')}}">
<link rel="stylesheet" href="{{asset('assets/css/plugins/dropzone.min.css')}}">
<link rel="stylesheet" href="{{asset('css/test.css')}}">
<style>
    .table-bordered{
        width: 30%;
        margin-top: -15px;
        margin-left: 10%;
    }
    .table-bordered2{
        width: 30%;
        margin-left: 76%;
        margin-top: -26%;
    }
</style>
@endpush
@section('page-title')
{{ __('Company & Manager Information') }}
@endsection
@push('script-page')
<script>
   $(document).on("click", ".status", function() {
       var status = $(this).attr('data-id');
       var url = $(this).attr('data-url');
       $.ajax({
           url: url,
           type: 'POST',
           data: {
               "status": status ,
               "_token": "{{ csrf_token() }}",
           },
           success: function(data) {
               show_toastr('{{__("success")}}', 'Status Update Successfully!', 'success');
               location.reload();
           }
       });
   });
</script>
<script src="{{asset('css/summernote/summernote-bs4.js')}}"></script>
<script src="{{asset('assets/js/plugins/dropzone-amd-module.min.js')}}"></script>

<script>
   var scrollSpy = new bootstrap.ScrollSpy(document.body, {
       target: '#useradd-sidenav',
       offset: 300,
   })
   $(".list-group-item").click(function(){
       $('.list-group-item').filter(function(){
           return this.href == id;
       }).parent().removeClass('text-primary');
   });
</script>
@endpush
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
<li class="breadcrumb-item"><a href="{{ url()->previous() }}">{{ __('company') }}</a></li>
<li class="breadcrumb-item"><a href="">{{ ucwords(strtolower($contractType->name)) }}</a></li>
{{--  <li class="breadcrumb-item active" aria-current="page">{{\Auth::user()->contractNumberFormat($contract->id)}}</li>  --}}
@endsection
@section('action-btn')
{{--  <div class="float-end d-flex align-items-center">
   <a href="{{route('contract.download.pdf',\Crypt::encrypt($contract->id))}}" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="tooltip" data-bs-placement="top" title="{{__('Download')}}" target="_blanks">
   <i class="ti ti-download"></i>
   </a>
   <a href="{{ route('get.contract',$contract->id) }}"  target="_blank" class="btn btn-sm btn-primary btn-icon m-1" >
   <i class="ti ti-eye text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('PreView') }}"> </i>
   </a>
   @php
   $status = App\Models\Contract::status();
   @endphp
   @php
   $status = App\Models\Contract::status();
   @endphp
   @if(\Auth::user()->type == 'client' )
   <ul class="list-unstyled m-0 ">
      <li class="dropdown dash-h-item status-drp">
         <a class="dash-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#"
            role="button" aria-haspopup="false" aria-expanded="false">
         <span class="drp-text hide-mob text-primary">{{ ucfirst($contract->status) }}
         <i class="ti ti-chevron-down drp-arrow nocolor hide-mob"></i>
         </span>
         </a>
         <div class="dropdown-menu dash-h-dropdown">
            @foreach ($status as $k => $status)
            <a class="dropdown-item status" data-id="{{ $k }}" data-url="{{ route('contract.status', $contract->id) }}" href="#">{{ ucfirst($status) }}
            </a>
            @endforeach
         </div>
      </li>
   </ul>
   @endif
</div>  --}}
@endsection
@section('content')
<div class="row">
    <div class="col-xl-9" style="width: 100%">
        <div id="useradd-1">
            <div class="row">
                <div class="col-xxl-5" style="width: 100%;">
                    <div class="card report_card total_amount_card">
                        <div class="card-body pt-0" style="margin-bottom: -30px; margin-top: -10px;">
                            <address class="mb-0 text-sm">
                                <dl class="row mt-4 align-items-center">
                                    <h3>{{ __('Company Detail') }}</h3>
                                    <br>
                                    <div class="col-sm-6" style="font-size: 0.85rem !important; margin-top: 0%;">
                                         <dt class="col-sm-14 h6 text-sm">
                                            <span style="font-weight: bold; font-size: 0.85rem !important;">{{ __('Account No') }}:</span>
                                            <span class="col-sm-8 text-sm">{{ $contractType->account_no }}</span>
                                        </dt>
                                        <dt class="col-sm-14 h6 text-sm">
                                            <span style="font-weight: bold; font-size: 0.85rem !important;">{{ __('Company Name') }}:</span>
                                            <span class="col-sm-8 text-sm">{{ strtoupper($contractType->name) }}</span>
                                        </dt>
                                        <dt class="col-sm-14 h6 text-sm">
                                            <span style="font-weight: bold; font-size: 0.85rem !important;">{{ __('Company Email') }}:</span>
                                            <span class="col-sm-8 text-sm">{{ $contractType->email }}</span>
                                        </dt>
                                        <dt class="col-sm-14 h6 text-sm" style="width: 64ch;">
                                            <span style="font-weight: bold; font-size: 0.85rem !important;">{{ __('Company Address') }}:</span>
                                            <span class="col-sm-8 text-sm">{{ $contractType->address }}</span>
                                        </dt>
                                        <dt class="col-sm-14 h6 text-sm">
                                            <span style="font-weight: bold; font-size: 0.85rem !important;">{{ __('Company Contact Number') }}:</span>
                                            <span class="col-sm-8 text-sm">{{ $contractType->contact }}</span>
                                        </dt>
                                        <dt class="col-sm-14 h6 text-sm">
                                            <span style="font-weight: bold; font-size: 0.85rem !important;">{{ __('Fors Browse Policy') }}:</span>
                                            <span class="col-sm-8 text-sm">{{ $contractType->fors_browse_policy ? \Carbon\Carbon::parse($contractType->fors_browse_policy)->format('d/m/Y') : '-' }}</span>
                                        </dt>
                                        <dt class="col-sm-14 h6 text-sm">
                                            <span style="font-weight: bold; font-size: 0.85rem !important;">{{ __('Fors Silver Policy') }}:</span>
                                            <span class="col-sm-8 text-sm">{{ $contractType->fors_silver_policy ? \Carbon\Carbon::parse($contractType->fors_silver_policy)->format('d/m/Y') : '-' }}</span>
                                        </dt>
                                        <dt class="col-sm-14 h6 text-sm">
                                            <span style="font-weight: bold; font-size: 0.85rem !important;">{{ __('Fors Gold Policy') }}:</span>
                                            <span class="col-sm-8 text-sm">{{ $contractType->fors_gold_policy ? \Carbon\Carbon::parse($contractType->fors_gold_policy)->format('d/m/Y') : '-' }}</span>
                                        </dt>

                                    </div>
                                    <!-- End of New Box -->
                                    <div class="col-sm-6"> <!-- Existing Boxes -->
                                        {{--  <table class="table table-bordered">
                                            <thead>
                                               <tr>
                                                <th>{{ __('Total Operating Center Vehicles Limit') }}</th>
                                                   <th>{{ __('Total Vehicles') }}</th>
                                                   <th>{{ __('Total Trailers') }}</th>
                                                   <th>{{ __('Total Drivers') }}</th>
                                               </tr>
                                           </thead>
                                           <tbody>

                                                   <tr>
                                                    <td>{{ $totalVehicles }}</td>
                                                    <td>{{  $vehiclesCount }}</td>
                                                       <td>{{ $totalTrailers }}</td>
                                                       <td>{{  $driversCount }}</td>
                                                   </tr>

                                           </tbody>

                                       </table>  --}}
                                       <div class="col-xl-13">
                                        <div class="row">
                                          <div class="col-lg-3 col-6" style="margin-left: -10%;">
                                            <a href="{{ route('contract.index') }}" style="text-decoration: none;">
                                              <div class="card">
                                                  <div class="card-body" style="min-height: 205px; background-color: #b7b7b7; border-radius: 10px;">
                                                      <div class="theme-avtar bg-primary">
                                                          <i class="fas fa-warehouse"></i>
                                                      </div>
                                                      <h6 class="mb-3 mt-4">{{ __('Total Authorisation Vehicles') }}</h6>
                                                      <h3 class="mb-0">{{ $totalVehicles }}</h3>
                                                      <h3 class="mb-0"></h3>
                                                  </div>

                                              </div>
                                            </a>
                                          </div>
                                          <div class="col-lg-3 col-6">
                                            <a href="{{ route('contract.index') }}" style="text-decoration: none;">
                                            <div class="card">
                                                <div class="card-body" style="min-height: 205px; background-color: #acc5a4;border-radius: 10px;">
                                                    <div class="theme-avtar bg-primary">
                                                        <i class="ti ti-truck"></i>
                                                    </div>
                                                    <h6 class="mb-3 mt-4">{{ __('Total Vehicles') }}</h6>
                                                    <h3 class="mb-0">{{ $vehiclesCount }}</h3>
                                                    <h3 class="mb-0"></h3>
                                                </div>

                                            </div>
                                            </a>
                                          </div>
                                        <div class="col-lg-3 col-6">
                                            <a href="{{ route('contract.index') }}" style="text-decoration: none;">
                                            <div class="card">
                                                <div class="card-body" style="min-height: 205px;background-color: #db9079;border-radius: 10px;">
                                                    <div class="theme-avtar bg-primary">
                                                        <i class="fas fa-truck-loading" style="color: #ffffff;"></i>
                                                    </div>
                                                    <h6 class="mb-3 mt-4">{{ __('Total Trailers') }}</h6>
                                                    <h3 class="mb-0">{{ $totalTrailers }}</h3>
                                                    <h3 class="mb-0"></h3>
                                                </div>

                                            </div>
                                            </a>
                                        </div>
                                        <div class="col-lg-3 col-6">
                                            <a href="{{ route('driver.index') }}" style="text-decoration: none;">
                                            <div class="card">
                                                <div class="card-body" style="min-height: 205px; background-color: #5e9ed1; border-radius: 10px;">
                                                    <div class="theme-avtar bg-primary">
                                                        <i class="ti ti-steering-wheel"></i>
                                                    </div>
                                                    <h6 class="mb-3 mt-4">{{ __('Total Drivers') }}</h6>
                                                    <h3 class="mb-0">{{ $driversCount }}</h3>
                                                    <h3 class="mb-0"></h3>
                                                </div>

                                            </div>
                                            </a>
                                        </div>
                                     </div>

                                      </div>
                                    </div> <!-- End of Existing Boxes -->

                                </dl>

                            </address>

                        </div>
                    </div>
                </div>
                <div class="row">
    <!-- First Box: Payment Type -->
    <div class="col-md-6 mb-3">
        <div class="card shadow-sm rounded-3" style="background-color: #e3f2fd;"> <!-- Light Blue -->
            <div class="card-body text-center py-4">
                <h5 class="card-title mb-2" style="color: #0d6efd; font-weight: 600;">{{ __('Payment Type') }}</h5>
                <p class="card-text display-6 fw-bold" style="color: #0d6efd;">
                    {{ $contractType->payment_type ?? 'N/A' }}
                </p>
            </div>
        </div>
    </div>

    <!-- Second Box: Available Coins -->
    <div class="col-md-6 mb-3">
        <div class="card shadow-sm rounded-3" style="background-color: #d1e7dd;"> <!-- Light Green -->
            <div class="card-body text-center py-4">
                <h5 class="card-title mb-2" style="color: #198754; font-weight: 600;">{{ __('Available Coins') }}</h5>
                <p class="card-text display-6 fw-bold" style="color: #198754;">
                    {{ $contractType->coins ?? 0 }}
                    @if(empty($contractType->coins) || $contractType->coins == 0)
                        <span style="color: #dc3545; font-weight: bold; font-size: 0.9rem; margin-left: 8px;">
                            (No coins left. Please recharge)
                        </span>
                    @endif
                </p>
            </div>
        </div>
    </div>
</div>

                <div class="col-xxl-5" style="width: 100%;">
                    <div class="card report_card total_amount_card">
                        <div class="card-body pt-0" style="margin-bottom: -30px; margin-top: -10px;">
                            <address class="mb-0 text-sm">
                                <dl class="row mt-4 align-items-center">
                                    <h3>{{ ucwords(strtolower( $contractType->name)) }} {{ __('Manager Detail') }}</h3>
                                    <br>
                                    <div class="row">
                                        @php
                                            $operators = [
                                                'operator_role' => json_decode($contractType->operator_role, true) ?? [],
                                                'operator_name' => json_decode($contractType->operator_name, true) ?? [],
                                                'device' => json_decode($contractType->device, true) ?? [],
                                                'operator_phone' => json_decode($contractType->operator_phone, true) ?? [],
                                                'operator_dob' => json_decode($contractType->operator_dob, true) ?? [],
                                                'status' => json_decode($contractType->status, true) ?? [],
                                                'compliance' => json_decode($contractType->compliance, true) ?? [],
                                                'operator_email' => json_decode($contractType->operator_email, true) ?? [],
                                            ];
                                            $operatorCount = max(array_map('count', $operators));
                                        @endphp
                                        @for ($i = 0; $i < $operatorCount; $i++)
                                            <div class="col-md-4">
                                                <div class="operator-section" style="margin-top: 32px;">
                                                    <dt class="col-sm-14 h6 text-sm">
                                                        <span style="font-weight: bold; font-size: 0.85rem !important;">
                                                            Manager Name {{ $i + 1 }}:
                                                        </span>
                                                        <span class="col-sm-8 text-sm">
                                                            {{ $operators['operator_name'][$i] ?? '' }}
                                                        </span>
                                                    </dt>
                                                    <dt class="col-sm-14 h6 text-sm">
                                                        <span style="font-weight: bold; font-size: 0.85rem !important;">
                                                            Manager Role {{ $i + 1 }}:
                                                        </span>
                                                        <span class="col-sm-8 text-sm">
                                                            {{ $operators['operator_role'][$i] ?? '' }}
                                                        </span>
                                                    </dt>
                                                    <div class="sub-operator-section">
                                                        <dt class="col-sm-14 h6 text-sm">
                                                            <span style="font-weight: bold; font-size: 0.85rem !important;">
                                                                Device {{ $i + 1 }}:
                                                            </span>
                                                            <span class="col-sm-8 text-sm">
                                                                {{ $operators['device'][$i] ?? '' }}
                                                            </span>
                                                        </dt>
                                                        <dt class="col-sm-14 h6 text-sm">
                                                            <span style="font-weight: bold; font-size: 0.85rem !important;">
                                                                Phone {{ $i + 1 }}:
                                                            </span>
                                                            <span class="col-sm-8 text-sm">
                                                                {{ $operators['operator_phone'][$i] ?? '' }}
                                                            </span>
                                                        </dt>
                                                        <dt class="col-sm-14 h6 text-sm">
                                                            <span style="font-weight: bold; font-size: 0.85rem !important;">
                                                                DOB {{ $i + 1 }}:
                                                            </span>
                                                            <span class="col-sm-8 text-sm">
                                                                {{ $operators['operator_dob'][$i] ?? '' }}
                                                            </span>
                                                        </dt>
                                                        <dt class="col-sm-14 h6 text-sm">
                                                            <span style="font-weight: bold; font-size: 0.85rem !important;">
                                                                Status {{ $i + 1 }}:
                                                            </span>
                                                            <span class="col-sm-8 text-sm">
                                                                {{ $operators['status'][$i] ?? '' }}
                                                            </span>
                                                        </dt>
                                                        <dt class="col-sm-14 h6 text-sm">
                                                            <span style="font-weight: bold; font-size: 0.85rem !important;">
                                                                Compliance {{ $i + 1 }}:
                                                            </span>
                                                            <span class="col-sm-8 text-sm">
                                                                {{ $operators['compliance'][$i] ?? '' }}
                                                            </span>
                                                        </dt>
                                                        <dt class="col-sm-14 h6 text-sm">
                                                            <span style="font-weight: bold; font-size: 0.85rem !important;">
                                                                Email {{ $i + 1 }}:
                                                            </span>
                                                            <span class="col-sm-8 text-sm">
                                                                {{ $operators['operator_email'][$i] ?? '' }}
                                                            </span>
                                                        </dt>
                                                    </div>
                                                </div>
                                            </div>
                                        @endfor
                                    </div>
                                    <!-- End of Operator Section -->



                                </dl>
                            </address>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-5" style="width: 100%;">
                    <div class="card report_card total_amount_card">
                        <div class="card-body pt-0" style="margin-bottom: -30px; margin-top: -10px;">
                            <address class="mb-0 text-sm">
                                <dl class="row mt-4 align-items-center">
                                    <h3>{{ ucwords(strtolower( $contractType->name)) }} {{ __('Depot Detail') }}</h3>
                                    <br>
                                    <!-- Depot Section -->
                                    <div class="row" style="margin-top: 21px;">
                                        @foreach($depots as $depot)
                                            <div class="col-md-4">
                                                <div class="depot-section" style="margin-top: 32px;">
                                                    <dt class="col-sm-14 h6 text-sm">
                                                        <span style="font-weight: bold; font-size: 0.85rem !important;">
                                                            Operating Centers Name:
                                                        </span>
                                                        <span class="col-sm-8 text-sm">
                                                            {{ $depot->name }}
                                                        </span>
                                                    </dt>
                                                    <div class="sub-depot-section">
                                                        <dt class="col-sm-14 h6 text-sm">
                                                            <span style="font-weight: bold; font-size: 0.85rem !important;">
                                                                Licence Number:
                                                            </span>
                                                            <span class="col-sm-8 text-sm">
                                                                {{ $depot->licence_number }}
                                                            </span>
                                                        </dt>
                                                        <dt class="col-sm-14 h6 text-sm">
                                                            <span style="font-weight: bold; font-size: 0.85rem !important;">
                                                                Traffic Area:
                                                            </span>
                                                            <span class="col-sm-8 text-sm">
                                                                {{ $depot->traffic_area }}
                                                            </span>
                                                        </dt>
                                                        <dt class="col-sm-14 h6 text-sm">
                                                            <span style="font-weight: bold; font-size: 0.85rem !important;">
                                                                Continuation date:
                                                            </span>
                                                            <span class="col-sm-8 text-sm">
                                                                {{ $depot->continuation_date }}
                                                            </span>
                                                        </dt>
                                                        <dt class="col-sm-14 h6 text-sm">
                                                            <span style="font-weight: bold; font-size: 0.85rem !important;">
                                                                Transport Manager Name:
                                                            </span>
                                                            <span class="col-sm-8 text-sm">
                                                                {{ $depot->transport_manager_name }}
                                                            </span>
                                                        </dt>
                                                        <dt class="col-sm-14 h6 text-sm">
                                                            <span style="font-weight: bold; font-size: 0.85rem !important;">
                                                                Operating Center:
                                                            </span>
                                                            <span class="col-sm-8 text-sm">
                                                                {{ $depot->operating_centre }}
                                                            </span>
                                                        </dt>
                                                        <dt class="col-sm-14 h6 text-sm">
                                                            <span style="font-weight: bold; font-size: 0.85rem !important;">
                                                                No Of Vehicles:
                                                            </span>
                                                            <span class="col-sm-8 text-sm">
                                                                {{ $depot->vehicles }}
                                                            </span>
                                                        </dt>
                                                        <dt class="col-sm-14 h6 text-sm">
                                                            <span style="font-weight: bold; font-size: 0.85rem !important;">
                                                                No Of Trailers:
                                                            </span>
                                                            <span class="col-sm-8 text-sm">
                                                                {{ $depot->trailers }}
                                                            </span>
                                                        </dt>
                                                        <dt class="col-sm-14 h6 text-sm">
                                                            <span style="font-weight: bold; font-size: 0.85rem !important;">
                                                                Status:
                                                            </span>
                                                            <span class="col-sm-8 text-sm">
                                                                {{ $depot->status }}
                                                            </span>
                                                        </dt>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <!-- End of Depot Section -->

                                </dl>
                            </address>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
