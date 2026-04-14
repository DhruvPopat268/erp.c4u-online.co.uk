@php
    $users=\Auth::user();
    $profile=\App\Models\Utility::get_file('uploads/avatar/');
    $languages=\App\Models\Utility::languages();

    $lang = isset($users->lang)?$users->lang:'en';
    if ($lang == null) {
        $lang = 'en';
    }
    // $LangName = \App\Models\Language::where('code',$lang)->first();
    // $LangName =\App\Models\Language::languageData($lang);
    $LangName = cache()->remember('full_language_data_' . $lang, now()->addHours(24), function () use ($lang) {
    return \App\Models\Language::languageData($lang);
    });

    $setting = \App\Models\Utility::settings();

    $unseenCounter=App\Models\ChMessage::where('to_id', Auth::user()->id)->where('seen', 0)->count();
@endphp
@if (isset($setting['cust_theme_bg']) && $setting['cust_theme_bg'] == 'on')
    <header class="dash-header transprent-bg">
@else
    <header class="dash-header">
@endif
<div class="header-wrapper">
    <div class="me-auto dash-mob-drp">
        <ul class="list-unstyled">
            <li class="dash-h-item mob-hamburger">
                <a href="#!" class="dash-head-link" id="mobile-collapse">
                    <div class="hamburger hamburger--arrowturn">
                        <div class="hamburger-box">
                            <div class="hamburger-inner"></div>
                        </div>
                    </div>
                </a>
            </li>

            <li class="dropdown dash-h-item drp-company">
                <a class="dash-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                    <span class="theme-avtar">
                         <img src="{{ !empty(\Auth::user()->avatar) ? $profile . \Auth::user()->avatar :  $profile.'avatar.png'}}" class="img-fluid rounded-circle">
                    </span>
                                        <span class="hide-mob ms-2">{{__('Hi, ')}}{{\Auth::user()->username }} <br><small>{{__('You are Logged in as ')}} @if (\Auth::user()->type == 'company')
            {{ 'Super Admin' }}
        @elseif (\Auth::user()->type == 'Companies')
            {{ 'Company' }}
        @else
            {{ \Auth::user()->type }}
        @endif</small></span>
                    <i class="ti ti-chevron-down drp-arrow nocolor hide-mob"></i>
                </a>
                <div class="dropdown-menu dash-h-dropdown">

                    <a href="{{route('profile')}}" class="dropdown-item">
                        <i class="ti ti-user text-dark"></i><span>{{__('Profile')}}</span>
                    </a>

                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('frm-logout').submit();" class="dropdown-item">
                        <i class="ti ti-power text-dark"></i><span>{{__('Logout')}}</span>
                    </a>

                    <form id="frm-logout" action="{{ route('logout') }}" method="POST" class="d-none">
                        {{ csrf_field() }}
                    </form>

                </div>
            </li>

        </ul>
    </div>

 @if(Route::currentRouteName() === 'driver.index' && \Auth::user()->hasRole('Companies'))
    <div class="mb-4" style="margin-top:2%;">
        @if($contracts && count($contracts) > 0)
            @php
                // Initialize an array to keep track of unique companies
                $uniqueCompanies = [];
            @endphp

            @foreach($contracts as $driver)
                @php
                    $company = $driver->types; // Retrieve the company details
                    $companyId = $company->id ?? null;

                    // Check if the company has already been added
                    if ($companyId && !isset($uniqueCompanies[$companyId])) {
                        $uniqueCompanies[$companyId] = [
                            'api_call_count' => $company->api_call_count ?? 0,
                            'payment_type'   => $company->payment_type ?? null,
                            'coins' => $company->coins ?? 0, // assuming this field exists
                            'name' => $company->name ?? 'N/A',
                        ];
                    }
                @endphp
            @endforeach
            @foreach($uniqueCompanies as $company)
                <div style="font-weight:bold;">
                    @if($company['payment_type'] === 'Prepaid')
                        @if($company['coins'] > 0)
                            <span style="color:green;margin-right:10px;">
                                Available Coins: {{ $company['coins'] }}
                            </span>
                        @else
                            <span style="color:#dc3545;font-weight:600;margin-right:10px;">
                                ⚠️ No coins left. Please recharge or contact admin.
                            </span>
                        @endif
                    @endif
                    <span style="color:red; margin-right:10px;">  | </span><span style="color:red; margin-right:10px;">API Call Total Count: {{ $company['api_call_count'] }}</span>
                    
                </div>
            @endforeach

        @else
            <p>No company details found for the logged-in user.</p>
        @endif
    </div>
@endif


</div>
    </header>
