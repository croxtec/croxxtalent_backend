<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\Skill;
use App\Models\Language;
use Illuminate\Support\Facades\Log;

class CvImportParser
{
     protected static $debug = false;

    /**
     * Extract sections from resume content
     */
    public static function extractResumeSections(string $content): array
    {
        // Initialize empty sections
        $sections = [
            'summary' => '',
            'contact_info' => [],
            'work_experience' => [],
            'education' => [],
            'skills' => [],
            'certifications' => [],
            'languages' => [],
            'projects' => [],
            'awards' => [],
            'hobbies' => []
        ];

        // Log original content for debugging
        if (self::$debug) {
            // Log::info('Original CV Content:', ['content' => $content]);
        }

        // Clean and normalize content first
        $content = self::preprocessContent($content);

        if (self::$debug) {
            Log::info('Preprocessed CV Content:', ['content' => $content]);
        }

        // Define section markers with variations
        $sectionMarkers = [
            'summary' => [
                'patterns' => [
                    '/\b(Professional\s+Summary|Summary|Profile|About\s+Me|Career\s+Objective|Personal\s+Summary|Introduction|Objective|Career\s+Overview|Personal\s+Profile)\s*[:.\-]*\s*(.*?)(?=\n\s*\b(?:Experience|Education|Skills|Languages|Certifications|Projects|Awards|Employment|Work\s+History)\b|\z)/is',
                    '/\b(Career\s+Summary|Executive\s+Summary|Personal\s+Statement|Overview|Summary\s+Statement)\s*[:.\-]*\s*(.*?)(?=\n\s*\b(?:Experience|Education|Skills|Languages|Certifications|Projects|Awards|Employment|Work\s+History)\b|\z)/is',
                ],
                'type' => 'text'
            ],
            'work_experience' => [
                'patterns' => [
                    '/\b(Work\s+Experience|Professional\s+Experience|Employment\s+History|Experience|Career\s+History|Job\s+Experience|Work\s+and\s+Employment\s+History)\s*[:.\-]*\s*(.*?)(?=\n\s*\b(?:Education|Skills|Languages|Certifications|Projects|Awards|Hobbies)\b|\z)/is',
                    '/\b(Employment\s+Experience|Career\s+Experience|Work\s+History|Job\s+Experience)\s*[:.\-]*\s*(.*?)(?=\n\s*\b(?:Education|Skills|Languages|Certifications|Projects|Awards|Hobbies)\b|\z)/is',
                ],
                'type' => 'experience'
            ],
            'education' => [
                'patterns' => [
                    '/\b(Education|Academic\s+Background|Educational\s+Qualifications|Academic\s+Experience|Education\s+and\s+Training)\s*[:.\-]*\s*(.*?)(?=\n\s*\b(?:Experience|Skills|Languages|Certifications|Projects|Awards|Hobbies)\b|\z)/is',
                    '/\b(Academic\s+History|Education\s+Details|Educational\s+Background|Scholastic\s+Achievements|Education\s+Background)\s*[:.\-]*\s*(.*?)(?=\n\s*\b(?:Experience|Skills|Languages|Certifications|Projects|Awards|Hobbies)\b|\z)/is',
                ],
                'type' => 'education'
            ],
            'skills' => [
                'patterns' => [
                    '/\b(Skills|Technical\s+Skills|Core\s+Competencies|Key\s+Skills|Skill\s+Set|Relevant\s+Skills|Professional\s+Skills|Specialized\s+Skills)\s*[:.\-]*\s*(.*?)(?=\n\s*\b(?:Experience|Education|Languages|Certifications|Projects|Awards|Hobbies)\b|\z)/is',
                    '/\b(Strengths|Key\s+Strengths|Competencies|Abilities|Key\s+Competencies)\s*[:.\-]*\s*(.*?)(?=\n\s*\b(?:Experience|Education|Languages|Certifications|Projects|Awards|Hobbies)\b|\z)/is',
                ],
                'type' => 'list'
            ],
            'languages' => [
                'patterns' => [
                    '/\b(Languages|Language\s+Skills|Language\s+Proficiency|Linguistic\s+Abilities|Languages\s+Spoken|Languages\s+and\s+Proficiency)\s*[:.\-]*\s*(.*?)(?=\n\s*\b(?:Experience|Education|Skills|Certifications|Projects|Awards|Hobbies)\b|\z)/is',
                ],
                'type' => 'list'
            ],
            'certifications' => [
                'patterns' => [
                    '/\b(Certifications|Licenses|Professional\s+Certifications|Certifications\s+and\s+Training|Credentials|Certifications\s+Received|Professional\s+Licenses)\s*[:.\-]*\s*(.*?)(?=\n\s*\b(?:Experience|Education|Skills|Languages|Projects|Awards|Hobbies)\b|\z)/is',
                ],
                'type' => 'certification'
            ],
            'projects' => [
                'patterns' => [
                    '/\b(Projects|Project\s+Experience|Relevant\s+Projects|Professional\s+Projects|Major\s+Projects|Notable\s+Projects)\s*[:.\-]*\s*(.*?)(?=\n\s*\b(?:Experience|Education|Skills|Languages|Certifications|Awards|Hobbies)\b|\z)/is',
                ],
                'type' => 'list'
            ],
            'hobbies' => [
                'patterns' => [
                    '/\b(Hobbies|Interests|Leisure\s+Activities|Personal\s+Interests|Leisure\s+Interests|Pastimes)\s*[:.\-]*\s*(.*?)(?=\n\s*\b(?:Experience|Education|Skills|Languages|Certifications|Projects|Awards)\b|\z)/is',
                ],
                'type' => 'list'
            ],
            'awards' => [
                'patterns' => [
                    '/\b(Awards|Honors|Achievements|Recognitions|Awards\s+and\s+Honors|Distinctions)\s*[:.\-]*\s*(.*?)(?=\n\s*\b(?:Experience|Education|Skills|Languages|Certifications|Projects|Hobbies)\b|\z)/is',
                ],
                'type' => 'list'
            ]
        ];

        function sanitizeInput($text) {
            return  preg_replace('/[^\x20-\x7E]/', '', $text);
            $sanitized = iconv('UTF-8', 'UTF-8//IGNORE', $text);
            return $sanitized;
        }

        $sanitizeContent = sanitizeInput($content);

        // Extract each section
        foreach ($sectionMarkers as $sectionKey => $sectionInfo) {
            foreach ($sectionInfo['patterns'] as $pattern) {
                if (preg_match($pattern, $sanitizeContent, $matches)) {
                    $sectionContent = trim($matches[2]);

                    if (self::$debug) {
                        Log::info("Found section: {$sectionKey}", [
                            'content' => $sectionContent,
                            'pattern' => $pattern
                        ]);
                    }

                    switch ($sectionInfo['type']) {
                        case 'text':
                            $sections[$sectionKey] = self::cleanText($sectionContent);
                            break;
                        case 'experience':
                            $sections[$sectionKey] = self::parseWorkExperience($sectionContent);
                            break;
                        case 'education':
                            $sections[$sectionKey] = self::parseEducation($sectionContent);
                            break;
                        case 'list':
                            $sections[$sectionKey] = self::parseList($sectionContent);
                            break;
                        case 'certification':
                            $sections[$sectionKey] = self::parseCertification($sectionContent);
                            break;
                    }
                }
            }
        }

        // Extract contact information
        $sections['contact_info'] = self::extractContactInfo($content);

        if (self::$debug) {
            Log::info('Extracted Sections:', ['sections' => $sections]);
        }

        return $sections;
    }

