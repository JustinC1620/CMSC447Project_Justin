document.addEventListener('DOMContentLoaded', function () {
  const triggers = document.querySelectorAll('.sights-expander-trigger');

  triggers.forEach(function (trigger) {
    trigger.addEventListener('click', function () {
      const contentId = trigger.getAttribute('aria-controls');
      const content = document.getElementById(contentId);
      if (!content) return;

      const isExpanded = trigger.getAttribute('aria-expanded') === 'true';

      trigger.setAttribute('aria-expanded', isExpanded ? 'false' : 'true');
      content.classList.toggle('sights-expander-hidden', isExpanded);
    });

    trigger.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        trigger.click();
      }
    });
  });
});

document.addEventListener('DOMContentLoaded', function () {
  const filterButtons = document.querySelectorAll('.subject-filter-button');
  const subjectSections = document.querySelectorAll('.subject-section');

  filterButtons.forEach(function (button) {
    button.addEventListener('click', function () {
      const subject = button.getAttribute('data-subject');

      filterButtons.forEach(function (btn) {
        btn.classList.remove('active');
      });
      button.classList.add('active');

      subjectSections.forEach(function (section) {
        const sectionSubject = section.getAttribute('data-subject');

        if (subject === 'all' || subject === sectionSubject) {
          section.style.display = '';
        } else {
          section.style.display = 'none';
        }
      });
    });
  });
});