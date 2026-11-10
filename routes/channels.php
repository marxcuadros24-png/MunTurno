<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Canales públicos para TV de sala y seguimiento ciudadano
Broadcast::channel('cola.publica', function () {
    return true;
});

Broadcast::channel('turno.{id}', function () {
    return true;
});

Broadcast::channel('area.{id}', function () {
    return true;
});
