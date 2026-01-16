<!DOCTYPE html>
<html lang="en">
  <head>
    <?php require('include/head.php');
    $title="maindash"; ?>
  </head>
  <body>
    <!-- Sidebar -->
    <?php require('pages/sidebar.php') ?>

    <!-- Main Content -->
    <div id="main-content">
      <div class="sidebar-overlay"></div>

      <!-- Fixed Header -->
      <?php require('include/navbar.php') ?>

      <!-- Main Dashboard Content -->
      <main>
        <div class="row g-4">
          <!-- Dashboard Cards -->
          <?php require('include/dashbord-cards.php') ?>

          <!-- Statistics Chart -->
          <?php require('include/statistics.php') ?>


          <!-- Recent Orders -->
         <?php require('include/order.php') ?>

         <!-- footer -->
         <?php require('include/footer.php') ?>

        </div>
      </main>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- ApexCharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <!-- Custom JavaScript -->
  <script src="css&js/script.js"></script>
  <script>
    // Initialize tooltips
var tooltipTriggerList = [].slice.call(
  document.querySelectorAll('[data-bs-toggle="tooltip"]')
);
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
  return new bootstrap.Tooltip(tooltipTriggerEl);
});

// --- ApexCharts Initialization ---
// Monthly Sales Bar Chart
const salesChartOptions = {
  series: [
    {
      name: "Sales",
      data: [160, 380, 200, 290, 180, 190, 240, 110, 210, 385, 270, 110],
    },
  ],
  chart: {
    type: "bar",
    height: "100%",
    toolbar: {
      show: false,
    },
  },
  colors: ["#2E8B57"],
  plotOptions: {
    bar: {
      borderRadius: 4,
      columnWidth: "50%",
    },
  },
  dataLabels: {
    enabled: false,
  },
  legend: {
    show: false,
  },
  xaxis: {
    categories: [
      "Jan",
      "Feb",
      "Mar",
      "Apr",
      "May",
      "Jun",
      "Jul",
      "Aug",
      "Sep",
      "Oct",
      "Nov",
      "Dec",
    ],
    labels: {
      style: {
        colors: "#64748b",
        fontSize: "12px",
      },
    },
    axisBorder: {
      show: false,
    },
    axisTicks: {
      show: false,
    },
  },
  yaxis: {
    labels: {
      show: false,
    },
  },
  grid: {
    show: false,
  },
};

const salesChart = new ApexCharts(
  document.querySelector("#sales-chart"),
  salesChartOptions
);
salesChart.render();

// Monthly Target Radial Chart
const targetChartOptions = {
  series: [75.55],
  chart: {
    height: "100%",
    type: "radialBar",
  },
  plotOptions: {
    radialBar: {
      startAngle: -135,
      endAngle: 135,
      hollow: {
        margin: 0,
        size: "70%",
        background: "#fff",
      },
      track: {
        background: "#f5f7fa",
        strokeWidth: "67%",
        margin: 0,
      },
      dataLabels: {
        show: true,
        name: {
          show: false,
        },
        value: {
          formatter: function (val) {
            return val + "%";
          },
          color: "#1e293b",
          fontSize: "36px",
          fontWeight: "700",
          show: true,
        },
      },
    },
  },
  fill: {
    colors: ["#2E8B57"],
  },
  stroke: {
    lineCap: "round",
  },
  labels: ["Progress"],
};

const targetChart = new ApexCharts(
  document.querySelector("#target-chart"),
  targetChartOptions
);
targetChart.render();

// --- Chart.js Statistics Chart ---
const ctx = document.getElementById("myChart").getContext("2d");
const myChart = new Chart(ctx, {
  type: "line",
  data: {
    labels: [
      "Jan",
      "Feb",
      "Mar",
      "Apr",
      "May",
      "Jun",
      "Jul",
      "Aug",
      "Sep",
      "Oct",
      "Nov",
      "Dec",
    ],
    datasets: [
      {
        label: "Target",
        data: [180, 190, 175, 165, 175, 168, 172, 205, 230, 210, 240, 235],
        fill: true,
        backgroundColor: "rgba(46, 139, 87, 0.05)",
        borderColor: "rgba(46, 139, 87, 1)",
        borderWidth: 2,
        tension: 0.3,
      },
      {
        label: "Achieved",
        data: [40, 30, 50, 40, 55, 40, 70, 100, 110, 125, 150, 140],
        fill: true,
        backgroundColor: "rgba(60, 179, 113, 0.05)",
        borderColor: "rgba(60, 179, 113, 1)",
        borderWidth: 2,
        tension: 0.3,
      },
    ],
  },
  options: {
    responsive: true,
    plugins: {
      legend: { display: false },
    },
    scales: {
      y: {
        beginAtZero: true,
        ticks: { stepSize: 50 },
      },
    },
  },
});
  </script>
  </body>
</html>