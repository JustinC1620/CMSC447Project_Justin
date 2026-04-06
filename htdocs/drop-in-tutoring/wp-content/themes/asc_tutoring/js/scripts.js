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


function throttle(callback, limit) {
  var waiting = false;
  return function () {
    if (!waiting) {
      callback.apply(this, arguments);
      waiting = true;
      setTimeout(function () {
        waiting = false;
      }, limit);
    }
  };
}

var DOMAnimations = {
  slideUp: function (element, duration = 500) {
    return new Promise(function (resolve) {
      element.style.height = element.offsetHeight + 'px';
      element.style.transitionProperty = 'height, margin, padding';
      element.style.transitionDuration = duration + 'ms';
      element.offsetHeight;
      element.style.overflow = 'hidden';
      element.style.height = 0;
      element.style.paddingTop = 0;
      element.style.paddingBottom = 0;
      element.style.marginTop = 0;
      element.style.marginBottom = 0;
      window.setTimeout(function () {
        element.style.display = 'none';
        element.style.removeProperty('height');
        element.style.removeProperty('padding-top');
        element.style.removeProperty('padding-bottom');
        element.style.removeProperty('margin-top');
        element.style.removeProperty('margin-bottom');
        element.style.removeProperty('overflow');
        element.style.removeProperty('transition-duration');
        element.style.removeProperty('transition-property');
        resolve(false);
      }, duration);
    });
  },

  slideDown: function (element, duration = 500) {
    return new Promise(function (resolve) {
      element.style.removeProperty('display');
      let display = window.getComputedStyle(element).display;
      if (display === 'none') display = 'block';
      element.style.display = display;
      let height = element.offsetHeight;
      element.style.overflow = 'hidden';
      element.style.height = 0;
      element.style.paddingTop = 0;
      element.style.paddingBottom = 0;
      element.style.marginTop = 0;
      element.style.marginBottom = 0;
      element.offsetHeight;
      element.style.transitionProperty = 'height, margin, padding';
      element.style.transitionDuration = duration + 'ms';
      element.style.height = height + 'px';
      element.style.removeProperty('padding-top');
      element.style.removeProperty('padding-bottom');
      element.style.removeProperty('margin-top');
      element.style.removeProperty('margin-bottom');
      window.setTimeout(function () {
        element.style.removeProperty('height');
        element.style.removeProperty('overflow');
        element.style.removeProperty('transition-duration');
        element.style.removeProperty('transition-property');
      }, duration);
    });
  },

  slideToggle: function (element, duration = 500) {
    if (window.getComputedStyle(element).display === 'none') {
      return this.slideDown(element, duration);
    } else {
      return this.slideUp(element, duration);
    }
  }
};

function chevron_button(text) {
  return `
    <button>
      <span class="icon-chevron" aria-hidden="true">
        <svg viewBox="0 0 1024 661" xmlns="http://www.w3.org/2000/svg"><path d="m459.2 639.05c28.8 28.79 76.8 28.79 105.6 0l435.2-435.05c32-32 32-80 0-108.77l-70.4-73.64c-32-28.79-80-28.79-108.8 0l-310.4 310.33-307.2-310.33c-28.8-28.79-76.8-28.79-108.8 0l-70.4 73.59c-32 28.82-32 76.82 0 108.82z"/></svg>
      </span>
      <span class="sr-only">Toggle submenu for ${text}</span>
    </button>
  `;
}

let menu_items_has_children = document.querySelectorAll('.top-level > .sub-menu li.menu-item-has-children:not(.sub-menu .sub-menu li.menu-item-has-children), li.top-level.menu-item-has-children');

let top_level_menu_items = document.querySelectorAll(".top-level");

let menu_toggle = document.querySelector(".menu-toggle");
let whole_menu = document.querySelector("#primary-menu");

let menu_toggle_content = document.querySelector(".menu-toggle .menu-toggle-content");

const navigation_wrapper = document.querySelector('.navigation-wrapper');

let windowWidth;
let document_width;

let menu_navigation_duration = "300";

let thing_to_animate = document.querySelector(".navigation-wrapper");

menu_toggle.addEventListener('click', (el) => {
  el.preventDefault();

  DOMAnimations.slideToggle(thing_to_animate, menu_navigation_duration);

  if (el.target.getAttribute('aria-expanded') == 'true') {
    el.target.setAttribute('aria-expanded', false);
    menu_toggle_content.innerHTML = 'Menu';
    navigation_wrapper.classList.toggle('open');
    document.querySelector('body').classList.toggle('mobile-menu-open');

    document.querySelector('.mobile-header-title a').setAttribute('tabindex', -1);
    document.querySelector('.umbc-logo-wrapper').setAttribute('tabindex', 0);

    menu_items_has_children.forEach((menu_item) => {
      menu_item.classList.remove('menu-hover', 'open');
      menu_item.querySelectorAll('.sub-menu').forEach((mi) => {
        mi.classList.remove('open');
      });
    });

  } else {
    el.target.setAttribute('aria-expanded', true);
    menu_toggle_content.innerHTML = 'Close';
    navigation_wrapper.classList.toggle('open');
    document.querySelector('body').classList.toggle('mobile-menu-open');
    document.querySelector('.mobile-header-title a').setAttribute('tabindex', 0);
    document.querySelector('.umbc-logo-wrapper').setAttribute('tabindex', -1);
  }
});

