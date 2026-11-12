<?php
return [
    'active_sessions' => (int) env('TICKET_ACTIVE_SESSIONS', 100),
    'access_minutes' => (int) env('TICKET_ACCESS_MINUTES', 5),
    'max_per_user' => (int) env('TICKET_MAX_PER_USER', 2),
];