    /**
     * Preprocess content for better parsing
     */
    protected static function preprocessContent(string $content): string
    {
        // Convert multiple newlines to double newlines
        $content = preg_replace('/\n\s*\n\s*\n+/', "\n\n", $content);

        // Convert multiple spaces to single space
        $content = preg_replace('/[ \t]+/', ' ', $content);

        // Normalize section headers
        $content = preg_replace('/^(.*?):\s*$/m', '$1:', $content);

        // Add newlines before common section headers if missing
        $sectionHeaders = [
            'Summary', 'Profile', 'Experience', 'Education', 'Skills',
            'Languages', 'Certifications', 'Projects', 'Awards'
        ];

        foreach ($sectionHeaders as $header) {
            $content = preg_replace(
                '/([^\n])((?:' . $header . '|' . ucfirst($header) . '|' . strtoupper($header) . ')\s*:)/m',
                "$1\n$2",
                $content
            );
        }

        // Remove any null bytes
        $content = str_replace("\0", "", $content);

        // Ensure proper UTF-8 encoding
        if (!mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', mb_detect_encoding($content));
        }

        return trim($content);
    }

    /**
     * Parse work experience entries
     */
    protected static function parseWorkExperience(string $content): array
    {
        $experiences = [];

        // Split into experience blocks
        $blocks = preg_split('/\n(?=\d{4}|(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec))/i', $content);

        foreach ($blocks as $block) {
            if (empty(trim($block))) continue;

            $experience = [
                'job_title' => '',
                'employer' => '',
                'start_date' => '',
                'end_date' => '',
                'city' => '',
                'is_current' => false,
                'description' => ''
            ];

            // Extract dates
            if (preg_match('/(\d{4}|\b(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]*\s+\d{4})\s*(?:-|to|–)\s*(\d{4}|\b(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]*\s+\d{4}|Present|Current)/i', $block, $matches)) {
                $experience['start_date'] = trim($matches[1]);
                $experience['end_date'] = trim($matches[2]);
                $experience['is_current'] = stripos($matches[2], 'Present') !== false || stripos($matches[2], 'Current') !== false;
            }

            // Extract job title, employer, and city
            if (preg_match('/^(.+?)(?:at|with|for)?\s*([^,\n]+)(?:,\s*([^,\n]+))?/i', $block, $matches)) {
                $experience['job_title'] = trim($matches[1]);
                $experience['employer'] = trim($matches[2]);
                // Extract city if it exists in the matches
                if (isset($matches[3])) {
                    $experience['city'] = trim($matches[3]);
                }
            }

            // Try to extract city from a separate line if not found above
            if (empty($experience['city'])) {
                if (preg_match('/\b(?:in|at|located\sin)\s+([^,\n]+(?:,\s*[A-Z]{2})?)/i', $block, $cityMatches)) {
                    $experience['city'] = trim($cityMatches[1]);
                }
            }

            // Extract description
            $description = preg_replace('/^.*?\n/s', '', $block);
            $experience['description'] = self::cleanText($description);

            if (!empty($experience['job_title']) || !empty($experience['employer'])) {
                $experiences[] = $experience;
            }
        }

        return $experiences;
    }

