import './bootstrap';
import { createApp } from 'vue';
import Editor from './components/Editor.vue';

const app = createApp({});

// Global error handler
app.config.errorHandler = (err) => {
    alert("VUE FATAL ERROR: " + err.message);
    console.error(err);
};

app.component('editor-component', Editor);
app.mount('#app');
