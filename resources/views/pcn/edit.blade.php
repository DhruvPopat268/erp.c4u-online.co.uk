@extends('layouts.admin')
@section('page-title')
    {{ __('Edit PCN') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('pcn.index') }}">{{ __('PCN') }}</a></li>
    <li class="breadcrumb-item">{{ __('Edit PCN') }}</li>
@endsection

@section('content')
<div class="row">
    <form action="{{ route('pcn.update', $pcn->id) }}" method="POST" enctype="multipart/form-data" onsubmit="showLoader()">
        @method('PUT')
            @csrf


        <div class="col-xxl-5" style="width: 100%;">
            <div class="card report_card total_amount_card">
                <div class="card-body pt-0" style="margin-top: 13px;">
                    <div class="row">
                             <div class="form-group col-md-6">
                            <label for="notice_number">{{ __('Notice Number') }}</label>
                            <input type="text" name="notice_number" id="notice_number" value="{{ old('notice_number', $pcn->notice_number) }}"  class="form-control" placeholder="Enter Notice Number">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="notice_date">{{ __('Notice Date') }}</label>
                            <input type="date" name="notice_date" id="notice_date" value="{{ old('notice_date', $pcn->notice_date) }}"  class="form-control" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="location">{{ __('Location of Contravention') }}</label>
                            <input type="text" name="location" id="location" class="form-control" value="{{ old('location', $pcn->location) }}"  placeholder="Location of Contravention" required>
                        </div>
                    </div>
                </div>
            </div>
        </div>




        <div class="col-xxl-5" style="width: 100%;">
            <div class="card report_card total_amount_card">
                <div class="card-body pt-0" style="margin-top: 13px;">
                    <!-- Common Fields -->
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="fine_amount">{{ __('Fine Amount') }}</label>
                            <input type="number" name="fine_amount" id="fine_amount" value="{{ old('fine_amount', $pcn->fine_amount) }}" class="form-control" value="0">
                        </div>
                        @php
    $deductWagesValue = old('deduct_wages', ($pcn->deduction_amount > 0 ? 'Yes' : 'No'));
@endphp
                        <div class="form-group col-md-6">
                            <label for="deduct_wages">{{ __('Do you want to deduct from driver wages?') }}</label>
                            <select name="deduct_wages" id="deduct_wages" class="form-control" onchange="toggleDeductionAmount()">
                                <option value="No" {{ $deductWagesValue == 'No' ? 'selected' : '' }}>{{ __('No') }}</option>
                                <option value="Yes" {{ $deductWagesValue == 'Yes' ? 'selected' : '' }}>{{ __('Yes') }}</option>
                            </select>

                        </div>
                        <div class="form-group col-md-6" id="deduction_amount_container" style="display: none;">
                            <label for="deduction_amount">{{ __('Deduction Amount') }}</label>
                            <input type="number" name="deduction_amount" id="deduction_amount" value="{{ old('deduction_amount', $pcn->deduction_amount) }}" class="form-control" value="0">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="status">{{ __('Status') }}</label>
                            <select name="status" id="status" class="form-control" required>
                                <option value="">{{ __('Select Status') }}</option>
                                <option value="Closed" {{ old('status', $pcn->status) == 'Closed' ? 'selected' : '' }}>{{ __('Closed') }}</option>
                                <option value="Outstanding" {{ old('status', $pcn->status) == 'Outstanding' ? 'selected' : '' }}>{{ __('Outstanding') }}</option>
                            </select>

                        </div>
                        <div class="form-group col-md-6">
                            <label for="attachments">{{ __('Notice Attachment') }}</label>
                            <input type="file" name="attachments[]" id="attachments" class="form-control" accept=".pdf,.jpg,.jpeg,.png" multiple>
                        </div>


                    </div>
                    <div class="row">
                        <div class="form-group">
                            <label for="comments">{{ __('Comments') }}</label>
                            <textarea name="comments" id="comments" class="form-control" style="height: 150px;">{{ old('comments', $pcn->comments) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
            <a href="{{ route('pcn.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
        </div>

        <!-- Loader HTML -->
        <div id="loader" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(194, 194, 194, 0.8); z-index: 9999;">
            <div class="loader-content" style="background: rgba(255, 255, 255, 0.8);box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);border-radius: 10px;position: absolute; top: 50%; left: 50%;padding: 10px; ">
                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;color: #ffffff;">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>

    </form>
</div>

@endsection

@push('script-page')
    <script>
        function showLoader() {
            document.getElementById('loader').style.display = 'block';
        }

        function toggleDeductionAmount() {
            var deductWages = document.getElementById('deduct_wages').value;
            var deductionAmountContainer = document.getElementById('deduction_amount_container');
            var deductionAmountInput = document.getElementById('deduction_amount');

            if (deductWages === 'Yes') {
                deductionAmountContainer.style.display = 'block';
            } else {
                deductionAmountContainer.style.display = 'none';
                deductionAmountInput.value = 0; // Set value to 0 when "No" is selected
            }
        }


        // Call it on page load too:
        document.addEventListener('DOMContentLoaded', function () {
            toggleDeductionAmount();
        });
    </script>


@endpush
