<?php

return [
    'PermissionSetting' => [
        'types' => [
            'admin' =>   \App\User::class,
            'guest' => \App\Guest::class,
        ]
    ]
];
