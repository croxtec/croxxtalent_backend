<?php

namespace App\Helpers;

use App\Models\Skill;
use App\Models\SkillSecondary as Secondary;
use App\Models\SkillTertiary as Tertiary;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Models\Employee;
use App\Mail\WelcomeEmployee;
use Illuminate\Support\Facades\Mail;
use App\Models\Verification;
use App\Models\EmployerJobcode as Department;
use App\Models\DepartmentRole;
use App\Models\Supervisor;

class EmployeeImport implements ToModel, WithHeadingRow
{
    protected $employer;

    // Valid options for level and gender
    private const VALID_LEVELS = ['beginner', 'intermediate', 'advance', 'expert'];
    private const VALID_GENDERS = ['male', 'female', 'others'];
    private const DEFAULT_LEVEL = 'intermediate';
    private const DEFAULT_GENDER = 'others';

    // Level mapping for different company systems
    private const LEVEL_MAPPINGS = [
        // Numeric levels (1-5 scale)
        '1' => 'beginner',
        '2' => 'intermediate',
        '3' => 'intermediate',
        '4' => 'advance',
        '5' => 'expert',

        // Numeric levels (1-10 scale)
        '6' => 'expert',
        '7' => 'expert',
        '8' => 'expert',
        '9' => 'expert',
        '10' => 'expert',

        // Common alternative terms
        'entry' => 'beginner',
        'entry-level' => 'beginner',
        'entry level' => 'beginner',
        'junior' => 'beginner',
        'trainee' => 'beginner',
        'apprentice' => 'beginner',
        'fresher' => 'beginner',
        'new' => 'beginner',
        'level 1' => 'beginner',
        'l1' => 'beginner',

        'mid' => 'intermediate',
        'middle' => 'intermediate',
        'mid-level' => 'intermediate',
        'mid level' => 'intermediate',
        'regular' => 'intermediate',
        'standard' => 'intermediate',
        'associate' => 'intermediate',
        'level 2' => 'intermediate',
        'level 3' => 'intermediate',
        'l2' => 'intermediate',
        'l3' => 'intermediate',

        'advanced' => 'advance',
        'senior' => 'advance',
        'experienced' => 'advance',
        'specialist' => 'advance',
        'professional' => 'advance',
        'level 4' => 'advance',
        'l4' => 'advance',

        'master' => 'expert',
        'lead' => 'expert',
        'principal' => 'expert',
        'chief' => 'expert',
        'architect' => 'expert',
        'consultant' => 'expert',
        'director' => 'expert',
        'manager' => 'expert',
        'head' => 'expert',
        'level 5' => 'expert',
        'l5' => 'expert',

        // Percentage-based levels
        '0-25%' => 'beginner',
        '26-50%' => 'intermediate',
        '51-75%' => 'advance',
        '76-100%' => 'expert',

        // Years of experience based
        '0-1 years' => 'beginner',
        '0-2 years' => 'beginner',
        '1-3 years' => 'intermediate',
        '2-5 years' => 'intermediate',
        '3-7 years' => 'advance',
        '5+ years' => 'advance',
        '7+ years' => 'expert',
        '10+ years' => 'expert',
    ];
    private const DEFAULT_PHOTO_URL = 'https://res.cloudinary.com/dwty1bg7o/image/upload/v1721470055/l199zpjiq1t23uroq7g7ki1xi20hh_kwfrhy.png';

    public function __construct($employer)
    {
        $this->employer = $employer;
    }

