@extends('layouts.admin')
@php
$attachments=\App\Models\Utility::get_file('contract_attechment');
@endphp
@push('css-page')
<link rel="stylesheet" href="{{asset('css/summernote/summernote-bs4.css')}}">
<link rel="stylesheet" href="{{asset('assets/css/plugins/dropzone.min.css')}}">
<link rel="stylesheet" href="{{asset('css/test.css')}}">
<style>
   .datetested {
   color: #505a5f;
   font-size: 1.1875rem;
   line-height: 1.3157894737;
   font-family: "GDS Transport", arial, sans-serif;
   -webkit-font-smoothing: antialiased;
   -moz-osx-font-smoothing: grayscale;
   font-weight: 400;
   display: block;
   text-align: left;
   }
   .datetestedvalue {
   margin-bottom: 5px !important;
   font-size: 1.1875rem;
   line-height: 1.3157894737;
   color: #0b0c0c;
   font-family: "GDS Transport", arial, sans-serif;
   -webkit-font-smoothing: antialiased;
   -moz-osx-font-smoothing: grayscale;
   font-weight: 700;
   display: block;
   margin-top: 0;
   }
   #pass-fail {
   color: #00703c;
   margin-bottom: 0 !important;
   font-size: 2rem;
   line-height: 1.0416666667;
   font-family: "GDS Transport", arial, sans-serif;
   -webkit-font-smoothing: antialiased;
   -moz-osx-font-smoothing: grayscale;
   font-weight: 700;
   display: block;
   margin-top: -20px;
   }
   .datetestedmileage {
   color: #505a5f;
   font-size: 1.1875rem;
   line-height: 1.3157894737;
   font-family: "GDS Transport", arial, sans-serif;
   -webkit-font-smoothing: antialiased;
   -moz-osx-font-smoothing: grayscale;
   font-weight: 400;
   display: block;
   text-align: left;
   margin-left: 135%;
   margin-top: -40%;
   }
   .datetestedvaluemileage {
   margin-bottom: 5px !important;
   font-size: 1.1875rem;
   line-height: 1.3157894737;
   color: #0b0c0c;
   font-family: "GDS Transport", arial, sans-serif;
   -webkit-font-smoothing: antialiased;
   -moz-osx-font-smoothing: grayscale;
   font-weight: 700;
   display: block;
   margin-top: -92px;
   margin-left: 135%;
   }
   .datetestedcertificate {
   color: #505a5f;
   font-size: 1.1875rem;
   line-height: 1.3157894737;
   font-family: "GDS Transport", arial, sans-serif;
   -webkit-font-smoothing: antialiased;
   -moz-osx-font-smoothing: grayscale;
   font-weight: 400;
   display: block;
   text-align: left;
   margin-left: 418%;
   margin-top: -76%;
   }
   .datetestedvaluecertificate {
   margin-bottom: 5px !important;
   font-size: 1.1875rem;
   line-height: 1.3157894737;
   color: #0b0c0c;
   font-family: "GDS Transport", arial, sans-serif;
   -webkit-font-smoothing: antialiased;
   -moz-osx-font-smoothing: grayscale;
   font-weight: 700;
   display: block;
   margin-top: -130px;
   margin-left: 419%;
   }
   .datetestedlocation {
   color: #505a5f;
   font-size: 1.1875rem;
   line-height: 1.3157894737;
   font-family: "GDS Transport", arial, sans-serif;
   -webkit-font-smoothing: antialiased;
   -moz-osx-font-smoothing: grayscale;
   font-weight: 400;
   display: block;
   text-align: left;
   margin-left: 136%;
   margin-top: 21%;
   }
   .datetestedvalueloction {
   margin-bottom: 5px !important;
   font-size: 1.1875rem;
   line-height: 1.3157894737;
   color: #0b0c0c;
   font-family: "GDS Transport", arial, sans-serif;
   -webkit-font-smoothing: antialiased;
   -moz-osx-font-smoothing: grayscale;
   font-weight: 700;
   display: block;
   margin-top: 23%;
   margin-left: 137%;
   }
   .datetestedexpirydate {
   color: #505a5f;
   font-size: 1.1875rem;
   line-height: 1.3157894737;
   font-family: "GDS Transport", arial, sans-serif;
   -webkit-font-smoothing: antialiased;
   -moz-osx-font-smoothing: grayscale;
   font-weight: 400;
   display: block;
   text-align: left;
   margin-left: 418%;
   margin-top: 26%;
   }
   .datetestedvalueexpirydate {
   margin-bottom: 5px !important;
   font-size: 1.1875rem;
   line-height: 1.3157894737;
   color: #0b0c0c;
   font-family: "GDS Transport", arial, sans-serif;
   -webkit-font-smoothing: antialiased;
   -moz-osx-font-smoothing: grayscale;
   font-weight: 700;
   display: block;
   margin-top: 56px;
   margin-left: 419%;
   }
   .heading h3 {
   font-size: 1.1875rem;
   line-height: 1.3157894737;
   font-family: "GDS Transport", arial, sans-serif;
   -webkit-font-smoothing: antialiased;
   -moz-osx-font-smoothing: grayscale;
   font-weight: 400;
   display: block;
   color: #505a5f;
   margin-left: 35%;
   }
   .heading li {
   font-weight: 700 !important;
   text-align: -webkit-match-parent;
   list-style-type: disc;
   font-size: 1.1875rem;
   line-height: 1.3157894737;
   font-family: "GDS Transport", arial, sans-serif;
   -webkit-font-smoothing: antialiased;
   color: #0b0c0c;
   margin-top: -17px;
   margin-bottom: 15px;
   padding-left: 0;
   margin-left: 35%;
   width: 60ch;
   }
   .heading h1 {
   text-decoration: underline;
   color: #1d70b8;
   cursor: pointer;
   list-style: inside disclosure-closed;
   font-size: 1.1875rem;
   line-height: 1.3157894737;
   font-family: "GDS Transport", arial, sans-serif;
   -webkit-font-smoothing: antialiased;
   -moz-osx-font-smoothing: grayscale;
   font-weight: 400;
   margin-bottom: 20px;
   display: block;
   margin-left: 27%;
   }
   .heading p {
   display: block;
   margin-top: 0;
   margin-bottom: 20px;
   font-size: 1.1875rem;
   line-height: 1.3157894737;
   font-family: "GDS Transport", arial, sans-serif;
   -webkit-font-smoothing: antialiased;
   -moz-osx-font-smoothing: grayscale;
   font-weight: 400;
   color: #0b0c0c;
   margin-bottom: 20px;
   margin-left: 27%;
   }
   .govuk-details[open]>.govuk-details__summary:before {
   display: block;
   width: 0;
   height: 0;
   border-style: solid;
   border-color: rgba(0, 0, 0, 0);
   -webkit-clip-path: polygon(0% 0%, 50% 100%, 100% 0%);
   clip-path: polygon(0% 0%, 50% 100%, 100% 0%);
   border-width: 12.124px 7px 0 7px;
   border-top-color: inherit;
   }
   .govuk-details__summary:before {
   content: "";
   position: absolute;
   top: -1px;
   bottom: 0;
   left: 0;
   margin: auto;
   display: block;
   width: 0;
   height: 0;
   border-style: solid;
   border-color: rgba(0, 0, 0, 0);
   -webkit-clip-path: polygon(0% 0%, 100% 50%, 0% 100%);
   clip-path: polygon(0% 0%, 100% 50%, 0% 100%);
   border-width: 7px 0 7px 12.124px;
   border-left-color: inherit;
   }
   .govuk-details__summary {
   display: inline-block;
   position: relative;
   margin-bottom: 5px;
   padding-left: 25px;
   color: #1d70b8;
   cursor: pointer;
   }
   details[open]>summary:first-of-type {
   list-style-type: disclosure-open;
   }
   details>summary:first-of-type {
   list-style: inside disclosure-closed;
   }
   .govuk-details {
   font-family: "GDS Transport", arial, sans-serif;
   -webkit-font-smoothing: antialiased;
   -moz-osx-font-smoothing: grayscale;
   font-weight: 400;
   font-size: 1.1875rem;
   line-height: 1.3157894737;
   line-height: 1.25;
   color: #0b0c0c;
   margin-bottom: 20px;
   display: block;
   margin-left: 3%;
   }
   .govuk-details__text {
   padding-top: 15px;
   padding-bottom: 15px;
   padding-left: 20px;
   border-left: 5px solid #b1b4b6;
   }
   .govuk-details__summary-text {
   text-decoration: underline;
   }
   .dvsa-vrm {
   display: inline-block;
   min-width: 150px;
   font: 30px UK-VRM, Verdana, sans-serif;
   padding: .4em .2em;
   text-align: center;
   background-color: #fd0;
   border-radius: .25em;
   text-transform: uppercase;
   }
   .govuk-\!-margin-bottom-1 {
   margin-bottom: 5px !important;
   }
   .govuk-heading-xl {
   color: #0b0c0c;
   font-family: "GDS Transport", arial, sans-serif;
   -webkit-font-smoothing: antialiased;
   -moz-osx-font-smoothing: grayscale;
   font-weight: 700;
   font-size: 32px;
   font-size: 2rem;
   line-height: 1.09375;
   display: block;
   margin-top: 0;
   margin-bottom: 30px;
   }
   .govuk-grid-column-one-third {
   box-sizing: border-box;
   width: 100%;
   padding: 0 15px;
   }
   .govuk-grid-column-one-third2 {
   box-sizing: border-box;
   width: 100%;
   padding: 0 15px;
   }
   .govuk-caption-m {
   font-family: "GDS Transport", arial, sans-serif;
   -webkit-font-smoothing: antialiased;
   -moz-osx-font-smoothing: grayscale;
   font-weight: 400;
   font-size: 16px;
   font-size: 1rem;
   line-height: 1.25;
   display: block;
   color: #505a5f;
   }
   .govuk-caption-m2 {
   font-family: "GDS Transport", arial, sans-serif;
   -webkit-font-smoothing: antialiased;
   -moz-osx-font-smoothing: grayscale;
   font-weight: 400;
   font-size: 1.1875rem;
   line-height: 1.3157894737;
   display: block;
   color: #505a5f;
   }
   .govuk-caption-m3 {
   font-family: "GDS Transport", arial, sans-serif;
   -webkit-font-smoothing: antialiased;
   -moz-osx-font-smoothing: grayscale;
   font-weight: 400;
   font-size: 1.1875rem;
   line-height: 1.3157894737;
   display: block;
   color: #505a5f;
   }
   .govuk-heading-m {
   color: #0b0c0c;
   font-family: "GDS Transport", arial, sans-serif;
   -webkit-font-smoothing: antialiased;
   -moz-osx-font-smoothing: grayscale;
   font-weight: 700;
   font-size: 18px;
   font-size: 1.125rem;
   line-height: 1.1111111111;
   display: block;
   margin-top: 0;
   margin-bottom: 15px;
   }
   .govuk-heading-m2 {
   color: #0b0c0c;
   font-family: "GDS Transport", arial, sans-serif;
   -webkit-font-smoothing: antialiased;
   -moz-osx-font-smoothing: grayscale;
   font-weight: 700;
   font-size: 1.1875rem;
   line-height: 1.3157894737;
   display: block;
   margin-top: 0;
   margin-bottom: 15px;
   }
   .govuk-heading-m3 {
   color: #0b0c0c;
   font-family: "GDS Transport", arial, sans-serif;
   -webkit-font-smoothing: antialiased;
   -moz-osx-font-smoothing: grayscale;
   font-weight: 700;
   font-size: 1.1875rem;
   line-height: 1.3157894737;
   display: block;
   margin-top: 0;
   margin-bottom: 15px;
   }
   .govuk-caption-l {
   font-family: "GDS Transport", arial, sans-serif;
   -webkit-font-smoothing: antialiased;
   -moz-osx-font-smoothing: grayscale;
   font-weight: 400;
   font-size: 18px;
   font-size: 1.125rem;
   line-height: 1.1111111111;
   display: block;
   margin-bottom: 5px;
   color: #505a5f;
   }
   .govuk-heading-l {
   color: #0b0c0c;
   font-family: "GDS Transport", arial, sans-serif;
   -webkit-font-smoothing: antialiased;
   -moz-osx-font-smoothing: grayscale;
   font-weight: 700;
   font-size: 24px;
   font-size: 1.5rem;
   line-height: 1.0416666667;
   display: block;
   margin-top: 0;
   margin-bottom: 20px;
   }
   @media (min-width: 40.0625em) {
   .govuk-heading-xl {
   margin-bottom: 50px;
   font-size: 3rem;
   line-height: 1.0416666667;
   }
   .govuk-grid-column-one-third {
   width: 33.3333333333%;
   float: left;
   }
   .govuk-caption-m {
   font-size: 19px;
   font-size: 1.0875rem;
   line-height: 1.3157894737;
   }
   .govuk-caption-m2 {
   font-size: 1.1875rem;
   line-height: 1.3157894737;
   }
   .govuk-heading-m {
   margin-bottom: 20px;
   font-size: 1.1875rem;
   line-height: 1.25;
   }
   .govuk-heading-m2 {
   margin-bottom: 20px;
   font-size: 1.1875rem;
   line-height: 1.3157894737;
   }
   .govuk-caption-l {
   margin-bottom: 0;
   font-size: 1.5rem;
   line-height: 1.25;
   }
   .govuk-heading-l {
   margin-bottom: 30px;
   font-size: 1.25rem;
   line-height: 1.1111111111;
   }
   }
   .card {
    margin-bottom: 1rem; /* Adds space between rows of cards */
}

