<?php

ini_set('display_startup_errors',1);


require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../config/auth.php';

requireLogin();

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: index.php');
    exit;
}

$database = new Database();
$pdo = $database->connect();


$stmt = $pdo->prepare("
    SELECT *
    FROM contributions
    WHERE contribution_id = ?
    LIMIT 1
");



$stmt->execute([$id]);

$contribution = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$contribution) {
    header('Location: index.php?error=not_found');
    exit;
}


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

<i class="fas fa-edit mr-2 text-warning"></i>

Edit Contribution

</h3>

</div>


<div class="card-body">

<form
    action="update.php"
    method="POST"
    enctype="multipart/form-data"
>

<input
    type="hidden"
    name="contribution_id"
    value="<?= (int)$contribution['contribution_id']; ?>"
>


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
    value="<?= htmlspecialchars(
        $contribution['title']
    ); ?>"
    required
>

</div>


<div class="form-group">

<label>
Description
</label>

<textarea
    name="description"
    class="form-control"
    rows="5"
><?= htmlspecialchars(
    $contribution['description'] ?? ''
); ?></textarea>

</div>

</div>


<div class="col-md-4">

<?php if (!empty($contribution['contribution_image'])): ?>

<img
    src="../../uploads/contributions/<?= htmlspecialchars(
        $contribution['contribution_image']
    ); ?>"
    class="img-fluid rounded mb-3"
    alt="Contribution Image">

<?php endif; ?>


<div class="form-group">

<label>
Replace Image
</label>

<input
    type="file"
    name="contribution_image"
    class="form-control-file"
    accept="image/*"
>

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
    value="<?= htmlspecialchars(
        $contribution['target_amount']
    ); ?>"
    required
>

</div>

</div>


<div class="col-md-4">

<div class="form-group">

<label>
Minimum Amount
</label>

<input
    type="number"
    name="minimum_amount"
    class="form-control"
    min="0"
    step="0.01"
    value="<?= htmlspecialchars(
        $contribution['minimum_amount']
    ); ?>"
>

</div>

</div>


<div class="col-md-4">

<div class="form-group">

<label>
Allow Partial Payment
</label>

<select
    name="allow_partial_payment"
    class="form-control"
>

<option
    value="1"
    <?= $contribution['allow_partial_payment']
        ? 'selected'
        : ''; ?>
>
Yes
</option>

<option
    value="0"
    <?= !$contribution['allow_partial_payment']
        ? 'selected'
        : ''; ?>
>
No
</option>

</select>

</div>

</div>

</div>


<div class="row">


<div class="col-md-4">

<div class="form-group">

<label>
Start Date
</label>

<input
    type="date"
    name="start_date"
    class="form-control"
    value="<?= htmlspecialchars(
        $contribution['start_date'] ?? ''
    ); ?>"
>

</div>

</div>


<div class="col-md-4">

<div class="form-group">

<label>
End Date
</label>

<input
    type="date"
    name="end_date"
    class="form-control"
    value="<?= htmlspecialchars(
        $contribution['end_date'] ?? ''
    ); ?>"
>

</div>

</div>


<div class="col-md-4">

<div class="form-group">

<label>
Status
</label>

<select
    name="status"
    class="form-control"
>

<option
    value="active"
    <?= $contribution['status'] === 'active'
        ? 'selected'
        : ''; ?>
>
Active
</option>

<option
    value="closed"
    <?= $contribution['status'] === 'closed'
        ? 'selected'
        : ''; ?>
>
Closed
</option>

</select>

</div>

</div>

</div>


<div class="mt-4">

<button
    type="submit"
    class="btn btn-primary"
>

<i class="fas fa-save mr-1"></i>

Update Contribution

</button>


<a
    href="view.php?id=<?= $id; ?>"
    class="btn btn-info"
>

<i class="fas fa-eye mr-1"></i>

View

</a>


<a
    href="index.php"
    class="btn btn-secondary"
>

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