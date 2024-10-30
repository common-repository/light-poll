

<div class="container">

    <canvas id="<?php echo base64_encode($poll_uniq_name);?>"></canvas>
 
</div>


  <script>
  document.addEventListener("DOMContentLoaded", chart_ready);
  function chart_ready() {
var ctx = document.getElementById("<?php echo base64_encode($poll_uniq_name);?>").getContext('2d');
var myChart = new Chart(ctx, {
  type: 'doughnut',
  data: {
    labels: glob_labels,
    datasets: [{
      backgroundColor: glob_colors,
      data: glob_data
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: {
        display: true,
        labels: {
        font: {
                size: 18
              }
        }
      },
      title: {
        display: true,
        text: ''
      }
    }
  }
});
  }

  </script>


