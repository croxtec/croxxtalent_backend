<?php

namespace App\Http\Controllers\Api\v2\Operations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\ExperienceAssessmentRequest;

class ExperienceAssessmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ExperienceAssessmentRequest $request)
    {

        $user = $request->user();
        // Authorization is declared in the Form Request
        $validatedData = $request->validated();
        $validatedData['user_id'] = $user->id;
        // $validatedData['employer_id'] = $user->id;
        $validatedData['code'] = $user->id.md5(time());

        info($validatedData);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
