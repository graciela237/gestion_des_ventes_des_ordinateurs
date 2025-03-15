<?php
include '../DatabaseConnection/db_config.php';

$query = "SELECT * FROM stock_adjustments";
$result = $conn->query($query);
?>

<h2>Ajustements de Stock</h2>
<table border="1">
    <tr>
        <th>Adjustment ID</th>
        <th>Product ID</th>
        <th>Change Type</th>
        <th>Quantity</th>
        <th>Reason</th>
        <th>Adjustment Date</th>
    </tr>
    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['adjustment_id'] ?></td>
                <td><?= $row['product_id'] ?></td>
                <td><?= $row['change_type'] ?></td>
                <td><?= $row['quantity'] ?></td>
                <td><?= $row['reason'] ?></td>
                <td><?= $row['adjustment_date'] ?></td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="6">No data available</td></tr>
    <?php endif; ?>
</table>
