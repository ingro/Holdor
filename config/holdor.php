<?php

return [
    'token_expire' => env('HOLDOR_TOKEN_EXPIRE', 3600),
    'token_secret' => env('HOLDOR_TOKEN_SECRET'),
    'refresh_expire' => env('HOLDOR_REFRESH_EXPIRE', 7200)
];
