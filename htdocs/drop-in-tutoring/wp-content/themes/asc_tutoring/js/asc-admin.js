// =============================================================================
// ROW BUILDERS — SCHEDULE & ACCOUNTS
// =============================================================================

function resolveCourseLabel(courseId, schedule) {
  if (schedule.course_label) return schedule.course_label;

  const existingRow = qs(`#schedule-table tbody tr[data-course-id="${courseId}"]`);
  if (existingRow) return existingRow.children[1]?.textContent?.trim() || String(courseId);

  try {
    const raw = $('course_lookup_results')?.value;
    if (raw) {
      const c = JSON.parse(raw);
      if (String(c.course_id) === String(courseId)) {
        return `${c.course_subject} ${c.course_code} - ${c.course_name}`;
      }
    }
  } catch (_) {}

  return String(courseId);
}

function buildScheduleRow(s) {
  const userLabel   = resolveUserLabel(s.user_id, s.user_label);
  const courseLabel = resolveCourseLabel(s.course_id, s);

  return `
    <tr
      data-schedule-id="${s.schedule_id}"
      data-user-id="${s.user_id}"
      data-course-id="${s.course_id}"
      data-day-of-week="${DAY_ABBR[s.day_of_week] ?? s.day_of_week}"
      data-start-time="${s.start_time}"
      data-end-time="${s.end_time}"
    >
      <td>${userLabel}</td>
      <td>${courseLabel}</td>
      <td>${s.day_of_week}</td>
      <td>${formatDisplayTime(s.start_time)}</td>
      <td>${formatDisplayTime(s.end_time)}</td>
      <td>
        <button type="button" class="button button-primary admin-edit-schedule">Edit</button>
        <button type="button" class="button button-secondary admin-delete-schedule">Delete</button>
      </td>
    </tr>`;
}

function buildAccountRow(a) {
  return `
    <tr
      data-user-id="${a.user_id}"
      data-user-login="${a.user_login || ''}"
      data-user-email="${a.user_email || ''}"
      data-first-name="${a.first_name || ''}"
      data-last-name="${a.last_name || ''}"
      data-roles="${(a.roles || []).join(',')}"
    >
      <td>${a.user_login}</td>
      <td>${a.first_name} ${a.last_name}</td>
      <td>${a.user_email}</td>
      <td>${(a.roles || []).map(formatDisplayRole).join(', ')}</td>
      <td>
        <button type="button" class="button button-primary admin-edit-account">Edit</button>
        <button type="button" class="button button-secondary admin-delete-account">Delete</button>
      </td>
    </tr>`;
}


// =============================================================================
// ADMIN PANEL — FLATPICKR — SCHEDULE PICKERS
// =============================================================================

function initScheduleFlatpickr() {
  if (typeof flatpickr === 'undefined') return;

  const FLATPICKR_CONFIG = {
    enableTime:      true,
    noCalendar:      true,
    minuteIncrement: 15,
    time_24hr:       false,
    allowInput:      false,
    dateFormat:      'h:i K',
    static:          true,
  };

  ['schedule_start_time_picker', 'schedule_end_time_picker'].forEach(id => {
    const el = $(id);
    if (el) flatpickr(el, { ...FLATPICKR_CONFIG });
  });
}


// =============================================================================
// ADMIN PANEL — SELECT2 — SCHEDULE
// =============================================================================

const SCHEDULE_SELECT2_IDS = ['schedule_user_id', 'schedule_course_lookup', 'schedule_day_of_week'];

function initScheduleSelect2(scheduleCourseLookup) {
  if (typeof jQuery === 'undefined' || typeof jQuery.fn.select2 === 'undefined') return;

  SCHEDULE_SELECT2_IDS.forEach(id => {
    const el = $(id);
    if (!el) return;
    jQuery(`#${id}`).select2({
      width:       '100%',
      allowClear:  true,
      placeholder: el.options[0]?.text || 'Select\u2026',
    });
  });

  jQuery('#schedule_course_lookup').on('select2:select select2:clear', () => {
    const el = $('schedule_course_lookup');
    if (!el) return;

    const selected = el.value ? el.options[el.selectedIndex] : null;
    if (!selected?.dataset?.newCourse) {
      el.querySelector('option[data-new-course]')?.remove();
      $$('#course-search-list .account-search-item').forEach(li => li.classList.remove('selected'));
      clearVal('course_lookup_results');
    }

    if (!el.value) { clearVal('schedule_course_id'); return; }
    try { setVal('schedule_course_id', JSON.parse(el.value).course_id || ''); } catch (_) {}
  });
}


// =============================================================================
// ADMIN PANEL — TABLE INIT — SCHEDULE & ACCOUNTS
// =============================================================================

function initAdminTables() {
  ['schedule-table', 'account-table'].forEach(tableId => {
    const table = $(tableId);
    if (!table) return;
    buildTableFilterUI(table);
    addSortArrowsToTable(table);
  });
}


// =============================================================================
// ADMIN PANEL — TAB SWITCHING
// =============================================================================

