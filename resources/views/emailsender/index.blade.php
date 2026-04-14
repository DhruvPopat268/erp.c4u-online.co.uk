@extends('layouts.admin')
@section('page-title')
{{__('Weekly Email Report')}}
@endsection
@push('script-page')
    <!-- Include SweetAlert via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Function to handle clicking the send email button
            $('#btnSendEmail').click(function(e) {
                e.preventDefault();
                Swal.fire({
                    title: '{{ __("Are you sure?") }}',
                    text: '{{ __("Send a Report?") }}',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: '{{ __("Yes, send it!") }}'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loader
                        showLoader('Sending Email...');
                        // Redirect or perform action
                        window.location.href = $(this).attr('href');
                    }
                });
            });

            function showLoader(message) {
                // Add a loading overlay with blurred background
                $('<div class="loading-overlay"><div class="loader"></div><div class="loading-message">' + message + '</div></div>').appendTo('body');
            }
        });
    </script>
    <style>
   .loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5); /* Semi-transparent black background */
    z-index: 9999;
    display: flex;
    justify-content: center;
    align-items: center;
}

.loader {
    border: 5px solid #f3f3f3; /* Light grey */
    border-top: 5px solid #3498db; /* Blue */
    border-radius: 50%;
    width: 50px;
    height: 50px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

</style>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="#">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Weekly Email Report')}}</li>
    @endsection
@php
use Carbon\Carbon;
@endphp

@section('action-btn')
    <div class="float-end">
       @can('import email Report')
        <a href="#" data-size="md"  data-bs-toggle="tooltip" title="{{__('Import')}}" data-url="{{ route('emailsender.file.import') }}"  data-ajax-popup="true" data-bs-toggle="tooltip"  class="btn btn-sm btn-primary">
            <i class="ti ti-file-import"></i>
        </a>
        @endcan
          <a href="{{route('weeklyEmailDataexport.export')}}" data-bs-toggle="tooltip" title="{{__('Export')}}" class="btn btn-sm btn-primary">
            <i class="ti ti-file-export"></i>
        </a>
        {{--  @can('create driver')
        <a href="#" data-size="md" data-url="{{ route('driver.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Create New Driver')}}" class="btn btn-sm btn-primary">
            <i class="ti ti-plus"></i>
        </a>
        @endcan  --}}
        {{--  <a href="#" data-size="md" data-url="{{ route('driver.bulkimport') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Bulk Import')}}" class="btn btn-sm btn-success">
            <i class="ti ti-upload"></i> {{__('Bulk Import')}}
        </a>  --}}

    </div>
@endsection


@section('content')
    <div class="row">
        {{--  <div class="col-3">
            @include('layouts.depot_setup')
        </div>  --}}
        <div class="col-9" style="width: 100%">
            <div class="card">
                @can('send emailsend')
                 <div class="float-end">
                    <!--<a href="{{ route('weeklysendReminders') }}"  class="btn btn-sm btn-primary" style="float: inline-end;-->
                    <!--margin-top: 11px;-->
                    <!--margin-right: 26px;">-->
                    <!--    {{ __('Send Email') }}-->
                    <!--</a>-->
                    <a href="{{ route('weeklysendReminders') }}" id="btnSendEmail" class="btn btn-sm btn-primary" style="float: inline-end; margin-top: 11px; margin-right: 26px;">
                        {{ __('Send Email') }}
                    </a>
            </div>
             @endcan
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                            <tr>
                                <th>{{__('Account Id')}}</th>
                                <th>{{__('Company Name')}}</th>
                                <th>{{__('Attachment Files')}}</th>
                                <th>{{__('Status')}}</th>
                                <th>{{ __('Created') }} </th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($contracts as $contract)

                                <tr class="font-style" style="text-align: center; text-decoration: none;">
                                    <td>{{ !empty($contract->companyDetails) ? $contract->companyDetails->account_no : '' }}</td>
                                    <td>{{ !empty($contract->types) ? ucwords(strtoupper($contract->types->name)) : '' }}</td>
                                    <td>{{ $contract->file_count }}</td>
                                    <td>
                                        @if($contract->status === 'DONE')
                                            <span style="color: green;"><strong>{{ $contract->status }}</strong></span>
                                        @elseif($contract->status === 'FAILED')
                                            <span style="color: red;"><strong>{{ $contract->status }}</strong></span>
                                        @elseif($contract->status === 'SENDING')
                                            <span style="color: orange;"><strong>{{ $contract->status }}</strong></span>
                                        @else
                                            <strong>{{ $contract->status }}</strong>
                                        @endif
                                    </td>
                                    <td>{{ !empty($contract->creator)?$contract->creator->username:'' }}</td>
                                </tr>
                            @endforeach

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection

