<div class="card">
    <div class="card-header">
        <h5 class="card-title">Step 10: PM SuryaGhar Document Upload</h5>
        <p class="text-muted mb-0">Maximum file size: 5MB per document | Allowed formats: PDF, JPG, JPEG, PNG</p>
    </div>
    <div class="card-body">
        <form id="step10Form" enctype="multipart/form-data">
            
            <!-- Document Uploads Section -->
            <div class="row">
                <div class="col-12 mb-4">
                    <h6 class="border-bottom pb-2">Required Documents</h6>
                </div>
                
                <!-- Required Documents with Previews -->
                <?php
                $requiredDocuments = [
                    'aadhar' => 'aadhar_card',
                    'pan_card' => 'PAN Card',
                    'electric_bill' => 'Electricity Bill',
                    'bank_passbook' => 'Bank Passbook/Statement'
                ];
                
                foreach($requiredDocuments as $docKey => $docLabel):
                    $hasDoc = hasClientDocument($connect->dbconnect(), $client_id ?? 0, $docKey);
                    $docPath = getClientDocumentPath($connect->dbconnect(), $client_id ?? 0, $docKey);
                    $docUrl = getDocumentWebUrl($docPath);
                    $isImage = $docPath && (strpos($docPath, '.jpg') !== false || strpos($docPath, '.jpeg') !== false || strpos($docPath, '.png') !== false);
                ?>
                <div class="col-md-6 mb-3">
                    <label class="form-label"><?php echo $docLabel; ?> <span class="text-danger">*</span></label>
                    <input type="file" class="form-control" name="<?php echo $docKey; ?>" accept=".pdf,.jpg,.jpeg,.png" <?php echo !$hasDoc ? 'required' : ''; ?>>
                    <small class="text-muted">PDF, JPG, PNG (Max 5MB)</small>
                    
                    <?php if($hasDoc): ?>
                    <div class="mt-2">
                        <small class="text-muted">Current Document:</small>
                        <div class="d-flex align-items-center mt-1">
                            <?php if($isImage && $docUrl): ?>
                                <img src="<?php echo $docUrl; ?>" alt="<?php echo $docLabel; ?>" 
                                     class="me-2" style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px;">
                            <?php else: ?>
                                <div class="bg-light d-flex align-items-center justify-content-center me-2" 
                                     style="width: 60px; height: 60px; border-radius: 5px;">
                                    <i class="bi bi-file-earmark-pdf text-danger" style="font-size: 24px;"></i>
                                </div>
                            <?php endif; ?>
                            <a href="<?php echo $docUrl ?: '#'; ?>" target="_blank" 
                               class="btn btn-sm btn-outline-primary">View Document</a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Optional Documents -->
            <div class="row mt-4">
                <div class="col-12 mb-3">
                    <h6 class="border-bottom pb-2">Additional Documents (Optional)</h6>
                </div>
                
                <?php
                $optionalDocuments = [
                    'model_agreement' => 'Model Agreement',
                    'dcr_certificate' => 'DCR Certificate',
                    'bank_statement' => 'Bank Statement',
                    'salary_slip' => 'Salary Slip',
                    'it_return' => 'IT Return',
                    'gumasta' => 'Gumasta License'
                ];
                
                foreach($optionalDocuments as $docKey => $docLabel):
                    $hasDoc = hasClientDocument($connect->dbconnect(), $client_id ?? 0, $docKey);
                    $docPath = getClientDocumentPath($connect->dbconnect(), $client_id ?? 0, $docKey);
                    $docUrl = getDocumentWebUrl($docPath);
                    $isImage = $docPath && (strpos($docPath, '.jpg') !== false || strpos($docPath, '.jpeg') !== false || strpos($docPath, '.png') !== false);
                ?>
                <div class="col-md-6 mb-3">
                    <label class="form-label"><?php echo $docLabel; ?></label>
                    <input type="file" class="form-control" name="<?php echo $docKey; ?>" accept=".pdf,.jpg,.jpeg,.png">
                    <small class="text-muted">PDF, JPG, PNG (Max 5MB)</small>
                    
                    <?php if($hasDoc): ?>
                    <div class="mt-2">
                        <small class="text-muted">Current Document:</small>
                        <div class="d-flex align-items-center mt-1">
                            <?php if($isImage && $docUrl): ?>
                                <img src="<?php echo $docUrl; ?>" alt="<?php echo $docLabel; ?>" 
                                     class="me-2" style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px;">
                            <?php else: ?>
                                <div class="bg-light d-flex align-items-center justify-content-center me-2" 
                                     style="width: 60px; height: 60px; border-radius: 5px;">
                                    <i class="bi bi-file-earmark-pdf text-danger" style="font-size: 24px;"></i>
                                </div>
                            <?php endif; ?>
                            <a href="<?php echo $docUrl ?: '#'; ?>" target="_blank" 
                               class="btn btn-sm btn-outline-primary">View Document</a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Inverter & System Details -->
            <div class="row mt-4">
                <div class="col-12 mb-3">
                    <h6 class="border-bottom pb-2">System Details <span class="text-danger">*</span></h6>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label">Inverter Manufacturing Company <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="inverter_company_name" 
                           value="<?php echo htmlspecialchars($client_data['inverter_company_name'] ?? ''); ?>" required>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label">Inverter Capacity <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="inverter_capacity" 
                           value="<?php echo htmlspecialchars($client_data['inverter_capacity'] ?? ''); ?>" required>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label">Inverter Serial Number <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="inverter_serial_number" 
                           value="<?php echo htmlspecialchars($client_data['inverter_serial_number'] ?? ''); ?>" required>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label">DCR Certificate Number <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="dcr_certificate_number" 
                           value="<?php echo htmlspecialchars($client_data['dcr_certificate_number'] ?? ''); ?>" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Number of Solar Panels <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" name="number_of_panels" id="numberOfPanels" 
                           value="<?php echo htmlspecialchars($client_data['number_of_panels'] ?? ''); ?>" min="1" max="20" required>
                </div>
            </div>

            <!-- Dynamic Panel Serial Numbers -->
            <div class="row mt-3">
                <div class="col-12 mb-3">
                    <button type="button" class="btn btn-outline-primary" onclick="generatePanelFields()">
                        <i class="bi bi-plus-circle"></i> Generate Panel Serial Number Fields
                    </button>
                </div>
                
                <div id="panelSerialNumbers" class="col-12">
                    <!-- Dynamic panel serial number fields will be generated here -->
                </div>
            </div>

        </form>
    </div>
