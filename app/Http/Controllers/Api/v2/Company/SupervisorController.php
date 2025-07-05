<?php

namespace App\Http\Controllers\Api\v2\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Supervisor;
use App\Models\Employee;
use App\Http\Requests\SupervisorRequest;
use App\Traits\ApiResponseTrait;
use App\Notifications\SupervisorRemoved;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;


class SupervisorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    use ApiResponseTrait;

    public function index(Request $request)
    {
        $employer = $request->user();
        // $this->authorize('view-any', Employee::class);

        $per_page = $request->input('per_page', 100);
        $sort_by = $request->input('sort_by', 'created_at');
        $sort_dir = $request->input('sort_dir', 'desc');
        $search = $request->input('search');
        $archived = $request->input('archived');
        $department = $request->input('department');
        $role = $request->input('role');
        $datatable_draw = $request->input('draw');

        $archived = $archived == 'yes' ? true : ($archived == 'no' ? false : null);

        $supervisor = Supervisor::where('employer_id', $employer->id)
        ->whereNull('archived_at')
        ->when($archived, function ($query) use ($archived) {
            if ($archived !== null) {
                if ($archived === true) {
                    $query->whereNotNull('archived_at');
                } else {
                    $query->whereNull('archived_at');
                }
            }
        })
        ->when($department || $role,function ($query) use ($department, $role) {
            if ($department !== null  && is_numeric($department)) {
               $query->where('job_code_id', $department);
               if ($role !== null  && is_numeric($role)) {

               }
            }
        })
        ->when($request->start_date && $request->end_date, function ($query) use ($request) {
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            $query->whereBetween('created_at', [$startDate, $endDate]);
        })
        ->with(['employee'])
        ->orderBy($sort_by, $sort_dir);

        if ($per_page === 'all' || $per_page <= 0) {
            $results = $supervisor->get(); // Retrieve all data when 'all' is specified
            $supervisor = new \Illuminate\Pagination\LengthAwarePaginator($results, $results->count(), -1);
        } else {
            $supervisor = $supervisor->paginate($per_page);
        }

        // Flatten the employee data into the main structure
        $supervisor->getCollection()->transform(function ($item) {
            $item->email = $item->employee->email ?? null;
            $item->name = $item->employee->name ?? null;
            $item->code = $item->employee->code ?? null;
            $item->photo_url = $item->employee->photo_url ?? null;
            $item->department =   $item->employee->department;
            $item->department_role = $item->employee->department_role;
            $feedbackCount = $item->employee->feedbackSent->count();
            $taskCount = $item->employee->taskAssigned->count();

            $item->total_feedback_sent = $feedbackCount . ' Feedback' . ($feedbackCount > 1 ? 's' : '');
            $item->total_task_assigned = $taskCount . ' Task' . ($taskCount > 1 ? 's' : '');

            unset($item->employee);
            return $item;
        });


        $response = collect([
            'status' => true,
            'message' => ""
        ])->merge($supervisor)
          ->merge(['draw' => $datatable_draw]);
        return response()->json($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(SupervisorRequest $request)
    {
        $employer = $request->user();

        $validatedData = $request->validated();
        $validatedData['employer_id'] = $employer->id;

        // Check if any of the supervisors already exist
        $existingSupervisors = Supervisor::whereIn('supervisor_id', $validatedData['supervisor_ids'])
                                    ->where('employer_id', $employer->id)
                                    ->pluck('supervisor_id')
                                    ->toArray();

        if (!empty($existingSupervisors)) {
            return $this->unauthorizedResponse('company.supervisor.already_exists', [
                 $existingSupervisors
            ]);
            // return response()->json([
            //     "status" => false,
            //     "message" => "Some supervisors already exist.",
            //     "existing_supervisors" => $existingSupervisors
            // ], 422);
        }

        // Proceed to add all supervisors since none exists
        $addedSupervisors = [];

        foreach ($validatedData['supervisor_ids'] as $supervisorId) {
            $employee = Employee::where('id', $supervisorId)->first();

            $supervisorData = $validatedData;
            $supervisorData['supervisor_id'] = $supervisorId;

            $supervisor = Supervisor::create($supervisorData);

            $employee->supervisor_id = $supervisor->id;
            $employee->save();

            $addedSupervisors[] = $employee;
        }

         return $this->successResponse(
            $addedSupervisors, 
            'company.supervisor.created',
            [], 201 //Status
        );
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function destroy(Request $request, $id)
    {
        $employer = $request->user();

        // Find the supervisor by ID
        $supervisor = Supervisor::findOrFail($id);
        $employee = Employee::where('supervisor_id', $supervisor->id)->first();

        // Remove Supervisor
        if ($employee) {
            $employee->supervisor_id = null;
            $employee->save();
        }

        $supervisor->archived_at = now();
        $supervisor->save();

        // Send notifications to the user
        $user = $employee->talent;
        $employee->department;

          // Get user's preferred language
        $locale = $user->locale ?? app()->getLocale();

          // Send localized notification
        Notification::send($user, 
            (new SupervisorRemoved($employee))->locale($locale)
        );
        // Notification::send($user, new SupervisorRemoved($employee));

        return $this->successResponse(
            $employee, 
            'company.supervisor.removed',
            [], 201 //Status
        );
    }

}