function initTabSwitching() {
  $$('.admin-tab').forEach(tab => {
    on(tab, 'click', () => {
      clearMessages();
      $$('.admin-tab').forEach(t => t.classList.remove('active'));
      $$('.admin-section').forEach(s => s.classList.remove('active'));
      tab.classList.add('active');
      $(`admin-tab-${tab.dataset.tab}`)?.classList.add('active');
    });
  });
}


// =============================================================================
// ADMIN PANEL — UMBC SEARCH
// =============================================================================

async function searchUmbc({ endpoint, resultsBoxId, statusElId, listElId, collectionKey, renderItem, onSelect, getLabel }) {
  const resultsBox = $(resultsBoxId);
  const statusEl   = $(statusElId);
  const listEl     = $(listElId);
  if (!resultsBox) return;

  resultsBox.hidden    = false;
  statusEl.textContent = 'Searching\u2026';
  listEl.innerHTML     = '';

  try {
    const data  = await api.request(endpoint);
    const items = data[collectionKey] || [];

    if (!items.length) { statusEl.textContent = 'No results found.'; return; }

    statusEl.textContent = `${items.length} result${pluralSuffix(items.length)} found \u2014 click a result to select it.`;

    items.forEach(item => {
      const li = document.createElement('li');
      li.className = 'account-search-item';
      li.innerHTML = `
        <div class="account-search-item-info">${renderItem(item)}</div>
        <button type="button" class="button button-primary" style="flex-shrink:0;">Select</button>
      `;

      const selectFn = () => {
        listEl.querySelectorAll('.account-search-item').forEach(el => el.classList.remove('selected'));
        li.classList.add('selected');
        onSelect(item);
        showMessage(`Selected: ${getLabel(item)}`, 'success');
      };

      on(li, 'click', selectFn);
      on(li.querySelector('button'), 'click', (e) => { e.stopPropagation(); selectFn(); });
      listEl.appendChild(li);
    });
  } catch (err) {
    statusEl.textContent = 'Search failed.';
    showMessage(err.message, 'error');
  }
}

function bindLookupForm({ formId, queryId, resultsId, endpoint, collectionKey, headers, buildRow }) {
  on($(formId), 'submit', async (e) => {
    e.preventDefault();
    const query = $(queryId).value.trim();
    const box   = $(resultsId);
    try {
      const data  = await api.request(`${endpoint}?search_str=${encodeURIComponent(query)}`);
      const items = data[collectionKey] || [];
      if (!items.length) { box.innerHTML = '<p>No results found.</p>'; return; }
      const headerCells = headers.map(h => `<th>${h}</th>`).join('');
      const rows        = items.map(buildRow).join('');
      box.innerHTML = `
        <div class="umbc-table-wrapper">
          <table class="umbc-table">
            <thead><tr>${headerCells}</tr></thead>
            <tbody>${rows}</tbody>
          </table>
        </div>`;
    } catch (err) {
      showMessage(err.message, 'error');
    }
  });
}


// =============================================================================
// ADMIN PANEL — FORM LOAD HELPERS — SCHEDULE & ACCOUNTS
// =============================================================================

function loadScheduleIntoForm(row, setScheduleFormMode, scheduleCourseLookup) {
  setVal('schedule_id',         row.dataset.scheduleId);
  s2set('schedule_user_id',     row.dataset.userId);
  setVal('schedule_course_id',  row.dataset.courseId);
  s2set('schedule_day_of_week', DAY_UNABBR[row.dataset.dayOfWeek] || '');
  setFlatpickrTime('schedule_start_time_picker', row.dataset.startTime);
  setFlatpickrTime('schedule_end_time_picker',   row.dataset.endTime);

  if (scheduleCourseLookup) {
    const courseId = String(row.dataset.courseId);
    const matched  = Array.from(scheduleCourseLookup.options).find(opt => {
      try { return String(JSON.parse(opt.value).course_id) === courseId; } catch (_) { return false; }
    });
    s2set('schedule_course_lookup', matched ? matched.value : '');
  }

  setScheduleFormMode('edit');
}

function loadAccountIntoForm(row, accountForm, accountLookupResults, setAccountFormMode) {
  const roles = (row.dataset.roles || '').split(',').map(r => r.trim().toLowerCase()).filter(Boolean);

  setVal('account_user_id', row.dataset.userId);
  setVal('user_login',      row.dataset.userLogin || '');
  setVal('user_email',      row.dataset.userEmail || '');
  setVal('first_name',      row.dataset.firstName || '');
  setVal('last_name',       row.dataset.lastName  || '');

  accountForm.querySelectorAll('input[name="roles[]"]').forEach(cb => {
    cb.checked = roles.includes(cb.value.toLowerCase());
  });

  if (accountLookupResults) accountLookupResults.value = '';
  clearVal('account_search_query');
  setHidden('account_search_results', true);
  setAccountFormMode('edit');
}


// =============================================================================
// ADMIN PANEL — SCHEDULE SECTION
// =============================================================================