.card-header {
    padding-top: 10px;
    padding-bottom: 10px;
}

.card-body {
    padding: 15px;
}
.table {
    table-layout: fixed; /* Ensures columns have a fixed width */
    width: 100%; /* Ensure the table takes full width */
}
.table td, .table th {
    overflow: hidden; /* Hides overflowed text */
    text-overflow: ellipsis; /* Adds ellipsis (...) for overflowed text */
    word-wrap: break-word; /* Allows long words to break and wrap to the next line */
    white-space: normal; /* Ensures text wraps within the cell */
}
.table td {
    max-width: 200px; /* Adjust max width as needed for better fitting */
}
</style>
@endpush
@section('page-title')
{{ __('Audit Driver Detail') }}
@endsection
@push('script-page')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const validUntilInput = document.getElementById('valid_until');
        const checkIntervalInput = document.getElementById('check_interval');

        function updateCheckInterval() {
            const validUntilDate = new Date(validUntilInput.value);
            if (!isNaN(validUntilDate.getTime())) {
                validUntilDate.setMonth(validUntilDate.getMonth() + 3);
                checkIntervalInput.value = validUntilDate.toISOString().split('T')[0];
            }
        }

        validUntilInput.addEventListener('change', updateCheckInterval);
        updateCheckInterval(); // Initialize on page load
    });

    function previewImage(event, previewId) {
        var reader = new FileReader();
        reader.onload = function(){
            var output = document.getElementById(previewId);
            output.src = reader.result;
            output.style.display = 'block';
        };
        reader.readAsDataURL(event.target.files[0]);
    }

    function previewMultipleImages(event) {
        var previewContainer = document.getElementById('additional_cards_preview');
        previewContainer.innerHTML = ''; // Clear any existing previews

        var files = event.target.files;
        for (var i = 0; i < files.length; i++) {
            var reader = new FileReader();
            reader.onload = function(e) {
                var img = document.createElement('img');
                img.src = e.target.result;
                img.style.maxWidth = '100px';
                img.style.height = 'auto';
                img.classList.add('img-fluid', 'mb-2');

                var col = document.createElement('div');
                col.classList.add('col-md-6', 'mb-3');

                var card = document.createElement('div');

                var cardBody = document.createElement('div');

                var actionBtn = document.createElement('div');

                var downloadLink = document.createElement('a');


                cardBody.appendChild(img);
                cardBody.appendChild(actionBtn);
                card.appendChild(cardBody);
                col.appendChild(card);
                previewContainer.appendChild(col);
            };
            reader.readAsDataURL(files[i]);
        }
    }
