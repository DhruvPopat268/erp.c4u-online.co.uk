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
</style>
@endpush
@section('page-title')
{{ __('Vehicle Detail') }}
@endsection
@push('script-page')
<script>
   $(document).on("click", ".status", function() {
       var status = $(this).attr('data-id');
       var url = $(this).attr('data-url');
       $.ajax({
           url: url,
           type: 'POST',
           data: {

               "status": status ,
               "_token": "{{ csrf_token() }}",
           },
           success: function(data) {
               show_toastr('{{__("success")}}', 'Status Update Successfully!', 'success');
               location.reload();
           }

       });
   });
</script>
<script src="{{asset('css/summernote/summernote-bs4.js')}}"></script>
<script src="{{asset('assets/js/plugins/dropzone-amd-module.min.js')}}"></script>
<script>
   @can('manage contract')
   $('.summernote-simple').on('summernote.blur', function () {

       $.ajax({
           url: "{{route('contract.contract_description.store',$contract->id)}}",
           data: {_token: $('meta[name="csrf-token"]').attr('content'), contract_description: $(this).val()},
           type: 'POST',
           success: function (response) {
               console.log(response)
               if (response.is_success) {
                   show_toastr('success', response.success,'success');
               } else {
                   show_toastr('error', response.error, 'error');
               }
           },
           error: function (response) {

               response = response.responseJSON;
               if (response.is_success) {
                   show_toastr('error', response.error, 'error');
               } else {
                   show_toastr('error', response.error, 'error');
               }
           }
       })
   });
   @else
   // $('.summernote-simple').summernote('disable');
   @endcan
</script>
<script>
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


</script>
<script>
   var scrollSpy = new bootstrap.ScrollSpy(document.body, {
       target: '#useradd-sidenav',
       offset: 300,
   })
   $(".list-group-item").click(function(){
       $('.list-group-item').filter(function(){
           return this.href == id;
       }).parent().removeClass('text-primary');
   });
</script>
@endpush
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
<li class="breadcrumb-item"><a href="{{ url()->previous() }}">{{ __('Vehicle') }}</a></li>
<li class="breadcrumb-item"><a href="{{ url()->previous() }}">{{ !empty($contract->types) ? ucwords(strtoupper($contract->types->name)) : '' }}</a></li>
<li class="breadcrumb-item active" aria-current="page">{{$vehicle->registrations ?? null }}</li>
@endsection
@section('action-btn')
<div class="float-end d-flex align-items-center">
   <a href="{{route('contract.download.pdf',\Crypt::encrypt($contract->id))}}" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="tooltip" data-bs-placement="top" title="{{__('Download')}}" target="_blanks">
   <i class="ti ti-download"></i>
   </a>
   <a href="{{ route('get.contract',$contract->id) }}"  target="_blank" class="btn btn-sm btn-primary btn-icon m-1" >
   <i class="ti ti-eye text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('PreView') }}"> </i>
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
   @if(\Auth::user()->type == 'client' )
   <ul class="list-unstyled m-0 ">
      <li class="dropdown dash-h-item status-drp">
         <a class="dash-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#"
            role="button" aria-haspopup="false" aria-expanded="false">
         <span class="drp-text hide-mob text-primary">{{ ucfirst($contract->status) }}
         <i class="ti ti-chevron-down drp-arrow nocolor hide-mob"></i>
         </span>
         </a>
         <div class="dropdown-menu dash-h-dropdown">
            @foreach ($status as $k => $status)
            <a class="dropdown-item status" data-id="{{ $k }}" data-url="{{ route('contract.status', $contract->id) }}" href="#">{{ ucfirst($status) }}
            </a>
            @endforeach
         </div>
      </li>
   </ul>
   @endif
