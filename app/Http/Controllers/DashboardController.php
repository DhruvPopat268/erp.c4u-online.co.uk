<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AttendanceEmployee;
use App\Models\Bug;
use App\Models\BugStatus;
use App\Models\CompanyDetails;
use App\Models\Contract;
use App\Models\Deal;
use App\Models\DealTask;
use App\Models\Employee;
use App\Models\Event;
use App\Models\Expense;
use App\Models\Job;
use App\Models\Lead;
use App\Models\LeadStage;
use App\Models\Meeting;
use App\Models\Order;
use App\Models\Pcn;
use App\Models\Plan;
use App\Models\Pos;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\Purchase;
use App\Models\Stage;
use App\Models\Timesheet;
use App\Models\TimeTracker;
use App\Models\Trainer;
use App\Models\Training;
use App\Models\TrainingDriverAssign;
use App\Models\User;
use App\Models\Utility;
use App\Models\vehicleDetails;
use App\Models\WorkAroundStore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function account_dashboard_index(Request $request)
    {

        if (Auth::check()) {
            if (Auth::user()->type == 'super admin') {
                return redirect()->route('client.dashboard.view');
            } elseif (Auth::user()->type == 'client') {
                return redirect()->route('client.dashboard.view');
            } else {
                $user = Auth::user();
                if (\Auth::user()->can('show dashboard')) {

                    $selectedCompanyId = $request->input('company_id');

                    // Check the user's role
                    $isAdminOrManager = $user->hasRole('company') || $user->hasRole('PTC manager');
                    $userDepots = ! $isAdminOrManager ? json_decode($user->depot_id, true) : [];

                    // For non-admin and non-PTC manager users, set the selected company to the user's company
                    if (! $isAdminOrManager) {
                        $selectedCompanyId = $user->companyname; // Assuming the user's company_id is stored in the users table
                    }

                    $totalApiCallCount = CompanyDetails::sum('api_call_count');

                    // Get the list of companies for the dropdown, visible only to admin and PTC manager
                    $companies = $isAdminOrManager
                     ? CompanyDetails::where('created_by', \Auth::user()->creatorId())->where('company_status', 'Active')->orderBy('name', 'asc')->get()
                     : CompanyDetails::where('id', $user->companyname)->where('company_status', 'Active')->orderBy('name', 'asc')->get();

                    // Initialize API call count and name variables
                    $selectedCompanyApiCallCount = 0;
                    $selectedCompanyName = '';

                    if ($selectedCompanyId) {
                        // Fetch the selected company's API call count and name if a company is selected
                        $selectedCompany = CompanyDetails::find($selectedCompanyId);
                        $selectedCompanyApiCallCount = $selectedCompany ? $selectedCompany->api_call_count : 0;
                        $selectedCompanyName = $selectedCompany ? $selectedCompany->name : '';
                    } else {
                        // If no company is selected and the user is an admin or PTC manager, sum up the API call count for all companies
                        $selectedCompanyApiCallCount = $isAdminOrManager ? $totalApiCallCount : 0;
                        $selectedCompanyName = $isAdminOrManager ? 'All Companies' : $user->companyName;
                    }

                    $crm_data = [];

                    if ($isAdminOrManager) {
                        // Aggregate data for all active companies
                        $crm_data['total_company'] = CompanyDetails::where('company_status', 'Active')->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                            return $query->where('id', $selectedCompanyId);
                        })->count();

                        $crm_data['total_vehicle'] = vehicleDetails::whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                            return $query->where('companyName', $selectedCompanyId);
                        })->count();

                        $crm_data['total_work_around_stores'] = WorkAroundStore::whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                            return $query->where('company_id', $selectedCompanyId);
                        })->count();

                        $crm_data['pending_uploaded_date'] = WorkAroundStore::whereNull('uploaded_date')->whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                            return $query->where('company_id', $selectedCompanyId);
                        })->count();

                        $crm_data['complated_uploaded_date'] = WorkAroundStore::whereNotNull('uploaded_date')->whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                            return $query->where('company_id', $selectedCompanyId);
                        })->count();

                        $crm_data['total_pcns'] = Pcn::whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                            return $query->where('company_id', $selectedCompanyId);
                        })->count();

                        $issuingAuthorityCounts = Pcn::select('issuing_authority', \DB::raw('COUNT(*) as total'))
                            ->whereHas('types', function ($query) {
                                $query->where('company_status', 'Active');
                            })
                            ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                                return $query->where('company_id', $selectedCompanyId);
                            })
                            ->whereYear('created_at', now()->year)
                            ->groupBy('issuing_authority')
                            ->pluck('total', 'issuing_authority')
                            ->toArray();

                        // Normalize keys
                        $crm_data['pcn_issuing_counts'] = [
                            'DVSA' => $issuingAuthorityCounts['DVSA'] ?? 0,
                            'Local Council' => $issuingAuthorityCounts['Local Council'] ?? 0,
                            'Police' => $issuingAuthorityCounts['Police'] ?? 0,
                            'Other' => $issuingAuthorityCounts['Other'] ?? 0,
                        ];

                        $workaroundTotalCountsByMonth = \App\Models\WorkAroundStore::selectRaw('MONTH(created_at) as month, COUNT(*) as total')
                            ->whereHas('types', function ($query) {
                                $query->where('company_status', 'Active');
                            })
                            ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                                return $query->where('company_id', $selectedCompanyId);
                            })
                            ->whereYear('created_at', now()->year)
                            ->groupBy(\DB::raw('MONTH(created_at)'))
                            ->pluck('total', 'month')
                            ->toArray();

                        // Ensure you have data for all months up to the current month
                        $currentMonth = now()->month;
                        $monthlyWorkaround = [];
                        for ($i = 1; $i <= $currentMonth; $i++) {
                            $monthlyWorkaround[] = $workaroundTotalCountsByMonth[$i] ?? 0;
                        }

                        // Pass this to your view
                        $crm_data['monthly_total_workaround'] = $monthlyWorkaround;

                        $workaroundPendingCountsByMonth = WorkAroundStore::selectRaw('MONTH(created_at) as month, COUNT(*) as pending')
                            ->whereHas('types', function ($query) {
                                $query->where('company_status', 'Active');
                            })
                            ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                                return $query->where('company_id', $selectedCompanyId);
                            })
                            ->whereYear('created_at', now()->year)
                            ->whereNull('uploaded_date')
                            ->groupBy(\DB::raw('MONTH(created_at)'))
                            ->pluck('pending', 'month')
                            ->toArray();

                        // Ensure you have data for all months up to the current month
                        $monthlyPendingWorkaround = [];
                        for ($i = 1; $i <= $currentMonth; $i++) {
                            $monthlyPendingWorkaround[] = $workaroundPendingCountsByMonth[$i] ?? 0;
                        }

                        // Pass this to your view
                        $crm_data['monthly_pending_workaround'] = $monthlyPendingWorkaround;

                        $workaroundCompletedCountsByMonth = WorkAroundStore::selectRaw('MONTH(created_at) as month, COUNT(*) as completed')
                            ->whereHas('types', function ($query) {
                                $query->where('company_status', 'Active');
                            })
                            ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                                return $query->where('company_id', $selectedCompanyId);
                            })
                            ->whereYear('created_at', now()->year)
                            ->whereNotNull('uploaded_date')
                            ->groupBy(\DB::raw('MONTH(created_at)'))
                            ->pluck('completed', 'month')
                            ->toArray();

                        // Ensure you have data for all months up to the current month
                        $monthlyCompletedWorkaround = [];
                        for ($i = 1; $i <= $currentMonth; $i++) {
                            $monthlyCompletedWorkaround[] = $workaroundCompletedCountsByMonth[$i] ?? 0;
                        }

                        // Pass this to your view
                        $crm_data['monthly_completed_workaround'] = $monthlyCompletedWorkaround;

                        //TOTALPCNSCOUNT
                        $pcnCountsByMonth = Pcn::selectRaw('MONTH(created_at) as month, COUNT(*) as total')
                            ->whereHas('types', function ($query) {
                                $query->where('company_status', 'Active');
                            })
                            ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                                return $query->where('company_id', $selectedCompanyId);
                            })
                            ->whereYear('created_at', now()->year)
                            ->groupBy(\DB::raw('MONTH(created_at)'))
                            ->pluck('total', 'month')
                            ->toArray();

                        // Ensure you have data for all months up to the current month
                        $currentMonth = now()->month;
                        $monthlyPCNs = [];
                        for ($i = 1; $i <= $currentMonth; $i++) {
                            $monthlyPCNs[] = $pcnCountsByMonth[$i] ?? 0;
                        }

                        // Pass this to your view
                        $crm_data['monthly_pcns'] = $monthlyPCNs;

                        //colsechart
                        $closedPcnCountsByMonth = Pcn::selectRaw('MONTH(created_at) as month, COUNT(*) as closed')
                            ->whereHas('types', function ($query) {
                                $query->where('company_status', 'Active');
                            })
                            ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                                return $query->where('company_id', $selectedCompanyId);
                            })
                            ->whereYear('created_at', now()->year)
                            ->where('status', 'Closed')
                            ->groupBy(\DB::raw('MONTH(created_at)'))
                            ->pluck('closed', 'month')
                            ->toArray();

                        // Ensure you have data for all months up to the current month
                        $monthlyClosedPCNs = [];
                        for ($i = 1; $i <= $currentMonth; $i++) {
                            $monthlyClosedPCNs[] = $closedPcnCountsByMonth[$i] ?? 0;
                        }

                        // Pass this to your view
                        $crm_data['monthly_closed_pcns'] = $monthlyClosedPCNs;

                        //outstandingpendingchart
                        $outstandingPcnCountsByMonth = Pcn::selectRaw('MONTH(created_at) as month, COUNT(*) as outstanding')
                            ->whereHas('types', function ($query) {
                                $query->where('company_status', 'Active');
                            })
                            ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                                return $query->where('company_id', $selectedCompanyId);
                            })
                            ->whereYear('created_at', now()->year)
                            ->where('status', 'outstanding')
                            ->groupBy(\DB::raw('MONTH(created_at)'))
                            ->pluck('outstanding', 'month')
                            ->toArray();

                        // Ensure you have data for all months up to the current month
                        $monthlyOutstandingPCNs = [];
                        for ($i = 1; $i <= $currentMonth; $i++) {
                            $monthlyOutstandingPCNs[] = $outstandingPcnCountsByMonth[$i] ?? 0;
                        }

                        // Pass this to your view
                        $crm_data['monthly_outstanding_pcns'] = $monthlyOutstandingPCNs;

                        $currentMonth = now()->month;

                        //Driver Trinning chart

                        $statuses = ['Complete', 'Pending', 'Decline']; // Add more if needed

                        $monthlyData = [];

                        foreach ($statuses as $status) {
                            $counts = TrainingDriverAssign::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
                                ->whereHas('training.companies', function ($query) {
                                    $query->where('company_status', 'Active');
                                })
                                ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                                    return $query->whereHas('training', function ($q) use ($selectedCompanyId) {
                                        $q->where('companyName', $selectedCompanyId);
                                    });
                                })
                                ->whereYear('created_at', now()->year)
                                ->where('status', $status)
                                ->groupBy(\DB::raw('MONTH(created_at)'))
                                ->pluck('count', 'month')
                                ->toArray();

                            // Fill months with zero if not available
                            $monthlyData[$status] = [];
                            for ($i = 1; $i <= $currentMonth; $i++) {
                                $monthlyData[$status][] = $counts[$i] ?? 0;
                            }
                        }

                        $crm_data['monthly_chart_data'] = $monthlyData;

                        $crm_data['pcn_status'] = Pcn::where('status', 'Closed')->whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                            return $query->where('company_id', $selectedCompanyId);
                        })->count();

                        $crm_data['policy_pending_status'] = \App\Models\PolicyAssignment::whereHas('company', function ($query) {
                            $query->where('company_status', 'Active');
                        })->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                            return $query->where('company_id', $selectedCompanyId);
                        })->where('status', 'Pending')->count();

                        $crm_data['policy_accept_status'] = \App\Models\PolicyAssignment::whereHas('company', function ($query) {
                            $query->where('company_status', 'Active');
                        })->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                            return $query->where('company_id', $selectedCompanyId);
                        })->where('status', 'Accept')->count();

                        $crm_data['policy_reassigned_status'] = \App\Models\PolicyAssignment::whereHas('company', function ($query) {
                            $query->where('company_status', 'Active');
                        })->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                            return $query->where('company_id', $selectedCompanyId);
                        })->where('status', 'Reassigned')->count();

                        $crm_data['total_tacho_status_valid'] = \App\Models\vehicleDetails::whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('tacho_status', 'Valid')
                            ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                                return $query->where('companyName', $selectedCompanyId);
                            })->count();
                        $crm_data['total_tacho_status_expiring_soon'] = \App\Models\vehicleDetails::whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('tacho_status', 'EXPIRING SOON')
                            ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                                return $query->where('companyName', $selectedCompanyId);
                            })->count();
                        $crm_data['total_tacho_status_expired'] = \App\Models\vehicleDetails::whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('tacho_status', 'EXPIRED')
                            ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                                return $query->where('companyName', $selectedCompanyId);
                            })->count();

                        $crm_data['total_dvs_pss_status_valid'] = \App\Models\vehicleDetails::whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('dvs_pss_status', 'Valid')
                            ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                                return $query->where('companyName', $selectedCompanyId);
                            })->count();
                        $crm_data['total_dvs_pss_status_expiring_soon'] = \App\Models\vehicleDetails::whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('dvs_pss_status', 'EXPIRING SOON')
                            ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                                return $query->where('companyName', $selectedCompanyId);
                            })->count();
                        $crm_data['total_dvs_pss_status_expired'] = \App\Models\vehicleDetails::whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('dvs_pss_status', 'EXPIRED')
                            ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                                return $query->where('companyName', $selectedCompanyId);
                            })->count();

                        $crm_data['total_insurance_status_valid'] = \App\Models\vehicleDetails::whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('insurance_status', 'Valid')
                            ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                                return $query->where('companyName', $selectedCompanyId);
                            })->count();
                        $crm_data['total_insurance_status_expiring_soon'] = \App\Models\vehicleDetails::whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('insurance_status', 'EXPIRING SOON')
                            ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                                return $query->where('companyName', $selectedCompanyId);
                            })->count();
                        $crm_data['total_insurance_status_expired'] = \App\Models\vehicleDetails::whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('insurance_status', 'EXPIRED')
                            ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                                return $query->where('companyName', $selectedCompanyId);
                            })->count();

                        $crm_data['total_PMI_status_valid'] = \App\Models\vehicleDetails::whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('PMI_status', 'Valid')
                            ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                                return $query->where('companyName', $selectedCompanyId);
                            })->count();
                        $crm_data['total_PMI_status_expiring_soon'] = \App\Models\vehicleDetails::whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('PMI_status', 'EXPIRING SOON')
                            ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                                return $query->where('companyName', $selectedCompanyId);
                            })->count();
                        $crm_data['total_PMI_status_expired'] = \App\Models\vehicleDetails::whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('PMI_status', 'EXPIRED')
                            ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                                return $query->where('companyName', $selectedCompanyId);
                            })->count();

                        $crm_data['total_brake_test_status_valid'] = \App\Models\vehicleDetails::whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('brake_test_status', 'Valid')
                            ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                                return $query->where('companyName', $selectedCompanyId);
                            })->count();
                        $crm_data['total_brake_test_status_expiring_soon'] = \App\Models\vehicleDetails::whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('brake_test_status', 'EXPIRING SOON')
                            ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                                return $query->where('companyName', $selectedCompanyId);
                            })->count();
                        $crm_data['total_brake_test_status_expired'] = \App\Models\vehicleDetails::whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('brake_test_status', 'EXPIRED')
                            ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                                return $query->where('companyName', $selectedCompanyId);
                            })->count();

                        $crm_data['total_taxDueDate_status_valid'] = \App\Models\vehicleDetails::whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('taxDueDate_status', 'Valid')
                            ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                                return $query->where('companyName', $selectedCompanyId);
                            })->count();

                        $crm_data['total_taxDueDate_status_expiring_soon'] = \App\Models\vehicleDetails::whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('taxDueDate_status', 'EXPIRING SOON')
                            ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                                return $query->where('companyName', $selectedCompanyId);
                            })->count();
                        $crm_data['total_taxDueDate_status_expired'] = \App\Models\vehicleDetails::whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('taxDueDate_status', 'EXPIRED')
                            ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                                return $query->where('companyName', $selectedCompanyId);
                            })->count();

                        $crm_data['total_annual_test_status_valid'] = \App\Models\Vehicles::whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('annual_test_status', 'Valid')
                            ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                                return $query->where('companyName', $selectedCompanyId);
                            })->count();
                        $crm_data['total_annual_test_status_expiring_soon'] = \App\Models\Vehicles::whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('annual_test_status', 'EXPIRING SOON')
                            ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                                return $query->where('companyName', $selectedCompanyId);
                            })->count();
                        $crm_data['total_annual_test_status_expired'] = \App\Models\Vehicles::whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('annual_test_status', 'EXPIRED')
                            ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                                return $query->where('companyName', $selectedCompanyId);
                            })->count();

                        $crm_data['total_operating_centers'] = \App\Models\Depot::whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                            return $query->where('companyName', $selectedCompanyId);
                        })->count();

                        $crm_data['status'] = \App\Models\Depot::whereHas('types', function ($query) {
                            $query->where('company_status', 'Inactive');
                        })->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                            return $query->where('companyName', $selectedCompanyId);
                        })->count();

                        $crm_data['total_driver'] = \App\Models\Driver::whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                            return $query->where('companyName', $selectedCompanyId);
                        })->count();

                        $crm_data['training_complete_status'] = \App\Models\TrainingDriverAssign::whereHas('training.companies', function ($query) {
                            $query->where('company_status', 'Active');
                        })->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                            return $query->whereHas('training', function ($q) use ($selectedCompanyId) {
                                $q->where('companyName', $selectedCompanyId);
                            });
                        })->where('status', 'Complete')->count();

                        $crm_data['training_pending_status'] = \App\Models\TrainingDriverAssign::whereHas('training.companies', function ($query) {
                            $query->where('company_status', 'Active');
                        })->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                            return $query->whereHas('training', function ($q) use ($selectedCompanyId) {
                                $q->where('companyName', $selectedCompanyId);
                            });
                        })->where('status', 'Pending')->count();

                        $crm_data['training_decline_status'] = \App\Models\TrainingDriverAssign::whereHas('training.companies', function ($query) {
                            $query->where('company_status', 'Active');
                        })->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                            return $query->whereHas('training', function ($q) use ($selectedCompanyId) {
                                $q->where('companyName', $selectedCompanyId);
                            });
                        })->where('status', 'Decline')->count();

                        $crm_data['total_no_of_vehicles'] = \App\Models\Depot::whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                            return $query->where('companyName', $selectedCompanyId);
                        })->sum('vehicles');

                        $crm_data['defects_count'] = \App\Models\WorkAroundStore::whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                            return $query->where('company_id', $selectedCompanyId);
                        })->sum('defects_count');

                        $crm_data['rectified'] = \App\Models\WorkAroundStore::whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                            return $query->where('company_id', $selectedCompanyId);
                        })->sum('rectified');

                        // CPC and Tacho statuses, filtered for active companies

                        $crm_data['Inactive_status_count'] = \App\Models\Depot::whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('status', 'Inactive')
                            ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                                return $query->where('companyName', $selectedCompanyId);
                            })->count();

                        $crm_data['active_depot_status_count'] = \App\Models\Depot::whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('status', 'Active')
                            ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                                return $query->where('companyName', $selectedCompanyId);
                            })->count();

                        $crm_data['valid_cpc_count'] = \App\Models\Driver::where('driver_status', 'Active')->whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('cpc_status', 'Valid')
                            ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                                return $query->where('companyName', $selectedCompanyId);
                            })->count();

                        $crm_data['expiring_soon_cpc_count'] = \App\Models\Driver::where('driver_status', 'Active')->whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('cpc_status', 'EXPIRING SOON')
                            ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                                return $query->where('companyName', $selectedCompanyId);
                            })->count();

                        $crm_data['expired_cpc_count'] = \App\Models\Driver::where('driver_status', 'Active')->whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('cpc_status', 'EXPIRED')
                            ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                                return $query->where('companyName', $selectedCompanyId);
                            })->count();

                        $crm_data['expiring_soon_tacho_count'] = \App\Models\Driver::where('driver_status', 'Active')->whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('tacho_card_status', 'EXPIRING SOON')
                            ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                                return $query->where('companyName', $selectedCompanyId);
                            })->count();

                        $crm_data['expired_tacho_count'] = \App\Models\Driver::where('driver_status', 'Active')->whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('tacho_card_status', 'EXPIRED')
                            ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                                return $query->where('companyName', $selectedCompanyId);
                            })->count();

                        $crm_data['active_driver_count'] = \App\Models\Driver::whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('driver_status', 'Active')
                            ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                                return $query->where('companyName', $selectedCompanyId);
                            })->count();

                        $crm_data['inactive_driver_count'] = \App\Models\Driver::whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('driver_status', 'InActive')
                            ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                                return $query->where('companyName', $selectedCompanyId);
                            })->count();

                        $crm_data['archived_driver_count'] = \App\Models\Driver::whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('driver_status', 'Archive')
                            ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                                return $query->where('companyName', $selectedCompanyId);
                            })->count();

                        $crm_data['archived_Archive_count'] = \App\Models\vehicleDetails::whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('vehicle_status', 'LIKE', 'Archive%') // Matches 'Archive', 'Archived', 'Archive123', etc.
                            ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                                return $query->where('companyName', $selectedCompanyId);
                            })->count();

                    } else {
                        // Fetch depot-wise counts for non-admin users
                        $crm_data['total_vehicle'] = vehicleDetails::whereIn('depot_id', $userDepots)
                            ->whereHas('types', function ($query) {
                                $query->where('company_status', 'Active');
                            })
                            ->count();

                        $crm_data['total_driver'] = \App\Models\Driver::whereIn('depot_id', $userDepots)->whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })
                            ->count();

                        $crm_data['total_work_around_stores'] = WorkAroundStore::whereIn('operating_centres', $userDepots)
                            ->whereHas('types', function ($query) {
                                $query->where('company_status', 'Active');
                            })
                            ->count();
                        $crm_data['total_pcns'] = Pcn::whereIn('depot_id', $userDepots)
                            ->whereHas('types', function ($query) {
                                $query->where('company_status', 'Active');
                            })
                            ->count();

                        $pcnCountsByMonth = Pcn::selectRaw('MONTH(created_at) as month, COUNT(*) as total')->whereIn('depot_id', $userDepots)
                            ->whereHas('types', function ($query) {
                                $query->where('company_status', 'Active');
                            })
                            ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                                return $query->where('company_id', $selectedCompanyId);
                            })
                            ->whereYear('created_at', now()->year)
                            ->groupBy(\DB::raw('MONTH(created_at)'))
                            ->pluck('total', 'month')
                            ->toArray();

                        // Ensure you have data for all months up to the current month
                        $currentMonth = now()->month;
                        $monthlyPCNs = [];
                        for ($i = 1; $i <= $currentMonth; $i++) {
                            $monthlyPCNs[] = $pcnCountsByMonth[$i] ?? 0;
                        }

                        // Pass this to your view
                        $crm_data['monthly_pcns'] = $monthlyPCNs;

                        $workaroundTotalCountsByMonth = \App\Models\WorkAroundStore::selectRaw('MONTH(created_at) as month, COUNT(*) as total')->whereIn('operating_centres', $userDepots)
                            ->whereHas('types', function ($query) {
                                $query->where('company_status', 'Active');
                            })
                            ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                                return $query->where('company_id', $selectedCompanyId);
                            })
                            ->whereYear('created_at', now()->year)
                            ->groupBy(\DB::raw('MONTH(created_at)'))
                            ->pluck('total', 'month')
                            ->toArray();

                        // Ensure you have data for all months up to the current month
                        $currentMonth = now()->month;
                        $monthlyWorkaround = [];
                        for ($i = 1; $i <= $currentMonth; $i++) {
                            $monthlyWorkaround[] = $workaroundTotalCountsByMonth[$i] ?? 0;
                        }

                        // Pass this to your view
                        $crm_data['monthly_total_workaround'] = $monthlyWorkaround;

                        $workaroundPendingCountsByMonth = WorkAroundStore::selectRaw('MONTH(created_at) as month, COUNT(*) as pending')->whereIn('operating_centres', $userDepots)
                            ->whereHas('types', function ($query) {
                                $query->where('company_status', 'Active');
                            })
                            ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                                return $query->where('company_id', $selectedCompanyId);
                            })
                            ->whereYear('created_at', now()->year)
                            ->whereNull('uploaded_date')
                            ->groupBy(\DB::raw('MONTH(created_at)'))
                            ->pluck('pending', 'month')
                            ->toArray();

                        // Ensure you have data for all months up to the current month
                        $monthlyPendingWorkaround = [];
                        for ($i = 1; $i <= $currentMonth; $i++) {
                            $monthlyPendingWorkaround[] = $workaroundPendingCountsByMonth[$i] ?? 0;
                        }

                        // Pass this to your view
                        $crm_data['monthly_pending_workaround'] = $monthlyPendingWorkaround;

                        $workaroundCompletedCountsByMonth = WorkAroundStore::selectRaw('MONTH(created_at) as month, COUNT(*) as completed')->whereIn('operating_centres', $userDepots)
                            ->whereHas('types', function ($query) {
                                $query->where('company_status', 'Active');
                            })
                            ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                                return $query->where('company_id', $selectedCompanyId);
                            })
                            ->whereYear('created_at', now()->year)
                            ->whereNotNull('uploaded_date')
                            ->groupBy(\DB::raw('MONTH(created_at)'))
                            ->pluck('completed', 'month')
                            ->toArray();

                        // Ensure you have data for all months up to the current month
                        $monthlyCompletedWorkaround = [];
                        for ($i = 1; $i <= $currentMonth; $i++) {
                            $monthlyCompletedWorkaround[] = $workaroundCompletedCountsByMonth[$i] ?? 0;
                        }

                        // Pass this to your view
                        $crm_data['monthly_completed_workaround'] = $monthlyCompletedWorkaround;

                        $closedPcnCountsByMonth = Pcn::selectRaw('MONTH(created_at) as month, COUNT(*) as closed')->whereIn('depot_id', $userDepots)
                            ->whereHas('types', function ($query) {
                                $query->where('company_status', 'Active');
                            })
                            ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                                return $query->where('company_id', $selectedCompanyId);
                            })
                            ->whereYear('created_at', now()->year)
                            ->where('status', 'Closed')
                            ->groupBy(\DB::raw('MONTH(created_at)'))
                            ->pluck('closed', 'month')
                            ->toArray();

                        // Ensure you have data for all months up to the current month
                        $monthlyClosedPCNs = [];
                        for ($i = 1; $i <= $currentMonth; $i++) {
                            $monthlyClosedPCNs[] = $closedPcnCountsByMonth[$i] ?? 0;
                        }

                        // Pass this to your view
                        $crm_data['monthly_closed_pcns'] = $monthlyClosedPCNs;

                        //outstandingpendingchart
                        $outstandingPcnCountsByMonth = Pcn::selectRaw('MONTH(created_at) as month, COUNT(*) as outstanding')->whereIn('depot_id', $userDepots)
                            ->whereHas('types', function ($query) {
                                $query->where('company_status', 'Active');
                            })
                            ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                                return $query->where('company_id', $selectedCompanyId);
                            })
                            ->whereYear('created_at', now()->year)
                            ->where('status', 'outstanding')
                            ->groupBy(\DB::raw('MONTH(created_at)'))
                            ->pluck('outstanding', 'month')
                            ->toArray();

                        // Ensure you have data for all months up to the current month
                        $monthlyOutstandingPCNs = [];
                        for ($i = 1; $i <= $currentMonth; $i++) {
                            $monthlyOutstandingPCNs[] = $outstandingPcnCountsByMonth[$i] ?? 0;
                        }

                        // Pass this to your view
                        $crm_data['monthly_outstanding_pcns'] = $monthlyOutstandingPCNs;

                        $statuses = ['Complete', 'Pending', 'Decline']; // Add more if needed

                        $monthlyData = [];

                        foreach ($statuses as $status) {
                            $counts = TrainingDriverAssign::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
                                ->whereHas('training.companies', function ($query) {
                                    $query->where('company_status', 'Active')->where('id', auth()->user()->companyname);
                                })
                                ->whereYear('created_at', now()->year)
                                ->where('status', $status)
                                ->groupBy(\DB::raw('MONTH(created_at)'))
                                ->pluck('count', 'month')
                                ->toArray();

                            // Fill months with zero if not available
                            $monthlyData[$status] = [];
                            for ($i = 1; $i <= $currentMonth; $i++) {
                                $monthlyData[$status][] = $counts[$i] ?? 0;
                            }
                        }

                        $crm_data['monthly_chart_data'] = $monthlyData;

                        $crm_data['total_tacho_status_valid'] = \App\Models\vehicleDetails::whereIn('depot_id', $userDepots)->whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('tacho_status', 'Valid')->count();
                        $crm_data['total_tacho_status_expiring_soon'] = \App\Models\vehicleDetails::whereIn('depot_id', $userDepots)->whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('tacho_status', 'EXPIRING SOON')->count();
                        $crm_data['total_tacho_status_expired'] = \App\Models\vehicleDetails::whereIn('depot_id', $userDepots)->whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('tacho_status', 'EXPIRED')->count();

                        $crm_data['total_dvs_pss_status_valid'] = \App\Models\vehicleDetails::whereIn('depot_id', $userDepots)->whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('dvs_pss_status', 'valid')->count();
                        $crm_data['total_dvs_pss_status_expiring_soon'] = \App\Models\vehicleDetails::whereIn('depot_id', $userDepots)->whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('dvs_pss_status', 'EXPIRING SOON')->count();
                        $crm_data['total_dvs_pss_status_expired'] = \App\Models\vehicleDetails::whereIn('depot_id', $userDepots)->whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('dvs_pss_status', 'EXPIRED')->count();

                        $crm_data['total_insurance_status_valid'] = \App\Models\vehicleDetails::whereIn('depot_id', $userDepots)->whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('insurance_status', 'Valid')->count();
                        $crm_data['total_insurance_status_expiring_soon'] = \App\Models\vehicleDetails::whereIn('depot_id', $userDepots)->whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('insurance_status', 'EXPIRING SOON')->count();
                        $crm_data['total_insurance_status_expired'] = \App\Models\vehicleDetails::whereIn('depot_id', $userDepots)->whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('insurance_status', 'EXPIRED')->count();

                        $crm_data['total_PMI_status_valid'] = \App\Models\vehicleDetails::whereIn('depot_id', $userDepots)->whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('PMI_status', 'Valid')->count();
                        $crm_data['total_PMI_status_expiring_soon'] = \App\Models\vehicleDetails::whereIn('depot_id', $userDepots)->whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('PMI_status', 'EXPIRING SOON')->count();
                        $crm_data['total_PMI_status_expired'] = \App\Models\vehicleDetails::whereIn('depot_id', $userDepots)->whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('PMI_status', 'EXPIRED')->count();

                        $crm_data['total_brake_test_status_valid'] = \App\Models\vehicleDetails::whereIn('depot_id', $userDepots)->whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('brake_test_status', 'Valid')->count();
                        $crm_data['total_brake_test_status_expiring_soon'] = \App\Models\vehicleDetails::whereIn('depot_id', $userDepots)->whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('brake_test_status', 'EXPIRING SOON')->count();
                        $crm_data['total_brake_test_status_expired'] = \App\Models\vehicleDetails::whereIn('depot_id', $userDepots)->whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('brake_test_status', 'EXPIRED')->count();

                        $crm_data['total_taxDueDate_status_valid'] = \App\Models\vehicleDetails::whereIn('depot_id', $userDepots)->whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('taxDueDate_status', 'Valid')->count();
                        $crm_data['total_taxDueDate_status_expiring_soon'] = \App\Models\vehicleDetails::whereIn('depot_id', $userDepots)->whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('taxDueDate_status', 'EXPIRING SOON')->count();
                        $crm_data['total_taxDueDate_status_expired'] = \App\Models\vehicleDetails::whereIn('depot_id', $userDepots)->whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('taxDueDate_status', 'EXPIRED')->count();

                        $crm_data['total_annual_test_status_expiring_soon'] = \App\Models\Vehicles::whereHas('vehicleDetails', function ($query) use ($userDepots) {
                            $query->whereIn('depot_id', $userDepots);
                            $query->whereHas('types', function ($subQuery) {
                                $subQuery->where('company_status', 'Active');
                            });
                        })->where('annual_test_status', 'EXPIRING SOON')->count();
                        $crm_data['total_annual_test_status_valid'] = \App\Models\Vehicles::whereHas('vehicleDetails', function ($query) use ($userDepots) {

                            $query->whereIn('depot_id', $userDepots);

                            // Ensure company is active
                            $query->whereHas('types', function ($subQuery) {
                                $subQuery->where('company_status', 'Active');
                            });
                        })->where('annual_test_status', 'Valid')->count();
                        $crm_data['total_annual_test_status_expired'] = \App\Models\Vehicles::whereHas('vehicleDetails', function ($query) use ($userDepots) {

                            $query->whereIn('depot_id', $userDepots);

                            // Ensure company is active
                            $query->whereHas('types', function ($subQuery) {
                                $subQuery->where('company_status', 'Active');
                            });
                        })->where('annual_test_status', 'EXPIRED')->count();

                        $crm_data['total_operating_centers'] = \App\Models\Depot::where('companyName', $selectedCompanyId)->whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->count();

                        $crm_data['status'] = \App\Models\Depot::where('companyName', $selectedCompanyId)->whereHas('types', function ($query) {
                            $query->where('company_status', 'Inactive');
                        })->count();

                        $crm_data['pcn_status'] = Pcn::where('status', 'Closed')->whereIn('depot_id', $userDepots)->whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->count();

                        $issuingAuthorityCounts = Pcn::select('issuing_authority', \DB::raw('COUNT(*) as total'))->whereIn('depot_id', $userDepots)
                            ->whereHas('types', function ($query) {
                                $query->where('company_status', 'Active');
                            })
                            ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                                return $query->where('company_id', $selectedCompanyId);
                            })
                            ->whereYear('created_at', now()->year)
                            ->groupBy('issuing_authority')
                            ->pluck('total', 'issuing_authority')
                            ->toArray();

                        // Normalize keys
                        $crm_data['pcn_issuing_counts'] = [
                            'DVSA' => $issuingAuthorityCounts['DVSA'] ?? 0,
                            'Local Council' => $issuingAuthorityCounts['Local Council'] ?? 0,
                            'Police' => $issuingAuthorityCounts['Police'] ?? 0,
                            'Other' => $issuingAuthorityCounts['Other'] ?? 0,
                        ];

                        $crm_data['total_driver'] = \App\Models\Driver::whereIn('depot_id', $userDepots)->whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->count();

                        $crm_data['training_complete_status'] = \App\Models\TrainingDriverAssign::whereHas('training.companies', function ($query) {
                            $query->where('company_status', 'Active')->where('id', auth()->user()->companyname);
                        })->where('status', 'Complete')->count();

                        $crm_data['training_pending_status'] = \App\Models\TrainingDriverAssign::whereHas('training.companies', function ($query) {
                            $query->where('company_status', 'Active')
                                ->where('id', auth()->user()->companyname); // filter by logged-in user's company
                        })->where('status', 'Pending')->count();

                        $crm_data['training_decline_status'] = \App\Models\TrainingDriverAssign::whereHas('training.companies', function ($query) {
                            $query->where('company_status', 'Active')->where('id', auth()->user()->companyname);
                        })->where('status', 'Decline')->count();

                        $crm_data['total_no_of_vehicles'] = \App\Models\Depot::where('companyName', $selectedCompanyId)->whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->sum('vehicles');

                        $crm_data['defects_count'] = \App\Models\WorkAroundStore::where('company_id', $selectedCompanyId)->whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->sum('defects_count');

                        $crm_data['rectified'] = \App\Models\WorkAroundStore::where('company_id', $selectedCompanyId)->whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->sum('rectified');

                        $crm_data['pending_uploaded_date'] = \App\Models\WorkAroundStore::whereNull('uploaded_date')->whereIN('operating_centres', $userDepots)->whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->count();

                        $crm_data['complated_uploaded_date'] = \App\Models\WorkAroundStore::whereNotNull('uploaded_date')->whereIN('operating_centres', $userDepots)->whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->count();

                        $forsBronzeIds = \App\Models\ForsBronze::pluck('id')->toArray();

                        $crm_data['policy_pending_status'] = \App\Models\PolicyAssignment::whereIn('policy_id', $forsBronzeIds)->whereHas('company', function ($query) {
                            $query->where('company_status', 'Active');
                        })->whereHas('driver', function ($query) use ($userDepots) {
                            $query->whereIn('depot_id', $userDepots);
                        })->where('status', 'Pending')->count();

                        $crm_data['policy_accept_status'] = \App\Models\PolicyAssignment::whereIn('policy_id', $forsBronzeIds)->whereHas('company', function ($query) {
                            $query->where('company_status', 'Active');
                        })->whereHas('driver', function ($query) use ($userDepots) {
                            $query->whereIn('depot_id', $userDepots);
                        })->where('status', 'Accept')->count();

                        $crm_data['policy_reassigned_status'] = \App\Models\PolicyAssignment::whereIn('policy_id', $forsBronzeIds)->whereHas('company', function ($query) {
                            $query->where('company_status', 'Active');
                        })->whereHas('driver', function ($query) use ($userDepots) {
                            $query->whereIn('depot_id', $userDepots);
                        })->where('status', 'Reassigned')->count();

                        // CPC and Tacho statuses, filtered for active companies

                        $crm_data['Inactive_status_count'] = \App\Models\Depot::whereIn('id', $userDepots)->whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('status', 'Inactive')->count();

                        $crm_data['active_depot_status_count'] = \App\Models\Depot::whereIn('id', $userDepots)->whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('status', 'Active')->count();

                        $crm_data['valid_cpc_count'] = \App\Models\Driver::where('driver_status', 'Active')->whereIn('depot_id', $userDepots)->whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('cpc_status', 'Valid')->count();

                        $crm_data['expiring_soon_cpc_count'] = \App\Models\Driver::where('driver_status', 'Active')->whereIn('depot_id', $userDepots)->whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('cpc_status', 'EXPIRING SOON')->count();

                        $crm_data['expired_cpc_count'] = \App\Models\Driver::where('driver_status', 'Active')->whereIn('depot_id', $userDepots)->whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('cpc_status', 'EXPIRED')->count();

                        $crm_data['expiring_soon_tacho_count'] = \App\Models\Driver::where('driver_status', 'Active')->whereIn('depot_id', $userDepots)->whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('tacho_card_status', 'EXPIRING SOON')->count();

                        $crm_data['expired_tacho_count'] = \App\Models\Driver::where('driver_status', 'Active')->whereIn('depot_id', $userDepots)->whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('tacho_card_status', 'EXPIRED')->count();

                        $crm_data['active_driver_count'] = \App\Models\Driver::whereIn('depot_id', $userDepots)->whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('driver_status', 'Active')->count();

                        $crm_data['inactive_driver_count'] = \App\Models\Driver::whereIn('depot_id', $userDepots)->whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('driver_status', 'InActive')->count();

                        $crm_data['archived_driver_count'] = \App\Models\Driver::whereIn('depot_id', $userDepots)->whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('driver_status', 'Archive')->count();

                        $crm_data['archived_Archive_count'] = \App\Models\vehicleDetails::whereIn('depot_id', $userDepots)->whereHas('types', function ($query) {
                            $query->where('company_status', 'Active');
                        })->where('vehicle_status', 'LIKE', 'Archive%')->count();
                    }

                    return view('dashboard.crm-dashboard', compact('crm_data', 'companies', 'selectedCompanyId', 'totalApiCallCount', 'selectedCompanyApiCallCount', 'selectedCompanyName'));
                } else {
                    return $this->account_dashboard_index();
                }

            }
        } else {
            if (! file_exists(storage_path().'/installed')) {
                header('location:install');
                exit;
            } else {
                $adminSettings = Utility::settings();
                if ($adminSettings['display_landing_page'] == 'on' && \Schema::hasTable('landing_page_settings')) {

                    return view('landingpage::layouts.landingpage', compact('adminSettings'));
                } else {
                    return redirect('login');
                }

            }
        }
    }

    public function project_dashboard_index()
    {
        $user = Auth::user();

        if (\Auth::user()->can('show project dashboard')) {
            if ($user->type == 'admin') {
                return view('admin.dashboard');
            } else {
                $home_data = [];
                //                dd($user->projects());

                $user_projects = $user->projects()->pluck('project_id')->toArray();

                $project_tasks = ProjectTask::whereIn('project_id', $user_projects)->get();
                $project_expense = Expense::whereIn('project_id', $user_projects)->get();
                $seven_days = Utility::getLastSevenDays();

                // Total Projects
                $complete_project = $user->projects()->where('status', 'LIKE', 'complete')->count();
                $home_data['total_project'] = [
                    'total' => count($user_projects),
                    'percentage' => Utility::getPercentage($complete_project, count($user_projects)),
                ];

                // Total Tasks
                $complete_task = ProjectTask::where('is_complete', '=', 1)->whereRaw("find_in_set('".$user->id."',assign_to)")->whereIn('project_id', $user_projects)->count();
                $home_data['total_task'] = [
                    'total' => $project_tasks->count(),
                    'percentage' => Utility::getPercentage($complete_task, $project_tasks->count()),
                ];

                // Total Expense
                $total_expense = 0;
                $total_project_amount = 0;
                foreach ($user->projects as $pr) {
                    $total_project_amount += $pr->budget;
                }
                foreach ($project_expense as $expense) {
                    $total_expense += $expense->amount;
                }
                $home_data['total_expense'] = [
                    'total' => $project_expense->count(),
                    'percentage' => Utility::getPercentage($total_expense, $total_project_amount),
                ];

                // Total Users
                $home_data['total_user'] = Auth::user()->contacts->count();

                // Tasks Overview Chart & Timesheet Log Chart
                $task_overview = [];
                $timesheet_logged = [];
                foreach ($seven_days as $date => $day) {
                    // Task
                    $task_overview[$day] = ProjectTask::where('is_complete', '=', 1)->where('marked_at', 'LIKE', $date)->whereIn('project_id', $user_projects)->count();

                    // Timesheet
                    $time = Timesheet::whereIn('project_id', $user_projects)->where('date', 'LIKE', $date)->pluck('time')->toArray();
                    $timesheet_logged[$day] = str_replace(':', '.', Utility::calculateTimesheetHours($time));
                }

                $home_data['task_overview'] = $task_overview;
                $home_data['timesheet_logged'] = $timesheet_logged;

                // Project Status
                $total_project = count($user_projects);

                $project_status = [];
                foreach (Project::$project_status as $k => $v) {

                    $project_status[$k]['total'] = $user->projects->where('status', 'LIKE', $k)->count();
                    //                    dd($project_status[$k]['total']    );
                    $project_status[$k]['percentage'] = Utility::getPercentage($project_status[$k]['total'], $total_project);
                }
                $home_data['project_status'] = $project_status;

                // Top Due Project
                $home_data['due_project'] = $user->projects()->orderBy('end_date', 'DESC')->limit(5)->get();

                // Top Due Tasks
                $home_data['due_tasks'] = ProjectTask::where('is_complete', '=', 0)->whereIn('project_id', $user_projects)->orderBy('end_date', 'DESC')->limit(5)->get();

                $home_data['last_tasks'] = ProjectTask::whereIn('project_id', $user_projects)->orderBy('end_date', 'DESC')->limit(5)->get();

                return view('dashboard.project-dashboard', compact('home_data'));
            }
        } else {

            return $this->account_dashboard_index();
        }
    }

    public function hrm_dashboard_index()
    {

        if (Auth::check()) {

            if (\Auth::user()->can('show hrm dashboard')) {

                $user = Auth::user();

                if ($user->type != 'client' && $user->type != 'company') {
                    $emp = Employee::where('user_id', '=', $user->id)->first();

                    $announcements = Announcement::orderBy('announcements.id', 'desc')->take(5)->leftjoin('announcement_employees', 'announcements.id', '=', 'announcement_employees.announcement_id')->where('announcement_employees.employee_id', '=', $emp->id)->orWhere(function ($q) {
                        $q->where('announcements.department_id', '["0"]')->where('announcements.employee_id', '["0"]');
                    })->get();

                    $employees = Employee::get();
                    $meetings = Meeting::orderBy('meetings.id', 'desc')->take(5)->leftjoin('meeting_employees', 'meetings.id', '=', 'meeting_employees.meeting_id')->where('meeting_employees.employee_id', '=', $emp->id)->orWhere(function ($q) {
                        $q->where('meetings.department_id', '["0"]')->where('meetings.employee_id', '["0"]');
                    })->get();
                    $events = Event::leftjoin('event_employees', 'events.id', '=', 'event_employees.event_id')->where('event_employees.employee_id', '=', $emp->id)->orWhere(function ($q) {
                        $q->where('events.department_id', '["0"]')->where('events.employee_id', '["0"]');
                    })->get();

                    $arrEvents = [];
                    foreach ($events as $event) {

                        $arr['id'] = $event['id'];
                        $arr['title'] = $event['title'];
                        $arr['start'] = $event['start_date'];
                        $arr['end'] = $event['end_date'];
                        $arr['backgroundColor'] = $event['color'];
                        $arr['borderColor'] = '#fff';
                        $arr['textColor'] = 'white';
                        $arrEvents[] = $arr;
                    }

                    $date = date('Y-m-d');
                    $time = date('H:i:s');
                    $employeeAttendance = AttendanceEmployee::orderBy('id', 'desc')->where('employee_id', '=', ! empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0)->where('date', '=', $date)->first();

                    $officeTime['startTime'] = Utility::getValByName('company_start_time');
                    $officeTime['endTime'] = Utility::getValByName('company_end_time');

                    return view('dashboard.dashboard', compact('arrEvents', 'announcements', 'employees', 'meetings', 'employeeAttendance', 'officeTime'));
                } elseif ($user->type == 'super admin') {
                    $user = \Auth::user();
                    $user['total_user'] = $user->countCompany();
                    $user['total_paid_user'] = $user->countPaidCompany();
                    $user['total_orders'] = Order::total_orders();
                    $user['total_orders_price'] = Order::total_orders_price();
                    $user['total_plan'] = Plan::total_plan();
                    $user['most_purchese_plan'] = (! empty(Plan::most_purchese_plan()) ? Plan::most_purchese_plan()->name : '');

                    $chartData = $this->getOrderChart(['duration' => 'week']);

                    return view('dashboard.super_admin', compact('user', 'chartData'));
                } else {
                    $events = Event::where('created_by', '=', \Auth::user()->creatorId())->get();
                    $arrEvents = [];

                    foreach ($events as $event) {
                        $arr['id'] = $event['id'];
                        $arr['title'] = $event['title'];
                        $arr['start'] = $event['start_date'];
                        $arr['end'] = $event['end_date'];

                        $arr['backgroundColor'] = $event['color'];
                        $arr['borderColor'] = '#fff';
                        $arr['textColor'] = 'white';
                        $arr['url'] = route('event.edit', $event['id']);

                        $arrEvents[] = $arr;
                    }

                    $announcements = Announcement::orderBy('announcements.id', 'desc')->take(5)->where('created_by', '=', \Auth::user()->creatorId())->get();

                    // $emp           = User::where('type', '!=', 'client')->where('type', '!=', 'company')->where('created_by', '=', \Auth::user()->creatorId())->get();
                    // $countEmployee = count($emp);

                    $user = User::where('type', '!=', 'client')->where('type', '!=', 'company')->where('created_by', '=', \Auth::user()->creatorId())->get();
                    $countUser = count($user);

                    $countTrainer = Trainer::where('created_by', '=', \Auth::user()->creatorId())->count();
                    $onGoingTraining = Training::where('status', '=', 1)->where('created_by', '=', \Auth::user()->creatorId())->count();
                    $doneTraining = Training::where('status', '=', 2)->where('created_by', '=', \Auth::user()->creatorId())->count();

                    $currentDate = date('Y-m-d');

                    $employees = User::where('type', '=', 'client')->where('created_by', '=', \Auth::user()->creatorId())->get();
                    $countClient = count($employees);
                    $notClockIn = AttendanceEmployee::where('date', '=', $currentDate)->get()->pluck('employee_id');

                    $notClockIns = Employee::where('created_by', '=', \Auth::user()->creatorId())->whereNotIn('id', $notClockIn)->get();
                    $activeJob = Job::where('status', 'active')->where('created_by', '=', \Auth::user()->creatorId())->count();
                    $inActiveJOb = Job::where('status', 'in_active')->where('created_by', '=', \Auth::user()->creatorId())->count();

                    $meetings = Meeting::where('created_by', '=', \Auth::user()->creatorId())->limit(5)->get();

                    return view('dashboard.dashboard', compact('arrEvents', 'onGoingTraining', 'activeJob', 'inActiveJOb', 'doneTraining', 'announcements', 'employees', 'meetings', 'countTrainer', 'countClient', 'countUser', 'notClockIns'));
                }
            } else {

                return $this->project_dashboard_index();
            }
        } else {
            if (! file_exists(storage_path().'/installed')) {
                header('location:install');
                exit;
            } else {
                $settings = Utility::settings();
                if ($settings['display_landing_page'] == 'on') {
                    $plans = Plan::get();

                    return view('layouts.landing', compact('plans'));
                } else {
                    return redirect('login');
                }

            }
        }
    }

    // public function crm_dashboard_index(Request $request)
    // {
    //     $user = Auth::user();

    //     if (\Auth::user()->can('show dashboard')) {

    //         $selectedCompanyId = $request->input('company_id');

    //         // Check the user's role
    //         $isAdminOrManager = $user->hasRole('company') || $user->hasRole('PTC manager');

    //         // For non-admin and non-PTC manager users, set the selected company to the user's company
    //         if (!$isAdminOrManager) {
    //             $selectedCompanyId = $user->companyname; // Assuming the user's company_id is stored in the users table
    //         }

    //         $totalApiCallCount = CompanyDetails::sum('api_call_count');

    //         // Get the list of companies for the dropdown, visible only to admin and PTC manager
    //       $companies = $isAdminOrManager
    //         ? CompanyDetails::where('created_by', \Auth::user()->creatorId())->where('company_status', 'Active')->orderBy('name', 'asc')->get()
    //         : CompanyDetails::where('id', $user->companyname)->where('company_status', 'Active')->orderBy('name', 'asc')->get();

    //         // Initialize API call count and name variables
    //         $selectedCompanyApiCallCount = 0;
    //         $selectedCompanyName = '';

    //         if ($selectedCompanyId) {
    //             // Fetch the selected company's API call count and name if a company is selected
    //             $selectedCompany = CompanyDetails::find($selectedCompanyId);
    //             $selectedCompanyApiCallCount = $selectedCompany ? $selectedCompany->api_call_count : 0;
    //             $selectedCompanyName = $selectedCompany ? $selectedCompany->name : '';
    //         } else {
    //             // If no company is selected and the user is an admin or PTC manager, sum up the API call count for all companies
    //             $selectedCompanyApiCallCount = $isAdminOrManager ? $totalApiCallCount : 0;
    //             $selectedCompanyName = $isAdminOrManager ? 'All Companies' : $user->companyName;
    //         }

    //         $crm_data = [];

    //         if ($selectedCompanyId) {
    //             // Fetch only active companies and filter related data
    //             $crm_data['total_company'] = CompanyDetails::where('id', $selectedCompanyId)
    //                 ->where('company_status', 'Active')
    //                 ->count();

    //             $crm_data['total_vehicle'] = vehicleDetails::where('companyName', $selectedCompanyId)
    //                 ->whereHas('types', function ($query) {
    //                     $query->where('company_status', 'Active');
    //                 })
    //                 ->count();

    //              $crm_data['total_tacho_status_expiring_soon'] = \App\Models\vehicleDetails::where('companyName', $selectedCompanyId)
    //                 ->whereHas('types', function ($query) {
    //                     $query->where('company_status', 'Active');
    //                 })
    //                  ->where('tacho_status', 'EXPIRING SOON')
    //                 ->count();

    //             $crm_data['total_tacho_status_expired'] = \App\Models\vehicleDetails::where('companyName', $selectedCompanyId)
    //                 ->whereHas('types', function ($query) {
    //                     $query->where('company_status', 'Active');
    //                 })
    //                 ->where('tacho_status', 'EXPIRED')
    //                 ->count();

    //                     $crm_data['total_dvs_pss_status_expiring_soon'] = \App\Models\vehicleDetails::where('companyName', $selectedCompanyId)
    //                     ->whereHas('types', function ($query) {
    //                         $query->where('company_status', 'Active');
    //                     })
    //                     ->where('dvs_pss_status', 'EXPIRING SOON')
    //                     ->count();

    //                     $crm_data['total_dvs_pss_status_expired'] = \App\Models\vehicleDetails::where('companyName', $selectedCompanyId)
    //                     ->whereHas('types', function ($query) {
    //                         $query->where('company_status', 'Active');
    //                     })
    //                     ->where('dvs_pss_status', 'EXPIRED')
    //                     ->count();

    //                     $crm_data['total_insurance_status_expiring_soon'] = \App\Models\vehicleDetails::where('companyName', $selectedCompanyId)
    //                     ->whereHas('types', function ($query) {
    //                         $query->where('company_status', 'Active');
    //                     })
    //                     ->where('insurance_status', 'EXPIRING SOON')
    //                     ->count();

    //                     $crm_data['total_insurance_status_expired'] = \App\Models\vehicleDetails::where('companyName', $selectedCompanyId)
    //                     ->whereHas('types', function ($query) {
    //                         $query->where('company_status', 'Active');
    //                     })
    //                     ->where('insurance_status', 'EXPIRED')
    //                     ->count();

    //                     $crm_data['total_PMI_status_expiring_soon'] = \App\Models\vehicleDetails::where('companyName', $selectedCompanyId)
    //                     ->whereHas('types', function ($query) {
    //                         $query->where('company_status', 'Active');
    //                     })
    //                     ->where('PMI_status', 'EXPIRING SOON')
    //                     ->count();

    //                     $crm_data['total_PMI_status_expired'] = \App\Models\vehicleDetails::where('companyName', $selectedCompanyId)
    //                     ->whereHas('types', function ($query) {
    //                         $query->where('company_status', 'Active');
    //                     })
    //                     ->where('PMI_status', 'EXPIRED')
    //                     ->count();

    //                     $crm_data['total_brake_test_status_expiring_soon'] = \App\Models\vehicleDetails::where('companyName', $selectedCompanyId)
    //                     ->whereHas('types', function ($query) {
    //                         $query->where('company_status', 'Active');
    //                     })
    //                     ->where('brake_test_status', 'EXPIRING SOON')
    //                     ->count();

    //                     $crm_data['total_brake_test_status_expired'] = \App\Models\vehicleDetails::where('companyName', $selectedCompanyId)
    //                     ->whereHas('types', function ($query) {
    //                         $query->where('company_status', 'Active');
    //                     })
    //                     ->where('brake_test_status', 'EXPIRED')
    //                     ->count();

    //                     $crm_data['total_taxDueDate_status_expiring_soon'] = \App\Models\vehicleDetails::where('companyName', $selectedCompanyId)
    //                     ->whereHas('types', function ($query) {
    //                         $query->where('company_status', 'Active');
    //                     })
    //                     ->where('taxDueDate_status', 'EXPIRING SOON')
    //                     ->count();

    //                     $crm_data['total_taxDueDate_status_expired'] = \App\Models\vehicleDetails::where('companyName', $selectedCompanyId)
    //                     ->whereHas('types', function ($query) {
    //                         $query->where('company_status', 'Active');
    //                     })
    //                     ->where('taxDueDate_status', 'EXPIRED')
    //                     ->count();

    //                     $crm_data['total_annual_test_status_expiring_soon'] = \App\Models\Vehicles::where('companyName', $selectedCompanyId)
    //                     ->whereHas('types', function ($query) {
    //                         $query->where('company_status', 'Active');
    //                     })
    //                     ->where('annual_test_status', 'EXPIRING SOON')
    //                     ->count();

    //                     $crm_data['total_annual_test_status_expired'] = \App\Models\Vehicles::where('companyName', $selectedCompanyId)
    //                     ->whereHas('types', function ($query) {
    //                         $query->where('company_status', 'Active');
    //                     })
    //                     ->where('annual_test_status', 'EXPIRED')
    //                     ->count();

    //                     $crm_data['total_operating_centers'] = \App\Models\Depot::where('companyName', $selectedCompanyId)
    //                     ->whereHas('types', function ($query) {
    //                         $query->where('company_status', 'Active');
    //                     })
    //                     ->count();

    //                 $crm_data['total_driver'] = \App\Models\Driver::where('companyName', $selectedCompanyId)
    //                     ->whereHas('types', function ($query) {
    //                         $query->where('company_status', 'Active');
    //                     })
    //                     ->count();

    //             $crm_data['total_no_of_vehicles'] = \App\Models\Depot::where('companyName', $selectedCompanyId)
    //                 ->whereHas('types', function ($query) {
    //                     $query->where('company_status', 'Active');
    //                 })
    //                 ->sum('vehicles');

    //             // CPC and Tacho statuses, filtered for active companies
    //             $crm_data['expiring_soon_cpc_count'] = \App\Models\Driver::where('companyName', $selectedCompanyId)
    //                 ->whereHas('types', function ($query) {
    //                     $query->where('company_status', 'Active');
    //                 })
    //                 ->where('cpc_status', 'EXPIRING SOON')
    //                 ->count();

    //             $crm_data['expired_cpc_count'] = \App\Models\Driver::where('companyName', $selectedCompanyId)
    //                 ->whereHas('types', function ($query) {
    //                     $query->where('company_status', 'Active');
    //                 })
    //                 ->where('cpc_status', 'EXPIRED')
    //                 ->count();

    //             $crm_data['expiring_soon_tacho_count'] = \App\Models\Driver::where('companyName', $selectedCompanyId)
    //                 ->whereHas('types', function ($query) {
    //                     $query->where('company_status', 'Active');
    //                 })
    //                 ->where('tacho_card_status', 'EXPIRING SOON')
    //                 ->count();

    //             $crm_data['expired_tacho_count'] = \App\Models\Driver::where('companyName', $selectedCompanyId)
    //                 ->whereHas('types', function ($query) {
    //                     $query->where('company_status', 'Active');
    //                 })
    //                 ->where('tacho_card_status', 'EXPIRED')
    //                 ->count();

    //             $crm_data['active_driver_count'] = \App\Models\Driver::where('companyName', $selectedCompanyId)
    //                 ->whereHas('types', function ($query) {
    //                     $query->where('company_status', 'Active');
    //                 })
    //                 ->where('driver_status', 'Active')
    //                 ->count();

    //             $crm_data['inactive_driver_count'] = \App\Models\Driver::where('companyName', $selectedCompanyId)
    //                 ->whereHas('types', function ($query) {
    //                     $query->where('company_status', 'Active');
    //                 })
    //                 ->where('driver_status', 'InActive')
    //                 ->count();

    //             $crm_data['archived_driver_count'] = \App\Models\Driver::where('companyName', $selectedCompanyId)
    //                 ->whereHas('types', function ($query) {
    //                     $query->where('company_status', 'Active');
    //                 })
    //                 ->where('driver_status', 'Archive')
    //                 ->count();
    //         } else if ($isAdminOrManager) {
    //             // Aggregate data for all active companies
    //             $crm_data['total_company'] = CompanyDetails::where('company_status', 'Active')->count();

    //             $crm_data['total_vehicle'] = vehicleDetails::whereHas('types', function ($query) {
    //                 $query->where('company_status', 'Active');
    //             })->count();

    //                 $crm_data['total_tacho_status_expiring_soon'] = \App\Models\vehicleDetails::whereHas('types', function ($query) {
    //                     $query->where('company_status', 'Active');
    //                 })->where('tacho_status', 'EXPIRING SOON')->count();
    //                 $crm_data['total_tacho_status_expired'] = \App\Models\vehicleDetails::whereHas('types', function ($query) {
    //                     $query->where('company_status', 'Active');
    //                 })->where('tacho_status', 'EXPIRED')->count();

    //                 $crm_data['total_dvs_pss_status_expiring_soon'] = \App\Models\vehicleDetails::whereHas('types', function ($query) {
    //                     $query->where('company_status', 'Active');
    //                 })->where('dvs_pss_status', 'EXPIRING SOON')->count();
    //                 $crm_data['total_dvs_pss_status_expired'] = \App\Models\vehicleDetails::whereHas('types', function ($query) {
    //                     $query->where('company_status', 'Active');
    //                 })->where('dvs_pss_status', 'EXPIRED')->count();

    //                 $crm_data['total_insurance_status_expiring_soon'] = \App\Models\vehicleDetails::whereHas('types', function ($query) {
    //                     $query->where('company_status', 'Active');
    //                 })->where('insurance_status', 'EXPIRING SOON')->count();
    //                 $crm_data['total_insurance_status_expired'] = \App\Models\vehicleDetails::whereHas('types', function ($query) {
    //                     $query->where('company_status', 'Active');
    //                 })->where('insurance_status', 'EXPIRED')->count();

    //                 $crm_data['total_PMI_status_expiring_soon'] = \App\Models\vehicleDetails::whereHas('types', function ($query) {
    //                     $query->where('company_status', 'Active');
    //                 })->where('PMI_status', 'EXPIRING SOON')->count();
    //                 $crm_data['total_PMI_status_expired'] = \App\Models\vehicleDetails::whereHas('types', function ($query) {
    //                     $query->where('company_status', 'Active');
    //                 })->where('PMI_status', 'EXPIRED')->count();

    //                 $crm_data['total_brake_test_status_expiring_soon'] = \App\Models\vehicleDetails::whereHas('types', function ($query) {
    //                     $query->where('company_status', 'Active');
    //                 })->where('brake_test_status', 'EXPIRING SOON')->count();
    //                 $crm_data['total_brake_test_status_expired'] = \App\Models\vehicleDetails::whereHas('types', function ($query) {
    //                     $query->where('company_status', 'Active');
    //                 })->where('brake_test_status', 'EXPIRED')->count();

    //                 $crm_data['total_taxDueDate_status_expiring_soon'] = \App\Models\vehicleDetails::whereHas('types', function ($query) {
    //                     $query->where('company_status', 'Active');
    //                 })->where('taxDueDate_status', 'EXPIRING SOON')->count();
    //                 $crm_data['total_taxDueDate_status_expired'] = \App\Models\vehicleDetails::whereHas('types', function ($query) {
    //                     $query->where('company_status', 'Active');
    //                 })->where('taxDueDate_status', 'EXPIRED')->count();

    //                 $crm_data['total_annual_test_status_expiring_soon'] = \App\Models\Vehicles::whereHas('types', function ($query) {
    //                     $query->where('company_status', 'Active');
    //                 })->where('annual_test_status', 'EXPIRING SOON')->count();
    //                 $crm_data['total_annual_test_status_expired'] = \App\Models\Vehicles::whereHas('types', function ($query) {
    //                     $query->where('company_status', 'Active');
    //                 })->where('annual_test_status', 'EXPIRED')->count();

    //             $crm_data['total_operating_centers'] = \App\Models\Depot::whereHas('types', function ($query) {
    //                 $query->where('company_status', 'Active');
    //             })->count();

    //             $crm_data['total_driver'] = \App\Models\Driver::whereHas('types', function ($query) {
    //                 $query->where('company_status', 'Active');
    //             })->count();

    //             $crm_data['total_no_of_vehicles'] = \App\Models\Depot::whereHas('types', function ($query) {
    //                 $query->where('company_status', 'Active');
    //             })->sum('vehicles');

    //             // CPC and Tacho statuses, filtered for active companies
    //             $crm_data['expiring_soon_cpc_count'] = \App\Models\Driver::whereHas('types', function ($query) {
    //                 $query->where('company_status', 'Active');
    //             })->where('cpc_status', 'EXPIRING SOON')->count();

    //             $crm_data['expired_cpc_count'] = \App\Models\Driver::whereHas('types', function ($query) {
    //                 $query->where('company_status', 'Active');
    //             })->where('cpc_status', 'EXPIRED')->count();

    //             $crm_data['expiring_soon_tacho_count'] = \App\Models\Driver::whereHas('types', function ($query) {
    //                 $query->where('company_status', 'Active');
    //             })->where('tacho_card_status', 'EXPIRING SOON')->count();

    //             $crm_data['expired_tacho_count'] = \App\Models\Driver::whereHas('types', function ($query) {
    //                 $query->where('company_status', 'Active');
    //             })->where('tacho_card_status', 'EXPIRED')->count();

    //             $crm_data['active_driver_count'] = \App\Models\Driver::whereHas('types', function ($query) {
    //                 $query->where('company_status', 'Active');
    //             })->where('driver_status', 'Active')->count();

    //             $crm_data['inactive_driver_count'] = \App\Models\Driver::whereHas('types', function ($query) {
    //                 $query->where('company_status', 'Active');
    //             })->where('driver_status', 'InActive')->count();

    //             $crm_data['archived_driver_count'] = \App\Models\Driver::whereHas('types', function ($query) {
    //                 $query->where('company_status', 'Active');
    //             })->where('driver_status', 'Archive')->count();
    //         }

    //         return view('dashboard.crm-dashboard', compact('crm_data', 'companies', 'selectedCompanyId', 'totalApiCallCount', 'selectedCompanyApiCallCount', 'selectedCompanyName'));
    //     } else {
    //         return $this->account_dashboard_index();
    //     }
    // }

    public function crm_dashboard_index(Request $request)
    {
        $user = Auth::user();

        if (\Auth::user()->can('show dashboard')) {

            $selectedCompanyId = $request->input('company_id');

            // Check the user's role
            $isAdminOrManager = $user->hasRole('company') || $user->hasRole('PTC manager');
            $userDepots = ! $isAdminOrManager ? json_decode($user->depot_id, true) : [];
            $userVehicleGroups = ! $isAdminOrManager ? json_decode($user->vehicle_group_id, true) : [];
            $userDriverGroups = ! $isAdminOrManager ? json_decode($user->driver_group_id, true) : [];

            // For non-admin and non-PTC manager users, set the selected company to the user's company
            if (! $isAdminOrManager) {
                $selectedCompanyId = $user->companyname; // Assuming the user's company_id is stored in the users table
            }

            $totalApiCallCount = CompanyDetails::sum('api_call_count');

            // Get the list of companies for the dropdown, visible only to admin and PTC manager
            $companies = $isAdminOrManager
             ? CompanyDetails::where('created_by', \Auth::user()->creatorId())->where('company_status', 'Active')->orderBy('name', 'asc')->get()
             : CompanyDetails::where('id', $user->companyname)->where('company_status', 'Active')->orderBy('name', 'asc')->get();

            // Initialize API call count and name variables
            $selectedCompanyApiCallCount = 0;
            $selectedCompanyName = '';

            if ($selectedCompanyId) {
                // Fetch the selected company's API call count and name if a company is selected
                $selectedCompany = CompanyDetails::find($selectedCompanyId);
                $selectedCompanyApiCallCount = $selectedCompany ? $selectedCompany->api_call_count : 0;
                $selectedCompanyName = $selectedCompany ? $selectedCompany->name : '';
            } else {
                // If no company is selected and the user is an admin or PTC manager, sum up the API call count for all companies
                $selectedCompanyApiCallCount = $isAdminOrManager ? $totalApiCallCount : 0;
                $selectedCompanyName = $isAdminOrManager ? 'All Companies' : $user->companyName;
            }

            $crm_data = [];

            if ($isAdminOrManager) {
                // Aggregate data for all active companies
                $crm_data['total_company'] = CompanyDetails::where('company_status', 'Active')->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                    return $query->where('id', $selectedCompanyId);
                })->count();

                $crm_data['total_vehicle'] = vehicleDetails::whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                    return $query->where('companyName', $selectedCompanyId);
                })->count();

                $crm_data['total_work_around_stores'] = WorkAroundStore::whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                    return $query->where('company_id', $selectedCompanyId);
                })->count();

                $crm_data['pending_uploaded_date'] = WorkAroundStore::whereNull('uploaded_date')->whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                    return $query->where('company_id', $selectedCompanyId);
                })->count();

                $crm_data['complated_uploaded_date'] = WorkAroundStore::whereNotNull('uploaded_date')->whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                    return $query->where('company_id', $selectedCompanyId);
                })->count();

                $crm_data['total_pcns'] = Pcn::whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                    return $query->where('company_id', $selectedCompanyId);
                })->count();

                $issuingAuthorityCounts = Pcn::select('issuing_authority', \DB::raw('COUNT(*) as total'))
                    ->whereHas('types', function ($query) {
                        $query->where('company_status', 'Active');
                    })
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('company_id', $selectedCompanyId);
                    })
                    ->whereYear('created_at', now()->year)
                    ->groupBy('issuing_authority')
                    ->pluck('total', 'issuing_authority')
                    ->toArray();

                // Normalize keys
                $crm_data['pcn_issuing_counts'] = [
                    'DVSA' => $issuingAuthorityCounts['DVSA'] ?? 0,
                    'Local Council' => $issuingAuthorityCounts['Local Council'] ?? 0,
                    'Police' => $issuingAuthorityCounts['Police'] ?? 0,
                    'Other' => $issuingAuthorityCounts['Other'] ?? 0,
                ];

                $workaroundTotalCountsByMonth = \App\Models\WorkAroundStore::selectRaw('MONTH(created_at) as month, COUNT(*) as total')
                    ->whereHas('types', function ($query) {
                        $query->where('company_status', 'Active');
                    })
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('company_id', $selectedCompanyId);
                    })
                    ->whereYear('created_at', now()->year)
                    ->groupBy(\DB::raw('MONTH(created_at)'))
                    ->pluck('total', 'month')
                    ->toArray();

                // Ensure you have data for all months up to the current month
                $currentMonth = now()->month;
                $monthlyWorkaround = [];
                for ($i = 1; $i <= $currentMonth; $i++) {
                    $monthlyWorkaround[] = $workaroundTotalCountsByMonth[$i] ?? 0;
                }

                // Pass this to your view
                $crm_data['monthly_total_workaround'] = $monthlyWorkaround;

                $workaroundPendingCountsByMonth = WorkAroundStore::selectRaw('MONTH(created_at) as month, COUNT(*) as pending')
                    ->whereHas('types', function ($query) {
                        $query->where('company_status', 'Active');
                    })
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('company_id', $selectedCompanyId);
                    })
                    ->whereYear('created_at', now()->year)
                    ->whereNull('uploaded_date')
                    ->groupBy(\DB::raw('MONTH(created_at)'))
                    ->pluck('pending', 'month')
                    ->toArray();

                // Ensure you have data for all months up to the current month
                $monthlyPendingWorkaround = [];
                for ($i = 1; $i <= $currentMonth; $i++) {
                    $monthlyPendingWorkaround[] = $workaroundPendingCountsByMonth[$i] ?? 0;
                }

                // Pass this to your view
                $crm_data['monthly_pending_workaround'] = $monthlyPendingWorkaround;

                $workaroundCompletedCountsByMonth = WorkAroundStore::selectRaw('MONTH(created_at) as month, COUNT(*) as completed')
                    ->whereHas('types', function ($query) {
                        $query->where('company_status', 'Active');
                    })
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('company_id', $selectedCompanyId);
                    })
                    ->whereYear('created_at', now()->year)
                    ->whereNotNull('uploaded_date')
                    ->groupBy(\DB::raw('MONTH(created_at)'))
                    ->pluck('completed', 'month')
                    ->toArray();

                // Ensure you have data for all months up to the current month
                $monthlyCompletedWorkaround = [];
                for ($i = 1; $i <= $currentMonth; $i++) {
                    $monthlyCompletedWorkaround[] = $workaroundCompletedCountsByMonth[$i] ?? 0;
                }

                // Pass this to your view
                $crm_data['monthly_completed_workaround'] = $monthlyCompletedWorkaround;

                //TOTALPCNSCOUNT
                $pcnCountsByMonth = Pcn::selectRaw('MONTH(created_at) as month, COUNT(*) as total')
                    ->whereHas('types', function ($query) {
                        $query->where('company_status', 'Active');
                    })
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('company_id', $selectedCompanyId);
                    })
                    ->whereYear('created_at', now()->year)
                    ->groupBy(\DB::raw('MONTH(created_at)'))
                    ->pluck('total', 'month')
                    ->toArray();

                // Ensure you have data for all months up to the current month
                $currentMonth = now()->month;
                $monthlyPCNs = [];
                for ($i = 1; $i <= $currentMonth; $i++) {
                    $monthlyPCNs[] = $pcnCountsByMonth[$i] ?? 0;
                }

                // Pass this to your view
                $crm_data['monthly_pcns'] = $monthlyPCNs;

                //colsechart
                $closedPcnCountsByMonth = Pcn::selectRaw('MONTH(created_at) as month, COUNT(*) as closed')
                    ->whereHas('types', function ($query) {
                        $query->where('company_status', 'Active');
                    })
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('company_id', $selectedCompanyId);
                    })
                    ->whereYear('created_at', now()->year)
                    ->where('status', 'Closed')
                    ->groupBy(\DB::raw('MONTH(created_at)'))
                    ->pluck('closed', 'month')
                    ->toArray();

                // Ensure you have data for all months up to the current month
                $monthlyClosedPCNs = [];
                for ($i = 1; $i <= $currentMonth; $i++) {
                    $monthlyClosedPCNs[] = $closedPcnCountsByMonth[$i] ?? 0;
                }

                // Pass this to your view
                $crm_data['monthly_closed_pcns'] = $monthlyClosedPCNs;

                //outstandingpendingchart
                $outstandingPcnCountsByMonth = Pcn::selectRaw('MONTH(created_at) as month, COUNT(*) as outstanding')
                    ->whereHas('types', function ($query) {
                        $query->where('company_status', 'Active');
                    })
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('company_id', $selectedCompanyId);
                    })
                    ->whereYear('created_at', now()->year)
                    ->where('status', 'outstanding')
                    ->groupBy(\DB::raw('MONTH(created_at)'))
                    ->pluck('outstanding', 'month')
                    ->toArray();

                // Ensure you have data for all months up to the current month
                $monthlyOutstandingPCNs = [];
                for ($i = 1; $i <= $currentMonth; $i++) {
                    $monthlyOutstandingPCNs[] = $outstandingPcnCountsByMonth[$i] ?? 0;
                }

                // Pass this to your view
                $crm_data['monthly_outstanding_pcns'] = $monthlyOutstandingPCNs;

                $currentMonth = now()->month;

                //Driver Trinning chart

                $statuses = ['Complete', 'Pending', 'Decline']; // Add more if needed

                $monthlyData = [];

                foreach ($statuses as $status) {
                    $counts = TrainingDriverAssign::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
                        ->whereHas('training.companies', function ($query) {
                            $query->where('company_status', 'Active');
                        })
                        ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                            return $query->whereHas('training', function ($q) use ($selectedCompanyId) {
                                $q->where('companyName', $selectedCompanyId);
                            });
                        })
                        ->whereYear('created_at', now()->year)
                        ->where('status', $status)
                        ->groupBy(\DB::raw('MONTH(created_at)'))
                        ->pluck('count', 'month')
                        ->toArray();

                    // Fill months with zero if not available
                    $monthlyData[$status] = [];
                    for ($i = 1; $i <= $currentMonth; $i++) {
                        $monthlyData[$status][] = $counts[$i] ?? 0;
                    }
                }

                $crm_data['monthly_chart_data'] = $monthlyData;

                $crm_data['pcn_status'] = Pcn::where('status', 'Closed')->whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                    return $query->where('company_id', $selectedCompanyId);
                })->count();

                $crm_data['policy_pending_status'] = \App\Models\PolicyAssignment::whereHas('company', function ($query) {
                    $query->where('company_status', 'Active');
                })->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                    return $query->where('company_id', $selectedCompanyId);
                })->where('status', 'Pending')->count();

                $crm_data['policy_accept_status'] = \App\Models\PolicyAssignment::whereHas('company', function ($query) {
                    $query->where('company_status', 'Active');
                })->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                    return $query->where('company_id', $selectedCompanyId);
                })->where('status', 'Accept')->count();

                $crm_data['policy_reassigned_status'] = \App\Models\PolicyAssignment::whereHas('company', function ($query) {
                    $query->where('company_status', 'Active');
                })->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                    return $query->where('company_id', $selectedCompanyId);
                })->where('status', 'Reassigned')->count();

                $crm_data['total_tacho_status_valid'] = \App\Models\vehicleDetails::whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('tacho_status', 'Valid')
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('companyName', $selectedCompanyId);
                    })->count();
                $crm_data['total_tacho_status_expiring_soon'] = \App\Models\vehicleDetails::whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('tacho_status', 'EXPIRING SOON')
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('companyName', $selectedCompanyId);
                    })->count();
                $crm_data['total_tacho_status_expired'] = \App\Models\vehicleDetails::whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('tacho_status', 'EXPIRED')
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('companyName', $selectedCompanyId);
                    })->count();

                $crm_data['total_dvs_pss_status_valid'] = \App\Models\vehicleDetails::whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('dvs_pss_status', 'Valid')
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('companyName', $selectedCompanyId);
                    })->count();
                $crm_data['total_dvs_pss_status_expiring_soon'] = \App\Models\vehicleDetails::whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('dvs_pss_status', 'EXPIRING SOON')
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('companyName', $selectedCompanyId);
                    })->count();
                $crm_data['total_dvs_pss_status_expired'] = \App\Models\vehicleDetails::whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('dvs_pss_status', 'EXPIRED')
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('companyName', $selectedCompanyId);
                    })->count();

                $crm_data['total_insurance_status_valid'] = \App\Models\vehicleDetails::whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('insurance_status', 'Valid')
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('companyName', $selectedCompanyId);
                    })->count();
                $crm_data['total_insurance_status_expiring_soon'] = \App\Models\vehicleDetails::whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('insurance_status', 'EXPIRING SOON')
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('companyName', $selectedCompanyId);
                    })->count();
                $crm_data['total_insurance_status_expired'] = \App\Models\vehicleDetails::whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('insurance_status', 'EXPIRED')
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('companyName', $selectedCompanyId);
                    })->count();

                $crm_data['total_PMI_status_valid'] = \App\Models\vehicleDetails::whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('PMI_status', 'Valid')
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('companyName', $selectedCompanyId);
                    })->count();
                $crm_data['total_PMI_status_expiring_soon'] = \App\Models\vehicleDetails::whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('PMI_status', 'EXPIRING SOON')
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('companyName', $selectedCompanyId);
                    })->count();
                $crm_data['total_PMI_status_expired'] = \App\Models\vehicleDetails::whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('PMI_status', 'EXPIRED')
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('companyName', $selectedCompanyId);
                    })->count();

                $crm_data['total_brake_test_status_valid'] = \App\Models\vehicleDetails::whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('brake_test_status', 'Valid')
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('companyName', $selectedCompanyId);
                    })->count();
                $crm_data['total_brake_test_status_expiring_soon'] = \App\Models\vehicleDetails::whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('brake_test_status', 'EXPIRING SOON')
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('companyName', $selectedCompanyId);
                    })->count();
                $crm_data['total_brake_test_status_expired'] = \App\Models\vehicleDetails::whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('brake_test_status', 'EXPIRED')
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('companyName', $selectedCompanyId);
                    })->count();

                $crm_data['total_taxDueDate_status_valid'] = \App\Models\vehicleDetails::whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('taxDueDate_status', 'Valid')
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('companyName', $selectedCompanyId);
                    })->count();

                $crm_data['total_taxDueDate_status_expiring_soon'] = \App\Models\vehicleDetails::whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('taxDueDate_status', 'EXPIRING SOON')
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('companyName', $selectedCompanyId);
                    })->count();
                $crm_data['total_taxDueDate_status_expired'] = \App\Models\vehicleDetails::whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('taxDueDate_status', 'EXPIRED')
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('companyName', $selectedCompanyId);
                    })->count();

                $crm_data['total_annual_test_status_valid'] = \App\Models\Vehicles::whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('annual_test_status', 'Valid')
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('companyName', $selectedCompanyId);
                    })->count();
                $crm_data['total_annual_test_status_expiring_soon'] = \App\Models\Vehicles::whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('annual_test_status', 'EXPIRING SOON')
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('companyName', $selectedCompanyId);
                    })->count();
                $crm_data['total_annual_test_status_expired'] = \App\Models\Vehicles::whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('annual_test_status', 'EXPIRED')
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('companyName', $selectedCompanyId);
                    })->count();

                $crm_data['total_operating_centers'] = \App\Models\Depot::whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                    return $query->where('companyName', $selectedCompanyId);
                })->count();

                $crm_data['status'] = \App\Models\Depot::whereHas('types', function ($query) {
                    $query->where('company_status', 'Inactive');
                })->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                    return $query->where('companyName', $selectedCompanyId);
                })->count();

                $crm_data['total_driver'] = \App\Models\Driver::whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                    return $query->where('companyName', $selectedCompanyId);
                })->count();

                $crm_data['training_complete_status'] = \App\Models\TrainingDriverAssign::whereHas('training.companies', function ($query) {
                    $query->where('company_status', 'Active');
                })->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                    return $query->whereHas('training', function ($q) use ($selectedCompanyId) {
                        $q->where('companyName', $selectedCompanyId);
                    });
                })->where('status', 'Complete')->count();

                $crm_data['training_pending_status'] = \App\Models\TrainingDriverAssign::whereHas('training.companies', function ($query) {
                    $query->where('company_status', 'Active');
                })->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                    return $query->whereHas('training', function ($q) use ($selectedCompanyId) {
                        $q->where('companyName', $selectedCompanyId);
                    });
                })->where('status', 'Pending')->count();

                $crm_data['training_decline_status'] = \App\Models\TrainingDriverAssign::whereHas('training.companies', function ($query) {
                    $query->where('company_status', 'Active');
                })->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                    return $query->whereHas('training', function ($q) use ($selectedCompanyId) {
                        $q->where('companyName', $selectedCompanyId);
                    });
                })->where('status', 'Decline')->count();

                $crm_data['total_no_of_vehicles'] = \App\Models\Depot::whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                    return $query->where('companyName', $selectedCompanyId);
                })->sum('vehicles');

                $crm_data['defects_count'] = \App\Models\WorkAroundStore::whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                    return $query->where('company_id', $selectedCompanyId);
                })->sum('defects_count');

                $crm_data['rectified'] = \App\Models\WorkAroundStore::whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                    return $query->where('company_id', $selectedCompanyId);
                })->sum('rectified');

                // CPC and Tacho statuses, filtered for active companies

                $crm_data['Inactive_status_count'] = \App\Models\Depot::whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('status', 'Inactive')
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('companyName', $selectedCompanyId);
                    })->count();

                $crm_data['active_depot_status_count'] = \App\Models\Depot::whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('status', 'Active')
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('companyName', $selectedCompanyId);
                    })->count();

                $crm_data['valid_cpc_count'] = \App\Models\Driver::where('driver_status', 'Active')->whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('cpc_status', 'Valid')
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('companyName', $selectedCompanyId);
                    })->count();

                $crm_data['expiring_soon_cpc_count'] = \App\Models\Driver::where('driver_status', 'Active')->whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('cpc_status', 'EXPIRING SOON')
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('companyName', $selectedCompanyId);
                    })->count();

                $crm_data['expired_cpc_count'] = \App\Models\Driver::where('driver_status', 'Active')->whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('cpc_status', 'EXPIRED')
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('companyName', $selectedCompanyId);
                    })->count();

                $crm_data['expiring_soon_tacho_count'] = \App\Models\Driver::where('driver_status', 'Active')->whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('tacho_card_status', 'EXPIRING SOON')
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('companyName', $selectedCompanyId);
                    })->count();

                $crm_data['expired_tacho_count'] = \App\Models\Driver::where('driver_status', 'Active')->whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('tacho_card_status', 'EXPIRED')
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('companyName', $selectedCompanyId);
                    })->count();

                $crm_data['active_driver_count'] = \App\Models\Driver::whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('driver_status', 'Active')
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('companyName', $selectedCompanyId);
                    })->count();

                $crm_data['inactive_driver_count'] = \App\Models\Driver::whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('driver_status', 'InActive')
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('companyName', $selectedCompanyId);
                    })->count();

                $crm_data['archived_driver_count'] = \App\Models\Driver::whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('driver_status', 'Archive')
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('companyName', $selectedCompanyId);
                    })->count();

                $crm_data['archived_Archive_count'] = \App\Models\vehicleDetails::whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('vehicle_status', 'LIKE', 'Archive%') // Matches 'Archive', 'Archived', 'Archive123', etc.
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('companyName', $selectedCompanyId);
                    })->count();

            } else {
                                                $validDriverIds = \App\Models\Driver::whereIn('depot_id', $userDepots)
    ->when(!empty($userDriverGroups), function ($query) use ($userDriverGroups) {
        $query->whereIn('group_id', $userDriverGroups);
    })
    ->pluck('id')
    ->toArray();

$crm_data['driver_api_log_count'] = \App\Models\DriverAPILog::whereIn('driver_id', $validDriverIds)

    // ✅ company filter
    ->where('companyName', $selectedCompanyId)

    // ✅ depot filter (IMPORTANT 🔥)
    ->whereHas('drivers', function ($q) use ($userDepots) {
        $q->whereIn('depot_id', $userDepots);
    })

    ->count();
    
                // Fetch depot-wise counts for non-admin users
                $crm_data['total_vehicle'] = vehicleDetails::whereIn('depot_id', $userDepots)
                    ->whereHas('types', function ($query) {
                        $query->where('company_status', 'Active');
                    })
                    ->when(! empty($userVehicleGroups), function ($query) use ($userVehicleGroups) {
                        $query->whereIn('group_id', $userVehicleGroups);
                    })->count();

                $crm_data['total_driver'] = \App\Models\Driver::whereIn('depot_id', $userDepots)->whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })

                    ->when(! empty($userDriverGroups), function ($query) use ($userDriverGroups) {
                        $query->whereIn('group_id', $userDriverGroups); // or driver_group_id check karo
                })
                    ->count();

                $crm_data['total_work_around_stores'] = WorkAroundStore::whereIn('operating_centres', $userDepots)->when(! empty($userDriverGroups), function ($query) use ($userDriverGroups) {
                    $query->whereHas('driver', function ($q) use ($userDriverGroups) {
                        $q->whereIn('group_id', $userDriverGroups);
                    });
                })->when(! empty($userVehicleGroups), function ($query) use ($userVehicleGroups) {
                    $query->whereHas('vehicle.vehicleDetail', function ($q) use ($userVehicleGroups) {
                        $q->whereIn('group_id', $userVehicleGroups);
                    });
                })
                    ->whereHas('types', function ($query) {
                        $query->where('company_status', 'Active');
                    })
                    ->count();
                $crm_data['total_pcns'] = Pcn::whereIn('depot_id', $userDepots)
                    ->whereHas('types', function ($query) {
                        $query->where('company_status', 'Active');
                    })
                    ->when(! empty($userVehicleGroups), function ($query) use ($userVehicleGroups) {
                        $query->whereHas('vehicle.vehicleDetail', function ($q) use ($userVehicleGroups) {
                            $q->whereIn('group_id', $userVehicleGroups);
                        });
                    })
                    ->count();

                $pcnCountsByMonth = Pcn::selectRaw('MONTH(created_at) as month, COUNT(*) as total')->whereIn('depot_id', $userDepots)
                    ->whereHas('types', function ($query) {
                        $query->where('company_status', 'Active');
                    })
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('company_id', $selectedCompanyId);
                    })
                    ->when(! empty($userVehicleGroups), function ($query) use ($userVehicleGroups) {
                        $query->whereHas('vehicle.vehicleDetail', function ($q) use ($userVehicleGroups) {
                            $q->whereIn('group_id', $userVehicleGroups);
                        });
                    })

                    ->whereYear('created_at', now()->year)
                    ->groupBy(\DB::raw('MONTH(created_at)'))
                    ->pluck('total', 'month')
                    ->toArray();

                // Ensure you have data for all months up to the current month
                $currentMonth = now()->month;
                $monthlyPCNs = [];
                for ($i = 1; $i <= $currentMonth; $i++) {
                    $monthlyPCNs[] = $pcnCountsByMonth[$i] ?? 0;
                }

                // Pass this to your view
                $crm_data['monthly_pcns'] = $monthlyPCNs;

                $workaroundTotalCountsByMonth = \App\Models\WorkAroundStore::selectRaw('MONTH(created_at) as month, COUNT(*) as total')->whereIn('operating_centres', $userDepots)
                    ->whereHas('types', function ($query) {
                        $query->where('company_status', 'Active');
                    })
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('company_id', $selectedCompanyId);
                    })
                    ->when(! empty($userDriverGroups), function ($query) use ($userDriverGroups) {
                        $query->whereHas('driver', function ($q) use ($userDriverGroups) {
                            $q->whereIn('group_id', $userDriverGroups);
                        });
                    })
                    ->when(! empty($userVehicleGroups), function ($query) use ($userVehicleGroups) {
                        $query->whereHas('vehicle.vehicleDetail', function ($q) use ($userVehicleGroups) {
                            $q->whereIn('group_id', $userVehicleGroups);
                        });
                    })

                    ->whereYear('created_at', now()->year)
                    ->groupBy(\DB::raw('MONTH(created_at)'))
                    ->pluck('total', 'month')
                    ->toArray();

                // Ensure you have data for all months up to the current month
                $currentMonth = now()->month;
                $monthlyWorkaround = [];
                for ($i = 1; $i <= $currentMonth; $i++) {
                    $monthlyWorkaround[] = $workaroundTotalCountsByMonth[$i] ?? 0;
                }

                // Pass this to your view
                $crm_data['monthly_total_workaround'] = $monthlyWorkaround;

                $workaroundPendingCountsByMonth = WorkAroundStore::selectRaw('MONTH(created_at) as month, COUNT(*) as pending')->whereIn('operating_centres', $userDepots)
                    ->whereHas('types', function ($query) {
                        $query->where('company_status', 'Active');
                    })
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('company_id', $selectedCompanyId);
                    })
                    ->when(! empty($userDriverGroups), function ($query) use ($userDriverGroups) {
                        $query->whereHas('driver', function ($q) use ($userDriverGroups) {
                            $q->whereIn('group_id', $userDriverGroups);
                        });
                    })->when(! empty($userVehicleGroups), function ($query) use ($userVehicleGroups) {
                        $query->whereHas('vehicle.vehicleDetail', function ($q) use ($userVehicleGroups) {
                            $q->whereIn('group_id', $userVehicleGroups);
                        });
                    })

                    ->whereYear('created_at', now()->year)
                    ->whereNull('uploaded_date')
                    ->groupBy(\DB::raw('MONTH(created_at)'))
                    ->pluck('pending', 'month')
                    ->toArray();

                // Ensure you have data for all months up to the current month
                $monthlyPendingWorkaround = [];
                for ($i = 1; $i <= $currentMonth; $i++) {
                    $monthlyPendingWorkaround[] = $workaroundPendingCountsByMonth[$i] ?? 0;
                }

                // Pass this to your view
                $crm_data['monthly_pending_workaround'] = $monthlyPendingWorkaround;

                $workaroundCompletedCountsByMonth = WorkAroundStore::selectRaw('MONTH(created_at) as month, COUNT(*) as completed')->whereIn('operating_centres', $userDepots)
                    ->whereHas('types', function ($query) {
                        $query->where('company_status', 'Active');
                    })

                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('company_id', $selectedCompanyId);
                    })
                    ->when(! empty($userDriverGroups), function ($query) use ($userDriverGroups) {
                        $query->whereHas('driver', function ($q) use ($userDriverGroups) {
                            $q->whereIn('group_id', $userDriverGroups);
                        });
                    })->when(! empty($userVehicleGroups), function ($query) use ($userVehicleGroups) {
                        $query->whereHas('vehicle.vehicleDetail', function ($q) use ($userVehicleGroups) {
                            $q->whereIn('group_id', $userVehicleGroups);
                        });
                    })
                    ->whereYear('created_at', now()->year)
                    ->whereNotNull('uploaded_date')
                    ->groupBy(\DB::raw('MONTH(created_at)'))
                    ->pluck('completed', 'month')
                    ->toArray();

                // Ensure you have data for all months up to the current month
                $monthlyCompletedWorkaround = [];
                for ($i = 1; $i <= $currentMonth; $i++) {
                    $monthlyCompletedWorkaround[] = $workaroundCompletedCountsByMonth[$i] ?? 0;
                }

                // Pass this to your view
                $crm_data['monthly_completed_workaround'] = $monthlyCompletedWorkaround;

                $closedPcnCountsByMonth = Pcn::selectRaw('MONTH(created_at) as month, COUNT(*) as closed')->whereIn('depot_id', $userDepots)
                    ->whereHas('types', function ($query) {
                        $query->where('company_status', 'Active');
                    })
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('company_id', $selectedCompanyId);
                    })
                    ->when(! empty($userVehicleGroups), function ($query) use ($userVehicleGroups) {
                        $query->whereHas('vehicle.vehicleDetail', function ($q) use ($userVehicleGroups) {
                            $q->whereIn('group_id', $userVehicleGroups);
                        });
                    })
                    ->whereYear('created_at', now()->year)
                    ->where('status', 'Closed')
                    ->groupBy(\DB::raw('MONTH(created_at)'))
                    ->pluck('closed', 'month')
                    ->toArray();

                // Ensure you have data for all months up to the current month
                $monthlyClosedPCNs = [];
                for ($i = 1; $i <= $currentMonth; $i++) {
                    $monthlyClosedPCNs[] = $closedPcnCountsByMonth[$i] ?? 0;
                }

                // Pass this to your view
                $crm_data['monthly_closed_pcns'] = $monthlyClosedPCNs;

                //outstandingpendingchart
                $outstandingPcnCountsByMonth = Pcn::selectRaw('MONTH(created_at) as month, COUNT(*) as outstanding')->whereIn('depot_id', $userDepots)
                    ->whereHas('types', function ($query) {
                        $query->where('company_status', 'Active');
                    })
                    ->when(! empty($userVehicleGroups), function ($query) use ($userVehicleGroups) {
                        $query->whereHas('vehicle.vehicleDetail', function ($q) use ($userVehicleGroups) {
                            $q->whereIn('group_id', $userVehicleGroups);
                        });
                    })
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('company_id', $selectedCompanyId);
                    })
                    ->whereYear('created_at', now()->year)
                    ->where('status', 'outstanding')
                    ->groupBy(\DB::raw('MONTH(created_at)'))
                    ->pluck('outstanding', 'month')
                    ->toArray();

                // Ensure you have data for all months up to the current month
                $monthlyOutstandingPCNs = [];
                for ($i = 1; $i <= $currentMonth; $i++) {
                    $monthlyOutstandingPCNs[] = $outstandingPcnCountsByMonth[$i] ?? 0;
                }

                // Pass this to your view
                $crm_data['monthly_outstanding_pcns'] = $monthlyOutstandingPCNs;

                $statuses = ['Complete', 'Pending', 'Decline']; // Add more if needed

                $monthlyData = [];

                foreach ($statuses as $status) {
                    $counts = TrainingDriverAssign::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
                        ->whereHas('training.companies', function ($query) {
                            $query->where('company_status', 'Active')->where('id', auth()->user()->companyname);
                        })
                        ->whereHas('driver', function ($query) use ($userDepots, $userDriverGroups) {

                            if (! empty($userDepots)) {
                                $query->whereIn('depot_id', $userDepots);
                            }

                            if (! empty($userDriverGroups)) {
                                $query->whereIn('group_id', $userDriverGroups); // check column name
                            }
                        })
                        ->whereYear('created_at', now()->year)
                        ->where('status', $status)
                        ->groupBy(\DB::raw('MONTH(created_at)'))
                        ->pluck('count', 'month')
                        ->toArray();

                    // Fill months with zero if not available
                    $monthlyData[$status] = [];
                    for ($i = 1; $i <= $currentMonth; $i++) {
                        $monthlyData[$status][] = $counts[$i] ?? 0;
                    }
                }

                $crm_data['monthly_chart_data'] = $monthlyData;

                $crm_data['total_tacho_status_valid'] = \App\Models\vehicleDetails::whereIn('depot_id', $userDepots)
                    ->when(! empty($userVehicleGroups), function ($query) use ($userVehicleGroups) {
                        $query->whereIn('group_id', $userVehicleGroups);
                    })
                    ->whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('tacho_status', 'Valid')->count();
                $crm_data['total_tacho_status_expiring_soon'] = \App\Models\vehicleDetails::whereIn('depot_id', $userDepots)->when(! empty($userVehicleGroups), function ($query) use ($userVehicleGroups) {
                    $query->whereIn('group_id', $userVehicleGroups);
                })->whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('tacho_status', 'EXPIRING SOON')->count();
                $crm_data['total_tacho_status_expired'] = \App\Models\vehicleDetails::whereIn('depot_id', $userDepots)->when(! empty($userVehicleGroups), function ($query) use ($userVehicleGroups) {
                    $query->whereIn('group_id', $userVehicleGroups);
                })->whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('tacho_status', 'EXPIRED')->count();

                $crm_data['total_dvs_pss_status_valid'] = \App\Models\vehicleDetails::whereIn('depot_id', $userDepots)->when(! empty($userVehicleGroups), function ($query) use ($userVehicleGroups) {
                    $query->whereIn('group_id', $userVehicleGroups);
                })->whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('dvs_pss_status', 'valid')->count();
                $crm_data['total_dvs_pss_status_expiring_soon'] = \App\Models\vehicleDetails::whereIn('depot_id', $userDepots)->when(! empty($userVehicleGroups), function ($query) use ($userVehicleGroups) {
                    $query->whereIn('group_id', $userVehicleGroups);
                })->whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('dvs_pss_status', 'EXPIRING SOON')->count();
                $crm_data['total_dvs_pss_status_expired'] = \App\Models\vehicleDetails::whereIn('depot_id', $userDepots)->when(! empty($userVehicleGroups), function ($query) use ($userVehicleGroups) {
                    $query->whereIn('group_id', $userVehicleGroups);
                })->whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('dvs_pss_status', 'EXPIRED')->count();

                $crm_data['total_insurance_status_valid'] = \App\Models\vehicleDetails::whereIn('depot_id', $userDepots)->when(! empty($userVehicleGroups), function ($query) use ($userVehicleGroups) {
                    $query->whereIn('group_id', $userVehicleGroups);
                })->whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('insurance_status', 'Valid')->count();
                $crm_data['total_insurance_status_expiring_soon'] = \App\Models\vehicleDetails::whereIn('depot_id', $userDepots)->when(! empty($userVehicleGroups), function ($query) use ($userVehicleGroups) {
                    $query->whereIn('group_id', $userVehicleGroups);
                })->whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('insurance_status', 'EXPIRING SOON')->count();
                $crm_data['total_insurance_status_expired'] = \App\Models\vehicleDetails::whereIn('depot_id', $userDepots)->when(! empty($userVehicleGroups), function ($query) use ($userVehicleGroups) {
                    $query->whereIn('group_id', $userVehicleGroups);
                })->whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('insurance_status', 'EXPIRED')->count();

                $crm_data['total_PMI_status_valid'] = \App\Models\vehicleDetails::whereIn('depot_id', $userDepots)->when(! empty($userVehicleGroups), function ($query) use ($userVehicleGroups) {
                    $query->whereIn('group_id', $userVehicleGroups);
                })->whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('PMI_status', 'Valid')->count();
                $crm_data['total_PMI_status_expiring_soon'] = \App\Models\vehicleDetails::whereIn('depot_id', $userDepots)->when(! empty($userVehicleGroups), function ($query) use ($userVehicleGroups) {
                    $query->whereIn('group_id', $userVehicleGroups);
                })->whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('PMI_status', 'EXPIRING SOON')->count();
                $crm_data['total_PMI_status_expired'] = \App\Models\vehicleDetails::whereIn('depot_id', $userDepots)->when(! empty($userVehicleGroups), function ($query) use ($userVehicleGroups) {
                    $query->whereIn('group_id', $userVehicleGroups);
                })->whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('PMI_status', 'EXPIRED')->count();

                $crm_data['total_brake_test_status_valid'] = \App\Models\vehicleDetails::whereIn('depot_id', $userDepots)->when(! empty($userVehicleGroups), function ($query) use ($userVehicleGroups) {
                    $query->whereIn('group_id', $userVehicleGroups);
                })->whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('brake_test_status', 'Valid')->count();
                $crm_data['total_brake_test_status_expiring_soon'] = \App\Models\vehicleDetails::whereIn('depot_id', $userDepots)->when(! empty($userVehicleGroups), function ($query) use ($userVehicleGroups) {
                    $query->whereIn('group_id', $userVehicleGroups);
                })->whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('brake_test_status', 'EXPIRING SOON')->count();
                $crm_data['total_brake_test_status_expired'] = \App\Models\vehicleDetails::whereIn('depot_id', $userDepots)->when(! empty($userVehicleGroups), function ($query) use ($userVehicleGroups) {
                    $query->whereIn('group_id', $userVehicleGroups);
                })->whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('brake_test_status', 'EXPIRED')->count();

                $crm_data['total_taxDueDate_status_valid'] = \App\Models\vehicleDetails::whereIn('depot_id', $userDepots)->when(! empty($userVehicleGroups), function ($query) use ($userVehicleGroups) {
                    $query->whereIn('group_id', $userVehicleGroups);
                })->whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('taxDueDate_status', 'Valid')->count();
                $crm_data['total_taxDueDate_status_expiring_soon'] = \App\Models\vehicleDetails::whereIn('depot_id', $userDepots)->when(! empty($userVehicleGroups), function ($query) use ($userVehicleGroups) {
                    $query->whereIn('group_id', $userVehicleGroups);
                })->whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('taxDueDate_status', 'EXPIRING SOON')->count();
                $crm_data['total_taxDueDate_status_expired'] = \App\Models\vehicleDetails::whereIn('depot_id', $userDepots)->when(! empty($userVehicleGroups), function ($query) use ($userVehicleGroups) {
                    $query->whereIn('group_id', $userVehicleGroups);
                })->whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('taxDueDate_status', 'EXPIRED')->count();

                $crm_data['total_annual_test_status_expiring_soon'] = \App\Models\Vehicles::whereHas('vehicleDetails', function ($query) use ($userDepots, $userVehicleGroups) {
                    $query->whereIn('depot_id', $userDepots);
                    if (! empty($userVehicleGroups)) {
                        $query->whereIn('group_id', $userVehicleGroups);
                    }

                    $query->whereHas('types', function ($subQuery) {
                        $subQuery->where('company_status', 'Active');
                    });
                })->where('annual_test_status', 'EXPIRING SOON')->count();
                $crm_data['total_annual_test_status_valid'] = \App\Models\Vehicles::whereHas('vehicleDetails', function ($query) use ($userDepots, $userVehicleGroups) {

                    $query->whereIn('depot_id', $userDepots);
                    if (! empty($userVehicleGroups)) {
                        $query->whereIn('group_id', $userVehicleGroups);
                    }

                    // Ensure company is active
                    $query->whereHas('types', function ($subQuery) {
                        $subQuery->where('company_status', 'Active');
                    });
                })->where('annual_test_status', 'Valid')->count();
                $crm_data['total_annual_test_status_expired'] = \App\Models\Vehicles::whereHas('vehicleDetails', function ($query) use ($userDepots, $userVehicleGroups) {

                    $query->whereIn('depot_id', $userDepots);
                    if (! empty($userVehicleGroups)) {
                        $query->whereIn('group_id', $userVehicleGroups);
                    }

                    // Ensure company is active
                    $query->whereHas('types', function ($subQuery) {
                        $subQuery->where('company_status', 'Active');
                    });
                })->where('annual_test_status', 'EXPIRED')->count();

                $crm_data['total_operating_centers'] = \App\Models\Depot::whereIn('id', $userDepots)->where('companyName', $selectedCompanyId)->whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->count();

                $crm_data['status'] = \App\Models\Depot::whereIn('id', $userDepots)->where('companyName', $selectedCompanyId)->whereHas('types', function ($query) {
                    $query->where('company_status', 'Inactive');
                })->count();

                $crm_data['pcn_status'] = Pcn::where('status', 'Closed')->whereIn('depot_id', $userDepots)->when(! empty($userVehicleGroups), function ($query) use ($userVehicleGroups) {
                    $query->whereHas('vehicle.vehicleDetail', function ($q) use ($userVehicleGroups) {
                        $q->whereIn('group_id', $userVehicleGroups);
                    });
                })->whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->count();

                $issuingAuthorityCounts = Pcn::select('issuing_authority', \DB::raw('COUNT(*) as total'))->whereIn('depot_id', $userDepots)
                    ->when(! empty($userVehicleGroups), function ($query) use ($userVehicleGroups) {
                        $query->whereHas('vehicle.vehicleDetail', function ($q) use ($userVehicleGroups) {
                            $q->whereIn('group_id', $userVehicleGroups);
                        });
                    })
                    ->whereHas('types', function ($query) {
                        $query->where('company_status', 'Active');
                    })
                    ->when($selectedCompanyId, function ($query) use ($selectedCompanyId) {
                        return $query->where('company_id', $selectedCompanyId);
                    })
                    ->whereYear('created_at', now()->year)
                    ->groupBy('issuing_authority')
                    ->pluck('total', 'issuing_authority')
                    ->toArray();

                // Normalize keys
                $crm_data['pcn_issuing_counts'] = [
                    'DVSA' => $issuingAuthorityCounts['DVSA'] ?? 0,
                    'Local Council' => $issuingAuthorityCounts['Local Council'] ?? 0,
                    'Police' => $issuingAuthorityCounts['Police'] ?? 0,
                    'Other' => $issuingAuthorityCounts['Other'] ?? 0,
                ];

                $crm_data['total_driver'] = \App\Models\Driver::whereIn('depot_id', $userDepots)->when(! empty($userDriverGroups), function ($query) use ($userDriverGroups) {
                    $query->whereIn('group_id', $userDriverGroups); // or driver_group_id
                })->whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->count();

                $crm_data['training_complete_status'] = \App\Models\TrainingDriverAssign::whereHas('training.companies', function ($query) {
                    $query->where('company_status', 'Active')->where('id', auth()->user()->companyname);
                })->where('status', 'Complete')
                    ->whereHas('driver', function ($query) use ($userDepots, $userDriverGroups) {

                        if (! empty($userDepots)) {
                            $query->whereIn('depot_id', $userDepots);
                        }

                        if (! empty($userDriverGroups)) {
                            $query->whereIn('group_id', $userDriverGroups); // or driver_group_id
                        }
                    })->count();

                $crm_data['training_pending_status'] = \App\Models\TrainingDriverAssign::whereHas('training.companies', function ($query) {
                    $query->where('company_status', 'Active')
                        ->where('id', auth()->user()->companyname); // filter by logged-in user's company
                })->where('status', 'Pending')
                    ->whereHas('driver', function ($query) use ($userDepots, $userDriverGroups) {

                        if (! empty($userDepots)) {
                            $query->whereIn('depot_id', $userDepots);
                        }

                        if (! empty($userDriverGroups)) {
                            $query->whereIn('group_id', $userDriverGroups); // or driver_group_id
                        }
                    })->count();

                $crm_data['training_decline_status'] = \App\Models\TrainingDriverAssign::whereHas('training.companies', function ($query) {
                    $query->where('company_status', 'Active')->where('id', auth()->user()->companyname);
                })->where('status', 'Decline')
                    ->whereHas('driver', function ($query) use ($userDepots, $userDriverGroups) {

                        if (! empty($userDepots)) {
                            $query->whereIn('depot_id', $userDepots);
                        }

                        if (! empty($userDriverGroups)) {
                            $query->whereIn('group_id', $userDriverGroups); // or driver_group_id
                        }
                    })->count();

                $crm_data['total_no_of_vehicles'] = \App\Models\Depot::whereIn('id', $userDepots)->where('companyName', $selectedCompanyId)->whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->sum('vehicles');

                $crm_data['defects_count'] = \App\Models\WorkAroundStore::where('company_id', $selectedCompanyId)->whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->whereIn('operating_centres', $userDepots)->when(! empty($userDriverGroups), function ($query) use ($userDriverGroups) {
                    $query->whereHas('driver', function ($q) use ($userDriverGroups) {
                        $q->whereIn('group_id', $userDriverGroups);
                    });
                })->when(! empty($userVehicleGroups), function ($query) use ($userVehicleGroups) {
                    $query->whereHas('vehicle.vehicleDetail', function ($q) use ($userVehicleGroups) {
                        $q->whereIn('group_id', $userVehicleGroups);
                    });
                })->sum('defects_count');

                $crm_data['rectified'] = \App\Models\WorkAroundStore::where('company_id', $selectedCompanyId)->whereIn('operating_centres', $userDepots)->when(! empty($userDriverGroups), function ($query) use ($userDriverGroups) {
                    $query->whereHas('driver', function ($q) use ($userDriverGroups) {
                        $q->whereIn('group_id', $userDriverGroups);
                    });
                })->whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->when(! empty($userVehicleGroups), function ($query) use ($userVehicleGroups) {
                    $query->whereHas('vehicle.vehicleDetail', function ($q) use ($userVehicleGroups) {
                        $q->whereIn('group_id', $userVehicleGroups);
                    });
                })->sum('rectified');

                $crm_data['pending_uploaded_date'] = \App\Models\WorkAroundStore::whereNull('uploaded_date')->whereIN('operating_centres', $userDepots)->when(! empty($userDriverGroups), function ($query) use ($userDriverGroups) {
                    $query->whereHas('driver', function ($q) use ($userDriverGroups) {
                        $q->whereIn('group_id', $userDriverGroups);
                    });
                })->whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->when(! empty($userVehicleGroups), function ($query) use ($userVehicleGroups) {
                    $query->whereHas('vehicle.vehicleDetail', function ($q) use ($userVehicleGroups) {
                        $q->whereIn('group_id', $userVehicleGroups);
                    });
                })->count();

                $crm_data['complated_uploaded_date'] = \App\Models\WorkAroundStore::whereNotNull('uploaded_date')->whereIN('operating_centres', $userDepots)->when(! empty($userDriverGroups), function ($query) use ($userDriverGroups) {
                    $query->whereHas('driver', function ($q) use ($userDriverGroups) {
                        $q->whereIn('group_id', $userDriverGroups);
                    });
                })->whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->when(! empty($userVehicleGroups), function ($query) use ($userVehicleGroups) {
                    $query->whereHas('vehicle.vehicleDetail', function ($q) use ($userVehicleGroups) {
                        $q->whereIn('group_id', $userVehicleGroups);
                    });
                })->count();

                $forsBronzeIds = \App\Models\ForsBronze::pluck('id')->toArray();

                $crm_data['policy_pending_status'] = \App\Models\PolicyAssignment::whereIn('policy_id', $forsBronzeIds)->whereHas('company', function ($query) {
                    $query->where('company_status', 'Active');
                })->whereHas('driver', function ($query) use ($userDepots, $userDriverGroups) {

                    if (! empty($userDepots)) {
                    $query->whereIn('depot_id', $userDepots);
                    }

                    if (! empty($userDriverGroups)) {
                        $query->whereIn('group_id', $userDriverGroups); // or driver_group_id
                    }
                })->where('status', 'Pending')->count();

                $crm_data['policy_accept_status'] = \App\Models\PolicyAssignment::whereIn('policy_id', $forsBronzeIds)->whereHas('company', function ($query) {
                    $query->where('company_status', 'Active');
                })->whereHas('driver', function ($query) use ($userDepots, $userDriverGroups) {

                    if (! empty($userDepots)) {
                    $query->whereIn('depot_id', $userDepots);
                    }

                    if (! empty($userDriverGroups)) {
                        $query->whereIn('group_id', $userDriverGroups); // or driver_group_id
                    }
                })->where('status', 'Accept')->count();

                $crm_data['policy_reassigned_status'] = \App\Models\PolicyAssignment::whereIn('policy_id', $forsBronzeIds)->whereHas('company', function ($query) {
                    $query->where('company_status', 'Active');
                })->whereHas('driver', function ($query) use ($userDepots, $userDriverGroups) {

                    if (! empty($userDepots)) {
                    $query->whereIn('depot_id', $userDepots);
                    }

                    if (! empty($userDriverGroups)) {
                        $query->whereIn('group_id', $userDriverGroups); // or driver_group_id
                    }
                })->where('status', 'Reassigned')->count();

                // CPC and Tacho statuses, filtered for active companies

                $crm_data['Inactive_status_count'] = \App\Models\Depot::whereIn('id', $userDepots)->whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('status', 'Inactive')->count();

                $crm_data['active_depot_status_count'] = \App\Models\Depot::whereIn('id', $userDepots)->whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('status', 'Active')->count();

                $crm_data['valid_cpc_count'] = \App\Models\Driver::where('driver_status', 'Active')->whereIn('depot_id', $userDepots)->when(! empty($userDriverGroups), function ($query) use ($userDriverGroups) {
                    $query->whereIn('group_id', $userDriverGroups); // or driver_group_id
                })->whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('cpc_status', 'Valid')->count();

                $crm_data['expiring_soon_cpc_count'] = \App\Models\Driver::where('driver_status', 'Active')->whereIn('depot_id', $userDepots)->when(! empty($userDriverGroups), function ($query) use ($userDriverGroups) {
                    $query->whereIn('group_id', $userDriverGroups); // or driver_group_id
                })->whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('cpc_status', 'EXPIRING SOON')->count();

                $crm_data['expired_cpc_count'] = \App\Models\Driver::where('driver_status', 'Active')->whereIn('depot_id', $userDepots)->when(! empty($userDriverGroups), function ($query) use ($userDriverGroups) {
                    $query->whereIn('group_id', $userDriverGroups); // or driver_group_id
                })->whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('cpc_status', 'EXPIRED')->count();

                $crm_data['expiring_soon_tacho_count'] = \App\Models\Driver::where('driver_status', 'Active')->whereIn('depot_id', $userDepots)->when(! empty($userDriverGroups), function ($query) use ($userDriverGroups) {
                    $query->whereIn('group_id', $userDriverGroups); // or driver_group_id
                })->whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('tacho_card_status', 'EXPIRING SOON')->count();

                $crm_data['expired_tacho_count'] = \App\Models\Driver::where('driver_status', 'Active')->whereIn('depot_id', $userDepots)->when(! empty($userDriverGroups), function ($query) use ($userDriverGroups) {
                    $query->whereIn('group_id', $userDriverGroups); // or driver_group_id
                })->whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('tacho_card_status', 'EXPIRED')->count();

                $crm_data['active_driver_count'] = \App\Models\Driver::whereIn('depot_id', $userDepots)->whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('driver_status', 'Active')->when(! empty($userDriverGroups), function ($query) use ($userDriverGroups) {
                    $query->whereIn('group_id', $userDriverGroups); // or driver_group_id
                })->count();

                $crm_data['inactive_driver_count'] = \App\Models\Driver::whereIn('depot_id', $userDepots)->when(! empty($userDriverGroups), function ($query) use ($userDriverGroups) {
                    $query->whereIn('group_id', $userDriverGroups); // or driver_group_id
                })->whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('driver_status', 'InActive')->count();

                $crm_data['archived_driver_count'] = \App\Models\Driver::whereIn('depot_id', $userDepots)->when(! empty($userDriverGroups), function ($query) use ($userDriverGroups) {
                    $query->whereIn('group_id', $userDriverGroups); // or driver_group_id
                })->whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('driver_status', 'Archive')->count();

                $crm_data['archived_Archive_count'] = \App\Models\vehicleDetails::whereIn('depot_id', $userDepots)->when(! empty($userVehicleGroups), function ($query) use ($userVehicleGroups) {
                    $query->whereIn('group_id', $userVehicleGroups);
                })->whereHas('types', function ($query) {
                    $query->where('company_status', 'Active');
                })->where('vehicle_status', 'LIKE', 'Archive%')->count();
            }

            return view('dashboard.crm-dashboard', compact('crm_data', 'companies', 'selectedCompanyId', 'totalApiCallCount', 'selectedCompanyApiCallCount', 'selectedCompanyName'));
        } else {
            return $this->account_dashboard_index();
        }
    }

    public function driver_dashboard_index()
    {
        $user = Auth::user();
        if (\Auth::user()->can('show driver dashboard')) {
            if ($user->type == 'admin') {
                return view('admin.dashboard');
            } else {
                $crm_data = [];

                $leads = Lead::where('created_by', \Auth::user()->creatorId())->get();
                $deals = Deal::where('created_by', \Auth::user()->creatorId())->get();

                //count data
                $crm_data['total_leads'] = $total_leads = count($leads);
                $crm_data['total_deals'] = $total_deals = count($deals);
                $crm_data['total_contracts'] = Contract::where('created_by', \Auth::user()->creatorId())->count();

                //lead status
                //                $user_leads   = $leads->pluck('lead_id')->toArray();
                $total_leads = count($leads);
                $lead_status = [];
                $status = LeadStage::select('lead_stages.*', 'pipelines.name as pipeline')
                    ->join('pipelines', 'pipelines.id', '=', 'lead_stages.pipeline_id')
                    ->where('pipelines.created_by', '=', \Auth::user()->creatorId())
                    ->where('lead_stages.created_by', '=', \Auth::user()->creatorId())
                    ->orderBy('lead_stages.pipeline_id')->get();

                foreach ($status as $k => $v) {
                    $lead_status[$k]['lead_stage'] = $v->name;
                    $lead_status[$k]['lead_total'] = count($v->lead());
                    $lead_status[$k]['lead_percentage'] = Utility::getCrmPercentage($lead_status[$k]['lead_total'], $total_leads);

                }

                $crm_data['lead_status'] = $lead_status;

                //deal status
                //                $user_deal   = $deals->pluck('deal_id')->toArray();
                $total_deals = count($deals);
                $deal_status = [];
                $dealstatuss = Stage::select('stages.*', 'pipelines.name as pipeline')
                    ->join('pipelines', 'pipelines.id', '=', 'stages.pipeline_id')
                    ->where('pipelines.created_by', '=', \Auth::user()->creatorId())
                    ->where('stages.created_by', '=', \Auth::user()->creatorId())
                    ->orderBy('stages.pipeline_id')->get();
                foreach ($dealstatuss as $k => $v) {
                    $deal_status[$k]['deal_stage'] = $v->name;
                    $deal_status[$k]['deal_total'] = count($v->deals());
                    $deal_status[$k]['deal_percentage'] = Utility::getCrmPercentage($deal_status[$k]['deal_total'], $total_deals);
                }
                $crm_data['deal_status'] = $deal_status;

                $crm_data['latestContract'] = Contract::where('created_by', '=', \Auth::user()->creatorId())->orderBy('id', 'desc')->limit(5)->with(['clients', 'projects', 'types'])->get();

                return view('dashboard.driver-dashboard', compact('crm_data'));
            }
        } else {
            return $this->account_dashboard_index();
        }
    }

    public function pos_dashboard_index()
    {
        $user = Auth::user();
        if (\Auth::user()->can('show pos dashboard')) {
            if ($user->type == 'admin') {
                return view('admin.dashboard');
            } else {
                $pos_data = [];
                $pos_data['monthlyPosAmount'] = Pos::totalPosAmount(true);
                $pos_data['totalPosAmount'] = Pos::totalPosAmount();
                $pos_data['monthlyPurchaseAmount'] = Purchase::totalPurchaseAmount(true);
                $pos_data['totalPurchaseAmount'] = Purchase::totalPurchaseAmount();

                $purchasesArray = Purchase::getPurchaseReportChart();
                $posesArray = Pos::getPosReportChart();

                return view('dashboard.pos-dashboard', compact('pos_data', 'purchasesArray', 'posesArray'));
            }
        } else {
            return $this->account_dashboard_index();
        }
    }

    // Load Dashboard user's using ajax
    public function filterView(Request $request)
    {
        $usr = Auth::user();
        $users = User::where('id', '!=', $usr->id);

        if ($request->ajax()) {
            if (! empty($request->keyword)) {
                $users->where('name', 'LIKE', $request->keyword.'%')->orWhereRaw('FIND_IN_SET("'.$request->keyword.'",skills)');
            }

            $users = $users->get();
            $returnHTML = view('dashboard.view', compact('users'))->render();

            return response()->json([
                'success' => true,
                'html' => $returnHTML,
            ]);
        }
    }

    public function clientView()
    {

        if (Auth::check()) {
            if (Auth::user()->type == 'super admin') {
                $user = \Auth::user();
                $user['total_user'] = $user->countCompany();
                $user['total_paid_user'] = $user->countPaidCompany();
                $user['total_orders'] = Order::total_orders();
                $user['total_orders_price'] = Order::total_orders_price();
                $user['total_plan'] = Plan::total_plan();
                $user['most_purchese_plan'] = (! empty(Plan::most_purchese_plan()) ? Plan::most_purchese_plan()->total : 0);
                // $user['most_purchese_plan'] = Plan::most_purchese_plan()->total;
                $chartData = $this->getOrderChart(['duration' => 'week']);

                return view('dashboard.super_admin', compact('user', 'chartData'));

            } elseif (Auth::user()->type == 'client') {
                $transdate = date('Y-m-d', time());
                $currentYear = date('Y');

                $calenderTasks = [];
                $chartData = [];
                $arrCount = [];
                $arrErr = [];
                $m = date('m');
                $de = date('d');
                $y = date('Y');
                $format = 'Y-m-d';
                $user = \Auth::user();
                if (\Auth::user()->can('View Task')) {
                    $company_setting = Utility::settings();
                }
                $arrTemp = [];
                for ($i = 0; $i <= 7 - 1; $i++) {
                    $date = date($format, mktime(0, 0, 0, $m, ($de - $i), $y));
                    $arrTemp['date'][] = __(date('D', strtotime($date)));
                    $arrTemp['invoice'][] = 10;
                    $arrTemp['payment'][] = 20;
                }

                $chartData = $arrTemp;

                foreach ($user->clientDeals as $deal) {
                    foreach ($deal->tasks as $task) {
                        $calenderTasks[] = [
                            'title' => $task->name,
                            'start' => $task->date,
                            'url' => route('deals.tasks.show', [
                                $deal->id,
                                $task->id,
                            ]),
                            'className' => ($task->status) ? 'bg-primary border-primary' : 'bg-warning border-warning',
                        ];
                    }

                    $calenderTasks[] = [
                        'title' => $deal->name,
                        'start' => $deal->created_at->format('Y-m-d'),
                        'url' => route('deals.show', [$deal->id]),
                        'className' => 'deal bg-primary border-primary',
                    ];
                }
                $client_deal = $user->clientDeals->pluck('id');

                $arrCount['deal'] = ! empty($user->clientDeals) ? $user->clientDeals->count() : 0;

                if (! empty($client_deal->first())) {

                    $arrCount['task'] = DealTask::whereIn('deal_id', [$client_deal->first()])->count();

                } else {
                    $arrCount['task'] = 0;
                }

                $project['projects'] = Project::where('client_id', '=', Auth::user()->id)->where('created_by', \Auth::user()->creatorId())->where('end_date', '>', date('Y-m-d'))->limit(5)->orderBy('end_date')->get();
                $project['projects_count'] = count($project['projects']);
                $user_projects = Project::where('client_id', \Auth::user()->id)->pluck('id', 'id')->toArray();
                $tasks = ProjectTask::whereIn('project_id', $user_projects)->where('created_by', \Auth::user()->creatorId())->get();
                $project['projects_tasks_count'] = count($tasks);
                $project['project_budget'] = Project::where('client_id', Auth::user()->id)->sum('budget');

                $project_last_stages = Auth::user()->last_projectstage();
                $project_last_stage = (! empty($project_last_stages) ? $project_last_stages->id : 0);
                $project['total_project'] = Auth::user()->user_project();
                $total_project_task = Auth::user()->created_total_project_task();
                $allProject = Project::where('client_id', \Auth::user()->id)->where('created_by', \Auth::user()->creatorId())->get();
                $allProjectCount = count($allProject);

                $bugs = Bug::whereIn('project_id', $user_projects)->where('created_by', \Auth::user()->creatorId())->get();
                $project['projects_bugs_count'] = count($bugs);
                $bug_last_stage = BugStatus::orderBy('order', 'DESC')->first();
                $completed_bugs = Bug::whereIn('project_id', $user_projects)->where('status', $bug_last_stage->id)->where('created_by', \Auth::user()->creatorId())->get();
                $allBugCount = count($bugs);
                $completedBugCount = count($completed_bugs);
                $project['project_bug_percentage'] = ($allBugCount != 0) ? intval(($completedBugCount / $allBugCount) * 100) : 0;
                $complete_task = Auth::user()->project_complete_task($project_last_stage);
                $completed_project = Project::where('client_id', \Auth::user()->id)->where('status', 'complete')->where('created_by', \Auth::user()->creatorId())->get();
                $completed_project_count = count($completed_project);
                $project['project_percentage'] = ($allProjectCount != 0) ? intval(($completed_project_count / $allProjectCount) * 100) : 0;
                $project['project_task_percentage'] = ($total_project_task != 0) ? intval(($complete_task / $total_project_task) * 100) : 0;
                $invoice = [];
                $top_due_invoice = [];
                $invoice['total_invoice'] = 5;
                $complete_invoice = 0;
                $total_due_amount = 0;
                $top_due_invoice = [];
                $pay_amount = 0;

                if (Auth::user()->type == 'client') {
                    if (! empty($project['project_budget'])) {
                        $project['client_project_budget_due_per'] = intval(($pay_amount / $project['project_budget']) * 100);
                    } else {
                        $project['client_project_budget_due_per'] = 0;
                    }

                }

                $top_tasks = Auth::user()->created_top_due_task();
                $users['staff'] = User::where('created_by', '=', Auth::user()->creatorId())->count();
                $users['user'] = User::where('created_by', '=', Auth::user()->creatorId())->where('type', '!=', 'client')->count();
                $users['client'] = User::where('created_by', '=', Auth::user()->creatorId())->where('type', '=', 'client')->count();
                $project_status = array_values(Project::$project_status);
                $projectData = \App\Models\Project::getProjectStatus();

                $taskData = \App\Models\TaskStage::getChartData();

                return view('dashboard.clientView', compact('calenderTasks', 'arrErr', 'arrCount', 'chartData', 'project', 'invoice', 'top_tasks', 'top_due_invoice', 'users', 'project_status', 'projectData', 'taskData', 'transdate', 'currentYear'));
            }
        }
    }

    public function getOrderChart($arrParam)
    {
        $arrDuration = [];
        if ($arrParam['duration']) {
            if ($arrParam['duration'] == 'week') {
                $previous_week = strtotime('-2 week +1 day');
                for ($i = 0; $i < 14; $i++) {
                    $arrDuration[date('Y-m-d', $previous_week)] = date('d-M', $previous_week);
                    $previous_week = strtotime(date('Y-m-d', $previous_week).' +1 day');
                }
            }
        }

        $arrTask = [];
        $arrTask['label'] = [];
        $arrTask['data'] = [];
        foreach ($arrDuration as $date => $label) {

            $data = Order::select(\DB::raw('count(*) as total'))->whereDate('created_at', '=', $date)->first();
            $arrTask['label'][] = $label;
            $arrTask['data'][] = $data->total;
        }

        return $arrTask;
    }

    public function stopTracker(Request $request)
    {
        if (Auth::user()->isClient()) {
            return Utility::error_res(__('Permission denied.'));
        }
        $validatorArray = [
            'name' => 'required|max:120',
            'project_id' => 'required|integer',
        ];
        $validator = Validator::make(
            $request->all(), $validatorArray
        );
        if ($validator->fails()) {
            return Utility::error_res($validator->errors()->first());
        }
        $tracker = TimeTracker::where('created_by', '=', Auth::user()->id)->where('is_active', '=', 1)->first();
        if ($tracker) {
            $tracker->end_time = $request->has('end_time') ? $request->input('end_time') : date('Y-m-d H:i:s');
            $tracker->is_active = 0;
            $tracker->total_time = Utility::diffance_to_time($tracker->start_time, $tracker->end_time);
            $tracker->save();

            return Utility::success_res(__('Add Time successfully.'));
        }

        return Utility::error_res('Tracker not found.');
    }
}
