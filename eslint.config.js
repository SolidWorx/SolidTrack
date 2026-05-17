// Minimal flat config: the platform's webpack pipeline includes ESLintPlugin
// which requires *some* config file to be present. We don't enforce any rules
// at the SolidTrack level (Encore's TS loader and the IDE already cover us).
export default [
    {
        ignores: ['node_modules/**', 'vendor/**', 'public/**'],
    },
];
