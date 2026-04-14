@extends('layouts.admin')

@push('css-page')

@endpush
@section('page-title')
{{ __('Planner Log Details') }}
@endsection
@push('script-page')

@endpush
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
<li class="breadcrumb-item"><a href="{{ url()->previous() }}">{{__('Planner Log Details')}}</a></li>
@endsection
@section('action-btn')

@endsection
@section('content')
<div class="row">
    <!-- Fleet Details Section -->
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5>{{ __('Planner Reminder Information ') }}</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th>{{ __('Planner Type') }}</th>
                        <td>{{ $plannerreminder->fleet->planner_type }}</td>
                      <th>{{ __('Vehicle Registration Number') }}</th>
                        <td>{{ $plannerreminder->fleet->vehicle->registrationNumber }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('Reminder Date') }}</th>
                        <td>{{ \Carbon\Carbon::parse($plannerreminder->next_reminder_date)->format('d/m/Y') }}</td>
                        <th>{{ __('Depot Name') }}</th>
                        <td>{{ $plannerreminder->fleet->vehicle->depot->name ?? '-'}}</td>
                    </tr>
                    @if(Auth::user()->hasRole('company') || Auth::user()->hasRole('PTC manager'))
                    <tr>
                        <th>{{ __('Company Name') }}</th>
                        <td>{{ $plannerreminder->fleet->company->name }}</td>
                    </tr>
                    @endif

                </table>
            </div>

        </div>
    </div>
</div>
<div class="row">
    <div class="col-xl-9" style="width: 100%">

        <div id="useradd-2">
            <div class="card">
               <div class="card-header">
                  <h5 class="mb-0">{{ __('Planner Attachments') }}</h5>
               </div>
               <div class="card-body">
                 
                  <div class="scrollbar-inner">
                     <div class="card-wrapper p-3 lead-common-box">
                        @if(optional($plannerreminder->fileUploads)->isNotEmpty())
    @foreach($plannerreminder->fileUploads as $file)
        <div class="card mb-3 border shadow-none">
            <div class="px-3 py-3">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="text-sm mb-0">
                            <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank">
                                {{ basename($file->file_path) }}
                            </a>
                        </h6>
                        <p class="card-text small text-muted">
                            @if(!empty($file->file_path) && file_exists(storage_path('app/public/' . $file->file_path)))
                                {{ number_format(\File::size(storage_path('app/public/' . $file->file_path)) / 1048576, 2) . ' MB' }}
                            @endif
                        </p>
                    </div>
                    <div class="col-auto actions">
                        <!-- Download Button -->
                        <div class="action-btn bg-warning">
                            <a href="{{ asset('storage/' . $file->file_path) }}" 
                               class="btn btn-sm d-inline-flex align-items-center"
                               download="{{ basename($file->file_path) }}"
                               data-bs-toggle="tooltip" title="Download">
                                <span class="text-white"><i class="ti ti-download"></i></span>
                            </a>
                        </div>
                        <!-- View Button -->
                        <div class="action-btn bg-warning">
                            <a href="{{ asset('storage/' . $file->file_path) }}" 
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
