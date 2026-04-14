@extends('layouts.admin')

@push('css-page')
<link rel="stylesheet" href="{{ asset('css/summernote/summernote-bs4.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/plugins/dropzone.min.css') }}">
@endpush

@section('page-title')
{{ __('Walk Around Profile') }}
@endsection

@push('script-page')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const availableContainer = document.getElementById('available-checks');

    availableContainer.addEventListener('change', function(event) {
        if (event.target.classList.contains('available-checkbox')) {
            const checkbox = event.target;
            const questionId = checkbox.value;

            if (checkbox.checked) {
                // Mark the question as selected
                checkbox.setAttribute('name', 'available_questions[]');
            } else {
                // Remove the checkbox from selected questions
                checkbox.removeAttribute('name');
            }
        }
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const vehicleContainer = document.getElementById('vehicleContainer');
    const availableChecksContainer = document.getElementById('available-checks');
    const saveButton = document.getElementById('save-button');

    // Function to check if at least one checkbox is checked in a container
    function isAnyCheckboxChecked(container, inputName) {
        const checkboxes = container.querySelectorAll(`input[name="${inputName}"]`);
        return Array.from(checkboxes).some(checkbox => checkbox.checked);
    }

    // Function to update Save button state
    function updateSaveButtonState() {
        const isVehicleChecked = isAnyCheckboxChecked(vehicleContainer, 'selected_vehicles[]');
        const isDescriptionChecked = isAnyCheckboxChecked(availableChecksContainer, 'available_questions[]');

        // Enable button only when both conditions are met
        saveButton.disabled = !(isVehicleChecked && isDescriptionChecked);
    }

    // Event listener for vehicle checkboxes
    vehicleContainer.addEventListener('change', function(event) {
        if (event.target.name === 'selected_vehicles[]') {
            updateSaveButtonState();
        }
    });

    // Event listener for description checkboxes
    availableChecksContainer.addEventListener('change', function(event) {
        if (event.target.name === 'available_questions[]') {
            updateSaveButtonState();
        }
    });

    // Initial check on page load
    updateSaveButtonState();
});



document.addEventListener('DOMContentLoaded', function() {
    const groupCheckboxes = document.querySelectorAll('.vehicle-group-checkbox');
    const vehicleContainer = document.getElementById('vehicleContainer');

    groupCheckboxes.forEach(checkbox => {
        const selectedGroupId = checkbox.value;
        const groupElement = vehicleContainer.querySelector(`[data-group-id="${selectedGroupId}"]`);

        // Automatically show the vehicle group if it's checked on page load
        if (checkbox.checked && groupElement) {
            groupElement.style.display = 'block';
        }

        checkbox.addEventListener('change', function() {
            if (this.checked) {
                // Show the vehicles for the selected group
                if (groupElement) {
                    groupElement.style.display = 'block';
                }
            } else {
                // Hide the vehicles for the unselected group
                if (groupElement) {
                    groupElement.style.display = 'none';
                }
            }
        });
    });
});
</script>
@endpush

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
<li class="breadcrumb-item"><a href="{{ route('profile.index') }}">{{ __('Walk Around Profile') }}</a></li>
@endsection

@section('action-btn')
<div class="float-end d-flex align-items-center">
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-xl-13">
        <div id="useradd-1">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Profile Details') }}</h5>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <p><b>{{ __('Title') }} : </b> {{ $profile->name }} </p>
                        <p><b>{{ __('Description') }} :</b> {{ $profile->description }} </p>
                    </div>
                </div>
            </div>
        </div>
        <form action="{{ route('profile.assign', $profile->id) }}" method="POST">
            @csrf
            <div id="useradd-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __('Select Vehicle Groups') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <div class="row" id="vehicleGroupSelectContainer">
                                @foreach($vehicleGroups as $index => $group)
                                    <div class="col-md-2">
                                        <div class="form-check">
                                            <input class="form-check-input vehicle-group-checkbox" type="checkbox" value="{{ $group->id }}" id="group-{{ $group->id }}"
                                                @foreach($vehicles as $vehicle)
                                                    @if(in_array($vehicle->id, $selectedVehicles) && $vehicle->vehicleDetail->group_id == $group->id)
                                                        checked
                                                        @break
                                                    @endif
                                                @endforeach>
                                            <label class="form-check-label" for="group-{{ $group->id }}">
                                                {{ $group->name }}
                                            </label>
                                        </div>
                                    </div>

                                    @if (($index + 1) % 5 == 0) <!-- After every 5th group, start a new row -->
                                        </div><div class="row">
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <div id="useradd-2">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Select Vehicles') }}</h5>
                </div>
                <div class="card-body">
                        <div class="form-group" id="vehicleContainer">
                            @foreach($vehicleGroups as $group)
                                <div class="vehicle-group" data-group-id="{{ $group->id }}" style="display: none;">
                                    <h6>{{ $group->name }}</h6>
                                    <div class="row">
                                        @foreach($vehicles as $vehicle)
                                            @if($vehicle->vehicleDetail && $vehicle->vehicleDetail->group_id == $group->id)
                                                <div class="col-md-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" value="{{ $vehicle->id }}" id="vehicle-{{ $vehicle->id }}" name="selected_vehicles[]"
                                                            @if(in_array($vehicle->id, $selectedVehicles)) checked @endif>
                                                        <label class="form-check-label" for="vehicle-{{ $vehicle->id }}">
                                                                                                                   @if($vehicle->vehicle_type == 'Trailer')
                                                            {{ $vehicle->vehicleDetail->vehicle_nick_name ?? 'Null' }} - {{ $vehicle->vehicleDetail->make ?? 'Null' }}
                                                        @else
                                                            {{ $vehicle->registrations }} - {{ $vehicle->vehicleDetail->make ?? 'Null' }}
                                                        @endif                                                        
                                                        </label>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                    <hr>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

        <div id="useradd-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Description Check') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group" id="available-checks">
                                @foreach($availableQuestions->chunk(3) as $questionChunk)
                                    <div class="row">
                                        @foreach($questionChunk as $question)
                                            <div class="col-md-4">
                                                <div class="form-check">
                                                    <input class="form-check-input available-checkbox" type="checkbox" value="{{ $question->id }}" id="available-{{ $question->id }}" name="available_questions[]"
                                                        @if(in_array($question->id, $selectedQuestions)) checked @endif>
                                                    <label class="form-check-label" for="available-{{ $question->id }}">
                                                        {{ $question->name }} - <span style="color:#ff0404">({{ $question->question_type }})</span>
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary" id="save-button" disabled>{{ __('Save') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
