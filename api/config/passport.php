<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authorization guard
    |--------------------------------------------------------------------------
    |
    | /oauth/authorize identifies the user by the browser session (guard "web",
    | signed in on the API login page), not by a bearer token (guard "api").
    |
    */

    'guard' => 'web',

];
