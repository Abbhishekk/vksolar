<div class="card">
    <div class="card-header">
        <h5 class="card-title">Step 9: Fitting Photos</h5>
    </div>
    <div class="card-body">
        <form id="step9Form" enctype="multipart/form-data">
            <div class="row">
                <!-- Solar Panel Photo -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Solar Panel Structure Photo</label>
                    <input type="file" class="form-control" name="solar_panel_photo" accept="image/*">
                    <small class="text-muted">Max size: 1MB</small>
                    
                    <!-- File Preview -->
                    <?php 
                    $hasSolarPanelPhoto = hasClientDocument($connect->dbconnect(), $client_id, 'solar_panel_photo');
                    $solarPanelPath = getClientDocumentPath($connect->dbconnect(), $client_id, 'solar_panel_photo');
                    $solarPanelUrl = getDocumentWebUrl($solarPanelPath);
                    ?>
                    <?php if($hasSolarPanelPhoto && $solarPanelUrl): ?>
                    <div class="mt-2">
                        <small class="text-muted">Current Photo:</small>
                        <div class="d-flex align-items-center mt-1">
                            <img src="<?php echo $solarPanelUrl; ?>" alt="Solar Panel Photo" 
                                 class="file-preview me-2">
                            <a href="<?php echo $solarPanelUrl; ?>" target="_blank" 
                               class="btn btn-sm btn-outline-primary">View Full</a>
                        </div>
                    </div>
                    <?php elseif($hasSolarPanelPhoto): ?>
                    <div class="mt-2">
                        <span class="badge bg-success">
                            <i class="bi bi-check-circle"></i> Photo Uploaded
                        </span>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Inverter Photo -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Inverter Photo</label>
                    <input type="file" class="form-control" name="inverter_photo" accept="image/*">
                    <small class="text-muted">Max size: 1MB</small>
                    
                    <!-- File Preview -->
                    <?php 
                    $hasInverterPhoto = hasClientDocument($connect->dbconnect(), $client_id, 'inverter_photo');
                    $inverterPath = getClientDocumentPath($connect->dbconnect(), $client_id, 'inverter_photo');
                    $inverterUrl = getDocumentWebUrl($inverterPath);
                    ?>
                    <?php if($hasInverterPhoto && $inverterUrl): ?>
                    <div class="mt-2">
                        <small class="text-muted">Current Photo:</small>
                        <div class="d-flex align-items-center mt-1">
                            <img src="<?php echo $inverterUrl; ?>" alt="Inverter Photo" 
                                 class="file-preview me-2">
                            <a href="<?php echo $inverterUrl; ?>" target="_blank" 
                               class="btn btn-sm btn-outline-primary">View Full</a>
                        </div>
                    </div>
                    <?php elseif($hasInverterPhoto): ?>
                    <div class="mt-2">
                        <span class="badge bg-success">
                            <i class="bi bi-check-circle"></i> Photo Uploaded
                        </span>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Geotag Photo -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Geotag Photo</label>
                    <input type="file" class="form-control" name="geotag_photo" accept="image/*">
                    <small class="text-muted">Max size: 1MB</small>
                    
                    <!-- File Preview -->
                    <?php 
                    $hasGeotagPhoto = hasClientDocument($connect->dbconnect(), $client_id, 'geotag_photo');
                    $geotagPath = getClientDocumentPath($connect->dbconnect(), $client_id, 'geotag_photo');
                    $geotagUrl = getDocumentWebUrl($geotagPath);
                    ?>
                    <?php if($hasGeotagPhoto && $geotagUrl): ?>
                    <div class="mt-2">
                        <small class="text-muted">Current Photo:</small>
                        <div class="d-flex align-items-center mt-1">
                            <img src="<?php echo $geotagUrl; ?>" alt="Geotag Photo" 
                                 class="file-preview me-2">
                            <a href="<?php echo $geotagUrl; ?>" target="_blank" 
                               class="btn btn-sm btn-outline-primary">View Full</a>
                        </div>
                    </div>
                    <?php elseif($hasGeotagPhoto): ?>
                    <div class="mt-2">
                        <span class="badge bg-success">
                            <i class="bi bi-check-circle"></i> Photo Uploaded
                        </span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function validateStep9() {
    // Photo uploads are optional in edit mode
    return true;
}
</script>