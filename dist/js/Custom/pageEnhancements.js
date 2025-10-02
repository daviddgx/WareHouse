(function () {
    'use strict';

    function onDocumentReady(callback) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback, { once: true });
        } else {
            callback();
        }
    }

    function initialiseSkeletons() {
        const skeletonTargets = document.querySelectorAll('.skeleton-target');
        skeletonTargets.forEach((element) => {
            element.setAttribute('aria-busy', 'true');
        });
    }

    function handleActionButtons() {
        const actionButtons = Array.from(document.querySelectorAll('.action-button'));

        if (!actionButtons.length) {
            return;
        }

        actionButtons.forEach((button) => {
            button.addEventListener('click', function () {
                if (this.classList.contains('is-processing')) {
                    return;
                }

                actionButtons.forEach((otherButton) => {
                    if (otherButton === this) {
                        return;
                    }

                    if (otherButton.tagName === 'BUTTON') {
                        otherButton.disabled = true;
                    }
                    otherButton.classList.add('disabled-button');
                    otherButton.setAttribute('aria-disabled', 'true');
                });

                if (this.tagName === 'BUTTON') {
                    this.disabled = true;
                }

                this.classList.add('is-processing');
            }, { once: true });
        });
    }

    function initialiseAOS() {
        if (window.AOS && typeof window.AOS.init === 'function') {
            window.AOS.init({
                duration: 600,
                easing: 'ease-out-quart',
                once: true,
                offset: 60
            });
        }
    }

    function markSkeletonsReady() {
        const skeletonTargets = document.querySelectorAll('.skeleton-target');
        skeletonTargets.forEach((element) => {
            element.removeAttribute('aria-busy');
        });
        document.body.classList.add('page-ready');
        initialiseAOS();
    }

    onDocumentReady(function () {
        initialiseSkeletons();
        handleActionButtons();
    });

    window.addEventListener('load', markSkeletonsReady, { once: true });
})();
