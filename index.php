<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS System</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .dashboard-container {
            max-width: 70%;
            margin: auto;
        }
    </style>
</head>
<body>
<div class="container dashboard-container mt-5">
    <legend class="border-bottom mb-4">
        <h1>Supermarket POS System</h1>
    </legend>

    <legend>
        <h2>Purchase Products</h2>
        <form id="add-product-form" class="form-inline">
            <div class="form-group mb-2">
                <label for="product-search" class="sr-only">Search Products:</label>
                <input type="text" id="product-search" class="form-control mr-2" placeholder="Search Products">
            </div>
            <div class="form-group mb-2">
                <label for="product" class="sr-only">Select Product:</label>
                <select name="product" id="product" class="form-control mr-2">
                    <?php
                    $conn = new mysqli('localhost', 'root', '', 'pos_system');
                    if ($conn->connect_error) {
                        die("Connection failed: " . $conn->connect_error);
                    }
                    $result = $conn->query("SELECT * FROM products");
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='{$row['id']}' data-price='{$row['price']}'>{$row['name']} - {$row['price']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group mb-2">
                <label for="quantity" class="sr-only">Quantity:</label>
                <input type="number" name="quantity" id="quantity" class="form-control mr-2" placeholder="Quantity" min="1" value="1" required>
            </div>
            <button type="button" id="add-product" class="btn btn-secondary mb-2">Add Product</button>
        </form>

        <h3>Selected Products</h3>
        <table class="table table-bordered" id="selected-products-table">
            <thead>
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Total</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody id="selected-products-body">
            </tbody>
        </table>

        <div class="form-group">
            <label for="total-amount">Total Amount: </label>
            <input type="text" id="total-amount" class="form-control" readonly>
        </div>

        <form action="process_transaction.php" method="POST">
            <div class="form-group mb-2">
                <label for="msisdn" class="sr-only">Enter M-Pesa Number:</label>
                <input type="text" name="msisdn" id="msisdn" class="form-control mr-2" placeholder="M-Pesa Number" required>
            </div>
            <input type="hidden" name="items" id="items">
            <button type="submit" class="btn btn-primary mb-2">Complete Purchase</button>
        </form>
    </legend>

    <legend class="mt-5">
        <h2>Transaction History</h2>
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Product</th>
                <th>Amount</th>
                <th>M-Pesa Number</th>
                <th>Status</th>
                <th>M-Pesa Receipt Number</th>
                <th>Transaction Date</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $result = $conn->query("SELECT t.*, p.name FROM transactions t JOIN products p ON t.product_id = p.id");
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                                <td>{$row['name']}</td>
                                <td>{$row['amount']}</td>
                                <td>{$row['msisdn']}</td>
                                <td>{$row['transaction_status']}</td>
                                <td>{$row['mpesa_receipt_number']}</td>
                                <td>{$row['transaction_date']}</td>
                            </tr>";
            }
            ?>
            </tbody>
        </table>
    </legend>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    $(document).ready(function() {
        $('#product-search').on('input', function() {
            var searchTerm = $(this).val().toLowerCase();
            $('#product option').each(function() {
                var productText = $(this).text().toLowerCase();
                if (productText.includes(searchTerm)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });

        let totalAmount = 0;
        const selectedProducts = [];

        $('#add-product').on('click', function() {
            const product = $('#product option:selected');
            const productId = product.val();
            const productName = product.text();
            const productPrice = product.data('price');
            const quantity = $('#quantity').val();
            const total = productPrice * quantity;

            const productData = {
                id: productId,
                name: productName,
                price: productPrice,
                quantity: quantity,
                total: total
            };

            selectedProducts.push(productData);
            totalAmount += total;

            $('#selected-products-body').append(`
                    <tr>
                        <td>${productName}</td>
                        <td>${quantity}</td>
                        <td>${productPrice}</td>
                        <td>${total}</td>
                        <td><button type="button" class="btn btn-danger btn-sm remove-product">Remove</button></td>
                    </tr>
                `);

            $('#total-amount').val(totalAmount);

            updateItemsField();
        });

        $(document).on('click', '.remove-product', function() {
            const row = $(this).closest('tr');
            const total = parseFloat(row.find('td:eq(3)').text());

            totalAmount -= total;
            $('#total-amount').val(totalAmount);

            const productName = row.find('td:eq(0)').text();
            const index = selectedProducts.findIndex(product => product.name === productName);
            selectedProducts.splice(index, 1);

            row.remove();
            updateItemsField();
        });

        function updateItemsField() {
            $('#items').val(JSON.stringify(selectedProducts));
        }
    });
</script>
</body>
</html>
