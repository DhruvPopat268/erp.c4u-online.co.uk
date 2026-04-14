{{ Form::model($contractType, array('route' => array('contractType.update', $contractType->id), 'method' => 'PUT')) }}

<style>
  .container {
    background-color: #f1f1f1;
    padding: 20px;
  }

  /* The message box is shown when the user clicks on the password field */
  #message {
    display: none;
    background: #f1f1f1;
    color: #000;
    position: relative;
    padding: 20px;
    margin-top: 10px;
  }

  #message p {
    padding: 10px 35px;
    font-size: 18px;
  }

  /* Add a green text color and a checkmark when the requirements are right */
  .valid {
    color: green;
  }

  .valid:before {
    position: relative;
    left: -35px;
    content: "✔";
  }

  /* Add a red text color and an "x" when the requirements are wrong */
  .invalid {
    color: red;
  }

  .invalid:before {
    position: relative;
    left: -35px;
    content: "✖";
  }
</style>

<div class="modal-body">
  <div class="row">
    <div class="form-group">
      {{ Form::label('name', __('Name')) }}
      {{ Form::text('name', $contractType['name'] ?? '', array('class' => 'form-control', 'required' => 'required')) }}
    </div>
    <div class="form-group">
      {{ Form::label('email', __('Email')) }}
      {{ Form::text('email', $contractType['email'] ?? '', array('class' => 'form-control')) }}
    </div>
        <div class="form-group">
        {{ Form::label('promotional_email', __('Promotional Email')) }}
        {{ Form::select('promotional_email', ['Yes' => 'Yes', 'No' => 'No'], old('promotional_email', $contractType->promotional_email), ['class' => 'form-control', 'required' => 'required']) }}
    </div>
                <div class="form-group">
        {{ Form::label('ptc_library', __('PTC Library')) }}
        {{ Form::select('ptc_library', ['Yes' => 'Yes', 'No' => 'No'], old('ptc_library', $contractType->ptc_library), ['class' => 'form-control', 'required' => 'required']) }}
    </div>
    <div class="form-group">
      {{ Form::label('address', __('Address')) }}
      {{ Form::text('address', $contractType['address'] ?? '', array('class' => 'form-control')) }}
    </div>
    <div class="form-group">
      {{ Form::label('contact', __('Contact')) }}
      @php
      // Remove '+44 ' prefix if present
      $contactValue = isset($contractType['contact']) ? str_replace('+44 ', '', $contractType['contact']) : '';
      @endphp
      {{ Form::text('contact', $contactValue, array('class' => 'form-control')) }}
    </div>
        <div class="form-group">
        {{ Form::label('lc_check_status', __('LC Check Status')) }}
        {{ Form::select('lc_check_status', ['Enable' => 'Enable', 'Disable' => 'Disable'], old('lc_check_status', $contractType->lc_check_status), ['class' => 'form-control', 'required' => 'required']) }}
    </div>

    <div>
        <div class="form-group">
        {{ Form::label('company_status', __('Company Status'), ['class' => 'form-label']) }}
        {{ Form::select('company_status', ['' => __('Please select'), 'Active' => 'Active', 'InActive' => 'InActive'], old('company_status', $contractType->company_status), ['class' => 'form-control']) }}
     </div>

      <div class="form-group">
    {{ Form::label('payment_type', __('Driver Licence Add API Payment Type')) }}
    {{ Form::select('payment_type', ['' => 'Select', 'Prepaid' => 'Prepaid', 'Postpaid' => 'Postpaid'], old('payment_type', $contractType->payment_type ?? ''), ['class' => 'form-control', 'id' => 'payment_type', 'required' => 'required']) }}
</div>

<div class="form-group" id="coins-field" style="display: none;">
    {{ Form::label('coins', __('Coins')) }}
    {{ Form::number('coins', old('coins', $contractType->coins ?? ''), ['class' => 'form-control', 'id' => 'coins', 'placeholder' => 'Enter coins']) }}
