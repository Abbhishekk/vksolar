<div class="card">
    <div class="card-header">
        <h5 class="card-title">Step 7: MAHADISCOM Sanction Load</h5>
    </div>
    <div class="card-body">
        <form id="step7Form">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Load Change Application Number <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="load_change_application_number" 
                           value="<?php echo htmlspecialchars($client_data['load_change_application_number'] ?? ''); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Rooftop Solar Application Number <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="rooftop_solar_application_number" 
                           value="<?php echo htmlspecialchars($client_data['rooftop_solar_application_number'] ?? ''); ?>" required>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function validateStep7() {
    const loadAppNo = document.querySelector('input[name="load_change_application_number"]').value.trim();
    const solarAppNo = document.querySelector('input[name="rooftop_solar_application_number"]').value.trim();
    
    if(!loadAppNo) {
        alert('Please enter Load Change Application Number');
        return false;
    }
    
    if(!solarAppNo) {
        alert('Please enter Rooftop Solar Application Number');
        return false;
    }
    
    return true;
}
</script>