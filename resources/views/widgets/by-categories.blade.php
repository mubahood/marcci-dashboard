<div class="card  mb-4 mb-md-5 border-0">
    <!--begin::Header-->
    <div class="d-flex justify-content-between px-3 pt-2 px-md-4 border-bottom">
        <h4 style="line-height: 1; margin: 0;" class="fs-22 fw-800">
            Groundnut Varieties 
        </h4>
    </div>
    <div class="card-body py-2 py-md-3">
        <canvas id="graph_animals" style="width: 100%;"></canvas>
    </div>
</div>

<script>
    $(function () {
        // Define your groundnut varieties and corresponding data
        var varieties = ['Valencia', 'Virginia', 'Spanish'];
        var data = [2, 1, 3]; // Update this array with the counts for each variety
        var backgroundColors = generateRandomColors(varieties.length);

        var config = {
            type: 'pie',
            data: {
                datasets: [{
                    data: data,
                    backgroundColor: backgroundColors,
                    label: 'Groundnut Varieties'
                }],
                labels: varieties,
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    position: 'left',
                    display: true,
                },
                title: {
                    display: false,
                    text: 'Gardens By Crop'
                },
                animation: {
                    animateScale: true,
                    animateRotate: true
                }
            }
        };

        var ctx = document.getElementById('graph_animals').getContext('2d');
        new Chart(ctx, config);
    });

    // Function to generate random colors
    function generateRandomColors(count) {
        var colors = [];
        for (var i = 0; i < count; i++) {
            colors.push('#' + Math.floor(Math.random() * 16777215).toString(16));
        }
        return colors;
    }
</script>
