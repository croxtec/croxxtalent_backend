<?php

use App\Events\NotificationMessage;
use App\Notifications\CroxxTalentUsers;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
//     return (int) $user->id === (int) $id;
// });

// Broadcast::channel('croxx_notifications', function ($user) {
//     info('Public channel loaded');
//     return $user;
// });

// Broadcast::channel('croxx_notifications.{id}', function ($user, $id) {
//     return true;
// });
