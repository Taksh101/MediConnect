<?php require_once __DIR__ . '/../includes/header.php'; ?>
<div class="row justify-content-center">
  <div class="col-md-5">
    <h3 class="mb-3">Login</h3>
    <form action="../../controllers/AuthController.php" method="post">
      <?= csrf_field() ?>
      <div class="mb-3">
        <label>Email</label>
        <input name="email" class="form-control" required>
      </div>
      <div class="mb-3">
        <label>Password</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <button class="btn btn-primary">Login</button>
    </form>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