</div>


      <div class="form-group">
        {{ Form::label('fors_browse_policy', __('FORS Browse Policy'), ['class' => 'form-label']) }}
        {{ Form::date('fors_browse_policy', $contractType->fors_browse_policy, array('class' => 'form-control')) }}
     </div>

     <!--<div class="form-group">-->
     <!--   {{ Form::label('fors_silver_policy', __('FORS Silver Policy'), ['class' => 'form-label']) }}-->
     <!--   {{ Form::date('fors_silver_policy',  $contractType->fors_silver_policy, array('class' => 'form-control')) }}-->
     <!--</div>-->

     <!--<div class="form-group">-->
     <!--   {{ Form::label('fors_gold_policy', __('FORS Gold Policy'), ['class' => 'form-label']) }}-->
     <!--   {{ Form::date('fors_gold_policy',  $contractType->fors_gold_policy, array('class' => 'form-control')) }}-->
     <!--</div>-->

      <div class="form-group">
        {{ Form::label('public_liability', __('Public Liability'), ['class' => 'form-label']) }}
        {{ Form::date('public_liability',  $contractType->public_liability, array('class' => 'form-control')) }}
     </div>

      <div class="form-group">
        {{ Form::label('goods_in_transit', __('Goods In Transit'), ['class' => 'form-label']) }}
        {{ Form::date('goods_in_transit',  $contractType->goods_in_transit, array('class' => 'form-control')) }}
     </div>

      <div class="form-group">
        {{ Form::label('public_liability_insurance', __('Public Liability Insurance'), ['class' => 'form-label']) }}
        {{ Form::date('public_liability_insurance',  $contractType->public_liability_insurance, array('class' => 'form-control')) }}
     </div>

<div id="insurance-wrapper">
    @foreach($contractType->insurances ?? [] as $index => $insurance)
        <div class="insurance-field mb-3">
            <div class="form-group">
                {{ Form::label("insurance_type[]", __('Insurance Type') . ' ' . ($index + 1)) }}
                {{ Form::text("insurance_type[]", $insurance->insurance_type, ['class' => 'form-control', 'required']) }}
            </div>
            <div class="form-group">
                {{ Form::label("insurance_date[]", __('Insurance Date') . ' ' . ($index + 1)) }}
                {{ Form::date("insurance_date[]", $insurance->insurance_date, ['class' => 'form-control', 'required']) }}
            </div>
            <button type="button" class="remove-insurance btn btn-sm btn-danger mb-2">
            <i class="ti ti-trash text-white"></i>
        </button>


        </div>
    @endforeach
