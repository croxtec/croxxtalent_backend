
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware('auth:sanctum')->name('api.')->group( function () {
    Route::prefix('talent')->name('talent')->group( function () {
        Route::get('company', 'Api\v2\Talent\TalentCompanyController@index')->name('company.employee');
        Route::post('company/photo', 'Api\v2\Talent\TalentCompanyController@photo')->name('company.employee.photo');
        Route::get('company/supervisor', 'Api\v2\Talent\TalentCompanyController@supervisor')->name('company.supervisor');
        Route::get('company/employee/{id}', 'Api\v2\Talent\TalentCompanyController@employeeInformation')->name('company.employee');
        Route::get('company/team/performance', 'Api\v2\Talent\TalentCompanyController@teamPerformanceProgress')->name('company.performance');
    });
});
