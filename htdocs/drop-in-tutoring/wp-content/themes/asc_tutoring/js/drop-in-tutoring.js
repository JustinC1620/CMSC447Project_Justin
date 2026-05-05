// =============================================================================
// SHARED HELPERS
// =============================================================================

// Ensures no extra bottom margin on whichever subject-section is currently last visible
function updateFirstVisibleSection() {
  const sections = $$('.subject-section');
  let lastVisible = null;
  sections.forEach(sec => {
    if (sec.style.display !== 'none') lastVisible = sec;
  });
  sections.forEach(sec => {
    sec.style.marginBottom = sec === lastVisible ? '0' : '';
  });
}


// =============================================================================
// EXPANDERS
// =============================================================================

function initExpanders() {
  $$('.sights-expander-trigger').forEach(trigger => {
    const toggle = () => {
      const content  = $(trigger.getAttribute('aria-controls'));
      if (!content) return;
      const expanded = trigger.getAttribute('aria-expanded') === 'true';
      trigger.setAttribute('aria-expanded', String(!expanded));
      content.classList.toggle('sights-expander-hidden', expanded);
    };

    on(trigger, 'click', toggle);
    on(trigger, 'keydown', (e) => {
      if (['Enter', ' '].includes(e.key)) { e.preventDefault(); toggle(); }
    });
  });
}


// =============================================================================
// SUBJECT FILTERS
// =============================================================================

function initSubjectFilters() {
  const buttons  = $$('.subject-filter-button');
  const sections = $$('.subject-section');

  buttons.forEach(btn => {
    on(btn, 'click', () => {
      const subject = btn.dataset.subject;
      buttons.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      sections.forEach(sec => toggleDisplay(sec, subject === 'all' || sec.dataset.subject === subject));
      updateFirstVisibleSection();
    });
  });
}


// =============================================================================
// DAY FILTERS
// =============================================================================

function initDayFilters() {
  const tiles = $$('.day-filter-tile');

  const DAY_LABEL = {
    MON: 'Monday',
    TUE: 'Tuesday',
    WED: 'Wednesday',
    THU: 'Thursday',
    FRI: 'Friday',
  };

  // Reverse map: full label → code, built from the same source of truth
  const LABEL_TO_CODE = Object.fromEntries(
    Object.entries(DAY_LABEL).map(([code, label]) => [label, code])
  );

  const NO_AVAIL_CLASS = 'tutoring-no-days-message';
  const NO_AVAIL_TEXT  = 'No tutoring available on the selected days.';

  function getActiveDays() {
    const active = new Set();
    tiles.forEach(tile => {
      if (tile.classList.contains('active')) active.add(tile.dataset.day);
    });
    return active;
  }

  // Returns or creates the "no availability" <p> that lives just after the table wrapper
  function getOrCreateNoAvailMsg(tableWrapper) {
    let msg = tableWrapper.nextElementSibling;
    if (!msg || !msg.classList.contains(NO_AVAIL_CLASS)) {
      msg = document.createElement('p');
      msg.className = NO_AVAIL_CLASS;
      msg.textContent = NO_AVAIL_TEXT;
      msg.style.display = 'none';
      tableWrapper.after(msg);
    }
    return msg;
  }

  function applyDayFilter() {
    const activeDays = getActiveDays();

    $$('.umbc-table-wrapper').forEach(wrapper => {
      const table      = wrapper.querySelector('.umbc-table');
      const noAvailMsg = getOrCreateNoAvailMsg(wrapper);
      if (!table) return;

      let currentDayCode    = null;
      let visibleRows       = 0;
      let visibleDayGroups  = 0;
      let inVisibleDayGroup = false;

      table.querySelectorAll('tbody tr').forEach(row => {
        const dayCell = row.querySelector('.tutoring-day-cell');
        if (dayCell) {
          currentDayCode = LABEL_TO_CODE[dayCell.textContent.trim()] ?? null;
          const groupVisible = currentDayCode !== null && activeDays.has(currentDayCode);

          if (groupVisible) {
            dayCell.classList.toggle('row-even', visibleDayGroups % 2 === 1);
            dayCell.classList.toggle('row-odd',  visibleDayGroups % 2 === 0);
            visibleDayGroups++;
          }

          inVisibleDayGroup = groupVisible;
        }

        const visible = inVisibleDayGroup;
        row.style.display = visible ? '' : 'none';

        if (visible) {
          row.classList.toggle('row-even', visibleRows % 2 === 1);
          row.classList.toggle('row-odd',  visibleRows % 2 === 0);
          visibleRows++;
        } else {
          row.classList.remove('row-even', 'row-odd');
        }
      });

      wrapper.style.display    = visibleRows > 0 ? '' : 'none';
      noAvailMsg.style.display = visibleRows > 0 ? 'none' : '';
    });

    updateFirstVisibleSection();
  }

  tiles.forEach(tile => {
    on(tile, 'click', () => {
      const nowActive = !tile.classList.contains('active');
      tile.classList.toggle('active', nowActive);
      tile.setAttribute('aria-pressed', String(nowActive));
      applyDayFilter();
    });
  });
}


// =============================================================================
// BOOT
// =============================================================================

document.addEventListener('DOMContentLoaded', () => {
  initExpanders();
  initSubjectFilters();
  initDayFilters();
  updateFirstVisibleSection();
});