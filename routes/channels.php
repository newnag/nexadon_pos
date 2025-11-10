<?php

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

// User-specific private channel
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Kitchen Display System channel - Only kitchen staff can access
// Authorized roles: Admin, Manager (kitchen managers)
Broadcast::channel('kitchen-channel', function ($user) {
    // Allow Admin and Manager roles to access kitchen channel
    // In a production environment, you might want a specific "Kitchen Staff" role
    return in_array($user->role->name, ['Admin', 'Manager']);
});