function initScheduleSection(scheduleForm, scheduleCourseLookup, setScheduleFormMode, resetScheduleForm, SCHEDULE_FIELD_IDS) {
  // --- Course lookup dropdown (non-Select2 fallback) ---

  on(scheduleCourseLookup, 'change', () => {
    if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') return;
    if (!scheduleCourseLookup.value) { clearVal('schedule_course_id'); return; }
    const selected = scheduleCourseLookup.options[scheduleCourseLookup.selectedIndex];
    if (!selected.dataset.newCourse) {
      scheduleCourseLookup.querySelector('option[data-new-course]')?.remove();
      $$('#course-search-list .account-search-item').forEach(el => el.classList.remove('selected'));
      clearVal('course_lookup_results');
    }
    try { setVal('schedule_course_id', JSON.parse(scheduleCourseLookup.value).course_id || ''); } catch (_) {}
  });

  // --- UMBC course search ---

  const searchUmbcCourses = (query) => searchUmbc({
    endpoint:      `/umbc_db/courses?search_str=${encodeURIComponent(query)}`,
    resultsBoxId:  'course_search_results',
    statusElId:    'course-search-status',
    listElId:      'course-search-list',
    collectionKey: 'umbc_courses',
    renderItem:    (c) => `
      <span class="account-search-item-name">${c.course_subject} ${c.course_code} \u2014 ${c.course_name}</span>
      <span class="account-search-item-meta">${c.subject_name}</span>`,
    onSelect: (course) => {
      const courseLookupResults = $('course_lookup_results');
      if (courseLookupResults) courseLookupResults.value = JSON.stringify(course);
      if (scheduleCourseLookup) {
        scheduleCourseLookup.querySelector('option[data-new-course]')?.remove();
        const opt             = document.createElement('option');
        opt.value             = 'new';
        opt.textContent       = 'New Course Selected';
        opt.dataset.newCourse = 'true';
        opt.selected          = true;
        scheduleCourseLookup.prepend(opt);
        if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
          jQuery('#schedule_course_lookup').trigger('change');
        }
      }
      setVal('schedule_course_id', course.course_id || '');
    },
    getLabel: (c) => `${c.course_subject} ${c.course_code} \u2014 ${c.course_name}`,
  });

  on($('course-search-submit'), 'click', async () => {
    const query = $('course_search_query').value.trim();
    if (!query) { showMessage('Please enter a search term.', 'error'); return; }
    await searchUmbcCourses(query);
  });

  onEnter($('course_search_query'), () => $('course-search-submit')?.click());

  // --- Schedule form submit ---

  on(scheduleForm, 'submit', async (e) => {
    e.preventDefault();

    const startTime = getFlatpickrTimeString('schedule_start_time_picker');
    const endTime   = getFlatpickrTimeString('schedule_end_time_picker');

    if (!startTime || !endTime) { showMessage('Error: Start Time and End Time are required.', 'error'); return; }
    if (endTime <= startTime)   { showMessage('Error: End Time must be after Start Time.', 'error'); return; }

    const id      = $('schedule_id').value.trim();
    const payload = {
      user_id:     Number($('schedule_user_id').value),
      course_id:   Number($('schedule_course_id').value),
      day_of_week: $('schedule_day_of_week').value,
      start_time:  startTime,
      end_time:    endTime,
    };

    try {
      const courseLookupResults = $('course_lookup_results');
      if (courseLookupResults?.value) {
        const newCourse = JSON.parse(courseLookupResults.value);
        if (newCourse.course_subject && newCourse.course_code && newCourse.course_name) {
          Object.assign(payload, {
            course_subject: newCourse.course_subject,
            course_code:    newCourse.course_code,
            course_name:    newCourse.course_name,
            course_id:      newCourse.course_id,
          });
        }
      }
    } catch (_) {}

    for (const row of $$('#schedule-table tbody tr')) {
      if (id && row.dataset.scheduleId === id) continue;
      if (Number(row.dataset.userId)   !== payload.user_id)   continue;
      if (Number(row.dataset.courseId) !== payload.course_id) continue;
      const rowDay = DAY_UNABBR[row.dataset.dayOfWeek] || row.dataset.dayOfWeek;
      if (rowDay !== payload.day_of_week) continue;
      if (payload.start_time < row.dataset.endTime && payload.end_time > row.dataset.startTime) {
        showMessage('Error: This schedule entry overlaps an existing one for the same tutor, course, and day.', 'error'); return;
      }
    }

    try {
      if (id) {
        await api.request(`/schedule/${id}`, 'PATCH', payload);
        upsertTableRow('schedule-table', 'schedule-id', id, buildScheduleRow({ ...payload, schedule_id: id }));
        showMessage(`Updated schedule entry ${id}.`);
      } else {
        const data = await api.request('/schedule', 'POST', payload);
        upsertTableRow('schedule-table', 'schedule-id', data.schedule_id, buildScheduleRow({ ...payload, schedule_id: data.schedule_id }));
        showMessage(`Created schedule entry ${data.schedule_id}.`);
      }

      let promotedCourse = null;
      const courseLookupResultsEl = $('course_lookup_results');
      if (courseLookupResultsEl?.value) {
        try { promotedCourse = JSON.parse(courseLookupResultsEl.value); } catch (_) {}
        courseLookupResultsEl.value = '';
      }

      scheduleCourseLookup?.querySelector('option[data-new-course]')?.remove();
      $$('#course-search-list .account-search-item').forEach(li => li.classList.remove('selected'));

      resetScheduleForm();

      if (promotedCourse?.course_id && scheduleCourseLookup) {
        const alreadyExists = Array.from(scheduleCourseLookup.options).some(opt => {
          try { return String(JSON.parse(opt.value).course_id) === String(promotedCourse.course_id); } catch (_) { return false; }
        });
        if (!alreadyExists) {
          const permOpt       = document.createElement('option');
          permOpt.value       = JSON.stringify(promotedCourse);
          permOpt.textContent = `${promotedCourse.course_subject} ${promotedCourse.course_code}`;
          scheduleCourseLookup.appendChild(permOpt);
          if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
            jQuery('#schedule_course_lookup').trigger('change.select2');
          }
        }
      }
    } catch (err) {
      showMessage(err.message, 'error');
    }
  });
}


