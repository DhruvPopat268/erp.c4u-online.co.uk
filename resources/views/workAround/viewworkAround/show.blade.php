@extends('layouts.admin')

@push('css-page')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
<style>
    .info-table td {
        padding-left: 9px;
    }
    .info-table {
        font-size: 13px;
    }
    .status-yes {
    color: green;
}

.status-no {
    color: red;
}

.status-no-row {
    background-color: #f1b4b4; /* Background color for the row when reason or image is present */
    padding: 5px; /* Padding to ensure the background color covers the content */
    border-radius: 3px; /* Optional: Round corners for better visual appeal */
}

    .status-icon {
        margin-left: 10px;
    }
    .reason-text {
        font-size: 12px;
        color: #666;
    }
    .row-item {
        display: inline-block;
        width: 23%; /* Adjust width based on the number of items per row and desired spacing */
        vertical-align: top;
        margin-bottom: 19px;
        border-radius: 6px;
        margin-right: 2%; /* Space between items */
    }
    .row-item:nth-child(4n) {
       margin-right: 3px;
    }
    .modal {
        display: none;
        position: fixed;
        z-index: 1060;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0,0,0,0.5);
    }
    .modal-content {
        background-color: #fff;
        margin: 10% auto;
        padding: 20px;
        border-radius: 8px;
        width: 30%;
        box-shadow: 0px 4px 10px rgba(0,0,0,0.3);
    }

    .close {
        float: right;
        font-size: 22px;
        font-weight: bold;
        cursor: pointer;
    }

    h5 {
        text-align: center;
        margin-bottom: 10px;
    }

    p {
        text-align: center;
    }

    label {
        font-weight: bold;
        display: block;
        margin: 8px 0 5px;
    }

    select, textarea, input[type="file"] {
        width: 100%;
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    input[type="checkbox"] {
        margin-right: 5px;
    }

    button {
        display: block;
        width: 100%;
        background-color: #48494B;
        color: white;
        padding: 10px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        margin-top: 15px;
    }

    button:hover {
        background-color: #218838;
    }
    .modal img {
        width: 100%;
    }
    .map-container {
        width: 55%;
        height: 300px;
       margin-top: -302px;
        margin-left: 45%;
        position: relative !important;
   }

   /* Responsive Styles */
   @media (max-width: 768px) {
       .map-container {
           width: 100%;
           height: 200px;
           margin-top: 10px;
           margin-left: 0;
       }
       .info-table {
           margin-top: 20px;
       }
       .row-item {
           width: 48%;
           margin-bottom: 15px;
       }
       .row-item:nth-child(2n) {
           margin-right: 2%;
       }
   }

   @media (max-width: 480px) {
       .map-container {
           height: 150px;
       }
       .row-item {
           width: 100%;
           margin-bottom: 15px;
       }
    }
</style>

@endpush

@section('page-title')
{{ __('WalkAround Details') }}
@endsection

@push('script-page')
<script>
    function openImageModal(imageSrc) {
        var modal = document.getElementById("imageModal");
        var modalImg = document.getElementById("modalImage");
        modal.style.display = "block";
        modalImg.src = imageSrc;
    }

    function closeImageModal() {
        var modal = document.getElementById("imageModal");
        modal.style.display = "none";
    }

    window.onclick = function(event) {
        var modal = document.getElementById("imageModal");
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }

        function openEditModal(answerId, currentStatus) {
        document.getElementById('answer_id').value = answerId;

        // Set the current status in the select dropdown
        const statusSelect = document.getElementById('status');
        statusSelect.value = currentStatus === 'Yes' ? 'Yes' : 'No'; // Ensure this matches the values in the dropdown

        // Show the modal
        document.getElementById("editStatusModal").style.display = "block";
    }




    function closeEditModal() {
        document.getElementById("editStatusModal").style.display = "none";
    }

    window.onclick = function(event) {
        var modal = document.getElementById("editStatusModal");
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }

    function openRectifiedModal(answerId) {
        document.getElementById('answer_id').value = answerId;
        document.getElementById("rectifiedStatusModal").style.display = "block";
    }


    function closeRectifiedModal() {
        document.getElementById("rectifiedStatusModal").style.display = "none";
    }

    window.onclick = function(event) {
        var modal = document.getElementById("rectifiedStatusModal");
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }

    function openRectifiedModal(answerId) {
        $.ajax({
            url: "{{ route('workaround.getDefectOptions') }}",
            type: "GET",
            data: { answer_id: answerId },
            success: function(response) {
                if (response.success) {
                    let dropdown = $('#problem_type');
                    dropdown.empty(); // Clear existing options
                    dropdown.append('<option value="">Select Defect Type</option>'); // Default option

                    response.defects_options.forEach(option => {
                        dropdown.append(`<option value="${option}">${option}</option>`);
                    });

                    $('#question_name').text("Description: " + response.question_name);
                    $('#reason_text').text("Defect: " + response.reason);
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert("Failed to fetch defect options.");
            }
        });

        $('#rectified_answer_id').val(answerId);
        $('#rectifiedStatusModal').show();
    }


</script>
@endpush

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('viewworkaround.index') }}">{{__('WalkAround Log')}}</a></li>
<li class="breadcrumb-item"><a href="">{{ __('WalkAround Log Details') }}</a></li>

