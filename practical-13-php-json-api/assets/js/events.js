(() => {
    'use strict';
    const apiUrl = 'api/events.php';
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const form = document.querySelector('#event-form');
    const body = document.querySelector('#events-body');
    const state = document.querySelector('#events-state');
    const notice = document.querySelector('#notice');
    const search = document.querySelector('#search');
    const saveButton = document.querySelector('#save-button');
    const cancelButton = document.querySelector('#cancel-edit');
    let debounceTimer;
    let requestController;

    const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[char]);
    function showNotice(message, type = 'success') {
        notice.textContent = message;
        notice.className = `p13-notice ${type}`;
        notice.hidden = false;
    }
    async function request(url, options = {}) {
        const headers = new Headers(options.headers ?? {});
        headers.set('Accept', 'application/json');
        if (options.body) {
            headers.set('Content-Type', 'application/json');
            headers.set('X-CSRF-Token', csrf);
        }
        const response = await fetch(url, { ...options, headers, credentials: 'same-origin' });
        let payload;
        try { payload = await response.json(); } catch { throw new Error('The server returned an unreadable response.'); }
        if (!response.ok || !payload.ok) {
            const error = new Error(payload.message || `Request failed (${response.status}).`);
            error.fields = payload.errors ?? {};
            throw error;
        }
        return payload;
    }
    function formatDate(value) {
        const date = new Date(String(value).replace(' ', 'T'));
        return Number.isNaN(date.getTime()) ? value : date.toLocaleString();
    }
    function renderEvents(events) {
        if (!events.length) {
            body.innerHTML = '';
            state.textContent = 'No events match this search.';
            state.hidden = false;
            return;
        }
        state.hidden = true;
        body.innerHTML = events.map((event) => `<tr>
            <td><strong>${escapeHtml(event.title)}</strong><span class="p13-muted">${escapeHtml(event.description || '')}</span></td>
            <td>${escapeHtml(event.venue)}<span class="p13-muted">${escapeHtml(formatDate(event.starts_at))}</span></td>
            <td>${escapeHtml(event.category)}</td><td>${Number(event.registration_count)} / ${Number(event.capacity)}</td>
            <td><span class="p13-badge ${event.event_status === 'closed' ? 'closed' : ''}">${escapeHtml(event.event_status)}</span></td>
            <td><div class="p13-row-actions"><button type="button" data-action="edit" data-id="${Number(event.id)}">Edit</button><button type="button" class="delete" data-action="delete" data-id="${Number(event.id)}">Delete</button></div></td>
        </tr>`).join('');
        body.querySelectorAll('[data-action="edit"]').forEach((button) => button.addEventListener('click', () => beginEdit(events.find((event) => Number(event.id) === Number(button.dataset.id)))));
        body.querySelectorAll('[data-action="delete"]').forEach((button) => button.addEventListener('click', () => deleteEvent(Number(button.dataset.id))));
    }
    async function loadEvents() {
        requestController?.abort();
        requestController = new AbortController();
        state.classList.remove('error');
        state.hidden = false;
        state.textContent = 'Loading events…';
        try {
            const params = new URLSearchParams();
            if (search.value.trim()) params.set('q', search.value.trim());
            renderEvents((await request(`${apiUrl}?${params}`, { signal: requestController.signal })).events);
        } catch (error) {
            if (error.name === 'AbortError') return;
            state.hidden = false;
            state.classList.add('error');
            state.textContent = error.message;
        }
    }
    function beginEdit(event) {
        if (!event) return;
        const values = { id: event.id, title: event.title, category: event.category, venue: event.venue,
            starts_at: String(event.starts_at).replace(' ', 'T').slice(0, 16), capacity: event.capacity,
            event_status: event.event_status, description: event.description ?? '' };
        for (const [key, value] of Object.entries(values)) form.elements.namedItem(key).value = value;
        document.querySelector('#add-heading').textContent = 'Edit event';
        saveButton.textContent = 'Save changes';
        cancelButton.hidden = false;
        form.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    function resetForm() {
        form.reset();
        form.elements.namedItem('id').value = '';
        form.elements.namedItem('capacity').value = '40';
        document.querySelector('#add-heading').textContent = 'Add an event';
        saveButton.textContent = 'Add event';
        cancelButton.hidden = true;
    }
    async function deleteEvent(id) {
        if (!window.confirm('Delete this event? Events with registrations cannot be deleted.')) return;
        try {
            const result = await request(`${apiUrl}?id=${encodeURIComponent(id)}`, { method: 'DELETE', body: JSON.stringify({ id }) });
            showNotice(result.message);
            await loadEvents();
        } catch (error) { showNotice(error.message, 'error'); }
    }
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        saveButton.disabled = true;
        const values = Object.fromEntries(new FormData(form).entries());
        const editing = values.id !== '';
        try {
            const result = await request(apiUrl, { method: editing ? 'PUT' : 'POST', body: JSON.stringify(values) });
            showNotice(result.message);
            resetForm();
            await loadEvents();
        } catch (error) {
            showNotice(Object.values(error.fields ?? {}).join(' ') || error.message, 'error');
        } finally { saveButton.disabled = false; }
    });
    cancelButton.addEventListener('click', resetForm);
    search.addEventListener('input', () => {
        window.clearTimeout(debounceTimer);
        debounceTimer = window.setTimeout(loadEvents, 250);
    });
    loadEvents();
})();
