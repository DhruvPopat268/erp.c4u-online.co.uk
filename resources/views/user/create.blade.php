{{Form::open(array('url'=>'users','method'=>'post'))}}
<div class="modal-body">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
    {{ Form::label('companyname', __('Company Name'), ['class' => 'form-label']) }}
                {{ Form::select('companyname', ['' => __('Select a company')] + array_map('strtoupper', $companyName->toArray()), null, ['class' => 'form-control', 'data-toggle' => 'select', 'required' => 'required']) }}
    @error('companyname')
    <small class="invalid-name" role="alert">
        <strong class="text-danger">{{ $message }}</strong>
    </small>
    @enderror
</div>
        </div>

        @if(\Auth::user()->type != 'super admin')
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('depot_id', __('Depot Name'), ['class' => 'form-label']) }}
                <div id="depot-checkboxes">
                    <p class="text-muted">Select a company first</p>
                </div>
                @error('depot_id')
<small class="text-danger">{{ $message }}</small>
@enderror

            </div>
        </div>
        @endif
    </div>

    @if(\Auth::user()->type != 'super admin')
    <div class="row mt-3">
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('vehicle_group', __('Vehicle Group'), ['class' => 'form-label']) }}
                <div id="vehicle-group-checkboxes">
                    <p class="text-muted">Select a company first</p>
            </div>
            @error('vehicle_group_id')
<small class="text-danger">{{ $message }}</small>
@enderror

        </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('driver_group', __('Driver Group'), ['class' => 'form-label']) }}
                <div id="driver-group-checkboxes">
                    <p class="text-muted">Select a company first</p>
                </div>
                @error('driver_group_id')
<small class="text-danger">{{ $message }}</small>
@enderror

            </div>
        </div>
    </div>
    @endif


        <div class="row">
        <div class="col-md-6">
            <div class="form-group">
    {{ Form::label('username', __('Name'), ['class' => 'form-label']) }}
                {{Form::text('username',null,array('class'=>'form-control','placeholder'=>__('Enter User Name'),'required'=>'required'))}}
    @error('username')
    <small class="invalid-name" role="alert">
        <strong class="text-danger">{{ $message }}</strong>
    </small>
    @enderror
</div>

        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{Form::label('email',__('Email'),['class'=>'form-label'])}}
                {{Form::text('email',null,array('class'=>'form-control','placeholder'=>__('Enter User Email'),'required'=>'required'))}}
                @error('email')
                <small class="invalid-email" role="alert">
                    <strong class="text-danger">{{ $message }}</strong>
                </small>
                @enderror
            </div>
        </div>
        @if(\Auth::user()->type != 'super admin')
            <div class="form-group col-md-6">
                {{ Form::label('role', __('User Role'),['class'=>'form-label']) }}
                {!! Form::select('role', $roles, null,array('class' => 'form-control select','required'=>'required')) !!}
                @error('role')
                <small class="invalid-role" role="alert">
                    <strong class="text-danger">{{ $message }}</strong>
                </small>
                @enderror
            </div>
        @elseif(\Auth::user()->type == 'super admin')
            {!! Form::hidden('role', 'company', null,array('class' => 'form-control select2','required'=>'required')) !!}
        @endif
        <div class="col-md-6">
            <div class="form-group position-relative">
                {{ Form::label('password', __('Password'), ['class' => 'form-label']) }}
                <div class="input-group">
                    {{ Form::password('password', ['class' => 'form-control', 'placeholder' => __('Enter User Password'), 'required' => 'required', 'minlength' => "6", 'id' => 'password']) }}
                    <div class="input-group-append">
                        <span class="input-group-text" onclick="togglePassword()"  style="height: 42px;background-color: transparent;boder-color: transparent;border-color: transparent;">
                            <i class="fa fa-eye" id="togglePasswordIcon"></i>
                        </span>
                    </div>
                </div>
                @error('password')
                <small class="invalid-password" role="alert">
                    <strong class="text-danger">{{ $message }}</strong>
                </small>
                @enderror
            </div>
        </div>
        @if(!$customFields->isEmpty())
            <div class="col-md-6">
                <div class="tab-pane fade show" id="tab-2" role="tabpanel">
                    @include('customFields.formBuilder')
                </div>
            </div>
        @endif
    </div>

</div>

<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn  btn-primary">
</div>

{{Form::close()}}

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    function togglePassword() {
        const passwordField = document.getElementById('password');
        const toggleIcon = document.getElementById('togglePasswordIcon');
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
        } else {
            passwordField.type = 'password';
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        }
    }

</script>
<script>
    $('select[name="companyname"]').on('changed.bs.select change', function () {
        let companyId = $(this).val();
        if (!companyId) return;

        $.get("{{ route('depots.by.company', ':id') }}".replace(':id', companyId), function(data) {
            renderCheckboxes('#depot-checkboxes', 'depot_id[]', data);
        });

        $.get("{{ route('vehicle.groups.by.company', ':id') }}".replace(':id', companyId), function(data) {
            renderCheckboxes('#vehicle-group-checkboxes', 'vehicle_group_id[]', data);
        });

        $.get("{{ route('driver.groups.by.company', ':id') }}".replace(':id', companyId), function(data) {
            renderCheckboxes('#driver-group-checkboxes', 'driver_group_id[]', data);
        });
                        });

    function renderCheckboxes(container, name, data) {
        if (!$(container).length) {
            console.error('Container not found:', container);
            return;
        }

        let html = '';

        if (Object.keys(data).length === 0) {
            html = '<p class="text-muted">No data found</p>';
        } else {
            $.each(data, function(key, value) {
                html += `
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="${name}" value="${key}">
                    <label class="form-check-label">${value.toUpperCase()}</label>
                </div>`;
            });
        }

        $(container).html(html);
    }

</script>
<script>
$('form').on('submit', function(e) {

    let isValid = true;
    $('.validation-error').remove();

    // only for non super admin
    @if(\Auth::user()->type != 'super admin')
        if ($('input[name="depot_id[]"]:checked').length === 0) {
            $('#depot-checkboxes').after('<small class="text-danger validation-error">Depot is required</small>');
            isValid = false;
        }

        if ($('input[name="vehicle_group_id[]"]:checked').length === 0) {
            $('#vehicle-group-checkboxes').after('<small class="text-danger validation-error">Vehicle group is required</small>');
            isValid = false;
        }

        if ($('input[name="driver_group_id[]"]:checked').length === 0) {
            $('#driver-group-checkboxes').after('<small class="text-danger validation-error">Driver group is required</small>');
            isValid = false;
        }
    @endif

    if (!isValid) {
        e.preventDefault();
    }
});
</script>
