<!DOCTYPE html>
<html>
<head>
    <title>Онлайн-магазин мебели</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        h1 {
            background-color: #333;
            color: #fff;
            padding: 20px;
            margin: 0;
        }

        h2 {
            margin-top: 20px;
        }

        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 0 20px;
        }

        .product {
            margin-bottom: 10px;
            padding: 10px;
            background-color: #f2f2f2;
            border-radius: 5px;
        }

        .product span {
            display: inline-block;
            margin-right: 10px;
        }

        .product form {
            display: inline-block;
        }

        .product input[type="number"] {
            width: 60px;
            padding: 5px;
        }

        .product input[type="submit"] {
            padding: 5px 10px;
            background-color: #333;
            color: #fff;
            border: none;
            cursor: pointer;
        }

        .cart {
            margin-top: 20px;
        }

        .cart table {
            width: 100%;
            border-collapse: collapse;
        }

        .cart th, .cart td {
            padding: 8px;
            border: 1px solid #ddd;
        }

        .cart th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .delete-button {
            padding: 5px 10px;
            background-color: #fff;
            color: #333;
            border: 1px solid #ddd;
            cursor: pointer;
        }

        .total {
            margin-top: 10px;
            font-weight: bold;
            text-align: right;
        }

        .empty-cart {
            text-align: center;
        }

        .empty-cart p {
            margin: 0;
        }
    </style>