    /**
     * Parse education entries
     */
    protected static function parseEducation(string $content): array
    {
        $education = [];

        // Split into education blocks
        $blocks = preg_split('/\n(?=\d{4}|(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec))/i', $content);

        foreach ($blocks as $block) {
            if (empty(trim($block))) continue;

            $edu = [
                'degree' => '',
                'field_of_study' => '',
                'school' => '',
                'start_date' => '',
                'end_date' => '',
                'is_current' => false
            ];

            // Extract degree and field
            if (preg_match('/(Bachelor|Master|PhD|BSc|BA|MSc|MA|MBA|MD)[\'"]?s?\s+(?:of|in)?\s+([^,\n]+)/i', $block, $matches)) {
                $edu['degree'] = trim($matches[1]);
                $edu['field_of_study'] = trim($matches[2]);
            }

            // Extract school
            if (preg_match('/(?:at|from)\s+([^,\n]+)/i', $block, $matches)) {
                $edu['school'] = trim($matches[1]);
            }

            // Extract dates
            if (preg_match('/(\d{4}|\b(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]*\s+\d{4})\s*(?:-|to|–)\s*(\d{4}|\b(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]*\s+\d{4}|Present|Current)/i', $block, $matches)) {
                $edu['start_date'] = trim($matches[1]);
                $edu['end_date'] = trim($matches[2]);
                $edu['is_current'] = stripos($matches[2], 'Present') !== false || stripos($matches[2], 'Current') !== false;
            }

            if (!empty($edu['degree']) || !empty($edu['school'])) {
                $education[] = $edu;
            }
        }

        return $education;
    }

    protected static function parseCertification(string $content): array
    {
        $certifications = [];

        // Split content into individual certification blocks
        $blocks = preg_split('/\n(?=(?:\d{4}|(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)))/i', $content);

        foreach ($blocks as $block) {
            if (empty(trim($block))) continue;

            $certification = [
                'institution' => '',
                'date' => '',
                'certification_course' => ''
            ];

            // Extract date
            if (preg_match('/(\d{4}|\b(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]*\s+\d{4})/i', $block, $matches)) {
                $certification['date'] = trim($matches[1]);
            }

            // Extract certification course and institution
            // Pattern 1: "Certification Name from Institution"
            if (preg_match('/^(.+?)\s+(?:from|by|at|through)\s+(.+?)(?:\s+|\n|$)/i', $block, $matches)) {
                $certification['certification_course'] = trim($matches[1]);
                $certification['institution'] = trim($matches[2]);
            }
            // Pattern 2: "Institution - Certification Name"
            elseif (preg_match('/^(.+?)\s*[-–]\s*(.+?)(?:\s+|\n|$)/i', $block, $matches)) {
                $certification['institution'] = trim($matches[1]);
                $certification['certification_course'] = trim($matches[2]);
            }
            // Pattern 3: Just certification name (if no clear institution)
            else {
                $lines = array_filter(array_map('trim', explode("\n", $block)));
                if (!empty($lines)) {
                    $certification['certification_course'] = reset($lines);
                }
            }

            // Clean up and add if we have at least a certification name
            if (!empty($certification['certification_course'])) {
                // Remove date information from course name if it was accidentally included
                $certification['certification_course'] = preg_replace('/\b\d{4}\b|\b(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]*\s+\d{4}\b/i', '', $certification['certification_course']);
                $certification['certification_course'] = trim($certification['certification_course']);

                $certifications[] = $certification;
            }
        }

        return $certifications;
    }

