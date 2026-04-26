// =============================================================================
// UTILITY HELPERS
// =============================================================================

const $  = (id)  => document.getElementById(id);
const $$ = (sel) => document.querySelectorAll(sel);
const qs = (sel) => document.querySelector(sel);

const on = (el, event, handler) => el?.addEventListener(event, handler);

const onEnter = (el, handler) => {
  on(el, 'keydown', (e) => {
    if (e.key === 'Enter') { e.preventDefault(); handler(e); }
  });
};

const setVal      = (id, value) => { const el = $(id); if (el) el.value = value; };
const setHidden   = (id, hidden) => { const el = $(id); if (el) el.hidden = hidden; };
const clearVal    = (id) => setVal(id, '');
const clearFields = (ids) => ids.forEach(id => { const el = $(id); if (el) el.value = ''; });

const toggleDisplay = (el, show) => { if (el) el.style.display = show ? '' : 'none'; };
const pluralSuffix  = (count) => (count !== 1 ? 's' : '');

function throttle(callback, limit) {
  let waiting = false;
  return function (...args) {
    if (waiting) return;
    callback.apply(this, args);
    waiting = true;
    setTimeout(() => { waiting = false; }, limit);
  };
}


// =============================================================================
// DISPLAY FORMATTERS
// =============================================================================

function formatDisplayDate(isoDate) {
  if (!isoDate) return '';
  const [y, m, d] = isoDate.split('-');
  return `${m}-${d}-${y}`;
}

function formatDisplayTime(timeValue) {
  if (!timeValue) return '';
  const match = String(timeValue).trim().match(/^(\d{1,2}):(\d{2})(?::\d{2})?(?:\s*((?:a|p)\.m\.))?$/i);
  if (!match) return timeValue;
  let hour  = parseInt(match[1], 10);
  const min = match[2];
  let ampm  = match[3];
  if (!ampm) {
    ampm = hour >= 12 ? 'p.m.' : 'a.m.';
    hour = hour % 12 || 12;
  }
  return `${hour}:${min} ${ampm}`;
}

function formatDisplayRole(role) {
  return role.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
}


// =============================================================================
// SLIDE ANIMATIONS
// =============================================================================

const applyStyles  = (el, styles) => Object.assign(el.style, styles);
const removeStyles = (el, props)  => props.forEach(p => el.style.removeProperty(p));

const SLIDE_CLEANUP_PROPS = [
  'height', 'padding-top', 'padding-bottom',
  'margin-top', 'margin-bottom', 'overflow',
  'transition-duration', 'transition-property',
];

const DOMAnimations = {
  slideUp(element, duration = 500) {
    return new Promise(resolve => {
      applyStyles(element, {
        height:             `${element.offsetHeight}px`,
        transitionProperty: 'height, margin, padding',
        transitionDuration: `${duration}ms`,
      });
      element.offsetHeight; // force reflow
      applyStyles(element, { overflow: 'hidden', height: '0', paddingTop: '0', paddingBottom: '0', marginTop: '0', marginBottom: '0' });
      setTimeout(() => {
        element.style.display = 'none';
        removeStyles(element, SLIDE_CLEANUP_PROPS);
        resolve(false);
      }, duration);
    });
  },

  slideDown(element, duration = 500) {
    return new Promise(resolve => {
      element.style.removeProperty('display');
      let display = window.getComputedStyle(element).display;
      if (display === 'none') display = 'block';
      applyStyles(element, { display, overflow: 'hidden', height: '0', paddingTop: '0', paddingBottom: '0', marginTop: '0', marginBottom: '0' });
      const height = element.offsetHeight;
      applyStyles(element, { transitionProperty: 'height, margin, padding', transitionDuration: `${duration}ms`, height: `${height}px` });
      removeStyles(element, ['padding-top', 'padding-bottom', 'margin-top', 'margin-bottom']);
      setTimeout(() => {
        removeStyles(element, ['height', 'overflow', 'transition-duration', 'transition-property']);
        resolve(true);
      }, duration);
    });
  },

  slideToggle(element, duration = 500) {
    return window.getComputedStyle(element).display === 'none'
      ? this.slideDown(element, duration)
      : this.slideUp(element, duration);
  },
};


// =============================================================================
// NAVIGATION
// =============================================================================

