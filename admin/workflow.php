<?php
// admin/workflow.php (replace your current file with this)
// Backup the original before pasting.
require_once "connect/auth_middleware.php";

if (session_status() === PHP_SESSION_NONE) session_start();

// include DB connection (must set $conn mysqli)
require_once __DIR__ . '/connect/db.php'; 

// helper: get incomplete clients (mysqli)
function getIncompleteClientsMysqli($conn, $step, $limit = 500, $include_client_id = 0) {
    $sql = '';
    switch ($step) {
        // 1 - Basic Details (name or consumer_number missing)
        case 1:
            $sql = "SELECT id, name FROM clients
                    WHERE (name IS NULL OR name = '')
                       OR (consumer_number IS NULL OR consumer_number = '')
                    ORDER BY id DESC
                    LIMIT ?";
            break;

        // 2 - Communication & Address
        case 2:
            $sql = "SELECT id, name FROM clients
                    WHERE (mobile IS NULL OR mobile = '')
                       OR (adhar IS NULL OR adhar = '' OR adhar = 0)
                    ORDER BY id DESC
                    LIMIT ?";
            break;

        // 3 - MAHADISCOM Email & Mobile Update
        case 3:
            $sql = "SELECT id, name FROM clients
                    WHERE (mahadiscom_email IS NULL OR mahadiscom_email = '')
                       OR (mahadiscom_email_password IS NULL OR mahadiscom_email_password = '')
                       OR (mahadiscom_mobile IS NULL OR mahadiscom_mobile = '')
                    ORDER BY id DESC
                    LIMIT ?";
            break;

        // 4 - MAHADISCOM Registration
        case 4:
            $sql = "SELECT id, name FROM clients
                    WHERE (mahadiscom_user_id IS NULL OR mahadiscom_user_id = '')
                       OR (mahadiscom_password IS NULL OR mahadiscom_password = '')
                    ORDER BY id DESC
                    LIMIT ?";
            break;

        // 5 - Name Change Require
        case 5:
            $sql = "SELECT id, name FROM clients
                    WHERE (name_change_require IS NULL OR name_change_require = '')
                    ORDER BY id DESC
                    LIMIT ?";
            break;

        // 6 - PM Suryaghar Registration
        case 6:
            // incomplete if registration not set or (registered yes but app id/date missing)
            $sql = "SELECT id, name FROM clients
                    WHERE (pm_suryaghar_registration IS NULL OR pm_suryaghar_registration = '')
                       OR (
                           pm_suryaghar_registration = 'yes'
                           AND (
                               pm_suryaghar_app_id IS NULL OR pm_suryaghar_app_id = ''
                               OR pm_registration_date IS NULL OR pm_registration_date = '0000-00-00'
                           )
                       )
                    ORDER BY id DESC
                    LIMIT ?";
            break;

        // 7 - MAHADISCOM Sanction Load
        case 7:
            $sql = "SELECT id, name FROM clients
                    WHERE (load_change_application_number IS NULL OR load_change_application_number = '')
                       OR (rooftop_solar_application_number IS NULL OR rooftop_solar_application_number = '')
                    ORDER BY id DESC
                    LIMIT ?";
            break;

        // 8 - Bank Loan
        case 8:
            // include if bank_loan_status not set OR if 'yes' but bank details missing
            $sql = "SELECT id, name FROM clients
                    WHERE (bank_loan_status IS NULL OR bank_loan_status = '')
                       OR (
                           bank_loan_status = 'yes'
                           AND (
                               bank_name IS NULL OR bank_name = ''
                               OR account_number IS NULL OR account_number = ''
                               OR loan_amount IS NULL OR loan_amount = 0
                           )
                       )
                    ORDER BY id DESC
                    LIMIT ?";
            break;

        // 9 - Fitting Photos (check documents table)
        case 9:
            $sql = "SELECT DISTINCT c.id, c.name
                    FROM clients c
                    LEFT JOIN client_documents sp ON sp.client_id = c.id AND sp.document_type = 'solar_panel_photo'
                    LEFT JOIN client_documents ip ON ip.client_id = c.id AND ip.document_type = 'inverter_photo'
                    LEFT JOIN client_documents gp ON gp.client_id = c.id AND gp.document_type = 'geotag_photo'
                    WHERE sp.id IS NULL OR ip.id IS NULL OR gp.id IS NULL
                    ORDER BY c.id DESC
                    LIMIT ?";
            break;

        // 10 - PM SuryaGhar Document Upload (inverter/system + required docs)
        case 10:
            $sql = "SELECT DISTINCT c.id, c.name
                    FROM clients c
                    LEFT JOIN client_documents da ON da.client_id = c.id AND da.document_type = 'aadhar'
                    LEFT JOIN client_documents dp ON dp.client_id = c.id AND dp.document_type = 'pan_card'
                    LEFT JOIN client_documents eb ON eb.client_id = c.id AND eb.document_type = 'electric_bill'
                    LEFT JOIN client_documents bp ON bp.client_id = c.id AND bp.document_type = 'bank_passbook'
                    WHERE (
                        c.inverter_company_name IS NULL OR c.inverter_company_name = ''
                        OR c.inverter_serial_number IS NULL OR c.inverter_serial_number = ''
                        OR c.dcr_certificate_number IS NULL OR c.dcr_certificate_number = ''
                        OR c.number_of_panels IS NULL OR c.number_of_panels = 0
                    )
                    OR da.id IS NULL OR dp.id IS NULL OR eb.id IS NULL OR bp.id IS NULL
                    ORDER BY c.id DESC
                    LIMIT ?";
            break;

        // 11 - RTS Portal Status
        case 11:
            $sql = "SELECT id, name FROM clients
                    WHERE rts_portal_status IS NULL OR rts_portal_status = '' OR rts_portal_status = 'no'
                    ORDER BY id DESC
                    LIMIT ?";
            break;

        // 12 - Meter Installation Photo
        case 12:
            $sql = "SELECT DISTINCT c.id, c.name
                    FROM clients c
                    LEFT JOIN client_documents mp ON mp.client_id = c.id AND mp.document_type = 'meter_photo'
                    WHERE (c.meter_number IS NULL OR c.meter_number = '')
                       OR mp.id IS NULL
                    ORDER BY c.id DESC
                    LIMIT ?";
            break;

        // 13 - PM Suryaghar Redeem Status
        case 13:
            $sql = "SELECT id, name FROM clients
                    WHERE
                      (
                        pm_redeem_status IS NULL
                        OR pm_redeem_status = ''
                        OR pm_redeem_status = 'no'
                      )
                      OR
                      (
                        pm_redeem_status = 'yes'
                        AND (
                          subsidy_amount IS NULL
                          OR subsidy_amount = 0
                          OR subsidy_redeem_date IS NULL
                          OR subsidy_redeem_date = '0000-00-00'
                        )
                      )
                    ORDER BY id DESC
                    LIMIT ?";
            break;

        // 14 - Reference
        case 14:
            $sql = "SELECT id, name FROM clients
                    WHERE reference_name IS NULL OR reference_name = ''
                       OR reference_contact IS NULL OR reference_contact = ''
                    ORDER BY id DESC
                    LIMIT ?";
            break;

        // default: return latest clients
        default:
            $sql = "SELECT id, name FROM clients ORDER BY id DESC LIMIT ?";
            break;
    }

    $results = [];

    // prepare statement
    if (!$stmt = $conn->prepare($sql)) {
        error_log('getIncompleteClientsMysqli prepare error (step ' . $step . '): ' . $conn->error . ' -- SQL: ' . $sql);
        return $results;
    }

    // bind limit param
    if (!$stmt->bind_param('i', $limit)) {
        error_log('getIncompleteClientsMysqli bind_param error: ' . $stmt->error);
        $stmt->close();
        return $results;
    }

    if (!$stmt->execute()) {
        error_log('getIncompleteClientsMysqli execute error: ' . $stmt->error);
        $stmt->close();
        return $results;
    }

    $res = $stmt->get_result();
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $results[$row['id']] = $row['name'];
        }
    }
    $stmt->close();

    // If include_client_id requested and not present, fetch that client and prepend it
    if ($include_client_id && !isset($results[$include_client_id])) {
        if ($qr = $conn->prepare("SELECT id, name FROM clients WHERE id = ? LIMIT 1")) {
            $qr->bind_param('i', $include_client_id);
            $qr->execute();
            $r2 = $qr->get_result();
            if ($r2 && $row2 = $r2->fetch_assoc()) {
                // prepend selected client at top
                $results = array($row2['id'] => $row2['name']) + $results;
            }
            $qr->close();
        } else {
            error_log('getIncompleteClientsMysqli include_client prepare error: ' . $conn->error);
        }
    }

    return $results;
}

