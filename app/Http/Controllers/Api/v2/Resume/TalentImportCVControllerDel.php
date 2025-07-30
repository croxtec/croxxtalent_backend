<?php

namespace App\Http\Controllers\Api\v2\Resume;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Models\Country;
use App\Models\Cv;
use App\Libraries\LinkedIn;
use Smalot\PdfParser\Parser as PdfParser;
use PhpOffice\PhpWord\IOFactory;
use App\Helpers\CVParser;
use App\Models\CvCertification;
use App\Models\CvEducation;
use App\Models\CVFileUpload;
use App\Models\CvWorkExperience;
use App\Services\CvImportParser;
use App\Services\MediaService;
use Exception;
use Illuminate\Support\Facades\Log;

class TalentImprtCVController extends Controller
{

    protected $mediaService;

    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

     /**
     * Upload CV file
     */
    public function uploadCV(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'cv' => 'required|file|mimes:pdf,doc,docx|max:5120', // 5MB max
            'cv_id' => 'nullable|exists:cvs,id',
            'set_as_primary' => 'boolean'
        ]);

        try {
            $file = $request->file('cv');

            // Get or create CV record
            $cv = null;
            if ($request->has('cv_id')) {
                $cv = Cv::where('id', $request->cv_id)
                        ->where('user_id', $user->id)
                        ->firstOrFail();
            } else {
                // Create a basic CV record if none exists
                $cv = Cv::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'first_name' => $user->first_name ?? '',
                        'last_name' => $user->last_name ?? '',
                        'email' => $user->email,
                        'phone' => $user->phone ?? '',
                        'is_active' => true
                    ]
                );
            }

            // Upload using the media system
            $uploadOptions = [
                'user_id' => $user->id,
                'employer_id' => null, // Not employer related
                'employee_id' => null,
            ];

            $media = $cv->addMedia($file, 'cv_document', $uploadOptions);

            // Create CV file upload record
            $cvUpload = CVFileUpload::create([
                'user_id' => $user->id,
                'cv_id' => $cv->id,
                'file_name' => $media->file_name,
                'original_name' => $media->original_name,
                'file_size' => $media->file_size,
                'file_url' => $media->file_url,
                'file_type' => $media->file_type,
                'is_primary' => $request->boolean('set_as_primary', true),
                'uploaded_at' => now()
            ]);

            // Set as primary if requested or if it's the first CV
            if ($request->boolean('set_as_primary', true) || !CVFileUpload::where('user_id', $user->id)->where('is_primary', true)->exists()) {
                $cvUpload->setPrimary();
            }

            Log::info('CV uploaded successfully', [
                'user_id' => $user->id,
                'cv_id' => $cv->id,
                'file_name' => $media->original_name,
                'file_size' => $media->file_size
            ]);

            return response()->json([
                'status' => true,
                'message' => 'CV uploaded successfully',
                'data' => [
                    'upload' => $cvUpload,
                    'media' => $media
                ]
            ], 201);

        } catch (Exception $e) {
            Log::error('CV upload failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'file_name' => $request->file('cv')?->getClientOriginalName()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Failed to upload CV. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

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
            'cv' => 'required|file|mimes:pdf,docx|max:4088',
        ]);

        try {
            $file = $request->file('cv');
            $extension = strtolower($file->getClientOriginalExtension());
            $content = '';

            $uploadResponse = $this->uploadCV($request);
            if (!$uploadResponse->getData()->status) {
                return $uploadResponse;
            }

            $content = $this->extractContent($file, $extension);

            // Extract sections from the content
            // $sections = CVParser::extractSections($content);
            // $resume = CVParser::extractResumeSections($content);

            $cv = CV::where('user_id', $user->id)->firstorFail();

            $resumeData = CvImportParser::extractResumeSections($content);

            return response()->json([
                'status' => true,
                'message' => "Resume imported successfully. Kindly review the imported CV.",
                'data' => compact('resume', 'resumeData')
            ], 200);
        } catch (Exception $e) {
            Log::error('Resume import failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Failed to import resume'
            ], 500);
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
