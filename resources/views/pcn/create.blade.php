@extends('layouts.admin')
@section('page-title')
    {{ __('Create PCN') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('pcn.index') }}">{{ __('PCN') }}</a></li>
    <li class="breadcrumb-item">{{ __('Create PCN') }}</li>
@endsection

@section('content')
<div class="row">
    <form action="{{ route('pcn.store') }}" method="POST" enctype="multipart/form-data" onsubmit="showLoader()">
        @csrf

        <div class="col-xxl-5" style="width: 100%;">
            <div class="card report_card total_amount_card">
                <div class="card-body pt-0" style="margin-top: 13px;">
                    <div class="row">
                        <div class="form-group col-md-6" style="{{ \Auth::user()->hasRole('company') || \Auth::user()->hasRole('PTC manager') ? '' : 'display: none;' }}">
                            <label for="company_id">{{ __('Company') }}</label>
                            <select name="company_id" id="company_id" class="form-control" required>
                                <option value="">{{ __('Select Company') }}</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}"
                                        {{ \Auth::user()->companyname == $company->id ? 'selected' : '' }}>
                                        {{ strtoupper($company->name) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="depot_id">{{ __('Depot Name') }}</label>
                            <select name="depot_id" id="depot_id" class="form-control" required>
                                <option value="">{{ __('Select Depot') }}</option>
                                @foreach($depots as $depot)
                                    <option value="{{ $depot->id }}" data-company-id="{{ $depot->companyName }}">{{ $depot->name }}</option>
                                @endforeach
                            </select>
                        </div>
                                                                   
                        <div class="form-group col-md-6">
                            <label for="violation_date">{{ __('Violation Date') }}</label>
                            <input type="date" name="violation_date" id="violation_date" class="form-control" required>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="vehicle_registration_number">Vehicle Registration Number</label>
                            <input type="text" id="vehicle_registration_number" name="vehicle_registration_number" class="form-control" placeholder="Enter Vehicle Registration Number" required>
                            <input type="hidden" name="vehicle_id" id="vehicle_id">

                        </div>
                        <div class="form-group col-md-6">
                            <label for="driver_name">{{ __('Driver Name') }}</label>
                            <input type="text" name="driver_name" id="driver_name" class="form-control" required>
                        </div>
                        <button id="fetch-vehicle-data" class="btn btn-primary" style="display:none;">Fetch</button>
                        <!-- WorkAroundStore Modal -->
                        <div class="modal fade" id="vehicleModal" tabindex="-1" aria-labelledby="vehicleModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="vehicleModalLabel">Possible Driver Data Match</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body" id="vehicle-data">
                                        <!-- Vehicle data will be populated here -->
                                        <p><strong>Vehicle Registration Number:</strong> <span id="vehicle-registration"></span></p>
                                        <!-- WorkAroundStore data -->
                                        <div id="workaround-data-list">
                                            <!-- Dynamically populated -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-5" style="width: 100%;">
            <div class="card report_card total_amount_card">
                <div class="card-body pt-0" style="margin-top: 13px;">
                    <div class="row">
                             <div class="form-group col-md-6">
                            <label for="notice_number">{{ __('Notice Number') }}</label>
                            <input type="text" name="notice_number" id="notice_number" class="form-control" placeholder="Enter Notice Number">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="notice_date">{{ __('Notice Date') }}</label>
                            <input type="date" name="notice_date" id="notice_date" class="form-control" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="location">{{ __('Location of Contravention') }}</label>
                            <input type="text" name="location" id="location" class="form-control" placeholder="Location of Contravention" required>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="issuing_authority">{{ __('Issuing Authority') }}</label>
                            <select name="issuing_authority" id="issuing_authority" class="form-control" required>
                                <option value="">{{ __('Select Issuing Authority') }}</option>
                                <option value="Local Council">{{ __('Local Council') }}</option>
                                <option value="Police">{{ __('Police') }}</option>
                                <option value="DVSA">{{ __('DVSA') }}</option>
                                <option value="Other">{{ __('Other') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

                        <!-- Council Fields Section -->
                        <div id="council-fields" class="d-none">
                            <div class="col-lg-12">
                               <div class="card">
                                  <div class="card-header">
                                     <h5>{{ __('Local Council') }}</h5>
                                  </div>
                                  <div class="card-body">
                                     <div class="row">
                                        <div class="form-group col-md-6">
                                           <label for="contravention_type">{{ __('Contravention Type') }}</label>
                                           <select name="contravention_type" id="contravention_type" class="form-control">
                                              <option value="">{{ __('Select Contravention Type') }}</option>
                                              <option value="Options-N/A">{{ __('Options-N/A') }}</option>
                                              <option value="Loading/Unloading">{{ __('Loading/Unloading') }}</option>
                                              <option value="Incorrect Parking">{{ __('Incorrect Parking') }}</option>
                                              <option value="Bus Lane">{{ __('Bus Lane') }}</option>
                                              <option value="Red Route Stopping">{{ __('Red Route Stopping') }}</option>
                                              <option value="Yellow Box Junction">{{ __('Yellow Box Junction') }}</option>
                                              <option value="Other">{{ __('Other') }}</option>
                                           </select>
                                        </div>
                                        <div class="form-group col-md-6" id="other-contravention" style="display:none;">
                                           <label for="other_contravention_type">{{ __('Specify Other Contravention Type') }}</label>
                                           <input type="text" name="other_contravention_type" id="other_contravention_type" class="form-control">
                                        </div>

                                         <!-- Action Fields for Council -->

                                        <div class="form-group col-md-6">
                                           <label for="action">{{ __('Action') }}</label>
                                           <select name="action" id="action" class="form-control">
                                              <option value="">{{ __('Select Action') }}</option>
                                              <option value="Paid">{{ __('Paid') }}</option>
                                              <option value="Appealed">{{ __('Appealed') }}</option>
                                           </select>
                                        </div>
                                        <div class="form-group col-md-6" id="appealed-status" style="display:none;">
                                           <label for="appealed_status">{{ __('Appealed Status') }}</label>
                                           <select name="appealed_status" id="appealed_status" class="form-control">
                                              <option value="">{{ __('Select Appealed Status') }}</option>
                                              <option value="Won">{{ __('Won') }}</option>
                                              <option value="Lost">{{ __('Lost') }}</option>
                                           </select>
                                        </div>
                                        <!-- Lost Status Paid/Unpaid Dropdown -->
                                        <div class="form-group col-md-6" id="lost-status" style="display:none;">
                                           <label for="lost_status">{{ __('Lost Status') }}</label>
                                           <select name="lost_status" id="lost_status" class="form-control">
                                              <option value="">{{ __('Select Lost Status') }}</option>
                                              <option value="Paid">{{ __('Paid') }}</option>
                                           </select>
                                        </div>
                                     </div>
                                  </div>
                               </div>
                            </div>
                        </div>


                        <!-- Police Fields -->
                        <div id="police-fields" class="d-none">
                            <div class="col-lg-12">
                               <div class="card">
                                  <div class="card-header">
                                     <h5>{{ __('Police') }}</h5>
                                  </div>
                                  <div class="card-body">
                                     <div class="row">
                                        <div class="form-group col-md-6">
                                           <label for="offence_type">{{ __('Offence Type') }}</label>
                                           <select name="offence_type" id="offence_type" class="form-control">
                                              <option value="">{{ __('Select Offence Type') }}</option>
                                              <option value="Options-N/A">{{ __('Options-N/A') }}</option>
                                              <option value="Speeding">{{ __('Speeding') }}</option>
                                              <option value="Careless Driving">{{ __('Careless Driving') }}</option>
                                              <option value="Seat Belt">{{ __('Seat Belt') }}</option>
                                              <option value="Failing to stop after an accident">{{ __('Failing to stop after an accident') }}</option>
                                              <option value="Other">{{ __('Other') }}</option>
                                           </select>
                                        </div>
                                        <div class="form-group col-md-6" id="other-offence" style="display:none;">
                                           <label for="other_offence_type">{{ __('Specify Other Offence Type') }}</label>
                                           <input type="text" name="other_offence_type" id="other_offence_type" class="form-control">
                                        </div>

                                        <div class="form-group col-md-6">
                                           <label for="police_action">{{ __('Action') }}</label>
                                           <select name="police_action" id="police_action" class="form-control">
                                              <option value="">{{ __('Select Action') }}</option>
                                              <option value="Driver details sent">{{ __('Driver details sent') }}</option>
                                              <option value="Summons issued">{{ __('Summons issued') }}</option>
                                              <option value="Endorsable">{{ __('Endorsable') }}</option>
                                              <option value="Other">{{ __('Other') }}</option>
                                           </select>
                                        </div>
                                        <div class="form-group col-md-6" id="other-police-action" style="display:none;">
                                           <label for="other_police_action">{{ __('Specify Other Action') }}</label>
                                           <input type="text" name="other_police_action" id="other_police_action" class="form-control">
                                        </div>
                                     </div>
                                  </div>
                               </div>
                            </div>
                        </div>

                        <!-- DVSA Fields -->
                        <div id="dvsa-fields" class="d-none">
                            <div class="col-lg-12">
                               <div class="card">
                                  <div class="card-header">
                                     <h5>{{ __('DVSA') }}</h5>
                                  </div>
                                  <div class="card-body">
                                     <div class="row">
                                        <div class="form-group col-md-6">
                                           <label for="dvsa_offence_type">{{ __('Offence Type') }}</label>
                                           <select name="dvsa_offence_type" id="dvsa_offence_type" class="form-control">
                                              <option value="">{{ __('Select Offence Type') }}</option>
                                              <option value="(PG35EC) Inspection Notice">{{ __('(PG35EC) Inspection Notice') }}</option>
                                              <option value="(PG9-D) Prohibition Notice-Delayed">{{ __('(PG9-D) Prohibition Notice-Delayed') }}</option>
                                              <option value="(PG9-I) Prohibition Notice-Immediate">{{ __('(PG9-I) Prohibition Notice-Immediate') }}</option>
                                              <option value="(TE160) Prohibition-Other">{{ __('(TE160) Prohibition-Other') }}</option>
                                              <option value="(TE160-DH) Prohibition-Driving Hours">{{ __('(TE160-DH) Prohibition-Driving Hours') }}</option>
                                              <option value="(GV70) Foreign Vehicle-Prohibition">{{ __('(GV70) Foreign Vehicle-Prohibition') }}</option>
                                              <option value="(GV171) Foreign Vehicle-Immediate Prohibition">{{ __('(GV171) Foreign Vehicle-Immediate Prohibition') }}</option>
                                              <option value="(DG01) Dangerous Goods-Vehicle Monitoring">{{ __('(DG01) Dangerous Goods-Vehicle Monitoring') }}</option>
                                              <option value="(DG02) Dangerous Goods-Prohibition">{{ __('(DG02) Dangerous Goods-Prohibition') }}</option>
                                              <option value="(PN) H&S Prohibition Notice">{{ __('(PN) H&S Prohibition Notice') }}</option>
                                              <option value="Other">{{ __('Other') }}</option>
                                           </select>
                                        </div>

                                        <div class="form-group col-md-6" id="dvsa-other-offence" style="display:none;">
                                            <label for="dvsa_other_offence_type">{{ __('Specify Other Offence Type') }}</label>
                                            <input type="text" name="dvsa_other_offence_type" id="dvsa_other_offence_type" class="form-control">
                                        </div>


                                        <div class="form-group col-md-6">
                                           <label for="dvsa_action">{{ __('Action') }}</label>
                                           <select name="dvsa_action" id="dvsa_action" class="form-control">
                                              <option value="">{{ __('Select Action') }}</option>
                                              <option value="(FPN) Fixed Penalty Notice">{{ __('(FPN) Fixed Penalty Notice') }}</option>
                                              <option value="(FUE) Follow Up Enquiry">{{ __('(FUE) Follow Up Enquiry') }}</option>
                                              <option value="(MSI/VSI/SI) Further Investigation">{{ __('(MSI/VSI/SI) Further Investigation') }}</option>
                                              <option value="(NFA) No Followup Action">{{ __('(NFA) No Followup Action') }}</option>
                                              <option value="(AL) Advisory Letter">{{ __('(AL) Advisory Letter') }}</option>
                                              <option value="(X) Arrest Made by police">{{ __('(X) Arrest Made by police') }}</option>
                                              <option value="(P) Prosecution">{{ __('(P) Prosecution') }}</option>
                                              <option value="(VW) Verbal Warning">{{ __('(VW) Verbal Warning') }}</option>
                                              <option value="(FPN +) Fixed Penalty Notice & Prosecution">{{ __('(FPN +) Fixed Penalty Notice & Prosecution') }}</option>
                                              <option value="Other">{{ __('Other') }}</option>
                                           </select>
                                        </div>

                                        <div class="form-group col-md-6" id="other-action" style="display:none;">
                                            <label for="other_action">{{ __('Specify Other Action') }}</label>
                                            <input type="text" name="other_action" id="other_action" class="form-control">
                                        </div>
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
                            <input type="number" name="fine_amount" id="fine_amount" class="form-control" value="0">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="deduct_wages">{{ __('Do you want to deduct from driver wages?') }}</label>
                            <select name="deduct_wages" id="deduct_wages" class="form-control" onchange="toggleDeductionAmount()">
                                <option value="No">{{ __('No') }}</option>
                                <option value="Yes">{{ __('Yes') }}</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6" id="deduction_amount_container" style="display: none;">
                            <label for="deduction_amount">{{ __('Deduction Amount') }}</label>
                            <input type="number" name="deduction_amount" id="deduction_amount" class="form-control" value="0">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="status">{{ __('Status') }}</label>
                            <select name="status" id="status" class="form-control" required>
                                <option value="">{{ __('Select Status') }}</option>
                                <option value="Closed">{{ __('Closed') }}</option>
                                <option value="Outstanding">{{ __('Outstanding') }}</option>
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
                            <textarea name="comments" id="comments" class="form-control" style="height: 150px;"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-primary">{{ __('Create') }}</button>
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

            if (deductWages === 'Yes') {
                deductionAmountContainer.style.display = 'block';
            } else {
                deductionAmountContainer.style.display = 'none';
            }
        }

        $(document).ready(function () {
            $('#company_id').on('change', function () {
                var selectedCompanyId = $(this).val();

                // Reset the Depot dropdown
                $('#depot_id').val('');
                $('#depot_id option').hide();

                // Show only depots belonging to the selected company
                if (selectedCompanyId) {
                    $('#depot_id option[data-company-id="' + selectedCompanyId + '"]').show();
                } else {
                    // If no company is selected, show the placeholder
                    $('#depot_id option:first').show();
                }
            });

            // Trigger change event to set initial state
            $('#company_id').trigger('change');
        });
        $(document).ready(function() {
            $('#vehicle_registration_number').on('blur', function() {
                var registrationNumber = $(this).val();
                var violationDate = $('#violation_date').val();
                var companyId = $('#company_id').val(); // Get the selected company ID

                if (registrationNumber && violationDate && companyId) {
                    var formattedViolationDate = moment(violationDate).format('DD/MM/YYYY');

                    $.ajax({
                        url: '/fetch-vehicle-data',
                        method: 'GET',
                        data: {
                            registration_number: registrationNumber,
                            violation_date: formattedViolationDate,
                            company_id: companyId, // Include company_id in the request
                            depot_id: $('#depot_id').val()
                        },
                        success: function(response) {
                            if (response.success) {
                                var vehicle = response.vehicle;
                                var workAroundDataList = response.workAroundData;

                                // Set the vehicle ID to the hidden input
                                $('#vehicle_id').val(vehicle.id); // Assuming vehicle has an 'id' field

                                $('#vehicle-registration').text(vehicle.registrations);
                                $('#workaround-data-list').empty();

                                workAroundDataList.forEach(function(workAroundData) {
                                    var dataHtml = `
                                        <hr>
                                        <p>
                                            <strong>Driver Name:</strong>
                                            <a href="#" class="select-driver-name" data-driver-name="${workAroundData.driver_name}">
                                                ${workAroundData.driver_name}
                                            </a>
                                        </p>
                                        <p><strong>Depot Name:</strong> ${workAroundData.depot_name}</p>
                                    `;
                                    $('#workaround-data-list').append(dataHtml);
                                });

                                $('#vehicleModal').modal('show');

                                $(document).on('click', '.select-driver-name', function(e) {
                                    e.preventDefault();
                                    $('#driver_name').val($(this).data('driver-name'));
                                    $('#vehicleModal').modal('hide');
                                });
                            } else {
                                alert(response.message || 'Vehicle not found!');
                            }
                        },
                        error: function() {
                            alert('An error occurred while fetching the vehicle data.');
                        }
                    });
                } else {
                    alert('Please select a company, enter a vehicle registration number, and violation date.');
                }
            });
        });





        // Show fields based on Issuing Authority selection
        document.getElementById('issuing_authority').addEventListener('change', function () {
            const councilFields = document.getElementById('council-fields');
            const policeFields = document.getElementById('police-fields');
            const dvsaFields = document.getElementById('dvsa-fields');

            if (this.value === 'Local Council') {
                councilFields.classList.remove('d-none');
                policeFields.classList.add('d-none');
                dvsaFields.classList.add('d-none');
            } else if (this.value === 'Police') {
                policeFields.classList.remove('d-none');
                councilFields.classList.add('d-none');
                dvsaFields.classList.add('d-none');
            } else if (this.value === 'DVSA') {
                dvsaFields.classList.remove('d-none');
                policeFields.classList.add('d-none');
                councilFields.classList.add('d-none');
            } else {
                councilFields.classList.add('d-none');
                policeFields.classList.add('d-none');
                dvsaFields.classList.add('d-none');
            }
        });

        document.getElementById('action').addEventListener('change', function () {
            const appealedStatus = document.getElementById('appealed-status');
            const lostStatus = document.getElementById('lost-status');

            if (this.value === 'Appealed') {
                appealedStatus.style.display = 'block';
                lostStatus.style.display = 'none';
            } else if (this.value === 'Paid') {
                appealedStatus.style.display = 'none';
                lostStatus.style.display = 'none';
            } else {
                appealedStatus.style.display = 'none';
                lostStatus.style.display = 'none';
            }
        });

        // Additional logic for when 'Appealed' is selected and 'Lost' is selected
        document.getElementById('appealed_status').addEventListener('change', function () {
            const lostStatus = document.getElementById('lost-status');
            if (this.value === 'Lost') {
                lostStatus.style.display = 'block';
            } else {
                lostStatus.style.display = 'none';
            }
        });

        // Initial page load behavior based on the selected action
        document.addEventListener('DOMContentLoaded', function () {
            const initialAction = document.getElementById('action').value;
            const appealedStatus = document.getElementById('appealed-status');
            const lostStatus = document.getElementById('lost-status');

            if (initialAction === 'Appealed') {
                appealedStatus.style.display = 'block';
                lostStatus.style.display = 'none';
            } else if (initialAction === 'Paid') {
                appealedStatus.style.display = 'none';
                lostStatus.style.display = 'none';
            } else {
                appealedStatus.style.display = 'none';
                lostStatus.style.display = 'none';
            }
        });


        // Toggle "Other" field for contravention type in council fields
        document.getElementById('contravention_type').addEventListener('change', function () {
            const otherContravention = document.getElementById('other-contravention');
            otherContravention.style.display = this.value === 'Other' ? 'block' : 'none';
        });

        // Toggle "Other" field for offence type in police fields
        document.getElementById('offence_type').addEventListener('change', function () {
            const otherOffence = document.getElementById('other-offence');
            otherOffence.style.display = this.value === 'Other' ? 'block' : 'none';
        });

        // Toggle "Other" field for police action
        document.getElementById('police_action').addEventListener('change', function () {
            const otherPoliceAction = document.getElementById('other-police-action');
            otherPoliceAction.style.display = this.value === 'Other' ? 'block' : 'none';
        });

        document.getElementById('dvsa_offence_type').addEventListener('change', function () {
            const otherOffence = document.getElementById('dvsa-other-offence');
            otherOffence.style.display = this.value === 'Other' ? 'block' : 'none';
        });

        document.getElementById('dvsa_action').addEventListener('change', function () {
            const otherPoliceAction = document.getElementById('other-action');
            otherPoliceAction.style.display = this.value === 'Other' ? 'block' : 'none';
        });
    </script>
    <script>
    $('#company_id').on('change', function () {
        let companyId = $(this).val();

        $.ajax({
            url: '{{ route("get.depots.by.company") }}',
            type: 'GET',
            data: {
                company_id: companyId
            },
            success: function (data) {
                let options = '<option value="">Select Depot</option>';
                data.forEach(function (depot) {
                    options += `<option value="${depot.id}">${depot.name.toUpperCase()}</option>`;
                });
                $('#depot_id').html(options);
            },
            error: function (xhr) {
                alert(xhr.responseJSON.error || 'Failed to load depots');
                $('#depot_id').html('<option value="">Select Depot</option>');
            }
        });
    });
</script>

@endpush
