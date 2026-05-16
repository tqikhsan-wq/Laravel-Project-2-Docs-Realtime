import './bootstrap';
import { createApp } from 'vue';
import Editor from './components/Editor.vue';

const app = createApp({});
app.component('editor-component', Editor);
app.mount('#app');
