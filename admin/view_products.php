<?php
// Start the session
session_start();

// Check if the user is logged in as admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: logIn.html");
    exit();
}

// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gallery_cafe"; // Update this to your actual database name

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Query to select products
$sql = "SELECT id, name, description, price, stock, image, menu_type FROM products";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Products</title>
    <link rel="stylesheet" href="../styles/view_products.css">
    
    <script>
        function searchProducts() {
            var input = document.getElementById('searchInput');
            var filter = input.value.toLowerCase();
            var table = document.getElementById('productsTable');
            var tr = table.getElementsByTagName('tr');

            for (var i = 1; i < tr.length; i++) {
                tr[i].style.display = 'none';
                var td = tr[i].getElementsByTagName('td');
                for (var j = 0; j < td.length; j++) {
                    if (td[j]) {
                        if (td[j].innerHTML.toLowerCase().indexOf(filter) > -1) {
                            tr[i].style.display = '';
                            break;
                        }
                    }
                }
            }
        }

        function editRow(row) {
            var cells = row.getElementsByTagName('td');
            for (var i = 1; i < cells.length - 3; i++) {
                var cell = cells[i];
                var content = cell.innerText;
                cell.setAttribute('data-original-content', content);
                cell.innerHTML = '<input type="text" value="' + content + '">';
            }
            var menuTypeCell = cells[cells.length - 3];
            var menuTypeContent = menuTypeCell.innerText;
            menuTypeCell.setAttribute('data-original-content', menuTypeContent);
            menuTypeCell.innerHTML = `
                <select>
                    <option value="Sri Lankan" ${menuTypeContent === 'Sri Lankan' ? 'selected' : ''}>Sri Lankan</option>
                    <option value="Italian" ${menuTypeContent === 'Italian' ? 'selected' : ''}>Italian</option>
                    <option value="Chinese" ${menuTypeContent === 'Chinese' ? 'selected' : ''}>Chinese</option>
                    <option value="Beverages" ${menuTypeContent === 'Beverages' ? 'selected' : ''}>Beverages</option>
                </select>
            `;

            cells[cells.length - 2].innerHTML = '<input type="file" accept="image/*">';
            cells[cells.length - 1].innerHTML = '<button class="save-button" onclick="saveRow(this)">Save</button> | <button class="cancel-button" onclick="cancelEdit(this)">Cancel</button>';
        }

        function saveRow(button) {
            var row = button.parentNode.parentNode;
            var cells = row.getElementsByTagName('td');

            var id = cells[0].innerText;
            var name = cells[1].getElementsByTagName('input')[0].value;
            var description = cells[2].getElementsByTagName('input')[0].value;
            var price = cells[3].getElementsByTagName('input')[0].value;
            var stock = cells[4].getElementsByTagName('input')[0].value;
            var menuType = cells[5].getElementsByTagName('select')[0].value;
            var image = cells[6].getElementsByTagName('input')[0].files[0];

            var formData = new FormData();
            formData.append('id', id);
            formData.append('name', name);
            formData.append('description', description);
            formData.append('price', price);
            formData.append('stock', stock);
            formData.append('menu_type', menuType);
            formData.append('image', image);

            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'update_product.php', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    alert('Product updated successfully!');
                    location.reload();
                } else {
                    alert('Error updating product.');
                }
            };
            xhr.send(formData);
        }

        function cancelEdit(button) {
            var row = button.parentNode.parentNode;
            var cells = row.getElementsByTagName('td');

            for (var i = 1; i < cells.length - 3; i++) {
                var cell = cells[i];
                var originalContent = cell.getAttribute('data-original-content');
                cell.innerHTML = originalContent;
            }
            // Restore the menu_type cell to its original state
            var menuTypeCell = cells[cells.length - 3];
            var originalMenuTypeContent = menuTypeCell.getAttribute('data-original-content');
            menuTypeCell.innerHTML = originalMenuTypeContent;

            // Restore the image cell to its original state
            cells[cells.length - 2].innerHTML = '<img src="' + cells[cells.length - 2].getAttribute('data-original-content') + '" alt="Product Image" class="product-image"/>';

            // Restore the actions cell to its original state
            cells[cells.length - 1].innerHTML = '<button class="edit-button" onclick="editRow(this.parentNode.parentNode)">Edit</button> | <a class="delete-link" href="delete_product.php?id=' + cells[0].innerText + '" onclick="return confirm(\'Are you sure you want to delete this product?\')">Delete</a>';
        }
    </script>
</head>
<body>
    <div class="view-products-container">
        <h2>Available Products</h2>
        <input type="text" id="searchInput" onkeyup="searchProducts()" placeholder="Search for products..">
        <table id="productsTable">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Menu Type</th>
                <th>Image</th>
                <th>Actions</th>
            </tr>
            <?php
            // Check if there are any products in the result
            if (mysqli_num_rows($result) > 0) {
                // Fetch and display each product
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>";
                    echo "<td>" . $row['id'] . "</td>";
                    echo "<td>" . $row['name'] . "</td>";
                    echo "<td>" . $row['description'] . "</td>";
                    echo "<td>" . $row['price'] . "</td>";
                    echo "<td>" . $row['stock'] . "</td>";
                    echo "<td>" . $row['menu_type'] . "</td>";
                    echo '<td><img src="data:image/jpeg;base64,' . base64_encode($row['image']) . '" alt="Product Image" class="product-image"/></td>';
                    echo '<td class="actions">';
                    echo '<button onclick="editRow(this.parentNode.parentNode)">Edit</button> | ';
                    echo '<a href="delete_product.php?id=' . $row['id'] . '" onclick="return confirm(\'Are you sure you want to delete this product?\')">Delete</a>';
                    echo '</td>';
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='8'>No products found</td></tr>";
            }
            // Close the database connection
            mysqli_close($conn);
            ?>
        </table>
        <a href="admin_dashboard.html" class="back-link">Back to Admin Dashboard</a>
    </div>
</body>
</html>
