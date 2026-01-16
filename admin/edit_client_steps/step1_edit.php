<div class="card">
    <div class="card-header">
        <h5 class="card-title">Step 1: Basic Details</h5>
    </div>
    <div class="card-body">
        <form id="step1Form" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Name of Customer <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="name" 
                       value="<?php echo htmlspecialchars($client_data['name'] ?? ''); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Consumer Number <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="consumer_number" 
                       value="<?php echo htmlspecialchars($client_data['consumer_number'] ?? ''); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Billing Unit</label>
                <input type="text" class="form-control" name="billing_unit" 
                       value="<?php echo htmlspecialchars($client_data['billing_unit'] ?? ''); ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Location URL</label>
                <input type="url" class="form-control" name="location_url" 
                       value="<?php echo htmlspecialchars($client_data['location'] ?? ''); ?>" 
                       placeholder="https://maps.google.com/...">
            </div>
        </form>
    </div>
</div>

<script>
function validateStep1() {
    const name = document.querySelector('input[name="name"]').value.trim();
    const consumerNumber = document.querySelector('input[name="consumer_number"]').value.trim();
    
    if(!name) {
        alert('Please enter customer name');
        return false;
    }
    
    if(!consumerNumber) {
        alert('Please enter consumer number');
        return false;
    }
    
    return true;
}
</script>