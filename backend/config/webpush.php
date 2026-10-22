<?php

return [
    'key_path' => env('WEBPUSH_KEY_PATH', storage_path('app/private/webpush-vapid.json')),
    'subject' => env('WEBPUSH_SUBJECT', 'mailto:admin@motocare.local'),
    'allowed_hosts' => ['fcm.googleapis.com', 'updates.push.services.mozilla.com', 'push.services.mozilla.com', 'web.push.apple.com', 'notify.windows.com'],
];