</script>

<script src="{{asset('css/summernote/summernote-bs4.js')}}"></script>
<script src="{{asset('assets/js/plugins/dropzone-amd-module.min.js')}}"></script>
{{--  <script>
   Dropzone.autoDiscover = true;
   myDropzone = new Dropzone("#dropzonewidget", {
       maxFiles: 20,
       parallelUploads: 1,

       url: "{{route('contract.file.upload',[$contract->id])}}",
       success: function (file, response) {
           // location.reload()

           if (response.is_success) {
               if(response.status==1){
                   show_toastr('success', response.success_msg, 'success');
               }else{
                   show_toastr('{{__("success")}}', 'Attachment Create Successfully!', 'success');
                   dropzoneBtn(file, response);
               }

           } else {

               myDropzone.removeFile(file);
               show_toastr('{{__("Error")}}', 'The attachment must be same as stoarge setting', 'Error');
           }
       },
       error: function (file, response) {
           myDropzone.removeFile(file);
           if (response.error) {
               show_toastr('{{__("Error")}}', 'The attachment must be same as stoarge setting', 'error');
           } else {
               show_toastr('{{__("Error")}}', 'The attachment must be same as stoarge setting', 'error');
           }
       }
   });
   myDropzone.on("sending", function (file, xhr, formData) {
       formData.append("_token", $('meta[name="csrf-token"]').attr('content'));
       formData.append("contract_id", {{$contract->id}});
   });

   function dropzoneBtn(file, response) {
       var download = document.createElement('a');
       download.setAttribute('href', response.download);
       download.setAttribute('class', "action-btn btn-primary mx-1 mt-1 btn btn-sm d-inline-flex align-items-center");
       download.setAttribute('data-toggle', "tooltip");
       download.setAttribute('data-original-title', "{{__('Download')}}");
       download.innerHTML = "<i class='fas fa-download'></i>";

       var del = document.createElement('a');
       del.setAttribute('href', response.delete);
       del.setAttribute('class', "action-btn btn-danger mx-1 mt-1 btn btn-sm d-inline-flex align-items-center");
       del.setAttribute('data-toggle', "tooltip");
       del.setAttribute('data-original-title', "{{__('Delete')}}");
       del.innerHTML = "<i class='ti ti-trash'></i>";

       del.addEventListener("click", function (e) {
           e.preventDefault();
           e.stopPropagation();
           if (confirm("Are you sure ?")) {
               var btn = $(this);
               $.ajax({
                   url: btn.attr('href'),
                   data: {_token: $('meta[name="csrf-token"]').attr('content')},
                   type: 'DELETE',
                   success: function (response) {
                       location.reload();
                       if (response.is_success) {
                           btn.closest('.dz-image-preview').remove();
                       } else {
                           show_toastr('{{__("Error")}}', response.error, 'error');
                       }
                   },
                   error: function (response) {
                       response = response.responseJSON;
                       if (response.is_success) {
                           show_toastr('{{__("Error")}}', response.error, 'error');
                       } else {
                           show_toastr('{{__("Error")}}', response.error, 'error');
                       }
                   }
               })
           }
       });

       var html = document.createElement('div');
       html.setAttribute('class', "text-center mt-10");
       file.previewTemplate.appendChild(html);
   }
   $(document).on('click', '#comment_submit', function (e) {
       var curr = $(this);

       var comment = $.trim($("#form-comment textarea[name='comment']").val());
       if (comment != '') {
           $.ajax({
               url: $("#form-comment").data('action'),
               data: {comment: comment, "_token": "{{ csrf_token() }}"},
               type: 'POST',
               success: function (data) {
                   show_toastr('{{__("success")}}', 'Comment Create Successfully!', 'success');
                   setTimeout(function () {
                       location.reload();
                   }, 500)
                   data = JSON.parse(data);
                   console.log(data);
                   var html = "<div class='list-group-item px-0'>" +
                       "                    <div class='row align-items-center'>" +
                       "                        <div class='col-auto'>" +
                       "                            <a href='#' class='avatar avatar-sm rounded-circle ms-2'>" +
                       "                                <img src="+data.default_img+" alt='' class='avatar-sm rounded-circle'>" +
                       "                            </a>" +
                       "                        </div>" +
                       "                        <div class='col ml-n2'>" +
                       "                            <p class='d-block h6 text-sm font-weight-light mb-0 text-break'>" + data.comment + "</p>" +
                       "                            <small class='d-block'>"+data.current_time+"</small>" +
                       "                        </div>" +
                       "                        <div class='action-btn bg-danger me-4'><div class='col-auto'><a href='#' class='mx-3 btn btn-sm  align-items-center delete-comment' data-url='" + data.deleteUrl + "'><i class='ti ti-trash text-white'></i></a></div></div>" +
                       "                    </div>" +
                       "                </div>";

                   $("#comments").prepend(html);
                   $("#form-comment textarea[name='comment']").val('');
                   load_task(curr.closest('.task-id').attr('id'));
                   show_toastr('{{__('success')}}', '{{ __("Comment Added Successfully!")}}');
               },
               error: function (data) {
                   show_toastr('error', '{{ __("Some Thing Is Wrong!")}}');
               }
           });
       } else {
           show_toastr('error', '{{ __("Please write comment!")}}');
       }
   });

   $(document).on("click", ".delete-comment", function () {
       var btn = $(this);

       $.ajax({
           url: $(this).attr('data-url'),
           type: 'DELETE',
           dataType: 'JSON',
           data: {"_token": "{{ csrf_token() }}"},
           success: function (data) {
               load_task(btn.closest('.task-id').attr('id'));
               show_toastr('{{__('success')}}', '{{ __("Comment Deleted Successfully!")}}');
               btn.closest('.list-group-item').remove();
           },
           error: function (data) {
               data = data.responseJSON;
               if (data.message) {
                   show_toastr('error', data.message);
               } else {
                   show_toastr('error', '{{ __("Some Thing Is Wrong!")}}');
               }
           }
       });
   });


</script>  --}}
@endpush
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
<li class="breadcrumb-item"><a href="{{ url()->previous() }}">{{ __('Driver') }}</a></li>
<li class="breadcrumb-item"><a href="">{{ !empty($driver->types) ? ucwords(strtolower($driver->types->name)) : ' '}}</a></li>
@endsection
@section('action-btn')
<div class="float-end d-flex align-items-center">
    @php
    // Encode the driver ID before using it in the URL
    $encodedId = base64_encode($driver->id);
