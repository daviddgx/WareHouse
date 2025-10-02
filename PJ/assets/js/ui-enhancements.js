(() => {
  const init = () => {
    const body = document.body;
    if (!body) {
      return;
    }

    const loader = document.getElementById('pageLoader');
    body.classList.add('is-loading');

    let revealIndex = 0;

    const shouldIgnore = (el) => {
      if (!(el instanceof HTMLElement)) {
        return true;
      }
      if (el.dataset.revealProcessed === 'true') {
        return true;
      }
      if (el.closest('#pageLoader')) {
        return true;
      }
      if (el.matches('script, style')) {
        return true;
      }
      if (el.hasAttribute('data-aos')) {
        return true;
      }
      return false;
    };

    const applyReveal = (el) => {
      if (shouldIgnore(el)) {
        return;
      }
      const delay = Math.min(revealIndex * 18, 720);
      el.classList.add('reveal-element');
      el.style.setProperty('--reveal-delay', `${delay}ms`);
      el.dataset.revealProcessed = 'true';
      revealIndex += 1;
    };

    const seed = () => {
      revealIndex = 0;
      body.querySelectorAll('*').forEach((node) => {
        if (node instanceof HTMLElement) {
          applyReveal(node);
        }
      });
    };

    seed();

    const observer = new MutationObserver((mutations) => {
      mutations.forEach((mutation) => {
        mutation.addedNodes.forEach((node) => {
          if (!(node instanceof HTMLElement)) {
            return;
          }
          applyReveal(node);
          node.querySelectorAll('*').forEach((child) => {
            if (child instanceof HTMLElement) {
              applyReveal(child);
            }
          });
        });
      });
    });

    observer.observe(body, { childList: true, subtree: true });

    window.addEventListener('load', () => {
      body.classList.remove('is-loading');
      body.classList.add('page-loaded');
      if (loader) {
        loader.classList.add('page-loader--hide');
        setTimeout(() => loader.remove(), 650);
      }
    }, { once: true });
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init, { once: true });
  } else {
    init();
  }
})();
