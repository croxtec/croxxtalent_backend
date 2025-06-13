<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Industry;

class IndustrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		// $data = [
		// 	[ 'name' => 'Oil & Gas', 'description' => null ],
		// 	[ 'name' => 'IT & Telecoms', 'description' => null ],
		// 	[ 'name' => 'Mining, Energy & Metals', 'description' => null ],
		// 	[ 'name' => 'Construction', 'description' => null ],
		// 	[ 'name' => 'Finance', 'description' => null ],
		// ];

        $data = [
            // Technology & Digital
            ['name' => 'Information Technology', 'description' => 'Software development, IT services, and digital solutions'],
            ['name' => 'IT & Telecoms', 'description' => 'Information technology and telecommunications services'],
            ['name' => 'Software Development', 'description' => 'Design, development, and maintenance of software applications'],
            ['name' => 'Cybersecurity', 'description' => 'Protection of digital systems and information security'],
            ['name' => 'Fintech', 'description' => 'Financial technology and digital payment solutions'],
            ['name' => 'E-commerce', 'description' => 'Online retail and digital marketplace platforms'],
            ['name' => 'Gaming & Entertainment', 'description' => 'Video game development and digital entertainment'],
            ['name' => 'Digital Marketing', 'description' => 'Online marketing, social media, and digital advertising'],

            // Finance & Banking
            ['name' => 'Banking', 'description' => 'Commercial banking, investment banking, and financial services'],
            ['name' => 'Finance', 'description' => 'Financial planning, investment management, and capital markets'],
            ['name' => 'Insurance', 'description' => 'Risk management and insurance services'],
            ['name' => 'Microfinance', 'description' => 'Small-scale financial services for underserved populations'],
            ['name' => 'Investment Management', 'description' => 'Portfolio management and investment advisory services'],

            // Energy & Natural Resources
            ['name' => 'Oil & Gas', 'description' => 'Exploration, production, and distribution of petroleum products'],
            ['name' => 'Renewable Energy', 'description' => 'Solar, wind, and other sustainable energy solutions'],
            ['name' => 'Mining, Energy & Metals', 'description' => 'Extraction and processing of natural resources'],
            ['name' => 'Power Generation', 'description' => 'Electricity generation and power distribution'],
            ['name' => 'Water Management', 'description' => 'Water treatment, distribution, and conservation'],

            // Manufacturing & Industrial
            ['name' => 'Manufacturing', 'description' => 'Production of goods and industrial equipment'],
            ['name' => 'Automotive', 'description' => 'Vehicle manufacturing and automotive services'],
            ['name' => 'Aerospace', 'description' => 'Aircraft and spacecraft design and manufacturing'],
            ['name' => 'Textiles', 'description' => 'Fabric production and garment manufacturing'],
            ['name' => 'Food & Beverage', 'description' => 'Food processing, production, and beverage manufacturing'],
            ['name' => 'Pharmaceuticals', 'description' => 'Drug development, manufacturing, and distribution'],
            ['name' => 'Chemical Industry', 'description' => 'Chemical production and processing'],

            // Construction & Real Estate
            ['name' => 'Construction', 'description' => 'Building construction, infrastructure development, and engineering'],
            ['name' => 'Real Estate', 'description' => 'Property development, sales, and management'],
            ['name' => 'Architecture', 'description' => 'Building design and architectural services'],
            ['name' => 'Urban Planning', 'description' => 'City planning and urban development'],

            // Healthcare & Life Sciences
            ['name' => 'Healthcare', 'description' => 'Medical services, hospitals, and healthcare delivery'],
            ['name' => 'Biotechnology', 'description' => 'Biological research and biotechnological applications'],
            ['name' => 'Medical Devices', 'description' => 'Medical equipment design and manufacturing'],
            ['name' => 'Health Technology', 'description' => 'Digital health solutions and medical technology'],

            // Agriculture & Food
            ['name' => 'Agriculture', 'description' => 'Farming, crop production, and agricultural services'],
            ['name' => 'Agribusiness', 'description' => 'Commercial agricultural enterprises and food systems'],
            ['name' => 'Fisheries', 'description' => 'Fish farming and marine resource management'],
            ['name' => 'Livestock', 'description' => 'Animal husbandry and livestock production'],

            // Transportation & Logistics
            ['name' => 'Transportation', 'description' => 'Passenger and freight transportation services'],
            ['name' => 'Logistics', 'description' => 'Supply chain management and distribution'],
            ['name' => 'Aviation', 'description' => 'Air transportation and aviation services'],
            ['name' => 'Maritime', 'description' => 'Shipping, ports, and marine transportation'],

            // Media & Communications
            ['name' => 'Media & Broadcasting', 'description' => 'Television, radio, and digital media content'],
            ['name' => 'Publishing', 'description' => 'Book, magazine, and digital content publishing'],
            ['name' => 'Advertising', 'description' => 'Marketing communications and brand promotion'],

            // Education & Training
            ['name' => 'Education', 'description' => 'Schools, universities, and educational services'],
            ['name' => 'Training & Development', 'description' => 'Professional training and skill development'],
            ['name' => 'EdTech', 'description' => 'Educational technology and online learning platforms'],

            // Professional Services
            ['name' => 'Consulting', 'description' => 'Management, strategy, and specialized consulting services'],
            ['name' => 'Legal Services', 'description' => 'Law firms and legal advisory services'],
            ['name' => 'Accounting', 'description' => 'Accounting, auditing, and tax services'],
            ['name' => 'Human Resources', 'description' => 'HR consulting and talent management services'],

            // Retail & Consumer
            ['name' => 'Retail', 'description' => 'Consumer goods retail and distribution'],
            ['name' => 'Fashion', 'description' => 'Clothing design, manufacturing, and retail'],
            ['name' => 'Hospitality', 'description' => 'Hotels, restaurants, and hospitality services'],
            ['name' => 'Tourism', 'description' => 'Travel and tourism services'],

            // Government & Public Sector
            ['name' => 'Government', 'description' => 'Public administration and government services'],
            ['name' => 'Non-Profit', 'description' => 'Non-governmental organizations and social enterprises'],
            ['name' => 'International Development', 'description' => 'Development aid and humanitarian organizations'],

            // Emerging Industries
            ['name' => 'Cryptocurrency', 'description' => 'Digital currencies and blockchain technology'],
            ['name' => 'Green Technology', 'description' => 'Environmental technology and sustainability solutions'],
            ['name' => 'Space Technology', 'description' => 'Space exploration and satellite technology'],
        ];

		foreach ($data as $row) {
			Industry::updateOrCreate(
				['name' => $row['name']],
				$row
			);
		}
    }
}
