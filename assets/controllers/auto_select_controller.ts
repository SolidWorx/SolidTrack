import { Controller } from '@hotwired/stimulus';

const AUTOCOMPLETE_CONTROLLER = 'symfony--ux-autocomplete--autocomplete';
const OPT_OUT_ATTR = 'data-tom-select';

const shouldEnhance = (select: HTMLSelectElement): boolean => {
    if (select.getAttribute(OPT_OUT_ATTR) === 'off') {
        return false;
    }
    if (select.multiple) {
        return false;
    }
    const existing = (select.getAttribute('data-controller') ?? '').split(/\s+/);
    return !existing.includes(AUTOCOMPLETE_CONTROLLER);
};

const enhance = (select: HTMLSelectElement): void => {
    if (!shouldEnhance(select)) {
        return;
    }
    const existing = select.getAttribute('data-controller');
    const next = existing && existing.trim() !== '' ? `${existing} ${AUTOCOMPLETE_CONTROLLER}` : AUTOCOMPLETE_CONTROLLER;
    select.setAttribute('data-controller', next);
};

const enhanceAll = (root: ParentNode): void => {
    root.querySelectorAll<HTMLSelectElement>('select').forEach(enhance);
};

export default class extends Controller {
    private observer?: MutationObserver;

    connect(): void {
        enhanceAll(document.body);

        this.observer = new MutationObserver((mutations) => {
            for (const mutation of mutations) {
                mutation.addedNodes.forEach((node) => {
                    if (!(node instanceof Element)) {
                        return;
                    }
                    if (node instanceof HTMLSelectElement) {
                        enhance(node);
                    } else {
                        enhanceAll(node);
                    }
                });
            }
        });

        this.observer.observe(document.body, { childList: true, subtree: true });
    }

    disconnect(): void {
        this.observer?.disconnect();
        this.observer = undefined;
    }
}
