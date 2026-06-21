import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap';
import '../css/app.css';

createInertiaApp({
    resolve: name => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
        return pages[`./Pages/${name}.vue`];
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
});

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js');
    });
}
