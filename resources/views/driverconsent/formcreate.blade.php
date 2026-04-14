<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Driver Consent Form</title>
    <style>
        * {
            box-sizing: border-box;
        }

        input[type="text"],
        input[type="date"],
        textarea,
        select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            resize: vertical;
        }

        label {
            padding: 12px 12px 12px 0;
            display: inline-block;
            font-family: sans-serif;
        }

        input[type="submit"] {
            background-color: #04AA6D;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            float: right;
        }


        input[type="submit"]:hover {
            background-color: #45a049;
        }

        .container {
            border-radius: 5px;
            background-color: #f2f2f2;
            padding: 20px;
            margin: 20px auto;
            max-width: 800px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .col-25 {
            float: left;
            width: 25%;
            margin-top: 6px;
        }

        .col-75 {
            float: left;
            width: 75%;
            margin-top: 6px;
        }

        /* Clear floats after the columns */
        .row:after {
            content: "";
            display: table;
            clear: both;
        }
        .companydetails{
            margin-left: 10px;
            color: white;
            padding: 12px 20px;
            background-color: #48494B;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            float: right;
        }

        .logo img {
            max-width: 130px; margin-left:43%
        }
        .signature-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .signature-pad {
            border: 1px solid #000;
            width: 100%;
            max-width: 600px;
            height: 300px;
            background-color: #f3f3f3;
            touch-action: none; /* Disables default touch interactions */
        }

        .buttons {
            margin-top: 10px;
            display: flex;
            gap: 10px;
        }

        button {
            padding: 10px;
            cursor: pointer;
        }
                .clear-signature{
            background-color: #48494B;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        /* Responsive layout */
        @media screen and (max-width: 600px) {
            .col-25,
            .col-75,
            input[type="submit"] {
                width: 100%;
                margin-top: 0;
            }
            .companydetails{
                margin-left: 5px;
                color: white;
                padding: 4px;
                background-color: #48494B;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                float: right;
                width: 38%;
            }
             .logo img {
                max-width: 130px; margin-left:34%
            }
            
        }

        /* Error message styling */
        .text-danger {
            color: #d9534f;
            font-size: 0.9em;
        }
        
       /*.popup-overlay {*/
            display: none; /* Initially hidden */
       /*     position: fixed;*/
       /*     top: 0;*/
       /*     left: 0;*/
       /*     width: 100%;*/
       /*     height: 100%;*/
       /*     background: rgba(0, 0, 0, 0.5);*/
       /*     justify-content: center;*/
       /*     align-items: center;*/
       /*     z-index: 1000;*/
       /* }*/

       /* .popup-content {*/
       /*     background: white;*/
       /*     padding: 20px;*/
       /*     border-radius: 8px;*/
       /*     width: 80%;*/
       /*     max-width: 500px;*/
       /*     box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);*/
       /* }*/

       /* .no-scroll {*/
       /*     overflow: hidden;*/
       /* }*/
    </style>
</head>
<body>


<div class="container">
@if ($errors->any())
    <div class="alert text-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="logo">

    <img src="{{ $img }}" alt="Logo"/>
    <h2 style="text-align:center;font-family: sans-serif;">Driver Consent Form</h2>
</div>


    <form id="driverconsent-form" action="{{ route('driverconsent.formstore') }}" method="POST">
        @csrf <!-- Include CSRF token for security -->

        <div class="row">
            <div class="col-25">
                <label for="account_no">Company Account Id</label>
            </div>
            <div class="col-75">
                <input type="text" id="account_no" name="account_no" placeholder="company account id.." required style="width: 60%;">
                <button type="button" onclick="fetchCompanyDetails()" class="companydetails">Fetch Company Details</button>
                @error('account_no')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>


        <!-- Other input fields remain the same -->
                <div class="row" style="display: none;">
            <div class="col-25">
                <label for="company_id">Company Id</label>
            </div>
            <div class="col-75">
                <input type="text" id="company_id" name="company_id" placeholder="company Id.." style="text-transform: uppercase;"  readonly>
                @error('company_id')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="row">
            <div class="col-25">
                <label for="companyName">Company Name</label>
            </div>
            <div class="col-75">
                <input type="text" id="companyName" name="companyName" placeholder="company name.." style="text-transform: uppercase;" required readonly>
                @error('companyName')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="row">
            <div class="col-25">
                <label for="company_address">Company Address</label>
            </div>
            <div class="col-75">
                <textarea id="company_address" name="company_address" placeholder="company address.." required style="height: 100px; text-transform: uppercase;"></textarea>
                @error('company_address')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>





        <div class="row">
            <div class="col-25">
                <label for="account_number">Account Number</label>
            </div>
            <div class="col-75">
                <input type="text" id="account_number" name="account_number" placeholder="Your account number.." style="text-transform: uppercase;" required maxlength="4">
                @error('account_number')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>


        <div class="row">
            <div class="col-25">
                <label for="reference_number">Driver Number</label>
            </div>
            <div class="col-75">
                <input type="text" id="reference_number" name="reference_number" placeholder="Your driver number.." style="text-transform: uppercase;" required>
                @error('reference_number')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="row">
            <div class="col-25">
                <label for="making_an_enquiry">Are you making an enquiry  on behalf of another company?</label>
            </div>
            <div class="col-75">
                <select id="making_an_enquiry" name="making_an_enquiry" required onchange="toggleEnquiryDetails()">
                    <option value="">Select an option</option>
                    <option value="yes">Yes</option>
                    <option value="no">No</option>
                </select>
                @error('making_an_enquiry')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="row" id="enquiry_details_row" style="display: none;">
            <div class="col-25">
                <label for="making_an_enquiry_details">If yes, please give the company name</label>
            </div>
            <div class="col-75">
                <textarea id="making_an_enquiry_details" name="making_an_enquiry_details" placeholder="If yes, please give the company name.." style="height: 100px; text-transform: uppercase;"></textarea>
                @error('making_an_enquiry_details')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="row">
            <div class="col-25">
                <label for="reason_for_processing_information">Reason for Processing Information</label>
            </div>
            <div class="col-75">
                <textarea  id="reason_for_processing_information" name="reason_for_processing_information" placeholder="Reason.." style="height: 100px;text-transform: uppercase;" required></textarea>
                @error('reason_for_processing_information')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>
        
        <div class="row">
            <div class="col-25">
                <label for="cpc_information">Do you need CPC information?</label>
            </div>
            <div class="col-75">
                <select id="cpc_information" name="cpc_information">
                    <option value="">Select an option</option>
                    <option value="yes">Yes</option>
                    <option value="no">No</option>
                </select>
                @error('cpc_information')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="row">
            <div class="col-25">
                <label for="tacho_information">Do you need tachograph information?</label>
            </div>
            <div class="col-75">
                <select id="tacho_information" name="tacho_information">
                    <option value="">Select an option</option>
                    <option value="yes">Yes</option>
                    <option value="no">No</option>
                </select>
                @error('tacho_information')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="row">
            <div class="col-25">
                <label for="surname">Surname</label>
            </div>
            <div class="col-75">
                <input type="text" id="surname" name="surname" placeholder="Your surname.." style="text-transform: uppercase;" required>
                @error('surname')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="row">
            <div class="col-25">
                <label for="first_name">First Name</label>
            </div>
            <div class="col-75">
                <input type="text" id="first_name" name="first_name" placeholder="Your first name.." style="text-transform: uppercase;" required>
                @error('first_name')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="row">
            <div class="col-25">
                <label for="middle_name">Middle Name</label>
            </div>
            <div class="col-75">
                <input type="text" id="middle_name" name="middle_name" placeholder="Your middle name.." style="text-transform: uppercase;">
                @error('middle_name')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>
        <div class="row">
            <div class="col-25">
                <label for="email">Email</label>
            </div>
            <div class="col-75">
                <input type="text" id="email" name="email" placeholder="Your Email Id.." required>
                @error('email')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="row">
            <div class="col-25">
                <label for="date_of_birth">Date of Birth</label>
            </div>
            <div class="col-75">
                <input type="date" id="date_of_birth" name="date_of_birth" required>
                @error('date_of_birth')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="row">
            <div class="col-25">
                <label for="current_address_line1">Current Address Line 1</label>
            </div>
            <div class="col-75">
                <input type="text" id="current_address_line1" name="current_address_line1" placeholder="Your current address line 1.." style="text-transform: uppercase;" required maxlength="30">
                @error('current_address_line1')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="row">
            <div class="col-25">
                <label for="current_address_line2">Current Address Line 2</label>
            </div>
            <div class="col-75">
                <input type="text" id="current_address_line2" name="current_address_line2" placeholder="Your current address line 2.." style="text-transform: uppercase;" maxlength="30">
                @error('current_address_line2')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="row">
            <div class="col-25">
                <label for="current_address_line3">Current Address Line 3</label>
            </div>
            <div class="col-75">
                <input type="text" id="current_address_line3" name="current_address_line3" placeholder="Your current address line 3.." style="text-transform: uppercase;" maxlength="30">
                @error('current_address_line3')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>
        <div class="row">
            <div class="col-25">
                <label for="current_address_posttown">Current Address Post town</label>
            </div>
            <div class="col-75">
                <input type="text" id="current_address_posttown" name="current_address_posttown" placeholder="Your current address Post town.." style="text-transform: uppercase;" required maxlength="30">
                @error('current_address_posttown')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="row">
            <div class="col-25">
                <label for="current_address_postcode">Current Address Postcode</label>
            </div>
            <div class="col-75">
                <input type="text" id="current_address_postcode" name="current_address_postcode" placeholder="Your current address postcode.." style="text-transform: uppercase;" required maxlength="7">
                @error('current_address_postcode')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- Licence Address Fields -->
        <div class="row">
             <div class="col-25">
            </div>
            <div class="col-75">
                <input type="checkbox" id="same_address" onclick="copyLicenceAddress()"> <label for="same_address">Is Licence Address Same as Current Address?</label>
            </div>
        </div>

        <div class="row">
            <div class="col-25">
                <label for="licence_address_line1">Licence Address Line 1</label>
            </div>
            <div class="col-75">
                <input type="text" id="licence_address_line1" name="licence_address_line1" placeholder="Your licence address line 1.." style="text-transform: uppercase;" required maxlength="30">
                @error('licence_address_line1')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>
        <div class="row">
            <div class="col-25">
                <label for="licence_address_line2">Licence Address Line 2</label>
            </div>
            <div class="col-75">
                <input type="text" id="licence_address_line2" name="licence_address_line2" placeholder="Your licence address line 2.." style="text-transform: uppercase;" maxlength="30">
                @error('licence_address_line2')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="row">
            <div class="col-25">
                <label for="licence_address_line3">Licence Address Line 3</label>
            </div>
            <div class="col-75">
                <input type="text" id="licence_address_line3" name="licence_address_line3" placeholder="Your licence address line 3.." style="text-transform: uppercase;" maxlength="30">
                @error('licence_address_line3')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="row">
            <div class="col-25">
                <label for="licence_address_posttown">Licence Address Post town</label>
            </div>
            <div class="col-75">
                <input type="text" id="licence_address_posttown" name="licence_address_posttown" placeholder="Your licence address post town.." style="text-transform: uppercase;" required maxlength="30">
                @error('licence_address_posttown')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>
        <div class="row">
            <div class="col-25">
                <label for="licence_address_postcode">Licence Address Postcode</label>
            </div>
            <div class="col-75">
                <input type="text" id="licence_address_postcode" name="licence_address_postcode" placeholder="Your licence address postcode.." style="text-transform: uppercase;" required maxlength="7">
                @error('licence_address_postcode')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="row">
            <div class="col-25">
                <label for="driver_licence_no">Driver Licence Number</label>
            </div>
            <div class="col-75">
                <input type="text" id="driver_licence_no" name="driver_licence_no" placeholder="Your driver licence number.." style="text-transform: uppercase;" required>
                @error('driver_licence_no')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>

<!--        <div class="row">-->
<!--            <div class="col-25">-->
<!--                <label for="image">Signature</label>-->
<!--            </div>-->
<!--        </div>-->
<!--<div class="signature-container">-->
<!--        <canvas id="signature-pad" class="signature-pad"></canvas>-->
<!--        <div class="buttons">-->
<!--            <button onclick="clearSignature()">Clear</button>-->
<!--            <button onclick="saveSignature()">Save</button>-->
<!--        </div>-->
<!--    </div>-->
        <div class="row" style="margin-top: 10px;">
            <input type="submit" value="Submit" id="submit-btn">

            <!-- Loader HTML -->
            <div id="loader" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(194, 194, 194, 0.8); z-index: 9999;">
                <div class="loader-content" style="background: rgba(255, 255, 255, 0.8);box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);border-radius: 10px;position: absolute; top: 50%; left: 50%;padding: 10px; ">
                    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;color: #ffffff;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>

</body>
</html>
<script>

    function copyLicenceAddress() {
        if (document.getElementById('same_address').checked) {
            document.getElementById('licence_address_line1').value = document.getElementById('current_address_line1').value;
            document.getElementById('licence_address_line2').value = document.getElementById('current_address_line2').value;
            document.getElementById('licence_address_line3').value = document.getElementById('current_address_line3').value;
            document.getElementById('licence_address_posttown').value = document.getElementById('current_address_posttown').value;
            document.getElementById('licence_address_postcode').value = document.getElementById('current_address_postcode').value;
        } else {
            document.getElementById('licence_address_line1').value = '';
            document.getElementById('licence_address_line2').value = '';
            document.getElementById('licence_address_line3').value = '';
            document.getElementById('licence_address_posttown').value = '';
            document.getElementById('licence_address_postcode').value = '';
        }
    }
    // Show loader on submit button click
    document.getElementById('driverconsent-form').addEventListener('submit', function() {
        document.getElementById('submit-btn').disabled = true; // Disable submit button
        document.getElementById('loader').style.display = 'block'; // Show loader
    });

    function toggleEnquiryDetails() {
        const selectElement = document.getElementById('making_an_enquiry');
        const enquiryDetailsRow = document.getElementById('enquiry_details_row');

        if (selectElement.value === 'yes') {
            enquiryDetailsRow.style.display = 'block';
        } else {
            enquiryDetailsRow.style.display = 'none';
        }
    }

    function fetchCompanyDetails() {
        const accountNo = document.getElementById('account_no').value;

        if (!accountNo) {
            alert('Please enter an account number');
            return;
        }

        // Make AJAX request to fetch company details
        fetch(`/company-details/${accountNo}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Populate fields with fetched data
                    document.getElementById('companyName').value = data.companyName;
                    document.getElementById('company_address').value = data.companyAddress;
                                        document.getElementById('company_id').value = data.company_id;

                    // Populate other fields as needed
                } else {
                    alert(data.message || 'Company details not found');
                }
            })
            .catch(error => console.error('Error fetching company details:', error));
    }

</script>
<script src="{{ asset('assets/js/plugins/signature_pad/signature_pad.min.js') }}"></script>
<script>
 const canvas = document.getElementById("signature-pad");
        const ctx = canvas.getContext("2d");

        let drawing = false;
        let lastX = 0;
        let lastY = 0;

        function resizeCanvas() {
            canvas.width = canvas.offsetWidth;
            canvas.height = canvas.offsetHeight;
            ctx.lineWidth = 2;
            ctx.lineCap = "round";
            ctx.strokeStyle = "#000";
        }

        window.addEventListener("resize", resizeCanvas);
        resizeCanvas();

        function startDrawing(event) {
            drawing = true;
            const { x, y } = getPosition(event);
            lastX = x;
            lastY = y;
        }

        function draw(event) {
            if (!drawing) return;
            const { x, y } = getPosition(event);
            ctx.beginPath();
            ctx.moveTo(lastX, lastY);
            ctx.lineTo(x, y);
            ctx.stroke();
            lastX = x;
            lastY = y;
        }

        function stopDrawing() {
            drawing = false;
        }

        function getPosition(event) {
            const rect = canvas.getBoundingClientRect();
            const x = event.clientX
                ? event.clientX - rect.left
                : event.touches[0].clientX - rect.left;
            const y = event.clientY
                ? event.clientY - rect.top
                : event.touches[0].clientY - rect.top;
            return { x, y };
        }

        canvas.addEventListener("mousedown", startDrawing);
        canvas.addEventListener("mousemove", draw);
        canvas.addEventListener("mouseup", stopDrawing);
        canvas.addEventListener("mouseout", stopDrawing);

        canvas.addEventListener("touchstart", (event) => {
            event.preventDefault();
            startDrawing(event);
        });
        canvas.addEventListener("touchmove", (event) => {
            event.preventDefault();
            draw(event);
        });
        canvas.addEventListener("touchend", stopDrawing);

        function clearSignature() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
        }

        function saveSignature() {
            const dataURL = canvas.toDataURL("image/png");
            console.log("Signature saved as image URL:", dataURL);
            // Send dataURL to server via AJAX or a form submission
        }

</script>












 <!-- Popup container -->
 <!--   <div id="signaturePopup" class="popup-overlay">-->
 <!--       <div class="popup-content">-->
 <!--           <canvas id="signatureCanvas" width="400" height="200" style="border: 1px solid #000;"></canvas>-->
 <!--           <button id="clearSig">Clear</button>-->
 <!--           <button onclick="closePopup()">Close</button>-->
 <!--       </div>-->
 <!--   </div>-->

    <!-- Button to open popup -->
 <!--   <button onclick="openPopup()">Open Signature Pad</button>-->

 <!--   <script>-->
 <!--       let isDrawing = false;-->
 <!--       const canvas = document.getElementById('signatureCanvas');-->
 <!--       const ctx = canvas.getContext('2d');-->

        <!--// Start drawing-->
 <!--       function startDrawing(event) {-->
 <!--           isDrawing = true;-->
 <!--           ctx.beginPath();-->
 <!--           const { x, y } = getPointerPosition(event);-->
 <!--           ctx.moveTo(x, y);-->
 <!--           event.preventDefault();-->
 <!--       }-->

        <!--// Draw on canvas-->
 <!--       function draw(event) {-->
 <!--           if (!isDrawing) return;-->
 <!--           const { x, y } = getPointerPosition(event);-->
 <!--           ctx.lineTo(x, y);-->
 <!--           ctx.stroke();-->
 <!--           ctx.beginPath();-->
 <!--           ctx.moveTo(x, y);-->
 <!--           event.preventDefault();-->
 <!--       }-->

        <!--// End drawing-->
 <!--       function endDrawing() {-->
 <!--           if (isDrawing) {-->
 <!--               isDrawing = false;-->
 <!--               updateSignatureInput();-->
 <!--           }-->
 <!--       }-->

        <!--// Get pointer position relative to canvas-->
 <!--       function getPointerPosition(event) {-->
 <!--           const rect = canvas.getBoundingClientRect();-->
 <!--           let x, y;-->

 <!--           if (event.type.includes('touch')) {-->
 <!--               const touch = event.touches[0];-->
 <!--               x = touch.clientX - rect.left;-->
 <!--               y = touch.clientY - rect.top;-->
 <!--           } else {-->
 <!--               x = event.offsetX;-->
 <!--               y = event.offsetY;-->
 <!--           }-->

 <!--           return { x, y };-->
 <!--       }-->

        <!--// Update the signature input-->
 <!--       function updateSignatureInput() {-->
 <!--           console.log("Signature updated with canvas data URL");-->
 <!--       }-->

        <!--// Clear the signature pad-->
 <!--       document.getElementById('clearSig').addEventListener('click', function () {-->
 <!--           ctx.clearRect(0, 0, canvas.width, canvas.height);-->
 <!--           updateSignatureInput();-->
 <!--       });-->

        <!--// Open the popup-->
 <!--       function openPopup() {-->
 <!--           console.log("Opening popup...");-->
 <!--           document.getElementById('signaturePopup').style.display = 'flex';-->
 <!--           document.body.classList.add('no-scroll');-->
 <!--       }-->

        <!--// Close the popup-->
 <!--       function closePopup() {-->
 <!--           console.log("Closing popup...");-->
 <!--           document.getElementById('signaturePopup').style.display = 'none';-->
 <!--           document.body.classList.remove('no-scroll');-->
 <!--       }-->

        <!--// Event listeners for drawing-->
 <!--       canvas.addEventListener('mousedown', startDrawing);-->
 <!--       canvas.addEventListener('mouseup', endDrawing);-->
 <!--       canvas.addEventListener('mousemove', draw);-->
 <!--       canvas.addEventListener('touchstart', startDrawing);-->
 <!--       canvas.addEventListener('touchend', endDrawing);-->
 <!--       canvas.addEventListener('touchmove', draw);-->
 <!--   </script>-->





