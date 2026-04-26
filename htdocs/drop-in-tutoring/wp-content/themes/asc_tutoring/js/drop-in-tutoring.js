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
    });
  });
}


// =============================================================================
// BOOT
// =============================================================================

document.addEventListener('DOMContentLoaded', () => {
  initExpanders();
  initSubjectFilters();
});