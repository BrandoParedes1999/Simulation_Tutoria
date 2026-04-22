import './bootstrap';

// Plugin de Alpine: x-collapse (Livewire 3 ya carga Alpine base)
import collapse from '@alpinejs/collapse';

document.addEventListener('alpine:init', () => {
    window.Alpine.plugin(collapse);
});

// Chart.js auto-hospedado (evita el bloqueo de Tracking Prevention de Edge)
import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);
window.Chart = Chart;