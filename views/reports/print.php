<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Duty Roster Report - <?= htmlspecialchars($campInfo['camp_name']) ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 for Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: white !important;
            color: black !important;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 30px;
        }
        .report-header {
            border-bottom: 2px double #333;
            margin-bottom: 30px;
            padding-bottom: 20px;
        }
        .slaf-crest {
            font-size: 2.5rem;
            color: #1e3a8a;
            margin-bottom: 10px;
        }
        .table-report th {
            background-color: #f1f5f9 !important;
            color: #1e293b !important;
            border-bottom: 2px solid #94a3b8 !important;
            font-size: 0.85rem;
            text-transform: uppercase;
        }
        .table-report td {
            font-size: 0.9rem;
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>

    <div class="container-fluid">
        <!-- Control buttons -->
        <div class="row mb-4 no-print">
            <div class="col-12 text-end">
                <button onclick="window.print();" class="btn btn-primary me-2"><i class="fas fa-print"></i> Trigger Print</button>
                <button onclick="window.close();" class="btn btn-secondary"><i class="fas fa-xmark"></i> Close Window</button>
            </div>
        </div>

        <!-- Header -->
        <div class="row report-header align-items-center">
            <div class="col-md-2 text-center text-md-start">
                <div class="slaf-crest"><i class="fas fa-shield-halved"></i></div>
            </div>
            <div class="col-md-10 text-center text-md-start">
                <h3 class="fw-bold mb-1 text-primary">SRI LANKA AIR FORCE</h3>
                <h4 class="fw-bold mb-1 text-secondary">PROVOST DIVISION WATCH DUTY REPORT</h4>
                <div class="small text-muted">
                    Base: <strong><?= htmlspecialchars($campInfo['camp_name']) ?> (<?= htmlspecialchars($campInfo['camp_code']) ?>)</strong> &bull;
                    Period: <strong><?= date('d M Y', strtotime($startDate)) ?> - <?= date('d M Y', strtotime($endDate)) ?></strong>
                </div>
            </div>
        </div>

        <!-- Report Data Table -->
        <div class="row">
            <div class="col-12">
                <?php if (empty($results)): ?>
                    <div class="alert alert-warning text-center">No assignments match the specified filters in this period.</div>
                <?php else: ?>
                    <table class="table table-bordered table-striped table-report align-middle">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Service Number</th>
                                <th>Rank & Name</th>
                                <th>Duty Type</th>
                                <th>Shift rotation</th>
                                <th>Timings</th>
                                <th>Priority</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($results as $row): ?>
                                <tr>
                                    <td class="fw-bold"><?= date('D, d M Y', strtotime($row['duty_date'])) ?></td>
                                    <td class="font-monospace fw-medium"><?= htmlspecialchars($row['service_number']) ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($row['rank']) ?></strong> 
                                        <?= htmlspecialchars($row['initials'] . ' ' . $row['full_name']) ?>
                                    </td>
                                    <td><span class="badge bg-light text-dark border border-secondary border-opacity-20"><?= htmlspecialchars($row['duty_type_name']) ?></span></td>
                                    <td><?= htmlspecialchars($row['shift_name']) ?></td>
                                    <td class="font-monospace small"><?= htmlspecialchars(date('H:i', strtotime($row['start_time']))) ?> - <?= htmlspecialchars(date('H:i', strtotime($row['end_time']))) ?></td>
                                    <td><?= htmlspecialchars($row['priority_level']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Signature/Footer Block -->
        <div class="row mt-5 pt-5 text-center">
            <div class="col-md-4 offset-md-8">
                <div class="border-top border-dark pt-2" style="font-size: 0.85rem;">
                    <strong>Officer Commanding Provost (OCPROVST)</strong><br>
                    Sri Lanka Air Force
                </div>
            </div>
        </div>
    </div>

    <!-- Auto Print Script -->
    <script>
        window.addEventListener('load', () => {
            // Trigger automatic print on open, excluding visual components in print stylesheet
            setTimeout(() => {
                window.print();
            }, 500);
        });
    </script>
</body>
</html>
