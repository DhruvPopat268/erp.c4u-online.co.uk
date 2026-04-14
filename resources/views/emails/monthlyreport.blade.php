<div style="box-sizing:border-box;margin:0;font-family:Roboto,Arial,Helvetica,Verdana;font-size:17px;font-weight:400;line-height:1.8;color:#212529;text-align:left;background-color:#fff">
    <p style="box-sizing:border-box;margin-top:0;margin-bottom:1rem"><strong style="box-sizing:border-box;font-weight:bolder">Dear {{ strtoupper($companyData['companyName']) }},</strong><br style="box-sizing:border-box"><br style="box-sizing:border-box">
       <span style="font-size:10pt;box-sizing:border-box">Please find the attached monthly report for the above mentioned period.
</span><br style="box-sizing:border-box"><span style="font-size:10pt;box-sizing:border-box">If you have any questions, please do not hesitate to contact us.
</span>
    </p>
    <div class="gmail_signature" style="box-sizing:border-box">
       <div style="box-sizing:border-box"><span style="font-size:10pt;box-sizing:border-box">Kind regards,</span></div>
       <div style="box-sizing:border-box"><span style="font-size:10pt;box-sizing:border-box"><span class="il">PTC</span> Compliance</span></div>
       <div style="box-sizing:border-box"><span style="font-size:10pt;box-sizing:border-box">07868882977<br style="box-sizing:border-box"><br style="box-sizing:border-box"></span></div>
    </div>
    <p style="box-sizing:border-box;margin-top:0;margin-bottom:1rem"><img src="https://erp.c4u-online.co.uk/storage/uploads/logo/email%20footer%20unimix_small.png" style="box-sizing:border-box;vertical-align:middle;border-style:none" class="CToWUd" data-bit="iit" jslog="138226; u014N:xr6bB; 53:WzAsMl0."></p>
    <p style="box-sizing:border-box;margin-top:0;margin-bottom:1rem"><img src="https://erp.c4u-online.co.uk/storage/uploads/logo/Email%20Footer%20logo%20small.png" style="box-sizing:border-box;vertical-align:middle;border-style:none" class="CToWUd" data-bit="iit" jslog="138226; u014N:xr6bB; 53:WzAsMl0."></p>
    <p style="box-sizing:border-box;margin-top:0;margin-bottom:1rem"><span style="font-size:10pt;box-sizing:border-box">Courses available</span></p>
    <p style="box-sizing:border-box;margin-top:0;margin-bottom:1rem"><span style="font-size:10pt;box-sizing:border-box">Transport Manager |&nbsp; OLAT |&nbsp;
       Safe Urban Driving | LoCity | FORS</span>
    </p>
    <p style="box-sizing:border-box;margin-top:0;margin-bottom:1rem"><strong style="box-sizing:border-box;font-weight:bolder">Disclaimer</strong></p>
    <p style="box-sizing:border-box;margin-top:0;margin-bottom:1rem"><span style="font-size:10pt;box-sizing:border-box">This email and any attachments to it may be confidential and are intended solely for the use
        of the individual to whom it is addressed.
        If you are not the intended recipient of this email, you must neither take any action based upon its contents,
        nor copy or show it to anyone. Please contact the sender if you believe you have received this email in error. </span>
    </p>
    @foreach ($companyData['drivers'] as $driver)

    @foreach ($driver['files'] as $file)

    @endforeach

    @endforeach
 </div>
