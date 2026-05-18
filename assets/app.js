import { getApp } from '@solidworx/platform/core';
import './styles/app.scss';

const app = getApp();
const context = require.context('./controllers', true, /_controller\.[jt]sx?$/);

context.keys().forEach((key) => {
    const module = context(key);
    if (typeof module.default !== 'function') {
        return;
    }

    const identifier = key
        .replace(/^\.\//, '')
        .replace(/_controller\.[jt]sx?$/, '')
        .replace(/_/g, '-')
        .replace(/\//g, '--');

    app.register(identifier, module.default);
});