menu_items_has_children.forEach((el) => {
  let activatingA = el.querySelector("a");
  let btn = chevron_button(activatingA.text);
  activatingA.insertAdjacentHTML("afterend", btn);
});

let resize_fn = throttle(() => {
  if (window.innerWidth != windowWidth) {
    document_width = window.innerWidth;

    if (document_width > 768) {
      desktop_navigation_enable();
    } else {
      mobile_navigation_enable();
    }

    windowWidth = window.innerWidth;
  }
}, 50);

window.addEventListener("resize", resize_fn);

function desktop_navigation_enable() {
  navigation_wrapper.style.display = "block";

  document.querySelector('body').classList.remove('mobile-menu-open');
  navigation_wrapper.classList.remove('open');
  menu_toggle_content.innerHTML = 'Menu';

  menu_items_has_children.forEach((menu_item) => {
    menu_item.classList.remove('menu-hover', 'open');
    menu_item.querySelectorAll('.sub-menu').forEach((mi) => {
      mi.classList.remove('open');
    });
  });

  menu_items_has_children.forEach((menu) => {
    let menu_rect = menu.getBoundingClientRect();
    let menu_item_has_submenus = menu.querySelectorAll(".sub-menu").length > 1;
    let sub_menu_width = menu.querySelector('.sub-menu').getBoundingClientRect().width + 16;
    let width_of_menu_item = menu_item_has_submenus ? sub_menu_width * 2 : sub_menu_width;

    if (menu_rect.x + width_of_menu_item > document_width) {
      menu.classList.add('too-wide');
    } else {
      menu.classList.remove('too-wide');
    }
  });

  top_level_menu_items.forEach((tlmi) => {
    tlmi.addEventListener('mouseover', (el) => {
      top_level_menu_items.forEach((open_el) => {
        open_el.classList.add("menu-disable");
        open_el.querySelectorAll("menu-item").forEach((mi) => {
          mi.classList.remove("menu-hover");
        });
      });
    });
  });

  document.querySelectorAll('.top-level > a').forEach((tlmia) => {
    tlmia.addEventListener('focus', (item) => {
      whole_menu.classList.add('menu-instant');

      top_level_menu_items.forEach((tlmi) => {
        tlmi.classList.add('menu-disable');
        tlmi.classList.remove('menu-hover');

        tlmi.querySelectorAll("li").forEach((mi) => {
          mi.classList.remove("menu-hover");
        });

        tlmi.querySelectorAll('.sub-menu').forEach((submenu) => {
          submenu.classList.remove('open');
        });
      });
      item.target.closest('.top-level').classList.remove('menu-disable');
    }, true);
  });

  document.querySelectorAll('.top-level > button').forEach((button) => {
    button.addEventListener("click", (event) => {
      whole_menu.classList.add('menu-instant');
      event.preventDefault();
      event.target.closest('.top-level').classList.toggle('menu-hover');
      event.target.closest('.top-level').querySelectorAll('.menu-item').forEach((submenu) => {
        submenu.classList.remove('menu-hover');
      });
    });
  });

  document.querySelectorAll('.sub-menu button').forEach((button) => {
    button.addEventListener("click", (event) => {
      whole_menu.classList.add('menu-instant');
      event.preventDefault();

      if (!event.target.closest('.menu-item').classList.contains("menu-hover")) {
        function demo() {
          event.target.closest('.top-level').querySelectorAll('.menu-hover').forEach((mh) => {
            mh.classList.remove('menu-hover');
          });
          return Promise.resolve("Success");
        }

        demo().then(() => {
          event.target.closest('.menu-item').classList.add('menu-hover');
        });
      } else {
        event.target.closest('.menu-item').classList.remove('menu-hover');
      }
    });
  });

  menu_items_has_children.forEach((menu_item) => {
    menu_item.addEventListener('mouseover', () => {
      whole_menu.classList.remove('menu-instant');
      menu_item.classList.add('menu-hover');
      menu_item.classList.remove('menu-disable');
      menu_item.classList.remove('menu-item-instant');
    });

    menu_item.addEventListener('mouseleave', (item) => {
      menu_item.classList.remove('menu-hover', 'open');

      if (item.relatedTarget) {
        if (item.relatedTarget.closest("li")) {
          if (item.relatedTarget.closest("li").classList.contains("menu-item")) {
            item.target.classList.add("menu-item-instant");
          }
        }
      }

      document.querySelectorAll('.menu-disable').forEach((mi) => {
        mi.classList.remove('menu-disable');
      });
      menu_item.querySelectorAll('.sub-menu').forEach((mi) => {
        mi.classList.remove('open');
      });
    });

    menu_item.querySelector("button").addEventListener("focus", (event) => {
      event.target.closest('.top-level').classList.remove('menu-disable');
    });
  });
}

