<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\Skill;
use App\Models\Language;

class CvImportParserCopy
{
        /**
     * Section patterns for different resume formats
     */
    protected static $sectionPatterns = [
        'summary' => [
            'headers' => [
                'Summary',
                'Professional\s+Summary',
                'Career\s+Summary',
                'Profile\s+Summary',
                'Executive\s+Summary',
                'Personal\s+Statement',
                'Objective',
                'About\s+Me',
            ],
            'type' => 'text'
        ],
        'job_title' => [
            'headers' => [
                'Job\s+Title',
                'Position\s+Title',
                'Current\s+Position',
                'Role',
                'Designation',
            ],
            'type' => 'text'
        ],
        'contact_info' => [
            'headers' => [
                'Contact\s+Information',
                'Personal\s+Information',
                'Contact\s+Details',
            ],
            'type' => 'contact'
        ],
        'work_experience' => [
            'headers' => [
                'Work\s+Experience',
                'Professional\s+Experience',
                'Employment\s+History',
                'Career\s+History',
                'Work\s+History',
            ],
            'type' => 'experience'
        ],
        'education' => [
            'headers' => [
                'Education',
                'Academic\s+Background',
                'Educational\s+Qualifications',
                'Academic\s+Qualifications',
                'Educational\s+History',
            ],
            'type' => 'education'
        ],
        'skills' => [
            'headers' => [
                'Skills',
                'Technical\s+Skills',
                'Core\s+Competencies',
                'Key\s+Skills',
                'Professional\s+Skills',
                'Expertise',
            ],
            'type' => 'list'
        ],
        'certifications' => [
            'headers' => [
                'Certifications',
                'Professional\s+Certifications',
                'Licenses',
                'Certificates',
                'Accreditations',
            ],
            'type' => 'certification'
        ],
        'languages' => [
            'headers' => [
                'Languages',
                'Language\s+Skills',
                'Language\s+Proficiency',
            ],
            'type' => 'list'
        ],
        'projects' => [
            'headers' => [
                'Projects',
                'Project\s+Experience',
                'Key\s+Projects',
                'Professional\s+Projects',
            ],
            'type' => 'projects'
        ],
        'awards' => [
            'headers' => [
                'Awards',
                'Honors',
                'Achievements',
                'Recognition',
            ],
            'type' => 'list'
        ],
    ];

    /**
     * Extract sections from resume content
     */
    public static function extractResumeSections($content): array
    {
        // Clean and normalize content
        $content = self::preprocessContent($content);
        info($content);
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

        // Extract sections based on patterns
        foreach (self::$sectionPatterns as $sectionKey => $sectionInfo) {
            $headers = implode('|', $sectionInfo['headers']);
            $pattern = "/(?:^|\n)(?:" . $headers . ")[:\s]*\n+(.*?)(?=\n(?:" . $headers . ")|$)/is";

            if (preg_match($pattern, $content, $matches)) {
                $sectionContent = trim($matches[1]);

                switch ($sectionInfo['type']) {
                    case 'text':
                        $sections[$sectionKey] = self::cleanText($sectionContent);
                        break;

                    case 'contact':
                        $sections[$sectionKey] = self::parseContactInfo($sectionContent);
                        break;

                    case 'experience':
                        $sections[$sectionKey] = self::parseWorkExperience($sectionContent);
                        break;

                    case 'education':
                        $sections[$sectionKey] = self::parseEducation($sectionContent);
                        break;

                    case 'certification':
                        $sections[$sectionKey] = self::parseCertifications($sectionContent);
                        break;

                    case 'list':
                        $sections[$sectionKey] = self::parseList($sectionContent);
                        break;

                    case 'projects':
                        $sections[$sectionKey] = self::parseProjects($sectionContent);
                        break;
                }
            }
        }

        info($sections);
        // Post-process sections
        $sections = self::postProcessSections($sections);
        return $sections;
    }

    /**
     * Preprocess content for better parsing
     */
    protected static function preprocessContent(string $content): string
    {
        // Remove special characters and normalize whitespace
        $content = preg_replace('/[^\p{L}\p{N}\s\.,;:\-\(\)\/\'\"\@\+]/u', ' ', $content);
        $content = preg_replace('/\s+/', ' ', $content);
        $content = str_replace(["\r"], "\n", $content);
        $content = preg_replace('/\n\s+/', "\n", $content);
        $content = preg_replace('/\n{3,}/', "\n\n", $content);

        return trim($content);
    }