@endsection

@section('action-btn')
<div class="float-end d-flex align-items-center">
     @php
    // Encode the driver ID before using it in the URL
    $encodedId = base64_encode($walkaround->id);
@endphp
    <a href="{{ route('walkAround.pdf', ['slug' => $encodedId]) }}" class="btn btn-sm btn-primary btn-icon" title="{{__('Download')}}" target="_blank">
        <i class="ti ti-download"></i>
    </a>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div id="useradd-1">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('WalkAround Details') }}</h5>
                </div>
                <div class="card-body">
                    <!-- Display WalkAround Details in Table -->

                    <div>
                        <table class="info-table">
                            <tr>
                                <th>{{ __('Walkaround') }}</th>
                                <td>#{{ $walkaround->id }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('Driver') }}</th>
                                <td>{{ ucwords(strtolower($walkaround->driver->name  ?? 'N/A')) }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('Operating Centre') }}</th>
                                <td>{{ $walkaround->depot->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('Vehicle') }}</th>
                                <td>
                                     @if ($walkaround->vehicle)
                                        @if ($walkaround->vehicle->vehicle_type == 'Trailer')
                                            {{ $walkaround->vehicle->vehicleDetail->vehicle_nick_name ?? 'No Vehicle ID' }} - {{ $walkaround->vehicle->vehicleDetail->make ?? 'No Make' }}
                                        @else
                                            {{ $walkaround->vehicle->registrations ?? 'No Registration' }} - {{ $walkaround->vehicle->vehicleDetail->make ?? 'No Make' }}
                                        @endif
                                    @else
                                        N/A
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>{{ __('Profile') }}</th>
                                <td>{{ $walkaround->profile->name ?? 'N/A' }}</td>
                            </tr>

                      <tr style="display:none";>
    <th>{{ __('Location') }}</th>
    <td>
        @php
            $location = $walkaround->location ?? 'N/A';
            $chunkSize = 80;
            $chunks = str_split($location, $chunkSize);
        @endphp
        @foreach ($chunks as $chunk)
            {{ $chunk }}<br>
        @endforeach
    </td>
</tr>

                            <tr>
                                <th>{{ __('Defects') }}</th>
                                <td>
                                    @if ($defectCount > 0)
                                        {{ $defectCount }} defects reported
                                    @else
                                        No defects reported, all questions answered
                                    @endif
                                </td>
                            </tr>


                            <tr>
                                <th>{{ __('Start') }}</th>
                                <td>{{ $walkaround->start_date ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('End') }}</th>
                                <!--<td>{{ $walkaround->end_date ?? 'N/A' }}</td>-->
                                <td>{{ $walkaround->uploaded_date ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('Duration') }}</th>
                                <td>
                                    {{ $walkaround->duration ?? 'N/A' }}
                                </td>
                            </tr>
                            <tr>
                                <th>{{ __('Uploaded') }}</th>
                                <td>{{ $walkaround->uploaded_date ?? 'N/A' }}</td>
                            </tr>

                            <tr>
                                <th>{{ __('Signature') }}</th>
                                <td>
                                    @if ($walkaround->signature)
                                        <a href="{{ asset('storage/' . $walkaround->signature) }}" target="_blank">
                                            <img src="{{ asset('storage/' . $walkaround->signature) }}" alt="Signature" style="width: 100px; height: auto;"/>
                                        </a>
                                    @else
                                        {{ 'N/A' }}
                                    @endif
                                </td>
                            </tr>
                        </table>

                    </div>
                    <div class="map-container">
    @php
        // Default fallback values
        $latLng = $walkaround->lat_lng ?? '51.53349241641747,-0.27503465449038955';
        list($latitude, $longitude) = explode(',', $latLng);
    @endphp
    <iframe
        src="https://maps.google.com/maps?q={{ $latitude }},{{ $longitude }}&t=k&z=15&output=embed"
        width="100%"
        height="100%"
        style="border:0;"
        allowfullscreen=""
        loading="lazy">
    </iframe>
