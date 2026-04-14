@extends('layouts.admin')

@push('css-page')

@endpush
@section('page-title')
{{ __('PCN  Attachments') }}
@endsection
@push('script-page')

@endpush
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
<li class="breadcrumb-item">{{__('PCN  Attachments')}}</li>
@endsection
@section('action-btn')

@endsection
@section('content')
<div class="row">
    <div class="col-xl-9" style="width: 100%">

        <div id="useradd-2">
            <div class="card">
               <div class="card-header">
                  <h5 class="mb-0">{{ __('PCN Attachments') }}</h5>
               </div>
               <div class="card-body">

                  <div class="scrollbar-inner">
                     <div class="card-wrapper p-3 lead-common-box">
                      @if(!empty($pcn->attachment))
    @foreach(json_decode($pcn->attachment, true) as $file)
        <div class="card mb-3 border shadow-none">
            <div class="px-3 py-3">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="text-sm mb-0">
                            <a href="{{ asset('storage/' . $file) }}" target="_blank">
                                {{ basename($file) }}
                            </a>
                        </h6>
                        <p class="card-text small text-muted">
                            @if(!empty($file) && file_exists(storage_path('app/public/' . $file)))
                                {{ number_format(\File::size(storage_path('app/public/' . $file)) / 1048576, 2) . ' MB' }}
                            @endif
                        </p>
                    </div>
                    <div class="col-auto actions">
                        <!-- Download Button -->
                        <div class="action-btn bg-warning">
                            <a href="{{ asset('storage/' . $file) }}"
                               class="btn btn-sm d-inline-flex align-items-center"
                               download="{{ basename($file) }}"
                               data-bs-toggle="tooltip" title="Download">
                                <span class="text-white"><i class="ti ti-download"></i></span>
                            </a>
                        </div>
                        <!-- View Button -->
                        <div class="action-btn bg-warning">
                            <a href="{{ asset('storage/' . $file) }}"
                               class="btn btn-sm d-inline-flex align-items-center mx-2"
                               target="_blank" data-bs-toggle="tooltip" title="View">
                                <span class="text-white"><i class="ti ti-eye"></i></span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@else
    <p class="text-muted">No attachments available.</p>
@endif


                     </div>
                  </div>
               </div>
            </div>
         </div>
    </div>
</div>

@endsection
