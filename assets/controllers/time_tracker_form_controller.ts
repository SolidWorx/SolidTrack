import { Controller } from '@hotwired/stimulus';

interface TomSelectLike {
    clear: (silent?: boolean) => void;
}

interface SelectWithTomSelect extends HTMLSelectElement {
    tomselect?: TomSelectLike;
}

// Clears the TomSelect-enhanced project field when the server emits
// `time-tracker:cleared` (after Stop). The autocomplete column is wrapped in
// `data-live-ignore` so Live's morph cannot replace its contents; this listener
// bridges that gap by talking to TomSelect's own API.

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    clear() {
        const select = this.element.querySelector<SelectWithTomSelect>('select');
        if (!select) {
            return;
        }

        if (select.tomselect) {
            select.tomselect.clear();
            return;
        }

        select.value = '';
        select.dispatchEvent(new Event('change', { bubbles: true }));
    }
}
