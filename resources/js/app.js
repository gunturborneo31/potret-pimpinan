import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { Ziggy } from './ziggy';

import 'trix';
import 'trix/dist/trix.css';
import 'dropzone/dist/dropzone.css';

import Toast from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';
import $ from 'jquery'
import 'select2'
import 'select2/dist/css/select2.min.css'
import './plugins/select2';
window.$ = $
window.jQuery = $


const appName = import.meta.env.VITE_APP_NAME || 'Layanan Komunikasi dan Dokumentasi  Pimpinan';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const vueApp = createApp({ render: () => h(App, props) });
        vueApp.use(plugin);
        vueApp.use(ZiggyVue, Ziggy);
        vueApp.use(Toast, {
            autoClose: 3000,
            position: 'top-right',
            theme: 'light',
        });
        vueApp.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