const MENU_DURATION = 300;

function initNavigation() {
  const menuItemsWithChildren = $$('.top-level > .sub-menu li.menu-item-has-children:not(.sub-menu .sub-menu li.menu-item-has-children), li.top-level.menu-item-has-children');
  const topLevelMenuItems     = $$('.top-level');
  const menuToggle            = qs('.menu-toggle');
  const wholeMenu             = qs('#primary-menu');
  const menuToggleContent     = qs('.menu-toggle .menu-toggle-content');
  const navigationWrapper     = qs('.navigation-wrapper');

  if (!menuToggle || !navigationWrapper || !wholeMenu) return;

  let windowWidth;
  let touchmoved;

  function chevronButton(text) {
    return `
      <button>
        <span class="icon-chevron" aria-hidden="true">
          <svg viewBox="0 0 1024 661" xmlns="http://www.w3.org/2000/svg">
            <path d="m459.2 639.05c28.8 28.79 76.8 28.79 105.6 0l435.2-435.05c32-32 32-80 0-108.77l-70.4-73.64c-32-28.79-80-28.79-108.8 0l-310.4 310.33-307.2-310.33c-28.8-28.79-76.8-28.79-108.8 0l-70.4 73.59c-32 28.82-32 76.82 0 108.82z"/>
          </svg>
        </span>
        <span class="sr-only">Toggle submenu for ${text}</span>
      </button>`;
  }

  function closeAllSubMenus() {
    menuItemsWithChildren.forEach(item => {
      item.classList.remove('menu-hover', 'open');
      item.querySelectorAll('.sub-menu').forEach(sm => sm.classList.remove('open'));
    });
  }

  function setMenuToggleExpanded(expanded) {
    menuToggle.setAttribute('aria-expanded', expanded);
    menuToggleContent.innerHTML = expanded ? 'Close' : 'Menu';
  }

  menuToggle.addEventListener('click', (e) => {
    e.preventDefault();
    DOMAnimations.slideToggle(navigationWrapper, MENU_DURATION);

    const isExpanded = menuToggle.getAttribute('aria-expanded') === 'true';
    setMenuToggleExpanded(!isExpanded);
    navigationWrapper.classList.toggle('open');
    document.body.classList.toggle('mobile-menu-open');

    const mobileLink  = qs('.mobile-header-title a');
    const logoWrapper = qs('.umbc-logo-wrapper');

    if (isExpanded) {
      mobileLink?.setAttribute('tabindex', -1);
      logoWrapper?.setAttribute('tabindex', 0);
      closeAllSubMenus();
    } else {
      mobileLink?.setAttribute('tabindex', 0);
      logoWrapper?.setAttribute('tabindex', -1);
    }
  });

  menuItemsWithChildren.forEach(el => {
    const link = el.querySelector('a');
    link.insertAdjacentHTML('afterend', chevronButton(link.textContent));
  });

  function withMenuInstant(fn) {
    wholeMenu.classList.add('menu-instant');
    fn();
  }

  function clearMenuDisable() {
    $$('.menu-disable').forEach(el => el.classList.remove('menu-disable'));
  }

  function enableDesktopNavigation() {
    navigationWrapper.style.display = 'block';
    document.body.classList.remove('mobile-menu-open');
    navigationWrapper.classList.remove('open');
    menuToggleContent.innerHTML = 'Menu';
    closeAllSubMenus();

    const docWidth = window.innerWidth;

    menuItemsWithChildren.forEach(menu => {
      const rect         = menu.getBoundingClientRect();
      const hasSubMenus  = menu.querySelectorAll('.sub-menu').length > 1;
      const subMenuWidth = menu.querySelector('.sub-menu').getBoundingClientRect().width + 16;
      const totalWidth   = hasSubMenus ? subMenuWidth * 2 : subMenuWidth;
      menu.classList.toggle('too-wide', rect.x + totalWidth > docWidth);
    });

    topLevelMenuItems.forEach(tlmi => {
      tlmi.addEventListener('mouseover', () => {
        topLevelMenuItems.forEach(item => {
          if (item === tlmi) return;
          item.classList.add('menu-disable');
          item.querySelectorAll('.menu-item').forEach(mi => mi.classList.remove('menu-hover'));
        });
      });
    });

    $$('.top-level > a').forEach(link => {
      link.addEventListener('focus', (e) => {
        withMenuInstant(() => {
          topLevelMenuItems.forEach(tlmi => {
            tlmi.classList.add('menu-disable');
            tlmi.classList.remove('menu-hover');
            tlmi.querySelectorAll('li').forEach(li => li.classList.remove('menu-hover'));
            tlmi.querySelectorAll('.sub-menu').forEach(sm => sm.classList.remove('open'));
          });
          e.target.closest('.top-level').classList.remove('menu-disable');
        });
      }, true);
    });

    $$('.top-level > button').forEach(button => {
      button.addEventListener('click', (e) => {
        withMenuInstant(() => {
          e.preventDefault();
          const topLevel = e.target.closest('.top-level');
          topLevel.classList.toggle('menu-hover');
          topLevel.querySelectorAll('.menu-item').forEach(item => item.classList.remove('menu-hover'));
        });
      });
    });

    $$('.sub-menu button').forEach(button => {
      button.addEventListener('click', (e) => {
        withMenuInstant(() => {
          e.preventDefault();
          const menuItem = e.target.closest('.menu-item');
          if (menuItem.classList.contains('menu-hover')) {
            menuItem.classList.remove('menu-hover');
            return;
          }
          Promise.resolve().then(() => {
            e.target.closest('.top-level').querySelectorAll('.menu-hover').forEach(el => el.classList.remove('menu-hover'));
            menuItem.classList.add('menu-hover');
          });
        });
      });
    });

    menuItemsWithChildren.forEach(item => {
      item.addEventListener('mouseover', () => {
        wholeMenu.classList.remove('menu-instant');
        item.classList.add('menu-hover');
        item.classList.remove('menu-disable', 'menu-item-instant');
      });

      item.addEventListener('mouseleave', (e) => {
        item.classList.remove('menu-hover', 'open');
        if (e.relatedTarget?.closest('li')?.classList.contains('menu-item')) {
          item.classList.add('menu-item-instant');
        }
        clearMenuDisable();
        item.querySelectorAll('.sub-menu').forEach(sm => sm.classList.remove('open'));
      });

      item.querySelector('button').addEventListener('focus', (e) => {
        e.target.closest('.top-level').classList.remove('menu-disable');
      });
    });
  }

  function enableMobileNavigation() {
    navigationWrapper.style.removeProperty('display');

    if ('ontouchstart' in window) {
      menuItemsWithChildren.forEach(item => {
        item.addEventListener('touchstart', () => { touchmoved = false; });
        item.addEventListener('touchmove',  () => { touchmoved = true; });
        item.addEventListener('touchend', (e) => {
          if (e.target.getAttribute('data-clickable') !== 'false' || touchmoved) return;
          withMenuInstant(() => {
            e.currentTarget.parentNode.classList.add('menu-hover');
            item.classList.add('menu-hover');
          });
          e.preventDefault();
          e.stopPropagation();
          e.target.setAttribute('data-clickable', 'true');
        });
      });
      $$('.menu-item-has-children > a').forEach(link => link.setAttribute('data-clickable', 'false'));
      return;
    }

    menuItemsWithChildren.forEach(item => {
      item.querySelector('button').addEventListener('click', (e) => {
        withMenuInstant(() => {
          e.preventDefault();
          const parent  = e.currentTarget.parentNode;
          const subMenu = parent.querySelector('.sub-menu');
          subMenu.classList.toggle('open');
          parent.classList.toggle('menu-hover');
          subMenu.querySelectorAll('.sub-menu').forEach(sm => {
            sm.classList.remove('open');
            sm.parentNode.classList.remove('menu-hover');
          });
        });
      });

      item.addEventListener('mouseover', () => {
        wholeMenu.classList.remove('menu-instant');
        item.classList.add('menu-hover');
        item.classList.remove('menu-disable');
      });
    });
  }

  const handleResize = throttle(() => {
    if (window.innerWidth === windowWidth) return;
    windowWidth = window.innerWidth;
    windowWidth > 768 ? enableDesktopNavigation() : enableMobileNavigation();
  }, 50);

  window.addEventListener('resize', handleResize);
  handleResize();
}


// =============================================================================
// BOOT
// =============================================================================

document.addEventListener('DOMContentLoaded', () => {
  initNavigation();
});