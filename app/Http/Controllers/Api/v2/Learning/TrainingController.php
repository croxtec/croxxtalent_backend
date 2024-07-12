<?php

namespace App\Http\Controllers\Api\v2\Learning;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\TrainingRequest;
use App\Models\Training\CroxxTraining;


class TrainingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $user_type = $user->type;
        $per_page = $request->input('per_page', 25);
        $sort_by = $request->input('sort_by', 'created_at');
        $sort_dir = $request->input('sort_dir', 'desc');
        $search = $request->input('search');
        $archived = $request->input('archived');
        $datatable_draw = $request->input('draw'); // if any

        $archived = $archived == 'yes' ? true : ($archived == 'no' ? false : null);

        $training = CroxxTraining::when($user_type == 'employer', function($query) use ($user){
                $query->where('user_id', $user->id);
            })
            ->when($archived ,function ($query) use ($archived) {
            if ($archived !== null ) {
                if ($archived === true ) {
                    $query->whereNotNull('archived_at');
                } else {
                    $query->whereNull('archived_at');
                }
            }
        })
        ->where( function($query) use ($search) {
            $query->where('code', 'LIKE', "%{$search}%");
        })
        ->orderBy($sort_by, $sort_dir);

        if ($per_page === 'all' || $per_page <= 0 ) {
            $results = $training->get();
            $training = new \Illuminate\Pagination\LengthAwarePaginator($results, $results->count(), -1);
        } else {
            $training = $training->paginate($per_page);
        }

        $response = collect([
            'status' => true,
            'data' => $training,
            'message' => ""
        ]);
        return response()->json($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(TrainingRequest $request)
    {
        $user = $request->user();
        $validatedData = $request->validated();
        $validatedData['code'] = $user->id . md5(time());

        $validatedData['employer_id'] = $user->id;
        $validatedData['user_id'] = $user->id;

        $training = CroxxTraining::create($validatedData);

        return response()->json([
            'status' => true,
            'message' => "",
            'data' => $training,
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


    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(TrainingRequest $request, $id)
    {
        $validatedData = $request->validated();
        $training = CroxxTraining::findOrFail($id);

        $training->update($validatedData);

        return response()->json([
            'status' => true,
            'message' => "Training updated successfully.",
            'data' => CroxxTraining::find($training->id)
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
        $training = CroxxTraining::findOrFail($id);

        $this->authorize('delete', [CroxxTraining::class, $training]);

        $training->archived_at = now();
        $training->save();

        return response()->json([
            'status' => true,
            'message' => "Training archived successfully.",
            'data' => CroxxTraining::find($training->id)
        ], 200);
    }

    /**
     * Publish Training.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function publish($id)
    {
        $training = CroxxTraining::findOrFail($id);

        // $this->authorize('update', [CroxxTraining::class, $training]);

        if ($training->is_published != true) {
            $training->is_published = true;
            $training->save();
            // Send Push notification
            // $notification = new Notification();
            // $notification->user_id = $training->user_id;
            // $notification->action = "/Trainings";
            // $notification->title = 'Training Published';
            // $notification->message = " Your Training <b>$training->title</b> has been published.";
            // $notification->save();
            // event(new NewNotification($notification->user_id,$notification));
            // // send email notification
            // if ($training->user->email) {
            //     if (config('mail.queue_send')) {
            //         Mail::to($training->user->email)->queue(new TrainingPublished($training));
            //     } else {
            //         Mail::to($training->user->email)->send(new TrainingPublished($training));
            //     }
            // }
        }

        return response()->json([
            'status' => true,
            'message' => "Training published successfully.",
            'data' => CroxxTraining::find($training->id)
        ], 200);
    }

    /**
     * Unpublish Training.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function unpublish($id)
    {
        $training = CroxxTraining::findOrFail($id);

        $this->authorize('update', [CroxxTraining::class, $training]);

        $training->is_published = false;
        $training->save();

        return response()->json([
            'status' => true,
            'message' => "Training unpublished successfully.",
            'data' => CroxxTraining::find($training->id)
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
        $training = CroxxTraining::findOrFail($id);

        $this->authorize('delete', [CroxxTraining::class, $training]);

        $training->archived_at = null;
        $training->save();

        return response()->json([
            'status' => true,
            'message' => "Training unarchived successfully.",
            'data' => CroxxTraining::find($training->id)
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
        $training = CroxxTraining::findOrFail($id);

        $this->authorize('delete', [CroxxTraining::class, $training]);

        $name = $training->name;
        // check if the record is linked to other records
        $relatedRecordsCount = related_records_count(CroxxTraining::class, $training);

        if ($relatedRecordsCount <= 0) {
            $training->delete();
            return response()->json([
                'status' => true,
                'message' => "Training deleted successfully.",
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => "The \"{$name}\" record cannot be deleted because it is associated with {$relatedRecordsCount} other record(s). You can archive it instead.",
            ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function destroyMultiple(Request $request)
    {
        $ids = $request->input('ids');
        $valid_ids = [];
        $deleted_count = 0;
        if (is_array($ids)) {
            foreach ($ids as $id) {
                $training = CroxxTraining::find($id);
                if ($training) {
                    $this->authorize('delete', [CroxxTraining::class, $training]);
                    $valid_ids[] = $training->id;
                }
            }
        }
        $valid_ids = collect($valid_ids);
        if ($valid_ids->isNotEmpty()) {
            foreach ($valid_ids as $id) {
                $training = CroxxTraining::find($id);
                // check if the record is linked to other records
                $relatedRecordsCount = related_records_count(CroxxTraining::class, $training);
                if ($relatedRecordsCount <= 0) {
                    $training->delete();
                    $deleted_count++;
                }
            }
        }

        return response()->json([
            'status' => true,
            'message' => "{$deleted_count} Trainings deleted successfully.",
        ], 200);
    }
}