</div>



                </div>
            </div>
        </div>

        <div id="useradd-2">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('WalkAround Description Details') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Display Odometer Reading and Fuel Level -->
                        <div class="row-item" style="background-color: #d1d1d1;height: 23px;">
                            <strong class="label">Odometer Reading:</strong> {{ $walkaround->speedo_odometer ?? '0' }}
                        </div>
                        <div class="row-item" style="background-color: #d1d1d1;height: 23px;">
                            <strong class="label">Fuel Level:</strong> {{ $walkaround->fuel_level }}
                        </div>
                        <div class="row-item" style="background-color: #d1d1d1;height: 23px;">
                            <strong class="label">Adblue Level:</strong> {{ $walkaround->adblue_level }}
                        </div>

                        @php
                            $questions = $walkaround->workAroundQuestionAnswers;
                            $chunks = $questions->chunk(4);
                        @endphp

                      @foreach ($chunks as $chunk)
    @foreach ($chunk as $answer)
        <div class="row-item {{ $answer->image || $answer->reason ? 'status-no-row' : '' }}">
            <span class="status-icon">
                @if($answer->image || $answer->reason)
                    <span class="status-no">
                        <i class="fas fa-times-circle"></i> <!-- Red cross icon -->
                    </span>
                @else
                    <span class="status-yes">
                        <i class="fas fa-check-circle"></i> <!-- Green check icon -->
                    </span>
                @endif
            </span>
            <strong>{{ $answer->question->name ?? 'N/A' }}</strong>
            
            @if($answer->image)
                <i class="fa fa-camera status-icon" style="color:rgb(0, 0, 0); margin-left: 5px; cursor: pointer;" onclick="openImageModal('{{ asset('storage/' . $answer->image) }}')"></i>
            @endif
             @if($answer->image || $answer->reason)
            <i class="fa fa-edit status-icon" style="color:rgb(0, 0, 0); margin-left: 5px; cursor: pointer;" title="Edit" onclick="openEditModal('{{ $answer->id }}', '{{ $answer->status }}')"></i>
            <i class="fa fa-wrench status-icon" style="color:rgb(0, 0, 0); margin-left: 5px; cursor: pointer;" title="Edit" onclick="openRectifiedModal('{{ $answer->id }}')"></i>

            @endif
            @if($answer->reason || $answer->other_reason)
            <div class="reason-text">
                @if($answer->question->select_reasonimage == 'None')
                    {{ $answer->other_reason }}
                @else
                    {{ $answer->reason }}
                @endif
            </div>
        @endif
        </div>
    @endforeach
@endforeach



                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Modal -->
<div id="imageModal" class="modal">
    <div class="modal-content">
        <span onclick="closeImageModal()" class="close">&times;</span>
        <img id="modalImage" src="" alt="Image">
    </div>
</div>

<!-- Edit Status Modal -->
<div id="editStatusModal" class="modal">
    <div class="modal-content" style="width: 20%;">
        <span onclick="closeEditModal()" class="close">&times;</span>
        <h5>Edit Defect</h5>
        <p style="color:red">Notes: This step/change may affect your walkaround report, as well as the deletion of the defect description and image </p>
        <form id="editStatusForm" method="POST" action="{{ route('workaround.updateStatus') }}">
            @csrf
            <input type="hidden" name="answer_id" id="answer_id">
            <div class="modal-body" style="display:none;">
                <div class="row">
                    <div class="form-group col-md-15" style="margin-top: 7px;">
                        {{ Form::label('status', __('Status')) }}
                        {{ Form::select('status', ['Yes' => 'Yes', 'No' => 'No'], null, ['class' => 'form-control', 'id' => 'status']) }}
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <input type="button" value="{{__('Cancel')}}" class="btn btn-light" onclick="closeEditModal()">
                <input type="submit" value="{{__('Update')}}" class="btn btn-primary">
            </div>
        </form>

    </div>
</div>

