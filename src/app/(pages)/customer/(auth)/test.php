<?php

namespace App\Http\Controllers\Admin;

use App\Events\NotificationEvent;
use App\Http\Controllers\Controller;
use App\Models\Groups;
use App\Models\Organizations;
use App\Models\Ticket\Comment;
use App\Services\SMSService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Workflow;
use App\Models\Ticket\Ticket;
use App\Models\User;
use Auth;
use App\Models\tickethistory;
use App\Models\Department;
use App\Models\JobTitle;
use Mail;
use App\Mail\mailmailablesend;
use App\Models\Performance;
use App\Models\Queue;
use App\Notifications\TicketAssignNotification;
use App\Models\Ticket\Category;
use App\Models\ticketassignchild;
use App\Models\TicketParticipant;
use DB;
class AdminAssignedticketsController extends Controller
{

    public function create(Request $request)
    {


        $this->validate($request, [
            'assigned_user_id' => 'required',
        ]);

        $calID = Ticket::find($request->assigned_id);
        // $partisipants = ticketassignchild::where('ticket_id',$calID->id)->pluck('toassignuser_id');
        // $newparticipant = array_push($participants,Auth::id());
        Performance::where('ticket_id', $calID)->delete();
        $calID->myassignuser_id = Auth::id();
        $calID->selfassignuser_id = null;
        $category_id = $calID->category_id;
        $workflow_number = $calID->workflow_No;
        $calIDworkflow = $calID->workflow_No;



        $workflows = Workflow::where('categorie_id', $category_id)
            ->where('workflow_No', '=', $workflow_number + 1)
            ->first();
        if ($workflows) {
            $workfloweWeight = $workflows->weight;
            $calIDworkflow = $workflows->workflow_No;
        } else {
            $workfloweWeight = 0;
            $workflowsenddd = Workflow::where('categorie_id', $category_id)
                ->where('workflow_No', '=', $workflow_number)
                ->first();
            if ($workflowsenddd) {
                $calIDworkflow = $calID->workflow_No + 1;
            }

        }
        if ($workflow_number !== $request->next_workflow_No) {
            $calIDworkflow = $request->next_workflow_No;
            $workflows = Workflow::where('categorie_id', $category_id)
                ->where('workflow_No', '=', $calIDworkflow + 1)
                ->first();
            if ($workflows) {
                $workfloweWeight = $workfloweWeight + $workflows->weight;
                $calIDworkflow = $workflows->workflow_No;

            } else {
                $workfloweWeight = 0;
            }
        }


        $workflowstoadd = Workflow::where('categorie_id', $category_id)
            ->where('workflow_No', '<', $calIDworkflow)
            ->get();
        $workflowWeightSum = $workflowstoadd->sum('weight');

        if ($request->has_workflow == '1') {
            $calID->workflow_No = $calIDworkflow;
            $calID->progress = $workflowWeightSum;
        }
        $calID->viewed = 0;
        $calID->orgTickets = false;
        $calID->save();
        $userIds = $request->input('assigned_user_id');

        $calID->ticketassignmutliple()->sync($userIds);
        $calID->ticketpartisipant()->syncWithoutDetaching($userIds);
        $calID->update(['retruned' => 0]);

        $assignedUserId = $request->input('assigned_user_id.0');
        $currentUser = User::find($assignedUserId);
        $currentDepartmentId = $currentUser->department_id;

        $validatedData = [
            'department_id' => $currentDepartmentId,
            'assigned_user_id' => $assignedUserId,
        ];
        $departmentcheck = Department::find($validatedData['department_id']);
        if (!$departmentcheck) {
            return response()->json(['error' => 'Department not found'], 404);
        }
        $queue = Queue::where('ticket_no', $calID->id)->latest()->first();
        // if (!$queue) {
        //     return response()->json(['error' => 'Queue item not found'], 404);
        // }
        if ($queue) {
            if ($assignedUserId) {
                $queue->update([
                    'department_id' => $validatedData['department_id'],
                    'assigned_emp' => $validatedData['assigned_user_id'],
                    'calling_status' => false,
                    'status' => 'waiting',
                    'call_count' => 0,
                ]);
            } elseif ($departmentcheck->queue_to_leader) {
                $queue->update([
                    'department_id' => $validatedData['department_id'],
                    'assigned_emp' => $departmentcheck->leader_id,
                    'calling_status' => false,
                    'call_count' => 0,
                ]);
            } else {
                $queue->update([
                    'department_id' => $validatedData['department_id'],
                    'assigned_emp' => null,
                    'calling_status' => false,
                    'call_count' => 0,
                ]);
            }
        }

        // user informatiom
        $users = User::findOrFail($request->assigned_user_id);
        $useroutput = '';
        foreach ($users as $user) {
            $useroutput .= '

            <div class="fs-11 font-weight-semibold ps-3">
                <div>
                    <span class="fs-12">' . $user->name . '</span>
                    <span class="text-muted">(Assignee)</span>
                </div>
                <small class="text-muted useroutput" >' . $user->getRoleNames()[0] . '</small>
            </div>

            ';

        }
        // Assignee

        $tickethistory = new tickethistory();
        $tickethistory->ticket_id = $calID->id;

        $output = '<div class="d-flex align-items-center">
                      <div class="mt-0">
                          <p class="mb-0 fs-12 mb-1">Status
            ';
        if ($calID->ticketnote->isEmpty()) {
            if ($calID->overduestatus != null) {
                $output .= '
                <span class="text-teal font-weight-semibold mx-1">' . $calID->status . '</span>
                <span class="text-danger font-weight-semibold mx-1">' . $calID->overduestatus . '</span>
                ';
            } else {
                $output .= '
                <span class="text-teal font-weight-semibold mx-1">' . $calID->status . '</span>
                ';
            }

        } else {
            if ($calID->overduestatus != null) {
                $output .= '
                <span class="text-teal font-weight-semibold mx-1">' . $calID->status . '</span>
                <span class="text-danger font-weight-semibold mx-1">' . $calID->overduestatus . '</span>
                <span class="text-warning font-weight-semibold mx-1">Note</span>
                ';
            } else {
                $output .= '
                <span class="text-teal font-weight-semibold mx-1">' . $calID->status . '</span>
                <span class="text-warning font-weight-semibold mx-1">Note</span>
                ';
            }
        }

        $output .= '
            <p class="mb-0 fs-17 font-weight-semibold text-dark">' . Auth::user()->name . '<span class="fs-11 mx-1 text-muted">(Assigner)</span></p>
            ' . $useroutput . '
        </div>
        <div class="ms-auto">
        <span class="float-end badge badge-primary-light">
            <span class="fs-11 font-weight-semibold">' . Auth::user()->getRoleNames()[0] . '</span>
        </span>
        </div>

        </div>
        ';
        $tickethistory->ticketactions = $output;
        $tickethistory->save();
        try {
            $queue = Queue::where('ticket_no', $calID->ticket_id)
                ->whereDate('created_at', now())
                ->first();
            if ($queue) {
                $usertoassign = $userIds[array_rand($userIds)];
                $userinfo = User::find($usertoassign);
                $queue->update([
                    'department_id' => $userinfo->department_id,
                    'assigned_emp' => $userinfo->id,
                    'calling_status' => false,
                    'status' => 'waiting',
                    'call_count' => 0,
                ]);
            }
        } catch (\Throwable $th) {
            return response()->json(['code' => 200, 'success' => lang('The ticket was successfully assigned.', 'alerts')], 200);

        }



        $ticketData = [
            'ticket_username' => $calID->cust->username,
            'ticket_id' => $calID->ticket_id,
            'ticket_title' => $calID->subject,
            'ticket_description' => $calID->message,
            'ticket_customer_url' => route('gusetticket', $calID->ticket_id),
            'ticket_admin_url' => url('/admin/ticket-view/' . $calID->ticket_id),
        ];


        try {

            $assignee = $calID->ticketassignmutliples;
            foreach ($assignee as $assignees) {
                $user = User::where('id', $assignees->toassignuser_id)->get();
                foreach ($user as $users) {

                    if ($users->id == $assignees->toassignuser_id) {
                        $users->notify(new TicketAssignNotification($calID));
                        try {
                            //code...
                            event(new NotificationEvent($users->id));

                        } catch (\Throwable $th) {
                            //throw $th;
                        }
                        // if($users->usetting->emailnotifyon == 1){
                        //     Mail::to($users->email)
                        //         ->send( new mailmailablesend('when_ticket_assign_to_other_employee', $ticketData) );
                        // }
                    }
                }
            }

        } catch (\Exception $e) {
            return response()->json(['code' => 200, 'success' => lang('The ticket was successfully assigned.', 'alerts')], 200);
        }

        return response()->json(['code' => 200, 'success' => lang('The ticket was successfully assigned.', 'alerts')], 200);

    }