    /**
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        try {
            // Extract and validate row data
            $rowData = $this->extractRowData($row);

            // Skip invalid rows silently
            if (!$this->isValidRow($rowData)) {
                return null;
            }

            // Check if employee already exists
            $existingEmployee = $this->findExistingEmployee($rowData['email']);

            if ($existingEmployee) {
                // Handle supervisor assignment for existing employee
                $this->handleSupervisorAssignment($existingEmployee, $rowData['supervisor']);
                return $existingEmployee;
            }

            // Create new employee
            return $this->createNewEmployee($rowData);

        } catch (\Exception $e) {
            // Log error but don't throw to avoid breaking the import
            Log::error('Employee import error: ' . $e->getMessage(), [
                'row' => $row,
                'employer_id' => $this->employer->id
            ]);
            return null;
        }
    }

    /**
     * Extract and sanitize data from row
     */
    private function extractRowData(array $row): array
    {
        return [
            'name' => $this->sanitizeString($row['employee_name'] ?? null),
            'email' => $this->sanitizeEmail($row['official_email'] ?? null),
            'phone' => $this->sanitizeString($row['phone'] ?? null),
            'department' => $this->sanitizeString($row['department'] ?? null),
            'role' => $this->sanitizeString($row['job_rolecode'] ?? null),
            'level' => $this->validateLevel($row['level'] ?? null),
            'location' => $this->sanitizeString($row['Location'] ?? null),
            'supervisor' => $this->sanitizeString($row['supervisor'] ?? null),
            'gender' => $this->validateGender($row['gender'] ?? null),
            'work_type' => $this->sanitizeString($row['work_type'] ?? null),
        ];
    }

    /**
     * Validate if row has required fields
     */
    private function isValidRow(array $rowData): bool
    {
        $requiredFields = ['name', 'email', 'phone', 'department', 'role', 'level'];

        foreach ($requiredFields as $field) {
            if (empty($rowData[$field])) {
                return false;
            }
        }

        return $this->isValidEmail($rowData['email']);
    }

    /**
     * Find existing employee by email
     */
    private function findExistingEmployee(string $email): ?Employee
    {
        return Employee::where([
            'employer_id' => $this->employer->id,
            'email' => $email,
        ])->first();
    }

    /**
     * Create new employee with all related data
     */
    private function createNewEmployee(array $rowData): ?Employee
    {
        // Prepare employee data
        $employeeData = [
            'employer_id' => $this->employer->id,
            'name' => $rowData['name'],
            'email' => $rowData['email'],
            'phone' => $rowData['phone'],
            'location' => $rowData['location'],
            'work_type' => strtolower($rowData['work_type'] ?? ''),
            'level' => $rowData['level'],
            'gender' => $rowData['gender'],
            'photo_url' => self::DEFAULT_PHOTO_URL
        ];

        // Handle department
        if (!empty($rowData['department'])) {
            $department = $this->getOrCreateDepartment($rowData['department']);
            $employeeData['job_code_id'] = $department->id;

            // Handle department role
            if (!empty($rowData['role'])) {
                $departmentRole = $this->getOrCreateDepartmentRole($rowData['role'], $department->id);
                $employeeData['department_role_id'] = $departmentRole->id;
            }
        }

        // Create employee
        $employee = Employee::create($employeeData);

        if ($employee) {
            // Send welcome email
            $this->sendWelcomeEmail($employee);

            // Handle supervisor assignment
            $this->handleSupervisorAssignment($employee, $rowData['supervisor'], $employeeData['job_code_id'] ?? null);
        }

        return $employee;
    }

    /**
     * Get or create department
     */
    private function getOrCreateDepartment(string $departmentCode): Department
    {
        return Department::firstOrCreate([
            'employer_id' => $this->employer->id,
            'job_code' => $departmentCode
        ]);
    }

    /**
     * Get or create department role
     */
    private function getOrCreateDepartmentRole(string $roleName, int $departmentId): DepartmentRole
    {
        return DepartmentRole::firstOrCreate([
            'employer_id' => $this->employer->id,
            'department_id' => $departmentId,
            'name' => $roleName
        ]);
    }

