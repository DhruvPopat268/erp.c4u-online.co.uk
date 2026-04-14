@extends('layouts.admin')
@section('page-title')
    {{__('Manage Prepaid/Postpaid Credit Logs')}}
@endsection
@push('script-page')
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Prepaid/Postpaid Credit Logs')}}</li>
@endsection
@section('action-btn')
@can('create depot')
    <div class="float-end">

    </div>
    @endcan
@endsection


@section('content')
    <div class="row">
        <div class="row" style="margin-bottom: 10px;margin-top:10px;">
            <div class="col-12">
                <!-- Filter Form -->
                 @if(Auth::user()->hasRole('company') || Auth::user()->hasRole('PTC manager'))
                <form method="GET" action="{{ route('credit-coins.index') }}">
                    <div class="row">
                        <div class="col-md-4">
                            <label for="company_id">{{__('Filter by Company')}}</label>
                            <select name="company_id" id="company_id" class="form-control">
                                <option value="">{{__('All Companies')}}</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>
                                        {{ strtoupper($company->name) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary mt-4">{{__('Filter')}}</button>
                            <a href="{{ route('credit-coins.index') }}" class="btn btn-secondary mt-4">{{__('Reset Filter')}}</a>
                        </div>
                    </div>
                </form>
                  @endif
            </div>
        </div>
        <div class="col-9">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                            <tr>
                                 <th>{{__('Date')}}</th>
                                     <th>{{__('Company Name')}}</th>
                                <th>{{__('Old Payment Type')}}</th>
                                <th>{{__('Old Credit Coins')}}</th>
                                <th>{{__('New payment type')}}</th>
                                <th>{{__('New Credit Coins')}}</th>
                                <th>{{__('Updated By')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                                @forelse($paymentHistories as $history)
                            <tr>
                                <td class="text-center">{{ $history->created_at ? \Carbon\Carbon::parse($history->created_at)->format('d/m/Y') : 'N/A' }}</td>
                                <td class="text-center">{{ $history->company->name ?? 'N/A' }}</td>
                                <td class="text-center">{{ $history->old_payment_type ?? 'N/A' }}</td>
                                <td class="text-center">{{ $history->old_coins ?? 'N/A' }}</td>
                                <td class="text-center">{{ $history->new_payment_type ?? 'N/A' }}</td>
                                <td class="text-center">{{ $history->new_coins ?? 'N/A' }}</td>
                                <td class="text-center">{{ $history->creator->username ?? 'System' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">{{ __('No records found.') }}</td>
                            </tr>
                        @endforelse

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