// =============================================================================
// ADMIN PANEL — ACCOUNT SECTION
// =============================================================================

function initAccountSection(accountForm, accountLookupResults, setAccountFormMode, resetAccountForm, ACCOUNT_FIELD_IDS) {
  // --- UMBC account search ---

  const searchUmbcAccounts = (query) => searchUmbc({
    endpoint:      `/umbc_db/accounts?search_str=${encodeURIComponent(query)}`,
    resultsBoxId:  'account_search_results',
    statusElId:    'account-search-status',
    listElId:      'account-search-list',
    collectionKey: 'umbc_accounts',
    renderItem:    (a) => `
      <span class="account-search-item-name">${a.first_name} ${a.last_name}</span>
      <span class="account-search-item-meta">${a.umbc_id} &bull; ${a.umbc_email}</span>`,
    onSelect: (account) => {
      if (accountLookupResults) accountLookupResults.value = JSON.stringify(account);
      setVal('user_login', account.umbc_id    || '');
      setVal('user_email', account.umbc_email || '');
      setVal('first_name', account.first_name || '');
      setVal('last_name',  account.last_name  || '');
      setAccountFormMode('add');
    },
    getLabel: (a) => `${a.first_name} ${a.last_name} (${a.umbc_id})`,
  });

  on($('account-search-submit'), 'click', async () => {
    const query = $('account_search_query').value.trim();
    if (!query) { showMessage('Please enter a search term.', 'error'); return; }
    if (accountLookupResults) accountLookupResults.value = '';
    clearFields(ACCOUNT_FIELD_IDS);
    setAccountFormMode('add');
    await searchUmbcAccounts(query);
  });

  onEnter($('account_search_query'), () => $('account-search-submit')?.click());

  // --- Account form submit ---

  on(accountForm, 'submit', async (e) => {
    e.preventDefault();
    const id         = $('account_user_id').value.trim();
    const user_login = $('user_login').value.trim();
    const user_email = $('user_email').value.trim();
    const first_name = $('first_name').value.trim();
    const last_name  = $('last_name').value.trim();
    const roles      = Array.from(accountForm.querySelectorAll('input[name="roles[]"]:checked')).map(el => el.value);

    if (!roles.length) { showMessage('Select at least one role.', 'error'); return; }

    const ADMIN_ROLE = 'asc_admin';
    if (roles.includes(ADMIN_ROLE) && roles.length > 1) {
      showMessage('ASC Admin cannot be assigned with other roles.', 'error'); return;
    }

    const payload = { user_login, user_email, first_name, last_name, roles };
    try {
      if (id) {
        await api.request(`/accounts/${id}`, 'PATCH', payload);
        upsertTableRow('account-table', 'user-id', id, buildAccountRow({ ...payload, user_id: id }));
        showMessage(`Updated account ${id}.`);
      } else {
        const data = await api.request('/accounts', 'POST', payload);
        upsertTableRow('account-table', 'user-id', data.user_id, buildAccountRow({ ...payload, user_id: data.user_id }));
        showMessage(`Created account ${data.user_id}.`);
      }
      resetAccountForm();
    } catch (err) {
      showMessage(err.message, 'error');
    }
  });
}


// =============================================================================
// ADMIN PANEL — LOOKUP FORMS
// =============================================================================

