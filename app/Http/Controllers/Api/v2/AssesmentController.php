<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Assesment;
use App\Models\AssesmentQuestion as Question;
use App\Http\Requests\AssesmentRequest;

class AssesmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $per_page = $request->input('per_page', 100);
        $sort_by = $request->input('sort_by', 'created_at');
        $sort_dir = $request->input('sort_dir', 'desc');
        $search = $request->input('search');
        $archived = $request->input('archived');
        $datatable_draw = $request->input('draw'); // if any

        $archived = $archived == 'yes' ? true : ($archived == 'no' ? false : null);

        $assesments = Assesment::when($archived ,function ($query) use ($archived) {
            if ($archived !== null ) {
                if ($archived === true ) {
                    $query->whereNotNull('archived_at');
                } else {
                    $query->whereNull('archived_at');
                }
            }
        })->where( function($query) use ($search) {
            $query->where('code', 'LIKE', "%{$search}%");
        })->orderBy($sort_by, $sort_dir)
          ->with('questions');

        if ($per_page === 'all' || $per_page <= 0 ) {
            $results = $assesments->get();
            $assesments = new \Illuminate\Pagination\LengthAwarePaginator($results, $results->count(), -1);
        } else {
            $assesments = $assesments->paginate($per_page);
        }

        $response = collect([
            'status' => true,
            'message' => "Successful."
        ])->merge($assesments)->merge(['draw' => $datatable_draw]);
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
        $validatedData['employer_id'] = $user->id;
        $validatedData['code'] = $user->id.md5(time());
        info($validatedData);
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
    public function show($code)
    {
        $assesment = Assesment::where('code', $code)
                    ->with('questions', 'answers')->firstOrFail();

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
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
