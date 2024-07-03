<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;

// Route::middleware('jwt.auth')->group(function () {
//     Broadcast::channel('status-notification', function () {
//         return true;
//     });
// });

Broadcast::channel('status-notification', function () {
    return true;
});
