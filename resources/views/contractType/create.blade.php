{{ Form::open(array('url' => 'contractType')) }}
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
            {{ Form::text('name', '', array('class' => 'form-control', 'required' => 'required')) }}
        </div>
        <div class="form-group">
            {{ Form::label('email', __('Email')) }}
            {{ Form::email('email', '', array('class' => 'form-control', 'required' => 'required')) }}
        </div>
                <div class="form-group">
            {{ Form::label('promotional_email', __('Promotional Email')) }}
            {{ Form::select('promotional_email', ['Yes' => 'Yes', 'No' => 'No'], null, array('class' => 'form-control', 'required' => 'required')) }}
        </div>
        <div class="form-group">
            {{ Form::label('ptc_library', __('PTC Library')) }}
            {{ Form::select('ptc_library', ['Yes' => 'Yes', 'No' => 'No'], null, array('class' => 'form-control', 'required' => 'required')) }}
        </div>
        <div class="form-group">
            {{ Form::label('address', __('Address')) }}
            {{ Form::text('address', '', array('class' => 'form-control', 'required' => 'required')) }}
        </div>
        <div class="form-group">
            {{ Form::label('contact', __('Contact')) }}
            {{ Form::text('contact', '', array('class' => 'form-control', 'required' => 'required')) }}
        </div>
        <div class="form-group">
            {{ Form::label('lc_check_status', __('LC Check Status')) }}
            {{ Form::select('lc_check_status', ['Enable' => 'Enable', 'Disable' => 'Disable'], null, array('class' => 'form-control', 'required' => 'required')) }}
        </div>
        <div>
            <div class="form-group">
            {{ Form::label('company_status', __('Company Status'), ['class' => 'form-label']) }}
            {{ Form::select('company_status', ['' => __('Please select'), 'Active' => 'Active', 'InActive' => 'InActive'], null, ['class' => 'form-control']) }}
         </div>

          <div class="form-group">
            {{ Form::label('payment_type', __('Driver Licence Add API Payment Type'), ['class' => 'form-label']) }}
            {{ Form::select('payment_type', ['' => 'Select Type', 'Prepaid' => 'Prepaid', 'Postpaid' => 'Postpaid'], null, ['class' => 'form-control', 'id' => 'payment_type', 'required' => 'required']) }}
        </div>

        {{-- 🔹 Coins input field (hidden by default) --}}
        <div class="form-group" id="coins-field" style="display: none;">
            {{ Form::label('coins', __('Coins'), ['class' => 'form-label']) }}
            {{ Form::number('coins', '', ['class' => 'form-control', 'placeholder' => 'Enter number of coins', 'min' => '0']) }}
        </div>

          <div class="form-group">
            {{ Form::label('fors_browse_policy', __('FORS Browse Policy'), ['class' => 'form-label']) }}
            {{ Form::date('fors_browse_policy', '', array('class' => 'form-control')) }}
         </div>

         <!--<div class="form-group">-->
         <!--   {{ Form::label('fors_silver_policy', __('FORS Silver Policy'), ['class' => 'form-label']) }}-->
         <!--   {{ Form::date('fors_silver_policy', '', array('class' => 'form-control')) }}-->
         <!--</div>-->

         <!--<div class="form-group">-->
         <!--   {{ Form::label('fors_gold_policy', __('FORS Gold Policy'), ['class' => 'form-label']) }}-->
         <!--   {{ Form::date('fors_gold_policy', '', array('class' => 'form-control')) }}-->
         <!--</div>-->

          <div class="form-group">
            {{ Form::label('public_liability', __('Public Liability'), ['class' => 'form-label']) }}
            {{ Form::date('public_liability', '', array('class' => 'form-control')) }}
         </div>

          <div class="form-group">
            {{ Form::label('goods_in_transit', __('Goods In Transit'), ['class' => 'form-label']) }}
            {{ Form::date('goods_in_transit', '', array('class' => 'form-control')) }}
         </div>

          <div class="form-group">
            {{ Form::label('public_liability_insurance', __('Public Liability Insurance'), ['class' => 'form-label']) }}
            {{ Form::date('public_liability_insurance', '', array('class' => 'form-control')) }}
         </div>

         <div id="insurance-fields">
    <div class="insurance-field">
        <div class="form-group">
            {{ Form::label('insurance_type[]', __('Insurance Type 1')) }}
            {{ Form::text('insurance_type[]', '', ['class' => 'form-control', 'required' => 'required', 'placeholder' => 'Enter Insurance Type']) }}
        </div>
        <div class="form-group">
            {{ Form::label('insurance_date[]', __('Insurance Date 1')) }}
            {{ Form::date('insurance_date[]', '', ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        <button type="button" class="remove-insurance-field btn btn-sm btn-danger mb-2">
            <i class="ti ti-trash text-white"></i>
        </button>
            <a href="#" id="add-insurance-field" class="btn btn-sm btn-primary mb-2">
        <i class="ti ti-plus"></i> {{ __('Add Insurance') }}
    </a>
    </div>
</div>




        <!-- New fields -->
        <div id="additional-fields">
            <div class="form-group">
                {{ Form::label('operator_role[]', __('Manager Role 1')) }}
                {{ Form::select('operator_role[]', ['Director' => 'Director','Manager' => 'Manager','Transport Manager' => 'Transport Manager'], null, array('class' => 'form-control', 'required' => 'required')) }}
            </div>
            <div class="form-group">
                {{ Form::label('device[]', __('Device 1')) }}
                {{ Form::select('device[]', ['Convey' => 'Convey', 'SJD' => 'SJD', 'Geotab'=>'Geotab', 'DigiDL' => 'DigiDL', 'other' => 'Other'], null, array('class' => 'form-control device-dropdown', 'required' => 'required')) }}
                <input type="text" name="device_other[]" class="form-control device-other" placeholder="Please specify" style="display:none; margin-top: 10px;">
            </div>
            <div class="form-group">
                {{ Form::label('operator_name[]', __('Manager Name 1')) }}
                {{ Form::text('operator_name[]', '', array('class' => 'form-control', 'required' => 'required')) }}
            </div>
            <div class="form-group">
                {{ Form::label('operator_phone[]', __('Manager Phone 1')) }}
                {{ Form::text('operator_phone[]', '', array('class' => 'form-control', 'required' => 'required')) }}
            </div>
            <!--<div class="form-group">-->
            <!--    {{ Form::label('operator_dob[]', __('Manager Date of Birth 1')) }}-->
            <!--    {{ Form::text('operator_dob[]', '', array('class' => 'form-control', 'required' => 'required', 'placeholder' => 'DD/MM/YYYY')) }}-->
            <!--</div>-->
            <div class="form-group">
                {{ Form::label('status[]', __('Status 1')) }}
                {{ Form::select('status[]', ['ACTIVE' => 'ACTIVE', 'INACTIVE' => 'INACTIVE'], null, array('class' => 'form-control', 'required' => 'required')) }}
            </div>
            <div class="form-group">
                {{ Form::label('compliance[]', __('Compliance 1')) }}
                {{ Form::select('compliance[]', ['YES' => 'YES', 'NO' => 'NO'], null, array('class' => 'form-control', 'required' => 'required')) }}
            </div>
            <div class="form-group">
                {{ Form::label('operator_email[]', __('Manager Email 1')) }}
                {{ Form::email('operator_email[]', '', array('class' => 'form-control', 'required' => 'required')) }}
            </div>

            <div class="float-end">
                <a href="#" id="add-additional-field" title="{{__('Add Additional Field')}}" class="btn btn-sm btn-primary mb-2" style="margin-top: -8%;">
                    <i class="ti ti-plus"></i>
                </a>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn btn-primary">
</div>
{{ Form::close() }}

<script>
$(document).ready(function () {
    var additionalFieldInsuranceCount = 1;

    $('#add-insurance-field').click(function (e) {
        e.preventDefault();
        additionalFieldInsuranceCount++;
        var newInsuranceField = `
            <div class="insurance-field">
                <div class="form-group">
                    <label>Insurance Type ${additionalFieldInsuranceCount}</label>
                    <input type="text" name="insurance_type[]" class="form-control" required placeholder="Enter Insurance Type">
                </div>
                <div class="form-group">
                    <label>Insurance Date ${additionalFieldInsuranceCount}</label>
                    <input type="date" name="insurance_date[]" class="form-control" required>
                </div>
                <button type="button" class="remove-insurance-field btn btn-sm btn-danger mb-2">
                    <i class="ti ti-trash text-white"></i>
                </button>
            </div>`;
        $('#insurance-fields').append(newInsuranceField);
    });

    $(document).on('click', '.remove-insurance-field', function () {
        $(this).closest('.insurance-field').remove();
        additionalFieldInsuranceCount--;
    });
});


    $(document).ready(function() {
        var directorFieldCount = 1;
        var additionalFieldCount = 1;

        $('#add-director-field').click(function() {
            directorFieldCount++;
            var newDirectorField = `
            `;
            $('#director-fields').append(newDirectorField);
        });

        $('#add-additional-field').click(function() {
            additionalFieldCount++;
            var newAdditionalField = `
                <div class="additional-field">
                    <div class="form-group">
                        {{ Form::label('operator_role[]', __('Manager Role ${additionalFieldCount}')) }}
                        {{ Form::select('operator_role[]', ['Director' => 'Director', 'Manager' => 'Manager', 'Transport Manager' => 'Transport Manager'], null, array('class' => 'form-control', 'required' => 'required')) }}
                    </div>
                    <div class="form-group">
                        {{ Form::label('device[]', __('Device ${additionalFieldCount}')) }}
                        <select name="device[]" class="form-control device-dropdown" required>
                            <option value="Convey">Convey</option>
                            <option value="SJD">SJD</option>
                            <option value="DigiDL">DigiDL</option>
                            <option value="Geotab">Geotab</option>
                            <option value="other">Other</option>
                        </select>
                        <input type="text" name="device_other[]" class="form-control device-other" placeholder="Please specify" style="display:none; margin-top: 10px;">
                    </div>
                    <div class="form-group">
                        {{ Form::label('operator_name[]', __('Manager Name ${additionalFieldCount}')) }}
                        {{ Form::text('operator_name[]', '', array('class' => 'form-control', 'required' => 'required')) }}
                    </div>
                    <div class="form-group">
                        {{ Form::label('operator_phone[]', __('Manager Phone ${additionalFieldCount}')) }}
                        {{ Form::text('operator_phone[]', '', array('class' => 'form-control', 'required' => 'required')) }}
                    </div>

                    <div class="form-group">
                        {{ Form::label('status[]', __('Status ${additionalFieldCount}')) }}
                        {{ Form::select('status[]', ['ACTIVE' => 'ACTIVE', 'INACTIVE' => 'INACTIVE'], null, array('class' => 'form-control', 'required' => 'required')) }}
                    </div>
                    <div class="form-group">
                        {{ Form::label('compliance[]', __('Compliance ${additionalFieldCount}')) }}
                        {{ Form::select('compliance[]', ['YES' => 'YES', 'NO' => 'NO'], null, array('class' => 'form-control', 'required' => 'required')) }}
                    </div>
                    <div class="form-group">
                        {{ Form::label('operator_email[]', __('Manager Email ${additionalFieldCount}')) }}
                        {{ Form::email('operator_email[]', '', array('class' => 'form-control', 'required' => 'required')) }}
                    </div>
                    <button type="button" class="remove-additional-field btn btn-sm btn-danger mb-2" style="margin-left: 92%; margin-top: -26%;">
                        <i class="ti ti-trash text-white"></i>
                    </button>
                </div>
            `;
            $('#additional-fields').append(newAdditionalField);
        });

        $(document).on('change', '.device-dropdown', function() {
            var selectedValue = $(this).val();
            var otherInput = $(this).siblings('.device-other');
            if (selectedValue === 'other') {
                otherInput.show();
            } else {
                otherInput.hide();
            }
        });

        $(document).on('click', '.remove-director-field', function() {
            $(this).closest('.director-field').remove();
            directorFieldCount--;
        });

        $(document).on('click', '.remove-additional-field', function() {
            $(this).closest('.additional-field').remove();
            additionalFieldCount--;
        });
    });
</script>
<script>
var myInput = document.getElementById("psw");
var length = document.getElementById("length");

// When the user clicks on the password field, show the message box
myInput.onfocus = function() {
  document.getElementById("message").style.display = "block";
}

// When the user clicks outside of the password field, hide the message box
myInput.onblur = function() {
  document.getElementById("message").style.display = "none";
}

// When the user starts to type something inside the password field
myInput.onkeyup = function() {
  // Validate length
  if(myInput.value.length === 7) {
    length.classList.remove("invalid");
    length.classList.add("valid");
  } else {
    length.classList.remove("valid");
    length.classList.add("invalid");
  }
}


</script>

<script>
$(document).ready(function() {
    // Confirm jQuery is running
    console.log('Document ready - jQuery working');

    // Payment type change handler
    $('#payment_type').on('change', function() {
        const selectedValue = $(this).val();
        console.log('Payment type changed:', selectedValue); // Debug line

        if (selectedValue === 'Prepaid') {
            $('#coins-field').slideDown(); // Show coins field
            $('#coins-field input').attr('required', true);
        } else {
            $('#coins-field').slideUp(); // Hide coins field
            $('#coins-field input').removeAttr('required').val('');
        }
    });

    // 🔹 Optional: trigger once on page load (for editing form)
    $('#payment_type').trigger('change');
});
</script>


