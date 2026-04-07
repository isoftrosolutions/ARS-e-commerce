<?php
ignore_user_abort(true);
set_time_limit(0);

// Close the session so it doesn't block the user who triggered this
if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}

// Redirect output so script continues in background
if (php_sapi_name() !== 'cli') {
    ob_end_clean();
    header("Connection: close\r\n");
    header("Content-Encoding: none\r\n");
    header("Content-Length: 0");
    ob_start();
    echo "";
    ob_end_flush();
    flush();
    if(function_exists('fastcgi_finish_request')) {
        fastcgi_finish_request();
    }
}

// Now include the actual worker
require_once __DIR__ . '/scripts/cron_worker.php';
