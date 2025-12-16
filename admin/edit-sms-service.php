<?php
include 'header.php';
include '../config.php';
include '../db/db.php'; // Assuming this is the correct path for the database connection

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM sms_services WHERE id = ?");
    $stmt->execute([$id]);
    $service = $stmt->fetch();

    if (!$service) {
        // Handle case where service is not found
        echo "<script>alert('SMS Service not found!'); window.location.href='admin-sms-verification.php';</script>";
        exit();
    }
} else {
    echo "<script>window.location.href='admin-sms-verification.php';</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $service_name = $_POST['service_name'];
    $login_details = $_POST['login_details'];
    $sms_price = $_POST['sms_price'];
    $status = $_POST['status'];

    $stmt = $pdo->prepare("UPDATE sms_services SET service_name = ?, login_details = ?, sms_price = ?, status = ? WHERE id = ?");
    if ($stmt->execute([$service_name, $login_details, $sms_price, $status, $id])) {
        echo "<script>alert('SMS Service updated successfully!'); window.location.href='admin-sms-verification.php';</script>";
    } else {
        echo "<script>alert('Failed to update SMS Service.');</script>";
    }
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Edit SMS Service</h4>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="form-group">
                            <label for="service_name">Service Name:</label>
                            <input type="text" class="form-control" id="service_name" name="service_name" value="<?php echo htmlspecialchars($service['service_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="login_details">Login Details:</label>
                            <textarea class="form-control" id="login_details" name="login_details" rows="5" required><?php echo htmlspecialchars($service['login_details']); ?></textarea>
                            <small class="form-text text-muted">Enter login details for the SMS service. This will be shown to users upon purchase.</small>
                        </div>
                        <div class="form-group">
                            <label for="sms_price">SMS Price:</label>
                            <input type="number" step="0.01" class="form-control" id="sms_price" name="sms_price" value="<?php echo htmlspecialchars($service['sms_price']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="status">Status:</label>
                            <select class="form-control" id="status" name="status" required>
                                <option value="active" <?php echo ($service['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo ($service['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Update SMS Service</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>