    /**
     * Parse contact information
     */
    protected static function parseContactInfo(string $content): array
    {
        $contactInfo = [
            'email' => '',
            'phone' => '',
            'address' => '',
            'country' => '',
            'city' => '',
            // 'linkedin' => '',
        ];

        // Extract email
        if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $content, $matches)) {
            $contactInfo['email'] = $matches[0];
        }

        // Extract phone
        if (preg_match('/(?:\+?\d{1,3}[-.\s]?)?\(?\d{3}\)?[-.\s]?\d{3}[-.\s]?\d{4}/', $content, $matches)) {
            $contactInfo['phone'] = $matches[0];
        }

        // Extract LinkedIn
        // if (preg_match('/linkedin\.com\/in\/[a-zA-Z0-9-]+/', $content, $matches)) {
        //     $contactInfo['linkedin'] = 'https://www.' . $matches[0];
        // }

        // Extract location information
        $locationPatterns = [
            'address' => '/(?:Address|Location):\s*([^,\n]+)(?:,|\n|$)/',
            'city' => '/(?:City):\s*([^,\n]+)(?:,|\n|$)/',
            'country' => '/(?:Country):\s*([^,\n]+)(?:,|\n|$)/',
        ];

        foreach ($locationPatterns as $key => $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                $contactInfo[$key] = trim($matches[1]);
            }
        }

        return array_filter($contactInfo);
    }

    /**
     * Parse work experience
     */
    protected static function parseWorkExperience(string $content): array
    {
        $experiences = [];

        // Split into individual experiences
        $experienceBlocks = preg_split('/\n(?=\d{4}|(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec))/i', $content);

        foreach ($experienceBlocks as $block) {
            if (empty(trim($block))) continue;

            $experience = [
                'job_title' => '',
                'employer' => '',
                'location' => '',
                'start_date' => '',
                'end_date' => '',
                'is_current' => false,
                'description' => '',
            ];

            // Extract dates
            if (preg_match('/(?:(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]*[\s.,]+\d{4}|(?:\d{4}))\s*(?:-|to|–)\s*(?:(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]*[\s.,]+\d{4}|(?:\d{4})|Present|Current)/i', $block, $matches)) {
                $experience['start_date'] = self::parseDate($matches[1]);
                $experience['end_date'] = isset($matches[2]) ? self::parseDate($matches[2]) : null;
                $experience['is_current'] = stripos($matches[0], 'Present') !== false || stripos($matches[0], 'Current') !== false;
            }

            // Extract job title and employer
            if (preg_match('/^(.*?)(?:at|with|for)?\s*([^,\n]+)(?:,|\n|$)/i', $block, $matches)) {
                $experience['job_title'] = trim($matches[1]);
                $experience['employer'] = trim($matches[2]);
            }

            // Extract location
            if (preg_match('/(?:in|at)\s+([^,\n]+)(?:,|\n|$)/i', $block, $matches)) {
                $experience['location'] = trim($matches[1]);
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
     * Parse education information
     */
    protected static function parseEducation(string $content): array
    {
        $education = [];

        $educationBlocks = preg_split('/\n(?=\d{4}|(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec))/i', $content);

        foreach ($educationBlocks as $block) {
            if (empty(trim($block))) continue;

            $edu = [
                'degree' => '',
                'field_of_study' => '',
                'school' => '',
                'location' => '',
                'start_date' => '',
                'end_date' => '',
                'is_current' => false,
                'description' => '',
            ];

            // Extract dates
            if (preg_match('/(?:(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]*[\s.,]+\d{4}|(?:\d{4}))\s*(?:-|to|–)\s*(?:(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]*[\s.,]+\d{4}|(?:\d{4})|Present|Current)/i', $block, $matches)) {
                $edu['start_date'] = self::parseDate($matches[1]);
                $edu['end_date'] = isset($matches[2]) ? self::parseDate($matches[2]) : null;
                $edu['is_current'] = stripos($matches[0], 'Present') !== false || stripos($matches[0], 'Current') !== false;
            }

            // Extract degree and field of study
            if (preg_match('/(Bachelor|Master|PhD|Doctorate|BSc|BA|MSc|MA|MBA|MD|Ph\.D\.).+?(in|of)?\s+([^,\n]+)/i', $block, $matches)) {
                $edu['degree'] = trim($matches[1]);
                $edu['field_of_study'] = trim($matches[3]);
            }

            // Extract school
            if (preg_match('/(?:at|from)\s+([^,\n]+)(?:,|\n|$)/i', $block, $matches)) {
                $edu['school'] = trim($matches[1]);
            }

            // Extract location
            if (preg_match('/(?:in|at)\s+([^,\n]+)(?:,|\n|$)/i', $block, $matches)) {
                $edu['location'] = trim($matches[1]);
            }

            if (!empty($edu['degree']) || !empty($edu['school'])) {
                $education[] = $edu;
            }
        }

        return $education;
    }

    /**
     * Parse certifications
     */
    protected static function parseCertifications(string $content): array
    {
        $certifications = [];

        $certificationBlocks = preg_split('/\n(?=\d{4}|(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec))/i', $content);

        foreach ($certificationBlocks as $block) {
            if (empty(trim($block))) continue;

            $cert = [
                'name' => '',
                'institution' => '',
                'date' => '',
                'expiry_date' => null,
            ];

            // Extract certification name
            if (preg_match('/^([^,\n]+)/', $block, $matches)) {
                $cert['name'] = trim($matches[1]);
            }

            // Extract institution
            if (preg_match('/(?:from|by|through)\s+([^,\n]+)(?:,|\n|$)/i', $block, $matches)) {
                $cert['institution'] = trim($matches[1]);
            }

            // Extract dates
            if (preg_match('/(?:(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]*[\s.,]+\d{4}|(?:\d{4}))\s*(?:-|to|–)\s*(?:(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]*[\s.,]+\d{4}|(?:\d{4})|Present|Current)?/i', $block, $matches)) {
                $cert['date'] = self::parseDate($matches[1]);
                $cert['expiry_date'] = isset($matches[2]) ? self::parseDate($matches[2]) : null;
            }

            if (!empty($cert['name'])) {
                $certifications[] = $cert;
            }
        }

        return $certifications;
    }

    /**
     * Parse list-type sections (skills, languages, etc.)
     */
    protected static function parseList(string $content): array
    {
        $items = [];

        // Split by common list separators
        $content = preg_replace('/[•·⋅◦∙◾◽▪▫-]\s*/', "\n", $content);
        $lines = preg_split('/[\n,;]+/', $content);

        foreach ($lines as $line) {
            $item = trim($line);
            if (!empty($item)) {
                // Check for skill level indicators
                if (preg_match('/^(.*?)\s*(?:\(([^)]+)\)|:([^:]+))$/', $item, $matches)) {
                    $items[] = [
                        'name' => trim($matches[1]),
                        'level' => trim($matches[2] ?? $matches[3])
                    ];
                } else {
                    $items[] = [
                        'name' => $item,
                        'level' => null
                    ];
                }
            }
        }

        return $items;
    }

    /**
     * Parse projects section
     */
    protected static function parseProjects(string $content): array
    {
        $projects = [];

        // Split into project blocks
        $projectBlocks = preg_split('/\n(?=Project|Assignment):/i', $content);

        foreach ($projectBlocks as $block) {
            if (empty(trim($block))) continue;

            $project = [
                'name' => '',
                'role' => '',
                'duration' => '',
                'technologies' => [],
                'description' => '',
            ];

            // Extract project name
            if (preg_match('/^(?:Project|Assignment)?:?\s*([^:\n]+)/i', $block, $matches)) {
                $project['name'] = trim($matches[1]);
            }

            // Extract role if present
            if (preg_match('/Role:\s*([^:\n]+)/i', $block, $matches)) {
                $project['role'] = trim($matches[1]);
            }

            // Extract duration
            if (preg_match('/Duration:\s*([^:\n]+)/i', $block, $matches)) {
                $project['duration'] = trim($matches[1]);
            }

            // Extract technologies
            if (preg_match('/Technologies?(?:\s+used)?:\s*([^:\n]+)/i', $block, $matches)) {
                $techs = explode(',', $matches[1]);
                $project['technologies'] = array_map('trim', $techs);
            }

            // Extract description
            $description = preg_replace('/^.*?\n/s', '', $block);
            $project['description'] = self::cleanText($description);

            if (!empty($project['name']) || !empty($project['description'])) {
                $projects[] = $project;
            }
        }

        return $projects;
    }

    /**
     * Clean and normalize text content
     */
    protected static function cleanText(string $text): string
    {
        // Remove excessive whitespace (collapse multiple spaces into one)
        $text = preg_replace('/\s+/', ' ', $text);

        // Remove bullet points and list markers at the beginning of the line
        $text = preg_replace('/^[•·⋅◦∙◾◽▪▫\-]\s*/m', '', $text); // 'm' flag to handle multiple lines

        // Standardize quotes (replace smart quotes with regular quotes)
        $text = str_replace(
            ['“', '”', '‘', '’'],
            ['"', '"', "'", "'"],
            $text
        );

        return trim($text);
    }


    /**
     * Parse date strings into standard format
     */
    protected static function parseDate($dateStr): ?string
    {
        if (empty($dateStr)) return null;

        try {
            // Handle year-only dates
            if (preg_match('/^\d{4}$/', $dateStr)) {
                return $dateStr . '-01-01';
            }

            // Parse full dates
            $date = Carbon::parse($dateStr);
            return $date->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Post-process extracted sections
     */
    protected static function postProcessSections(array $sections): array
    {
        // Process skills
        if (!empty($sections['skills'])) {
            $sections['skills'] = self::processSkills($sections['skills']);
        }

        // Process languages
        if (!empty($sections['languages'])) {
            $sections['languages'] = self::processLanguages($sections['languages']);
        }

        // Clean up any empty sections
        $sections = array_filter($sections, function ($value) {
            return !empty($value) && $value !== [''];
        });

        return $sections;
    }

    /**
     * Process and standardize skills
     */
    protected static function processSkills(array $skills): array
    {
        $processedSkills = [];
        $skillLevels = [
            'beginner' => ['basic', 'beginner', 'elementary', 'novice'],
            'intermediate' => ['intermediate', 'working', 'competent'],
            'advanced' => ['advanced', 'expert', 'proficient', 'master'],
        ];

        foreach ($skills as $skill) {
            $skillName = $skill['name'];
            $level = strtolower($skill['level'] ?? '');

            // Standardize skill level
            $standardLevel = 'intermediate'; // default level
            foreach ($skillLevels as $standardName => $variants) {
                if (Str::contains($level, $variants)) {
                    $standardLevel = $standardName;
                    break;
                }
            }

            $processedSkills[] = [
                'name' => $skillName,
                'level' => $standardLevel
            ];
        }

        return $processedSkills;
    }

    /**
     * Process and standardize languages
     */
    protected static function processLanguages(array $languages): array
    {
        $processedLanguages = [];
        $languageLevels = [
            'basic' => ['basic', 'beginner', 'elementary', 'a1', 'a2'],
            'intermediate' => ['intermediate', 'working', 'b1', 'b2'],
            'fluent' => ['fluent', 'advanced', 'proficient', 'native', 'c1', 'c2'],
        ];

        foreach ($languages as $language) {
            $langName = $language['name'];
            $level = strtolower($language['level'] ?? '');

            // Standardize language level
            $standardLevel = 'intermediate'; // default level
            foreach ($languageLevels as $standardName => $variants) {
                if (Str::contains($level, $variants)) {
                    $standardLevel = $standardName;
                    break;
                }
            }

            // Match with system languages if available
            $systemLanguage = Language::where('name', 'LIKE', "%{$langName}%")->first();

            $processedLanguages[] = [
                'name' => $systemLanguage ? $systemLanguage->name : $langName,
                'level' => $standardLevel,
                'language_id' => $systemLanguage ? $systemLanguage->id : null
            ];
        }

        return $processedLanguages;
    }

    /**
     * Extract contact details from text
     */
    protected static function extractContactDetails(string $text): array
    {
        $contact = [];

        // Email pattern
        if (preg_match('/[\w\.-]+@[\w\.-]+\.\w+/', $text, $matches)) {
            $contact['email'] = $matches[0];
        }

        // Phone pattern (international format)
        if (preg_match('/(?:\+\d{1,3}[\s-]?)?\(?\d{3}\)?[\s-]?\d{3}[\s-]?\d{4}/', $text, $matches)) {
            $contact['phone'] = $matches[0];
        }

        // Location pattern
        if (preg_match('/(?:Location|Address):\s*([^,\n]+)(?:,\s*([^,\n]+))?(?:,\s*([^,\n]+))?/', $text, $matches)) {
            $contact['address'] = trim($matches[1]);
            if (isset($matches[2])) $contact['city'] = trim($matches[2]);
            if (isset($matches[3])) $contact['country'] = trim($matches[3]);
        }

        // LinkedIn profile
        // if (preg_match('/(?:linkedin\.com\/in\/|LinkedIn:?\s*)([^\s\/]+)/', $text, $matches)) {
        //     $contact['linkedin'] = 'https://www.linkedin.com/in/' . trim($matches[1], '/');
        // }

        return $contact;
    }
}