    public function show(Request $req, $id, $category_id, $workflow_No, $organizationId = null)
    {

        try {
            if ($req->ajax()) {

                $has_workflow = Workflow::where('categorie_id', $category_id)->where('workflow_No', $workflow_No + 1)->first();
                $workflow = 0;
                $user_random = 0;
                $department_id = 0;
                $job_title_id = 0;
                $mandatory = 0;
                $workflownum = $workflow_No;
                $orgnaizationid = "";
                $group = null;
                $groupname=null;

                if ($has_workflow != null) {
                    $workflow_org_ids = $has_workflow->organizations->pluck('id')->toArray();
                    $user_organization_id = Auth::user()->organization;
                    if (in_array($user_organization_id, $workflow_org_ids)) {
                        $user_org = $user_organization_id;
                    } else {
                        if (Auth::user()->can('Transfer Organization')) {
                            $user_org = $organizationId ?? $has_workflow->organizations->pluck('id');
                        } else {
                            return response()->json(['error' => 'User does not have permission to transfer to another organization.'], 403);
                        }
                    }
                    $workflow_No = $has_workflow->workflow_No;
                    $department_id = $has_workflow->department_id;
                    $job_title_id = $has_workflow->job_title_id;
                    $user_random = $has_workflow->user_random;
                    $workflow = 1;
                    $mandatory = $has_workflow->Mandatory;
                    $group = $has_workflow->group_id;


                    if ($group === null) {

                        $departments_id = Department::select('departments.id', 'departments.departmentname', 'departments.parentdepartment')
                            ->where('departments.status', 1)
                            ->whereHas('organizations', function ($query) use ($user_org) {
                                $query->where('listoforganizations.id', $user_org);
                            })
                            ->where('departments.id', $department_id)
                            ->get();
                        $jobtitles_id = JobTitle::select('id', 'title')
                            ->where('status', 1)
                            ->where('id', $job_title_id)
                            ->get();


                    }else{
                        $groupname=Groups::select("groupname")->where('id',$group)->first();
                    }
                    $orgnaizationid = Organizations::select('id', 'name')
                    ->where('id', $user_org)
                    ->get();

                } else {

                    $user_org = $organizationId ?? Auth::user()->organization;


                    if (Auth::user()->can('Transfer Organization')) {

                        $orgnaizationid = Organizations::select('id', 'name')
                            ->where('status', 1)
                            ->get();
                    } else {

                        $orgnaizationid = Organizations::select('id', 'name')
                            ->where('id', $user_org)
                            ->get();
                    }
                    $departments_id = Department::select('departments.id', 'departments.departmentname', 'departments.parentdepartment')
                        ->where('departments.status', 1)
                        ->whereHas('organizations', function ($query) use ($user_org) {
                            $query->where('listoforganizations.id', $user_org);
                        })
                        ->get();

                    // Fetch job titles
                    $jobtitles_id = JobTitle::select('id', 'title')
                        ->where('status', 1)
                        ->get();
                }
                if ($group === null) {
                    $dep_total_row = $departments_id->count();
                    $dep_output = '';
                    $grouped_departments = [];
                    if ($dep_total_row > 0) {
                        $dep_output = $departments_id->push(['id' => "All", 'departmentname' => 'All']);
                    }
                    $job_total_row = $jobtitles_id->count();
                    $job_output = '';
                    if ($job_total_row > 0) {
                        if ($workflow == 0) {
                            $job_output .= '<option ></option>';
                        }
                        foreach ($jobtitles_id as $row) {

                            $job_output .= '
                        <option  value="' . $row->id . '">' . $row->title . '</option>

                        ';

                        }

                    }




                    $output = '';

                    $assign = Ticket::find($id);
                    $assugnuser_id = $assign->ticketassignmutliples->pluck('toassignuser_id')->toArray();

                    $participants = TicketParticipant::where('ticket_id', $id)->where('type', null)
                        ->whereHas('participant', function ($query) use ($job_title_id, $department_id) {
                            $query->where('job_title_id', $job_title_id)
                                ->where('department_id', $department_id)
                                ->where('status', true);
                            // ->where('active_onjob', true);
                        })->inRandomOrder()->take(1)->first();
                } else {
                    $dep_total_row = 0;
                    $dep_output = '';
                    $grouped_departments = [];

                    $job_total_row = 0;
                    $job_output = '';
                    $output = '';
                    $assign = Ticket::find($id);
                    $assugnuser_id = $assign->ticketassignmutliples->pluck('toassignuser_id')->toArray();
                    $gp_id = DB::table("groups_users")
                        ->where("groups_users.groups_id", $group)
                        ->pluck('groups_users.users_id')
                        ->all();

                    $participants = TicketParticipant::where('ticket_id', $id)
                        ->where('type', null)
                        ->whereHas('participant', function ($query) use ($gp_id) {
                            $query->whereIn('id', $gp_id)
                                ->where('status', true);
                        })
                        ->inRandomOrder()
                        ->take(1)
                        ->first();

                }




                if (!is_null($participants)) {

                    $user = User::where('id', $participants->participant_id)->get();
                    $data = $user;

                } elseif ($user_random != 0) {

                    if ($group === null) {
                        $categoryIds = [6];


                        $eligibleUsers = User::where('job_title_id', $job_title_id)
                            ->where('department_id', $department_id)
                            ->where('status', true)
                            ->where('active_onjob', true)
                            ->withCount([
                                'ticketParticipants as active_tickets' => function ($query) use ($categoryIds) {
                                    $query->whereHas('ticket', function ($q) use ($categoryIds) {
                                        $q->whereIn('category_id', $categoryIds)
                                            ->whereIn('status', ['Inprogress']);
                                    });
                                }
                            ])
                            ->orderBy('active_tickets', 'asc')
                            ->get();

                        if ($eligibleUsers->isNotEmpty()) {
                            $minWorkload = $eligibleUsers->min('active_tickets');
                            // dd($eligibleUsers);

                            // Filter users who have the minimum workload
                            $usersWithMinWorkload = $eligibleUsers->filter(function ($user) use ($minWorkload) {
                                return $user->active_tickets == $minWorkload;
                            });


                            $selectedUser = $usersWithMinWorkload->random();


                            $data = collect([$selectedUser]);
                        } else {

                            $data = collect();
                        }
                    } else {
                        $categoryIds = [6];

                        // Fetch group user IDs
                        $gp_id = DB::table("groups_users")
                            ->where("groups_users.groups_id", $group)
                            ->pluck('groups_users.users_id')
                            ->all();

                        $eligibleUsers = User::whereIn('id', $gp_id)
                            ->where('status', true)
                            ->where('active_onjob', true)
                            ->withCount([
                                'ticketParticipants as active_tickets' => function ($query) use ($categoryIds) {
                                    $query->whereHas('ticket', function ($q) use ($categoryIds) {
                                        $q->whereIn('category_id', $categoryIds)
                                            ->whereIn('status', ['Inprogress']);
                                    });
                                }
                            ])
                            ->orderBy('active_tickets', 'asc')
                            ->get();

                        if ($eligibleUsers->isNotEmpty()) {
                            $minWorkload = $eligibleUsers->min('active_tickets');

                            // Filter users who have the minimum workload
                            $usersWithMinWorkload = $eligibleUsers->filter(function ($user) use ($minWorkload) {
                                return $user->active_tickets == $minWorkload;
                            });

                            $selectedUser = $usersWithMinWorkload->random();

                            $data = collect([$selectedUser]);
                        } else {
                            $data = collect();
                        }

                    }

                } else {
                    if ($group === null) {
                        $data = User::where('job_title_id', $job_title_id)
                            ->where('department_id', $department_id)
                            ->where('status', 1)
                            ->where('active_onjob', 1)
                            ->get();
                    } else {
                        $gp_id = DB::table("groups_users")
                            ->where("groups_users.groups_id", $group)
                            ->pluck('groups_users.users_id')
                            ->all();

                        $data = User::whereIn('id', $gp_id)
                            ->where('status', 1)
                            ->where('active_onjob', 1)
                            ->get();

                    }
                }


                $total_row = $data->count();

                if ($total_row > 0) {
                    if ($workflow == 0) {
                        $output .= '<option></option>';
                    }

                    foreach ($data as $row) {
                        if (Auth::user()->id != $row->id) {
                            if ($user_random != 0) {
                                $output .= '
                            <option  value="' . $row->id . '"' . (in_array($row->id, $assugnuser_id) ? 'selected' : '') . '> xxxxxxxx (' . (!empty($row->getRoleNames()[0]) ? $row->getRoleNames()[0] : '') . ')</option>
                            ';
                            } else {

                                $output .= '
                        <option  value="' . $row->id . '"' . (in_array($row->id, $assugnuser_id) ? 'selected' : '') . '> ' . $row->name . ' (' . (!empty($row->getRoleNames()[0]) ? $row->getRoleNames()[0] : '') . ')</option>

                        ';
                            }

                        }
                    }

                }

                $data = array(
                    //has workflow
                    'has_workflow' => $workflow,
                    'workflow_No' => $workflownum,
                    //Ticket
                    'assign_data' => $assign,
                    //Users
                    'table_data' => $output,
                    'total_data' => $total_row,
                    //dep
                    'departments_id' => $dep_output,
                    'departments_total' => $dep_total_row,
                    //dep
                    'jobtitles_id' => $job_output,
                    'jobtitles_total' => $job_total_row,
                    //organization
                    'organizations' => $orgnaizationid ? $orgnaizationid : "",
                    'mandatory' => $mandatory,
                    'user_org' => $user_org,
                    'group' => $group,
                    'groupname' => $groupname,

                );

                return response()->json($data);
            }
        } catch (\Throwable $e) {
            return response()->json(['code' => 500, 'error' => 'Error fetching queue' . $e]);

        }


    }