function initLookupForms() {
  bindLookupForm({
    formId:        'lookup-accounts-form',
    queryId:       'lookup-accounts-query',
    resultsId:     'lookup-accounts-results',
    endpoint:      '/umbc_db/accounts',
    collectionKey: 'umbc_accounts',
    headers:       ['UMBC ID', 'Name', 'Email'],
    buildRow:      (a) => `<tr><td>${a.umbc_id}</td><td>${a.first_name} ${a.last_name}</td><td>${a.umbc_email}</td></tr>`,
  });

  bindLookupForm({
    formId:        'lookup-courses-form',
    queryId:       'lookup-courses-query',
    resultsId:     'lookup-courses-results',
    endpoint:      '/umbc_db/courses',
    collectionKey: 'umbc_courses',
    headers:       ['Course ID', 'Course', 'Name', 'Subject'],
    buildRow:      (c) => `<tr><td>${c.course_id}</td><td>${c.course_subject} ${c.course_code}</td><td>${c.course_name}</td><td>${c.subject_name}</td></tr>`,
  });
}


// =============================================================================
// ADMIN PANEL — AUDIT LOGS
// =============================================================================

function initLogsUI() {
  const fetchBtn  = $('logs-fetch-btn');
  const exportBtn = $('logs-export-btn');
  const viewer    = $('logs-viewer');
  const box       = $('logs-box');
  const emptyMsg  = $('logs-empty');
  const dateLabel = $('logs-date-label');
  const prevBtn   = $('logs-prev-btn');
  const nextBtn   = $('logs-next-btn');
  const jumpBtn   = $('logs-jump-btn');
  const jumpInput = $('logs-jump-date');

  if (!fetchBtn) return;

  let logsByDate  = {};
  let allDates    = [];
  let oldestDate  = null;
  let windowStart = null;

  // --- Date key helpers ---

  function toDateKey(date) {
    return date.toLocaleDateString('en-CA', { timeZone: 'America/New_York' });
  }

  function dateFromKey(key) {
    const [y, m, d] = key.split('-').map(Number);
    return new Date(y, m - 1, d);
  }

  function addDays(key, n) {
    const d = dateFromKey(key);
    d.setDate(d.getDate() + n);
    return toDateKey(d);
  }

  function formatLabel(key) {
    return dateFromKey(key).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
  }

  // --- Rendering ---

  function renderWindow(startKey) {
    const todayKey = toDateKey(new Date());
    if (startKey > todayKey)   startKey = todayKey;
    if (startKey < oldestDate) startKey = oldestDate;

    windowStart = startKey;
    const endKey = addDays(startKey, -6);

    const windowEntries = [];
    for (let i = 0; i < 7; i++) {
      const dayKey = addDays(startKey, -i);
      (logsByDate[dayKey] || []).forEach(entry => windowEntries.push(entry.lines.join('\n')));
    }

    dateLabel.textContent = `${formatLabel(endKey)} - ${formatLabel(startKey)}`;
    Array.from(box.childNodes).forEach(n => { if (n !== emptyMsg) n.remove(); });

    if (!windowEntries.length) {
      emptyMsg.hidden = false;
    } else {
      emptyMsg.hidden = true;
      windowEntries.forEach(text => {
        const span       = document.createElement('span');
        span.className   = 'logs-entry';
        span.textContent = text;
        box.appendChild(span);
      });
    }

    prevBtn.disabled = endKey <= oldestDate;
    nextBtn.disabled = startKey >= todayKey;
  }

  function buildExport() {
    return allDates
      .flatMap(date => (logsByDate[date] || []).map(e => e.lines.join('\n')))
      .join('\n');
  }

  // --- Event handlers ---

  on(exportBtn, 'click', () => {
    const blob = new Blob([buildExport()], { type: 'text/plain' });
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href     = url;
    a.download = `audit-logs-full-${toDateKey(new Date())}.txt`;
    a.click();
    URL.revokeObjectURL(url);
  });

  on(fetchBtn, 'click', async () => {
    fetchBtn.disabled = true;
    showMessage('Fetching logs\u2026');
    try {
      const data = await api.request('/logs');
      logsByDate = {};
      for (const entry of data.logs) {
        if (!logsByDate[entry.date]) logsByDate[entry.date] = [];
        logsByDate[entry.date].push(entry);
      }
      allDates         = Object.keys(logsByDate).sort();
      oldestDate       = allDates[0] || toDateKey(new Date());
      viewer.hidden    = false;
      exportBtn.hidden = false;
      renderWindow(toDateKey(new Date()));
      showMessage('Logs loaded.');
    } catch (err) {
      showMessage('Network error fetching logs.', 'error');
    } finally {
      fetchBtn.disabled = false;
    }
  });

  on(prevBtn, 'click', () => renderWindow(addDays(windowStart, -7)));
  on(nextBtn, 'click', () => renderWindow(addDays(windowStart,  7)));

  on(jumpBtn, 'click', () => {
    const val = jumpInput?.value;
    if (!val) { showMessage('Select a date first.', 'error'); return; }
    renderWindow(val);
    showMessage(`Jumped to week of ${formatLabel(val)}.`);
  });
}


// =============================================================================
// ADMIN PANEL — IMPORT / EXPORT
// =============================================================================

