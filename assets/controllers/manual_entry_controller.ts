import { Controller } from '@hotwired/stimulus';

interface TomSelectOption {
    [key: string]: unknown;
}

interface TomSelectLike {
    options: Record<string, TomSelectOption>;
    addOption: (option: TomSelectOption) => void;
    removeOption: (value: string, silent?: boolean) => void;
    refreshOptions: (triggerDropdown?: boolean) => void;
    clear: (silent?: boolean) => void;
    getValue: () => string | string[];
}

interface SelectWithTomSelect extends HTMLSelectElement {
    tomselect?: TomSelectLike;
}

// Narrows the manual-entry modal's project list to a single client, for the
// trigger on a client row (which has no one project to pre-select).
//
// The generic "seed a field from the trigger" behaviour lives in the platform's
// `modal-prefill` controller; only the project => client relationship is
// SolidTrack's, so it stays here. The two are mutually exclusive in practice —
// a trigger names either a project or a client, never both.
//
// The narrowing has to go through TomSelect rather than the server because the
// project field sits behind `data-live-ignore`, so a re-render cannot replace
// its options. The project => client map is published by the component as
// `data-project-clients`.

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    private removed: TomSelectOption[] = [];

    private onShow = (event: Event) => this.narrow(event as Event & { relatedTarget?: HTMLElement });

    private onHidden = () => this.restore();

    connect() {
        this.element.addEventListener('show.bs.modal', this.onShow);
        this.element.addEventListener('hidden.bs.modal', this.onHidden);
    }

    disconnect() {
        this.element.removeEventListener('show.bs.modal', this.onShow);
        this.element.removeEventListener('hidden.bs.modal', this.onHidden);
    }

    private narrow(event: Event & { relatedTarget?: HTMLElement }) {
        this.restore();

        const clientId = event.relatedTarget?.dataset?.prefillClient;
        if (!clientId) {
            return;
        }

        const select = this.projectField();
        const tomSelect = select?.tomselect;
        if (!tomSelect) {
            return;
        }

        const projectClients = this.projectClients();

        Object.keys(tomSelect.options).forEach((value) => {
            if (projectClients[value] !== clientId) {
                this.removed.push(tomSelect.options[value]);
                tomSelect.removeOption(value, true);
            }
        });

        // A project carried over from a previous open may no longer be on offer.
        if (this.removed.length > 0) {
            tomSelect.clear(true);
            tomSelect.refreshOptions(false);
        }
    }

    private restore() {
        if (this.removed.length === 0) {
            return;
        }

        const tomSelect = this.projectField()?.tomselect;
        if (tomSelect) {
            this.removed.forEach((option) => tomSelect.addOption(option));
            tomSelect.refreshOptions(false);
        }

        this.removed = [];
    }

    private projectField(): SelectWithTomSelect | null {
        return this.element.querySelector<SelectWithTomSelect>('select[name$="[project]"]');
    }

    private projectClients(): Record<string, string> {
        const holder = this.element.querySelector<HTMLElement>('[data-project-clients]');

        try {
            return JSON.parse(holder?.dataset.projectClients ?? '{}');
        } catch {
            return {};
        }
    }
}
