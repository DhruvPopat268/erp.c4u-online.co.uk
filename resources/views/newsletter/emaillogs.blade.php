@extends('layouts.admin')
@section('page-title')
    {{__('Manage NewsLetter')}}
@endsection
@push('script-page')
// <script>
//     document.addEventListener('DOMContentLoaded', function () {
//         document.getElementById('resend-button').addEventListener('click', function () {
//             const loaderOverlay = document.querySelector('.loader-overlay');

//             // Show the loader
//             loaderOverlay.style.display = 'flex';

//             // Create a form to submit
//             const form = document.createElement('form');
//             form.method = 'POST';
//             form.action = '{{ route('email.resend') }}';

//             // Add CSRF token
//             const csrfInput = document.createElement('input');
//             csrfInput.type = 'hidden';
//             csrfInput.name = '_token';
//             csrfInput.value = '{{ csrf_token() }}';
//             form.appendChild(csrfInput);

//             // Append the form to the body and submit it
//             document.body.appendChild(form);
//             form.submit();
//         });
//     });
// </script>
@endpush


<style>
/*    .loader {*/
/*        border: 4px solid rgba(0, 0, 0, 0.1);*/
/*        border-radius: 50%;*/
/*        border-top: 4px solid #3498db;*/
/*        width: 30px;*/
/*        height: 30px;*/
/*        animation: spin 1s linear infinite;*/
/*        display: none;*/
/*        position: absolute;*/
/*        top: 50%;*/
/*        left: 50%;*/
/*        transform: translate(-50%, -50%);*/
/*    }*/

/*    @keyframes spin {*/
/*        0% { transform: rotate(0deg); }*/
/*        100% { transform: rotate(360deg); }*/
/*    }*/

/*    .loader-overlay {*/
/*        position: fixed;*/
/*        top: 0;*/
/*        left: 0;*/
/*        width: 100%;*/
/*        height: 100%;*/
/*        background: rgba(255, 255, 255, 0.8);*/
/*        display: none;*/
/*        justify-content: center;*/
/*        align-items: center;*/
/*        z-index: 1000;*/
/*    }*/
</style>
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Newsletter')}}</li>
    <li class="breadcrumb-item">{{__('Email Log')}}</li>

@endsection
@php
use Carbon\Carbon;
@endphp
@section('action-btn')
    <div class="float-end">
        <a href="{{route('emaillogs.export')}}" data-bs-toggle="tooltip" title="{{__('Export')}}" class="btn btn-sm btn-primary">
            <i class="ti ti-file-export"></i>
        </a>
         <form action="{{ route('email.resend') }}" method="POST" style="display: inline;">
            @csrf
            <button type="submit" class="btn btn-sm btn-danger">Resend All Failed Emails</button>
        </form>
        <form action="{{ route('emaillogs.deleteAll') }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete all email logs?');">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-sm btn-warning"><i class="ti ti-trash text-white"></i>Delete All Email Logs</button>
</form>
  

        <!--<div class="resend-container" style="position: relative;">-->
        <!--    <button id="resend-button" type="button" class="btn btn-sm btn-danger">Resend Failed Emails</button>-->
        <!--    <div class="loader-overlay">-->
        <!--        <div class="loader"></div>-->
        <!--    </div>-->
        <!--</div>-->

    </div>
    
@endsection


@section('content')
    <div class="row">

        <div class="col-9" style="width: 100%">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>{{__('Email')}}</th>
                                <th>{{__('Subject')}}</th>
                                <th>{{__('Status')}}</th>
                                <th>{{ __('Sent At') }} </th>
                                <th>{{__('Sent By')}}</th>

                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($emailLogs as $log)

                                <tr class="font-style">
                                    <td style="text-align: center">{{ $loop->iteration }}</td>

                                    <td style="text-align: center">{{ $log->email }}</td>
                                    <td style="text-align: center">{{ $log->subject }}</td>
                                    <td style="text-align: center">
                                        @if($log->status === 'SEND')
                                            <span style="color: green; font-weight:bold;">{{ $log->status }}</span>
                                        @elseif($log->status === 'FAILED')
                                            <span style="color: red; font-weight:bold;">{{ $log->status }}</span>
                                        @else
                                            {{ $log->status }}
                                        @endif
                                    </td>
                                    <td style="text-align: center">{{ \Carbon\Carbon::parse($log->sent_at)->format('d/m/Y') }}</td>
                                    <td style="text-align: center">{{ !empty($log->creator)?$log->creator->username:'' }}</td>

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

