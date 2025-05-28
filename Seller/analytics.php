<?php

session_start();
include('../Database/dbConnection.php');

// --- Handle AJAX request ---
if (isset($_GET['action']) && $_GET['action'] === 'data') {
    $seller_id = $_SESSION['user_id'];
    $range = isset($_GET['range']) ? (int)$_GET['range'] : 6;

    $months = [];
    $salesData = [];

    for ($i = $range - 1; $i >= 0; $i--) {
        $date = date("Y-m", strtotime("-$i months"));
        $monthNum = date("n", strtotime($date));
        $monthLabel = date("M", strtotime($date));
        $year = date("Y", strtotime($date));

        $months[] = $monthLabel;
        $stmt = $conn->prepare("
             SELECT SUM(order_items.quantity * order_items.price_at_purchase) AS total
             FROM orders
             JOIN order_items ON orders.order_id = order_items.order_id
             JOIN products ON order_items.order_id = products.product_id
            WHERE products.user_id = ? AND MONTH(orders.order_date) = ? AND YEAR(orders.order_date) = ?");

        $stmt->bind_param("iii", $seller_id, $monthNum, $year);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $salesData[] = $result['total'] ?? 0;
    }

    header('Content-Type: application/json');
    echo json_encode([
        "labels" => $months,
        "data" => $salesData
    ]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Sales Analytics</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<style>
    body {
        background: #1A355B;
        color: white;
        font-family: sans-serif;
        min-height: 90vh;
    }

    .chart-container {
        background: rgba(255, 255, 255, 0.1);
        padding: 20px;
        border-radius: 10px;
        margin-top: 30px;
    }

    .back-btn {
        background-color: #f2f2f2;
        border: none;
        color: #333;
        font-size: 16px;
        padding: 10px 15px;
        border-radius: 8px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .back-btn:hover {
        background-color: #ddd;
    }
</style>


<body>
    <!-- Back Arrow Button -->
    <button onclick="history.back()" class="back-btn">← Back</button>


    <div style="text-align: center; margin-top: 40px;">
        <label for="monthRange">Select Range: </label>
        <select id="monthRange">
            <option value="3">Last 3 Months</option>
            <option value="6" selected>Last 6 Months</option>
            <option value="9" selected>Last 9 Months</option>
            <option value="12">Last 12 Months</option>
        </select>
    </div>

    <div class="chart-container mt-5">
        <h1 class="text-center">Sales Over Selected Months</h1>
        <canvas id="salesChart" height="100"></canvas>
    </div>

    <script>
        let salesChart;

        function loadChart(range) {
            fetch(`analytics.php?action=data&range=${range}`)
                .then(response => response.json())
                .then(chartData => {
                    console.log(chartData); // Add this line

                    const ctx = document.getElementById('salesChart').getContext('2d');

                    if (salesChart) salesChart.destroy(); // destroy old chart

                    salesChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: chartData.labels,
                            datasets: [{
                                label: 'Sales in Rands',
                                data: chartData.data,
                                backgroundColor: 'rgba(255, 255, 255, 0.6)',
                                borderColor: 'white',
                                borderWidth: 2
                            }]
                        },
                        options: {
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        color: 'white'
                                    },
                                    grid: {
                                        color: 'rgba(255,255,255,0.2)'
                                    }
                                },
                                x: {
                                    ticks: {
                                        color: 'white'
                                    },
                                    grid: {
                                        color: 'rgba(255,255,255,0.2)'
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    labels: {
                                        color: 'white'
                                    }
                                }
                            }
                        }
                    });
                })
                .catch(error => console.error('Error loading sales data:', error));
        }

        // Initial load
        loadChart(document.getElementById('monthRange').value);

        // Event listener for dropdown
        document.getElementById('monthRange').addEventListener('change', function() {
            loadChart(this.value);
        });
    </script>
</body>

</html>