    /**
     * Send welcome email to new employee
     */
    private function sendWelcomeEmail(Employee $employee): void
    {
        try {
            $verification = new Verification();
            $verification->action = "employee";
            $verification->sent_to = $employee->email;
            $verification->metadata = null;
            $verification->is_otp = false;

            $verification = $employee->verifications()->save($verification);

            if ($verification) {
                Mail::to($employee->email)->queue(new WelcomeEmployee($employee, $this->employer, $verification));
            }
        } catch (\Exception $e) {
            Log::error('Failed to send welcome email', [
                'employee_id' => $employee->id,
                'email' => $employee->email,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle supervisor assignment
     */
    private function handleSupervisorAssignment(Employee $employee, ?string $supervisorFlag, ?int $departmentId = null): void
    {
        if (empty($supervisorFlag) || strtolower(trim($supervisorFlag)) !== 'yes') {
            return;
        }

        $departmentId = $departmentId ?? $employee->job_code_id;

        // Check if already a supervisor
        $existingSupervisor = Supervisor::where('supervisor_id', $employee->id)
            ->where('employer_id', $this->employer->id)
            ->first();

        if (!$existingSupervisor && $departmentId) {
            $supervisor = Supervisor::create([
                'type' => 'department',
                'employer_id' => $this->employer->id,
                'supervisor_id' => $employee->id,
                'department_id' => $departmentId
            ]);

            // Update employee's supervisor_id only for new employees
            if (!$employee->supervisor_id) {
                $employee->supervisor_id = $supervisor->id;
                $employee->save();
            }
        }
    }

    /**
     * Validate and normalize level with intelligent mapping
     */
    private function validateLevel(?string $level): string
    {
        if (empty($level)) {
            return self::DEFAULT_LEVEL;
        }

        $originalLevel = trim($level);
        $normalizedLevel = strtolower($originalLevel);

        // Check if it's already a valid level
        if (in_array($normalizedLevel, self::VALID_LEVELS)) {
            return $normalizedLevel;
        }

        // Check direct mappings
        if (isset(self::LEVEL_MAPPINGS[$normalizedLevel])) {
            return self::LEVEL_MAPPINGS[$normalizedLevel];
        }

        // Try to extract numeric value for percentage or numeric levels
        if (preg_match('/(\d+)/', $normalizedLevel, $matches)) {
            $numericValue = (int) $matches[1];

            // Handle percentage values (0-100)
            if ($numericValue <= 100 && (strpos($normalizedLevel, '%') !== false || $numericValue > 10)) {
                if ($numericValue <= 25) return 'beginner';
                if ($numericValue <= 50) return 'intermediate';
                if ($numericValue <= 75) return 'advance';
                return 'expert';
            }

            // Handle 1-10 scale
            if ($numericValue >= 1 && $numericValue <= 10) {
                if ($numericValue <= 2) return 'beginner';
                if ($numericValue <= 5) return 'intermediate';
                if ($numericValue <= 8) return 'advance';
                return 'expert';
            }
        }

        // Try fuzzy matching for common variations
        $mappedLevel = $this->fuzzyLevelMatch($normalizedLevel);
        if ($mappedLevel) {
            return $mappedLevel;
        }

        // Log unrecognized level for future improvement
        Log::info('Unrecognized level value defaulted to intermediate', [
            'original_level' => $originalLevel,
            'employer_id' => $this->employer->id
        ]);

        return self::DEFAULT_LEVEL;
    }

    /**
     * Fuzzy matching for level variations
     */
    private function fuzzyLevelMatch(string $level): ?string
    {
        // Common words that indicate each level
        $levelKeywords = [
            'beginner' => ['begin', 'start', 'new', 'fresh', 'entry', 'junior', 'trainee', 'novice', 'basic'],
            'intermediate' => ['inter', 'middle', 'mid', 'regular', 'standard', 'moderate', 'average'],
            'advance' => ['adv', 'senior', 'experienced', 'skilled', 'proficient', 'competent'],
            'expert' => ['exp', 'master', 'lead', 'principal', 'specialist', 'professional', 'guru', 'ninja']
        ];

        foreach ($levelKeywords as $standardLevel => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($level, $keyword) !== false) {
                    return $standardLevel;
                }
            }
        }

        return null;
    }

    /**
     * Validate and normalize gender
     */
    private function validateGender(?string $gender): string
    {
        if (empty($gender)) {
            return self::DEFAULT_GENDER;
        }

        $normalizedGender = strtolower(trim($gender));
        return in_array($normalizedGender, self::VALID_GENDERS) ? $normalizedGender : self::DEFAULT_GENDER;
    }

    /**
     * Sanitize string input
     */
    private function sanitizeString(?string $input): ?string
    {
        return !empty($input) ? trim($input) : null;
    }

    /**
     * Sanitize and validate email
     */
    private function sanitizeEmail(?string $email): ?string
    {
        if (empty($email)) {
            return null;
        }

        $email = trim(strtolower($email));
        return $this->isValidEmail($email) ? $email : null;
    }

    /**
     * Validate email format
     */
    private function isValidEmail(?string $email): bool
    {
        return !empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}