import domReady from '@wordpress/dom-ready';
import { createRoot, render } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import App from './App';
import { ConfirmProvider } from './ConfirmDialog';
import './style.scss';

domReady(() => {
    const rootEl = document.getElementById('ppcartcomp-migration-wizard-root');
    if (!rootEl) {
        return;
    }

    const config = window.ppcartcompMigrationWizard;
    if (!config) {
        rootEl.textContent = __(
            'Migration wizard could not load its configuration.',
            'publishpress-cart-compat'
        );
        return;
    }

    const tree = (
        <ConfirmProvider>
            <App config={config} />
        </ConfirmProvider>
    );

    if (typeof createRoot === 'function') {
        createRoot(rootEl).render(tree);
        return;
    }

    render(tree, rootEl);
});
