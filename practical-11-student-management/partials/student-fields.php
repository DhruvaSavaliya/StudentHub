<div>
    <label for="student_code">Student code</label>
    <input id="student_code" name="student_code" type="text" required minlength="3" maxlength="24" pattern="[A-Za-z0-9-]{3,24}" value="<?= p11Escape((string) ($values['student_code'] ?? '')) ?>">
    <?php if (isset($errors['student_code'])): ?><small class="field-error"><?= p11Escape($errors['student_code']) ?></small><?php endif; ?>
</div>
<div>
    <label for="first_name">First name</label>
    <input id="first_name" name="first_name" type="text" autocomplete="given-name" required maxlength="60" value="<?= p11Escape((string) ($values['first_name'] ?? '')) ?>">
    <?php if (isset($errors['first_name'])): ?><small class="field-error"><?= p11Escape($errors['first_name']) ?></small><?php endif; ?>
</div>
<div>
    <label for="last_name">Last name</label>
    <input id="last_name" name="last_name" type="text" autocomplete="family-name" required maxlength="60" value="<?= p11Escape((string) ($values['last_name'] ?? '')) ?>">
    <?php if (isset($errors['last_name'])): ?><small class="field-error"><?= p11Escape($errors['last_name']) ?></small><?php endif; ?>
</div>
<div>
    <label for="email">Email</label>
    <input id="email" name="email" type="email" autocomplete="email" required maxlength="254" value="<?= p11Escape((string) ($values['email'] ?? '')) ?>">
    <?php if (isset($errors['email'])): ?><small class="field-error"><?= p11Escape($errors['email']) ?></small><?php endif; ?>
</div>
<div>
    <label for="mobile">Mobile (optional)</label>
    <input id="mobile" name="mobile" type="tel" autocomplete="tel" maxlength="24" value="<?= p11Escape((string) ($values['mobile'] ?? '')) ?>">
    <?php if (isset($errors['mobile'])): ?><small class="field-error"><?= p11Escape($errors['mobile']) ?></small><?php endif; ?>
</div>
<div>
    <label for="course_id">Course</label>
    <select id="course_id" name="course_id" required>
        <option value="">Choose a course</option>
        <?php foreach ($courses as $course): ?>
            <option value="<?= (int) $course['id'] ?>" <?= (int) ($values['course_id'] ?? 0) === (int) $course['id'] ? 'selected' : '' ?>><?= p11Escape($course['course_code'] . ' · ' . $course['course_name']) ?></option>
        <?php endforeach; ?>
    </select>
    <?php if (isset($errors['course_id'])): ?><small class="field-error"><?= p11Escape($errors['course_id']) ?></small><?php endif; ?>
</div>
<div class="full">
    <label for="year_of_study">Year of study</label>
    <select id="year_of_study" name="year_of_study" required>
        <option value="">Choose year</option>
        <?php for ($year = 1; $year <= 4; $year++): ?>
            <option value="<?= $year ?>" <?= (int) ($values['year_of_study'] ?? 0) === $year ? 'selected' : '' ?>>Year <?= $year ?></option>
        <?php endfor; ?>
    </select>
    <?php if (isset($errors['year_of_study'])): ?><small class="field-error"><?= p11Escape($errors['year_of_study']) ?></small><?php endif; ?>
</div>
