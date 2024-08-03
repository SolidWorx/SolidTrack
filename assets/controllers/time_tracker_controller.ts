import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = [ 'timer' ]

    static values = {
        time: Number
    }

    declare timerTarget: HTMLElement
    declare timeValue: number

    private timerInterval: any

    private readonly MILLIS_PER_SECOND = 1000;

    connect() {
        this.start()
    }

    disconnect() {
        clearInterval(this.timerInterval);
    }

    start() {
        this.update();
        this.timerInterval = setInterval(this.update.bind(this), this.MILLIS_PER_SECOND);
    }

    update() {
        const currentTime = new Date().getTime(); // get current time in milliseconds
        const elapsedTime = currentTime - this.timeValue; // calculate elapsed time in milliseconds
        const seconds = Math.floor(elapsedTime / this.MILLIS_PER_SECOND) % 60; // calculate seconds
        const minutes = Math.floor(elapsedTime / this.MILLIS_PER_SECOND / 60) % 60; // calculate minutes
        const hours = Math.floor(elapsedTime / this.MILLIS_PER_SECOND / 60 / 60); // calculate hours

        this.timerTarget.innerHTML = this.pad(hours) + ":" + this.pad(minutes) + ":" + this.pad(seconds); // update the display
    }

    pad(number: number) {
        return (number < 10 ? "0" : "") + number;
    }
}
