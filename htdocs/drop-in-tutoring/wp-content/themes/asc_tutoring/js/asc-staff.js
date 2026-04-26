// =============================================================================
// ADMIN PANEL — MESSAGES
// =============================================================================

const messageBoxes = $$('.tutoring-admin-message');

const showMessage = (text, type = 'success') => {
  messageBoxes.forEach(box => {
    box.textContent = text;
    box.className   = `tutoring-admin-message ${type}`;
    box.hidden      = false;
    setTimeout(() => { box.hidden = true; }, 4000);
  });
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

const clearMessages = () => {
  messageBoxes.forEach(box => {
    box.textContent = '';
    box.classList.remove('success', 'error');
    box.hidden = true;
  });
};


// =============================================================================
// API CLIENT
// =============================================================================

const api = {
  root: (window.wpApiSettings?.root || '/wp-json').replace(/\/$/, ''),

  headers() {
    return {
      'Content-Type': 'application/json',
      'X-WP-Nonce': window.wpApiSettings?.nonce || '',
    };
  },

  async request(endpoint, method = 'GET', body = null) {
    const options = { method, headers: this.headers() };
    if (body)             options.body = JSON.stringify(body);
    if (method === 'GET') delete options.headers['Content-Type'];

    const res  = await fetch(`${this.root}/asc-tutoring/v1${endpoint}`, options);
    const data = await res.json().catch(() => ({}));
    if (!res.ok) throw new Error(data.message || 'Request failed');
    return data;
  },
};


// =============================================================================
// DOM / TABLE HELPERS
// =============================================================================

function findTableRow(tableId, attr, id) {
  return qs(`#${tableId} tbody tr[data-${attr}="${id}"]`);
}

function removeTableRow(entityLabel, id) {
  const attr    = entityLabel !== 'account' ? entityLabel : 'user';
  const tableId = `${entityLabel}-table`;
  findTableRow(tableId, `${attr}-id`, id)?.remove();
  reapplyTableFilter(tableId);
}

function upsertTableRow(tableId, attr, id, rowHTML) {
  const tbody = qs(`#${tableId} tbody`);
  if (!tbody) return;

  const temp = document.createElement('tbody');
  temp.innerHTML = rowHTML.trim();
  const newRow = temp.firstElementChild;

  const existing = findTableRow(tableId, attr, id);
  existing ? existing.replaceWith(newRow) : tbody.prepend(newRow);

  reapplyTableFilter(tableId);
}

function removeTutorRelatedRows(userId) {
  $$(`#event-table    tbody tr[data-user-id="${userId}"]`).forEach(r => r.remove());
  $$(`#schedule-table tbody tr[data-user-id="${userId}"]`).forEach(r => r.remove());
}


// =============================================================================
// ROW BUILDERS — EVENTS
// =============================================================================

const EVENT_TYPE_KEYS = {
  '1': 'called_out',
  '2': 'late',
  '3': 'leaving_early',
  '4': 'at_capacity',
};

const DAY_ABBR   = { Monday: 'MON', Tuesday: 'TUE', Wednesday: 'WED', Thursday: 'THU', Friday: 'FRI' };
const DAY_UNABBR = { MON: 'Monday', TUE: 'Tuesday', WED: 'Wednesday', THU: 'Thursday', FRI: 'Friday' };

function resolveUserLabel(userId, fallback) {
  if (fallback) return fallback;
  const userRow  = qs(`#account-table tr[data-user-id="${userId}"]`);
  const nameCell = userRow?.children[1]?.textContent?.trim();
  const idCell   = userRow?.children[0]?.textContent?.trim();
  return nameCell ? `${nameCell}${idCell ? ` (${idCell})` : ''}` : String(userId);
}

function buildEventRow(e) {
  const typeKey     = EVENT_TYPE_KEYS[e.event_type];
  const finalDay    = typeKey === 'called_out'    ? formatDisplayDate(e.final_day) : '--';
  const leavingTime = typeKey === 'leaving_early' ? e.leaving_time                 : '--';

  const userLabel      = resolveUserLabel(e.user_id);
  const typeOption     = qs(`#event_type option[value="${e.event_type}"]`);
  const eventTypeLabel = typeOption ? typeOption.textContent.trim() : String(e.event_type);

  return `
    <tr
      data-event-id="${e.event_id}"
      data-user-id="${e.user_id}"
      data-event-type="${e.event_type}"
      data-start-day="${e.start_day}"
      data-final-day="${e.final_day || ''}"
      data-leaving-time="${e.leaving_time || ''}"
    >
      <td>${userLabel}</td>
      <td>${eventTypeLabel}</td>
      <td>${formatDisplayDate(e.start_day)}</td>
      <td>${finalDay}</td>
      <td>${formatDisplayTime(leavingTime)}</td>
      <td>
        <button type="button" class="button button-primary admin-edit-event">Edit</button>
        <button type="button" class="button button-secondary admin-delete-event">Delete</button>
      </td>
    </tr>`;
}


// =============================================================================
// ADMIN PANEL — FLATPICKR TIME HELPERS
// =============================================================================

function timeStringToDate(timeValue) {
  if (!timeValue) return null;
  const match = String(timeValue).trim().match(/^(\d{1,2}):(\d{2})(?::\d{2})?/);
  if (!match) return null;
  const d = new Date();
  d.setHours(parseInt(match[1], 10), parseInt(match[2], 10), 0, 0);
  return d;
}

function setFlatpickrTime(instanceOrId, timeValue) {
  const fp = typeof instanceOrId === 'string'
    ? $(instanceOrId)?._flatpickr
    : instanceOrId;
  if (!fp) return;
  const d = timeStringToDate(timeValue);
  if (d) { fp.setDate(d, true); } else { fp.clear(); }
}

function getFlatpickrTimeString(id) {
  const fp = $(id)?._flatpickr;
  if (!fp?.selectedDates.length) return null;
  const d  = fp.selectedDates[0];
  const hh = String(d.getHours()).padStart(2, '0');
  const mm = String(d.getMinutes()).padStart(2, '0');
  return `${hh}:${mm}:00`;
}

function initEventFlatpickr() {
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

  const el = $('leaving_time_picker');
  if (el) flatpickr(el, { ...FLATPICKR_CONFIG });
}


// =============================================================================
// ADMIN PANEL — EVENT TYPE FIELD TOGGLE
// =============================================================================

var toggleEventFields = () => {};

function initEventFields() {
  const eventType = $('event_type');
  if (!eventType) return;

  const dateRangeFields   = $('date-range-fields');
  const leavingEarlyField = $('leaving-early-field');
  const today             = new Date().toLocaleDateString('en-CA', { timeZone: 'America/New_York' });

  const hideFieldGroup = (group, defaultDate = '') => {
    group.style.display = 'none';
    group.querySelectorAll('input, select').forEach(i => { i.removeAttribute('required'); i.value = defaultDate; });
  };

  const showFieldGroup = (group) => {
    group.style.display = 'block';
    group.querySelectorAll('input, select').forEach(i => i.setAttribute('required', ''));
  };

  toggleEventFields = function () {
    const selectedText = eventType.options[eventType.selectedIndex].text.toLowerCase();
    const calledOut    = selectedText.includes('called out');
    const leavingEarly = selectedText.includes('leaving early');

    if (!calledOut)    hideFieldGroup(dateRangeFields, today);
    if (!leavingEarly) hideFieldGroup(leavingEarlyField);
    if (calledOut) {
      showFieldGroup(dateRangeFields);
      dateRangeFields.querySelectorAll('input').forEach(i => { i.value = ''; });
    } else if (leavingEarly) {
      showFieldGroup(leavingEarlyField);
    }
  };

  on(eventType, 'change', toggleEventFields);
  toggleEventFields();
}


// =============================================================================
// ADMIN PANEL — FORM LOAD HELPERS — EVENTS
// =============================================================================

function loadEventIntoForm(row, setEventFormMode) {
  s2set('event_type',    row.dataset.eventType);
  toggleEventFields();
  setVal('event_id',     row.dataset.eventId);
  s2set('event_user_id', row.dataset.userId);
  setVal('start_day',    row.dataset.startDay);
  setVal('final_day',    row.dataset.finalDay);
  setFlatpickrTime('leaving_time_picker', row.dataset.leavingTime || '');
  setEventFormMode('edit');
}


// =============================================================================
// ADMIN PANEL — SELECT2 — EVENTS
// =============================================================================

const EVENT_SELECT2_IDS = ['event_user_id', 'event_type'];

function s2set(id, value) {
  if (typeof jQuery === 'undefined' || typeof jQuery.fn.select2 === 'undefined') {
    setVal(id, value);
    return;
  }
  jQuery(`#${id}`).val(value).trigger('change');
}

function s2reset(id) {
  s2set(id, '');
}

function initEventSelect2() {
  if (typeof jQuery === 'undefined' || typeof jQuery.fn.select2 === 'undefined') return;

  EVENT_SELECT2_IDS.forEach(id => {
    const el = $(id);
    if (!el) return;
    jQuery(`#${id}`).select2({
      width:       '100%',
      allowClear:  true,
      placeholder: el.options[0]?.text || 'Select\u2026',
    });
  });

  jQuery('#event_type').on('select2:select select2:clear', () => toggleEventFields());
}


// =============================================================================
// ADMIN PANEL — TABLE FILTERING
// =============================================================================

const TABLE_FILTER_STATE = {};

function normalizeFilterText(value) {
  return String(value || '').trim().toLowerCase();
}

function escapeHtml(value) {
  return String(value)
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');
}

function getFilterableHeaders(table) {
  return Array.from(table.querySelectorAll('thead th'))
    .map((th, index) => ({ index, label: th.textContent.trim() }))
    .filter(col => normalizeFilterText(col.label) !== 'actions');
}

function getUniqueColumnValues(table, columnIndex) {
  const values = new Map();

  table.querySelectorAll('tbody tr').forEach(row => {
    const raw        = row.children[columnIndex]?.textContent?.trim() || '';
    const normalized = normalizeFilterText(raw);
    if (!raw) return;
    if (!values.has(normalized)) values.set(normalized, raw);
  });

  return Array.from(values.values()).sort((a, b) =>
    a.localeCompare(b, undefined, { numeric: true, sensitivity: 'base' })
  );
}

function initTableFilterState(tableId) {
  TABLE_FILTER_STATE[tableId] = { appliedColumnIndex: '', appliedQuery: '' };
}

function applyTableFilter(tableId) {
  const table = $(tableId);
  const state = TABLE_FILTER_STATE[tableId];
  if (!table || !state) return;

  const query = normalizeFilterText(state.appliedQuery);

  table.querySelectorAll('tbody tr').forEach(row => {
    if (state.appliedColumnIndex === '' || !query) { row.hidden = false; return; }
    const cellValue = row.children[state.appliedColumnIndex]?.textContent?.trim() || '';
    row.hidden = normalizeFilterText(cellValue) !== query;
  });
}

function reapplyTableFilter(tableId) {
  if (!TABLE_FILTER_STATE[tableId]) return;
  applyTableFilter(tableId);
}

function hasSelect2() {
  return typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined';
}

function initTableFilterSelect2(selectEl, placeholder) {
  if (!hasSelect2() || !selectEl) return;
  const $select = jQuery(selectEl);
  if ($select.hasClass('select2-hidden-accessible')) return;
  $select.select2({
    width:                   '100%',
    allowClear:              true,
    placeholder,
    minimumResultsForSearch: 0,
    dropdownParent:          $select.closest('.admin-table-filter'),
  });
}

function getTableFilterSelectValue(selectEl) {
  if (!selectEl) return '';
  if (hasSelect2() && jQuery(selectEl).hasClass('select2-hidden-accessible')) {
    return jQuery(selectEl).val() || '';
  }
  return selectEl.value || '';
}

function setTableFilterSelectValue(selectEl, value) {
  if (!selectEl) return;
  selectEl.value = value;
  if (hasSelect2() && jQuery(selectEl).hasClass('select2-hidden-accessible')) {
    jQuery(selectEl).val(value).trigger('change.select2');
  }
}

function resetTableFilterSearchSelect(searchSelect) {
  if (!searchSelect) return;
  searchSelect.innerHTML = '<option value=""></option>';
  searchSelect.disabled  = true;
  setTableFilterSelectValue(searchSelect, '');
}

function rebuildTableFilterSearchOptions(tableId, columnIndex) {
  const table        = $(tableId);
  const wrapper      = qs(`.admin-table-filter[data-table-id="${tableId}"]`);
  const searchSelect = wrapper?.querySelector('.admin-table-filter-search-select');
  if (!table || !searchSelect) return;

  resetTableFilterSearchSelect(searchSelect);
  if (columnIndex === '') return;

  getUniqueColumnValues(table, Number(columnIndex)).forEach(value => {
    const opt       = document.createElement('option');
    opt.value       = value;
    opt.textContent = value;
    searchSelect.appendChild(opt);
  });

  searchSelect.disabled = false;
  if (hasSelect2()) jQuery(searchSelect).trigger('change.select2');
}

function buildTableFilterUI(table) {
  const tableId = table.id;
  const columns = getFilterableHeaders(table);

  initTableFilterState(tableId);

  const filter = document.createElement('div');
  filter.className       = 'admin-table-filter';
  filter.dataset.tableId = tableId;

  filter.innerHTML = `
    <div class="admin-table-filter-row">
      <label class="admin-table-filter-label">
        <strong>Filter by</strong>
      </label>

      <select class="admin-table-filter-column-select" aria-label="Select filter column for ${tableId}">
        <option value=""></option>
        ${columns.map(col => `<option value="${col.index}">${escapeHtml(col.label)}</option>`).join('')}
      </select>

      <div class="admin-table-filter-search-wrap">
        <select
          class="admin-table-filter-search-select"
          aria-label="Filter search for ${tableId}"
          disabled
        >
          <option value=""></option>
        </select>
      </div>

      <button type="button" class="button button-primary admin-table-filter-search">Search</button>
      <button type="button" class="button button-secondary admin-table-filter-clear">Clear</button>
    </div>
  `;

  table.parentNode.insertBefore(filter, table);

  const columnSelect = filter.querySelector('.admin-table-filter-column-select');
  const searchSelect = filter.querySelector('.admin-table-filter-search-select');
  const searchBtn    = filter.querySelector('.admin-table-filter-search');
  const clearBtn     = filter.querySelector('.admin-table-filter-clear');

  initTableFilterSelect2(columnSelect, 'Select column');
  initTableFilterSelect2(searchSelect, 'Start typing to search...');

  const handleColumnChange = () => {
    TABLE_FILTER_STATE[tableId].appliedColumnIndex = '';
    TABLE_FILTER_STATE[tableId].appliedQuery       = '';
    rebuildTableFilterSearchOptions(tableId, getTableFilterSelectValue(columnSelect));
    applyTableFilter(tableId);
  };

  if (hasSelect2()) jQuery(columnSelect).on('select2:select select2:clear', handleColumnChange);
  on(columnSelect, 'change', handleColumnChange);

  on(searchBtn, 'click', () => {
    TABLE_FILTER_STATE[tableId].appliedColumnIndex = getTableFilterSelectValue(columnSelect);
    TABLE_FILTER_STATE[tableId].appliedQuery       = getTableFilterSelectValue(searchSelect);
    applyTableFilter(tableId);
  });

  on(clearBtn, 'click', () => {
    initTableFilterState(tableId);
    setTableFilterSelectValue(columnSelect, '');
    resetTableFilterSearchSelect(searchSelect);
    applyTableFilter(tableId);
  });
}


// =============================================================================
// ADMIN PANEL — TABLE SORTING
// =============================================================================

function parseDisplayTimeForSort(text) {
  const value = String(text || '').trim().toLowerCase();
  if (value === 'noon')     return 12 * 60;
  if (value === 'midnight') return 0;

  const match = value.match(/^(\d{1,2}):(\d{2})\s*(a\.m\.|p\.m\.|am|pm)$/);
  if (!match) return null;

  let hour       = Number(match[1]);
  const minute   = Number(match[2]);
  const ampm     = match[3];

  if (ampm.startsWith('a') && hour === 12) hour = 0;
  if (ampm.startsWith('p') && hour !== 12) hour += 12;

  return hour * 60 + minute;
}

function getSortValue(row, columnIndex, headerLabel) {
  const text  = row.children[columnIndex]?.textContent.trim() || '';
  const label = headerLabel.toLowerCase();

  if (label === 'day') {
    const dayOrder = { monday: 1, tuesday: 2, wednesday: 3, thursday: 4, friday: 5 };
    return dayOrder[text.toLowerCase()] || 999;
  }

  if (label.includes('time')) {
    const parsedTime = parseDisplayTimeForSort(text);
    if (parsedTime !== null) return parsedTime;
  }

  return text.toLowerCase();
}

function sortTable(table, columnIndex, ascending = true) {
  const tbody = table.querySelector('tbody');
  if (!tbody) return;

  const headerLabel = table.querySelectorAll('thead th')[columnIndex]?.childNodes[0]?.textContent.trim() || '';
  const rows        = Array.from(tbody.querySelectorAll('tr'));

  rows.sort((a, b) => {
    const aVal = getSortValue(a, columnIndex, headerLabel);
    const bVal = getSortValue(b, columnIndex, headerLabel);

    if (typeof aVal === 'number' && typeof bVal === 'number') {
      return ascending ? aVal - bVal : bVal - aVal;
    }
    return ascending
      ? String(aVal).localeCompare(String(bVal), undefined, { numeric: true })
      : String(bVal).localeCompare(String(aVal), undefined, { numeric: true });
  });

  rows.forEach(row => tbody.appendChild(row));
}

function addSortArrowsToTable(table) {
  table.querySelectorAll('thead th').forEach((th, index) => {
    if (th.textContent.trim().toLowerCase() === 'actions') return;

    const wrapper     = document.createElement('span');
    wrapper.className = 'table-sort-arrows';
    wrapper.innerHTML = `
      <button type="button" class="sort-up"   aria-label="Sort ascending">▲</button>
      <button type="button" class="sort-down" aria-label="Sort descending">▼</button>
    `;
    th.appendChild(wrapper);

    on(wrapper.querySelector('.sort-up'),   'click', () => sortTable(table, index, true));
    on(wrapper.querySelector('.sort-down'), 'click', () => sortTable(table, index, false));
  });
}


// =============================================================================
// ADMIN PANEL — TABLE INIT — EVENTS ONLY
// =============================================================================

function initEventTable() {
  const table = $('event-table');
  if (!table) return;
  buildTableFilterUI(table);
  addSortArrowsToTable(table);
}


// =============================================================================
// ADMIN PANEL — EVENT SECTION INIT
// =============================================================================

function initEventSection(eventForm, setEventFormMode, resetEventForm) {
  on(eventForm, 'submit', async (e) => {
    e.preventDefault();
    const id      = $('event_id').value.trim();
    const payload = {
      user_id:      Number($('event_user_id').value),
      event_type:   Number($('event_type').value),
      start_day:    $('start_day').value,
      final_day:    $('final_day').value || null,
      leaving_time: getFlatpickrTimeString('leaving_time_picker'),
    };

    if (payload.final_day && payload.start_day && payload.final_day < payload.start_day) {
      showMessage('Error: Final Day must be the same as or after Start Day.', 'error'); return;
    }

    const EVENT_TYPE_CALLED_OUT = 1;

    for (const row of $$('#event-table tbody tr')) {
      if (id && row.dataset.eventId === id) continue;
      if (Number(row.dataset.userId) !== payload.user_id) continue;
      const rowType = Number(row.dataset.eventType);
      if (rowType !== payload.event_type) continue;

      if (payload.event_type !== EVENT_TYPE_CALLED_OUT) {
        showMessage('Error: This tutor already has an event of this type.', 'error'); return;
      } else {
        const rowStart = row.dataset.startDay;
        const rowEnd   = row.dataset.finalDay || row.dataset.startDay;
        const newStart = payload.start_day;
        const newEnd   = payload.final_day || payload.start_day;
        if (newStart <= rowEnd && newEnd >= rowStart) {
          showMessage('Error: This tutor already has a called out event overlapping that date range.', 'error'); return;
        }
      }
    }

    try {
      if (id) {
        await api.request(`/events/${id}`, 'PATCH', payload);
        upsertTableRow('event-table', 'event-id', id, buildEventRow({ ...payload, event_id: id }));
        showMessage(`Updated event ${id}.`);
      } else {
        const data = await api.request('/events', 'POST', payload);
        upsertTableRow('event-table', 'event-id', data.event_id, buildEventRow({ ...payload, event_id: data.event_id }));
        showMessage(`Created event ${data.event_id}.`);
      }
      resetEventForm();
    } catch (err) {
      showMessage(err.message, 'error');
    }
  });
}


// =============================================================================
// ADMIN PANEL — STAFF UI INIT
// =============================================================================

function initStaffUI() {
  const eventForm = $('event-form');
  if (!eventForm) return;

  // --- Form mode helpers ---

  const applyFormModeLabels = (labelId, resetId, isEdit, editLabel, addLabel) => {
    const label    = $(labelId);
    const resetBtn = $(resetId);
    if (label)    label.textContent    = isEdit ? editLabel : addLabel;
    if (resetBtn) resetBtn.textContent = isEdit ? 'Cancel'  : 'Clear';
  };

  const setEventFormMode = (mode) => {
    applyFormModeLabels('event-form-mode-label', 'reset-event-form', mode === 'edit', 'Editing Event', 'Create New Event');
  };

  // --- Reset event form ---

  function resetEventForm() {
    eventForm.reset();
    s2reset('event_user_id');
    s2reset('event_type');
    $('event_type').selectedIndex = 0;
    clearVal('event_id');
    setFlatpickrTime('leaving_time_picker', '');
    toggleEventFields();
    setEventFormMode('add');
  }

  // --- Edit buttons (events) ---

  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.admin-edit-event');
    if (!btn) return;
    const row = btn.closest('tr');
    if (!row) return;
    loadEventIntoForm(row, setEventFormMode);
    showMessage(`Loaded event ${row.dataset.eventId} into the form.`, 'success');
  });

  // --- Delete buttons (events) ---

  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.admin-delete-event');
    if (!btn) return;
    const row = btn.closest('tr');
    if (!row) return;
    const id = row.dataset.eventId;
    if (!confirm(`Delete event ${id}?`)) return;

    try {
      await api.request(`/events/${id}`, 'DELETE');
      removeTableRow('event', id);
      resetEventForm();
      showMessage(`Deleted event ${id}.`);
    } catch (err) {
      showMessage(err.message, 'error');
    }
  });

  // --- Reset button ---

  on($('reset-event-form'), 'click', resetEventForm);

  // --- Event section ---

  initEventSection(eventForm, setEventFormMode, resetEventForm);
}


// =============================================================================
// BOOT
// =============================================================================

document.addEventListener('DOMContentLoaded', () => {
  initEventTable();
  initStaffUI();
  initEventFields();
  initEventFlatpickr();
  initEventSelect2();
});