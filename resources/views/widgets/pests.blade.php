<!DOCTYPE html>
<html>
<head>
    <title>Pest and Disease Severity Bar Chart</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div style="width: 80%; margin: auto;">
        <canvas id="barChart"></canvas>
    </div>

    <script>
        // Sample data for pest and disease severity in different areas of the farm
        const farmData = [
            [1, 2, 3, 4, 2],
            [2, 1, 2, 3, 1],
            [3, 2, 1, 1, 2],
            [4, 3, 2, 1, 3]
        ];

        // Calculate the totals for each column
        const columnTotals = farmData[0].map((_, col) =>
            farmData.reduce((acc, row) => acc + row[col], 0)
        );

        // Create the bar chart
        const ctx = document.getElementById('barChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Area 1', 'Area 2', 'Area 3', 'Area 4', 'Area 5'],
                datasets: [{
                    label: 'Severity Level',
                    data: columnTotals,
                    backgroundColor: [
                        'rgba(0, 255, 0, 0.7)',
                        'rgba(255, 255, 0, 0.7)',
                        'rgba(255, 102, 0, 0.7)',
                        'rgba(255, 0, 0, 0.7)',
                        'rgba(0, 0, 255, 0.7)',
                    ],
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 20, // You can adjust this based on your data
                    }
                }
            }
        });
    </script>
</body>
</html>
