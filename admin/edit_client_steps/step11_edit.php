<div class="card">
    <div class="card-header">
        <h5 class="card-title">Step 11: RTS Portal Status</h5>
    </div>
    <div class="card-body">
        <form id="step11Form">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">RTS Portal Documents Updated? <span class="text-danger">*</span></label>
                    <select class="form-select" name="rts_portal_status" required>
                        <option value="no" <?php echo ($client_data['rts_portal_status'] ?? 'no') == 'no' ? 'selected' : ''; ?>>No</option>
                        <option value="yes" <?php echo ($client_data['rts_portal_status'] ?? 'no') == 'yes' ? 'selected' : ''; ?>>Yes</option>
                    </select>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function validateStep11() {
    return true;
}
</script>