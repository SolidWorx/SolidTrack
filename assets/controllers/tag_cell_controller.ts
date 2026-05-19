import { Controller } from '@hotwired/stimulus';

interface TomSelectInstance {
    focus(): void;
    open(): void;
    close(): void;
    refreshOptions(triggerDropdown: boolean): void;
    addItem(value: string, silent?: boolean): void;
    removeItem(value: string, silent?: boolean): void;
    items: string[];
    settings: { hideSelected: boolean | null };
    dropdown_content: HTMLElement;
    isOpen?: boolean;
}

interface SelectWithTomSelect extends HTMLSelectElement {
    tomselect?: TomSelectInstance;
}

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    private toggleHandler?: (event: MouseEvent) => void;

    connect(): void {
        this.bindTomSelect();
        // If TomSelect isn't ready yet (autocomplete controller may connect
        // after us), retry once on the next tick.
        if (!this.findSelect()?.tomselect) {
            window.setTimeout(() => this.bindTomSelect(), 0);
        }
    }

    disconnect(): void {
        const ts = this.findSelect()?.tomselect;
        if (ts && this.toggleHandler) {
            ts.dropdown_content.removeEventListener('mousedown', this.toggleHandler, true);
        }
        this.toggleHandler = undefined;
    }

    open(event: Event): void {
        const target = event.target as HTMLElement | null;
        // Don't re-trigger when the user clicked inside TomSelect itself.
        if (target?.closest('.ts-wrapper')) {
            return;
        }

        const ts = this.findSelect()?.tomselect;
        if (!ts) {
            return;
        }

        ts.focus();
        ts.open();
    }

    private findSelect(): SelectWithTomSelect | null {
        return this.element.querySelector<SelectWithTomSelect>('select');
    }

    private bindTomSelect(): void {
        const ts = this.findSelect()?.tomselect;
        if (!ts || this.toggleHandler) {
            return;
        }

        // Show selected options inside the dropdown so they can be toggled off.
        ts.settings.hideSelected = false;
        ts.refreshOptions(false);

        // Intercept option clicks so clicking an already-selected option
        // removes it (mousedown captured before TomSelect's own handler).
        this.toggleHandler = (event: MouseEvent) => {
            const optionEl = (event.target as HTMLElement | null)?.closest<HTMLElement>('[data-value]');
            if (!optionEl) {
                return;
            }
            const value = optionEl.getAttribute('data-value');
            if (value === null || !ts.items.includes(value)) {
                return;
            }
            event.preventDefault();
            event.stopPropagation();
            ts.removeItem(value);
            ts.refreshOptions(false);
        };
        ts.dropdown_content.addEventListener('mousedown', this.toggleHandler, true);
    }
}
