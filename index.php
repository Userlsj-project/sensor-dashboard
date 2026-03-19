<?php
$conn = mysqli_connect('localhost', 'sensor_user', 'Sensor@1234', 'sensor_db');
if (!$conn) {
    die('<p style="color:red;">Connection failed: ' . mysqli_connect_error() . '</p>');
}

// Fetch last 50 rows
$result = mysqli_query($conn, "
    SELECT sensor_id, temperature, humidity, pressure, recorded_at
    FROM sensor_data
    ORDER BY recorded_at DESC
    LIMIT 50
");
$rows = [];
while ($row = mysqli_fetch_assoc($result)) {
    $rows[] = $row;
}

// Latest value per sensor (using MAX(id) subquery)
$latestResult = mysqli_query($conn, "
    SELECT s.sensor_id, s.temperature, s.humidity, s.pressure, s.recorded_at
    FROM sensor_data s
    INNER JOIN (
        SELECT sensor_id, MAX(id) AS max_id
        FROM sensor_data
        GROUP BY sensor_id
    ) latest ON s.sensor_id = latest.sensor_id AND s.id = latest.max_id
    ORDER BY s.sensor_id
");
$latestRows = [];
while ($row = mysqli_fetch_assoc($latestResult)) {
    $latestRows[] = $row;
}

mysqli_close($conn);

// Prepare chart data
$sensor01_temps = [];
$sensor02_temps = [];
$sensor03_temps = [];
$labels = [];

// rows are DESC, reverse for chronological order in chart
$chartRows = array_reverse($rows);
foreach ($chartRows as $r) {
    $ts = date('H:i:s', strtotime($r['recorded_at']));
    if (!in_array($ts, $labels)) $labels[] = $ts;
}
foreach ($chartRows as $r) {
    $ts = date('H:i:s', strtotime($r['recorded_at']));
    if ($r['sensor_id'] === 'SENSOR-01') $sensor01_temps[$ts] = $r['temperature'];
    if ($r['sensor_id'] === 'SENSOR-02') $sensor02_temps[$ts] = $r['temperature'];
    if ($r['sensor_id'] === 'SENSOR-03') $sensor03_temps[$ts] = $r['temperature'];
}

$s01 = array_values(array_map(fn($l) => $sensor01_temps[$l] ?? null, $labels));
$s02 = array_values(array_map(fn($l) => $sensor02_temps[$l] ?? null, $labels));
$s03 = array_values(array_map(fn($l) => $sensor03_temps[$l] ?? null, $labels));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="5">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Factory Sensor Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f0f2f5; color: #333; }

        header {
            background: #2c3e50;
            color: #fff;
            padding: 18px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        header h1 { font-size: 1.4rem; letter-spacing: 1px; }
        header span { font-size: 0.9rem; opacity: 0.8; }

        .container { max-width: 1100px; margin: 30px auto; padding: 0 20px; }

        h2 { font-size: 1.1rem; color: #2c3e50; margin-bottom: 12px; }

        .card {
            background: #fff;
            border-radius: 8px;
            padding: 20px 24px;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
        }

        table { width: 100%; border-collapse: collapse; font-size: 0.92rem; }
        th {
            background: #2c3e50;
            color: #fff;
            padding: 10px 14px;
            text-align: left;
            font-weight: 600;
        }
        td { padding: 9px 14px; border-bottom: 1px solid #eee; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #f7f9fc; }

        .badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 0.82rem;
            font-weight: 600;
            color: #fff;
        }
        .s01 { background: #e74c3c; }
        .s02 { background: #2980b9; }
        .s03 { background: #27ae60; }

        .chart-wrap { position: relative; height: 320px; }
    </style>
</head>
<body>

<header>
    <h1>Smart Factory Sensor Dashboard</h1>
    <span>Current time: <?php echo date('Y-m-d H:i:s'); ?> &nbsp;|&nbsp; Auto-refresh: 5s</span>
</header>

<div class="container">

    <div class="card">
        <h2>Latest Reading per Sensor</h2>
        <table>
            <thead>
                <tr>
                    <th>Sensor ID</th>
                    <th>Temperature (°C)</th>
                    <th>Humidity (%)</th>
                    <th>Pressure (hPa)</th>
                    <th>Recorded At</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($latestRows as $r):
                    $id = $r['sensor_id'];
                    $cls = $id === 'SENSOR-01' ? 's01' : ($id === 'SENSOR-02' ? 's02' : 's03');
                ?>
                <tr>
                    <td><span class="badge <?php echo $cls; ?>"><?php echo htmlspecialchars($id); ?></span></td>
                    <td><?php echo number_format($r['temperature'], 1); ?></td>
                    <td><?php echo number_format($r['humidity'], 1); ?></td>
                    <td><?php echo number_format($r['pressure'], 1); ?></td>
                    <td><?php echo htmlspecialchars($r['recorded_at']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="card">
        <h2>Temperature Over Time (last 50 readings)</h2>
        <div class="chart-wrap">
            <canvas id="tempChart"></canvas>
        </div>
    </div>

    <div class="card">
        <h2>Recent Sensor Readings (last 50)</h2>
        <table>
            <thead>
                <tr>
                    <th>Sensor ID</th>
                    <th>Temperature (°C)</th>
                    <th>Humidity (%)</th>
                    <th>Pressure (hPa)</th>
                    <th>Recorded At</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r):
                    $id = $r['sensor_id'];
                    $cls = $id === 'SENSOR-01' ? 's01' : ($id === 'SENSOR-02' ? 's02' : 's03');
                ?>
                <tr>
                    <td><span class="badge <?php echo $cls; ?>"><?php echo htmlspecialchars($id); ?></span></td>
                    <td><?php echo number_format($r['temperature'], 1); ?></td>
                    <td><?php echo number_format($r['humidity'], 1); ?></td>
                    <td><?php echo number_format($r['pressure'], 1); ?></td>
                    <td><?php echo htmlspecialchars($r['recorded_at']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<script>
const labels = <?php echo json_encode($labels); ?>;
const s01 = <?php echo json_encode($s01); ?>;
const s02 = <?php echo json_encode($s02); ?>;
const s03 = <?php echo json_encode($s03); ?>;

new Chart(document.getElementById('tempChart'), {
    type: 'line',
    data: {
        labels: labels,
        datasets: [
            {
                label: 'SENSOR-01',
                data: s01,
                borderColor: '#e74c3c',
                backgroundColor: 'rgba(231,76,60,0.08)',
                tension: 0.3,
                spanGaps: true,
                pointRadius: 3
            },
            {
                label: 'SENSOR-02',
                data: s02,
                borderColor: '#2980b9',
                backgroundColor: 'rgba(41,128,185,0.08)',
                tension: 0.3,
                spanGaps: true,
                pointRadius: 3
            },
            {
                label: 'SENSOR-03',
                data: s03,
                borderColor: '#27ae60',
                backgroundColor: 'rgba(39,174,96,0.08)',
                tension: 0.3,
                spanGaps: true,
                pointRadius: 3
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'top' }
        },
        scales: {
            x: { ticks: { maxTicksLimit: 10 } },
            y: {
                title: { display: true, text: 'Temperature (°C)' }
            }
        }
    }
});
</script>

</body>
</html>
