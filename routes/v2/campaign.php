<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group( function () {
    // Campaigns
    Route::post('campaigns/{id}/photo', 'Api\v2\CampaignController@photo')->name('campaigns.update_photo');
    Route::patch('campaigns/{id}/publish', 'Api\v2\CampaignController@publish')->name('campaigns.publish');
    Route::patch('campaigns/{id}/unpublish', 'Api\v2\CampaignController@unpublish')->name('campaigns.unpublish');;
    Route::patch('campaigns/{id}/archive', 'Api\v2\CampaignController@archive')->name('campaigns.archive');
    Route::patch('campaigns/{id}/unarchive', 'Api\v2\CampaignController@unarchive')->name('campaigns.unarchive');;
    Route::post('campaigns/delete-multiple', 'Api\v2\CampaignController@destroyMultiple')->name('campaigns.destroy_multiple');
    // Route::get('campaigns', 'Api\v2\CampaignController@index')->name('campaigns.index');
    // Route::get('campaigns/{id}', 'Api\v2\CampaignController@show')->name('campaigns.show');
    // Route::post('campaigns', 'Api\v2\CampaignController@store')->name('campaigns.store');
    // Route::put('campaigns/{id}', 'Api\v2\CampaignController@update')->name('campaigns.update');
    // Route::delete('campaigns/{id}', 'Api\v2\CampaignController@destroy')->name('campaigns.destroy');
    Route::resources([
        'campaigns' => 'Api\v2\CampaignController',
    ]);
});

// dataTableFilter: {
//     per_page: 25, // 25, -1 or all = all records,
//     page: 1,
//     search: null,
//     active: "no",
//     sort_by: "created_at",
//     sort_dir: "desc"
//   },
