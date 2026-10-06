<?php
declare(strict_types=1);

$isHttps = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
session_set_cookie_params([
    'httponly' => true,
    'secure' => $isHttps,
    'samesite' => 'Lax',
]);
session_start();

function escapeHtml(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function storeRegistration(array $registration): void
{
    // Store submissions beside the Practical 7 source so students can inspect the required JSON output.
    $directory = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'data';

    if (is_link($directory)) {
        throw new RuntimeException('The private storage directory must not be a symbolic link.');
    }

    if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) {
        throw new RuntimeException('Could not create the private storage directory.');
    }

    $filePath = $directory . DIRECTORY_SEPARATOR . 'registrations.json';
    if (is_link($filePath)) {
        throw new RuntimeException('The registration data file must not be a symbolic link.');
    }

    $file = fopen($filePath, 'c+');
    if ($file === false) {
        throw new RuntimeException('Could not open the registration data file.');
    }

    try {
        if (!flock($file, LOCK_EX)) {
            throw new RuntimeException('Could not lock the registration data file.');
        }

        rewind($file);
        $contents = stream_get_contents($file);
        $records = $contents === false || trim($contents) === '' ? [] : json_decode($contents, true);
        if (
            !is_array($records)
            || json_last_error() !== JSON_ERROR_NONE
            || ($records !== [] && array_keys($records) !== range(0, count($records) - 1))
        ) {
            throw new RuntimeException('The registration data file does not contain a valid JSON array.');
        }

        $records[] = $registration;
        $json = json_encode($records, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
        if ($json === false) {
            throw new RuntimeException('Could not encode the registration as JSON.');
        }

        rewind($file);
        $payload = $json . PHP_EOL;
        if (!ftruncate($file, 0) || fwrite($file, $payload) !== strlen($payload) || !fflush($file)) {
            throw new RuntimeException('Could not write the registration data file.');
        }
        @chmod($filePath, 0600);
        flock($file, LOCK_UN);
    } finally {
        fclose($file);
    }
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method !== 'GET' && $method !== 'POST') {
    header('Allow: GET, POST');
    http_response_code(405);
    exit('Method not allowed.');
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION['csrf_token'];
$allowedCourses = ['Information Technology', 'Computer Engineering', 'Computer Science'];
$allowedYears = ['1', '2', '3', '4'];
$allowedGenders = ['Female', 'Male', 'Other', 'Prefer not to say'];
$fields = ['name', 'email', 'mobile', 'password', 'confirmPassword', 'course', 'year', 'gender'];
$values = array_fill_keys($fields, '');
$errors = [];
$pageError = '';
$flash = $_SESSION['practical7_flash'] ?? null;
unset($_SESSION['practical7_flash']);

if ($method === 'POST') {
    foreach ($fields as $field) {
        $posted = $_POST[$field] ?? '';
        $values[$field] = is_string($posted)
            ? (in_array($field, ['password', 'confirmPassword'], true) ? $posted : trim($posted))
            : '';
    }
    $values['name'] = preg_replace('/\s+/u', ' ', $values['name']) ?? $values['name'];
    $values['email'] = strtolower($values['email']);
    $values['mobile'] = preg_replace('/\s+/', ' ', $values['mobile']) ?? $values['mobile'];

    $submittedToken = $_POST['csrf_token'] ?? '';
    if (!is_string($submittedToken) || !hash_equals($csrfToken, $submittedToken)) {
        $pageError = 'Your form session expired. Reload the page and submit it again.';
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $csrfToken = $_SESSION['csrf_token'];
    } else {
        if (!preg_match("/^[\p{L}][\p{L}\p{M} .'-]{1,79}$/u", $values['name'])) {
            $errors['name'] = 'Enter a name between 2 and 80 characters using letters, spaces, apostrophes, or hyphens.';
        }
        if (strlen($values['email']) > 254 || filter_var($values['email'], FILTER_VALIDATE_EMAIL) === false) {
            $errors['email'] = 'Enter a valid email address.';
        }
        if (!preg_match('/^\+?[0-9\s().-]{8,24}$/', $values['mobile'])) {
            $errors['mobile'] = 'Enter a phone number containing 10 to 15 digits.';
        } else {
            $digits = preg_replace('/\D+/', '', $values['mobile']) ?? '';
            if (strlen($digits) < 10 || strlen($digits) > 15) {
                $errors['mobile'] = 'Enter a phone number containing 10 to 15 digits.';
            }
        }
        if (
            strlen($values['password']) < 8
            || !preg_match('/[A-Za-z]/', $values['password'])
            || !preg_match('/\d/', $values['password'])
        ) {
            $errors['password'] = 'Use at least 8 characters, including a letter and a number.';
        }
        if ($values['confirmPassword'] === '' || !hash_equals($values['password'], $values['confirmPassword'])) {
            $errors['confirmPassword'] = 'Enter the same password again.';
        }
        if (!in_array($values['course'], $allowedCourses, true)) {
            $errors['course'] = 'Choose one of the listed courses.';
        }
        if (!in_array($values['year'], $allowedYears, true)) {
            $errors['year'] = 'Choose a valid year of study.';
        }
        if (!in_array($values['gender'], $allowedGenders, true)) {
            $errors['gender'] = 'Choose an option.';
        }
        if (($_POST['terms'] ?? '') !== 'accepted') {
            $errors['terms'] = 'Accept the terms before submitting the form.';
        }

        if ($errors === []) {
            $registration = [
                'submitted_at' => gmdate(DATE_ATOM),
                'name' => $values['name'],
                'email' => $values['email'],
                'mobile' => $values['mobile'],
                'course' => $values['course'],
                'year' => (int) $values['year'],
                'gender' => $values['gender'],
            ];

            try {
                storeRegistration($registration);
                $_SESSION['practical7_flash'] = 'Registration received and saved to the local JSON file.';
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                header('Location: register.php', true, 303);
                exit;
            } catch (Throwable $exception) {
                error_log('[StudentHub Practical 7] ' . $exception->getMessage());
                $pageError = 'The registration could not be saved. Check PHP file permissions and try again.';
            }
        }
    }

    // Passwords are checked but never echoed back or included in the saved JSON record.
    $values['password'] = '';
    $values['confirmPassword'] = '';
}
