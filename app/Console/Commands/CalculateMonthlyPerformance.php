<?php

namespace App\Console\Commands;

use App\Models\Performance\PerformanceCalculatorService;
use Illuminate\Console\Command;

class CalculateMonthlyPerformance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'performance:calculate-monthly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate and store monthly performance metrics';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(PerformanceCalculatorService $calculator)
    {
        $this->info('Starting monthly performance calculations...');

        try {
            $calculator->calculateMonthlyPerformance();
            $this->info('Successfully calculated and stored monthly performance metrics.');
        } catch (\Exception $e) {
            $this->error('Error calculating monthly performance: ' . $e->getMessage());
        }
    }
}