function initImportUI() {
  const importForm   = $('import-form');
  if (!importForm) return;

  const fileInput    = $('csv_file');
  const uploadBtn    = $('import-upload-btn');
  const resultPanel  = $('import-result-panel');
  const errorPanel   = $('import-error-panel');
  const errorBox     = $('import-error-box');
  const successPanel = $('import-success-panel');
  const previewBox   = $('import-preview-box');
  const confirmBtn   = $('import-confirm-btn');
  const cancelBtn    = $('import-cancel-btn');
  const templateLink = $('import-download-template');
  const exportLink   = $('import-export-db');

  let pendingToken = null;

  // --- Panel helpers ---

  function hideResultPanels() {
    if (resultPanel)  resultPanel.hidden  = true;
    if (errorPanel)   errorPanel.hidden   = true;
    if (successPanel) successPanel.hidden = true;
    if (errorBox)     errorBox.innerHTML  = '';
    if (previewBox)   previewBox.innerHTML = '';
    pendingToken = null;
  }

  // --- Error renderer (compiler-style, grouped by section) ---

  function renderErrors(errors) {
    if (!errorBox) return;
    if (!errors || !errors.length) {
      errorBox.innerHTML = "<p class='logs-empty'>No errors.</p>";
      return;
    }

    const bySection = {};
    for (const e of errors) {
      const sec = e.section || 'general';
      if (!bySection[sec]) bySection[sec] = [];
      bySection[sec].push(e);
    }

    const frag = document.createDocumentFragment();

    for (const [section, errs] of Object.entries(bySection)) {
      const header = document.createElement('span');
      header.className        = 'logs-entry';
      header.style.fontWeight = '700';
      header.style.color      = '#b71c1c';
      header.textContent      = `[${section.toUpperCase()}]`;
      frag.appendChild(header);

      for (const e of errs) {
        const entry     = document.createElement('span');
        entry.className = 'logs-entry';
        const rowStr    = e.row   ? `row ${e.row}`   : '';
        const fieldStr  = e.field ? `(${e.field})`   : '';
        const loc       = [rowStr, fieldStr].filter(Boolean).join(' ');
        entry.textContent = `  ${loc ? loc + ' \u2014 ' : ''}${e.message}`;
        frag.appendChild(entry);
      }
    }

    errorBox.innerHTML = '';
    errorBox.appendChild(frag);
  }

  // --- Preview renderer ---

  function renderPreview(preview) {
    if (!previewBox) return;
    previewBox.innerHTML = '';
    const frag = document.createDocumentFragment();

    const title = document.createElement('span');
    title.className        = 'logs-entry';
    title.style.fontWeight = '700';
    title.textContent      = 'The following data will replace what is currently in the database:';
    frag.appendChild(title);

    [
      `  Subjects  : ${preview.subjects}`,
      `  Courses   : ${preview.courses}`,
      `  Users     : ${preview.users}`,
      `  Schedule  : ${preview.schedule} entries`,
    ].forEach(line => {
      const entry       = document.createElement('span');
      entry.className   = 'logs-entry';
      entry.textContent = line;
      frag.appendChild(entry);
    });

    const note = document.createElement('span');
    note.className       = 'logs-entry';
    note.style.color     = '#555';
    note.style.fontStyle = 'italic';
    note.textContent     = '  Existing subjects, courses, tutors, schedule, and events will be fully replaced.';
    frag.appendChild(note);

    previewBox.appendChild(frag);
  }

  // --- Template download ---

  on(templateLink, 'click', (e) => {
    e.preventDefault();
    window.location.href = `${api.root}/asc-tutoring/v1/import/template?_wpnonce=${window.wpApiSettings?.nonce || ''}`;
  });

  // --- Export current DB ---

  on(exportLink, 'click', (e) => {
    e.preventDefault();
    window.location.href = `${api.root}/asc-tutoring/v1/import/export?_wpnonce=${window.wpApiSettings?.nonce || ''}`;
  });

  // --- Upload & validate (multipart — cannot use api.request) ---

  on(importForm, 'submit', async (e) => {
    e.preventDefault();
    clearMessages();
    hideResultPanels();

    const file = fileInput?.files[0];
    if (!file) {
      showMessage('Please select a CSV file.', 'error');
      return;
    }

    const formData = new FormData();
    formData.append('csv_file', file);

    if (uploadBtn) {
      uploadBtn.disabled    = true;
      uploadBtn.textContent = 'Validating\u2026';
    }

    let data;
    try {
      const res = await fetch(`${api.root}/asc-tutoring/v1/import/validate`, {
        method:  'POST',
        headers: { 'X-WP-Nonce': window.wpApiSettings?.nonce || '' },
        body:    formData,
      });
      data = await res.json().catch(() => ({}));
    } catch {
      showMessage('Network error. Please try again.', 'error');
      if (uploadBtn) { uploadBtn.disabled = false; uploadBtn.textContent = 'Validate & Preview'; }
      return;
    }

    if (uploadBtn) { uploadBtn.disabled = false; uploadBtn.textContent = 'Validate & Preview'; }

    if (data.code && data.message) {
      showMessage(data.message, 'error');
      return;
    }

    if (resultPanel) resultPanel.hidden = false;

    if (data.status === 'error') {
      if (errorPanel) errorPanel.hidden = false;
      renderErrors(data.errors);
      showMessage(
        `Validation failed \u2014 ${data.errors.length} issue${pluralSuffix(data.errors.length)} found.`,
        'error'
      );
    } else if (data.status === 'success') {
      pendingToken = data.token;
      if (successPanel) successPanel.hidden = false;
      renderPreview(data.preview);
      showMessage('Validation passed. Review the summary and confirm to import.', 'success');
    } else {
      showMessage('Unexpected response from server.', 'error');
    }
  });

  // --- Cancel ---

  on(cancelBtn, 'click', () => {
    hideResultPanels();
    clearMessages();
    if (fileInput) fileInput.value = '';
  });

  // --- Confirm import ---

  on(confirmBtn, 'click', async () => {
    if (!pendingToken) {
      showMessage('No pending import. Please re-upload the CSV.', 'error');
      return;
    }

    if (confirmBtn) {
      confirmBtn.disabled    = true;
      confirmBtn.textContent = 'Importing\u2026';
    }
    clearMessages();

    let data;
    try {
      data = await api.request('/import/confirm', 'POST', { token: pendingToken });
    } catch (err) {
      showMessage('Import failed: ' + err.message, 'error');
      if (confirmBtn) { confirmBtn.disabled = false; confirmBtn.textContent = 'Confirm Import'; }
      return;
    }

    if (confirmBtn) { confirmBtn.disabled = false; confirmBtn.textContent = 'Confirm Import'; }

    if (data.success) {
      const imp = data.imported;
      hideResultPanels();
      if (fileInput) fileInput.value = '';
      showMessage(
        `Import successful \u2014 ` +
        `${imp.subjects} subject${pluralSuffix(imp.subjects)}, ` +
        `${imp.courses} course${pluralSuffix(imp.courses)}, ` +
        `${imp.users} user${pluralSuffix(imp.users)}, ` +
        `${imp.schedule} schedule entr${imp.schedule !== 1 ? 'ies' : 'y'} loaded.`,
        'success'
      );
    } else {
      showMessage('Import failed. Please try again or contact an administrator.', 'error');
    }
  });
}


