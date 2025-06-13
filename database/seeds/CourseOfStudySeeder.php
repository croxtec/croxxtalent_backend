<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CourseOfStudy;

class CourseOfStudySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		// $data = [
		// 	[ 'name' => 'Computer Science', 'description' => null ],
		// 	[ 'name' => 'Computer Engineering', 'description' => null ],
		// 	[ 'name' => 'Production Engineering', 'description' => null ],
		// 	[ 'name' => 'Electrical Engineering', 'description' => null ],
		// 	[ 'name' => 'Mechanical Engineering', 'description' => null ],
		// 	[ 'name' => 'Banking & Finance', 'description' => null ],
		// 	[ 'name' => 'Accountancy', 'description' => null ],
		// 	[ 'name' => 'Chemical Engineer', 'description' => null ],
		// 	[ 'name' => 'Mathematics', 'description' => null ],
		// 	[ 'name' => 'Statistics', 'description' => null ],
		// ];

        $data = [
            // Engineering & Technology
            ['name' => 'Computer Science', 'description' => 'Study of algorithms, computational systems, and computer system design'],
            ['name' => 'Computer Engineering', 'description' => 'Integration of computer science and electrical engineering'],
            ['name' => 'Software Engineering', 'description' => 'Systematic approach to software design, development, and maintenance'],
            ['name' => 'Information Technology', 'description' => 'Application of computers to store, study, retrieve, transmit data'],
            ['name' => 'Data Science', 'description' => 'Interdisciplinary field using scientific methods to extract knowledge from data'],
            ['name' => 'Cybersecurity', 'description' => 'Protection of digital information, systems, and networks'],
            ['name' => 'Artificial Intelligence', 'description' => 'Simulation of human intelligence in machines'],
            ['name' => 'Electrical Engineering', 'description' => 'Engineering discipline dealing with electricity, electronics, and electromagnetism'],
            ['name' => 'Electronics Engineering', 'description' => 'Engineering discipline focusing on electronic circuits and devices'],
            ['name' => 'Mechanical Engineering', 'description' => 'Engineering discipline involving design, analysis, and manufacturing of mechanical systems'],
            ['name' => 'Civil Engineering', 'description' => 'Engineering discipline dealing with infrastructure design and construction'],
            ['name' => 'Chemical Engineering', 'description' => 'Engineering discipline applying chemistry, physics, and math to transform materials'],
            ['name' => 'Production Engineering', 'description' => 'Engineering discipline focused on manufacturing processes and systems'],
            ['name' => 'Industrial Engineering', 'description' => 'Engineering discipline optimizing complex processes and systems'],
            ['name' => 'Petroleum Engineering', 'description' => 'Engineering discipline focused on oil and gas extraction'],
            ['name' => 'Agricultural Engineering', 'description' => 'Engineering discipline applying technology to farming and agriculture'],
            ['name' => 'Biomedical Engineering', 'description' => 'Application of engineering principles to medicine and biology'],
            ['name' => 'Environmental Engineering', 'description' => 'Engineering solutions for environmental protection and sustainability'],

            // Business & Finance
            ['name' => 'Business Administration', 'description' => 'Study of business operations, management, and organizational behavior'],
            ['name' => 'Banking & Finance', 'description' => 'Study of financial systems, banking operations, and investment management'],
            ['name' => 'Accountancy', 'description' => 'Measurement, processing, and communication of financial information'],
            ['name' => 'Economics', 'description' => 'Study of production, distribution, and consumption of goods and services'],
            ['name' => 'Marketing', 'description' => 'Study of market research, advertising, and customer relationship management'],
            ['name' => 'International Business', 'description' => 'Study of business operations across national boundaries'],
            ['name' => 'Financial Economics', 'description' => 'Study of financial markets and investment decisions'],
            ['name' => 'Project Management', 'description' => 'Application of knowledge, skills, and techniques to execute projects'],
            ['name' => 'Supply Chain Management', 'description' => 'Management of goods and services flow from origin to consumption'],
            ['name' => 'Human Resource Management', 'description' => 'Strategic approach to managing employees and workplace culture'],

            // Sciences
            ['name' => 'Mathematics', 'description' => 'Study of numbers, quantities, shapes, and patterns'],
            ['name' => 'Statistics', 'description' => 'Collection, analysis, interpretation, and presentation of data'],
            ['name' => 'Physics', 'description' => 'Study of matter, energy, and their fundamental interactions'],
            ['name' => 'Chemistry', 'description' => 'Study of matter, its properties, composition, and reactions'],
            ['name' => 'Biology', 'description' => 'Study of living organisms and life processes'],
            ['name' => 'Biochemistry', 'description' => 'Study of chemical processes within living organisms'],
            ['name' => 'Microbiology', 'description' => 'Study of microscopic organisms and their effects'],
            ['name' => 'Biotechnology', 'description' => 'Use of biological systems for technological applications'],
            ['name' => 'Environmental Science', 'description' => 'Interdisciplinary study of environmental problems and solutions'],
            ['name' => 'Geology', 'description' => 'Study of Earth, its materials, structures, and processes'],

            // Health Sciences
            ['name' => 'Medicine', 'description' => 'Science and practice of diagnosing, treating, and preventing disease'],
            ['name' => 'Nursing', 'description' => 'Healthcare profession focused on patient care and health promotion'],
            ['name' => 'Pharmacy', 'description' => 'Science and practice of medication preparation and dispensing'],
            ['name' => 'Public Health', 'description' => 'Science of protecting and improving community health'],
            ['name' => 'Medical Laboratory Science', 'description' => 'Analysis of body fluids and tissues for diagnostic purposes'],
            ['name' => 'Physiotherapy', 'description' => 'Treatment of disease and disability through physical methods'],
            ['name' => 'Dentistry', 'description' => 'Branch of medicine focused on oral health'],

            // Social Sciences & Humanities
            ['name' => 'Psychology', 'description' => 'Scientific study of mind and behavior'],
            ['name' => 'Sociology', 'description' => 'Study of society, social relationships, and social behavior'],
            ['name' => 'Political Science', 'description' => 'Study of government systems, political behavior, and public policy'],
            ['name' => 'International Relations', 'description' => 'Study of relationships between countries and global politics'],
            ['name' => 'Law', 'description' => 'System of rules and their interpretation'],
            ['name' => 'English Language', 'description' => 'Study of English language, literature, and communication'],
            ['name' => 'History', 'description' => 'Study of past events and their significance'],
            ['name' => 'Philosophy', 'description' => 'Study of fundamental questions about existence, knowledge, and ethics'],
            ['name' => 'Linguistics', 'description' => 'Scientific study of language and its structure'],

            // Creative Arts & Media
            ['name' => 'Mass Communication', 'description' => 'Study of media, journalism, and public communication'],
            ['name' => 'Graphic Design', 'description' => 'Visual communication through typography, imagery, and layout'],
            ['name' => 'Fine Arts', 'description' => 'Creative arts including painting, sculpture, and drawing'],
            ['name' => 'Music', 'description' => 'Art form using sound and rhythm for expression'],
            ['name' => 'Theatre Arts', 'description' => 'Performing art involving live presentations'],
            ['name' => 'Film Studies', 'description' => 'Academic study of cinema and film production'],

            // Education
            ['name' => 'Education', 'description' => 'Study of teaching methods and educational systems'],
            ['name' => 'Educational Technology', 'description' => 'Use of technology to enhance learning and teaching'],

            // Agriculture & Life Sciences
            ['name' => 'Agriculture', 'description' => 'Science and practice of farming and food production'],
            ['name' => 'Veterinary Medicine', 'description' => 'Medical care of animals and animal health'],
            ['name' => 'Forestry', 'description' => 'Science and craft of managing forests and woodlands'],
            ['name' => 'Food Science', 'description' => 'Study of food composition, processing, and safety'],
        ];

		foreach ($data as $row) {
			CourseOfStudy::updateOrCreate(
				['name' => $row['name']],
				$row
			);
		}
    }
}
