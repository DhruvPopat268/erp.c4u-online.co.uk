@if ($driverSignature)
    <form id='form_pad' method="post" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="id" value="{{ $driverSignature->id }}">
        <div class="modal-body">
            <div class="row">
                <div class="form-control">
                    <canvas id="signature-pad" class="signature-pad" height="200"></canvas>
                    <input type="hidden" name="driver_signature" id="SignupImage1">
                </div>
                <div class="mt-1">
                    <button type="button" class="btn-sm btn-danger" id="clearSig">{{__('Clear')}}</button>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary btn-light" data-bs-dismiss="modal">{{__('Cancel')}}</button>
            <button type="button" id="addSig" class="btn btn-primary ms-2">{{__('Sign')}}</button>
        </div>
    </form>
@else
    <p>{{ __('No signature data available.') }}</p>
@endif


<script src="{{ asset('assets/js/plugins/signature_pad/signature_pad.min.js') }}"></script>
<script>
    var signature = {
        canvas: null,
        clearButton: null,
        saveButton: null,
        signaturePad: null,

        init: function init() {
            this.canvas = document.querySelector(".signature-pad");
            this.clearButton = document.getElementById('clearSig');
            this.saveButton = document.getElementById('addSig');
            this.signaturePad = new SignaturePad(this.canvas);

            this.clearButton.addEventListener('click', function (event) {
                signature.signaturePad.clear();
            });

            this.saveButton.addEventListener('click', function (event) {
                var data = signature.signaturePad.toDataURL('image/png');
                $('#SignupImage1').val(data);

                $.ajax({
                    url: '{{ route("BronzesignatureStore") }}',
                    type: 'POST',
                    data: $("form").serialize(),
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function (data) {
                        if (data.Success) {
                            location.reload();
                            toastr.success(data.message, 'Success');
                            $("#exampleModal").modal('hide');
                        } else {
                            toastr.error(data.message, 'Error');
                        }
                    },
                    error: function () {
                        toastr.error('An error occurred. Please try again.');
                    }
                });
            });
        }
    };
    signature.init();

</script>
