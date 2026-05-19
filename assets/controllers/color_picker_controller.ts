import { Controller } from '@hotwired/stimulus';
import Pickr from '@simonwep/pickr';

/* stimulusFetch: 'lazy' */
export default class extends Controller<HTMLInputElement> {
    static values = {
        default: { type: String, default: '#206bc4' },
    };

    declare defaultValue: string;

    private pickr?: Pickr;

    private boundBeforeCache?: () => void;

    connect(): void {
        const input = this.element;
        const initial = input.value || this.defaultValue;

        // Hide the native color input but keep it in the DOM so Symfony form
        // binding still works through its value attribute.
        input.type = 'hidden';

        // Pickr replaces its host element with a `.pcr-button` and appends a
        // `.pcr-app` dropdown to <body>. Turbo's snapshot cache captures both
        // (cache happens before Stimulus disconnect), so on restore we end up
        // re-running connect() with the cached widget DOM still present. Wipe
        // any leftovers from a prior visit before creating a fresh picker.
        const parent = input.parentElement;
        if (parent) {
            parent.querySelectorAll('.pcr-button, .pickr').forEach((el) => el.remove());
        }
        document.querySelectorAll('.pcr-app').forEach((el) => el.remove());

        const host = document.createElement('div');
        host.className = 'd-inline-block';
        input.insertAdjacentElement('afterend', host);

        this.pickr = Pickr.create({
            el: host,
            theme: 'nano',
            default: initial,
            components: {
                preview: true,
                opacity: false,
                hue: true,
                interaction: {
                    hex: true,
                    input: true,
                    save: true,
                },
            },
        });

        this.pickr.on('save', (color: Pickr.HSVaColor | null) => {
            if (!color) {
                return;
            }
            const hex = color.toHEXA().toString(0).slice(0, 7);
            input.value = hex;
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
            this.pickr?.hide();
        });

        this.pickr.on('change', (color: Pickr.HSVaColor) => {
            const hex = color.toHEXA().toString(0).slice(0, 7);
            input.value = hex;
        });

        // Destroy Pickr before Turbo snapshots the page, so the cached HTML
        // doesn't contain the picker DOM (which would duplicate on restore).
        this.boundBeforeCache = () => {
            this.pickr?.destroyAndRemove();
            this.pickr = undefined;
        };
        document.addEventListener('turbo:before-cache', this.boundBeforeCache);
    }

    disconnect(): void {
        if (this.boundBeforeCache) {
            document.removeEventListener('turbo:before-cache', this.boundBeforeCache);
            this.boundBeforeCache = undefined;
        }
        this.pickr?.destroyAndRemove();
        this.pickr = undefined;
    }
}
