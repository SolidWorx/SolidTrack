import Encore from '@solidworx/platform/webpack.config.js';

export default Encore
    .enableStimulusBridge('./assets/controllers.json')
    .addEntry('app', './assets/app.js')

    // The platform's `_platform_ui` entry points at `core.ts` inside its own
    // package, and bun installs `file:` dependencies by copying rather than
    // symlinking — so that file really does sit under `node_modules/`, which
    // Encore's TypeScript rule excludes wholesale.
    .configureLoaderRule('typescript', (rule) => {
        rule.exclude = /node_modules\/(?!@solidworx\/platform\/)/;
    })

    .getWebpackConfig();
