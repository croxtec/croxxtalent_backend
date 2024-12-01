<?php

namespace App\Helpers;

class CVParser
{
    public static function extractSections($content)
    {
        $sections = [
            'first_name' => '',
            'last_name' => '',
            'other_name' => '',
            'gender' => '',
            'date_of_birth' => '',
            'class' => '',
            'career_summary' => '',
            'email' => '',
            'phone' => '',
            'address' => '',
            'contact_address' => '',
            'work_experiences' => '',
            'education' => '',
            'skills' => ''
        ];

        // Regular expressions to match each section
        $patterns = [
            'first_name' => '/First\s*Name[:\s\n]+(.*?)\n(?:\n|$)/is',
            'last_name' => '/Last\s*Name[:\s\n]+(.*?)\n(?:\n|$)/is',
            'other_name' => '/Other\s*Name[:\s\n]+(.*?)\n(?:\n|$)/is',
            'gender' => '/Gender[:\s\n]+(.*?)\n(?:\n|$)/is',
            'date_of_birth' => '/Date\s*of\s*Birth[:\s\n]+(.*?)\n(?:\n|$)/is',
            'class' => '/Class[:\s\n]+(.*?)\n(?:\n|$)/is',
            'career_summary' => '/Career\s*Summary[:\s\n]+(.*?)\n(?:\n|$)/is',
            'email' => '/Email[:\s\n]+(.*?)\n(?:\n|$)/is',
            'phone' => '/Phone[:\s\n]+(.*?)\n(?:\n|$)/is',
            'address' => '/Address[:\s\n]+(.*?)\n(?:\n|$)/is',
            'contact_address' => '/Contact\s*Address[:\s\n]+(.*?)\n(?:\n|$)/is',
            'work_experiences' => '/Work\s*Experiences[:\s\n]+(.*?)\n(?:\n|$)/is',
            'education' => '/Education[:\s\n]+(.*?)\n(?:\n|$)/is',
            'skills' => '/Skills[:\s\n]+(.*?)\n(?:\n|$)/is'
        ];

        foreach ($patterns as $section => $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                $sections[$section] = trim($matches[1]);
            }
        }

        // Normalize paragraphs to be separated by \n\n
        foreach ($sections as $key => $value) {
            $sections[$key] = preg_replace("/\n*/", "", $value);
        }

        return $sections;
    }

    public static function extractResumeSections($content)
    {
        $sections = [
            'summary' => '',
            'job_title' => '',
            'country' => '',
            'work_experience' => [],
            'education' => [],
            'hobbies' => [],
            'awards' => [],
            'certifications' => [],
            'languages' => [],
            'references' => [],
            'projects' => [],
            'skills' => [],
            'interests' => [],
        ];

        // Regular expressions to match each section
        $patterns = [
            'summary' => '/(Summary|Professional\s+Summary|Career\s+Summary|Objective)[:\s\n]+(.*?)(?=\n\n|\n\s*\n|\n\s*$|$)/is',
            'job_title' => '/(Job\s+Title|Position\s+Title|Current\s+Position)[:\s\n]+(.*?)(?=\n\n|\n\s*\n|\n\s*$|$)/is',
            'country' => '/(Country|Location|Residence)[:\s\n]+(.*?)(?=\n\n|\n\s*\n|\n\s*$|$)/is',
            'work_experience' => '/(Work\s+Experience|Professional\s+Experience|Experience|Employment\s+History)[:\s\n]+(.*?)(?=\n\n|\n\s*\n|\n\s*$|$)/is',
            'education' => '/(Education|Academic\s+Background|Educational\s+Qualifications)[:\s\n]+(.*?)(?=\n\n|\n\s*\n|\n\s*$|$)/is',
            'skills' => '/(Skills|Technical\s+Skills|Core\s+Competencies)[:\s\n]+(.*?)(?=\n\n|\n\s*\n|\n\s*$|$)/is',
            'certifications' => '/(Certifications|Licenses)[:\s\n]+(.*?)(?=\n\n|\n\s*\n|\n\s*$|$)/is',
            'projects' => '/(Projects|Project\s+Experience)[:\s\n]+(.*?)(?=\n\n|\n\s*\n|\n\s*$|$)/is',
            'languages' => '/(Languages|Language\s+Proficiency)[:\s\n]+(.*?)(?=\n\n|\n\s*\n|\n\s*$|$)/is',
            'hobbies' => '/(Hobbies|Interests)[:\s\n]+(.*?)(?=\n\n|\n\s*\n|\n\s*$|$)/is',
            'awards' => '/(Awards|Honors)[:\s\n]+(.*?)(?=\n\n|\n\s*\n|\n\s*$|$)/is',
            'references' => '/(References|Referees)[:\s\n]+(.*?)(?=\n\n|\n\s*\n|\n\s*$|$)/is',
            'interests' => '/(Interests|Hobbies)[:\s\n]+(.*?)(?=\n\n|\n\s*\n|\n\s*$|$)/is'
        ];

        foreach ($patterns as $section => $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                $matchContent = trim($matches[2]);
                if (in_array($section, ['job_title', 'summary'])) {
                    $sections[$section] = $matchContent;
                } else {
                    $sections[$section] = array_filter(array_map('trim', preg_split("/\n+/", $matchContent)));
                }
            }
        }

        return $sections;
    }


    public static function extractPersonalDetails($content)
    {
        $details = [
            'first_name' => '',
            'last_name' => '',
            'other_name' => '',
            'gender' => '',
            'date_of_birth' => '',
            'class' => '',
            'email' => '',
            'phone' => '',
            'address' => '',
            'address' => ''
        ];

        // Regular expressions to match each detail
        $patterns = [
            'gender' => '/Gender[:\s\n]+([^\n]+)/i',
            'date_of_birth' => '/Date\s*of\s*Birth[:\s\n]+([^\n]+)/i',
            'address' => '/Address[:\s\n]+([^\n]+)/i',
        ];

        foreach ($patterns as $detail => $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                $details[$detail] = trim($matches[1]);
            }
        }

        return $details;
    }

}
