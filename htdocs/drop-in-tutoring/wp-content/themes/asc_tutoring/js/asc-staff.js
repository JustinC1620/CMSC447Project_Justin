// =============================================================================
// VALIDATION — BUBBLE & OUTLINE HELPERS
// =============================================================================

function getFieldAnchor(id) {
  const el = $(id);
  if (!el) return null;
  if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
    const s2 = el.nextElementSibling;
    if (s2 && s2.classList.contains('select2-container')) return s2;
  }
  return el;
}

let _activeBubble   = null;
let _bubbleCleanups = [];

function clearValidationBubble() {
  if (_activeBubble) { _activeBubble.remove(); _activeBubble = null; }
  _bubbleCleanups.forEach(fn => fn());
  _bubbleCleanups = [];
}

function showFieldError(fieldId, message) {
  clearValidationBubble();

  const anchor = getFieldAnchor(fieldId);
  if (!anchor) { showMessage(message, 'error'); return; }

  const wrapper = anchor.closest('div') || anchor.parentElement;
  if (wrapper) wrapper.classList.add('field-invalid');

  const bubble = document.createElement('div');
  bubble.className   = 'validation-bubble';
  bubble.textContent = message;
  document.body.appendChild(bubble);
  _activeBubble = bubble;

  function positionBubble() {
    const rect   = anchor.getBoundingClientRect();
    const spaceBelow = window.innerHeight - rect.bottom;
    const above  = spaceBelow < 80;

    bubble.classList.toggle('bubble-below', !above);
    bubble.classList.toggle('bubble-above',  above);

    const top = above
      ? window.scrollY + rect.top  - bubble.offsetHeight - 8
      : window.scrollY + rect.bottom + 8;

    bubble.style.left = `${window.scrollX + rect.left}px`;
    bubble.style.top  = `${top}px`;
  }

  positionBubble();
  window.addEventListener('resize', positionBubble);
  window.addEventListener('scroll', positionBubble, true);

  function dismiss() { clearValidationBubble(); }

  const nativeEl = $(fieldId);
  if (nativeEl) {
    const handler = () => { dismiss(); if (wrapper) wrapper.classList.remove('field-invalid'); };

    nativeEl.addEventListener('change', handler, { once: true });
    nativeEl.addEventListener('input',  handler, { once: true });
    anchor.addEventListener('mousedown', handler, { once: true });

    if (typeof jQuery !== 'undefined' && nativeEl.classList.contains('select2-hidden-accessible')) {
      jQuery(nativeEl).one('select2:select select2:clear', handler);
    }

    _bubbleCleanups.push(() => {
      window.removeEventListener('resize', positionBubble);
      window.removeEventListener('scroll', positionBubble, true);
      nativeEl.removeEventListener('change', handler);
      nativeEl.removeEventListener('input',  handler);
      anchor.removeEventListener('mousedown', handler);
      if (wrapper) wrapper.classList.remove('field-invalid');
    });
  }
}


