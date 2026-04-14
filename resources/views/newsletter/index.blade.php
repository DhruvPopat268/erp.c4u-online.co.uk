@extends('layouts.admin')
@section('page-title')
{{__('Newsletter')}}
@endsection
@push('script-page')
<link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/summernote.min.css" rel="stylesheet">

<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/summernote.min.js"></script>
<script src="https://code.jquery.com/jquery-3.5.1.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
<script>
    $(document).ready(function() {
        $('#summernote').summernote({
            tabsize: 2,
            height: 300,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link']],
                ['view', ['help']]
            ],
            buttons: {
                myPicture: function(context) {
                    var ui = $.summernote.ui;

                    // create button
                    var button = ui.button({
                        contents: '<i class="note-icon-picture"/>',
                        tooltip: 'Insert Image URL',
                        click: function() {
                            var url = prompt('Enter Image URL:');
                            if (url) {
                                context.invoke('editor.insertImage', url);
                            }
                        }
                    });

                    return button.render();   // return button as jquery object
                }
            },
            // Set default content when summernote is initialized
            callbacks: {
                onInit: function() {
                    $('#summernote').summernote('code', 'Dear {name},');
                }
            }
        });

        $('input[name="roles[]"]').change(function() {
            var namePlaceholder = 'Dear {name},';
            var currentContent = $('#summernote').summernote('code');
            var isChecked = $(this).is(':checked');
            var shouldUpdate = false;

            if ($('#driver').is(':checked') || $('#operator').is(':checked') || $('#newsletter').is(':checked')) {
                if (isChecked) {
                    if (!currentContent.includes(namePlaceholder)) {
                        currentContent = namePlaceholder + '<br><br>' + currentContent;
                        shouldUpdate = true;
                    }
                } else {
                    if (currentContent.startsWith(namePlaceholder)) {
                        currentContent = currentContent.replace(namePlaceholder + '<br><br>', '');
                        shouldUpdate = true;
                    }
                }
            }

            if (shouldUpdate) {
                $('#summernote').summernote('code', currentContent);
            }
        });

        // Show import modal when newsletter checkbox is clicked and checked
        $('#newsletter').click(function() {
            if ($(this).is(':checked')) {
                $('#importConfirmationModal').modal('show');
            }
        });

        // Handle file input change event
        $('#attachments').on('change', function() {
            var files = $(this)[0].files;
            var selectedFilesList = $('#selectedFilesList');
            selectedFilesList.empty(); // Clear existing list

            if (files.length > 0) {
                $('#selectedFilesContainer').show(); // Show the container
                $.each(files, function(index, file) {
                    selectedFilesList.append('<li>' + file.name + '</li>');
                });
            } else {
                $('#selectedFilesContainer').hide(); // Hide container if no files selected
            }
        });

        // Show loader and blur background on form submit, validate checkboxes
        $('form').submit(function(event) {
            // Check if any checkbox is checked
            var anyChecked = false;
            $('input[name="roles[]"]').each(function() {
                if ($(this).is(':checked')) {
                    anyChecked = true;
                }
            });

            if (!anyChecked) {
                event.preventDefault(); // Prevent form submission
                alert('Please select at least one checkbox.');
            } else {
                $('#loader').show();
                $('#blurBackground').show();
            }
        });
    });
</script>

@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="#">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Newsletter')}}</li>
@endsection
@php
use Carbon\Carbon;
@endphp

@section('action-btn')
    <div class="float-end">
        <a href="#" data-size="md" data-url="{{ route('import.newsletteremail') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('News Letter Email Import')}}" class="btn btn-sm btn-success"   style="background-color: #48494B; border-color:#48494B">
            <i class="ti ti-upload"></i>
        </a>

        <a href="{{ route('newsletter.show') }}"
                                                   class="btn btn-sm btn-success"
                                                   data-bs-whatever="{{__('View Budget Planner')}}" data-bs-toggle="tooltip" data-size="md"
                                                   data-bs-original-title="{{__('View')}}"  style="background-color: #48494B; border-color:#48494B"> <span class="text-white"> {{__('Show Email List')}}</span></a>
        <a href="{{ route('email.log') }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-size="md" data-bs-original-title="{{__('Show Email Log')}}"   style="background-color: #48494B; border-color:#48494B">
                                                    <i class="ti ti-list"></i> {{__('Show Email Log')}}</a>
    </div>
@endsection

@section('content')

    <div class="row">
        <div class="col-9" style="width: 100%">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="container mt-5">
                        <h2 style="margin-bottom: 30px;
    margin-top: -2%;">Send Newsletter</h2>
                        <form action="{{ route('send.email') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <div class="row">
                                    <div class="col">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="roles[]" value="Driver" id="driver">
                                            <label class="form-check-label" for="driver">Driver</label>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="roles[]" value="Operator" id="operator">
                                            <label class="form-check-label" for="operator">Operator</label>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="roles[]" value="NewsLetterEmail" id="newsletter">
                                            <label class="form-check-label" for="newsletter">Other Email</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="subject">Subject:</label>
                                <input type="text" class="form-control" name="subject" id="subject" required>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-6">
                                    <label for="header_image">Image:</label>
                                    <input type="file" class="form-control" name="header_image" id="header_image">
                                    <small class="form-text text-muted">Upload an image to be used as the image.</small>
                                </div>
                                <div class="col-md-6">
                                    <label for="header_image_url">Image URL (Optional):</label>
                                    <input type="text" class="form-control" name="header_image_url" id="header_image_url" placeholder="Enter URL for header image">
                                    <small class="form-text text-muted">Specify a URL if you want to use an existing image.</small>
                                </div>
                            </div>



                            <div class="form-group">
                                <label for="text">Body: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <span style="color: red">placeholder used is {name} </span></label>
                                <textarea class="form-control" name="text" id="summernote" required></textarea>
                            </div>

                            <div class="form-group row">
                                <div class="col-md-6">
                                    <label for="button_text">Button Text:</label>
                                    <input type="text" class="form-control" name="button_text" id="button_text" placeholder="Enter text for the button">
                                </div>
                                <div class="col-md-6">
                                <label for="button_url">Button URL:</label>
                                <input type="url" class="form-control" name="button_url" id="button_url" placeholder="Enter URL for the button">
                                </div>


                        </div>
                            <div class="form-group">
                                <label for="attachments">Attachments:</label>
                                <input type="file" class="form-control" name="attachments[]" id="attachments" multiple>
                            </div>
                            <!-- Display selected file names -->
                            <div class="form-group" id="selectedFilesContainer" style="display: none;">
                                <label>Attach Files:</label>
                                <ul id="selectedFilesList"></ul>
                            </div>
                            <button type="submit" class="btn btn-primary"   style="background-color: #48494B; border-color:#48494B">Submit</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loader and Background Blur -->
    <div id="loader" style="display: none;">
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
    <div id="blurBackground" style="display: none;"></div>

    <!-- Import Confirmation Modal -->
   <!-- Import Confirmation Modal -->
    <div class="modal fade" id="importConfirmationModal" tabindex="-1" role="dialog" aria-labelledby="importConfirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importConfirmationModalLabel">Import Email IDs</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to import email IDs data?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                    <a href="#" data-size="md" data-url="{{ route('import.newsletteremail') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('News Letter Import')}}" class="btn btn-success">
                        Yes, Import
                    </a>
                </div>
            </div>
        </div>
    </div>
    <style>
        #loader {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1050;
        }

        #blurBackground {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.7);
            z-index: 1040;
        }
    </style>

@endsection