<!-- Rectified Status Modal -->
<div id="rectifiedStatusModal" class="modal">
    <div class="modal-content">
        <span onclick="closeRectifiedModal()" class="close">&times;</span>
        <h5>Mark as Rectified</h5>
        <!--<p style="color:red">Note: Ensure that rectification details are accurate.</p>-->
        <h6 id="question_name"></h6>
        <h6 id="reason_text"></h6>

        <form action="{{ route('workaround.markRectified') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="answer_id" id="rectified_answer_id">

            <label for="problem_type">Defect Type:</label>
            <select name="problem_type" id="problem_type" required>
                <option value="">Select Defect Type</option>
                <!-- Options will be dynamically added here -->
            </select>

            <label for="defect_options">Rectified Type:</label>
            <select name="defect_options" required>
                <option value="" disabled selected>Please select rectified type</option>
                <option value="fixed">Fixed</option>
                <option value="replaced">Replaced</option>
                <option value="repaired">Repaired</option>
                <option value="pending_repair">Pending Repair</option>
            </select>

            <label for="problem_solution">How Problem Was Rectified:</label>
            <textarea name="problem_solution" id="problem_solution" rows="3" required></textarea>

            <label for="third_party">Fix by Maintenance Contractor:</label>
                <select name="third_party" required>
                    <option value="" disabled selected>Please select</option>
                    <option value="true">Yes</option>
                    <option value="false">No</option>
                </select>

            <label for="rectified_images">Upload Rectified Images:</label>
            <input type="file" name="rectified_images[]" multiple accept="image/*">

            <button class="btn-primary" type="submit">Mark as Rectified</button>
        </form>
    </div>
</div>


<div class="row">
    <div class="col-xl-12">
        <div id="useradd-2">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('WalkAround Rectified Attachments') }}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                            @if(!empty($walkaround) && $walkaround->workAroundQuestionAnswers && $walkaround->workAroundQuestionAnswers->isNotEmpty())
                            <table class="table datatable">
                                <thead class="thead-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Question Name</th>
                                        <th>Defect Type</th>
                                        <th>Rectified Type</th>
                                        <th>Problem Was Rectified</th>
                                         <th>Rectified By</th>
                                        <th>Attachment</th>


                                    </tr>
                                </thead>
                                <tbody>
                                    @php $index = 1; @endphp
                            @foreach($walkaround->workAroundQuestionAnswers as $answer)
                                        @if(!empty($answer->defect_options)) {{-- Show only if defect_options has a value --}}
                                @if($answer->fileUploads && $answer->fileUploads->isNotEmpty())
                                    @foreach($answer->fileUploads as $file)
                                                <tr>
                                                    <td class="text-center">{{ $index++ }}</td>
                                                    <td class="text-center">{{ $answer->question->name ?? 'Unknown Question' }}</td>
                                                    <td class="text-center">{{ $answer->problem_type ?? 'N/A' }}</td>
                                                    <td class="text-center">{{ $answer->defect_options ?? 'N/A' }}</td>
                                                    <td class="text-center">{{ $answer->problem_solution ?? 'N/A' }}</td>
                                                    <td class="text-center">{{ $answer->rectified_username ?? 'N/A' }}</td>
                                                    <td class="text-center">
                                                        <a href="{{ asset('storage/' . $file->image_path) }}"
                                                       class="btn btn-sm btn-primary"
                                                           download="{{ $answer->question->name ?? 'Unknown' }}"
                                                           data-bs-toggle="tooltip" title="Download">
                                                        <i class="ti ti-download"></i>
                                                        </a>
                                                        <a href="{{ asset('storage/' . $file->image_path) }}"
                                                       class="btn btn-sm btn-warning mx-2"
                                                           target="_blank" data-bs-toggle="tooltip" title="View">
                                                        <i class="ti ti-eye"></i>
                                                        </a>
                                                </td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td class="text-center">{{ $index++ }}</td>
                                                    <td class="text-center">{{ $answer->question->name ?? 'Unknown Question' }}</td>
                                                    <td class="text-center">{{ $answer->problem_type ?? 'N/A' }}</td>
                                                    <td class="text-center">{{ $answer->defect_options }}</td>
                                                    <td class="text-center">{{ $answer->problem_solution ?? 'N/A' }}</td>
                                                    <td class="text-center">{{ $answer->rectified_username ?? 'N/A' }}</td>
                                                    <td class="text-muted text-center">No Attachment</td>
                                                </tr>
                                            @endif
                                @endif
                            @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="text-muted">No attachments available.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection
