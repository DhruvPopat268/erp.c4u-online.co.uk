@extends('layouts.admin')

@section('page-title')
    {{ __('Assign Policy') }}
@endsection

@push('script-page')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="#">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Assign Policy') }}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        <!-- Add any action buttons if needed -->
    </div>
@endsection

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <!-- Policy Category Selector -->
                    <div class="mb-4">
                        <label for="policy-category" class="form-label">{{ __('Select Policy Category') }}</label>
                        <select id="policy-category" class="form-select">
                            <option value="">{{ __('Select Category') }}</option>
                            <option value="bronze">{{ __('PTC Library') }}</option>
                           @if(!Auth::user()->hasRole('company') && !Auth::user()->hasRole('PTC manager'))
        <option value="company">{{ __('Our Company Policy') }}</option>
    @endif
                            <!-- Uncomment below if needed -->
                            <!-- <option value="silver">{{ __('Silver Policy') }}</option>
                            <option value="gold">{{ __('Gold Policy') }}</option> -->
                        </select>
                    </div>

                    <!-- Policy List Display in a Box -->
                    <div id="policy-list-box" class="policy-list-box">
                        <h5>{{ __('Available Policies') }}</h5>
                        <div id="policy-list" class="policy-list">
                            <!-- Policy items will be displayed here based on the selection -->
                        </div>
                    </div>
{{--
                    <!-- Selected Policies -->
                    <div id="selected-policies">
                        <h5>{{ __('Selected Policies') }}</h5>
                        <ul id="selected-policies-list"></ul>
                    </div>  --}}

                    <!-- Next Step Button -->
                    <div class="mt-4" id="action-buttons">
                    @if(Auth::user()->hasRole('company') || Auth::user()->hasRole('PTC manager'))
                        <!-- Next Step always -->
                        <form id="next-step-form" method="POST" action="{{ route('assign-policy.step2') }}">
                            @csrf
                            <input type="hidden" id="selected-policies-hidden" name="selected_policies">
                            <input type="hidden" id="selected-company-id" name="company_id">
                            <button type="submit" class="btn btn-primary">{{ __('Next Step') }}</button>
                        </form>
                        <div id="validation-message" class="text-danger mt-2 d-none">
                            {{ __('Please select at least one policy before proceeding.') }}
                        </div>
                    @else
                        <!-- Other users -->
                      <form id="next-step-form-company" class="d-none" method="POST" action="{{ route('assign-policy.step2') }}">
    @csrf
    <input type="hidden" id="selected-policies-hidden-company" name="selected_policies">
    <button type="submit" class="btn btn-primary">{{ __('Next Step') }}</button>
</form>
                        <div id="validation-message-company" class="text-danger mt-2 d-none">
                            {{ __('Please select at least one policy before proceeding.') }}
                        </div>

                        <form id="send-email-form" method="POST" action="{{ route('assign-policy.sendEmail') }}">
    @csrf
    <input type="hidden" id="selected-policies-hidden-email" name="selected_policies">
<input type="hidden" name="operator_name" value="{{ auth()->user()->username }}">
    <button type="submit" class="btn btn-primary">{{ __('Send Request') }}</button>
</form>
                        <div id="validation-message-email" class="text-danger mt-2 d-none">
                            {{ __('Please select at least one policy before sending email.') }}
                    </div>
                    @endif
                </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Loader and Overlay -->
    <div id="loader-overlay" class="d-none">
        <div class="loader"></div>
    </div>
    <!-- Confirmation Modal -->
<div class="modal fade" id="sendRequestModal" tabindex="-1" aria-labelledby="sendRequestModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="sendRequestModalLabel">{{ __('Confirm Policy Request') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        {{ __('Do you want to send a request for the selected policies? This request will be sent to the PTC Transport for approval.') }}
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        <button type="button" class="btn btn-primary" id="confirm-send-request">{{ __('Yes, Send Request') }}</button>
      </div>
    </div>
  </div>
</div>


    <!-- Add script for dynamic content loading and validation -->
    <script>
    // Show modal when Send Request button is clicked
const sendEmailForm = document.getElementById('send-email-form');
const confirmSendBtn = document.getElementById('confirm-send-request');

if (sendEmailForm && confirmSendBtn) {
    sendEmailForm.addEventListener('submit', function(event) {
        // Check if at least one policy is selected
        const checkedCheckboxes = document.querySelectorAll('.policy-checkbox:checked');
        if (checkedCheckboxes.length === 0) {
            event.preventDefault();
            document.getElementById('validation-message-email').classList.remove('d-none');
            return;
        }

        event.preventDefault(); // Prevent default submission
        const modal = new bootstrap.Modal(document.getElementById('sendRequestModal'));
        modal.show();
    });

    confirmSendBtn.addEventListener('click', function() {
        sendEmailForm.submit(); // Submit form when confirmed
    });
}

            document.addEventListener('DOMContentLoaded', function() {
                const policyCategorySelect = document.getElementById('policy-category');
                const policyListDiv = document.getElementById('policy-list');
    const userRoles = @json(Auth::user()->roles->pluck('name'));
    const ptcLibraryValue = "{{ Auth::user()->company->ptc_library ?? 'No' }}";

                policyCategorySelect.addEventListener('change', function() {
                    const selectedCategory = this.value;
                    let items = [];

        // Reset buttons for other users
        if (!userRoles.includes('company') && !userRoles.includes('PTC manager')) {
            if (selectedCategory === 'bronze') {
                document.getElementById('send-email-form').classList.remove('d-none');
                document.getElementById('next-step-form-company').classList.add('d-none');
            } else if (selectedCategory === 'company') {
                document.getElementById('send-email-form').classList.add('d-none');
                document.getElementById('next-step-form-company').classList.add('d-none'); // hidden initially
            }
        }

        switch(selectedCategory) {
                        case 'bronze':
                @foreach ($fors['bronze']->whereNull('companyName') as $policy)
                    items.push('<div class="policy-item"><input type="checkbox" class="policy-checkbox" data-id="{{ $policy->id }}" data-name="{{ $policy->bronze_policy_name }}" data-category="bronze" id="bronze-{{ $policy->id }}"> <label for="bronze-{{ $policy->id }}">{{ $policy->bronze_policy_name }}</label></div>');
                            @endforeach
                            break;

                   case 'company':
    @if(!Auth::user()->hasRole('company') && !Auth::user()->hasRole('PTC manager'))
        @php
            $hasCompanyPolicies = $companyFors['bronze']->isNotEmpty() || $companyFors['silver']->isNotEmpty() || $companyFors['gold']->isNotEmpty();
        @endphp

        @if (!$hasCompanyPolicies)
            items.push('<p>{{ __("No company-specific policies found.") }}</p>');
        @else
            @foreach ($companyFors['bronze'] as $policy)
                items.push('<div class="policy-item"><input type="checkbox" class="policy-checkbox" data-id="{{ $policy->id }}" data-name="{{ $policy->bronze_policy_name }}" data-category="company" id="company-bronze-{{ $policy->id }}"> <label for="company-bronze-{{ $policy->id }}">{{ $policy->bronze_policy_name }}</label></div>');
            @endforeach

            @foreach ($companyFors['silver'] as $policy)
                items.push('<div class="policy-item"><input type="checkbox" class="policy-checkbox" data-id="{{ $policy->id }}" data-name="{{ $policy->bronze_policy_name }}" data-category="company" id="company-silver-{{ $policy->id }}"> <label for="company-silver-{{ $policy->id }}">{{ $policy->bronze_policy_name }} (Silver)</label></div>');
            @endforeach

            @foreach ($companyFors['gold'] as $policy)
                items.push('<div class="policy-item"><input type="checkbox" class="policy-checkbox" data-id="{{ $policy->id }}" data-name="{{ $policy->bronze_policy_name }}" data-category="company" id="company-gold-{{ $policy->id }}"> <label for="company-gold-{{ $policy->id }}">{{ $policy->bronze_policy_name }} (Gold)</label></div>');
            @endforeach
        @endif
    @endif
    break;



                        case 'silver':
                            @foreach ($fors['silver'] as $policy)
                                items.push('<div class="policy-item"><input type="checkbox" class="policy-checkbox" data-id="{{ $policy->id }}" data-name="{{ $policy->bronze_policy_name }}" data-category="silver" id="silver-{{ $policy->id }}"> <label for="silver-{{ $policy->id }}">{{ $policy->bronze_policy_name }} (Silver)</label></div>');
                            @endforeach
                            break;
                        case 'gold':
                            @foreach ($fors['gold'] as $policy)
                                items.push('<div class="policy-item"><input type="checkbox" class="policy-checkbox" data-id="{{ $policy->id }}" data-name="{{ $policy->bronze_policy_name }}" data-category="gold" id="gold-{{ $policy->id }}"> <label for="gold-{{ $policy->id }}">{{ $policy->bronze_policy_name }} (Gold)</label></div>');
                            @endforeach
                            break;
                        default:
                            items.push('<p>{{ __('Please select a policy category.') }}</p>');
                    }

                    policyListDiv.innerHTML = items.join('');

                    // Add event listener for checkboxes
                    document.querySelectorAll('.policy-checkbox').forEach(checkbox => {
                        checkbox.addEventListener('change', function() {
                if (selectedCategory === 'company' && !userRoles.includes('company') && !userRoles.includes('PTC manager')) {
                    const hasChecked = document.querySelectorAll('.policy-checkbox:checked').length > 0;
                    const nextStepFormCompany = document.getElementById('next-step-form-company');
                    if (hasChecked) nextStepFormCompany.classList.remove('d-none');
                    else nextStepFormCompany.classList.add('d-none');
                            }
                        });
                    });
                });

    // Helper function to handle form submission for Next Step or Send Email
    function handleFormSubmission(formId, hiddenInputId, validationId) {
                    const form = document.getElementById(formId);
        const hiddenInput = document.getElementById(hiddenInputId);
        const validationMessage = document.getElementById(validationId);
        if (!form) return;

        form.addEventListener('submit', function(event) {
        const selectedCategory = policyCategorySelect.value;

        // PTC Library validation for other users
        if (
            selectedCategory === 'bronze' &&
            !userRoles.includes('company') &&
            !userRoles.includes('PTC manager')
        ) {
            if (ptcLibraryValue !== 'Yes') {
                event.preventDefault();
                alert("Access to the PTC Library is not currently available for your account. Please contact your administrator to request access.");
                return;
            }
        }

            const checkedCheckboxes = document.querySelectorAll('.policy-checkbox:checked');
        if (checkedCheckboxes.length === 0) {
                event.preventDefault();
                validationMessage.classList.remove('d-none');
                return;
            }
        validationMessage.classList.add('d-none');

            const selectedPolicies = { bronze: [], silver: [], gold: [] };
            checkedCheckboxes.forEach(checkbox => {
    let category = checkbox.dataset.category;

    // Normalize 'company' into actual categories
    if (category === 'company') {
        if (checkbox.id.includes('bronze')) category = 'bronze';
        else if (checkbox.id.includes('silver')) category = 'silver';
        else if (checkbox.id.includes('gold')) category = 'gold';
    }
                selectedPolicies[category].push({
    id: checkbox.dataset.id,
    name: checkbox.dataset.name
});
});
            hiddenInput.value = JSON.stringify(selectedPolicies);
        });
    }

    // Apply for all forms
    handleFormSubmission('next-step-form', 'selected-policies-hidden', 'validation-message');
    handleFormSubmission('next-step-form-company', 'selected-policies-hidden-company', 'validation-message-company');
    handleFormSubmission('send-email-form', 'selected-policies-hidden-email', 'validation-message-email');
            });

    </script>
    @endsection

    <style>
        /* Loader Styles */
        #loader-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5); /* Black background with opacity */
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999; /* Make sure the overlay is on top */
        }

        .loader {
            border: 3px solid #f3f3f3; /* Light grey */
            border-top: 3px solid #3498db; /* Blue */
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 2s linear infinite;
        }

        /* Keyframes for spinning animation */
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Policy List Box Styles */
        .policy-list-box {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
            background-color: #f9f9f9;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .policy-list-box h5 {
            margin-bottom: 15px;
            font-size: 1.25rem;
        }

        .policy-list-box .policy-list {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }

        .policy-list-box .policy-item {
            flex: 1 1 calc(33.333% - 15px);
            box-sizing: border-box;
            margin-bottom: 10px;
        }

        .policy-list-box .policy-item label {
            margin-left: 5px;
        }
    </style>