</div>
<button type="button" id="add-insurance" class="btn btn-sm btn-primary mb-2">+ Add Insurance</button>




    {{--  <!-- Director fields -->
    <div id="director-fields">
      @php
      $directors = json_decode($contractType->director_name, true) ?? [];
      $dobs = json_decode($contractType->director_dob, true) ?? [];
      @endphp

      @foreach ($directors as $i => $director)
      <div class="director-field">
        <div class="form-group">
          {{ Form::label("director_name[]", __('Director Name') . ' ' . ($i + 1)) }}
          {{ Form::text("director_name[]", $director ?? '', array('class' => 'form-control')) }}
        </div>
        <!--<div class="form-group" style="width: 90%">-->
        <!--  {{ Form::label("director_dob[]", __('Director Date of Birth') . ' ' . ($i + 1)) }}-->
        <!--  {{ Form::date("director_dob[]", $dobs[$i] ?? '', array('class' => 'form-control')) }}-->
        <!--</div>-->
        <button type="button" class="remove-director-field btn btn-sm btn-danger mb-2" style="margin-left: 92%; margin-top: -22%;">
          <i class="ti ti-trash text-white"></i>
        </button>
      </div>
      @endforeach
    </div>
    <div class="float-end">
            <button type="button" id="add-director-field" title="{{__('Add Director')}}" class="btn btn-sm btn-primary mb-2" style="margin-left: 92%; margin-top: -47%;">
        <i class="ti ti-plus"></i>
      </button>
    </div>  --}}

    <!-- Additional fields -->
    <div id="additional-fields">
      @php
      $operator_role = json_decode($contractType->operator_role, true) ?? [];
      $devices = json_decode($contractType->device, true) ?? [];
      $operator_names = json_decode($contractType->operator_name, true) ?? [];
      $operator_phones = json_decode($contractType->operator_phone, true) ?? [];
      $statuses = json_decode($contractType->status, true) ?? [];
      $compliances = json_decode($contractType->compliance, true) ?? [];
      $operator_emails = json_decode($contractType->operator_email, true) ?? [];
      $operator_dobs = json_decode($contractType->operator_dob, true) ?? []; // Ensure this is defined correctly

      @endphp

      @foreach ($devices as $i => $device)
      <div class="additional-field">
        <div class="form-group">
            {{ Form::label("operator_role[]", __('Manager Role') . ' ' . ($i + 1)) }}
            {{ Form::select("operator_role[]", ['Director' => 'Director','Manager' => 'Manager','Transport Manager' => 'Transport Manager'], $operator_role[$i] ?? '', ['class'
            => 'form-control']) }}
          </div>
        <div class="form-group">
            {{ Form::label("device[]", __('Device') . ' ' . ($i + 1)) }}
            @php
            $deviceOptions = ['Convey' => 'Convey', 'SJD' => 'SJD', 'Geotab' => 'Geotab', 'DigiDL' => 'DigiDL', 'other' => 'Other'];
            $selectedDevice = in_array($devices[$i], $deviceOptions) ? $devices[$i] : 'other';
            @endphp
            {{ Form::select("device[]", $deviceOptions, $selectedDevice, ['class' => 'form-control device-select']) }}
            <input type="text" name="device_other[]" class="form-control device-other"
                   style="{{ $selectedDevice === 'other' ? '' : 'display: none;' }}"
                   placeholder="Specify other device" value="{{ $selectedDevice === 'other' ? $devices[$i] : '' }}">
        </div>


        <div class="form-group">
          {{ Form::label("operator_name[]", __('Manager Name') . ' ' . ($i + 1)) }}
          {{ Form::text("operator_name[]", $operator_names[$i] ?? '', array('class' => 'form-control')) }}
        </div>
        <div class="form-group">
          {{ Form::label("operator_phone[]", __('Manager Phone') . ' ' . ($i + 1)) }}
          {{ Form::text("operator_phone[]", $operator_phones[$i] ?? '', array('class' => 'form-control')) }}
        </div>
        <!--<div class="form-group">-->
        <!--    {{ Form::label("operator_dob[]", __('Manager Date of Birth') . ' ' . ($i + 1)) }}-->
        <!--    {{ Form::text("operator_dob[]", $operator_dobs[$i] ?? '', array('class' => 'form-control','placeholder' => 'DD/MM/YYYY')) }}-->
        <!--  </div>-->

        <div class="form-group">
          {{ Form::label("status[]", __('Status') . ' ' . ($i + 1)) }}
          {{ Form::select("status[]", ['ACTIVE' => 'ACTIVE', 'INACTIVE' => 'INACTIVE'], $statuses[$i] ?? '', ['class'
          => 'form-control']) }}
        </div>

        <div class="form-group">
          {{ Form::label("compliance[]", __('Compliance') . ' ' . ($i + 1)) }}
          {{ Form::select("compliance[]", ['YES' => 'YES', 'NO' => 'NO'], $compliances[$i] ?? '', array('class' =>
          'form-control')) }}
        </div>

        <div class="form-group">
          {{ Form::label("operator_email[]", __('Manager Email') . ' ' . ($i + 1)) }}
          {{ Form::email("operator_email[]", $operator_emails[$i] ?? '', array('class' => 'form-control')) }}
        </div>
        <button type="button" class="remove-additional-field btn btn-sm btn-danger mb-2" style="margin-left: 92%;
          margin-top: -22%;">
          <i class="ti ti-trash text-white"></i>
        </button>
      </div>
      @endforeach
    </div>
    <div class="float-end">
            <button type="button" id="add-additional-field" title="{{__('Add Additional Field')}}" class="btn btn-sm btn-primary mb-2" style="margin-left: -7%; margin-top: -5%;position: absolute;">
        <i class="ti ti-plus"></i>
      </button>
    </div>
  </div>
</div>
<div class="modal-footer">
  <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
  <input type="submit" value="{{__('Update')}}" class="btn btn-primary">
</div>
{{ Form::close() }}

<script>
$(document).ready(function () {
    let insuranceCount = $('#insurance-wrapper .insurance-field').length;

    // Add insurance field
    $('#add-insurance').on('click', function () {
        insuranceCount++;
        let newField = `
            <div class="insurance-field mb-3">
                <div class="form-group">
                    <label>Insurance Type ${insuranceCount}</label>
                    <input type="text" name="insurance_type[]" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Insurance Date ${insuranceCount}</label>
                    <input type="date" name="insurance_date[]" class="form-control" required>
                </div>
                <button type="button" class="btn btn-sm btn-danger mb-2 remove-insurance"><i class="ti ti-trash text-white"></i></button>
            </div>
        `;
        $('#insurance-wrapper').append(newField);
    });

    // Remove insurance field + reindex labels
    $(document).on('click', '.remove-insurance', function () {
        $(this).closest('.insurance-field').remove();

        // reindex
        insuranceCount = 0;
        $('#insurance-wrapper .insurance-field').each(function () {
            insuranceCount++;
            $(this).find('label').eq(0).text(`Insurance Type ${insuranceCount}`);
            $(this).find('label').eq(1).text(`Insurance Date ${insuranceCount}`);
        });
    });
});
</script>

