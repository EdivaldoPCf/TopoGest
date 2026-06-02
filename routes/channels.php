<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('pasta.{pastaId}', function ($user, $pastaId) {
    // Both admin and the client owner of the pasta should have access
    // Or just simple auth check if it's too complex (since they are already logged in to reach the page)
    return $user !== null;
});

Broadcast::channel('marcos', function ($user) {
    return $user !== null;
});
