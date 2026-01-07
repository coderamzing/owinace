import './bootstrap';

import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';
import ChartDataLabels from 'chartjs-plugin-datalabels';

// Register the datalabels plugin globally
Chart.register(ChartDataLabels);

window.Alpine = Alpine;
window.Chart = Chart;

Alpine.start();
