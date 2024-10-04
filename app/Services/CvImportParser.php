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
            'job_title' => '',
            'contact_info' => [],
            'work_experience' => [],
            'education' => [],
            'skills' => [],
            'certifications' => [],
            'languages' => [],
            'projects' => [],
            'awards' => [],
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
                    '/\b(Professional\s+Summary|Summary|Profile|About\s+Me|Career\s+Objective)\s*[:.]\s*(.*?)(?=\n\s*\b(?:Experience|Education|Skills|Languages|Certifications|Projects|Awards)\b|\z)/is',
                    '/\b(Career\s+Summary|Executive\s+Summary|Personal\s+Statement)\s*[:.]\s*(.*?)(?=\n\s*\b(?:Experience|Education|Skills|Languages|Certifications|Projects|Awards)\b|\z)/is',
                    '/\b(Objective|Professional\s+Profile|Summary\s+of\s+Qualifications)\s*[:.]\s*(.*?)(?=\n\s*\b(?:Experience|Education|Skills|Languages|Certifications|Projects|Awards)\b|\z)/is',
                    '/\b(Career\s+Highlights|Professional\s+Objective|Overview)\s*[:.]\s*(.*?)(?=\n\s*\b(?:Experience|Education|Skills|Languages|Certifications|Projects|Awards)\b|\z)/is'
                ],
                'type' => 'text'
            ],
            'work_experience' => [
                'patterns' => [
                    '/\b(Work\s+Experience|Professional\s+Experience|Employment\s+History|Experience)\s*[:.]\s*(.*?)(?=\n\s*\b(?:Education|Skills|Languages|Certifications|Projects|Awards)\b|\z)/is',
                    '/\b(Employment\s+Experience|Career\s+Experience|Work\s+History)\s*[:.]\s*(.*?)(?=\n\s*\b(?:Education|Skills|Languages|Certifications|Projects|Awards)\b|\z)/is',
                    '/\b(Professional\s+Background|Career\s+History)\s*[:.]\s*(.*?)(?=\n\s*\b(?:Education|Skills|Languages|Certifications|Projects|Awards)\b|\z)/is'
                ],
                'type' => 'experience'
            ],
            'education' => [
                'patterns' => [
                    '/\b(Education|Academic\s+Background|Educational\s+Qualifications)\s*[:.]\s*(.*?)(?=\n\s*\b(?:Experience|Skills|Languages|Certifications|Projects|Awards)\b|\z)/is',
                    '/\b(Academic\s+History|Educational\s+Background|Education\s+Details)\s*[:.]\s*(.*?)(?=\n\s*\b(?:Experience|Skills|Languages|Certifications|Projects|Awards)\b|\z)/is',
                    '/\b(Scholastic\s+Achievements|Academic\s+Qualifications|Educational\s+History)\s*[:.]\s*(.*?)(?=\n\s*\b(?:Experience|Skills|Languages|Certifications|Projects|Awards)\b|\z)/is'
                ],
                'type' => 'education'
            ],
            'skills' => [
                'patterns' => [
                    '/\b(Skills|Technical\s+Skills|Core\s+Competencies|Key\s+Skills)\s*[:.]\s*(.*?)(?=\n\s*\b(?:Experience|Education|Languages|Certifications|Projects|Awards)\b|\z)/is',
                    '/\b(Professional\s+Skills|Key\s+Competencies|Core\s+Skills)\s*[:.]\s*(.*?)(?=\n\s*\b(?:Experience|Education|Languages|Certifications|Projects|Awards)\b|\z)/is',
                    '/\b(Strengths|Key\s+Strengths|Competencies)\s*[:.]\s*(.*?)(?=\n\s*\b(?:Experience|Education|Languages|Certifications|Projects|Awards)\b|\z)/is'
                ],
                'type' => 'list'
            ],
            'languages' => [
                'patterns' => [
                    '/\b(Languages|Language\s+Skills|Language\s+Proficiency)\s*[:.]\s*(.*?)(?=\n\s*\b(?:Experience|Education|Skills|Certifications|Projects|Awards)\b|\z)/is',
                    '/\b(Linguistic\s+Abilities|Languages\s+Spoken|Language\s+Knowledge)\s*[:.]\s*(.*?)(?=\n\s*\b(?:Experience|Education|Skills|Certifications|Projects|Awards)\b|\z)/is'
                ],
                'type' => 'list'
            ],
            'certifications' => [
                'patterns' => [
                    '/\b(Certifications|Licenses|Professional\s+Certifications)\s*[:.]\s*(.*?)(?=\n\s*\b(?:Experience|Education|Skills|Languages|Projects|Awards)\b|\z)/is',
                    '/\b(Licenses\s+and\s+Certifications|Certifications\s+and\s+Training)\s*[:.]\s*(.*?)(?=\n\s*\b(?:Experience|Education|Skills|Languages|Projects|Awards)\b|\z)/is',
                    '/\b(Credentials|Certifications\s+Received|Professional\s+Licenses)\s*[:.]\s*(.*?)(?=\n\s*\b(?:Experience|Education|Skills|Languages|Projects|Awards)\b|\z)/is'
                ],
                'type' => 'list'
            ],
            'projects' => [
                'patterns' => [
                    '/\b(Projects|Project\s+Experience|Relevant\s+Projects)\s*[:.]\s*(.*?)(?=\n\s*\b(?:Experience|Education|Skills|Languages|Certifications|Awards)\b|\z)/is',
                    '/\b(Professional\s+Projects|Major\s+Projects|Notable\s+Projects)\s*[:.]\s*(.*?)(?=\n\s*\b(?:Experience|Education|Skills|Languages|Certifications|Awards)\b|\z)/is'
                ],
                'type' => 'list'
            ],
            'hobbies' => [
                'patterns' => [
                    '/\b(Hobbies|Interests|Leisure\s+Activities)\s*[:.]\s*(.*?)(?=\n\s*\b(?:Experience|Education|Skills|Languages|Certifications|Projects|Awards)\b|\z)/is',
                    '/\b(Personal\s+Interests|Leisure\s+Interests|Pastimes)\s*[:.]\s*(.*?)(?=\n\s*\b(?:Experience|Education|Skills|Languages|Certifications|Projects|Awards)\b|\z)/is'
                ],
                'type' => 'list'
            ],
            'awards' => [
                'patterns' => [
                    '/\b(Awards|Honors|Achievements)\s*[:.]\s*(.*?)(?=\n\s*\b(?:Experience|Education|Skills|Languages|Certifications|Projects|Hobbies)\b|\z)/is',
                    '/\b(Achievements|Recognitions|Awards\s+and\s+Honors)\s*[:.]\s*(.*?)(?=\n\s*\b(?:Experience|Education|Skills|Languages|Certifications|Projects|Hobbies)\b|\z)/is'
                ],
                'type' => 'list'
            ]
        ];


        // Extract each section
        foreach ($sectionMarkers as $sectionKey => $sectionInfo) {
            foreach ($sectionInfo['patterns'] as $pattern) {
                if (preg_match($pattern, $content, $matches)) {
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
                    }
                }
            }
        }

        // Extract job title if not found in sections
        if (empty($sections['job_title'])) {
            if (preg_match('/^([^\n\r]+)/u', $content, $matches)) {
                $sections['job_title'] = self::cleanText($matches[1]);
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
                'is_current' => false,
                'description' => ''
            ];

            // Extract dates
            if (preg_match('/(\d{4}|\b(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]*\s+\d{4})\s*(?:-|to|–)\s*(\d{4}|\b(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]*\s+\d{4}|Present|Current)/i', $block, $matches)) {
                $experience['start_date'] = trim($matches[1]);
                $experience['end_date'] = trim($matches[2]);
                $experience['is_current'] = stripos($matches[2], 'Present') !== false || stripos($matches[2], 'Current') !== false;
            }

            // Extract job title and employer
            if (preg_match('/^(.+?)(?:at|with|for)?\s*([^,\n]+)/i', $block, $matches)) {
                $experience['job_title'] = trim($matches[1]);
                $experience['employer'] = trim($matches[2]);
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
    protected static function extractContactInfo(string $content): array
    {
        $contact = [];

        // Email
        if (preg_match('/[\w\.-]+@[\w\.-]+\.\w+/', $content, $matches)) {
            $contact['email'] = $matches[0];
        }

        // Phone
        if (preg_match('/(?:(?:\+|00)[1-9]\d{0,3}[\s.-]?)?\(?\d{3}\)?[\s.-]?\d{3}[\s.-]?\d{4}/', $content, $matches)) {
            $contact['phone'] = $matches[0];
        }

        // Location/Address
        if (preg_match('/(?:Address|Location):\s*([^,\n]+)(?:,\s*([^,\n]+))?/', $content, $matches)) {
            $contact['address'] = trim($matches[1]);
            if (isset($matches[2])) {
                $contact['city'] = trim($matches[2]);
            }
        }

        return $contact;
    }
}
