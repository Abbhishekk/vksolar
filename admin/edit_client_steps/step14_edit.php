<div class="card">
    <div class="card-header">
        <h5 class="card-title">Step 14: Reference Details</h5>
    </div>
    <div class="card-body">
        <form id="step14Form" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Reference Name</label>
                    <input type="text" class="form-control" name="reference_name" 
                           value="<?php echo htmlspecialchars($client_data['reference_name'] ?? ''); ?>" 
                           placeholder="Enter reference person name">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Reference Contact Number</label>
                    <input type="text" class="form-control" name="reference_contact" 
                           value="<?php echo htmlspecialchars($client_data['reference_contact'] ?? ''); ?>" 
                           placeholder="Enter reference contact number">
                </div>

            </div>
            
            <!-- Completion Note -->
            <div class="alert alert-success mt-3">
                <h6><i class="bi bi-check-circle"></i> Congratulations!</h6>
                <p class="mb-0">You have completed all steps of the client workflow. Click "Complete Workflow" to finish the process.</p>
            </div>
        </form>
    </div>
</div>

<script>
function validateStep14() {
    // Reference fields are optional, no validation needed
    return true;
}
</script>