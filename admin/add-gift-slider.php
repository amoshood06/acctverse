<?php
include 'header.php';
require_once "../flash.php";

$flash = get_flash();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Add New Gift Slider</h4>
                </div>
                <div class="card-body">
                    <?php if ($flash): ?>
                        <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show" role="alert">
                            <?php echo $flash['message']; ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <form action="process-add-gift-slider.php" method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="image">Slider Image (Recommended: 1920x600 pixels)</label>
                            <input type="file" class="form-control-file" id="image" name="image" required>
                        </div>
                        <div class="form-group">
                            <label for="title">Title (Optional)</label>
                            <input type="text" class="form-control" id="title" name="title" placeholder="Enter slider title">
                        </div>
                        <div class="form-group">
                            <label for="description">Description (Optional)</label>
                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Enter slider description"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="link">Link (Optional)</label>
                            <input type="url" class="form-control" id="link" name="link" placeholder="e.g., https://example.com/gifts">
                        </div>
                        <div class="form-group">
                            <label for="order_num">Order (Optional, default 0)</label>
                            <input type="number" class="form-control" id="order_num" name="order_num" value="0">
                        </div>
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select class="form-control" id="status" name="status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Slider</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