    public function jobtitle_employees($id, $org)
    {
        $job_output = '';
        $table_data = '';
        $job_data = [];

        if ($id == "All") {
            $job_data = JobTitle::where('status', 1)->get();
            $users = User::where('status', 1)->where('organization', $org)->get();


            $job_output .= '<option label=""></option>';
            foreach ($job_data as $job) {
                $job_output .= '<option value="' . $job->id . '">' . $job->title . '</option>';
            }
            foreach ($users as $user) {
                $table_data .= '<option value="' . $user->id . '">' . $user->name . '</option>';
            }
        } else {

            $department = Department::find($id);

            if ($department->ticket_to_leader == 1) {
                $leader_user = User::find($department->leader_id);

                if (!empty($leader_user)) {
                    $jobtitles = JobTitle::where('id', $leader_user->job_title_id)->first();
                    $job_data = $jobtitles;
                    $job_output = '<option label=""></option>';
                    $job_output .= '<option value="' . $jobtitles->id . '">' . $jobtitles->title . '</option>';
                    $table_data .= '<option value="' . $leader_user->id . '">' . $leader_user->name . '</option>';
                } else {
                    $job_output .= '<option value=""></option>';
                }
            } else {
                $job_data = $department->jobTitles;
                $job_total_row = $job_data->count();
                $users = User::where('department_id', $id)->get();

                if ($users) {
                    foreach ($users as $row) {
                        $table_data .= '<option value="' . $row->id . '">' . $row->name . '</option>';
                    }
                }

                if ($job_total_row > 0) {
                    $job_output .= '<option value=""></option>';

                    foreach ($job_data as $row) {
                        $job_output .= '<option value="' . $row->id . '">' . $row->title . '</option>';
                    }
                }
            }
        }
        $data = [
            'job_table_data' => $job_output,
            'jobTitles' => $job_data,
            'table_data' => $table_data
        ];

        return response()->json($data);
    }



