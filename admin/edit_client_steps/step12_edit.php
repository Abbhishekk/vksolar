<div class="card">
    <div class="card-header">
        <h5 class="card-title">Step 12: Meter Installation Photo</h5>
    </div>
    <div class="card-body">
        <form id="step12Form" enctype="multipart/form-data">
            <div class="row">
                <!-- Meter Photo -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Meter Installation Photo</label>
                    <input type="file" class="form-control" name="meter_installation_photo" accept="image/*">
                    <small class="text-muted">Max size: 1MB</small>
                    
                    <!-- File Preview -->
                    <?php 
                    $hasMeterPhoto = hasClientDocument($connect->dbconnect(), $client_id, 'meter_photo');
                    $meterPath = getClientDocumentPath($connect->dbconnect(), $client_id, 'meter_photo');
                    $meterUrl = getDocumentWebUrl($meterPath);
                    ?>
                    <?php if($hasMeterPhoto && $meterUrl): ?>
                    <div class="mt-2">
                        <small class="text-muted">Current Photo:</small>
                        <div class="d-flex align-items-center mt-1">
                            <img src="<?php echo $meterUrl; ?>" alt="Meter Installation Photo" 
                                 class="file-preview me-2">
                            <a href="<?php echo $meterUrl; ?>" target="_blank" 
                               class="btn btn-sm btn-outline-primary">View Full</a>
                        </div>
                    </div>
                    <?php elseif($hasMeterPhoto): ?>
                    <div class="mt-2">
                        <span class="badge bg-success">
                            <i class="bi bi-check-circle"></i> Photo Uploaded
                        </span>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Meter Details -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Meter Number</label>
                    <input type="text" class="form-control" name="meter_number" 
                           value="<?php echo htmlspecialchars($client_data['meter_number'] ?? ''); ?>">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Installation Date</label>
                    <input type="date" class="form-control" name="meter_installation_date" 
                           value="<?php echo htmlspecialchars($client_data['meter_installation_date'] ?? ''); ?>">
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function validateStep12() {
    // Photo upload is optional
    return true;
}
</script>