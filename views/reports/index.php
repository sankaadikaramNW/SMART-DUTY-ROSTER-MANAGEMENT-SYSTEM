<?php
include __DIR__ . '/../layout/header.php';
?>
<div class="row mb-4 align-items-center">
    <div class="col-md-12">
        <h2 class="fw-bold mb-1 gradient-text"><i class="fas fa-print"></i> Station Reports & Exports</h2>
        <p class="text-secondary">Generate and print watch schedules or download duty statistics as CSV.</p>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="glass-card mb-4 animate-fade-in">
            <div class="card-header border-bottom border-secondary border-opacity-10 bg-transparent py-3 px-4">
                <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-sliders text-info me-2"></i> Report Filter Parameters</h5>
            </div>
            
            <form action="<?= BASE_URL ?>/reports/generate" method="GET" target="_blank">
                <div class="card-body p-4">
                    <div class="row g-4">
                        <!-- Base selection -->
                        <div class="col-12">
                            <label for="camp_id" class="form-label text-secondary small">Base Camp Location</label>
                            <select class="form-select form-control-custom" id="camp_id" name="camp_id" required>
                                <?php foreach ($camps as $c): ?>
                                    <?php 
                                    // SNCO restriction
                                    $restrictedCampId = LocationMiddleware::getCampConstraint();
                                    if ($restrictedCampId !== null && (int)$c['camp_id'] !== $restrictedCampId) {
                                        continue;
                                    }
                                    ?>
                                    <option value="<?= $c['camp_id'] ?>" <?= (int)$c['camp_id'] === (int)$activeCampId ? 'selected' : '' ?>><?= htmlspecialchars($c['camp_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Date Range -->
                        <div class="col-md-6">
                            <label for="start_date" class="form-label text-secondary small">Start Date</label>
                            <input type="date" class="form-control form-control-custom" id="start_date" name="start_date" value="<?= date('Y-m-01') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="end_date" class="form-label text-secondary small">End Date</label>
                            <input type="date" class="form-control form-control-custom" id="end_date" name="end_date" value="<?= date('Y-m-t') ?>" required>
                        </div>

                        <!-- Optional Shift -->
                        <div class="col-md-6">
                            <label for="shift_id" class="form-label text-secondary small">Filter by Shift (Optional)</label>
                            <select class="form-select form-control-custom" id="shift_id" name="shift_id">
                                <option value="">All Shift Rotations</option>
                                <?php foreach ($shifts as $s): ?>
                                    <option value="<?= $s['shift_id'] ?>"><?= htmlspecialchars($s['shift_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Optional Duty Type -->
                        <div class="col-md-6">
                            <label for="duty_type_id" class="form-label text-secondary small">Filter by Duty Type (Optional)</label>
                            <select class="form-select form-control-custom" id="duty_type_id" name="duty_type_id">
                                <option value="">All Duty Types</option>
                                <?php foreach ($dutyTypes as $dt): ?>
                                    <option value="<?= $dt['duty_type_id'] ?>"><?= htmlspecialchars($dt['duty_type_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Optional Airman Service Number -->
                        <div class="col-12">
                            <label for="service_number" class="form-label text-secondary small">Filter by Specific Service Number (Optional)</label>
                            <input type="text" class="form-control form-control-custom" id="service_number" name="service_number" placeholder="e.g. 51837 or admin">
                        </div>

                        <!-- Export Type Selection -->
                        <div class="col-12 mt-4">
                            <label class="form-label text-secondary small d-block mb-3">Export Format</label>
                            
                            <div class="d-flex gap-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="export_type" id="export_print" value="print" checked>
                                    <label class="form-check-label text-dark fw-medium" for="export_print">
                                        <i class="fas fa-file-pdf text-danger me-1"></i> Print / PDF Layout
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="export_type" id="export_csv" value="csv">
                                    <label class="form-check-label text-dark fw-medium" for="export_csv">
                                        <i class="fas fa-file-excel text-success me-1"></i> Download CSV Data Sheet
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer border-top border-secondary border-opacity-20 bg-transparent py-3 px-4 d-flex flex-column-reverse flex-sm-row justify-content-sm-end gap-2">
                    <button type="reset" class="btn btn-custom btn-custom-orange">
                        <i class="fas fa-rotate"></i> Reset
                    </button>
                    <button type="submit" class="btn btn-custom btn-custom-primary">
                        <i class="fas fa-file-arrow-down"></i> Generate & Export Report
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php
include __DIR__ . '/../layout/footer.php';
?>
