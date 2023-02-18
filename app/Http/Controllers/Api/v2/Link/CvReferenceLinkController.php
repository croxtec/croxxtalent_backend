<?php

namespace App\Http\Controllers\Api\v2\Link;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;
use App\Models\User;
use App\Models\CvReference;
use App\Models\ReferenceQuestion;
use App\Mail\CvReferenceRequestApproved;

class CvReferenceLinkController extends Controller
{
    /**
     * Questionnaire form
     *
     * @param Illuminate\Http\Request $request
     * @param string $id
     * @return \Illuminate\Http\Response
     */
    public function questionnaireForm(Request $request, $id)
    {
        $cvReference = CvReference::find($id);
        $referenceQuestions = ReferenceQuestion::where('is_active', true)->orderBy('sort_order', 'asc')->get();

        $form_action_url = URL::signedRoute('api.links.cv_references.questionnaire_form.store', ['id' => $cvReference->id]);
        $success_page_url = URL::signedRoute('api.links.cv_references.questionnaire_form.successful', ['id' => $cvReference->id]);

        // check if feedback form was previously submitted
        if (!$cvReference->feedback) {
            $cvReference->is_approved = false;
            $cvReference->approved_at = null;
            $cvReference->save();
        }
        if ($cvReference->is_approved) {
            return redirect($success_page_url);
        }else{
            $feedback = [];
            // if (is_array($feedback)) {   }
            $cvReference->is_approved = true;
            $cvReference->approved_at = Carbon::now();
            $cvReference->feedback = count($feedback) > 0 ? $feedback : null;
            $cvReference->save();

            // send email notification
            if ($cvReference->cv->email) {
                if (config('mail.queue_send')) {
                    Mail::to($cvReference->cv->email)->queue(new CvReferenceRequestApproved($cvReference));
                } else {
                    Mail::to($cvReference->cv->email)->send(new CvReferenceRequestApproved($cvReference));
                }
            }
            return view('api.links.cv-references.questionnaire_form_successful')
                ->with( compact('cvReference') );
        }

        return view('api.links.cv-references.questionnaire_form')
                ->with( compact('cvReference', 'referenceQuestions', 'form_action_url') );
    }

    /**
     * Store Questionnaire form
     *
     * @param Illuminate\Http\Request $request
     * @param string $id
     * @return \Illuminate\Http\Response
     */
    public function storeQuestionnaireForm(Request $request, $id)
    {
        $cvReference = CvReference::findOrFail($id);

        $success_page_url = URL::signedRoute('api.links.cv_references.questionnaire_form.successful', ['id' => $cvReference->id]);

        // check if feedback form was previously submitted
        if (!$cvReference->feedback) {
            $cvReference->is_approved = false;
            $cvReference->approved_at = null;
            $cvReference->save();
        }
        if ($cvReference->is_approved) {
            return redirect($success_page_url);
        }

        // save the feeback from to the reference record
        $feedback = $request->input('feedback');
        if (is_array($feedback)) {
            $cvReference->is_approved = true;
            $cvReference->approved_at = Carbon::now();
            $cvReference->feedback = count($feedback) > 0 ? $feedback : null;
            $cvReference->save();

            // send email notification
            if ($cvReference->cv->email) {
                if (config('mail.queue_send')) {
                    Mail::to($cvReference->cv->email)->queue(new CvReferenceRequestApproved($cvReference));
                } else {
                    Mail::to($cvReference->cv->email)->send(new CvReferenceRequestApproved($cvReference));
                }
            }
        }

        return redirect($success_page_url);
    }

    /**
     * Questionnaire form Successful
     *
     * @param Illuminate\Http\Request $request
     * @param string $id
     * @return \Illuminate\Http\Response
     */
    public function questionnaireFormSuccessful(Request $request, $id)
    {
        $cvReference = CvReference::findOrFail($id);

        // check if feedback hasn't submitted
        if (!$cvReference->feedback) {
            $cvReference->is_approved = false;
            $cvReference->approved_at = null;
            $cvReference->save();
        }
        if (!$cvReference->is_approved) {
            $form_url = URL::signedRoute('api.links.cv_references.questionnaire_form', ['id' => $cvReference->id]);
            return redirect($form_url);
        }

        return view('api.links.cv-references.questionnaire_form_successful')
                ->with( compact('cvReference') );
    }
}
