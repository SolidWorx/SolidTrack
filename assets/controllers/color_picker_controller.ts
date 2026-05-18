import { Controller } from '@hotwired/stimulus';
import Pickr from '@simonwep/pickr';

/* stimulusFetch: 'lazy' */
export default class extends Controller<HTMLInputElement> {
    static values = {
        default: { type: String, default: '#206bc4' },
    };

    declare defaultValue: string;

    private pickr?: Pickr;

    connect(): void {
        const input = this.element;
        const initial = input.value || this.defaultValue;

        // Hide the native color input but keep it in the DOM so Symfony form
        // binding still works through its value attribute.
        input.type = 'hidden';

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
    }

    disconnect(): void {
        this.pickr?.destroyAndRemove();
        this.pickr = undefined;
    }
}