let touchmoved;

function mobile_navigation_enable() {
  navigation_wrapper.style.removeProperty('display');

  if ('ontouchstart' in window) {
    menu_items_has_children.forEach((menu_item) => {
      menu_item.addEventListener("touchend", (event) => {
        if (event.target.getAttribute('data-clickable') == 'false' && touchmoved == false) {
          whole_menu.classList.add('menu-instant');
          let parent = event.currentTarget.parentNode;
          parent.classList.add("menu-hover");
          menu_item.classList.add('menu-hover');
          event.preventDefault();
          event.stopPropagation();
          event.target.setAttribute('data-clickable', 'true');
        }
      });

      menu_item.addEventListener("touchmove", (event) => {
        touchmoved = true;
      });

      menu_item.addEventListener("touchstart", (event) => {
        touchmoved = false;
      });
    });

    document.querySelectorAll('.menu-item-has-children > a').forEach((link) => {
      link.setAttribute('data-clickable', 'false');
    });

  } else {
    menu_items_has_children.forEach((menu_item) => {
      menu_item.querySelector("button").addEventListener("click", (event) => {
        whole_menu.classList.add('menu-instant');
        event.preventDefault();
        let parent = event.currentTarget.parentNode;
        let sub_menu = parent.querySelector(".sub-menu");
        sub_menu.classList.toggle("open");
        parent.classList.toggle("menu-hover");

        sub_menu.querySelectorAll('.sub-menu').forEach((sm) => {
          sm.classList.remove("open");
          sm.parentNode.classList.remove('menu-hover');
        });
      });

      menu_item.addEventListener('mouseover', () => {
        whole_menu.classList.remove('menu-instant');
        menu_item.classList.add('menu-hover');
        menu_item.classList.remove('menu-disable');
      });
    });
  }
}

document.addEventListener("DOMContentLoaded", () => {
  resize_fn();
});

