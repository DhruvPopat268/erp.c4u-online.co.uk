@extends('layouts.admin')
@section('page-title')
{{__('Audit History for licence holder' )}} {{ ucwords(strtoupper($driver->name)) }}
@endsection
@push('script-page')
<script>
   function showUpdateModal(url) {
       document.getElementById('updateForm').action = url;
       new bootstrap.Modal(document.getElementById('updateModal')).show();
   }

   function showChangePasswordModal(url) {
       document.getElementById('changePasswordForm').action = url;
       new bootstrap.Modal(document.getElementById('changePasswordModal')).show();
   }


       document.addEventListener('DOMContentLoaded', function () {
           const newPasswordInput = document.getElementById('new_password');
           const confirmPasswordInput = document.getElementById('confirm_password');
           const submitButton = document.getElementById('submitButton');

           function validatePasswords() {
               if (newPasswordInput.value === confirmPasswordInput.value && newPasswordInput.value !== '') {
                   submitButton.disabled = false;
               } else {
                   submitButton.disabled = true;
               }
           }


           // Add event listeners for password fields
           newPasswordInput.addEventListener('input', validatePasswords);
           confirmPasswordInput.addEventListener('input', validatePasswords);
       });


   document.addEventListener('DOMContentLoaded', function () {


   const errorMessage = document.getElementById('error-message');
   if (errorMessage) {
       setTimeout(() => {
           errorMessage.style.opacity = '0';
           setTimeout(() => {
               errorMessage.style.display = 'none';
           }, 500); // Duration for fade-out effect
       }, 5000); // Duration to show error message
   }
   });
</script>

@endpush
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
<li class="breadcrumb-item">{{__('Driver')}}</li>
@endsection
@php
use Carbon\Carbon;
@endphp
@section('action-btn')
<div class="float-end">

</div>
@endsection
@section('content')
@if ($errors->any())
<div id="error-message" class="alert alert-danger">
   <ul>
      @foreach ($errors->all() as $error)
      <li>{{ $error }}</li>
      @endforeach
   </ul>
</div>
@endif
<div class="row">
   {{--
   <div class="col-3">
      @include('layouts.depot_setup')
   </div>
   --}}
   <div class="col-9" style="width: 100%">
      <div class="card">
         <div class="card-body table-border-style">
            <div class="table-responsive">
               <table class="table datatable">
                  <thead>
                     <tr>
                        <th class="text-end ">{{__('Action')}}</th>
                        <th>{{__('Driver Licence No')}}</th>
                        <th>{{__('Created')}}</th>
                        <th>{{__('LC Check')}}</th>
                        <th>{{__('Driver Status')}}</th>

                     </tr>
                  </thead>
                  <tbody>
                     @foreach($driver->duplicateDrivers as $duplicateDriver)
                     <tr>
                        <td class="text-center">
                            <div class="action-btn bg-warning ms-2">
                               <a href="{{ route('driver.history.show',$duplicateDriver->id) }}"
                                  class="mx-3 btn btn-sm d-inline-flex align-items-center"
                                  data-bs-whatever="{{__('View Budget Planner')}}" data-bs-toggle="tooltip"
                                  data-bs-original-title="{{__('View')}}"> <span class="text-white"> <i class="ti ti-eye"></i></span></a>
                            </div>
                        </td>
                        <td class="text-center">{{ $duplicateDriver->driver_licence_no }}</td> <!-- Adjust this field as per your DuplicateDriver model -->
                        <!--<td class="text-center">{{ !empty($duplicateDriver->creator)?$duplicateDriver->creator->username:'' }}</td>-->
                        <td class="text-center">{{ !empty($duplicateDriver->creator)? $duplicateDriver->creator->username : ($duplicateDriver->created_by == 1.1 
                                    ? 'Automation' : ($duplicateDriver->created_by == 'Auto Generator' ? 'Auto Generator' : '')) }}
                        </td>
                        <td class="text-center">{{ $duplicateDriver->latest_lc_check }}</td> <!-- Adjust this field as per your DuplicateDriver model -->
                        <td class="text-center">{{ $duplicateDriver->driver_status }}</td> <!-- Adjust this field as per your DuplicateDriver model -->
                     </tr>
                     @endforeach

                  </tbody>
               </table>
            </div>
         </div>
      </div>
   </div>
</div>


@endsection
