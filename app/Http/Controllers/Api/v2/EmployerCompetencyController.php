<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Employee;
use App\Models\AssesmentSummary;
use App\Models\Assesment;
use App\Models\VettingSummary;
use App\Models\EmployerJobcode as Department;
use  App\Models\Competency\DepartmentMapping;

class EmployerCompetencyController extends Controller
{
    protected static function competencies(){
        return[
            // Human Resources
            ['department' => 'human_resources', 'competency' => 'talent_acquisition', 'competency_role' => 'technical_skill', 'description' => 'Ability to identify, attract, and recruit top talent.'],
            ['department' => 'human_resources', 'competency' => 'employee_relations', 'competency_role' => 'soft_skill', 'description' => 'Managing relationships between the employer and employees.'],
            ['department' => 'human_resources', 'competency' => 'compensation_and_benefits', 'competency_role' => 'technical_skill', 'description' => 'Designing and managing employee compensation packages and benefits.'],
            ['department' => 'human_resources', 'competency' => 'training_and_development', 'competency_role' => 'technical_skill', 'description' => 'Creating and implementing employee training programs.'],
            ['department' => 'human_resources', 'competency' => 'conflict_resolution', 'competency_role' => 'soft_skill', 'description' => 'Effectively resolving disputes and conflicts in the workplace.'],
            // Finance
            ['department' => 'finance', 'competency' => 'financial_analysis', 'competency_role' => 'technical_skill', 'description' => 'Analyzing financial data to assist in decision-making.'],
            ['department' => 'finance', 'competency' => 'budgeting', 'competency_role' => 'technical_skill', 'description' => 'Planning and managing financial resources.'],
            ['department' => 'finance', 'competency' => 'accounting', 'competency_role' => 'technical_skill', 'description' => 'Recording, summarizing, and reporting financial transactions.'],
            ['department' => 'finance', 'competency' => 'financial_reporting', 'competency_role' => 'technical_skill', 'description' => 'Preparing financial statements and reports.'],
            ['department' => 'finance', 'competency' => 'risk_management', 'competency_role' => 'technical_skill', 'description' => 'Identifying and managing financial risks.'],
            // Marketing
            ['department' => 'marketing', 'competency' => 'market_research', 'competency_role' => 'technical_skill', 'description' => 'Collecting and analyzing market data to inform marketing strategies.'],
            ['department' => 'marketing', 'competency' => 'branding', 'competency_role' => 'technical_skill', 'description' => 'Developing and maintaining a brand image.'],
            ['department' => 'marketing', 'competency' => 'digital_marketing', 'competency_role' => 'technical_skill', 'description' => 'Using digital channels to promote products and services.'],
            ['department' => 'marketing', 'competency' => 'content_creation', 'competency_role' => 'technical_skill', 'description' => 'Creating engaging content for various marketing channels.'],
            ['department' => 'marketing', 'competency' => 'public_relations', 'competency_role' => 'soft_skill', 'description' => 'Managing the public image of the company.'],
            // Sales
            ['department' => 'sales', 'competency' => 'prospecting', 'competency_role' => 'technical_skill', 'description' => 'Identifying potential customers and leads.'],
            ['department' => 'sales', 'competency' => 'negotiation', 'competency_role' => 'soft_skill', 'description' => 'Reaching mutually beneficial agreements with customers.'],
            ['department' => 'sales', 'competency' => 'customer_relationship_management', 'competency_role' => 'technical_skill', 'description' => 'Managing and nurturing relationships with customers.'],
            ['department' => 'sales', 'competency' => 'sales_strategy', 'competency_role' => 'technical_skill', 'description' => 'Developing and implementing sales plans and strategies.'],
            ['department' => 'sales', 'competency' => 'closing_deals', 'competency_role' => 'soft_skill', 'description' => 'Successfully closing sales transactions.'],
            // Information Technology
            ['department' => 'information_technology', 'competency' => 'network_administration', 'competency_role' => 'technical_skill', 'description' => 'Managing and maintaining computer networks.'],
            ['department' => 'information_technology', 'competency' => 'software_development', 'competency_role' => 'technical_skill', 'description' => 'Designing and developing software applications.'],
            ['department' => 'information_technology', 'competency' => 'cybersecurity', 'competency_role' => 'technical_skill', 'description' => 'Protecting systems and data from cyber threats.'],
            ['department' => 'information_technology', 'competency' => 'database_management', 'competency_role' => 'technical_skill', 'description' => 'Administering and managing databases.'],
            ['department' => 'information_technology', 'competency' => 'technical_support', 'competency_role' => 'technical_skill', 'description' => 'Providing technical assistance to users.'],
            // Operations
            ['department' => 'operations', 'competency' => 'supply_chain_management', 'competency_role' => 'technical_skill', 'description' => 'Managing the flow of goods and services.'],
            ['department' => 'operations', 'competency' => 'project_management', 'competency_role' => 'technical_skill', 'description' => 'Planning and executing projects efficiently.'],
            ['department' => 'operations', 'competency' => 'process_improvement', 'competency_role' => 'technical_skill', 'description' => 'Enhancing business processes for better efficiency.'],
            ['department' => 'operations', 'competency' => 'inventory_management', 'competency_role' => 'technical_skill', 'description' => 'Tracking and managing inventory levels.'],
            ['department' => 'operations', 'competency' => 'logistics_management', 'competency_role' => 'technical_skill', 'description' => 'Coordinating the movement of goods.'],
            // Customer Service
            ['department' => 'customer_service', 'competency' => 'communication_skills', 'competency_role' => 'soft_skill', 'description' => 'Effectively communicating with customers.'],
            ['department' => 'customer_service', 'competency' => 'problem_solving', 'competency_role' => 'soft_skill', 'description' => 'Resolving customer issues efficiently.'],
            ['department' => 'customer_service', 'competency' => 'customer_satisfaction', 'competency_role' => 'technical_skill', 'description' => 'Ensuring customers are happy with the services provided.'],
            ['department' => 'customer_service', 'competency' => 'product_knowledge', 'competency_role' => 'technical_skill', 'description' => 'Understanding the products or services offered.'],
            ['department' => 'customer_service', 'competency' => 'empathy', 'competency_role' => 'soft_skill', 'description' => 'Understanding and sharing the feelings of customers.'],
            // Research and Development
            ['department' => 'research_and_development', 'competency' => 'innovation', 'competency_role' => 'technical_skill', 'description' => 'Developing new and creative solutions.'],
            ['department' => 'research_and_development', 'competency' => 'product_development', 'competency_role' => 'technical_skill', 'description' => 'Designing and developing new products.'],
            ['department' => 'research_and_development', 'competency' => 'data_analysis', 'competency_role' => 'technical_skill', 'description' => 'Analyzing data to inform R&D decisions.'],
            ['department' => 'research_and_development', 'competency' => 'prototyping', 'competency_role' => 'technical_skill', 'description' => 'Creating prototypes for testing and development.']
        ];
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $competencies = EmployerCompetencyController::competencies();

        // Separate technical and soft skills
        $technical_skills = array_filter($competencies, function($competency) {
            return $competency['competency_role'] === 'technical_skill';
        });

        $soft_skills = array_filter($competencies, function($competency) {
            return $competency['competency_role'] === 'soft_skill';
        });

        // Shuffle the arrays to randomize selection
        shuffle($technical_skills);
        shuffle($soft_skills);

        // Select 6 technical skills and 4 soft skills
        $technical_skills = array_slice($technical_skills, 0, 5);
        $soft_skills = array_slice($soft_skills, 0, 3);

        // Combine selected skills
        // $selected_skills = array_merge($selected_technical_skills, $selected_soft_skills);

        // Filter for competencies in the 'operations' department
        // $operations_competencies = array_filter($competencies, function($competency) {
        //     return $competency['department'] === 'operations';
        // });

        return response()->json([
            'status' => true,
            'data' => compact('technical_skills','soft_skills'),
            'message' => 'Suggested Competency Mapping.'
        ], 200);
    }