</div>
@endsection
@section('content')
<div class="row">
   <div class="col-xl-2">
      <div class="card sticky-top" style="top:30px">
         <div class="list-group list-group-flush" id="useradd-sidenav">
            <a href="#useradd-1" class="list-group-item list-group-item-action border-0">
               {{ __('General') }}
               <div class="float-end"><i class="ti ti-chevron-right"></i></div>
            </a>
            <a href="#useradd-2" class="list-group-item list-group-item-action border-0">
               {{ __('Attachment') }}
               <div class="float-end"><i class="ti ti-chevron-right"></i></div>
            </a>
         </div>
      </div>
   </div>
   <div class="col-xl-10">
      <div id="useradd-1">
         <div class="row">
            <div class="col-xl-12">
    <!-- First row with 4 boxes -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="card">
                <div class="card-body" style="min-height: 180px;">
                    <div class="theme-avtar bg-primary">
                        <i class="far fa-file-alt"></i>
                    </div>
                    <h6 class="mb-3 mt-4">
                        <a href="#useradd-2" class="mb-3 mt-4" style="color: black;">
                            {{ __('Attachment') }}
                        </a>
                    </h6>
                    <h3 class="mb-0">{{ count($contract->files) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="card">
                <div class="card-body" style="min-height: 180px;">
                    <div class="theme-avtar bg-primary">
                        <i class="far fa-calendar"></i>
                    </div>
                    <h6 class="mb-3 mt-4">{{ __('PMI Due') }}</h6>
                    @if (!empty($contract->PMI_due))
                        <h3 class="mb-0">{{ \Carbon\Carbon::parse($contract->PMI_due)->format('d/m/Y') }}</h3>
                    @else
                        <h3 class="mb-0">-</h3>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="card">
                <div class="card-body" style="min-height: 180px;">
                    <div class="theme-avtar bg-primary">
                        <i class="far fa-calendar"></i>
                    </div>
                    <h6 class="mb-3 mt-4">{{ __('Brake Test Due') }}</h6>
                    @if (!empty($contract->brake_test_due) && $contract->brake_test_due !== '-')
                        <h3 class="mb-0">{{ \Carbon\Carbon::parse($contract->brake_test_due)->format('d/m/Y') }}</h3>
                    @else
                        <h3 class="mb-0">-</h3>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="card">
                <div class="card-body" style="min-height: 180px;">
                    <div class="theme-avtar bg-primary">
                        <i class="far fa-calendar"></i>
                    </div>
                    <h6 class="mb-3 mt-4">{{ __('Insurance') }}</h6>
                    @if (!empty($contract->insurance))
                        <h3 class="mb-0">{{ \Carbon\Carbon::parse($contract->insurance)->format('d/m/Y') }}</h3>
                    @else
                        <h3 class="mb-0">-</h3>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Second row with 3 boxes -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="card">
                <div class="card-body" style="min-height: 180px;">
                    <div class="theme-avtar bg-primary">
                        <i class="far fa-calendar"></i>
                    </div>
                    <h6 class="mb-3 mt-4">{{ __('Tacho Calibration') }}</h6>
                    @if (!empty($contract->tacho_calibration) && $contract->tacho_calibration !== '-')
                        <h3 class="mb-0">{{ \Carbon\Carbon::parse($contract->tacho_calibration)->format('d/m/Y') }}</h3>
                    @else
                        <h3 class="mb-0">-</h3>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="card">
                <div class="card-body" style="min-height: 180px;">
                    <div class="theme-avtar bg-primary">
                        <i class="far fa-calendar"></i>
                    </div>
                    <h6 class="mb-3 mt-4">{{ __('DVS/PSS Permit Expiry') }}</h6>
                    @if (!empty($contract->dvs_pss_permit_expiry) && $contract->dvs_pss_permit_expiry !== '-')
                        <h3 class="mb-0">{{ \Carbon\Carbon::parse($contract->dvs_pss_permit_expiry)->format('d/m/Y') }}</h3>
                    @else
                        <h3 class="mb-0">-</h3>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="card">
                <div class="card-body" style="min-height: 180px;">
                    <div class="theme-avtar bg-primary">
                        <i class="far fa-calendar"></i>
                    </div>
                    <h6 class="mb-3 mt-4">{{ __('Date Of Inspection') }}</h6>
                    @if (!empty($contract->date_of_inspection) && $contract->date_of_inspection !== '-')
                        <h3 class="mb-0">{{ \Carbon\Carbon::parse($contract->date_of_inspection)->format('d/m/Y') }}</h3>
                    @else
                        <h3 class="mb-0">-</h3>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="card">
                <div class="card-body" style="min-height: 180px;">
                    <div class="theme-avtar bg-primary">
                        <i class="far fa-calendar"></i>
                    </div>
                    <h6 class="mb-3 mt-4">{{ __('Odometer Reading') }}</h6>
                    @if (!empty($contract->odometer_reading) && $contract->odometer_reading !== '-')
                        <h3 class="mb-0">{{ $contract->odometer_reading ? Carbon::parse($contract->odometer_reading)->format('d/m/Y') : 'N/A' }}</h3>
                    @else
                        <h3 class="mb-0">-</h3>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>



         <div class="col-xxl-5" style="width: 100%;">
            <div class="card report_card total_amount_card">
               <div class="card-body pt-0" style="margin-bottom: -30px; margin-top: 10px;">
                  <h5>{{ __('Vehicle Annual Test') }}</h5>
                  <div style="margin-top:3%">
                     <div class="dvsa-vrm govuk-!-margin-bottom-1" data-test-id="vehicle-registration" style="font: 24px UK-VRM, Verdana, sans-serif;">
                        {{$vehicle->registrations ?? null }}
                     </div>
                     <h1 class="govuk-heading-xl" data-test-id="vehicle-make-model" style="font-size:2rem;">{{$vehicle->make ?? null}} {{$vehicle->model ?? null}}</h1>
                     <div class="govuk-grid-column-one-third">
                        <span class="govuk-caption-m">Colour</span>
                        <div class="govuk-heading-m" data-test-id="vehicle-colour" style="font-size:1.1875rem;">{{$vehicle->primary_colour ?? null }}</div>
                     </div>
                     <div class="govuk-grid-column-one-third">
                        <span class="govuk-caption-m">Fuel type</span>
                        <div class="govuk-heading-m" data-test-id="vehicle-fuel-type" style="font-size:1.1875rem;">{{$vehicle->fuel_type ?? null }}</div>
                     </div>
                     <div class="govuk-grid-column-one-third">
                        <span class="govuk-caption-m">Date registered</span>
<div class="govuk-heading-m" data-test-id="vehicle-date-registered" style="font-size:1.1875rem;">
    @if($vehicle && $vehicle->registration_date)
        {{ Carbon::parse($vehicle->registration_date)->format('d/m/Y') }}
    @else
        {{ 'N/A' }}
    @endif
</div>
                     </div>
                     <div>
                        <span class="govuk-caption-l" data-test-id="mot-expiry-text">Annual test valid until</span>
                        <div class="govuk-heading-l" data-test-id="mot-due-date" style="font-size:1.2575rem;">
                           @if($vehicle && $vehicle->annualTests->count())
    {{ \Carbon\Carbon::parse($vehicle->annualTests->reverse()->first()->expiry_date)->format('d/m/Y') }}
        @else
            {{ 'N/A' }}
        @endif
                        </div>
                     </div>
                 @foreach($annualTests->reverse() as $test)
                     <div class="govuk-grid-column-one-third">
                        <span class="govuk-caption-m">Date tested</span>
                        {{--  <div class="govuk-heading-m" data-test-id="vehicle-colour" style="font-size: 1.1875rem;">{{ \Carbon\Carbon::createFromFormat('Y-m-d', $test->completed_date)->format('d/m/Y') ?? null }}</div>  --}}
                        <div class="govuk-heading-m" data-test-id="vehicle-colour" style="font-size: 1.1875rem;">
                              {{ \Carbon\Carbon::parse($test->completed_date)->format('d/m/Y') }}
                              </div>

                        <div id="pass-fail" style="font-size: 2rem; margin-top: -20px; color: {{ $test->test_result === 'PASSED' ? '#00703c' : '#ff0000' }}">
                           {{ $test->test_result ?? null }}
                        </div>
                     </div>
                     <!-- Start of second column -->
                     <div class="govuk-grid-column-one-third">
                        <span class="govuk-caption-m">Mileage</span>
                        <div class="govuk-heading-m" data-test-id="vehicle-fuel-type" style="font-size: 1.1875rem;">{{ $test->odometer_value ?? null }} {{ $test->odometer_unit ?? null }}</div>
                     </div>
                     <div class="govuk-grid-column-one-third">
                        <span class="govuk-caption-m">MOT test number</span>
                        <div class="govuk-heading-m" data-test-id="vehicle-date-registered" style="font-size: 1.1875rem;">{{ $test->mot_test_number ?? null }}</div>
                     </div>
                     <div class="govuk-grid-column-one-third">
                        <span class="govuk-caption-m">Test location</span>
                        <div class="govuk-heading-m" data-test-id="vehicle-date-registered" style="font-size: 1.1875rem;">{{ $test->location ?? null }}</div>
                     </div>
                     <div class="govuk-grid-column-one-third">
                        <span class="govuk-caption-m">Expiry date</span>
                        <div class="govuk-heading-m" data-test-id="vehicle-date-registered" style="font-size: 1.1875rem;">
                           @if($test->expiry_date)
                           {{ \Carbon\Carbon::createFromFormat('Y-m-d', $test->expiry_date)->format('d/m/Y') }}
                           @else
                           NULL
                           @endif
                        </div>
                     </div>
                     @php
                     $minorDefects = [];
                     $majorDefects = [];
                     $advisoryDefects = [];
                     $dangerousDefects = [];
                     @endphp
                     @foreach($test->defects as $defect)
                         @switch($defect->type)
                     @case('MINOR')
                                 @php $minorDefects[] = $defect->text; @endphp
                     @break
                     @case('PRS')
                     @case('FAIL')
                             @case('MAJOR')
                                 @php $majorDefects[] = $defect->text; @endphp
                     @break
                     @case('ADVISORY')
                                 @php $advisoryDefects[] = $defect->text; @endphp
                                 @break
                             @case('DANGEROUS')
                                 @php $dangerousDefects[] = $defect->text; @endphp
                     @break
                     @endswitch
                     @endforeach

                     @if(!empty($minorDefects))
                     <div class="govuk-grid-column-one-third2">
                        <span class="govuk-caption-m" style="width: max-content;">Repair as soon as possible (minor defects):</span>
                        <div class="govuk-heading-m" data-test-id="vehicle-date-registered" style="font-size: 1.0875rem; width: 60ch; display:block;">
                           <ul>
                              @foreach($minorDefects as $defect)
                              <li>{{$defect}}</li>
                              @endforeach
                           </ul>
                        </div>
                     </div>
                     @endif
                     @if(!empty($majorDefects))
                     <div class="govuk-grid-column-one-third2">
                        <span class="govuk-caption-m" style="width: max-content;">Repair immediately (major defects):</span>
                        <div class="govuk-heading-m" data-test-id="vehicle-date-registered" style="font-size: 1.0875rem; width: 60ch; display:block;">
                           <ul>
                              @foreach($majorDefects as $defect)
                              <li>{{$defect}}</li>
                              @endforeach
                           </ul>
                        </div>
                     </div>
                     @endif
                     @if(!empty($advisoryDefects))
                     <div class="govuk-grid-column-one-third2">
                        <span class="govuk-caption-m" style="width: max-content;">Monitor and repair if necessary (advisories):</span>
                        <div class="govuk-heading-m" data-test-id="vehicle-date-registered" style="font-size: 1.0875rem; width: 60ch; display:block;">
                           <ul>
                              @foreach($advisoryDefects as $defect)
                              <li>{{$defect}}</li>
                              @endforeach
                           </ul>
                        </div>
                     </div>
                     @endif

                     @if(!empty($dangerousDefects))
                       <div class="govuk-grid-column-one-third2">
                           <span class="govuk-caption-m" style="width: max-content;">Do not drive until repaired (dangerous defects):</span>
                           <div class="govuk-heading-m" data-test-id="vehicle-date-registered" style="font-size: 1.0875rem; width: 60ch; display:block;">
                               <ul>
                                   @foreach($dangerousDefects as $defect)
                                       <li>{{ $defect }}</li>
                                   @endforeach
                               </ul>
                           </div>
                       </div>
                       @endif


                     @endforeach

                  </div>
               </div>
            </div>
         </div>
      </div>
      {{--
      <div class="card">
         <div class="card-header">
            <h5 class="mb-0">{{ __('Contract Description ') }}</h5>
         </div>
         <div class="card-body" >
            <div class="col-md-12">
               <div class="form-group mt-3" >
                  <textarea class="summernote-simple" >{!! $contract->contract_description !!}</textarea>
               </div>
            </div>
         </div>
      </div>
      --}}
   </div>
   <div id="useradd-2">
      <div class="card">
         <div class="card-header">
            <h5 class="mb-0">{{ __('Vehicle Attachments') }}</h5>
         </div>
         <div class="card-body">
            <div class="form-group">
               <div class="col-md-12 dropzone top-5-scroll browse-file" id="dropzonewidget"></div>
            </div>
            <div class="scrollbar-inner">
               <div class="card-wrapper p-3 lead-common-box">
                  @foreach($contract->files as $file)
                  <div class="card mb-3 border shadow-none">
                     <div class="px-3 py-3">
                        <div class="row align-items-center">
                           <div class="col">
                              <h6 class="text-sm mb-0">
                                  <a href="#!">{{ $file->files }}</a>
                              </h6>
                              <p class="card-text small text-muted">
                                 {{--  <img src="{{ asset('storage/image_attechment/' . $file->files) }}" alt="{{ $file->files }} image">  --}}
                                 @if(!empty($file->files) && file_exists(storage_path('image_attechment/' . $file->files)))
                                  {{ number_format(\File::size(storage_path('image_attechment/' . $file->files)) / 1048576, 2) . ' ' . __('MB') }}
                                 @endif
                              </p>
                           </div>

                           <div class="col-auto actions">
                            <div class="action-btn bg-warning ">
                                <a href="{{$attachments . '/' . $file->files }}"
                                   class=" btn btn-sm d-inline-flex align-items-center"
                                   download="" data-bs-toggle="tooltip" title="Download">
                                <span class="text-white"> <i class="ti ti-download"></i></span>
                                </a>
                             </div>
                             <div class="action-btn bg-warning ">
                              <a href="{{ asset('storage/image_attechment/' . $file->files) }}"
                                  class="btn btn-sm d-inline-flex align-items-center mx-2"
                                  target="_blank" data-bs-toggle="tooltip" title="View">
                                  <span class="text-white"><i class="ti ti-eye"></i></span>
                               </a>

                            </div>
                              <div class="action-btn bg-danger">
                                 {!! Form::open(['method' => 'DELETE', 'route' => ['contracts.file.delete', $contract->id, $file->id]]) !!}
                                 <a href="#!" class="mx-3 btn btn-sm  align-items-center bs-pass-para ">
                                 <i class="ti ti-trash text-white" data-bs-toggle="tooltip" data-bs-original-title="{{__('Delete')}}" ></i>
                                 </a>
                                 {!! Form::close() !!}
                              </div>

                           </div>
                        </div>
                     </div>
                  </div>
                  @endforeach
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
</div>
@endsection