@endphp
 <a href="{{ route('driver.history.pdf', ['slug' => $encodedId]) }}" class="btn btn-sm btn-primary btn-icon" title="{{__('Download')}}" target="_blank">
    <i class="ti ti-download"></i> Download Driver Information
</a>



   @php
   $status = App\Models\Contract::status();
   @endphp
   @php
   $status = App\Models\Contract::status();
   @endphp
   @php
   use Carbon\Carbon;
   @endphp

</div>
@endsection
@section('content')


<div class="row">
    <div class="col-xl-9" style="width: 100%">
       <div id="useradd-1">
          <div class="row">
             <div class="col-xxl-5" style="width: 100%;">
                <div class="card report_card total_amount_card">
                   <div class="card-body pt-0" style="margin-bottom: -30px; margin-top: -10px;">
                      <address class="mb-0 text-sm">
                         <dl class="row mt-4 align-items-center">
                            <h3>{{ $driver->name }}</h3>
                            <div class="col-sm-6" style="font-size: 0.85rem !important; margin-top: -3%;">

                                <div class="row">
                                  <div class="col-sm-6">
                                  <dt class="h6 text-sm">
                                        <span style="font-weight: bold; font-size: 0.85rem !important;">{{ __('Company Name') }}:</span>
                                        <!--<span style="font-weight: bold; font-size: 0.85rem !important;">{{ strtoupper(__('Company Name')) }}:</span>-->
                                        <span class="text-sm">{{ !empty($driver->types) ? strtoupper($driver->types->name) : ' '}}</span>
                                     </dt>
                                  </div>

                               </div>

                               <div class="row">
                                  <div class="col-sm-6">
                                     <dt class="h6 text-sm">
                                        <span style="font-weight: bold; font-size: 0.85rem !important;">{{ __('Driver Licence No') }}:</span>
                                        <span class="text-sm">{{ $driver->driver_licence_no }}</span>
                                     </dt>
                                  </div>
                                  <div class="col-sm-6">
                                     <dt class="h6 text-sm">
                                        <span style="font-weight: bold; font-size: 0.85rem !important;">{{ __('Issue Number') }}:</span>
                                        <span class="text-sm">{{ $driver->token_issue_number }}</span>
                                     </dt>
                                  </div>
                               </div>
                               <div class="row">
                                  <div class="col-sm-6">
                                     <dt class="h6 text-sm">
                                        <span style="font-weight: bold; font-size: 0.85rem !important;">{{ __('Licence Valid From') }}:</span>
                                        <span class="text-sm">{{ $driver->token_valid_from_date }}</span>
                                     </dt>
                                  </div>
                                  <div class="col-sm-6">
                                     <dt class="h6 text-sm">
                                        <span style="font-weight: bold; font-size: 0.85rem !important;">{{ __('Licence Valid To') }}:</span>
                                        <span class="text-sm">{{ $driver->driver_licence_expiry }}</span>
                                     </dt>
                                  </div>
                               </div>
                               {{--  <div class="row">
                                  <div class="col-sm-6">
                                     <dt class="h6 text-sm">
                                        <span style="font-weight: bold; font-size: 0.85rem !important;">{{ __('Driver Name') }}:</span>
                                        <span class="text-sm">{{ $driver->name }}</span>
                                     </dt>
                                  </div>
                               </div>  --}}
                               <div class="row">
                                  <div class="col-sm-6">
                                     <dt class="h6 text-sm">
                                        <span style="font-weight: bold; font-size: 0.85rem !important;">{{ __('Gender') }}:</span>
                                        <span class="text-sm uppercase">{{ $driver->gender }}</span>
                                     </dt>
                                  </div>
                                  <div class="col-sm-6">
                                     <dt class="h6 text-sm">
                                        <span style="font-weight: bold; font-size: 0.85rem !important;">{{ __('Date Of Birth') }}:</span>
                                        <span class="text-sm">{{ $driver->driver_dob }}</span>
                                     </dt>
                                  </div>
                               </div>
                               <div class="row">
                                  <div class="col-sm-6">
                                     <dt class="h6 text-sm">
                                        <span style="font-weight: bold; font-size: 0.85rem !important;">{{ __('Address') }}:</span>
                                        <span class="text-sm">{{ $driver->driver_address }}, {{ $driver->post_code }} </span>
                                     </dt>
                                  </div>
                               </div>
                            </div>
                            <!-- End of New Box -->

