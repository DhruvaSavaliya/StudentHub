<?php
declare(strict_types=1);

require dirname(__DIR__) . '/lib/registration-handler.php';

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Register for the StudentHub student portal.">
    <title>Register | StudentHub</title>
    <link rel="stylesheet" href="../../practical-03-responsive-css/style.css">
    <link rel="stylesheet" href="../style.css">
    <script src="../../practical-04-dom-interactivity/script.js" defer></script>
    <script src="../../practical-05-registration-validation/register.js" defer></script>
</head>
<body>
<header class="navbar p7-navbar">
    <a class="logo" href="../../practical-02-semantic-html/pages/index.html" aria-label="StudentHub home">
        <img src="../../assets/images/logo.jpg" alt="">
        <span>StudentHub</span>
    </a>
    <button class="hamburger" id="hamburger" type="button" aria-label="Open menu" aria-controls="navLinks" aria-expanded="false">☰</button>
    <nav class="nav-links" id="navLinks" aria-label="Main navigation">
        <a href="../../practical-02-semantic-html/pages/index.html">Home</a>
        <a href="../../practical-02-semantic-html/pages/about.html">About</a>
        <a href="../../practical-06-fetch-json/pages/events.html">Events</a>
        <a href="../../practical-06-fetch-json/pages/students.html">Students</a>
        <a href="../../practical-06-fetch-json/pages/faq.html">FAQ</a>
        <a href="../../practical-02-semantic-html/pages/dashboard.html">Dashboard</a>
        <a href="../../practical-02-semantic-html/pages/contact.html">Contact</a>
    </nav>
    <a class="btn btn-blue" href="register.php" aria-current="page">Register</a>
</header>

<main class="auth-page p7-page">
    <section class="auth-card p7-card" aria-labelledby="page-title">
        <p class="p7-kicker">StudentHub · Registration</p>
        <h1 id="page-title">Student registration</h1>
        <p>Submit your details. The server validates the form and saves a valid registration to a local JSON file.</p>

        <?php if (is_string($flash) && $flash !== ''): ?>
            <p class="p7-message p7-message-success" role="status"><?= escapeHtml($flash) ?></p>
        <?php endif; ?>
        <?php if ($pageError !== ''): ?>
            <p class="p7-message p7-message-error" role="alert"><?= escapeHtml($pageError) ?></p>
        <?php endif; ?>
        <?php if ($errors !== []): ?>
            <p class="p7-message p7-message-error" id="formMessage" role="alert">Please correct the highlighted fields and submit the form again.</p>
        <?php endif; ?>

        <form id="registerForm" data-server-validation="true" method="post" action="register.php" novalidate>
            <input type="hidden" name="csrf_token" value="<?= escapeHtml($csrfToken) ?>">

            <div class="form-group">
                <label for="name">Full name</label>
                <input class="form-control" id="name" name="name" type="text" autocomplete="name" maxlength="80" required value="<?= escapeHtml($values['name']) ?>" aria-describedby="nameError"<?= isset($errors['name']) ? ' aria-invalid="true"' : '' ?>>
                <p class="field-error" id="nameError"><?= escapeHtml($errors['name'] ?? '') ?></p>
            </div>

            <div class="form-group">
                <label for="email">Email address</label>
                <input class="form-control" id="email" name="email" type="email" autocomplete="email" maxlength="254" required value="<?= escapeHtml($values['email']) ?>" aria-describedby="emailError"<?= isset($errors['email']) ? ' aria-invalid="true"' : '' ?>>
                <p class="field-error" id="emailError"><?= escapeHtml($errors['email'] ?? '') ?></p>
            </div>

            <div class="form-group">
                <label for="mobile">Mobile number</label>
                <input class="form-control" id="mobile" name="mobile" type="tel" autocomplete="tel" inputmode="tel" maxlength="24" required value="<?= escapeHtml($values['mobile']) ?>" aria-describedby="mobileError"<?= isset($errors['mobile']) ? ' aria-invalid="true"' : '' ?>>
                <p class="field-error" id="mobileError"><?= escapeHtml($errors['mobile'] ?? '') ?></p>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input class="form-control" id="password" name="password" type="password" autocomplete="new-password" minlength="8" maxlength="128" required aria-describedby="passwordStrength passwordError">
                <p class="p7-hint" id="passwordStrength" aria-live="polite">Use at least 8 characters, including a letter and a number.</p>
                <p class="field-error" id="passwordError"><?= escapeHtml($errors['password'] ?? '') ?></p>
            </div>

            <div class="form-group">
                <label for="confirmPassword">Confirm password</label>
                <input class="form-control" id="confirmPassword" name="confirmPassword" type="password" autocomplete="new-password" minlength="8" maxlength="128" required aria-describedby="confirmPasswordError">
                <p class="field-error" id="confirmPasswordError"><?= escapeHtml($errors['confirmPassword'] ?? '') ?></p>
            </div>

            <div class="form-group">
                <label for="course">Course</label>
                <select class="form-control" id="course" name="course" required aria-describedby="courseError"<?= isset($errors['course']) ? ' aria-invalid="true"' : '' ?>>
                    <option value="">Select your course</option>
                    <?php foreach ($allowedCourses as $course): ?>
                        <option value="<?= escapeHtml($course) ?>"<?= $values['course'] === $course ? ' selected' : '' ?>><?= escapeHtml($course) ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="field-error" id="courseError"><?= escapeHtml($errors['course'] ?? '') ?></p>
            </div>

            <div class="form-group">
                <label for="year">Year of study</label>
                <select class="form-control" id="year" name="year" required aria-describedby="yearError"<?= isset($errors['year']) ? ' aria-invalid="true"' : '' ?>>
                    <option value="">Select your year</option>
                    <?php foreach ($allowedYears as $year): ?>
                        <option value="<?= escapeHtml($year) ?>"<?= $values['year'] === $year ? ' selected' : '' ?>>Year <?= escapeHtml($year) ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="field-error" id="yearError"><?= escapeHtml($errors['year'] ?? '') ?></p>
            </div>

            <fieldset class="form-group p7-fieldset" aria-describedby="genderError">
                <legend>Gender</legend>
                <div class="choice-row">
                    <?php foreach ($allowedGenders as $gender): ?>
                        <label><input type="radio" name="gender" value="<?= escapeHtml($gender) ?>"<?= $values['gender'] === $gender ? ' checked' : '' ?><?= isset($errors['gender']) ? ' aria-invalid="true"' : '' ?>> <?= escapeHtml($gender) ?></label>
                    <?php endforeach; ?>
                </div>
                <p class="field-error" id="genderError"><?= escapeHtml($errors['gender'] ?? '') ?></p>
            </fieldset>

            <div class="p7-terms">
                <input id="terms" name="terms" type="checkbox" value="accepted" required aria-describedby="termsError"<?= ($_POST['terms'] ?? '') === 'accepted' ? ' checked' : '' ?><?= isset($errors['terms']) ? ' aria-invalid="true"' : '' ?>>
                <label for="terms">I agree to use sample details for this classroom demonstration.</label>
            </div>
            <p class="field-error" id="termsError"><?= escapeHtml($errors['terms'] ?? '') ?></p>

            <button class="btn btn-blue p7-submit" type="submit">Submit registration</button>
        </form>

        <p class="p7-privacy">Passwords are checked by the browser and server for this validation exercise, then discarded. They are never written to the JSON file. Use fictional classroom details only; this is not intended for production or real personal data.</p>
    </section>
</main>

<footer><p>StudentHub · Web Development Frameworks (ITUE203)</p></footer>
</body>
</html>
