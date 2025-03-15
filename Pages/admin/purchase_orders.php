<?php
include '../DatabaseConnection/db_config.php';

$query = "SELECT * FROM purchase_orders";
$result = $conn->query($query);
?>

<h2>Bons de Commande</h2>
<table border="1">
    <tr>
        <th>PO ID</th>
        <th>Supplier ID</th>
        <th>Total Amount</th>
        <th>Status</th>
        <th>Order Date</th>
    </tr>
    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['po_id'] ?></td>
                <td><?= $row['supplier_id'] ?></td>
                <td><?= $row['total_amount'] ?></td>
                <td><?= $row['status'] ?></td>
                <td><?= $row['order_date'] ?></td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="5">No data available</td></tr>
    <?php endif; ?>
</table>
