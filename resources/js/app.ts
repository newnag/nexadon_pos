import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { createPinia } from 'pinia';
// import router from './router'; // TODO: Integrate Vue Router with Inertia if needed

const appName = import.meta.env.VITE_APP_NAME || 'Nexadon POS';

// Create Pinia instance
const pinia = createPinia();

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });
        
        app.use(plugin);
        app.use(pinia);
        // Note: Vue Router is available for standalone pages but Inertia handles routing for Inertia pages
        // If you need standalone Vue Router pages, create a separate entry point
        
        app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