    /**
     * Parse list-type sections (skills, languages)
     */
    protected static function parseList(string $content): array
    {
        $items = [];

        // Split by common separators and clean
        $content = preg_replace('/[•·⋅◦∙◾◽▪▫-]\s*/', "\n", $content);
        $lines = preg_split('/[\n,;]+/', $content);

        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line)) {
                $items[] = $line;
            }
        }

        return array_unique($items);
    }

    /**
     * Clean and normalize text
     */
    protected static function cleanText(?string $text): string
    {
        if (empty($text)) {
            return '';
        }

        // Remove HTML tags
        $text = strip_tags($text);

        // Convert HTML entities
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Normalize whitespace
        $text = preg_replace('/\s+/', ' ', $text);

    // Remove bullet points and list markers
        $text = preg_replace('/^[•·⋅◦∙◾◽▪▫-]\s*/', '', $text);

        // Normalize quotes
        $text = str_replace(
            ['«', '»', '“', '”', '‘', '’', '´', '`'],  // characters to replace
            ['"', '"', '"', '"', "'", "'", "'", "'"],  // normalized replacements
            $text
        );

        // Remove any remaining control characters
        $text = preg_replace('/[\x00-\x1F\x7F]/u', '', $text);

        return trim($text);
    }

    /**
     * Extract contact information
     */
    /**
 * Extract contact information from resume content
 *
 * @param string $content The resume content to parse
 * @return array Extracted contact information
 */
    protected static function extractContactInfo(string $content): array
    {
        $contact = [
            'email' => '',
            'phone' => '',
            'address' => '',
            'city' => '',
            'state' => '',
            'country' => '',
            'postal_code' => '',
            // 'linkedin' => ''
        ];

        // Email extraction - handles various email formats
        if (preg_match('/\b[\w\.-]+@[\w\.-]+\.\w{2,}\b/', $content, $matches)) {
            $contact['email'] = strtolower(trim($matches[0]));
        }

        // Phone extraction - handles international and domestic formats
        $phonePatterns = [
            // International format: +1-234-567-8900, +1 (234) 567-8900
            '/(?:\+\d{1,4}[\s.-]?)?\(?\d{3}\)?[\s.-]?\d{3}[\s.-]?\d{4}/',
            // Alternative format: 234.567.8900
            '/\b\d{3}[.-]\d{3}[.-]\d{4}\b/',
            // Basic format: 2345678900
            '/\b\d{10}\b/'
        ];

        foreach ($phonePatterns as $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                $contact['phone'] = preg_replace('/[^\d+]/', '', $matches[0]);
                break;
            }
        }

        // Location extraction - handles various address formats
        $addressPatterns = [
            // Full address with labels
            '/(?:Address|Location|Residence):\s*([^,\n]+)(?:,\s*([^,\n]+))?(?:,\s*([A-Z]{2})\s*)?(?:,?\s*(\d{5}(?:-\d{4})?))?\s*(?:,\s*([^,\n]+))?/i',
            // Address without labels
            '/(\d+[^,\n]+)(?:,\s*([^,\n]+))?(?:,\s*([A-Z]{2})\s*)?(?:,?\s*(\d{5}(?:-\d{4})?))?\s*(?:,\s*([^,\n]+))?/i'
        ];

        foreach ($addressPatterns as $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                $contact['address'] = trim($matches[1]);
                if (!empty($matches[2])) {
                    $contact['city'] = trim($matches[2]);
                }
                if (!empty($matches[3])) {
                    $contact['state'] = strtoupper(trim($matches[3]));
                }
                if (!empty($matches[4])) {
                    $contact['postal_code'] = trim($matches[4]);
                }
                if (!empty($matches[5])) {
                    $contact['country'] = trim($matches[5]);
                }
                break;
            }
        }

        // City and Country fallback - if not found in address
        if (empty($contact['city'])) {
            if (preg_match('/(?:City|Location):\s*([^,\n]+)/i', $content, $matches)) {
                $contact['city'] = trim($matches[1]);
            }
        }

        if (empty($contact['country'])) {
            if (preg_match('/(?:Country):\s*([^,\n]+)/i', $content, $matches)) {
                $contact['country'] = trim($matches[1]);
            }
        }

        // LinkedIn profile extraction
        // if (preg_match('/(?:linkedin\.com\/in\/)([\w-]+)/?/i', $content, $matches)) {
        //     $contact['linkedin'] = 'linkedin.com/in/' . $matches[1];
        // }

        // Clean up empty values
        return array_filter($contact, function($value) {
            return !empty($value);
        });
    }
}