</head>
<body>
    <h1>Онлайн-магазин мебели</h1>

    <div class="container">

        <?php
        // Подключение к базе данных
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "store_plus";

        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            die("Ошибка подключения к базе данных: " . $conn->connect_error);
        }

        // Обработка нажатия кнопки "Добавить в корзину"
        if (isset($_POST['add_to_cart'])) {
            $furniture_id = $_POST['furniture_id'];
            $quantity = $_POST['quantity'];

            // Получаем количество товара на складе
            $furniture_query = "SELECT quantity FROM furniture WHERE id=$furniture_id";
            $furniture_result = $conn->query($furniture_query);
            $furniture_row = $furniture_result->fetch_assoc();
            $furniture_quantity = $furniture_row['quantity'];

            // Получаем количество товара в корзине
            $cart_query = "SELECT * FROM cart WHERE furniture_id = $furniture_id";
            $cart_result = $conn->query($cart_query);

            if ($cart_result->num_rows > 0) {
                $cart_row = $cart_result->fetch_assoc();
                $cart_quantity = $cart_row['quantity'];
            } else {
                $cart_quantity = 0;
            }

            if (($cart_quantity + $quantity) <= $furniture_quantity) {
                // Проверка, существует ли товар с заданным идентификатором
                $check_query = "SELECT * FROM furniture WHERE id = $furniture_id";
                $check_result = $conn->query($check_query);

                if ($check_result->num_rows > 0) {
                    // Товар существует, проверяем, есть ли он уже в корзине
                    $cart_query = "SELECT * FROM cart WHERE furniture_id = $furniture_id";
                    $cart_result = $conn->query($cart_query);

                    if ($cart_result->num_rows > 0) {
                        // Товар уже в корзине, обновляем количество
                        $cart_row = $cart_result->fetch_assoc();
                        $cart_id = $cart_row['id'];
                        $new_quantity = $cart_row['quantity'] + $quantity;

                        $update_query = "UPDATE cart SET quantity = $new_quantity WHERE id = $cart_id";
                        if ($conn->query($update_query) === TRUE) {
                            echo "Товар успешно добавлен в корзину.";
                        } else {
                            echo "Ошибка: " . $conn->error;
                        }
                    } else {
                        // Товара нет в корзине, добавляем новую запись
                        $insert_query = "INSERT INTO cart (furniture_id, quantity) VALUES ($furniture_id, $quantity)";
                        if ($conn->query($insert_query) === TRUE) {
                            echo "Товар успешно добавлен в корзину.";
                        } else {
                            echo "Ошибка: " . $conn->error;
                        }
                    }
                } else {
                    echo "Товар не найден.";
                }
            } else {
                echo "Невозможно добавить заданное количество.";
            }
        }

        // Обработка нажатия кнопки "Удалить"
        if (isset($_POST['delete_item'])) {
            $cart_id = $_POST['cart_id'];
            $quantity = $_POST['quantity'];

            // Обновление количества товара в корзине
            $update_query = "UPDATE cart SET quantity = quantity - $quantity WHERE id = $cart_id";
            if ($conn->query($update_query) === TRUE) {
                // Проверка, если количество стало нулевым, удаляем товар из корзины
                $delete_query = "DELETE FROM cart WHERE quantity = 0";
                $conn->query($delete_query);
                echo "Товар успешно удален из корзины.";
            } else {
                echo "Ошибка: " . $conn->error;
            }
        }

        // Обработка нажатия кнопки "Удалить все"
        if (isset($_POST['delete_all'])) {
            // Удаление всех товаров из корзины
            $delete_all_query = "DELETE FROM cart";
            if ($conn->query($delete_all_query) === TRUE) {
                echo "Все товары успешно удалены из корзины.";
            } else {
                echo "Ошибка: " . $conn->error;
            }
        }

        // Вывод списка товаров
        $products_query = "SELECT * FROM furniture";
        $products_result = $conn->query($products_query);

        if ($products_result->num_rows > 0) {
            echo "<h2>Список товаров:</h2>";

            while ($row = $products_result->fetch_assoc()) {
                $furniture_id = $row['id'];
                $furniture_name = $row['name'];
                $furniture_price = $row['price'];

                echo "<div class='product'>";
                echo "<span>$furniture_name (Цена: $furniture_price)</span>";
                echo "<form method='post'>";
                echo "<input type='hidden' name='furniture_id' value='$furniture_id'>";
                echo "<input type='number' name='quantity' min='1' value='1'>";
                echo "<input type='submit' name='add_to_cart' value='Добавить в корзину'>";
                echo "</form>";
                echo "</div>";
            }
        } else {
            echo "Нет доступных товаров.";
        }

        // Вывод корзины
        $cart_query = "SELECT * FROM cart";
        $cart_result = $conn->query($cart_query);

        if ($cart_result->num_rows > 0) {
            echo "<div class='cart'>";
            echo "<h2>Корзина:</h2>";
            echo "<table>";
            echo "<tr><th>Товар</th><th>Количество</th><th>Удалить</th></tr>";

            $total_price = 0;

            while ($cart_row = $cart_result->fetch_assoc()) {
                $cart_id = $cart_row['id'];
                $furniture_id = $cart_row['furniture_id'];
                $quantity = $cart_row['quantity'];

                // Получение информации о товаре из корзины
                $furniture_query = "SELECT name, price FROM furniture WHERE id = $furniture_id";
                $furniture_result = $conn->query($furniture_query);
                $furniture_row = $furniture_result->fetch_assoc();
                $furniture_name = $furniture_row['name'];
                $furniture_price = $furniture_row['price'];

                echo "<tr>";
                echo "<td>$furniture_name (Цена: $furniture_price)</td>";
                echo "<td>$quantity</td>";
                echo "<td>";
                echo "<form method='post'>";
                echo "<input type='hidden' name='cart_id' value='$cart_id'>";
                echo "<input type='hidden' name='quantity' value='$quantity'>";
                echo "<input type='submit' name='delete_item' class='delete-button' value='Удалить'>";
                echo "</form>";
                echo "</td>";
                echo "</tr>";

                $total_price += ($furniture_price * $quantity);
            }

            echo "</table>";

            echo "<div class='total'>";
            echo "<p>Общая стоимость: $total_price</p>";
            echo "</div>";

            echo "<form method='post'>";
            echo "<input type='submit' name='delete_all' value='Удалить все'>";
            echo "</form>";

            echo "</div>";
        } else {
            echo "<div class='cart'>";
            echo "<h2>Корзина:</h2>";
            echo "<div class='empty-cart'>";
            echo "<p>Корзина пуста.</p>";
            echo "</div>";
            echo "</div>";
        }

        // Закрытие соединения с базой данных
        $conn->close();
        ?>

    </div>

</body>
</html>
