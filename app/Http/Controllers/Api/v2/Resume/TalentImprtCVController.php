<?php

namespace App\Http\Controllers\Api\v2\Resume;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Models\Cv;
use App\Models\Country;
use App\Libraries\LinkedIn;
use Smalot\PdfParser\Parser as PdfParser;
use PhpOffice\PhpWord\IOFactory;
use App\Helpers\CVParser;
use App\Models\Competency\TalentCompetency;
use App\Models\CvCertification;
use App\Models\CvEducation;
use App\Models\CvLanguage;
use App\Models\CvWorkExperience;
use App\Models\Language;
use App\Services\CvImportParser;
use Exception;
use Illuminate\Support\Facades\Storage;

class TalentImprtCVController extends Controller
{

    /**
     * Store a newly created resource in storage
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function importResume(Request $request)
    {
        // Authorization was declared in the Form Request
        $user = $request->user();
        $cv = CV::where('user_id', $user->id)->firstorFail();
        // Validate the uploaded file
        $request->validate([
            'cv' => 'required|file|mimes:pdf,docx|max:2048',
        ]);

        $file = $request->file('cv');
        $path = $file->getPathname();

        $content = '';
        $content = $this->extractContent($file, $path);

        // Extract sections from the content
        // $sections = CVParser::extractSections($content);
        // $resume = CVParser::extractResumeSections($content);

        $cv = CV::where('user_id', $user->id)->firstorFail();

        $resumeData = CvImportParser::extractResumeSections($content);


        if (!$resumeData) {
            throw new Exception('Unable to parse CV content');
        }

        // $this->updatePersonalInfo($cv, $resumeData);
        $this->updateLanguages($cv, $resumeData);
        $this->updateWorkExperience($cv, $resumeData);
        $this->updateEducation($cv, $resumeData);
        $this->updateCertifications($cv, $resumeData);
        $this->updateSkills($cv, $resumeData);

        // Storage::disk('local')->delete($path);

        return response()->json([
            'status' => true,
            'message' => "Resume imported successfully. Kindly review the imported CV.",
            'data' => compact('resumeData')
        ], 200);

        // if (!$resumeData) {
        //     throw new Exception('Unable to parse CV content');
        // }

        // if($resume){
        //     $location = is_array($resume['country']) && array_reverse($resume['country']);
        //     $country = isset($location[0]) ? $location[0] : '';
        //     $city = isset($location[1]) ? $location[1] : '';
        //     $country_code = Country::where('name',$country)->first();

        //     $personal = [
        //         'job_title' => $resume['job_title'],
        //         'career_summary' => $resume['summary'],
        //         'country_code' => $country_code?->code,
        //         'city' => $city,
        //         'address' =>  is_array($resume['country']) ? implode($resume['country']) : ''
        //     ];
        //     $cv->update($personal);

        //     if( is_array($resume['languages']) ){
        //         $languageObjects = collect($resume['languages'])->map(function ($language) {
        //             return (object) ['name' => trim($language)];
        //         });
        //         // info($languageObjects);
        //         foreach($languageObjects as $language){
        //             $system_lang = App\Models\Language::where('name', $language)->first();
        //             $cvLanguage = CvLanguage::updateOrCreate(
        //                 [
        //                     'cv_id' =>  $cv->id,
        //                     'language_id' => $system_lang?->id
        //                 ]);

        //         }
        //     }

        //     $work_history_fields = [
        //         'job_title' => '',
        //         'employer' => '',
        //         'city' => '',
        //         'country_code' => '',
        //         'start_date' => '',
        //         'end_date' => '',
        //         'is_current' => '',
        //         'description' => ''
        //     ];

        //     CvWorkExperience::updateOrCreate($work_history_fields);


        //     $education_fields = [
        //         'school' => '',
        //         'course_of_study_id' => '',
        //         'degree_id' => '',
        //         'city' => '',
        //         'country_code' => '',
        //         'start_date' => '',
        //         'end_date' => '',
        //         'is_current' => '',
        //         'description' => '',
        //     ];

        //     CvEducation::updateOrCreate($education_fields);

        //     $certification_fields = [
        //         'institution' => '',
        //         'certification_course_id' => '',
        //         'start_date' => '',
        //     ];

        //     CvCertification::updateOrCreate($certification_fields);

        //     $skills_fields = [
        //         'competency',
        //         'level',
        //     ];

        //     TalentCompetency::updateOrCreate($skills_fields);

        // }


    }

    protected function extractContent($file, $path): string
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if ($extension === 'pdf') {
            return $this->extractPdfContent($path);
        } elseif ($extension === 'docx') {
            return $this->extractDocxContent($path);
        }

        throw new Exception('Unsupported file format');
    }

      /**
     * Extract content from PDF
     */
    protected function extractPdfContent(string $path): string
    {
        try {
            $parser = new PdfParser();
            $pdf = $parser->parseFile($path);
            return $pdf->getText();
        } catch (Exception $e) {
            throw new Exception('Failed to parse PDF file: ' . $e->getMessage());
        }
    }