// determine step & selected client
$current_step = isset($_GET['step']) ? intval($_GET['step']) : 1;
if ($current_step < 1) $current_step = 1;
if ($current_step > 14) $current_step = 14;
$selected_client_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;

// fetch incomplete clients for current step (includes selected client if provided)
$clientsArr = getIncompleteClientsMysqli($conn, $current_step, 500, $selected_client_id);

// fetch basic selected client data (for badge)
$client_data = null;
if ($selected_client_id > 0) {
    $q = $conn->prepare("SELECT id, name FROM clients WHERE id = ? LIMIT 1");
    $q->bind_param('i', $selected_client_id);
    $q->execute();
    $r = $q->get_result();
    $client_data = $r->fetch_assoc();
    $q->close();
}
$auth->requirePermission('customer_management', 'create');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workflow - Solar Quick</title>
    <?php require('include/head.php'); ?>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .step-nav { background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
        .step-item { padding: 10px; margin: 5px 0; cursor: pointer; border-radius: 5px; transition: all 0.3s ease; }
        .step-item.active { background: #007bff; color: white; }
        .step-item.completed { background: #28a745; color: white; }
        .step-item:hover:not(.active) { background: #e9ecef; }
        .client-selector { margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 10px; }
        .step-form-container { background: white; border-radius: 10px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .form-navigation { margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6; }
        .progress { height: 10px; margin-bottom: 20px; }
        .user-info { background: #e9ecef; padding: 10px; border-radius: 5px; margin-bottom: 15px; }
    </style>
</head>
<body>

  <?php include "include/sidebar.php"; ?>
  <div id="main-content"> 
    <?php require('include/navbar.php') ?>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Client Workflow</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active">Workflow</li>
                </ol>
            </nav>
        </div>

        <div class="user-info">
            <small>
                Logged in as: <strong></strong> |
                Role: <strong></strong>
            </small>
        </div>

        <?php
            // flash messages
            if (!empty($_SESSION['workflow_errors'])) {
                echo '<div class="alert alert-danger">';
                foreach ($_SESSION['workflow_errors'] as $err) {
                    echo htmlspecialchars($err) . "<br>";
                }
                echo '</div>';
                unset($_SESSION['workflow_errors']);
            }
            if (!empty($_SESSION['workflow_success'])) {
                echo '<div class="alert alert-success">';
                echo htmlspecialchars($_SESSION['workflow_success']);
                echo '</div>';
                unset($_SESSION['workflow_success']);
            }
        ?>

        <div class="container-fluid">
            <div class="row mt-3">
                <div class="col-12">
                    <div class="progress">
                        <div class="progress-bar" role="progressbar">
                            Step
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">

                <div class="col-md-3">
                    <div class="step-nav">
                        <h5>Workflow Steps</h5>
                        <div class="step-item">1. Basic Details</div>
                        <div class="step-item">2. Communication & Address</div>
                        <div class="step-item">3. MAHADISCOM Email & Mobile Update</div>
                        <div class="step-item">4. MAHADISCOM Registration</div>
                        <div class="step-item">5. Name Change Require</div>
                        <div class="step-item">6. PM Suryaghar Portal Registration</div>
                        <div class="step-item">7. MAHADISCOM Sanction Load</div>
                        <div class="step-item">8. Bank Loan</div>
                        <div class="step-item">9. Fitting Photos</div>
                        <div class="step-item">10. PM SuryaGhar Document Upload</div>
                        <div class="step-item">11. RTS Portal Status</div>
                        <div class="step-item">12. Meter Installation Photo</div>
                        <div class="step-item">13. PM Suryaghar Redeem Status</div>
                        <div class="step-item">14. Reference</div>
                    </div>
                </div>

                <div class="col-md-9">
                    <?php $clientSelectorHiddenClass = ($current_step <= 1) ? 'd-none' : ''; ?>
                    <div class="client-selector <?= $clientSelectorHiddenClass ?>">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Select Client:</label>
                                <select class="form-select" id="clientSelect" name="client_id">
                                    <option value="">-- Select Client --</option>
                                    <?php foreach ($clientsArr as $id => $name):
                                        $label = htmlspecialchars($name . " (ID: " . $id . ")");
                                        $sel = ($id == $selected_client_id) ? 'selected' : '';
                                    ?>
                                        <option value="<?= $id ?>" <?= $sel ?>><?= $label ?></option>
                                    <?php endforeach; ?>
                                    <?php if (function_exists('hasPermission') && hasPermission('workflow', 'create')): ?>
                                        <option value="new">+ Create New Client</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <div class="mt-4">
                                    <?php if($selected_client_id > 0): ?>
                                        <span class="badge bg-info">Editing: <?php echo htmlspecialchars($client_data['name'] ?? ''); ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">No client selected</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="step-form-container" style="position:sticky;z-index:1050;top:5rem">
                        <div id="step-content">
                            <div class='alert alert-warning'>Step form is under development.</div>
                        </div>

                        <div class="form-navigation d-none">
                            <div class="row">
                                <div class="col-md-6">
                                    <button type="button" class="btn btn-secondary">← Previous Step</button>
                                </div>
                                <div class="col-md-6 text-end">
                                    <button type="button" class="btn btn-primary">Save & Continue →</button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </main>
  </div>

<!-- Combined JS: loader + client logic -->
<script>
(function(){
  const TOTAL_STEPS = 14;

  // DOM references
  const stepNav = document.querySelector('.step-nav');
  const stepItems = Array.from(document.querySelectorAll('.step-nav .step-item'));
  const stepFormContainer = document.querySelector('.step-form-container');
  const formNavigation = stepFormContainer.querySelector('.form-navigation');
  const progressBar = document.querySelector('.progress .progress-bar');
  const clientSelect = document.getElementById('clientSelect');

  // step content wrapper
  let stepContent = stepFormContainer.querySelector('#step-content');
  if (!stepContent) {
    stepContent = document.createElement('div');
    stepContent.id = 'step-content';
    stepFormContainer.insertBefore(stepContent, formNavigation);
  }

  // helpers
  function stepNumberFromItem(item) {
    if (!item) return null;
    const txt = item.textContent.trim();
    const m = txt.match(/^(\d+)\s*[\.\-:]/);
    if (m) return parseInt(m[1], 10);
    const first = txt.split(/\s+/)[0];
    const n = parseInt(first, 10);
    return Number.isFinite(n) ? n : null;
  }

  function setActive(step) {
    stepItems.forEach(it => {
      const n = stepNumberFromItem(it);
      it.classList.toggle('active', n === step);
      if (n !== step) it.classList.remove('completed');
    });
  }

  function markCompletedUpTo(step) {
    stepItems.forEach(it => {
      const n = stepNumberFromItem(it);
      if (n && n < step) it.classList.add('completed'); else if (n && n >= step) it.classList.remove('completed');
    });
  }

  function updateProgress(step) {
    const pct = step ? Math.round((step / TOTAL_STEPS) * 100) : 0;
    if (progressBar) {
      progressBar.style.width = pct + '%';
      progressBar.setAttribute('aria-valuenow', String(pct));
      progressBar.textContent = 'Step ' + step + ' of ' + TOTAL_STEPS + ' (' + pct + '%)';
    }
  }

  function readState() {
    const state = (history.state && typeof history.state === 'object') ? history.state : {};
    const params = new URLSearchParams(location.search);
    const s = state.step || (params.get('step') ? parseInt(params.get('step'), 10) : null) || 1;
    const client = (typeof state.client_id !== 'undefined') ? state.client_id : (params.get('client_id') ? parseInt(params.get('client_id'), 10) : 0);
    return { step: parseInt(s, 10), client_id: client ? parseInt(client,10) : 0 };
  }

  function pushState(step, client_id, replace=false) {
    const newUrl = location.pathname + '?step=' + step + (client_id ? '&client_id=' + client_id : '');
    const stateObj = { step: step, client_id: client_id };
    if (replace && history.replaceState) history.replaceState(stateObj, '', newUrl);
    else if (history.pushState) history.pushState(stateObj, '', newUrl);
  }

  function updateClientUI(clientId) {
    if (!clientSelect) return;
    const opt = clientSelect.options[clientSelect.selectedIndex];
    const text = opt ? opt.text : '';
    let badge = document.querySelector('.client-selected-badge');
    if (!badge) {
      const container = clientSelect.closest('.client-selector') || clientSelect.parentNode;
      const div = container.querySelector('.mt-4') || document.createElement('div');
      if (!container.querySelector('.mt-4')) {
        div.className = 'mt-4';
        container.appendChild(div);
      }
      div.innerHTML = '<span class="client-selected-badge badge"></span>';
      badge = document.querySelector('.client-selected-badge');
    }
    if (clientId) {
      badge.textContent = 'Client: ' + text;
      badge.classList.remove('bg-warning'); badge.classList.add('bg-info');
    } else {
      badge.textContent = 'No client selected';
      badge.classList.remove('bg-info'); badge.classList.add('bg-warning');
    }

    const hid = document.querySelector('input#client_id, input[name="client_id"]');
    if (hid) hid.value = clientId || '';
  }

  async function refreshClientList(step) {
    if (!clientSelect || step <= 1) return;
    
    try {
      const response = await fetch(`api/workflow_api.php?action=get_incomplete_clients&step=${step}`);
      const data = await response.json();
      
      if (data.success && data.data) {
        const currentValue = clientSelect.value;
        
        // Clear existing options except first one
        while (clientSelect.options.length > 1) {
          clientSelect.removeChild(clientSelect.options[1]);
        }
        
        // Add new options
        data.data.forEach(client => {
          const option = document.createElement('option');
          option.value = client.id;
          option.textContent = `${client.name} (ID: ${client.id})`;
          if (client.id == currentValue) {
            option.selected = true;
          }
          clientSelect.appendChild(option);
        });
        
        // Add "Create New Client" option if it exists
        const createOption = document.createElement('option');
        createOption.value = 'new';
        createOption.textContent = '+ Create New Client';
        clientSelect.appendChild(createOption);
      }
    } catch (error) {
      console.error('Failed to refresh client list:', error);
    }
  }

  async function loadStep(step, client_id = 0, push=true) {
    if (!step || step < 1 || step > TOTAL_STEPS) return;
    setActive(step);
    markCompletedUpTo(step);
    updateProgress(step);

    stepContent.innerHTML = '<div class="py-4"><span style="display:inline-block;width:18px;height:18px;border:3px solid rgba(0,0,0,.1);border-left-color:#000;border-radius:50%;animation:spin .8s linear infinite;vertical-align:middle;margin-right:10px"></span> Loading step ' + step + '...</div>';
    if (!document.getElementById('workflow-spinner-style')) {
      const style = document.createElement('style');
      style.id = 'workflow-spinner-style';
      style.textContent = '@keyframes spin { to { transform: rotate(360deg); } }';
      document.head.appendChild(style);
    }

    try {
      const url = 'workflow_steps/step' + encodeURIComponent(step) + '' + (client_id ? ('?client_id=' + encodeURIComponent(client_id)) : '');
      const res = await fetch(url, { method: 'GET', credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' }});
      if (!res.ok) throw new Error('HTTP ' + res.status);
      const html = await res.text();

      let injected = html;
      const bodyMatch = html.match(/<body[^>]*>([\s\S]*)<\/body>/i);
      if (bodyMatch) injected = bodyMatch[1];

      stepContent.innerHTML = injected || '<div class="alert alert-info">This step has no content yet.</div>';


      const firstInput = stepContent.querySelector('input, select, textarea, button');
      if (firstInput) firstInput.focus();
        
// --- call step-specific initializer if it exists ---
// 1) try a specific function name (like initStep10)
// 2) fallback to a generic pattern: window['initStep'+step]
try {
  // if you want to pass context, compute client_id from URL/state
  const params = new URLSearchParams(window.location.search);
  const qClientId = params.get('client_id');
  const clientId = (qClientId !== null && qClientId !== '') ? parseInt(qClientId, 10) : (history.state && history.state.client_id ? history.state.client_id : 0);

  // direct init function (explicit)
  const explicit = window['initStep' + step];
  if (typeof explicit === 'function') {
    // call with (step, clientId, stepContent) so initializer has context
    explicit(step, clientId, stepContent);
  } else {
    // generic "initAllSteps" or per-step auto-dispatch
    const generic = window['initStep' + step];
    if (typeof generic === 'function') {
      generic(step, clientId, stepContent);
    }
  }
} catch (e) {
  // don't break the UI if init fails
  console.error('step init error for step', step, e);
}

      updateClientUI(client_id);
      
      // Refresh client list for current step
      await refreshClientList(step);

      if (push) pushState(step, client_id);
    } catch (err) {
      console.error('Failed to load step:', err);
      stepContent.innerHTML = '<div class="alert alert-danger">Failed to load step ' + step + '. Please try again later.</div>';
    }
  }

  if (stepNav) {
    stepNav.addEventListener('click', function(e) {
      const el = e.target.closest('.step-item');
      if (!el) return;
      const n = stepNumberFromItem(el);
      if (!n) return;
      const state = readState();
      loadStep(n, state.client_id, true);
    });
  }

  window.addEventListener('popstate', function(e) {
    const state = (e.state && typeof e.state === 'object') ? e.state : null;
    if (state && state.step) {
      loadStep(state.step, state.client_id || 0, false);
    } else {
      const s = readState();
      loadStep(s.step, s.client_id || 0, false);
    }
  });

  if (clientSelect) {
    clientSelect.addEventListener('change', function() {
      const clientId = this.value && this.value !== '' ? parseInt(this.value, 10) : 0;
      const s = readState();
      loadStep(s.step || 1, clientId, true);
    });
  }

  (function init() {
    const s = readState();
    pushState(s.step || 1, s.client_id || 0, true);
    loadStep(s.step || 1, s.client_id || 0, false);
  })();

})();

// Global function to refresh client list after step completion
window.refreshWorkflowClientList = async function() {
  const params = new URLSearchParams(window.location.search);
  const currentStep = parseInt(params.get('step')) || 1;
  
  if (currentStep <= 1) return;
  
  const clientSelect = document.getElementById('clientSelect');
  if (!clientSelect) return;
  
  try {
    const response = await fetch(`api/workflow_api.php?action=get_incomplete_clients&step=${currentStep}`);
    const data = await response.json();
    
    if (data.success && data.data) {
      const currentValue = clientSelect.value;
      
      // Clear existing options except first one
      while (clientSelect.options.length > 1) {
        clientSelect.removeChild(clientSelect.options[1]);
      }
      
      // Add new options
      data.data.forEach(client => {
        const option = document.createElement('option');
        option.value = client.id;
        option.textContent = `${client.name} (ID: ${client.id})`;
        if (client.id == currentValue) {
          option.selected = true;
        }
        clientSelect.appendChild(option);
      });
      
      // Add "Create New Client" option
      const createOption = document.createElement('option');
      createOption.value = 'new';
      createOption.textContent = '+ Create New Client';
      clientSelect.appendChild(createOption);
    }
  } catch (error) {
    console.error('Failed to refresh client list:', error);
  }
};

// Auto-refresh client list after form submissions
document.addEventListener('DOMContentLoaded', function() {
  // Listen for successful form submissions and refresh client list
  document.addEventListener('submit', function(e) {
    const form = e.target;
    if (form.id && form.id.includes('step')) {
      setTimeout(() => {
        if (typeof window.refreshWorkflowClientList === 'function') {
          window.refreshWorkflowClientList();
        }
      }, 1500);
    }
  });
});
</script>

<!-- Toggle client selector visibility when step changes -->
<script>
(function(){
  const clientSelector = document.querySelector('.client-selector');
  if (!clientSelector) return;
  function getStep() {
    if (history.state && history.state.step) return history.state.step;
    const p = new URLSearchParams(location.search);
    const s = parseInt(p.get('step'), 10);
    return (Number.isInteger(s) && s > 0) ? s : 1;
  }
  function toggle(step) { if (step > 1) clientSelector.classList.remove('d-none'); else clientSelector.classList.add('d-none'); }
  document.addEventListener('DOMContentLoaded', function(){ toggle(getStep()); });
  window.addEventListener('popstate', function(e){ const step = e.state && e.state.step ? e.state.step : getStep(); toggle(step); });
  (function() { const _push = history.pushState; history.pushState = function(state, title, url){ const r = _push.apply(history, arguments); toggle(state && state.step ? state.step : getStep()); return r; }; })();
})();
</script>
<script>
// Add this inside your main JS (so it's available globally)
window.toggleNameAppField = function(value) {
  try {
    const field = document.getElementById('nameChangeApplicationField');
    if (!field) return;
    field.style.display = (String(value) === 'yes') ? 'block' : 'none';
  } catch (e) {
    console.error('toggleNameAppField error', e);
  }
};
</script>
<script>
// put this once in workflow.php (global)
window.togglePmSuryagharFields = function(value) {
  try {
    const show = String(value) === 'yes';
    const f1 = document.getElementById('pmSuryagharAppIdField');
    const f2 = document.getElementById('pmSuryagharDateField');
    if (f1) f1.style.display = show ? 'block' : 'none';
    if (f2) f2.style.display = show ? 'block' : 'none';
  } catch (err) { console.error(err); }
};
</script>
<script>
window.toggleBankLoanFields = function(value) {
  try {
    const show = String(value) === 'yes';
    const container = document.getElementById('bankLoanFields');
    if (!container) return;
    container.style.display = show ? 'block' : 'none';

    // toggle required attributes for critical fields
    const requiredSelector = ['input[name="bank_name"]', 'input[name="account_number"]', 'input[name="ifsc_code"]', 'input[name="loan_amount"]'];
    requiredSelector.forEach(sel => {
      const el = container.querySelector(sel);
      if (!el) return;
      if (show) el.setAttribute('required', 'required');
      else { el.removeAttribute('required'); el.value = ''; }
    });

    // if hiding, clear all fields
    if (!show) {
      const all = container.querySelectorAll('input');
      all.forEach(i => i.value = '');
    }
  } catch (e) { console.error(e); }
};
</script>
<script>
    window.initStep10 = function(step, clientId, stepContent) {
  // stepContent is the DOM node that contains the injected fragment
  // Example: attach file size guards, and auto-generate panel fields if number present.
  try {
    // add file size validation
    stepContent.querySelectorAll('input[type="file"]').forEach(input => {
      input.addEventListener('change', function() {
        const f = this.files && this.files[0];
        if (f && f.size > 5 * 1024 * 1024) {
          alert('File exceeds 5MB. Please choose a smaller file.');
          this.value = '';
        }
      });
    });

    // attach generate button handler if not inline
    // NOTE: If your fragment uses onclick="window.generatePanelFields()", you don't need to do this.
    const genBtn = stepContent.querySelector('[onclick*="generatePanelFields"], #generatePanelBtn');
    if (genBtn && typeof window.generatePanelFields !== 'function') {
      // define global generatePanelFields only once
      window.generatePanelFields = function() {
        const numEl = document.getElementById('numberOfPanels');
        const container = document.getElementById('panelSerialNumbers');
        if (!numEl || !container) return;
        const numberOfPanels = parseInt(numEl.value, 10) || 0;
        if (numberOfPanels <= 0 || numberOfPanels > 50) {
          alert('Please enter a valid number of panels (1-50)');
          return;
        }
        let html = '<div class="row"><div class="col-12"><h6>Panel Serial Numbers <span class="text-danger">*</span></h6></div></div>';
        for (let i = 1; i <= numberOfPanels; i++) {
          html += `<div class="row mb-2"><div class="col-md-6"><label class="form-label">Panel ${i} Serial Number</label><input type="text" class="form-control" name="panel_serial_${i}" placeholder="Enter serial number for panel ${i}" required></div></div>`;
        }
        container.innerHTML = html;
        // prefill if hidden input exists
        const existingCsvEl = stepContent.querySelector('#existing_panel_serials, input[name="existing_panel_serials"]');
        if (existingCsvEl && existingCsvEl.value.trim() !== '') {
          const parts = existingCsvEl.value.split(',').map(s => s.trim());
          for (let i = 0; i < parts.length && i < numberOfPanels; i++) {
            const f = container.querySelector('[name="panel_serial_' + (i+1) + '"]');
            if (f) f.value = parts[i];
          }
        }
      };
    }

    // if number_of_panels has value on load, generate fields
    const numEl = stepContent.querySelector('#numberOfPanels');
    if (numEl && parseInt(numEl.value, 10) > 0) {
      setTimeout(() => {
        if (typeof window.generatePanelFields === 'function') window.generatePanelFields();
      }, 10);
    }
  } catch (err) {
    console.error('initStep10 error', err);
  }
};

</script>
<script>
// Put this in workflow.php once (global scope)

window.generatePanelFields = function() {
  try {
    const numEl = document.getElementById('numberOfPanels');
    const container = document.getElementById('panelSerialNumbers');
    if (!numEl || !container) return;

    const numberOfPanels = parseInt(numEl.value, 10) || 0;
    if (numberOfPanels <= 0 || numberOfPanels > 50) { // keep safe upper limit
      alert('Please enter a valid number of panels (1-50)');
      return;
    }

    let html = '<div class="row"><div class="col-12"><h6>Panel Serial Numbers <span class="text-danger">*</span></h6></div></div>';
    for (let i = 1; i <= numberOfPanels; i++) {
      html += `
        <div class="row mb-2">
          <div class="col-md-6">
            <label class="form-label">Panel ${i} Serial Number</label>
            <input type="text" class="form-control" name="panel_serial_${i}" placeholder="Enter serial number for panel ${i}" required>
          </div>
        </div>
      `;
    }
    container.innerHTML = html;

    // Try prefill from hidden field
    const existingCsvEl = document.getElementById('existing_panel_serials');
    if (existingCsvEl && existingCsvEl.value.trim() !== '') {
      const parts = existingCsvEl.value.split(',').map(s => s.trim());
      for (let i = 0; i < parts.length && i < numberOfPanels; i++) {
        const f = document.querySelector('[name="panel_serial_' + (i+1) + '"]');
        if (f) f.value = parts[i];
      }
    }
  } catch (e) {
    console.error('generatePanelFields error', e);
  }
};

</script>
<script>
function toggleSubsidyFields() {
    const status = document.getElementById('pmRedeemStatus')?.value || "no";

    const amountField = document.getElementById('subsidyAmountField');
    const dateField   = document.getElementById('subsidyDateField');

    const amountInput = document.querySelector('input[name="subsidy_amount"]');
    const dateInput   = document.querySelector('input[name="subsidy_redeem_date"]');

    if (!amountField || !dateField || !amountInput || !dateInput) return;

    if (status === 'yes') {
        // Show fields
        amountField.style.display = 'block';
        dateField.style.display = 'block';

        // Make required
        amountInput.required = true;
        dateInput.required = true;

    } else {
        // Hide fields
        amountField.style.display = 'none';
        dateField.style.display = 'none';

        // Remove required
        amountInput.required = false;
        dateInput.required = false;

        // Clear values
        amountInput.value = '';
        dateInput.value = '';
    }
}

// Initialize state on page load
document.addEventListener('DOMContentLoaded', function () {
    if (typeof toggleSubsidyFields === 'function') {
        toggleSubsidyFields(); // auto-adjust UI if editing saved data
    }
});
</script>


</body>
</html>