// =============================================================================
// ADMIN PANEL — ADMIN UI INIT
// =============================================================================

function initAdminUI() {
  const scheduleForm         = $('schedule-form');
  const accountForm          = $('account-form');
  const scheduleCourseLookup = $('schedule_course_lookup');
  const accountLookupResults = $('account_lookup_results');

  if (!scheduleForm && !accountForm) return;

  const SCHEDULE_FIELD_IDS = ['schedule_user_id', 'schedule_course_id', 'schedule_day_of_week'];
  const ACCOUNT_FIELD_IDS  = ['user_login', 'user_email', 'first_name', 'last_name'];

  // --- Form mode helpers ---

  const applyFormModeLabels = (labelId, resetId, isEdit, editLabel, addLabel) => {
    const label    = $(labelId);
    const resetBtn = $(resetId);
    if (label)    label.textContent    = isEdit ? editLabel : addLabel;
    if (resetBtn) resetBtn.textContent = isEdit ? 'Cancel'  : 'Clear';
  };

  const unlockFields = (ids) => ids.forEach(id => {
    const field = $(id);
    if (!field) return;
    field.readOnly = false;
    field.disabled = false;
    field.classList.remove('account-field-locked');
  });

  const setRolesEditable = (editable) => {
    accountForm.querySelectorAll('input[name="roles[]"]').forEach(cb => {
      cb.disabled     = !editable;
      cb.style.cursor = editable ? '' : 'not-allowed';
    });
  };

  const setAccountSearchEditable = (editable) => {
    const input = $('account_search_query');
    const btn   = $('account-search-submit');
    [input, btn].forEach(el => {
      if (!el) return;
      el.disabled     = !editable;
      el.style.cursor = editable ? '' : 'not-allowed';
    });
  };

  const setScheduleFormMode = (mode) => {
    const isEdit = mode === 'edit';
    applyFormModeLabels('schedule-form-mode-label', 'reset-schedule-form', isEdit, 'Editing Schedule Entry', 'Create New Schedule Entry');
    if (isEdit) { unlockFields(SCHEDULE_FIELD_IDS); return; }
    const hasSelected = !!scheduleCourseLookup?.value;
    if (hasSelected) { unlockFields(SCHEDULE_FIELD_IDS); return; }
    clearFields(SCHEDULE_FIELD_IDS);
    setFlatpickrTime('schedule_start_time_picker', '');
    setFlatpickrTime('schedule_end_time_picker', '');
  };

  const setAccountFormMode = (mode) => {
    const isEdit = mode === 'edit';
    applyFormModeLabels('account-form-mode-label', 'reset-account-form', isEdit, 'Editing Account Permissions', 'Add New Account');
    setAccountSearchEditable(!isEdit);
    if (isEdit) { setRolesEditable(true); return; }
    const hasSelected = !!accountLookupResults?.value;
    setRolesEditable(hasSelected);
    if (!hasSelected) clearFields(ACCOUNT_FIELD_IDS);
  };

  // --- Reset form functions ---

  function resetScheduleForm() {
    scheduleForm.reset();
    s2reset('schedule_user_id');
    s2reset('schedule_course_lookup');
    s2reset('schedule_day_of_week');
    if (scheduleCourseLookup) scheduleCourseLookup.value = '';
    scheduleCourseLookup?.querySelector('option[data-new-course]')?.remove();
    clearVal('schedule_id');
    clearVal('course_search_query');
    clearVal('course_lookup_results');
    clearFields(SCHEDULE_FIELD_IDS);
    setFlatpickrTime('schedule_start_time_picker', '');
    setFlatpickrTime('schedule_end_time_picker', '');
    setHidden('course_search_results', true);
    const courseList = $('course-search-list');
    if (courseList) courseList.innerHTML = '';
    setScheduleFormMode('add');
  }

  function resetAccountForm() {
    accountForm.reset();
    clearVal('account_user_id');
    clearVal('account_search_query');
    if (accountLookupResults) accountLookupResults.value = '';
    const accountSearchList = $('account-search-list');
    if (accountSearchList) accountSearchList.innerHTML = '';
    clearFields(ACCOUNT_FIELD_IDS);
    setHidden('account_search_results', true);
    setAccountFormMode('add');
  }

  // --- Tabs ---

  initTabSwitching();

  // --- Edit buttons (schedule & accounts) ---

  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.admin-edit-schedule, .admin-edit-account');
    if (!btn) return;
    const row = btn.closest('tr');
    if (!row) return;

    if (btn.classList.contains('admin-edit-schedule')) {
      loadScheduleIntoForm(row, setScheduleFormMode, scheduleCourseLookup);
      showMessage(`Loaded schedule ${row.dataset.scheduleId} into the form.`, 'success');
    } else if (btn.classList.contains('admin-edit-account')) {
      loadAccountIntoForm(row, accountForm, accountLookupResults, setAccountFormMode);
      showMessage(`Loaded account ${row.dataset.userId}.`, 'success');
    }
  });

  // --- Delete buttons (schedule & accounts) ---

  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.admin-delete-schedule');
    if (!btn) return;
    const row = btn.closest('tr');
    if (!row) return;
    const id = row.dataset.scheduleId;
    if (!confirm(`Delete schedule ${id}?`)) return;

    try {
      await api.request(`/schedule/${id}`, 'DELETE');
      removeTableRow('schedule', id);
      resetScheduleForm();
      showMessage(`Deleted schedule ${id}.`);
    } catch (err) {
      showMessage(err.message, 'error');
    }
  });

  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.admin-delete-account');
    if (!btn) return;
    const row = btn.closest('tr');
    if (!row) return;
    const id = row.dataset.userId;
    if (!confirm(`Delete account ${id}?`)) return;

    try {
      await api.request(`/accounts/${id}`, 'DELETE');
      removeTutorRelatedRows(id);
      removeTableRow('account', id);
      resetAccountForm();
      showMessage(`Deleted account ${id}.`);
    } catch (err) {
      showMessage(err.message, 'error');
    }
  });

  // --- Delete course schedule button ---

  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.admin-delete-course-schedule');
    if (!btn) return;
    const row = btn.closest('tr');
    if (!row) return;
    const courseId = row.dataset.courseId;
    if (!confirm(`Delete all schedule entries for course ${courseId}?`)) return;

    try {
      await api.request(`/schedule/course/${courseId}`, 'DELETE');
      $$(`#schedule-table tbody tr[data-course-id="${courseId}"]`).forEach(r => r.remove());
      reapplyTableFilter('schedule-table');
      showMessage(`Deleted all schedule entries for course ${courseId}.`);
    } catch (err) {
      showMessage(err.message, 'error');
    }
  });

  // --- Reset buttons ---

  on($('reset-schedule-form'), 'click', resetScheduleForm);
  on($('reset-account-form'),  'click', resetAccountForm);

  // --- Schedule section ---

  if (scheduleForm) {
    initScheduleSection(scheduleForm, scheduleCourseLookup, setScheduleFormMode, resetScheduleForm, SCHEDULE_FIELD_IDS);
  }

  // --- Account section ---

  if (accountForm) {
    initAccountSection(accountForm, accountLookupResults, setAccountFormMode, resetAccountForm, ACCOUNT_FIELD_IDS);
  }

  // --- Lookup forms ---

  initLookupForms();

  // --- Set initial form modes ---

  if (accountForm)  setAccountFormMode('add');
  if (scheduleForm) setScheduleFormMode('add');
}


// =============================================================================
// BOOT
// =============================================================================

document.addEventListener('DOMContentLoaded', () => {
  initAdminTables();
  initAdminUI();
  initScheduleFlatpickr();
  initScheduleSelect2($('schedule_course_lookup'));
  initLogsUI();
  initImportUI();
});