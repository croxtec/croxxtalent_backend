<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Company
Route::middleware('auth:sanctum')->prefix('employers')->name('employers.')->group( function () {
    Route::post('employee/import', 'Api\v2\Company\EmployeeController@importEmployees')->name('employee.import');

    Route::resources([
        'employee' => 'Api\v2\Company\EmployeeController',
        'supervisor' => 'Api\v2\Company\SupervisorController',
        'department' => 'Api\v2\Company\DepartmentController',
        'roles' => 'Api\v2\Company\DepartmentRoleController'
    ]);

    // Overview
    Route::get('/overview', 'Api\v2\Company\CompanyReportController@overview')->name('company.insights');
    Route::get('/overview/department', 'Api\v2\Company\CompanyReportController@departmentOverview')->name('company.summary');
    Route::get('/overview/assessment/feedback', 'Api\v2\Company\CompanyReportController@recentFeedback')->name('company.assessment.feedback');
    Route::get('/overview/assessment/chart', 'Api\v2\Company\CompanyReportController@assessmentChart')->name('company.assessment.chart');
    Route::get('/overview/courses/chart', 'Api\v2\Company\CompanyReportController@coursesChart')->name('company.courses.chart');
    // Report
    Route::get('/refresh/performance', 'Api\v2\Company\CompanyReportController@refreshPerformance');
    // Employee Report
    Route::get('/report/employee/gap', 'Api\v2\Company\ReportAnalysisController@getEmployeeCompetencyGap');
    Route::get('/report/employee/kpi', 'Api\v2\Company\PerformanceController@getEmployeeKPIPerformance');
    Route::get('/report/employee/performance', 'Api\v2\Company\PerformanceController@getEmployeeFeedbackPerformance');
    Route::get('/report/employee/historical', 'Api\v2\Company\PerformanceController@getEmployeeHistoricalPerformance');
    //Department Report
    Route::get('/overview/employees/gap', 'Api\v2\Company\ReportAnalysisController@gapAnalysisSummary');
    Route::get('/report/competency/gap', 'Api\v2\Company\ReportAnalysisController@gapAnalysisReport');
    Route::get('/report/team/compare', 'Api\v2\Company\ReportAnalysisController@getTeamCompetencyGap');
    Route::get('/report/team/distribution', 'Api\v2\Company\ReportAnalysisController@getEmployeesDistribution');
    Route::get('/report/department/analysis', 'Api\v2\Company\PerformanceController@getDepartmentSkillAnalysis');
    Route::get('/report/department/performance', 'Api\v2\Company\PerformanceController@getDepartmentPerformance');
    Route::get('/report/department/historical', 'Api\v2\Company\PerformanceController@getDepartmentHistoricalPerformance');

    // Mapping
    Route::get('competency/mapping', 'Api\v2\EmployerCompetencyController@index')->name('competency.index');
    Route::post('competency/mapping/{id}', 'Api\v2\EmployerCompetencyController@storeCompetency')->name('competency.store');
    Route::post('competency/add/{id}', 'Api\v2\EmployerCompetencyController@addCompetency')->name('competency.add');

    Route::get('onboarding/welcome', 'Api\v2\EmployerCompetencyController@confirmWelcome')->name('confirm.welcome');
    Route::get('onboarding/confirm-review', 'Api\v2\EmployerCompetencyController@confirmWelcome')->name('confirm.onboarding');

    // Route::get('competency/gap', 'Api\v2\EmployerCompetencyController@competency')->name('competency.skill');
    Route::post('employee/{id}/resend-invitation', 'Api\v2\Company\ManageEmployeeController@resendInvitation')->name('employee.resend_invitation');
    Route::put('employee/{id}/status', 'Api\v2\Company\EmployeeController@updateStatus')->name('employee.update_status');
    // Manage
    Route::patch('department/{id}/archive', 'Api\v2\Company\DepartmentController@archive')->name('department.archive');
    Route::patch('department/{id}/unarchive', 'Api\v2\Company\DepartmentController@unarchive')->name('department.unarchive');
});