function clearFieldErrors(formEl) {
  formEl.querySelectorAll('.field-invalid').forEach(el => el.classList.remove('field-invalid'));
  clearValidationBubble();
}


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
  if (d) {
    fp.setDate(d, true);
  } else {
    fp.clear();
    fp.set('defaultHour', 12);
    fp.set('defaultMinute', 0);
  }
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

  let _prevTypeText = '';

  const hideFieldGroup = (group, defaultDate = '') => {
    group.style.display = 'none';

    group.querySelectorAll('input, select').forEach(i => {
      if (i._flatpickr) {
        i._flatpickr.clear();
        i._flatpickr.set('defaultHour', 12);
        i._flatpickr.set('defaultMinute', 0);
      }
      else {
        i.value = defaultDate;

        if (window.jQuery && jQuery(i).hasClass('select2-hidden-accessible')) {
          jQuery(i).val(defaultDate).trigger('change');
        }
      }
    });
  };

  const showFieldGroup = (group) => {
    group.style.display = 'block';

    // When a static flatpickr lives inside a hidden container, its hour/minute
    // display inputs don't render until the container is visible. Redraw them
    // now without touching selectedDates.
    group.querySelectorAll('input').forEach(i => {
      const fp = i._flatpickr;
      if (!fp) return;
      const hourInput = fp.calendarContainer?.querySelector('.flatpickr-hour');
      const minInput  = fp.calendarContainer?.querySelector('.flatpickr-minute');
      const ampmInput = fp.calendarContainer?.querySelector('.flatpickr-am-pm');
      if (!hourInput) return;

      if (fp.selectedDates.length) {
        // A real value is set — re-apply it so the display matches
        fp.setDate(fp.selectedDates[0], false);
      } else {
        // No value selected — paint the default 12:00 PM visually only
        hourInput.value = '12';
        minInput.value  = '00';
        if (ampmInput) ampmInput.value = 'PM';
      }
    });
  };

  toggleEventFields = function () {
    const selectedText = eventType.options[eventType.selectedIndex].text.toLowerCase();
    const calledOut    = selectedText.includes('called out');
    const leavingEarly = selectedText.includes('leaving early');

    const wasCalledOut    = _prevTypeText.includes('called out');
    const wasLeavingEarly = _prevTypeText.includes('leaving early');

    // Clear time when leaving away from leaving_early
    if (wasLeavingEarly && !leavingEarly) {
      setFlatpickrTime('leaving_time_picker', '');
    }

    // Clear dates when leaving away from called_out
    if (wasCalledOut && !calledOut) {
      const startDay = $('start_day');
      const finalDay = $('final_day');
      if (startDay) startDay.value = today;
      if (finalDay) finalDay.value = '';
    }

    if (!calledOut)    hideFieldGroup(dateRangeFields, today);
    if (!leavingEarly) hideFieldGroup(leavingEarlyField);

    if (calledOut) {
      showFieldGroup(dateRangeFields);
      dateRangeFields.querySelectorAll('input').forEach(i => { i.value = ''; });
    } else if (leavingEarly) {
      showFieldGroup(leavingEarlyField);
    }

    _prevTypeText = selectedText;
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

function initTableFilterHandlers(table) {
  const tableId = table.id;
  const filter  = qs(`.admin-table-filter[data-table-id="${tableId}"]`);
  if (!filter) return;

  initTableFilterState(tableId);

  const columnSelect = filter.querySelector('.admin-table-filter-column-select');
  const searchSelect = filter.querySelector('.admin-table-filter-search-select');
  const searchBtn    = filter.querySelector('.admin-table-filter-search');
  const clearBtn     = filter.querySelector('.admin-table-filter-clear');
  const valueLabel   = filter.querySelector('.admin-table-filter-value-label');

  initTableFilterSelect2(columnSelect, 'Select column');
  initTableFilterSelect2(searchSelect, 'Start typing to search...');

  const updateValueLabel = () => {
    if (!valueLabel) return;
    const selected = columnSelect.options[columnSelect.selectedIndex];
    const text = selected?.value !== '' ? selected?.text?.trim() : '';
    valueLabel.innerHTML = `<strong>${text || 'Value'}</strong>`;
  };

  const handleColumnChange = () => {
    TABLE_FILTER_STATE[tableId].appliedColumnIndex = '';
    TABLE_FILTER_STATE[tableId].appliedQuery       = '';
    rebuildTableFilterSearchOptions(tableId, getTableFilterSelectValue(columnSelect));
    applyTableFilter(tableId);
    updateValueLabel();
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
    updateValueLabel();
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

function initTableSortHandlers(table) {
  table.querySelectorAll('thead th').forEach((th, index) => {
    const wrapper = th.querySelector('.table-sort-arrows');
    if (!wrapper) return;
    on(wrapper.querySelector('.sort-up'),   'click', () => sortTable(table, index, true));
    on(wrapper.querySelector('.sort-down'), 'click', () => sortTable(table, index, false));
  });
}


// =============================================================================
// ADMIN PANEL — TABLE INIT
// =============================================================================

function initAdminTable(tableId) {
  const table = $(tableId);
  if (!table) return;
  initTableFilterHandlers(table);
  initTableSortHandlers(table);
}

function initEventTable() {
  initAdminTable('event-table');
}


// =============================================================================
// ADMIN PANEL — EVENT SECTION INIT
// =============================================================================

let _eventFormSnapshot = null;

function captureEventFormSnapshot() {
  _eventFormSnapshot = {
    user_id:      $('event_user_id').value,
    event_type:   $('event_type').value,
    start_day:    $('start_day').value,
    final_day:    $('final_day').value,
    leaving_time: getFlatpickrTimeString('leaving_time_picker') || '',
  };
}

function clearEventFormSnapshot() {
  _eventFormSnapshot = null;
}

function initEventSection(eventForm, setEventFormMode, resetEventForm) {
  on(eventForm, 'submit', async (e) => {
    e.preventDefault();
    clearFieldErrors(eventForm);

    const id            = $('event_id').value.trim();
    const isEdit        = !!id;
    const today         = new Date().toLocaleDateString('en-CA', { timeZone: 'America/New_York' });
    const selectedText  = $('event_type').options[$('event_type').selectedIndex]?.text.toLowerCase() || '';
    const isCalledOut   = selectedText.includes('called out');
    const isLeavingEarly = selectedText.includes('leaving early');

    // ---- Required: Tutor ----
    if (!$('event_user_id').value) {
      showFieldError('event_user_id', 'Please select a tutor.');
      showMessage('Error: Tutor is required.', 'error');
      return;
    }

    // ---- Required: Event Type ----
    if (!$('event_type').value) {
      showFieldError('event_type', 'Please select an event type.');
      showMessage('Error: Event Type is required.', 'error');
      return;
    }

    // ---- Conditional: Leaving Early — Time required and must fall within schedule ----
    const leavingTime = getFlatpickrTimeString('leaving_time_picker');
    if (isLeavingEarly) {
      if (!leavingTime) {
        showFieldError('leaving_time_picker', 'Please select a time.');
        showMessage('Error: Time is required for a leaving early event.', 'error');
        return;
      }

      const userId  = Number($('event_user_id').value);
      const todayDow = new Date().toLocaleDateString('en-US', { weekday: 'long', timeZone: 'America/New_York' });
      const todayAbbr = { Monday:'MON', Tuesday:'TUE', Wednesday:'WED', Thursday:'THU', Friday:'FRI' }[todayDow];

      let withinSchedule = false;
      for (const row of $$('#schedule-table tbody tr')) {
        if (Number(row.dataset.userId) !== userId) continue;
        const rowDay = row.dataset.dayOfWeek;
        const fullDay = DAY_UNABBR[rowDay] || rowDay;
        if (fullDay !== todayDow && rowDay !== todayAbbr) continue;
        if (leavingTime > row.dataset.startTime && leavingTime < row.dataset.endTime) {
          withinSchedule = true;
          break;
        }
      }

      if (!withinSchedule) {
        showFieldError('leaving_time_picker', 'Time must fall within the tutor\'s scheduled hours for today.');
        showMessage('Error: Time must fall within the tutor\'s scheduled hours for today.', 'error');
        return;
      }
    }

    // ---- Conditional: Called Out — both dates required, order valid, no overlap ----
    const startDay = $('start_day').value;
    const finalDay = $('final_day').value || null;

    if (isCalledOut) {
      if (!startDay) {
        showFieldError('start_day', 'Please select a start date.');
        showMessage('Error: Start Date is required for a called out event.', 'error');
        return;
      }
      if (!finalDay) {
        showFieldError('final_day', 'Please select an end date.');
        showMessage('Error: End Date is required for a called out event.', 'error');
        return;
      }
      if (finalDay < startDay) {
        showFieldError('final_day', 'End Date cannot be before Start Date.');
        showMessage('Error: End Date cannot be before Start Date.', 'error');
        return;
      }
    }

    const payload = {
      user_id:      Number($('event_user_id').value),
      event_type:   Number($('event_type').value),
      start_day:    isCalledOut ? startDay : today,
      final_day:    isCalledOut ? finalDay : null,
      leaving_time: isLeavingEarly ? leavingTime : null,
    };

    // ---- Duplicate / overlap checks ----
    const EVENT_TYPE_CALLED_OUT = 1;

    for (const row of $$('#event-table tbody tr')) {
      if (isEdit && row.dataset.eventId === id) continue;
      if (Number(row.dataset.userId) !== payload.user_id) continue;
      const rowType = Number(row.dataset.eventType);
      if (rowType !== payload.event_type) continue;

      if (payload.event_type !== EVENT_TYPE_CALLED_OUT) {
        showMessage('Error: This tutor already has an event of this type.', 'error');
        return;
      } else {
        const rowStart = row.dataset.startDay;
        const rowEnd   = row.dataset.finalDay || row.dataset.startDay;
        const newStart = payload.start_day;
        const newEnd   = payload.final_day   || payload.start_day;
        if (newStart <= rowEnd && newEnd >= rowStart) {
          showFieldError('start_day', 'Overlaps an existing called out event for this tutor.');
          showMessage('Error: This tutor already has a called out event overlapping that date range.', 'error');
          return;
        }
      }
    }

    // ---- Edit mode: require at least one changed field ----
    if (isEdit && _eventFormSnapshot) {
      const current = {
        user_id:      $('event_user_id').value,
        event_type:   $('event_type').value,
        start_day:    isCalledOut   ? startDay    : today,
        final_day:    isCalledOut   ? (finalDay || '') : '',
        leaving_time: isLeavingEarly ? (leavingTime || '') : '',
      };
      const snap = {
        user_id:      _eventFormSnapshot.user_id,
        event_type:   _eventFormSnapshot.event_type,
        start_day:    _eventFormSnapshot.start_day,
        final_day:    _eventFormSnapshot.final_day    || '',
        leaving_time: _eventFormSnapshot.leaving_time || '',
      };
      const changed = Object.keys(current).some(k => current[k] !== snap[k]);
      if (!changed) {
        showMessage('No changes detected.', 'error');
        return;
      }
    }

    // ---- Submit ----
    try {
      if (isEdit) {
        await api.request(`/events/${id}`, 'PATCH', payload);
        upsertTableRow('event-table', 'event-id', id, buildEventRow({ ...payload, event_id: id }));
        showMessage(`Updated event ${id}.`);
      } else {
        const data = await api.request('/events', 'POST', payload);
        upsertTableRow('event-table', 'event-id', data.event_id, buildEventRow({ ...payload, event_id: data.event_id }));
        showMessage(`Created event ${data.event_id}.`);
      }
      clearEventFormSnapshot();
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
    captureEventFormSnapshot();
    showMessage(`Loaded event ${row.dataset.eventId} into the form.`, 'success');
    window.scrollTo({ top: 0, behavior: 'smooth' });
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
      clearEventFormSnapshot();
      resetEventForm();
      showMessage(`Deleted event ${id}.`);
    } catch (err) {
      showMessage(err.message, 'error');
    }
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });

  // --- Reset button ---

  on($('reset-event-form'), 'click', () => { clearEventFormSnapshot(); resetEventForm(); });

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