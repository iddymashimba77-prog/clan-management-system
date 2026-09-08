<?php

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

require_once '../../includes/dashboard/header.php';
require_once '../../includes/dashboard/sidebar.php';
require_once '../../includes/dashboard/navbar.php';

?>

<div class="content-wrapper">

<section class="content">

<div class="container-fluid clan-page-content">

<div class="card clan-page-card">

<div class="card-header">

<h3 class="card-title">

<i class="fas fa-plus-circle mr-2 text-primary"></i>

Create Contribution

</h3>

</div>


<div class="card-body">

<form action="store.php"
      method="POST"
      enctype="multipart/form-data">

<div class="row">

<div class="col-md-8">

<div class="form-group">

<label>
Contribution Title
<span class="text-danger">*</span>
</label>

<input
    type="text"
    name="title"
    class="form-control"
    maxlength="255"
    required
    placeholder="Example: Family Development Contribution">

</div>


<div class="form-group">

<label><?= __('description') ?></label>

<textarea
    name="description"
    class="form-control"
    rows="5"
    placeholder="Describe the purpose of this contribution..."></textarea>

</div>

</div>


<div class="col-md-4">

<div class="form-group">

<label>Contribution Image</label>

<input
    type="file"
    name="contribution_image"
    class="form-control-file"
    accept="image/*">

</div>

</div>

</div>


<hr>


<div class="row">

<div class="col-md-4">

<div class="form-group">

<label>
Target Amount
<span class="text-danger">*</span>
</label>

<input
    type="number"
    name="target_amount"
    class="form-control"
    min="0"
    step="0.01"
    required
    placeholder="0.00">

</div>

</div>


<div class="col-md-4">

<div class="form-group">

<label>Minimum Payment</label>

<input
    type="number"
    name="minimum_amount"
    class="form-control"
    min="0"
    step="0.01"
    value="0.00">

</div>

</div>


<div class="col-md-4">

<div class="form-group">

<label>Allow Partial Payment</label>

<select name="allow_partial_payment"
        class="form-control">

<option value="1">
Yes
</option>

<option value="0">
No
</option>

</select>

</div>

</div>

</div>


<div class="row">

<div class="col-md-4">

<div class="form-group">

<label>Start Date</label>

<input
    type="date"
    name="start_date"
    class="form-control">

</div>

</div>


<div class="col-md-4">

<div class="form-group">

<label>End Date</label>

<input
    type="date"
    name="end_date"
    class="form-control">

</div>

</div>


<div class="col-md-4">

<div class="form-group">

<label><?= __('status') ?></label>

<select name="status"
        class="form-control">

<option value="active">
Active
</option>

<option value="closed">
Closed
</option>

</select>

</div>

</div>

</div>


<div class="mt-4">

<button
    type="submit"
    class="btn btn-primary">

<i class="fas fa-save mr-1"></i>

Save Contribution

</button>


<a href="index.php"
   class="btn btn-secondary">

<i class="fas fa-arrow-left mr-1"></i>

Back

</a>

</div>


</form>

</div>

</div>

</div>

</section>

</div>

<?php

require_once '../../includes/dashboard/footer.php';

?>