<div class="col-sm-6">
    <!-- Existing Boxes -->
    <!--margin-left: 30%;-->
     <p class="text-sm" style="font-size: 12px; margin-top:-30px; margin-bottom:20px; margin-left:47%;">
                                         {{ __('Summary generated :') }} {{ $driver->latest_lc_check }}
                                    </p>
    <div class="col-xl-13">
        <div class="row">
            <div class="col-lg-4 col-6" style="margin-left: 30%;">
                <div class="card">
                    <div class="card-body" style="min-height: 205px; background-color: #229183; border-radius: 10px; color: white; ">
                        <cemter><h5 class="mb-3 mt-4 text-center" style="color: white">{{ __('Licence Status') }}</h5>
                        <div style="border-top: 2px  margin: 10px 0;"></div>
                        <div class="text-center">
                            <div style="font-size: 1.5rem; font-weight: bold;">{{ $driver->licence_type }}</div>
                            <div style="border-bottom: 1px ; margin: 10px 0;"></div>
                            <div style="font-size: 1.5rem; font-weight: bold;">{{ $driver->driver_licence_status }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">

    <div class="card">
        <div class="card-body" style="min-height: 205px;  width:125%;
            @if($firstPenaltyPoints >= 0 && $firstPenaltyPoints <= 5)
                background-color: #28a745; /* Green */
            @elseif($firstPenaltyPoints >= 6 && $firstPenaltyPoints <= 11)
                background-color: #fd7e14; /* Orange */
            @else
                background-color: #dc3545; /* Red */
            @endif
            border-radius: 10px; color: white;">
            <h5 class="mb-3 mt-4 text-center" style="color: white">{{ __(' Endorsements') }}</h5>
            <div style="border-top: 2px  ; margin: 10px 0;"></div>
            <div class="d-flex justify-content-between" style="margin-top:10%;">
                <!-- Left side: Penalty Points -->
                <div class="text-center" style="flex: 1;">
                    <div style="font-size: 1.5rem; font-weight: bold;">{{ $firstPenaltyPoints }}</div>
                    <span>Points</span>
                </div>
                <!-- Right side: Unique Offence Codes -->
                <div class="text-center" style="flex: 1;">
                    <div style="font-size: 1.5rem; font-weight: bold;">{{ $uniqueOffenceCodeCount }}</div>
                    <span>Offences</span>
                </div>
            </div>
        </div>
    </div>
</div>

        </div>
    </div>
</div>



                            <!-- End of Existing Boxes -->
                         </dl>
                      </address>
                   </div>
                </div>
             </div>

<div class="col-xxl-5" style="width: 100%;">
                <div class="card report_card total_amount_card">
                    <div class="card-body pt-0" style="margin-top: 13px;">
                        <h3>{{ __('Offences') }}</h3>
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>{{ __('Offence Code') }}</th>
                                    <th>{{ __('Penalty Points') }}</th>
                                    <th>{{ __('Offence Legal Literal') }}</th>
                                    <th>{{ __('Offence Date') }}</th>
                                    <th>{{ __('Conviction Date') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Check if endorsements is an array and has items -->
                                @if(is_array($endorsements) && count($endorsements) > 0)
                                    @foreach($endorsements as $item)
                                        <tr>
                                            <td>{{ $item['offenceCode'] ?? ' ' }}</td>
                                            <td>{{ $item['penaltyPoints'] ?? ' ' }}</td>
                                            <td>{{ $item['offenceLegalLiteral'] ?? ' ' }}</td>
                                            <td>{{ isset($item['offenceDate']) ? \Carbon\Carbon::parse($item['offenceDate'])->format('d/m/Y') : ' ' }}</td>
<td>{{ isset($item['convictionDate']) ? \Carbon\Carbon::parse($item['convictionDate'])->format('d/m/Y') : ' ' }}</td>


                                        </tr>
                                    @endforeach
                                @else
                                    <!-- Display a message if no endorsements are available -->
                                    <tr>
                                        <td colspan="5" class="text-center">No endorsements available</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-xxl-5" style="width: 100%;">
                <div class="card report_card total_amount_card">
                    <div class="card-body pt-0" style="margin-top: 31px;">
                                                <!--<h3 style="text-align: left;">{{ __('National Driver Qualification Card') }}</h3>-->
                        <address class="mb-0 text-sm">
                            <div class="d-flex justify-content-between">

                                <div class="card flex-fill me-2">
                                    <div class="card-header" style="padding-top: 10px; padding-bottom: 10px;">
                                        <h5 class="mb-0" style="text-align: center;">{{ __('Driver Tachograph') }}</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                             <div class="d-flex flex-column w-50">

                                                <span><strong>{{ __('Tacho Card Number') }}</strong></span><br>
                                                <span>{{ $driver->tacho_card_no }}</span>
                                            </div>
                                            <!--<div class="border-start border-2 border-dark mx-3"></div>-->
                                            <div class="border-start border-2 border-gray mx-3"></div>

                                            <div class="d-flex flex-column w-50">

                                                <span><strong>{{ __('Valid From') }}</strong></span><br>
                                                <span>{{ $driver->tacho_card_valid_from }}</span>
                                            </div>
                                            <!--<div class="border-start border-2 border-dark mx-3"></div>-->
                                            <div class="border-start border-2 border-gray mx-3"></div>
                                            <div class="d-flex flex-column w-50">
                                                <span><strong>{{ __('Valid Until') }}</strong></span><br>
                                                <span>{{ $driver->tacho_card_valid_to }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Driver Qualification Card Section -->
                                <div class="card flex-fill ms-2">
                                    <div class="card-header" style="padding-top: 10px; padding-bottom: 10px;">
                                        <h5 class="mb-0" style="text-align: center;">{{ __('Driver Qualification Card (CPC)') }}</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <div class="d-flex flex-column w-50">
                                                <span><strong>{{ __('Type') }}</strong></span><br>
                                                <span>International</span>
                                            </div>
                                             <div class="border-start border-2 border-gray mx-3"></div>
                                            <div class="d-flex flex-column w-50">
                                                <span><strong>{{ __('Valid From') }}</strong></span><br>
                                                <span>{{ $driver->dqc_issue_date }}</span>
                                            </div>
                                            <!--<div class="border-start border-2 border-dark mx-3"></div>-->
                                            <div class="border-start border-2 border-gray mx-3"></div>
                                            <div class="d-flex flex-column w-50">
                                                <span><strong>{{ __('Valid Until') }}</strong></span><br>
                                                <span>{{ $driver->cpc_validto }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Additional Content -->

                            {{--  <div class="mt-4">
                                <div class="d-flex justify-content-between">
                                    <div class="d-flex flex-column w-50">
                                        <span><strong style="font-size: 15px;">{{ __('Driver Consent Valid Until') }} : {{ $driver->content_valid_until ?? '___________'  }}</strong></span><br>
                                    </div>
                                    <div class="d-flex flex-column w-50">
                                        <span><strong style="font-size: 15px;">{{ __('Current Licence Check Interval') }} : {{ $driver->current_licence_check_interval ?? '___________' }}</strong></span><br>
                                        <span></span>
                                    </div>
                                </div>
                            </div>  --}}

                        </address>
                    </div>
                </div>
            </div>

             <div class="col-xxl-5" style="width: 100%;">
                <div class="card report_card total_amount_card">
                    <div class="card-body pt-0" style="margin-top: 13px;">
                        <h3>{{ __('Vehicle You Can Drive') }}</h3>
                        <table class="table table-bordered" style="font-size: 0.85rem; margin-top: 1%;">
                            <thead>
                                <tr>
                                    <th>{{ __('Category') }}</th>
                                    <th>{{ __('Start Date') }}</th>
                                    <th>{{ __('Until Date') }}</th>
                                    <th>{{ __('Category Type') }}</th>
                                    <th>{{ __('Restrictions Code') }}</th>

                                </tr>
                            </thead>
                           <tbody>
                                @foreach($driver->entitlements  as $entitlements)
                                <tr>
                                    <td>
                                         @if($entitlements->category_code == 'AM')
                                        {{ $entitlements->category_code }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="{{ asset('storage/category_icons/AM.png') }}" alt="">
                                        @elseif($entitlements->category_code == 'A')
                                        {{ $entitlements->category_code }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="{{ asset('storage/category_icons/A.png') }}" alt="">
                                        @elseif($entitlements->category_code == 'B1')
                                        {{ $entitlements->category_code }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="{{ asset('storage/category_icons/B1.png') }}" alt="">
                                        @elseif($entitlements->category_code == 'B')
                                        {{ $entitlements->category_code }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="{{ asset('storage/category_icons/B.png') }}" alt="">
                                        @elseif($entitlements->category_code == 'BE')
                                        {{ $entitlements->category_code }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <img src="{{ asset('storage/category_icons/BE.png') }}" alt="">&nbsp;&nbsp;<img src="{{ asset('storage/category_icons/B.png') }}" alt="">
                                        @elseif($entitlements->category_code == 'F')
                                        {{ $entitlements->category_code }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="{{ asset('storage/category_icons/F.png') }}" alt="">
                                        @elseif($entitlements->category_code == 'C')
                                        {{ $entitlements->category_code }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="{{ asset('storage/category_icons/C.png') }}" alt="">
                                        @elseif($entitlements->category_code == 'C1')
                                        {{ $entitlements->category_code }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="{{ asset('storage/category_icons/C1.png') }}" alt="">
                                        @elseif($entitlements->category_code == 'C1E')
                                        {{ $entitlements->category_code }}  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="{{ asset('storage/category_icons/C1E.png') }}" alt="">&nbsp;&nbsp;<img src="{{ asset('storage/category_icons/C1.png') }}" alt="">
                                        @elseif($entitlements->category_code == 'CE')
                                        {{ $entitlements->category_code }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="{{ asset('storage/category_icons/CE.png') }}" alt="" style="width:28%">
                                        @elseif($entitlements->category_code == 'D')
                                        {{ $entitlements->category_code }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="{{ asset('storage/category_icons/D.png') }}" alt="">
                                        @elseif($entitlements->category_code == 'D1')
                                        {{ $entitlements->category_code }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="{{ asset('storage/category_icons/D1.png') }}" alt="">
                                        @else
                                        {{ $entitlements->category_code ?? 'NULL' }}
                                        @endif
                                    </td>
                                    <td>{{ $entitlements->from_date ?? 'NULL' }}</td>
                                    <td>{{ $entitlements->expiry_date ?? 'NULL' }}</td>
                                    <td>{{ $entitlements->category_type ?? 'NULL' }}</td>
                                    <td>
                                @if($entitlements->restrictions)
                                    @php
                                        $restrictions = json_decode($entitlements->restrictions, true);
                                    @endphp
                                    @foreach($restrictions as $restriction)
                                        {{ $restriction['restrictionCode'] }}
                                        @if (!$loop->last), @endif
                                    @endforeach
                                @else
                                    NULL
                                @endif
                            </td>
                                </tr>
                                @endforeach

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>



            <div class="col-xxl-5" style="width: 100%;">
                <div class="card report_card total_amount_card">
                    <div class="card-body pt-0" style="margin-top: 2%;">
                        <address class="mb-0 text-sm">
                            <h3>{{ __('Entitlements') }}</h3>
                            <br>
                            <address class="mb-0 text-sm">
                                <div class="card flex-fill">
                                    <div class="card-body">
                                        <!-- Table for Entitlements -->
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th style="width: 14%;">{{ __('Category Code') }}</th>
                                                    <th>{{ __('Legal Literal') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($driver->entitlements as $entitlement)
                                                <tr>
                                                    <td>{{ $entitlement->category_code }}</td>
                                                    <td>{{ $entitlement->category_legal_literal }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>


                                        </table>
                                    </div>
                                </div>
                            </address>
                        </address>
                    </div>
                </div>
            </div>



<div class="col-xxl-5" style="width: 100%;">
    <div class="card report_card total_amount_card">
        <div class="card-body pt-0" style="margin-top: 13px;">
            <h3>Disclaimer</h3>
            <table class="table table-bordered table-sm">

                <tbody>
                    <div >
                        <p>This disclaimer clarifies that PTC (Paramount Transport Consultants Ltd) is not accountable for the accuracy of the provided data since
                            it originates from the DVLA (Driver and Vehicle Licensing Agency). By including this statement, PTC aims to inform users that any
                            discrepancies or errors in the data are beyond their control and responsibility. If users encounter any issues or inaccuracies within the data,
                            they are encouraged to reach out to PTC's technical team for assistance. The contact information for the technical team is provided,
                            specifically an email address <span style="color: blue">(it@ptctransport.co.uk)</span>, to ensure users have a direct line of communication to report problems or seek further information. This approach helps manage user expectations and directs them to the appropriate support channel for resolution, maintaining transparency and accountability in data handling.
                        </p>
                    </div>
                </tbody>

            </table>
        </div>
    </div>
</div>


       </div>
    </div>
 </div>

@endsection
