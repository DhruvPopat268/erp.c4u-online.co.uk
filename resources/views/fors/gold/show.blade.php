@extends('layouts.admin')

@push('css-page')
    <link rel="stylesheet" href="{{asset('css/summernote/summernote-bs4.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/plugins/dropzone.min.css')}}">
@endpush

@section('page-title')
    {{ __('Bronze Policy Detail') }}
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
        @can('manage fors')
        $('.summernote-simple').on('summernote.blur', function () {

            $.ajax({
                url: "{{route('gold.policy_description.store',$forsGold->id)}}",
                data: {_token: $('meta[name="csrf-token"]').attr('content'), gold_policy_description: $(this).val()},
                type: 'POST',
                success: function (response) {
                    console.log(response)
                    if (response.is_success) {
                        show_toastr('success', response.success,'success');
                    } else {
                        show_toastr('error', response.error, 'error');
                    }
                },
                error: function (response) {

                    response = response.responseJSON;
                    if (response.is_success) {
                        show_toastr('error', response.error, 'error');
                    } else {
                        show_toastr('error', response.error, 'error');
                    }
                }
            })
        });
        @else
        // $('.summernote-simple').summernote('disable');
        @endcan
    </script>

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
    <style>
        /* Add this CSS to your stylesheet */
.table .border-middle {
    border-left: 2px solid black; /* Adjust thickness as needed */
}

.table .border-middle:last-child {
    border-right: 2px solid black; /* Ensure the last column has a right border */
}

.table td:first-child, .table th:first-child {
    border-left: none; /* Remove the left border from the first column */
}

    </style>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('fors.gold.index') }}">{{ __('Gold Policy') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{$forsGold->gold_policy_name}}</li>
@endsection

{{--  @section('action-btn')
    <div class="float-end d-flex align-items-center">
        <a href="{{route('contract.download.pdf',\Crypt::encrypt($contract->id))}}" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="tooltip" data-bs-placement="top" title="{{__('Download')}}" target="_blanks">
            <i class="ti ti-download"></i>
        </a>
        <a href="{{ route('get.contract',$contract->id) }}"  target="_blank" class="btn btn-sm btn-primary btn-icon m-1" >
            <i class="ti ti-eye text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('PreView') }}"> </i>
        </a>

    @if((\Auth::user()->type=='company'))
       <a href="{{route('send.mail.contract',$contract->id)}}" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="tooltip" data-bs-original-title="{{__('Send Email')}}"  >
           <i class="ti ti-mail text-white"></i>
       </a>
            <a href="#" class="btn btn-sm btn-primary btn-icon m-1" data-size="lg" data-url="{{route('contract.copy',$contract->id)}}"
               data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Duplicate')}}" class="btn btn-sm btn-primary">
                <i class="ti ti-copy text-white"></i>
            </a>

        @endif

        @if((\Auth::user()->type=='company'))
            <a href="#" class="btn btn-sm btn-primary btn-icon m-1" data-size="lg" data-url="{{ route('signature',$contract->id) }}"
               data-ajax-popup="true" data-bs-toggle="tooltip" data-title="{{__('Add signature')}}" class="btn btn-sm btn-primary">
                <i class="ti ti-pencil text-white"></i>
            </a>
        @elseif(\Auth::user()->type == 'client' && ($contract->status == 'accept'))
            <a href="#" class="btn btn-sm btn-primary btn-icon m-1" data-size="lg" data-url="{{ route('signature',$contract->id) }}"
               data-ajax-popup="true" data-bs-toggle="tooltip" data-title="{{__('Add signature')}}" class="btn btn-sm btn-primary">
                <i class="ti ti-pencil text-white"></i>
            </a>
            @endif



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
</div>
@endsection  --}}

@section('content')
<div class="row">
   <div class="col-xl-3">
       <div class="card sticky-top" style="top:30px">
           <div class="list-group list-group-flush" id="useradd-sidenav">
               <a href="#useradd-1" class="list-group-item list-group-item-action border-0">{{ __('Policy Description') }}
                   <div class="float-end"><i class="ti ti-chevron-right"></i></div>
               </a>
               <a href="#useradd-2" class="list-group-item list-group-item-action border-0">{{ __(' Accept Policy  Driver List') }}
                   <div class="float-end"><i class="ti ti-chevron-right"></i></div>
               </a>
              <a href="#useradd-3" class="list-group-item list-group-item-action border-0">{{ __(' Decline Policy  Driver List') }}
                   <div class="float-end"><i class="ti ti-chevron-right"></i></div>
               </a>
               {{--   <a href="#useradd-4" class="list-group-item list-group-item-action border-0">{{ __('Notes') }}
                   <div class="float-end"><i class="ti ti-chevron-right"></i></div>
               </a>  --}}
           </div>
       </div>
   </div>
   <div class="col-xl-9">
       <div id="useradd-1">

           <div class="card">
               <div class="card-header">
                   <h5 class="mb-0">{{ __('Gold Policy Description - ')  }} {{$forsGold->gold_policy_name}}</h5>
               </div>
               <div class="card-body" >
                   <div class="col-md-12">
                       <div class="form-group mt-3" >
                           <textarea class="summernote-simple" >{!! $forsGold->gold_policy_description !!}</textarea>
                       </div>
                   </div>
               </div>
           </div>
       </div>

       <div id="useradd-2">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Accept Policy Driver List') }}</h5>
            </div>
            <div class="card-body">
                <div class="form-group">
                    @if($acceptedDrivers->isNotEmpty())
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ __('Driver Name') }}</th>
                                    <th>{{ __('Company Name') }}</th>
                                    <th>{{ __('Driver Signature') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                    <th class="border-middle">{{ __('Driver Name') }}</th>
                                    <th>{{ __('Company Name') }}</th>
                                    <th>{{ __('Driver Signature') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($acceptedDrivers->chunk(2) as $chunk)
                                    <tr>
                                        @foreach($chunk as $driver)
                                            <td class="border-middle">{{ $driver->name }}</td>
                                            <td>{{ $driver->companyName }}</td>
                                            <td>
                                                @if($driver->driver_signature)
                                                    <img src="{{ $driver->driver_signature }}" style="width: 129px;">
                                                @else
                                                    {{ __('No Signature') }}
                                                @endif
                                            </td>
                                            <td>
                                                <div class="action-btn bg-warning ms-2">
                                                    <a href="{{ route('bronze.policy', $driver->driver_id) }}" target="_blank" class="btn btn-sm btn-primary btn-icon m-1">
                                                        <i class="ti ti-eye text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('PreView') }}"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        @endforeach
                                        @if($chunk->count() < 2)
                                            <td class="border-middle"></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p>{{ __('No drivers have accepted the policy yet.') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div id="useradd-3">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Decline Policy Driver List') }}</h5>
            </div>
            <div class="card-body">
                <div class="form-group">
                    @if ($declinedDrivers->isNotEmpty())
                        @php
                            $drivers = $declinedDrivers->chunk(4); // Chunk the data into groups of 5
                        @endphp
                        @foreach ($drivers as $chunk)
                            <div class="row">
                                @foreach ($chunk as $driver)
                                    <div class="col-md-3"> <!-- Adjust column size as needed -->
                                        {{ $driver->declinedDriverName }} - {{ $driver->companyName }}
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    @else
                        <p>No declined drivers found.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>




   </div>
</div>
@endsection
