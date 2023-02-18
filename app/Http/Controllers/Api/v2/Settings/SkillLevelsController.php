<?php

namespace App\Http\Controllers\Api\v2\Settings;

use App\Http\Controllers\Controller;
use App\Models\SkillSecondary;
use Illuminate\Http\Request;

use App\Models\SkillTertiary as Tertiary;
use Illuminate\Support\Facades\Validator;

class SkillLevelsController extends Controller
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

    public function indexTertiary(Request $request, $secondary)
    {
        // $this->authorize('view', Tertiary::class);

        $per_page = $request->input('per_page', 100);
        $sort_by = $request->input('sort_by', 'name');
        $sort_dir = $request->input('sort_dir', 'asc');
        $search = $request->input('search');
        $datatable_draw = $request->input('draw'); // if any

        $skills = Tertiary::where('skill_secondary_id', $secondary)->get();

        $response = collect([
            'status' => true,
            'message' => "Successful.",
            'skills' => $skills
        ]);

        return response()->json($response, 200);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeTertiary(Request $request)
    {
        // return $request;
        $validator = Validator::make($request->all(),[
            'primary' => 'required|integer',
            'secondary' => 'required|integer',
            // 'name' => 'required|max:100',
        ]);

        if($validator->fails()){
            $status = false;
            $message = $validator->errors()->toJson();
            return response()->json(compact('status', 'message') , 400);
        }
        foreach ($request->skills as $skill)  {
            $tertiary = new Tertiary();
            $tertiary->skill_id = $request->primary;
            $tertiary->skill_secondary_id = $request->secondary;
            $tertiary->name = $skill['name'];
            $tertiary->description = $skill['description'];
            $tertiary->save();
        }
        return response()->json([
            'status' => true,
            'message' => "Skill Tertiary created successfully.",
            'data' => $request->skills
        ], 201);
    }

    public function storeSecondary(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'primary' => 'required|integer',
            // 'name' => 'required|max:100',
            // 'description' => 'nullable|max:500',
        ]);

        if($validator->fails()){
            return response()->json([
              'status' => false,
              'errors' =>  $validator->errors()->toJson()
            ], 400);
        }

        $secondary = new SkillSecondary();
        $secondary->skill_id = $request->primary;
        $secondary->name = $request->secondary['name'];
        $secondary->description = $request->secondary['description'];
        $secondary->save();
        foreach ($request->skills as $skill)  {
            $tertiary = new Tertiary();
            $tertiary->skill_id = $request->primary;
            $tertiary->skill_secondary_id = $secondary->id;
            $tertiary->name = $skill['name'];
            $tertiary->description = $skill['description'];
            $tertiary->save();
        }
        return response()->json([
            'status' => true,
            'message' => "Skill Secondary created successfully.",
            'data' => $request->skills
        ], 201);
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
    public function updateSecondary(Request $request, $id)
    {
        $validator = Validator::make($request->all(),[
            'skill_id' => 'required|integer',
            'name' => 'required|max:100',
            'description' => 'nullable|max:500',
        ]);

        if($validator->fails()){
            return response()->json([
              'status' => false,
              'errors' =>  $validator->errors()->toJson()
            ], 400);
        }

        $core_skill = SkillSecondary::findOrFail($id);
        $core_skill->update($request->all());
        return response()->json([
            'status' => true,
            'message' => "Core Skill \"{$core_skill->name}\" updated successfully.",
            'data' => $core_skill
        ], 200);
    }


    public function updateTertiary(Request $request, $id)
    {
        $validator = Validator::make($request->all(),[
            'skill_id' => 'required|integer',
            'skill_secondary_id' => 'required|integer',
            'name' => 'required|max:100',
            'description' => 'nullable|max:500',
        ]);

        if($validator->fails()){
            return response()->json([
              'status' => false,
              'errors' =>  $validator->errors()->toJson()
            ], 400);
        }

        $main_skill = Tertiary::findOrFail($id);
        $main_skill->update($request->all());
        return response()->json([
            'status' => true,
            'message' => "Skill \"{$main_skill->name}\" updated successfully.",
            'data' => $main_skill->id
        ], 200);
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