    /**
     * Extract content from DOCX
     */
    protected function extractDocxContent(string $path): string
    {
        try {
            $content = '';
            $phpWord = IOFactory::load($path);

            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    if (method_exists($element, 'getText')) {
                        $content .= $element->getText() . ' ';
                    }
                }
            }

            return $content;
        } catch (Exception $e) {
            throw new Exception('Failed to parse DOCX file: ' . $e->getMessage());
        }
    }

    protected function strToDate($date){
       return date('Y-m-d', strtotime(strtotime($date)));
    }

    /**
     * Update personal information
     */
    protected function updatePersonalInfo(CV $cv, array $resumeData): void
    {
        $location = is_array($resumeData['country']) ? array_reverse($resumeData['country']) : [];
        $country = $location[0] ?? '';
        $city = $location[1] ?? '';

        $countryRecord = Country::where('name', $country)->first();

        $cv->update([
            'job_title' => $resumeData['job_title'] ?? null,
            'career_summary' => $resumeData['summary'] ?? null,
            'country_code' => $countryRecord?->code,
            'city' => $city,
            'address' => is_array($resumeData['country']) ? implode(', ', $resumeData['country']) : null
        ]);
    }

    /**
     * Update languages
     */
    protected function updateLanguages(CV $cv, array $resumeData): void
    {
        if (!empty($resumeData['languages']) && is_array($resumeData['languages'])) {
            // First, remove existing languages
            $cv->languages()->delete();

            foreach ($resumeData['languages'] as $languageName) {
                $language = Language::where('name', 'LIKE', '%' . trim($languageName) . '%')->first();

                if ($language) {
                    CvLanguage::create([
                        'cv_id' => $cv->id,
                        'language_id' => $language->id
                    ]);
                }
            }
        }
    }

    /**
     * Update work experience
     */
    protected function updateWorkExperience(CV $cv, array $resumeData): void
    {
        if (!empty($resumeData['work_experience']) && is_array($resumeData['work_experience'])) {
            foreach ($resumeData['work_experience'] as $experience) {

                if($experience['job_title'] && $experience['employer'] && $experience['start_date']){
                    $countryRecord = null;
                    if (!empty($experience['country'])) {
                        $countryRecord = Country::where('name', $experience['country'])->first();
                    }
                    CvWorkExperience::updateOrCreate(
                        [
                            'cv_id' => $cv->id,
                            'employer' => $experience['employer'] ?? '',
                            'start_date' => $experience['start_date'] ? $this->strToDate($experience['start_date']): null,
                        ],
                        [
                            'job_title' => $experience['job_title'] ?? '',
                            'city' => $experience['city'] ?? '',
                            'country_code' => $countryRecord?->code,
                            'end_date' => $experience['end_date'] ? $this->strToDate($experience['end_date']): null,
                            'is_current' => $experience['is_current'] ?? false,
                            'description' =>isset($experience['description']) ? substr($experience['description'], 0, 150) . (strlen($experience['description']) > 150 ? '...' : '') : ''
                        ]
                    );
                }

            }
        }
    }



    /**
     * Update education
     */
    protected function updateEducation(CV $cv, array $resumeData): void
    {
        if (!empty($resumeData['education']) && is_array($resumeData['education'])) {
            foreach ($resumeData['education'] as $education) {
                $countryRecord = null;
                if (!empty($education['country'])) {
                    $countryRecord = Country::where('name', $education['country'])->first();
                }

                CvEducation::updateOrCreate(
                    [
                        'cv_id' => $cv->id,
                        'school' => $education['school'] ?? '',
                        'start_date' => $education['start_date'] ?? null,
                    ],
                    [
                        'course_of_study_id' => $education['course_of_study_id'] ?? null,
                        'degree_id' => $education['degree_id'] ?? null,
                        'city' => $education['city'] ?? '',
                        'country_code' => $countryRecord?->code,
                        'end_date' => $education['end_date'] ?? null,
                        'is_current' => $education['is_current'] ?? false,
                        'description' => $education['description'] ?? ''
                    ]
                );
            }
        }
    }

    /**
     * Update certifications
     */
    protected function updateCertifications(CV $cv, array $resumeData): void
    {
        if (!empty($resumeData['certifications']) && is_array($resumeData['certifications'])) {
            foreach ($resumeData['certifications'] as $certification) {
                CvCertification::updateOrCreate(
                    [
                        'cv_id' => $cv->id,
                        'institution' => $certification['institution'] ?? '',
                        'certification_course_id' => $certification['certification_course_id'] ?? null,
                    ],
                    [
                        'start_date' => $certification['start_date'] ?? null,
                    ]
                );
            }
        }
    }

    /**
     * Update skills/competencies
     */
    protected function updateSkills(CV $cv, array $resumeData): void
    {
        if (!empty($resumeData['skills']) && is_array($resumeData['skills'])) {
            foreach ($resumeData['skills'] as $skill) {
                TalentCompetency::updateOrCreate(
                    [
                        'cv_id' => $cv->id,
                        'competency' => $skill['name'] ?? '',
                    ],
                    [
                        'level' => $skill['level'] ?? 'Intermediate'
                    ]
                );
            }
        }
    }

    /**
     * Import LinkedIn Profile
     *
     * @param Illuminate\Http\Request $request
     * @param string $id
     * @return \Illuminate\Http\Response
     */
    public function importLinkedIn(Request $request, $id)
    {
        $cv = Cv::findOrFail($id);

        // https://api.croxxtalent.com/v1/links/cvs/import-linkedin-callback

        $linkedIn = new LinkedIn([
            'api_key' => env('LINKEDIN_APP_CLIENT_ID'),
            'api_secret' => env('LINKEDIN_APP_CLIENT_SECRET'),
            'callback_url' => route('api.links.cvs.import_linkedin_callback')
        ]);

        $login_url = $linkedIn->getLoginUrl([
            LinkedIn::SCOPE_BASIC_PROFILE,
            // LinkedIn::SCOPE_FULL_PROFILE, // needs approval
            LinkedIn::SCOPE_EMAIL_ADDRESS,
            // LinkedIn::SCOPE_CONTACT_INFO, // needs approval
        ]);

        session(['oauth2_target_cv_id' => $cv->id]);

        return redirect($login_url);
    }

    /**
     * Import LinkedIn Profile Callback
     *
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function importLinkedInCallback(Request $request)
    {
        $error_message = null;
        try {
            $error = $request->query('error');
            $error_description = $request->query('error_description');
            if ($error_description) {
                $error_message = $error_description;
                $data_retrieved = false;
            } else {

                $id = session('oauth2_target_cv_id');
                $cv = Cv::findOrFail($id);

                $authorization_code = $request->query('code');

                $linkedIn = new LinkedIn([
                    'api_key' => env('LINKEDIN_APP_CLIENT_ID'),
                    'api_secret' => env('LINKEDIN_APP_CLIENT_SECRET'),
                    'callback_url' => route('api.links.cvs.import_linkedin_callback')
                ]);

                $access_token = $linkedIn->getAccessToken($authorization_code);
                $access_token_expires = $linkedIn->getAccessTokenExpiration();

                // $info = $linkedIn->get("/me?projection=(id,firstName,lastName,profilePicture(displayImage~:playableStreams))");
                $profileInfo = $linkedIn->get("/me?projection=(id,firstName,lastName)");
                $emailInfo = $linkedIn->get("/emailAddress?q=members&projection=(elements*(handle~))");

                if ($profileInfo->firstName) {
                    $cv->first_name = $profileInfo->firstName->localized->en_US;
                }
                if ($profileInfo->lastName) {
                    $cv->last_name = $profileInfo->lastName->localized->en_US;
                }
                if ($emailInfo->elements[0]) {
                    $cv->email = $emailInfo->elements[0]->{'handle~'}->emailAddress;
                }
                $cv->save();

                $data_retrieved = true;
            }
        }
        catch(Exception $e) {
            $data_retrieved = false;
            $error_message = $e->getMessage();
        }
        catch(\RuntimeException $e) {
            $data_retrieved = false;
            $error_message = $e->getMessage();
        }

        return view('api.links.cvs.oauth2_import')
                ->with( compact('data_retrieved', 'error_message') );;
    }

}


// if ($extension === 'pdf') {
//     $parser = new PdfParser();
//     $pdf = $parser->parseFile($file->getPathname());
//     $content = $pdf->getText();
// } elseif ($extension === 'docx') {
//     $phpWord = IOFactory::load($file->getPathname(), 'Word2007');
//     foreach ($phpWord->getSections() as $section) {
//         foreach ($section->getElements() as $element) {
//             if (method_exists($element, 'getText')) {
//                 $content .= $element->getText() . ' ';
//             }
//         }
//     }
// }else {
//     return response()->json(['error' => 'Unsupported file format'], 422);
// }
