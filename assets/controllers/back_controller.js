import { Controller } from '@hotwired/stimulus';

/*
 * Navigates one step back in history (the page the user came from), falling
 * back to a provided URL when there is no history to return to (e.g. the page
 * was opened directly or refreshed).
 *
 * Usage:
 *   <a href="{{ path('fallback') }}"
 *      data-controller="back"
 *      data-back-fallback-value="{{ path('fallback') }}"
 *      data-action="back#back">Cancel</a>
 */
export default class extends Controller {
    static values = { fallback: String };

    back(event) {
        event.preventDefault();

        if (window.history.length > 1) {
            window.history.back();
            return;
        }

        if (this.hasFallbackValue) {
            window.location.href = this.fallbackValue;
        }
    }
}
