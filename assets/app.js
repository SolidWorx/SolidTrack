import { startStimulusApp } from '@symfony/stimulus-bridge';
import './styles/app.scss';

// Platform UI ships its own Stimulus app via the `_platform_ui` Encore entry; this
// second app registers SolidTrack-local controllers (assets/controllers/*) on top.
export const app = startStimulusApp(require.context(
    '@symfony/stimulus-bridge/lazy-controller-loader!./controllers',
    true,
    /\.[jt]sx?$/,
));
