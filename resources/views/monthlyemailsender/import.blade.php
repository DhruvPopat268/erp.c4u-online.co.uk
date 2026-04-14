{{ Form::open(array('route' => array('MonthlyReport.import'),'method'=>'post', 'enctype' => "multipart/form-data")) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
        {{ Form::label('email_type', __('Email Type'), ['class' => 'form-label']) }}
        <div class="form-group">
            <select name="email_type" class="form-control" required>
                <option value="">{{ __('Select Email Type') }}</option>
                <option value="OCRS">{{ __('OCRS') }}</option>
                <option value="VU unaccounted distance">{{ __('VU Unaccounted Distance') }}</option>
            </select>
        </div>
      </div>
      <div class="col-md-12">
        {{ Form::label('files', __('Select PDF Files'), ['class' => 'form-label']) }}
        <div class="choose-file form-group">
          <label for="files" class="form-label">
            <input type="file" class="form-control" name="files[]" id="files" multiple required>
            <p class="upload_file"></p>
          </label>
        </div>
      </div>
    </div>
  </div>
  <div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Upload') }}" class="btn btn-primary">
  </div>
  {{ Form::close() }}
