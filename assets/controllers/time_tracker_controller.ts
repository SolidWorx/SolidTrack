import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['timer'];

    static values = {
        time: Number,
    };

    declare timerTarget: HTMLElement;
    declare timeValue: number;

    private timerInterval: ReturnType<typeof setInterval> | null = null;

    private readonly MILLIS_PER_SECOND = 1000;

    connect() {
        if (this.isValidStart()) {
            this.start();
        }
    }

    disconnect() {
        this.stop();
    }

    // Stimulus calls this whenever data-time-tracker-time-value changes, including
    // when Live Components morphs the DOM after starting/stopping a tracker.
    timeValueChanged() {
        this.stop();
        if (this.isValidStart()) {
            this.start();
        } else {
            this.render(0, 0, 0);
        }
    }

    private isValidStart(): boolean {
        return (
            Number.isFinite(this.timeValue) &&
            this.timeValue > 0 &&
            this.timeValue <= Date.now()
        );
    }

    private start() {
        // Defensive: never let two intervals run at once.
        this.stop();
        this.update();
        this.timerInterval = setInterval(() => this.update(), this.MILLIS_PER_SECOND);
    }

    private stop() {
        if (this.timerInterval !== null) {
            clearInterval(this.timerInterval);
            this.timerInterval = null;
        }
    }

    private update() {
        if (!this.isValidStart()) {
            this.stop();
            return;
        }

        const elapsed = Date.now() - this.timeValue;
        const seconds = Math.floor(elapsed / this.MILLIS_PER_SECOND) % 60;
        const minutes = Math.floor(elapsed / this.MILLIS_PER_SECOND / 60) % 60;
        const hours = Math.floor(elapsed / this.MILLIS_PER_SECOND / 60 / 60);
        this.render(hours, minutes, seconds);
    }

    private render(hours: number, minutes: number, seconds: number) {
        this.timerTarget.textContent = `${this.pad(hours)}:${this.pad(minutes)}:${this.pad(seconds)}`;
    }

    private pad(n: number): string {
        return n < 10 ? `0${n}` : `${n}`;
    }
}
