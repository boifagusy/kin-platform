<div class="card">
    <div class="card-header">
        <h5>📈 Score History (7 Days)</h5>
    </div>
    <div class="card-body">
        <canvas id="scoreChart" style="width: 100%; height: 200px;"></canvas>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const ctx = document.getElementById('scoreChart').getContext('2d');
                const history = {!! json_encode($history ?? []) !!};
                
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: history.map(h => new Date(h.time).toLocaleDateString()),
                        datasets: [{
                            label: 'Safety Score',
                            data: history.map(h => h.score),
                            borderColor: '#007bff',
                            backgroundColor: 'rgba(0, 123, 255, 0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                min: 0,
                                max: 100,
                                ticks: {
                                    stepSize: 20
                                }
                            }
                        }
                    }
                });
            });
        </script>
    </div>
</div>
