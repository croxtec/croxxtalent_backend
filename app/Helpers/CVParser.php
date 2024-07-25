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
            'personal_details' => '',
            'summary' => '',
            'work_experience' => '',
            'education' => '',
            'skills' => '',
            'certifications' => '',
            'projects' => '',
            'languages' => '',
            'interests' => ''
        ];

        // Regular expressions to match each section
        $patterns = [
            'personal_details' => '/(Personal\s+Details|Contact\s+Information|Contact\s+Details|Personal\s+Information)[:\s\n]+(.*?)(?=\n\n|\n\s*\n|\n\s*$|$)/is',
            'summary' => '/(Summary|Professional\s+Summary|Career\s+Summary|Objective)[:\s\n]+(.*?)(?=\n\n|\n\s*\n|\n\s*$|$)/is',
            'work_experience' => '/(Work\s+Experience|Professional\s+Experience|Experience|Employment\s+History)[:\s\n]+(.*?)(?=\n\n|\n\s*\n|\n\s*$|$)/is',
            'education' => '/(Education|Academic\s+Background|Educational\s+Qualifications)[:\s\n]+(.*?)(?=\n\n|\n\s*\n|\n\s*$|$)/is',
            'skills' => '/(Skills|Technical\s+Skills|Core\s+Competencies)[:\s\n]+(.*?)(?=\n\n|\n\s*\n|\n\s*$|$)/is',
            'certifications' => '/(Certifications|Licenses)[:\s\n]+(.*?)(?=\n\n|\n\s*\n|\n\s*$|$)/is',
            'projects' => '/(Projects|Project\s+Experience)[:\s\n]+(.*?)(?=\n\n|\n\s*\n|\n\s*$|$)/is',
            'languages' => '/(Languages|Language\s+Proficiency)[:\s\n]+(.*?)(?=\n\n|\n\s*\n|\n\s*$|$)/is',
            'interests' => '/(Interests|Hobbies)[:\s\n]+(.*?)(?=\n\n|\n\s*\n|\n\s*$|$)/is'
        ];

        foreach ($patterns as $section => $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                $sections[$section] = trim($matches[2]);
            }
        }

        // Normalize paragraphs to be separated by \n\n
        foreach ($sections as $key => $value) {
            $sections[$key] = preg_replace("/\n+/", "\n\n", $value);
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
            'contact_address' => ''
        ];

        // Regular expressions to match each detail
        $patterns = [
            'first_name' => '/First\s*Name[:\s\n]+([^\n]+)/i',
            'last_name' => '/Last\s*Name[:\s\n]+([^\n]+)/i',
            'other_name' => '/Other\s*Name[:\s\n]+([^\n]+)/i',
            'gender' => '/Gender[:\s\n]+([^\n]+)/i',
            'date_of_birth' => '/Date\s*of\s*Birth[:\s\n]+([^\n]+)/i',
            'class' => '/Class[:\s\n]+([^\n]+)/i',
            'email' => '/Email[:\s\n]+([^\s\n]+)/i',
            'phone' => '/Phone[:\s\n]+([^\s\n]+)/i',
            'address' => '/Address[:\s\n]+([^\n]+)/i',
            'contact_address' => '/Contact\s*Address[:\s\n]+([^\n]+)/i'
        ];

        foreach ($patterns as $detail => $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                $details[$detail] = trim($matches[1]);
            }
        }

        return $details;
    }
}