    public function storeCompetency(Request $request, $id){
        $employer = $request->user();

        if (is_numeric($id)) {
            $department = Department::where('id', $id)->where('employer_id', $employer->id)
                ->select(['id','job_code', 'job_title', 'description'])->firstOrFail();
        } else {
            $department = Department::where('job_title', $id)->where('employer_id', $employer->id)
                ->select(['id','job_code', 'job_title', 'description'])->firstOrFail();
        }

        $competencies = EmployerCompetencyController::competencies();


        // $this->validate($request, [
        //     'mapping' => 'required|array'
        // ]);
        $mapping = [];

        foreach ($request->mapping as $map) {
            $cp = array_filter($competencies, function($competency) use ($map) {
                return $competency['competency'] === $map;
            });

            // If array_filter returns an array, we need to merge it into $mapping
            if (!empty($cp)) {
                $mapping = array_merge($mapping, $cp);
            }
        }

        if(count($mapping)){
            foreach($mapping as $map){
                DepartmentMapping::firstOrCreate([
                    'employer_id' => $employer->id,
                    'department_id' => $department->id,
                    'competency' => $map['competency'],
                ],[
                    'competency_role' => $map['competency_role'],
                    'description' => $map['description'],
                ]);
            }
        }

        if(isset($employer->onboarding_stage) && $employer->onboarding_stage == 2){
            $employer->onboarding_stage = 3;
            $employer->save();
        }

        return response()->json([
            'status' => true,
            'message' => "Competency matched successfully.",
            'data' => $mapping
        ], 201);

    }

    public function confirmWelcome(Request $request){
        $employer = $request->user();

        if(isset($employer->onboarding_stage) && $employer->onboarding_stage >= 2){
            $employer->onboarding_stage = 4;
            $employer->save();
        }

        return response()->json([
            'status' => true,
            'message' => "Competency matched successfully.",
            'data' => []
        ], 201);

    }
}
