<div class="full">
    <label for="title">Event title</label>
    <input id="title" name="title" type="text" required maxlength="140" value="<?= p12Escape((string) ($values['title'] ?? '')) ?>">
    <?php if (isset($errors['title'])): ?><small class="p12-field-error"><?= p12Escape($errors['title']) ?></small><?php endif; ?>
</div>
<div class="full">
    <label for="description">Description</label>
    <textarea id="description" name="description" maxlength="5000" rows="4"><?= p12Escape((string) ($values['description'] ?? '')) ?></textarea>
    <?php if (isset($errors['description'])): ?><small class="p12-field-error"><?= p12Escape($errors['description']) ?></small><?php endif; ?>
</div>
<div>
    <label for="venue">Venue</label>
    <input id="venue" name="venue" type="text" required maxlength="120" value="<?= p12Escape((string) ($values['venue'] ?? '')) ?>">
    <?php if (isset($errors['venue'])): ?><small class="p12-field-error"><?= p12Escape($errors['venue']) ?></small><?php endif; ?>
</div>
<div>
    <label for="starts_at">Date and time</label>
    <input id="starts_at" name="starts_at" type="datetime-local" required value="<?= p12Escape((string) ($values['starts_at_input'] ?? '')) ?>">
    <?php if (isset($errors['starts_at'])): ?><small class="p12-field-error"><?= p12Escape($errors['starts_at']) ?></small><?php endif; ?>
</div>
<div>
    <label for="capacity">Capacity</label>
    <input id="capacity" name="capacity" type="number" min="1" max="65535" required value="<?= p12Escape((string) ($values['capacity'] ?? '')) ?>">
    <?php if (isset($errors['capacity'])): ?><small class="p12-field-error"><?= p12Escape($errors['capacity']) ?></small><?php endif; ?>
</div>
<div>
    <label for="category">Category</label>
    <select id="category" name="category" required>
        <?php foreach (p12Categories() as $key => $label): ?>
            <option value="<?= p12Escape($key) ?>" <?= ($values['category'] ?? 'general') === $key ? 'selected' : '' ?>><?= p12Escape($label) ?></option>
        <?php endforeach; ?>
    </select>
    <?php if (isset($errors['category'])): ?><small class="p12-field-error"><?= p12Escape($errors['category']) ?></small><?php endif; ?>
</div>
<div>
    <label for="event_status">Registration status</label>
    <select id="event_status" name="event_status" required>
        <option value="open" <?= ($values['event_status'] ?? 'open') === 'open' ? 'selected' : '' ?>>Open</option>
        <option value="closed" <?= ($values['event_status'] ?? '') === 'closed' ? 'selected' : '' ?>>Closed</option>
    </select>
    <?php if (isset($errors['event_status'])): ?><small class="p12-field-error"><?= p12Escape($errors['event_status']) ?></small><?php endif; ?>
</div>
<div class="full">
    <label for="poster">Event poster</label>
    <input id="poster" name="poster" type="file" accept="image/jpeg,image/png,image/webp">
    <small class="p12-file-hint">JPEG, PNG, or WebP; maximum 2 MB. The poster is optional.</small>
    <?php if (isset($errors['poster'])): ?><small class="p12-field-error"><?= p12Escape($errors['poster']) ?></small><?php endif; ?>
    <?php if (!empty($currentPoster)): ?>
        <div class="p12-current-poster"><img src="uploads/<?= p12Escape($currentPoster) ?>" alt="Current event poster"><span>Current poster will stay unless you upload a replacement.</span></div>
    <?php endif; ?>
</div>