</div>

<script>
function generatePanelFields() {
    const numberOfPanels = parseInt(document.getElementById('numberOfPanels').value) || 0;
    const container = document.getElementById('panelSerialNumbers');
    
    if (numberOfPanels <= 0 || numberOfPanels > 20) {
        alert('Please enter a valid number of panels (1-20)');
        return;
    }
    
    let html = '<div class="row"><div class="col-12"><h6>Panel Serial Numbers <span class="text-danger">*</span></h6></div></div>';
    
    for (let i = 1; i <= numberOfPanels; i++) {
        html += `
            <div class="row mb-2">
                <div class="col-md-6">
                    <label class="form-label">Panel ${i} Serial Number</label>
                    <input type="text" class="form-control" name="panel_serial_${i}" 
                           placeholder="Enter serial number for panel ${i}" required>
                </div>
            </div>
        `;
    }
    
    container.innerHTML = html;
}

function validateStep10() {
    // Validate required fields
    const requiredFields = [
        'inverter_company_name',
        'inverter_serial_number', 
        'dcr_certificate_number',
        'number_of_panels'
    ];
    
    for (let field of requiredFields) {
        const value = document.querySelector(`[name="${field}"]`).value.trim();
        if (!value) {
            alert(`Please fill in ${field.replace(/_/g, ' ')}`);
            document.querySelector(`[name="${field}"]`).focus();
            return false;
        }
    }
    
    return true;
}

// Auto-generate panel fields if number is already set
document.addEventListener('DOMContentLoaded', function() {
    const numberOfPanels = document.getElementById('numberOfPanels').value;
    if (numberOfPanels && numberOfPanels > 0) {
        generatePanelFields();
        
        // Pre-fill existing panel serial numbers if available
        <?php if(isset($client_data['number_of_panels']) && $client_data['number_of_panels'] > 0): ?>
            setTimeout(() => {
                <?php 
                if(isset($client_data['id'])) {
                    $panelResult = $connect->dbconnect()->query("SELECT panel_number, serial_number FROM solar_panels WHERE client_id = " . $client_data['id']);
                    while($panel = $panelResult->fetch_assoc()): 
                ?>
                    const panelField = document.querySelector('[name="panel_serial_<?php echo $panel['panel_number']; ?>"]');
                    if (panelField) {
                        panelField.value = "<?php echo htmlspecialchars($panel['serial_number']); ?>";
                    }
                <?php 
                    endwhile; 
                }
                ?>
            }, 100);
        <?php endif; ?>
    }
});
</script>