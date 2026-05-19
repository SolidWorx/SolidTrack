// Placeholder: tag color rendering is handled server-side via `TimeTrackerType`
// (`options_as_html` + an inline `.st-tag-dot`) and the new `tag-cell`
// controller. This file is kept so previously-cached browsers that still
// reference `data-controller="tag-select"` don't error.
import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {}
