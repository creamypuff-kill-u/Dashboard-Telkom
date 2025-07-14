<script>
import { Line, Bar } from 'vue-chartjs';
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  LineElement,
  PointElement,
  CategoryScale,
  LinearScale,
  BarElement
} from 'chart.js';

ChartJS.register(
  Title,
  Tooltip,
  Legend,
  LineElement,
  PointElement,
  CategoryScale,
  LinearScale,
  BarElement
);

export default {
  name: 'DailyPerformanceChart',
  components: {
    LineChart: Line,
    BarChart: Bar
  },
  props: {
    chartData: {
      type: Object,
      required: true,
      // Validasi sederhana untuk memastikan struktur dasar
      validator: (value) => {
        return value && Array.isArray(value.labels) && Array.isArray(value.datasets);
      }
    },
    chartOptions: {
      type: Object,
      default: () => ({})
    }
  },
  computed: {
    // Ini memastikan bahwa setiap dataset memiliki 'type' yang benar untuk Chart.js
    // Walaupun data sudah diolah di parent, ini adalah safety measure
    preparedChartData() {
        return {
            labels: this.chartData.labels,
            datasets: this.chartData.datasets.map(dataset => ({
                ...dataset,
                // Pastikan type ditentukan, default ke 'bar' jika tidak ada
                type: dataset.type || 'bar'
            }))
        };
    }
  },
  render() {
    // Karena kita punya tipe campuran, kita akan menggunakan BarChart
    // dan biarkan Chart.js menangani tipe 'line' di dataset
    return (
      <BarChart
        :data="preparedChartData"
        :options="chartOptions"
        chart-id="daily-ps-re-chart"
        dataset-id-key="label"
      />
    );
  }
};
</script>

<style scoped>
/* Optional: Tambahkan styling khusus untuk chart jika diperlukan */
div {
    width: 100%; /* Atau lebar yang Anda inginkan */
    height: 400px; /* Tinggi yang Anda inginkan */
}
</style>