<script>
  $(document).ready(function () {
    var additionalFieldCount = {{ count($devices) > 0 ? count($devices) : 1 }};

    $('#add-director-field').click(function () {
      directorFieldCount++;
      var newDirectorField = `
      `;
      $('#director-fields').append(newDirectorField);
    });

    $('#add-additional-field').click(function () {
      additionalFieldCount++;
      var newAdditionalField = `
        <div class="additional-field">
            <div class="form-group">
            <label for="operator_role[]">Operator Role ${additionalFieldCount}</label>
            <select name="operator_role[]" class="form-control" required>
              <option value="Director">Director</option>
              <option value="Manager">Manager</option>
              <option value="Transport Manager">Transport Manager</option>
            </select>
          </div>
          <div class="form-group">
            <label for="device[]">Device ${additionalFieldCount}</label>
            <select name="device[]" class="form-control device-select" required>
              <option value="Convey">Convey</option>
              <option value="SJD">SJD</option>
              <option value="Geotab">Geotab</option>
              <option value="DigiDL">DigiDL</option>
              <option value="other">Other</option>
            </select>
            <input type="text" name="device_other[]" class="form-control device-other" style="display: none;" placeholder="Specify other device">
          </div>
          <div class="form-group">
            <label for="operator_name[]">Operator Name ${additionalFieldCount}</label>
            <input type="text" name="operator_name[]" class="form-control" required>
          </div>
          <div class="form-group">
            <label for="operator_phone[]">Operator Phone ${additionalFieldCount}</label>
            <input type="text" name="operator_phone[]" class="form-control" required>
          </div>

          <div class="form-group">
            <label for="status[]">Status ${additionalFieldCount}</label>
            <select name="status[]" class="form-control" required>
              <option value="ACTIVE">ACTIVE</option>
              <option value="INACTIVE">INACTIVE</option>
            </select>
          </div>
          <div class="form-group">
            <label for="compliance[]">Compliance ${additionalFieldCount}</label>
            <select name="compliance[]" class="form-control" required>
              <option value="YES">YES</option>
              <option value="NO">NO</option>
            </select>
          </div>
          <div class="form-group">
            <label for="operator_email[]">Operator Email ${additionalFieldCount}</label>
            <input type="email" name="operator_email[]" class="form-control" required>
          </div>
          <button type="button" class="remove-additional-field btn btn-sm btn-danger mb-2" style="margin-left: 92%; margin-top: -22%;">
            <i class="ti ti-trash text-white"></i>
          </button>
        </div>
      `;
      $('#additional-fields').append(newAdditionalField);
    });

    $(document).on('click', '.remove-director-field', function () {
      $(this).closest('.director-field').remove();
      directorFieldCount--;
    });

    $(document).on('click', '.remove-additional-field', function () {
      $(this).closest('.additional-field').remove();
      additionalFieldCount--;
    });

    $('.device-select').change(function () {
        var selectedDevice = $(this).val();
        if (selectedDevice === 'other') {
            $(this).siblings('.device-other').show().prop('required', true);
        } else {
            $(this).siblings('.device-other').hide().prop('required', false);
        }
    });


    // On form submission, remove '+44 ' prefix from operator_phone fields
    $('form').submit(function () {
      $('input[name^="operator_phone"]').each(function () {
        var currentValue = $(this).val();
        if (currentValue.startsWith('+44 ')) {
          $(this).val(currentValue.substring(4)); // Remove '+44 ' prefix
        }
      });
    });
  });
</script>

<script>
  var myInput = document.getElementById("psw");
  var length = document.getElementById("length");

  // Function to validate the length of the input
  function validateLength() {
    if (myInput.value.length === 7) {
      length.classList.remove("invalid");
      length.classList.add("valid");
    } else {
      length.classList.remove("valid");
      length.classList.add("invalid");
    }
  }

  // When the user clicks on the input field, show the message box
  myInput.onfocus = function () {
    document.getElementById("message").style.display = "block";
  }

  // When the user clicks outside of the input field, hide the message box
  myInput.onblur = function () {
    document.getElementById("message").style.display = "none";
  }

  // When the user starts to type something inside the input field
  myInput.onkeyup = function () {
    validateLength();
  }

  // Validate the input length on page load in case of pre-filled value
  window.onload = function () {
    validateLength();
  }
</script>
<script>
$(document).ready(function() {
    function toggleCoinsField() {
        const selected = $('#payment_type').val();
        if (selected === 'Prepaid') {
            $('#coins-field').slideDown();
            $('#coins').attr('required', true);
        } else {
            $('#coins-field').slideUp();
            $('#coins').removeAttr('required').val('');
        }
    }

    // Run on change
    $('#payment_type').on('change', toggleCoinsField);

    // Run on page load (for edit mode)
    toggleCoinsField();
});
</script>

