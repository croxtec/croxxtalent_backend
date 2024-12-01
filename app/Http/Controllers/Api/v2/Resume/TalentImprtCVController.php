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
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\Element\Text;

use App\Models\Competency\CompetencySetup;
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
        try{
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

            $this->updatePersonalInfo($cv, $resumeData);
            $this->updateLanguages($cv, $resumeData);
            $this->updateWorkExperience($cv, $resumeData);
            $this->updateEducation($cv, $resumeData);
            $this->updateSkills($cv, $resumeData);
            $this->updateCertifications($cv, $resumeData);

            // Storage::disk('local')->delete($path);

            return response()->json([
                'status' => true,
                'message' =>  "Resume imported successfully! Please review the information to ensure everything is accurate.",
                'data' => [], //compact('resumeData')
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Resume import failed. Please fill in the required fields on the Resume Builder."
            ], 422);
        }

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
    // protected function extractDocxContent(string $path): string
    // {
    //     try {
    //         $content = '';
    //         $phpWord = IOFactory::load($path);

    //         foreach ($phpWord->getSections() as $section) {
    //             foreach ($section->getElements() as $element) {
    //                 if (method_exists($element, 'getText')) {
    //                     $content .= $element->getText() . ' ';
    //                 }
    //             }
    //         }

    //         return $content;
    //     } catch (Exception $e) {
    //         throw new Exception('Failed to parse DOCX file: ' . $e->getMessage());
    //     }
    // }


    protected function extractDocxContent(string $path): string
    {
        try {
            $content = '';
            $phpWord = IOFactory::load($path);

            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    // Handle TextRun elements (which contain multiple text elements)
                    if ($element instanceof TextRun) {
                        foreach ($element->getElements() as $childElement) {
                            if ($childElement instanceof Text) {
                                $content .= $childElement->getText() . ' ';
                            }
                        }
                    }
                    // Handle regular Text elements
                    elseif ($element instanceof Text) {
                        $content .= $element->getText() . ' ';
                    }
                    // Handle other text-like elements (if any)
                    // elseif (method_exists($element, 'getText')) {
                    //     $content .= $element->getText() . ' ';
                    // }
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

    protected function humanString($str = '', $limit = 25){
        return isset($str) ? substr($str, 0, $limit) : '';
    }

    /**
     * Update personal information
     */
    protected function updatePersonalInfo(CV $cv, array $resumeData): void
    {
        if (!empty($resumeData['contact_info']) && is_array($resumeData['contact_info'])) {

            $contactInfo = $resumeData['contact_info'];
            info($contactInfo);
            // Prepare location data
            $country = $contactInfo['country'] ?? '';
            $countryRecord = null;

            if (!empty($country)) {
                $countryRecord = Country::where('name', $country)
                    ->orWhere('code', strtoupper($country))
                    ->first();
            }

            // Prepare address
            $addressParts = array_filter([
                $contactInfo['address'] ?? '',
                $contactInfo['city'] ?? '',
                $contactInfo['state'] ?? '',
                $contactInfo['postal_code'] ?? '',
                $contactInfo['country'] ?? ''
            ]);

            $fullAddress = !empty($addressParts) ? implode(', ', $addressParts) : null;

            // Prepare phone number
            $phone = $contactInfo['phone'] ?? '';
            if (!empty($phone)) {
                // Standardize phone format if needed
                $phone = preg_replace('/[^\d+]/', '', $phone);
            }

            $cv->update([
                'career_summary' => $this->humanString($resumeData['summary'] ?? '', 150),
                'country_code' => $countryRecord?->code,
                'city' => $contactInfo['city'] ?? '',
                'state' => $contactInfo['state'] ?? '',
                'postal_code' => $contactInfo['postal_code'] ?? '',
                'phone' => $phone,
                'email' => $contactInfo['email'] ?? '',
                'address' => $fullAddress,
            ]);
        }
    }

    /**
     * Update languages
     */
    protected function updateLanguages(CV $cv, array $resumeData): void
    {
        if (!empty($resumeData['languages']) && is_array($resumeData['languages'])) {
            // First, remove existing languages
            // $cv->languages()->delete();
            info('Languages');
            foreach ($resumeData['languages'] as $languageName) {
                info($languageName);
                $language = Language::where('name', 'LIKE', '%' . trim($languageName) . '%')->first();
                if ($language) {
                    CvLanguage::updateOrCreate([
                        'cv_id' => $cv->id,
                        'language_id' => $language->id,
                        'level' => 'intermediate'
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
                // info($experience);
                if($experience['job_title'] && $experience['employer'] && $experience['start_date']){
                    $countryRecord = null;
                    if (!empty($experience['country'])) {
                        $countryRecord = Country::where('name', $experience['country'])->first();
                    }
                    CvWorkExperience::updateOrCreate(
                        [
                            'cv_id' => $cv->id,
                            'employer' =>  $this->humanString($experience['employer']),
                            'start_date' => $experience['start_date'] ? $this->strToDate($experience['start_date']): null,
                        ],
                        [
                            'job_title' => $this->humanString($experience['job_title']),
                            'city' =>    $this->humanString($experience['city']),
                            'country_code' => $countryRecord?->code,
                            'end_date' => $experience['end_date'] ? $this->strToDate($experience['end_date']): null,
                            'is_current' => $experience['is_current'] ?? false,
                            'description' => $this->humanString($experience['description'], 150)
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
                info($education);
                if($education['school'] && $education['start_date']){
                    $countryRecord = null;
                    if (!empty($education['country'])) {
                        $countryRecord = Country::where('name', $education['country'])->first();
                    }

                    CvEducation::updateOrCreate(
                        [
                            'cv_id' => $cv->id,
                            'school' => $education['school'] ?? '',
                            'start_date' => $this->strToDate($education['start_date']) ,
                        ],
                        [
                            'course_of_study_id' => $education['course_of_study_id'] ?? null,
                            'degree_id' => $education['degree_id'] ?? null,
                            'city' => $this->humanString($education['city']) ?? '',
                            'country_code' => $countryRecord?->code,
                            'is_current' => $education['is_current'] ?? false,
                            'end_date' => $this->strToDate($education['end_date'] )?? null,
                            'description' => $this->humanString($education['description'], 150) ?? ''
                        ]
                    );
                }
            }
        }
    }
    /**
     * Update certifications
     */
    protected function updateCertifications(CV $cv, array $resumeData): void
    {
        if (!empty($resumeData['certifications']) && is_array($resumeData['certifications'])) {
            info('Certification');
            foreach ($resumeData['certifications'] as $certification) {
                // info($certification);
                if($certification['institution'] && $certification['date']){
                    CvCertification::updateOrCreate(
                        [
                            'cv_id' => $cv->id,
                            'institution' => $this->humanString($certification['institution']),
                            'start_date' => $this->strToDate($certification['date']) ?? null,
                        ],
                        [
                            'certification_course_id' => ($certification['certification_course_id']) ?? 1,
                        ]
                    );
                }
            }
        }
    }

    /**
     * Update skills/competencies
     */
    protected function updateSkills(CV $cv, array $resumeData): void
    {
        if (!empty($resumeData['skills']) && is_array($resumeData['skills'])) {
            info('Skills');
            foreach ($resumeData['skills'] as $skill) {
                // info($skill);
                $setup = CompetencySetup::where('competency', 'LIKE', '%' . trim($skill) . '%')->first();

                if($setup){
                    TalentCompetency::updateOrCreate(
                        [
                            'user_id' => $cv->user_id,
                            'cv_id' => $cv->id,
                            'competency' => $setup->competency,
                        ],
                        [
                            'level' => 'intermediate'
                        ]
                    );
                }
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
