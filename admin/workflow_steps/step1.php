<h4>Step 1: Basic Details</h4>
<form id="step1Form" action="workflow_steps/save_step1" method="POST" novalidate enctype="multipart/form-data">
        <input type="hidden" name="action" value="save_step_data">
        <input type="hidden" name="step" value="1">
        <!-- inside step1.php form, keep existing inputs above -->
        <input type="hidden" name="client_id" id="client_id" value="<?php echo intval($client_data['id'] ?? 0); ?>">
    <div class="mb-3">
        <label class="form-label">Name of Customer *</label>
        <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($client_data['name'] ?? ''); ?>" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Consumer Number *</label>
        <input type="text" class="form-control" name="consumer_number" value="<?php echo htmlspecialchars($client_data['consumer_number'] ?? ''); ?>" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Billing Unit</label>
        <input type="text" class="form-control" name="billing_unit" value="<?php echo htmlspecialchars($client_data['billing_unit'] ?? ''); ?>">
    </div>
    <div class="mb-3">
        <label class="form-label">Location URL</label>
        <input type="url" class="form-control" name="location_url" value="<?php echo htmlspecialchars($client_data['location'] ?? ''); ?>" placeholder="https://maps.google.com/...">
    </div>
    <div class="d-flex justify-content-end mt-3">
          <input type="submit" name="submit " class="btn btn-primary" value="Save & Continue">
        </div>

</form>