    public function employees($departmentId, $jobTitleId)
    {
        $output = '';
        $data = null;


        if (strtolower($departmentId) == 'all') {

            $data = User::where('job_title_id', $jobTitleId)
                ->where('status', true)
                ->where('active_onjob', true)
                ->get();
        } else {

            $department = Department::find($departmentId);

            if ($department) {
                if ($department->ticket_to_leader && $department->has_leader) {

                    $data = User::where('id', $department->leader_id)
                        ->where('job_title_id', $jobTitleId)
                        ->where('status', true)
                        ->where('active_onjob', true)
                        ->get();
                } else {

                    $data = User::where('department_id', $departmentId)
                        ->where('job_title_id', $jobTitleId)
                        ->where('status', true)
                        ->where('active_onjob', true)
                        ->get();
                }
            }
        }


        if ($data && $data->count() > 0) {
            $output .= '<option label=""></option>';
            foreach ($data as $row) {
                $roleName = !empty($row->getRoleNames()[0]) ? $row->getRoleNames()[0] : '';
                $output .= '<option value="' . $row->id . '">' . $row->name . ' (' . $roleName . ')</option>';
            }
        } else {

            $output .= '<option value="">No employees found</option>';
        }

        return response()->json(['table_data' => $output]);
    }

    public function update(Request $req, $id)
    {
        $calID = Ticket::find($id);
        $calID->myassignuser_id = null;
        $calID->selfassignuser_id = null;
        $calID->save();
        $calID->ticketassignmutliple()->detach($req->assigned_userid);

        $tickethistory = new tickethistory();
        $tickethistory->ticket_id = $calID->id;

        $output = '<div class="d-flex align-items-center">
            <div class="mt-0">
                <p class="mb-0 fs-12 mb-1">Status
            ';
        if ($calID->ticketnote->isEmpty()) {
            if ($calID->overduestatus != null) {
                $output .= '
                <span class="text-teal font-weight-semibold mx-1">' . $calID->status . '</span>
                <span class="text-danger font-weight-semibold mx-1">' . $calID->overduestatus . '</span>
                ';
            } else {
                $output .= '
                <span class="text-teal font-weight-semibold mx-1">' . $calID->status . '</span>
                ';
            }

        } else {
            if ($calID->overduestatus != null) {
                $output .= '
                <span class="text-teal font-weight-semibold mx-1">' . $calID->status . '</span>
                <span class="text-danger font-weight-semibold mx-1">' . $calID->overduestatus . '</span>
                <span class="text-warning font-weight-semibold mx-1">Note</span>
                ';
            } else {
                $output .= '
                <span class="text-teal font-weight-semibold mx-1">' . $calID->status . '</span>
                <span class="text-warning font-weight-semibold mx-1">Note</span>
                ';
            }
        }

        $output .= '
            <p class="mb-0 fs-17 font-weight-semibold text-dark">' . Auth::user()->name . '<span class="fs-11 mx-1 text-muted">(UnAssigned Ticket)</span></p>
        </div>
        <div class="ms-auto">
        <span class="float-end badge badge-primary-light">
            <span class="fs-11 font-weight-semibold">' . Auth::user()->getRoleNames()[0] . '</span>
        </span>
        </div>

        </div>
        ';
        $tickethistory->ticketactions = $output;
        $tickethistory->save();

        return response()->json(['data' => $calID, 'success' => lang('Updated successfully', 'alerts')]);
    }
    public function reassign(Request $request)
    {
        $userdep = Auth::user()->department;

        $ticket_id = $request->reassign_ticket_id;
        $ticket = Ticket::find($ticket_id);
        $Workflow = Workflow::where('categorie_id', $ticket->category_id)->where('workflow_No', $ticket->workflow_No)->first();
        $ticketParticipant = TicketParticipant::where('ticket_id', $ticket_id)->first();

        if (!$ticketParticipant) {
            return response()->json(['code' => 404, 'error' => 'No one assigned for this ticket'], 404);
        }
        if ($request->selected_reassign === "yes") {
            $participant = User::find($request->userid);
        } else {
            $participant = User::find($ticketParticipant->participant_id);
        }


        if ($Workflow == null) {
            $userstoassign = User::where('department_id', $participant->department_id)
                ->where('job_title_id', $participant->job_title_id)
                ->where('status', true)
                ->where('active_onjob', true)
                ->where('id', '!=', $participant->id)
                ->get();

        } else {
            $userstoassign = User::where('department_id', $Workflow->department_id)
                ->where('job_title_id', $Workflow->job_title_id)
                ->where('status', true)
                ->where('active_onjob', true)
                ->where('id', '!=', $participant->id)
                ->get();

        }



        if ($userdep->id === $participant->department_id) {
            if ($userstoassign->isEmpty()) {
                return response()->json(['code' => 404, 'error' => 'No users available for reassignment'], 404);
            }
            if ($request->selected_reassign === "yes") {
                $randomUser = User::find($request->employee_id);

            } else {
                $randomUser = $userstoassign->random();
            }


            // Sync the random user for reassignment
            $ticket->ticketassignmutliple()->sync([$randomUser->id]);
            $ticket->ticketpartisipant()->syncWithoutDetaching([$randomUser->id]);
            // User information
            $useroutput = '
        <div class="fs-11 font-weight-semibold ps-3">
            <div>
                <span class="fs-12">' . $randomUser->name . '</span>
                <span class="text-muted">(Assignee)</span>
            </div>
            <small class="text-muted useroutput">' . $randomUser->getRoleNames()[0] . '</small>
        </div>
        ';
            // Ticket history
            $tickethistory = new tickethistory();
            $tickethistory->ticket_id = $ticket->id;

            $output = '<div class="d-flex align-items-center">
                      <div class="mt-0">
                          <p class="mb-0 fs-12 mb-1">Status
        ';
            if ($ticket->ticketnote->isEmpty()) {
                if ($ticket->overduestatus != null) {
                    $output .= '
                <span class="text-teal font-weight-semibold mx-1">' . $ticket->status . '</span>
                <span class="text-danger font-weight-semibold mx-1">' . $ticket->overduestatus . '</span>
                ';
                } else {
                    $output .= '
                <span class="text-teal font-weight-semibold mx-1">' . $ticket->status . '</span>
                ';
                }
            } else {
                if ($ticket->overduestatus != null) {
                    $output .= '
                <span class="text-teal font-weight-semibold mx-1">' . $ticket->status . '</span>
                <span class="text-danger font-weight-semibold mx-1">' . $ticket->overduestatus . '</span>
                <span class="text-warning font-weight-semibold mx-1">Note</span>
                ';
                } else {
                    $output .= '
                <span class="text-teal font-weight-semibold mx-1">' . $ticket->status . '</span>
                <span class="text-warning font-weight-semibold mx-1">Note</span>
                ';
                }
            }

            $output .= '
            <p class="mb-0 fs-17 font-weight-semibold text-dark">' . Auth::user()->name . '<span class="fs-11 mx-1 text-muted">( Re Assigner)</span></p>
            ' . $useroutput . '
        </div>
        <div class="ms-auto">
        <span class="float-end badge badge-primary-light">
            <span class="fs-11 font-weight-semibold">' . Auth::user()->getRoleNames()[0] . '</span>
        </span>
        </div>
        </div>
        ';
            $tickethistory->ticketactions = $output;
            $tickethistory->save();
            $totalTime = null;
            $performance = Performance::where('ticket_id', $ticket->id)->where('user_id', Auth::id())->first();
            if ($performance) {
                $startTime = new Carbon($performance->ticket_open_time);
                $endTime = Carbon::now();

                $diffInMinutes = $startTime->diffInMinutes($endTime);

                $totalTime = $diffInMinutes;
            }
            Comment::create([
                'ticket_id' => $ticket->id,
                'user_id' => Auth::user()->id,
                'cust_id' => null,
                'comment' => $request->reassign_comment,
                'visable_to_customer' => false,
                'customerreply' => false,
                //   'timetaken' => $totalTime
            ]);

            $ticketData = [
                'ticket_username' => $ticket->cust->username,
                'ticket_id' => $ticket->ticket_id,
                'ticket_title' => $ticket->subject,
                'ticket_description' => $ticket->message,
                'ticket_customer_url' => route('gusetticket', $ticket->ticket_id),
                'ticket_admin_url' => url('/admin/ticket-view/' . $ticket->ticket_id),
            ];

            try {
                // Notify the randomly assigned user
                $randomUser->notify(new TicketAssignNotification($ticket));
                try {
                    event(new NotificationEvent($randomUser->id));
                } catch (\Throwable $th) {
                    // Handle event errors if needed
                }
            } catch (\Exception $e) {
                return response()->json(['code' => 200, 'success' => lang('The ticket was successfully re-assigned.', 'alerts')], 200);
            }
            try {
                $message = 'ለርሶ ተላልፎ የነበረው ትኬት ቁጥር' . $ticket->ticket_id . 'ለሌላ ሰራተኛ ተላልፉዋል';
                SMSService::sendSMS($participant->phone, $message);

            } catch (\Exception $e) {
                return response()->json(['success' => lang('The password has been successfully changed!', 'alerts')], 200);
            }

            return response()->json(['code' => 200, 'success' => lang('The ticket was successfully re-assigned.', 'alerts')], 200);

        } else {
            return response()->json(['code' => 404, 'error' => 'The ticket is not in your department!'], 404);
        }

    }
    public function reassignToDocumentation(Request $request)
    {
        $ticket_id = $request->reassigntodocumentationmodal_ticket_id;
        $ticket = Ticket::find($ticket_id);


        if (!$ticket) {
            return response()->json(['code' => 404, 'error' => 'Ticket not found'], 404);
        }

        if (!$ticket->additionaldata) {
            $documentationDeptId = Department::where('departmentname', 'የመብት ፈጠራ ዳይሬክቶሬት')->first()->id;
            $serviceDeskJobTitleId = JobTitle::where('title', 'የስራ ዕድል እና ቦታ አመቻች ባለሙያ')->first()->id;
            // $documentationDeptId = Department::where('departmentname', 'የይዞታ ማህደር አስተዳር ድጋፍና ክትትል ቡድን')->first()->id;
            // $serviceDeskJobTitleId = JobTitle::where('title', 'የይዞታ ማህደራት ባለሙያ')->first()->id;

            // Get the list of users in the Documentation department with the Service Desk job title
            $userstoassign = User::where('department_id', $documentationDeptId)
                ->where('job_title_id', $serviceDeskJobTitleId)
                ->where('status', true)
                ->where('active_onjob', true)
                ->get();

            if ($userstoassign->isEmpty()) {
                return response()->json(['code' => 404, 'error' => 'No users available for reassignment'], 404);
            }

            // Select a random user from the list
            $randomUser = $userstoassign->random();

            // Sync the random user for reassignment
            $ticket->ticketassignmutliple()->sync([$randomUser->id]);
            $ticket->ticketpartisipant()->syncWithoutDetaching([$randomUser->id]);

            // User information
            $useroutput = '
            <div class="fs-11 font-weight-semibold ps-3">
                <div>
                    <span class="fs-12">' . $randomUser->name . '</span>
                    <span class="text-muted">(Assignee)</span>
                </div>
                <small class="text-muted useroutput">' . $randomUser->getRoleNames()[0] . '</small>
            </div>
            ';

            // Ticket history
            $tickethistory = new tickethistory();
            $tickethistory->ticket_id = $ticket->id;

            $output = '<div class="d-flex align-items-center">
                        <div class="mt-0">
                            <p class="mb-0 fs-12 mb-1">Status
            ';
            if ($ticket->ticketnote->isEmpty()) {
                if ($ticket->overduestatus != null) {
                    $output .= '
                    <span class="text-teal font-weight-semibold mx-1">' . $ticket->status . '</span>
                    <span class="text-danger font-weight-semibold mx-1">' . $ticket->overduestatus . '</span>
                    ';
                } else {
                    $output .= '
                    <span class="text-teal font-weight-semibold mx-1">' . $ticket->status . '</span>
                    ';
                }
            } else {
                if ($ticket->overduestatus != null) {
                    $output .= '
                    <span class="text-teal font-weight-semibold mx-1">' . $ticket->status . '</span>
                    <span class="text-danger font-weight-semibold mx-1">' . $ticket->overduestatus . '</span>
                    <span class="text-warning font-weight-semibold mx-1">Note</span>
                    ';
                } else {
                    $output .= '
                    <span class="text-teal font-weight-semibold mx-1">' . $ticket->status . '</span>
                    <span class="text-warning font-weight-semibold mx-1">Note</span>
                    ';
                }
            }

            $output .= '
                <p class="mb-0 fs-17 font-weight-semibold text-dark">' . Auth::user()->name . '<span class="fs-11 mx-1 text-muted">(Additional data Requester)</span></p>
                ' . $useroutput . '
            </div>
            <div class="ms-auto">
            <span class="float-end badge badge-primary-light">
                <span class="fs-11 font-weight-semibold">' . Auth::user()->getRoleNames()[0] . '</span>
            </span>
            </div>
            </div>
            ';
            $tickethistory->ticketactions = $output;
            $tickethistory->save();
            $ticket->additionaldata = Auth::id();
            $ticket->save();
            Comment::create([
                'ticket_id' => $ticket->id,
                'user_id' => Auth::user()->id,
                'cust_id' => null,
                'comment' => $request->reassigntodocumentationmodal_comment,
                'visable_to_customer' => false,
                'customerreply' => false,
                //   'timetaken' => $totalTime
            ]);


            try {
                // Notify the randomly assigned user
                $randomUser->notify(new TicketAssignNotification($ticket));
                try {
                    event(new NotificationEvent($randomUser->id));
                } catch (\Throwable $th) {
                    // Handle event errors if needed
                }
            } catch (\Exception $e) {
                return response()->json(['code' => 200, 'success' => lang('The ticket was successfully re-assigned.', 'alerts')], 200);
            }
            return response()->json(['code' => 200, 'success' => 'The ticket was successfully reassigned to the Documentation department.'], 200);
        } else {


            // Select a random user from the list
            $randomUser = User::find($ticket->additionaldata);

            // Sync the random user for reassignment
            $ticket->ticketassignmutliple()->sync($randomUser->id);
            $ticket->ticketpartisipant()->syncWithoutDetaching($randomUser->id);

            // User information
            $useroutput = '
            <div class="fs-11 font-weight-semibold ps-3">
                <div>
                    <span class="fs-12">' . $randomUser->name . '</span>
                    <span class="text-muted">(Assignee)</span>
                </div>
                <small class="text-muted useroutput">' . $randomUser->getRoleNames()[0] . '</small>
            </div>
            ';

            // Ticket history
            $tickethistory = new tickethistory();
            $tickethistory->ticket_id = $ticket->id;

            $output = '<div class="d-flex align-items-center">
                        <div class="mt-0">
                            <p class="mb-0 fs-12 mb-1">Status
            ';
            if ($ticket->ticketnote->isEmpty()) {
                if ($ticket->overduestatus != null) {
                    $output .= '
                    <span class="text-teal font-weight-semibold mx-1">' . $ticket->status . '</span>
                    <span class="text-danger font-weight-semibold mx-1">' . $ticket->overduestatus . '</span>
                    ';
                } else {
                    $output .= '
                    <span class="text-teal font-weight-semibold mx-1">' . $ticket->status . '</span>
                    ';
                }
            } else {
                if ($ticket->overduestatus != null) {
                    $output .= '
                    <span class="text-teal font-weight-semibold mx-1">' . $ticket->status . '</span>
                    <span class="text-danger font-weight-semibold mx-1">' . $ticket->overduestatus . '</span>
                    <span class="text-warning font-weight-semibold mx-1">Note</span>
                    ';
                } else {
                    $output .= '
                    <span class="text-teal font-weight-semibold mx-1">' . $ticket->status . '</span>
                    <span class="text-warning font-weight-semibold mx-1">Note</span>
                    ';
                }
            }

            $output .= '
                <p class="mb-0 fs-17 font-weight-semibold text-dark">' . Auth::user()->name . '<span class="fs-11 mx-1 text-muted">(Additional data uploader)</span></p>
                ' . $useroutput . '
            </div>
            <div class="ms-auto">
            <span class="float-end badge badge-primary-light">
                <span class="fs-11 font-weight-semibold">' . Auth::user()->getRoleNames()[0] . '</span>
            </span>
            </div>
            </div>
            ';
            $tickethistory->ticketactions = $output;
            $tickethistory->save();
            $ticket->additionaldata = null;
            $ticket->save();
            try {
                // Notify the randomly assigned user
                $randomUser->notify(new TicketAssignNotification($ticket));
                try {
                    event(new NotificationEvent($randomUser->id));
                } catch (\Throwable $th) {
                    // Handle event errors if needed
                }
            } catch (\Exception $e) {
                return response()->json(['code' => 200, 'success' => lang('The ticket was successfully re-assigned.', 'alerts')], 200);
            }
            return response()->json(['code' => 200, 'success' => 'The ticket was successfully returned.'], 200);

        }
        // $originalParticipant = TicketParticipant::where('ticket_id', $ticket_id)->first();

        // if (!$originalParticipant) {
        //     return response()->json(['code' => 404, 'error' => 'No one assigned for this ticket'], 404);
        // }




    }


}