document.addEventListener('DOMContentLoaded', () => {
  const messageBox = document.getElementById('tutoring-admin-message');

  const showMessage = (text, type = 'success') => {
    messageBox.textContent = text;
    messageBox.className = `tutoring-admin-message ${type}`;
    messageBox.hidden = false;
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  const getNonceHeaders = () => ({
    'Content-Type': 'application/json',
    'X-WP-Nonce': (window.wpApiSettings && window.wpApiSettings.nonce) ? window.wpApiSettings.nonce : ''
  });

  const apiRoot = (window.wpApiSettings && window.wpApiSettings.root)
    ? window.wpApiSettings.root.replace(/\/$/, '')
    : '/wp-json';

  const requestJson = async (endpoint, method = 'GET', body = null) => {
    const options = {
      method,
      headers: getNonceHeaders()
    };

    if (body !== null) {
      options.body = JSON.stringify(body);
    } else if (method === 'GET') {
      delete options.headers['Content-Type'];
    }

    const response = await fetch(`${apiRoot}/asc-tutoring/v1${endpoint}`, options);
    const data = await response.json().catch(() => ({}));

    if (!response.ok) {
      throw new Error(data.message || 'Request failed.');
    }

    return data;
  };

  const tabs = document.querySelectorAll('.admin-tab');
  const sections = document.querySelectorAll('.admin-section');

  tabs.forEach(tab => {
    tab.addEventListener('click', () => {
      tabs.forEach(btn => btn.classList.remove('active'));
      sections.forEach(section => section.classList.remove('active'));
      tab.classList.add('active');
      document.getElementById(`admin-tab-${tab.dataset.tab}`)?.classList.add('active');
    });
  });

  const scheduleForm = document.getElementById('schedule-form');
  const eventForm = document.getElementById('event-form');
  const accountForm = document.getElementById('account-form');
  const scheduleCourseLookup = document.getElementById('schedule_course_lookup');

  const scheduleCourseFieldIds = [
    'schedule_user_id',
    'schedule_course_id',
    'schedule_day_of_week',
    'schedule_start_time',
    'schedule_end_time',
  ];

  const scheduleTimeDropdownIds = [
  'schedule_start_time_hour',
  'schedule_start_time_minute',
  'schedule_start_time_ampm',
  'schedule_end_time_hour',
  'schedule_end_time_minute',
  'schedule_end_time_ampm',
];

  const accountLookupResults = document.getElementById('account_lookup_results');

  const accountFieldIds = ['user_login', 'user_email', 'first_name', 'last_name'];

  const fillAccountFormFromUmbc = (account) => {
    document.getElementById('user_login').value = account.umbc_id || '';
    document.getElementById('user_email').value = account.umbc_email || '';
    document.getElementById('first_name').value = account.first_name || '';
    document.getElementById('last_name').value = account.last_name || '';
  };

  const clearAccountTextFields = () => {
    accountFieldIds.forEach((id) => {
      const field = document.getElementById(id);
      if (field) field.value = '';
    });
  };

  const setRolesEditable = (editable) => {
    accountForm.querySelectorAll('input[name="roles[]"]').forEach((cb) => {
      cb.disabled = !editable;
      cb.style.cursor = editable ? '' : 'not-allowed';
    });
  };

  const setAccountSearchEditable = (editable) => {
    const searchInput = document.getElementById('account_search_query');
    const searchBtn = document.getElementById('account-search-submit');
    if (searchInput) searchInput.disabled = !editable;
    if (searchBtn) searchBtn.disabled = !editable;
  };

  const setAccountFormMode = (mode) => {
    const isEditMode = mode === 'edit';

    // Disable the search UI while editing an existing record
    setAccountSearchEditable(!isEditMode);

    if (isEditMode) {
      setRolesEditable(true);
      return;
    }

    const hasSelectedAccount = !!(accountLookupResults && accountLookupResults.value);

    setRolesEditable(hasSelectedAccount);

    if (!hasSelectedAccount) {
      clearAccountTextFields();
    }
  };

const fillScheduleFormFromCourse = (course) => {
  document.getElementById('schedule_course_id').value = course.course_id || '';
};

const clearScheduleCourseFields = () => {
  scheduleCourseFieldIds.forEach((id) => {
    const field = document.getElementById(id);
    if (field) field.value = '';
  });
};

const setScheduleCourseFieldsEditable = (editable) => {
  scheduleCourseFieldIds.forEach((id) => {
    const field = document.getElementById(id);
    if (!field) return;
    field.readOnly = false;
    field.disabled = false;
    field.classList.remove('account-field-locked');
  });
};

const setScheduleTimeDropdownsEditable = (editable) => {
  scheduleTimeDropdownIds.forEach((id) => {
    const field = document.getElementById(id);
    if (!field) return;
    field.disabled = false;
    field.classList.remove('account-field-locked');
  });
};

function setTimeDropdowns(prefix, timeValue) {
  const hourField = document.getElementById(`${prefix}_hour`);
  const minuteField = document.getElementById(`${prefix}_minute`);
  const ampmField = document.getElementById(`${prefix}_ampm`);
  const hiddenField = document.getElementById(prefix);

  if (!hourField || !minuteField || !ampmField || !hiddenField) return;

  if (!timeValue) {
    hourField.value = '';
    minuteField.value = '';
    ampmField.value = '';
    hiddenField.value = '';
    return;
  }

  const time = String(timeValue).trim().toLowerCase();
  const match = time.match(/^(\d{1,2}):(\d{2})(?::\d{2})?(?:\s*(am|pm))?$/);
  if (!match) return;

  let hour = parseInt(match[1], 10);
  const minute = match[2];
  let ampm = match[3];

  if (!ampm) {
    ampm = hour >= 12 ? 'pm' : 'am';
    hour = hour % 12;
    if (hour === 0) hour = 12;
  }

  hourField.value = String(hour);
  minuteField.value = minute;
  ampmField.value = ampm;
  hiddenField.value = `${hour}:${minute} ${ampm}`;
}

function updateHiddenTimeField(prefix) {
  const hour = document.getElementById(`${prefix}_hour`)?.value || '';
  const minute = document.getElementById(`${prefix}_minute`)?.value || '';
  const ampm = document.getElementById(`${prefix}_ampm`)?.value || '';
  const hiddenField = document.getElementById(prefix);

  if (!hiddenField) return;

  if (!hour || !minute || !ampm) {
    hiddenField.value = '';
    return;
  }

  hiddenField.value = `${hour}:${minute} ${ampm}`;
}

function bindTimeDropdowns(prefix) {
  ['hour', 'minute', 'ampm'].forEach((part) => {
    document.getElementById(`${prefix}_${part}`)?.addEventListener('change', () => {
      updateHiddenTimeField(prefix);
    });
  });
}

bindTimeDropdowns('schedule_start_time');
bindTimeDropdowns('schedule_end_time');

const setScheduleFormMode = (mode) => {
  const isEditMode = mode === 'edit';

  if (isEditMode) {
    setScheduleCourseFieldsEditable(true);
    setScheduleTimeDropdownsEditable(true);
    return;
  }

  const hasSelectedCourse = !!(scheduleCourseLookup && scheduleCourseLookup.value);

  setScheduleCourseFieldsEditable(hasSelectedCourse);
  setScheduleTimeDropdownsEditable(hasSelectedCourse);

  if (!hasSelectedCourse) {
    clearScheduleCourseFields();
    setTimeDropdowns('schedule_start_time', '');
    setTimeDropdowns('schedule_end_time', '');
  }
};

const searchUmbcCourses = async (query) => {
  const resultsBox = document.getElementById('course_search_results');
  const statusEl = document.getElementById('course-search-status');
  const listEl = document.getElementById('course-search-list');

  if (!resultsBox) return;

  resultsBox.hidden = false;
  statusEl.textContent = 'Searching…';
  listEl.innerHTML = '';

  try {
    const data = await requestJson(`/umbc_db/courses?search_str=${encodeURIComponent(query)}`, 'GET');
    const courses = data.umbc_courses || [];

    if (courses.length === 0) {
      statusEl.textContent = 'No courses found.';
      return;
    }

    statusEl.textContent = `${courses.length} result${courses.length !== 1 ? 's' : ''} found — click a result to select it.`;

    courses.forEach((course) => {
      const li = document.createElement('li');
      li.className = 'account-search-item';
      li.dataset.course = JSON.stringify(course);
      li.innerHTML = `
        <div class="account-search-item-info">
          <span class="account-search-item-name">${course.course_subject} ${course.course_code} — ${course.course_name}</span>
          <span class="account-search-item-meta">${course.subject_name}</span>
        </div>
        <button type="button" class="button button-secondary" style="flex-shrink:0;">Select</button>
      `;

      const selectFn = () => {
        listEl.querySelectorAll('.account-search-item').forEach(el => el.classList.remove('selected'));
        li.classList.add('selected');

        const courseLookupResults = document.getElementById('course_lookup_results');
        if (courseLookupResults) courseLookupResults.value = JSON.stringify(course);

        if (scheduleCourseLookup) {
            const existing = scheduleCourseLookup.querySelector('option[data-new-course]');
            if (existing) existing.remove();

            const opt = document.createElement('option');
            opt.value = 'new';
            opt.textContent = 'New Course Selected';
            opt.dataset.newCourse = 'true';
            opt.selected = true;
            scheduleCourseLookup.prepend(opt);
        }
        fillScheduleFormFromCourse(course);
        showMessage(`Selected course: ${course.course_subject} ${course.course_code} — ${course.course_name}`, 'success');
      };

      li.addEventListener('click', selectFn);
      li.querySelector('button').addEventListener('click', (e) => {
        e.stopPropagation();
        selectFn();
      });

      listEl.appendChild(li);
    });
  } catch (err) {
    statusEl.textContent = 'Search failed.';
    showMessage(err.message, 'error');
  }
};

  const searchUmbcAccounts = async (query) => {
    const resultsBox = document.getElementById('account_search_results');
    const statusEl = document.getElementById('account-search-status');
    const listEl = document.getElementById('account-search-list');

    if (!resultsBox) return;

    resultsBox.hidden = false;
    statusEl.textContent = 'Searching…';
    listEl.innerHTML = '';

    try {
      const data = await requestJson(`/umbc_db/accounts?search_str=${encodeURIComponent(query)}`, 'GET');
      const accounts = data.umbc_accounts || [];

      if (accounts.length === 0) {
        statusEl.textContent = 'No accounts found.';
        return;
      }

      statusEl.textContent = `${accounts.length} result${accounts.length !== 1 ? 's' : ''} found — click a result to select it.`;

      accounts.forEach((account) => {
        const li = document.createElement('li');
        li.className = 'account-search-item';
        li.dataset.account = JSON.stringify(account);
        li.innerHTML = `
          <div class="account-search-item-info">
            <span class="account-search-item-name">${account.first_name} ${account.last_name}</span>
            <span class="account-search-item-meta">${account.umbc_id} &bull; ${account.umbc_email}</span>
          </div>
          <button type="button" class="button button-secondary" style="flex-shrink:0;">Select</button>
        `;

        const selectFn = () => {
          // Deselect others
          listEl.querySelectorAll('.account-search-item').forEach(el => el.classList.remove('selected'));
          li.classList.add('selected');

          // Store value in hidden input
          if (accountLookupResults) {
            accountLookupResults.value = JSON.stringify(account);
          }

          fillAccountFormFromUmbc(account);
          setAccountFormMode('add');
          showMessage(`Selected account: ${account.first_name} ${account.last_name} (${account.umbc_id})`, 'success');
        };

        li.addEventListener('click', selectFn);
        li.querySelector('button').addEventListener('click', (e) => {
          e.stopPropagation();
          selectFn();
        });

        listEl.appendChild(li);
      });
    } catch (err) {
      statusEl.textContent = 'Search failed.';
      showMessage(err.message, 'error');
    }
  };

  (async () => {
    if (accountForm) {
      setAccountFormMode('add');
    }

    if (scheduleForm && scheduleCourseLookup) {
      setScheduleFormMode('add');
    }
  })();

  scheduleCourseLookup?.addEventListener('change', () => {
    if (!scheduleCourseLookup.value) {
      const courseIdField = document.getElementById('schedule_course_id');
      if (courseIdField) courseIdField.value = '';
      return;
    }

    const selectedOption = scheduleCourseLookup.options[scheduleCourseLookup.selectedIndex];
    if (!selectedOption.dataset.newCourse) {
      const newCourseOpt = scheduleCourseLookup.querySelector('option[data-new-course]');
      if (newCourseOpt) newCourseOpt.remove();

      const courseList = document.getElementById('course-search-list');
      if (courseList) {
        courseList.querySelectorAll('.account-search-item').forEach(el => el.classList.remove('selected'));
      }
      const courseLookupResults = document.getElementById('course_lookup_results');
      if (courseLookupResults) courseLookupResults.value = '';
    }

    try {
      const course = JSON.parse(scheduleCourseLookup.value);
      fillScheduleFormFromCourse(course);
    } catch (err) {
      // 'new' or other non-JSON values are valid states (e.g. search selection), not errors
    }
  });


  document.getElementById('reset-schedule-form')?.addEventListener('click', () => {
    scheduleForm.reset();
    document.getElementById('schedule_id').value = '';
    if (scheduleCourseLookup) scheduleCourseLookup.value = '';
    const newCourseOpt = scheduleCourseLookup?.querySelector('option[data-new-course]');
    if (newCourseOpt) newCourseOpt.remove();
    const courseQuery = document.getElementById('course_search_query');
    if (courseQuery) courseQuery.value = '';
    const courseResults = document.getElementById('course_search_results');
    if (courseResults) courseResults.hidden = true;
    const courseList = document.getElementById('course-search-list');
    if (courseList) courseList.innerHTML = '';
    const courseLookupResults = document.getElementById('course_lookup_results');
    if (courseLookupResults) courseLookupResults.value = '';
    clearScheduleCourseFields();
    setScheduleFormMode('add');
  });
  document.getElementById('reset-event-form')?.addEventListener('click', () => eventForm.reset());
  document.getElementById('reset-account-form')?.addEventListener('click', () => {
    accountForm.reset();
    document.getElementById('account_user_id').value = '';
    if (accountLookupResults) {
      accountLookupResults.value = '';
    }
    // Clear search UI
    const searchQuery = document.getElementById('account_search_query');
    if (searchQuery) searchQuery.value = '';
    const searchResults = document.getElementById('account_search_results');
    if (searchResults) searchResults.hidden = true;
    const searchList = document.getElementById('account-search-list');
    if (searchList) searchList.innerHTML = '';
    clearAccountTextFields();
    setAccountFormMode('add');
  });

document.getElementById('account-search-submit')?.addEventListener('click', async () => {
  const query = document.getElementById('account_search_query').value.trim();
  if (!query) {
    showMessage('Please enter a search term.', 'error');
    return;
  }
  // Clear previously selected account when a new search runs
  if (accountLookupResults) {
    accountLookupResults.value = '';
  }
  clearAccountTextFields();
  setAccountFormMode('add');
  await searchUmbcAccounts(query);
});

document.getElementById('account_search_query')?.addEventListener('keydown', (e) => {
  if (e.key === 'Enter') {
    e.preventDefault();
    document.getElementById('account-search-submit')?.click();
  }
});

document.getElementById('course-search-submit')?.addEventListener('click', async () => {
  const query = document.getElementById('course_search_query').value.trim();
  if (!query) {
    showMessage('Please enter a search term.', 'error');
    return;
  }
  await searchUmbcCourses(query);
});

document.getElementById('course_search_query')?.addEventListener('keydown', (e) => {
  if (e.key === 'Enter') {
    e.preventDefault();
    document.getElementById('course-search-submit')?.click();
  }
});

document.querySelectorAll('.admin-edit-schedule').forEach(btn => {
  btn.addEventListener('click', (e) => {
    const row = e.target.closest('tr');
    document.getElementById('schedule_id').value = row.dataset.scheduleId;
    document.getElementById('schedule_user_id').value = row.dataset.userId;
    document.getElementById('schedule_course_id').value = row.dataset.courseId;

    const dayMap = {
      MON: 'Monday',
      TUE: 'Tuesday',
      WED: 'Wednesday',
      THU: 'Thursday',
      FRI: 'Friday'
    };

    document.getElementById('schedule_day_of_week').value = dayMap[row.dataset.dayOfWeek] || '';
    setTimeDropdowns('schedule_start_time', row.dataset.startTime);
    setTimeDropdowns('schedule_end_time', row.dataset.endTime);

    if (scheduleCourseLookup) {
      scheduleCourseLookup.value = '';
    }

    setScheduleFormMode('edit');

    showMessage(`Loaded schedule ${row.dataset.scheduleId} into the form.`, 'success');
  });
});

  document.querySelectorAll('.admin-delete-schedule').forEach(btn => {
    btn.addEventListener('click', async (e) => {
      const row = e.target.closest('tr');
      const id = row.dataset.scheduleId;
      if (!confirm(`Delete schedule entry ${id}?`)) return;

      try {
        await requestJson(`/schedule/${id}`, 'DELETE');
        row.remove();
        showMessage(`Deleted schedule entry ${id}.`);
      } catch (err) {
        showMessage(err.message, 'error');
      }
    });
  });

  scheduleForm?.addEventListener('submit', async (e) => {
    e.preventDefault();

    updateHiddenTimeField('schedule_start_time');
    updateHiddenTimeField('schedule_end_time');

    const startTime = document.getElementById('schedule_start_time').value;
    const endTime = document.getElementById('schedule_end_time').value;

    if (!startTime || !endTime) {
      showMessage('Please select both a start time and end time.', 'error');
      return;
    }

    const id = document.getElementById('schedule_id').value.trim();
    const payload = {
      user_id: Number(document.getElementById('schedule_user_id').value),
      course_id: Number(document.getElementById('schedule_course_id').value),
      day_of_week: document.getElementById('schedule_day_of_week').value,
      start_time: startTime,
      end_time: endTime,
    };

    try {
      if (id) {
        await requestJson(`/schedule/${id}`, 'PATCH', payload);
        showMessage(`Updated schedule entry ${id}. Reload to refresh the table.`);
      } else {
        const data = await requestJson('/schedule', 'POST', payload);
        showMessage(`Created schedule entry ${data.schedule_id}. Reload to refresh the table.`);
      }

      scheduleForm.reset();
      setTimeDropdowns('schedule_start_time', '');
      setTimeDropdowns('schedule_end_time', '');
    } catch (err) {
      showMessage(err.message, 'error');
    }
  });

  document.querySelectorAll('.admin-edit-event').forEach(btn => {
    btn.addEventListener('click', (e) => {
      const row = e.target.closest('tr');
      document.getElementById('event_id').value = row.dataset.eventId;
      document.getElementById('event_user_id').value = row.dataset.userId;
      document.getElementById('event_type').value = row.dataset.eventType;
      document.getElementById('start_day').value = row.dataset.startDay;
      document.getElementById('final_day').value = row.dataset.finalDay;
      document.getElementById('duration').value = row.dataset.duration;
      toggleFields();
      showMessage(`Loaded event ${row.dataset.eventId} into the form.`, 'success');
    });
  });

  document.querySelectorAll('.admin-delete-event').forEach(btn => {
    btn.addEventListener('click', async (e) => {
      const row = e.target.closest('tr');
      const id = row.dataset.eventId;
      if (!confirm(`Delete event ${id}?`)) return;
      try {
        await requestJson(`/events/${id}`, 'DELETE');
        row.remove();
        showMessage(`Deleted event ${id}.`);
      } catch (err) {
        showMessage(err.message, 'error');
      }
    });
  });



  eventForm?.addEventListener('submit', async (e) => {
    e.preventDefault();

    const id = document.getElementById('event_id').value.trim();
    const payload = {
      user_id: Number(document.getElementById('event_user_id').value),
      event_type: Number(document.getElementById('event_type').value),
      start_day: document.getElementById('start_day').value,
      final_day: document.getElementById('final_day').value || null,
      duration: document.getElementById('duration').value ? Number(document.getElementById('duration').value) : null
    };

    try {
      if (id) {
        await requestJson(`/events/${id}`, 'PATCH', payload);
        showMessage(`Updated event ${id}. Reload to refresh the table.`);
      } else {
        const data = await requestJson('/events', 'POST', payload);
        showMessage(`Created event ${data.event_id}. Reload to refresh the table.`);
      }
      eventForm.reset();
      document.getElementById("event_type").selectedIndex = 0;
      toggleFields();
    } catch (err) {
      showMessage(err.message, 'error');
    }
    
  });

  document.querySelectorAll('.admin-edit-account').forEach(btn => {
    btn.addEventListener('click', (e) => {
      const row = e.target.closest('tr');

      document.getElementById('account_user_id').value = row.dataset.userId;
      document.getElementById('user_login').value = row.dataset.userLogin || '';
      document.getElementById('user_email').value = row.dataset.userEmail || '';
      document.getElementById('first_name').value = row.dataset.firstName || '';
      document.getElementById('last_name').value = row.dataset.lastName || '';

      const roles = (row.dataset.roles || '')
        .split(',')
        .map(r => r.trim().toLowerCase())
        .filter(Boolean);

      accountForm.querySelectorAll('input[name="roles[]"]').forEach(cb => {
        cb.checked = roles.includes(cb.value.toLowerCase());
      });

      if (accountLookupResults) {
        accountLookupResults.value = '';
      }

      const searchResults = document.getElementById('account_search_results');
      if (searchResults) searchResults.hidden = true;
      const searchQuery = document.getElementById('account_search_query');
      if (searchQuery) searchQuery.value = '';

      setAccountFormMode('edit');

      showMessage(`Loaded account ${row.dataset.userId}.`, 'success');
    });
  });

  document.querySelectorAll('.admin-delete-account').forEach(btn => {
    btn.addEventListener('click', async (e) => {
      const row = e.target.closest('tr');
      const id = row.dataset.userId;
      if (!confirm(`Delete user ${id}?`)) return;
      console.log(row);
      try {
        await requestJson(`/accounts/${id}`, 'DELETE');
        row.remove();
        showMessage(`Deleted account ${id}.`);
      } catch (err) {
        showMessage(err.message, 'error');
      }
    });
  });

  accountForm?.addEventListener('submit', async (e) => {
    e.preventDefault();

    const id = document.getElementById('account_user_id').value.trim();
    const user_login = document.getElementById('user_login').value.trim();
    const user_email = document.getElementById('user_email').value.trim();
    const first_name = document.getElementById('first_name').value.trim();
    const last_name = document.getElementById('last_name').value.trim();
    const roles = Array.from(
      accountForm.querySelectorAll('input[name="roles[]"]:checked')
    ).map(el => el.value);

    if (roles.length === 0) {
      showMessage('Select at least one role.', 'error');
      return;
    }

    try {
      if (id) {
        await requestJson(`/accounts/${id}`, 'PATCH', {
          user_login,
          user_email,
          first_name,
          last_name,
          roles
        });
        showMessage(`Updated account ${id}. Reload to refresh the table.`);
      } else {
        const payload = {
          user_login,
          user_email,
          first_name,
          last_name,
          roles
        };
        const data = await requestJson('/accounts', 'POST', payload);
        showMessage(`Created account ${data.user_id}. Reload to refresh the table.`);
      }

      accountForm.reset();
      document.getElementById('account_user_id').value = '';
      if (accountLookupResults) {
        accountLookupResults.value = '';
      }
      // Clear search UI after save
      const searchQueryEl = document.getElementById('account_search_query');
      if (searchQueryEl) searchQueryEl.value = '';
      const searchResultsEl = document.getElementById('account_search_results');
      if (searchResultsEl) searchResultsEl.hidden = true;
      setAccountFormMode('add');
    } catch (err) {
      showMessage(err.message, 'error');
    }
  });

  document.getElementById('lookup-accounts-form')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const query = document.getElementById('lookup-accounts-query').value.trim();
    const box = document.getElementById('lookup-accounts-results');

    try {
      const data = await requestJson(`/umbc_db/accounts?search_str=${encodeURIComponent(query)}`, 'GET');
      const rows = (data.umbc_accounts || []).map(account => `
        <tr>
          <td>${account.umbc_id}</td>
          <td>${account.first_name} ${account.last_name}</td>
          <td>${account.umbc_email}</td>
        </tr>
      `).join('');

      box.innerHTML = rows
        ? `<div class="umbc-table-wrapper"><table class="umbc-table"><thead><tr><th>UMBC ID</th><th>Name</th><th>Email</th></tr></thead><tbody>${rows}</tbody></table></div>`
        : `<p>No accounts found.</p>`;
    } catch (err) {
      showMessage(err.message, 'error');
    }
  });

  document.getElementById('lookup-courses-form')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const query = document.getElementById('lookup-courses-query').value.trim();
    const box = document.getElementById('lookup-courses-results');

    try {
      const data = await requestJson(`/umbc_db/courses?search_str=${encodeURIComponent(query)}`, 'GET');
      const rows = (data.umbc_courses || []).map(course => `
        <tr>
          <td>${course.course_id}</td>
          <td>${course.course_subject} ${course.course_code}</td>
          <td>${course.course_name}</td>
          <td>${course.subject_name}</td>
        </tr>
      `).join('');

      box.innerHTML = rows
        ? `<div class="umbc-table-wrapper"><table class="umbc-table"><thead><tr><th>Course ID</th><th>Course</th><th>Name</th><th>Subject</th></tr></thead><tbody>${rows}</tbody></table></div>`
        : `<p>No courses found.</p>`;
    } catch (err) {
      showMessage(err.message, 'error');
    }
  });
});

function toggleFields() {
  const eventType = document.getElementById("event_type");
  const dateRangeFields = document.getElementById("date-range-fields");
  const durationField = document.getElementById("duration-field");
  const today = new Date().toISOString().split('T')[0];
  const selectedText = eventType.options[eventType.selectedIndex].text.toLowerCase();

  dateRangeFields.style.display = "none";
  durationField.style.display = "none";

  dateRangeFields.querySelectorAll("input").forEach(i => {i.removeAttribute("required"); i.value = today;});
  durationField.querySelectorAll("input").forEach(i => i.removeAttribute("required"));

  if (selectedText.includes("absent")) {
    dateRangeFields.style.display = "block";
    dateRangeFields.querySelectorAll("input").forEach(i => {i.setAttribute("required", ""); i.value = "";});
  } 
  else if (selectedText.includes("leaving early")) {
    durationField.style.display = "block";
    durationField.querySelectorAll("input").forEach(i => i.setAttribute("required", ""));
  }
}

document.addEventListener("DOMContentLoaded", function () {
  const eventType = document.getElementById("event_type");

  eventType.addEventListener("change", toggleFields);

  // run once on load
  toggleFields();
});