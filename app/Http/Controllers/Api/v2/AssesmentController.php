<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Assesment;
use App\Models\AssesmentQuestion as Question;
use App\Models\EmployerJobcode as JobCode;
use App\Models\Employee;

use App\Http\Requests\AssesmentRequest;
use App\Models\AssesmentSummary;

class AssesmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $per_page = $request->input('per_page', 100);
        $sort_by = $request->input('sort_by', 'created_at');
        $sort_dir = $request->input('sort_dir', 'desc');
        $search = $request->input('search');
        $archived = $request->input('archived');
        $datatable_draw = $request->input('draw'); // if any

        $archived = $archived == 'yes' ? true : ($archived == 'no' ? false : null);
        //
        $groups = array();
        $assesments = Assesment::where('admin_id', $user->id)->
            when($archived ,function ($query) use ($archived) {
            if ($archived !== null ) {
                if ($archived === true ) {
                    $query->whereNotNull('archived_at');
                } else {
                    $query->whereNull('archived_at');
                }
            }
        })->where( function($query) use ($search) {
            $query->where('code', 'LIKE', "%{$search}%");
        })->orderBy($sort_by, $sort_dir);

        if ($per_page === 'all' || $per_page <= 0 ) {
            $results = $assesments->get();
            $assesments = new \Illuminate\Pagination\LengthAwarePaginator($results, $results->count(), -1);
        } else {
            $assesments = $assesments->paginate($per_page);
        }

        foreach($assesments as $assessment){
            $groups[$assessment['domain_name']][$assessment['core_name']][] = $assessment;
        }


        $response = collect([
            'status' => true,
            'message' => "Successful."
        ])->merge($groups)->merge(['draw' => $datatable_draw]);
        return response()->json($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(AssesmentRequest $request)
    {
        $user = $request->user();
        // Authorization is declared in the Form Request
        $validatedData = $request->validated();
        $validatedData['admin_id'] = $user->id;
        // $validatedData['employer_id'] = $user->id;
        $validatedData['code'] = $user->id.md5(time());

        $questions = $validatedData['questions'];

        $assesment = Assesment::create($validatedData);

        if($assesment){
            foreach($questions as $question) {
                $question['assesment_id'] = $assesment->id;
                Question::create($question);
            }

            return response()->json([
                'status' => true,
                'message' => "Assesment created successfully.",
                'data' => Assesment::find($assesment->id)
            ], 201);

        } else {
            return response()->json([
                'status' => false,
                'message' => "Could not complete request.",
            ], 400);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $code)
    {
        $user = $request->user();

        $assesment = Assesment::where('code', $code)
                    ->with('questions')->firstOrFail();

        return response()->json([
            'status' => true,
            'message' => "Successful.",
            'data' => $assesment
        ], 200);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(AssesmentRequest $request, $id)
    {
        $validatedData = $request->validated();
        // info($validatedData);
        $assesment = Assesment::findOrFail($id);
        $assesment->update($validatedData);

        return response()->json([
            'status' => true,
            'message' => "Assesment \"{$assesment->name}\" updated successfully.",
            'data' => Assesment::find($assesment->id)
        ], 200);
    }

    /**
     * Publish Assesment
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function publish($id)
    {
        // $this->authorize('update', [Assesment::class, $assesment]);
        $assesment = Assesment::findOrFail($id);
        $employees = array();


        if($assesment->is_published != true){
            if($assesment->job_code_id) {
                $employees = Employee::where('job_code_id', $assesment->job_code_id)->get();
            }

            if($assesment->candidates) {
                $employees = $assesment->candidates;
            }

            foreach($employees as $employee) {
                AssesmentSummary::create([
                    'assesment_id' => $assesment->id,
                    'employer_id' => $assesment->admin_id,
                    'talent_id' => $employee->user_id
                ]);
            }

            $assesment->is_published = true;
            $assesment->save();
        }

        return response()->json([
            'status' => true,
            'message' => "Assesment \"{$assesment->name}\" publish successfully.",
            'data' => Assesment::find($assesment->id)
        ], 200);
    }

    /**
     * Publish Assesment
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function unpublish($id)
    {
        $assesment = Assesment::findOrFail($id);

        // $this->authorize('update', [Assesment::class, $assesment]);

        $assesment->is_published = false;
        $assesment->save();

        return response()->json([
            'status' => true,
            'message' => "Assesment \"{$assesment->name}\" unpublish successfully.",
            'data' => Assesment::find($assesment->id)
        ], 200);
    }

    /**
     * Archive the specified resource from active list.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function archive($id)
    {
        $assesment = Assesment::findOrFail($id);

        // $this->authorize('delete', [Assesment::class, $assesment]);

        $assesment->archived_at = now();
        $assesment->save();

        return response()->json([
            'status' => true,
            'message' => "Assesment \"{$assesment->name}\" archived successfully.",
            'data' => Assesment::find($assesment->id)
        ], 200);
    }

    /**
     * Unarchive the specified resource from archived storage.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function unarchive($id)
    {
        $assesment = Assesment::findOrFail($id);

        // $this->authorize('delete', [Assesment::class, $assesment]);

        $assesment->archived_at = null;
        $assesment->save();

        return response()->json([
            'status' => true,
            'message' => "Assesment \"{$assesment->name}\" unarchived successfully.",
            'data' => Assesment::find($assesment->id)
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $assesment = Assesment::findOrFail($id);

        // $this->authorize('delete', [Assesment::class, $assesment]);

        $name = $assesment->name;
        // check if the record is linked to other records
        $relatedRecordsCount = related_records_count(Assesment::class, $assesment);

        if ($relatedRecordsCount <= 0) {
            $assesment->delete();
            return response()->json([
                'status' => true,
                'message' => "Assesment \"{$name}\" deleted successfully.",
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => "The \"{$name}\" record cannot be deleted because it is associated with {$relatedRecordsCount} other record(s). You can archive it instead.",
            ], 400);
        }
    }
}
