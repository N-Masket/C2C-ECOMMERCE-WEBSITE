<?php
// orders.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// DB connection (same as before)
include '../Database/dbConnection.php';

// Fetch orders + items + user name + product names
$sql = "
SELECT o.order_id, o.user_id, u.full_name AS customer_name, o.order_date, o.status, o.total, o.shipping_fee,
       oi.order_item_id, oi.product_id, p.product_name, oi.quantity, oi.price_at_purchase
FROM orders o
JOIN users u ON o.user_id = u.user_id
JOIN order_items oi ON o.order_id = oi.order_id
JOIN products p ON oi.product_id = p.product_id
ORDER BY o.order_date DESC, o.order_id, oi.order_item_id
";

$result = $conn->query($sql);

$orders = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $orderId = $row['order_id'];
        if (!isset($orders[$orderId])) {
            $orders[$orderId] = [
                'customer_name' => $row['customer_name'],
                'order_date' => $row['order_date'],
                'status' => $row['status'],
                'total' => $row['total'],
                'shipping_fee' => $row['shipping_fee'],
                'items' => []
            ];
        }
        $orders[$orderId]['items'][] = [
            'product_name' => $row['product_name'],
            'quantity' => $row['quantity'],
            'price_at_purchase' => $row['price_at_purchase']
        ];
    }
} else {
    $orders = [];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Orders Page with Actions</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #1A355B;
            padding: 2rem;
            color: white;
        }

        .table-responsive {
            background: white;
            padding: 20px;
            border-radius: 10px;
            color: black;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        }

        .filter-bar {
            margin-bottom: 20px;
            color: black;
        }

        .items-table th,
        .items-table td {
            font-size: 0.9rem;
            vertical-align: middle;
        }

        .badge-new {
            background-color: #28a745;
            font-weight: bold;
        }

        .back-btn {
            background-color: #f2f2f2;
            border: none;
            color: #333;
            font-size: 16px;
            padding: 10px 15px;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .back-btn:hover {
            background-color: #ddd;
        }
    </style>
</head>

<body>
    <!-- Back Arrow Button -->
    <button onclick="history.back()" class="back-btn">← Back</button>

    <div class="container-fluid">
        <h2 class="mb-4 text-white"><i class="bi bi-cart-check-fill text-primary"></i> Orders</h2>

        <div class="row filter-bar">
            <div class="col-md-4">
                <label for="statusFilter" class="form-label">Filter by Status:</label>
                <select id="statusFilter" class="form-select">
                    <option value="">All</option>
                    <option value="Paid">Paid</option>
                    <option value="Unpaid">Unpaid</option>
                    <option value="Delivered">Delivered</option>
                    <option value="Processing">Processing</option>
                    <option value="Cancelled">Cancelled</option>
                    <option value="Refunded">Refunded</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="dateRange" class="form-label">Filter by Date Range:</label>
                <input type="text" id="dateRange" class="form-control" />
            </div>
        </div>

        <div class="table-responsive">
            <table id="ordersTable" class="table table-striped table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Items</th>
                        <th>Total (incl. Shipping)</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (empty($orders)) {
                        echo "<tr><td colspan='7'>No orders found</td></tr>";
                    } else {
                        $now = new DateTime();
                        foreach ($orders as $orderId => $order) {
                            // Check if order is new (placed within 24 hours)
                            $orderDate = new DateTime($order['order_date']);
                            $interval = $now->diff($orderDate);
                            $isNew = ($interval->days == 0 && $interval->h < 24);

                            // Items table HTML
                            $itemsHtml = "<table class='table table-sm table-bordered items-table'>";
                            $itemsHtml .= "<thead><tr><th>Product</th><th>Qty</th><th>Price</th></tr></thead><tbody>";
                            foreach ($order['items'] as $item) {
                                $itemsHtml .= "<tr>";
                                $itemsHtml .= "<td>" . htmlspecialchars($item['product_name']) . "</td>";
                                $itemsHtml .= "<td>" . (int)$item['quantity'] . "</td>";
                                $itemsHtml .= "<td>R" . number_format($item['price_at_purchase'], 2) . "</td>";
                                $itemsHtml .= "</tr>";
                            }
                            $itemsHtml .= "</tbody></table>";

                            $totalWithShipping = $order['total'] + $order['shipping_fee'];

                            echo "<tr data-order-id='{$orderId}' data-customer='" . htmlspecialchars($order['customer_name']) . "' data-date='{$order['order_date']}' data-status='{$order['status']}' data-total='{$totalWithShipping}'>";
                            echo "<td>" . htmlspecialchars($orderId);
                            if ($isNew) {
                                echo " <span class='badge badge-new'>New</span>";
                            }
                            echo "</td>";
                            echo "<td>" . htmlspecialchars($order['customer_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($order['order_date']) . "</td>";
                            echo "<td>" . $itemsHtml . "</td>";
                            echo "<td>R" . number_format($totalWithShipping, 2) . "</td>";
                            echo "<td>" . htmlspecialchars($order['status']) . "</td>";
                            echo "<td>
                                <button class='btn btn-sm btn-primary btn-view'>View</button>
                                <button class='btn btn-sm btn-danger btn-cancel' " . ($order['status'] == 'Cancelled' || $order['status'] == 'Refunded' ? "disabled" : "") . ">Cancel</button>
                                <button class='btn btn-sm btn-success btn-refund' " . ($order['status'] == 'Refunded' || $order['status'] == 'Cancelled' ? "disabled" : "") . ">Refund</button>
                                </td>";
                            echo "</tr>";
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Order Detail Modal -->
    <div class="modal fade" id="orderDetailModal" tabindex="-1" aria-labelledby="orderDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Order Details - <span id="modalOrderId"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Customer:</strong> <span id="modalCustomer"></span></p>
                    <p><strong>Date:</strong> <span id="modalDate"></span></p>
                    <p><strong>Status:</strong> <span id="modalStatus"></span></p>
                    <p><strong>Total (incl. shipping):</strong> R<span id="modalTotal"></span></p>
                    <hr />
                    <h6>Items:</h6>
                    <table class="table table-bordered" id="modalItemsTable">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Dynamically inserted -->
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirm Modal -->
    <div class="modal fade" id="confirmActionModal" tabindex="-1" aria-labelledby="confirmActionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="confirmActionModalLabel" class="modal-title"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="confirmActionMessage"></div>
                <div class="modal-footer">
                    <button type="button" id="confirmNo" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                    <button type="button" id="confirmYes" class="btn btn-primary">Yes</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTable with sorting, page size, and non-sortable columns
            const table = $('#orders').DataTable({
                order: [
                    [2, 'desc']
                ], 
                pageLength: 10, // Default rows per page
                lengthMenu: [5, 10, 25, 50], // Options for rows per page
                columnDefs: [{
                        targets: 3,
                        orderable: false,
                        searchable: false
                    }, // Disable sort/search on actions
                    {
                        targets: 6,
                        orderable: false,
                        searchable: false
                    } 
                ]
            });

            // Initialize date range picker for filtering by order date
            $('#dateRange').daterangepicker({
                autoUpdateInput: false,
                locale: {
                    cancelLabel: 'Clear'
                }
            });

            // On date selection: filter table based on selected date range
            $('#dateRange').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' to ' + picker.endDate.format('YYYY-MM-DD'));

                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    let date = data[2]; // Use the 3rd column (order date)
                    return moment(date).isBetween(picker.startDate, picker.endDate, null, '[]'); // Inclusive range
                });

                table.draw(); // Redraw the table
            });

            // Clear date filter on cancel
            $('#dateRange').on('cancel.daterangepicker', function() {
                $(this).val('');
                $.fn.dataTable.ext.search.pop(); // Remove last custom filter
                table.draw();
            });

            // Filter by order status (dropdown)
            $('#statusFilter').on('change', function() {
                let val = $(this).val();
                if (val) {
                    table.column(5).search('^' + val + '$', true, false).draw(); // Exact match
                } else {
                    table.column(5).search('').draw(); // Clear filter
                }
            });

            // Show order detail modal on button click
            $('#ordersTable tbody').on('click', '.btn-view', function() {
                const tr = $(this).closest('tr');
                const orderId = tr.data('order-id');
                const customer = tr.data('customer');
                const date = tr.data('date');
                const status = tr.data('status');
                const total = tr.data('total');
                const itemsTableHtml = tr.find('table.items-table').html(); // Get order items HTML

                // Fill modal with order details
                $('#modalOrderId').text(orderId);
                $('#modalCustomer').text(customer);
                $('#modalDate').text(date);
                $('#modalStatus').text(status);
                $('#modalTotal').text(parseFloat(total).toFixed(2));

                const $tbody = $('#modalItemsTable tbody');
                $tbody.empty();

                // Rebuild order items table in the modal
                tr.find('table.items-table tbody tr').each(function() {
                    const tds = $(this).find('td');
                    $tbody.append('<tr>' +
                        '<td>' + tds.eq(0).text() + '</td>' +
                        '<td>' + tds.eq(1).text() + '</td>' +
                        '<td>R' + tds.eq(2).text() + '</td>' +
                        '</tr>');
                });

                // Show the modal
                let orderDetailModal = new bootstrap.Modal(document.getElementById('orderDetailModal'));
                orderDetailModal.show();
            });

            // Track which order and action (cancel/refund) is selected
            let currentAction = null;
            let currentOrderId = null;

            // Response handler for cancel/refund actions
            function handleActionResponse(success, message, $button) {
                if (success) {
                    let row = $button.closest('tr');
                    row.find('td').eq(5).text(message.includes('Cancelled') ? 'Cancelled' : 'Refunded'); // Update status
                    row.find('.btn-cancel, .btn-refund').attr('disabled', true); // Disable buttons
                    alert(message); // Show confirmation
                } else {
                    alert("Error: " + message);
                }
            }

            // Open confirmation modal for cancel/refund
            $('.btn-cancel, .btn-refund').on('click', function() {
                currentOrderId = $(this).closest('tr').data('order-id');
                currentAction = $(this).hasClass('btn-cancel') ? 'cancel' : 'refund';

                $('#confirmActionModalLabel').text(currentAction.charAt(0).toUpperCase() + currentAction.slice(1) + " Order");
                $('#confirmActionMessage').text("Are you sure you want to " + currentAction + " order #" + currentOrderId + "?");

                let confirmModal = new bootstrap.Modal(document.getElementById('confirmActionModal'));
                confirmModal.show();
            });

            // Confirm action (AJAX call)
            $('#confirmYes').on('click', function() {
                if (!currentOrderId || !currentAction) return;

                // Disable buttons while processing
                $(this).prop('disabled', true);
                $('#confirmNo').prop('disabled', true);

                $.ajax({
                    url: 'update_order_status.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        order_id: currentOrderId,
                        action: currentAction
                    },
                    success: function(response) {
                        let buttonSelector = currentAction === 'cancel' ? '.btn-cancel' : '.btn-refund';
                        let $button = $('tr[data-order-id="' + currentOrderId + '"]').find(buttonSelector);

                        handleActionResponse(response.success, response.message, $button);

                        // Reset modal
                        $('#confirmYes').prop('disabled', false);
                        $('#confirmNo').prop('disabled', false);
                        let confirmModal = bootstrap.Modal.getInstance(document.getElementById('confirmActionModal'));
                        confirmModal.hide();

                        currentOrderId = null;
                        currentAction = null;
                    },
                    error: function() {
                        alert("Server error.");
                        $('#confirmYes').prop('disabled', false);
                        $('#confirmNo').prop('disabled', false);
                    }
                });
            });

            // Cancel action
            $('#confirmNo').on('click', function() {
                currentOrderId = null;
                currentAction = null;
            });
        });
    </script>
</body>

</html>
