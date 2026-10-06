<?php
declare(strict_types=1);

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if ($requestPath === '/practical-07-php-file-storage/data/registrations.json') {
    http_response_code(404);
    exit('Not found.');
}

return false;
