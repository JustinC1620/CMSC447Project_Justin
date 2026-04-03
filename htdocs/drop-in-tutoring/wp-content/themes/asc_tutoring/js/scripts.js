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