<?php

($conn = new mysqli('localhost', 'root', '', 'stock_csv')) or
    die('Database connection error.' . $conn->connect_error);

$from_time = strtotime($_GET['from_date']);
$from_date = date('Y-m-d', $from_time);

$to_time = strtotime($_GET['to_date']);
$to_date = date('Y-m-d', $to_time);

if ($_GET['stock_name'] == 'All') {
    $sql =
        "select stock_name,buy_date,buy_price,sell_date,sell_price,profit_loss from stock_profit_loss where buy_date>='" .
        $from_date .
        "' and buy_date<='" .
        $to_date .
        "' ORDER BY FIELD(stock_name, ''), profit_loss DESC";
} else {
    $sql =
        "select stock_name,buy_date,buy_price,sell_date,sell_price,profit_loss from stock_profit_loss where buy_date>='" .
        $from_date .
        "' and buy_date<='" .
        $to_date .
        "' and stock_name LIKE '%" .
        $_GET['stock_name'] .
        "%' ORDER BY FIELD(stock_name, ''), profit_loss DESC";
}

$profit = $conn->query($sql);

$result = [];
$count = 0;
if ($profit->num_rows > 0) {
    while ($row = $profit->fetch_assoc()) {
        $buy_time = strtotime($row['buy_date']);
        $buy_date = date('d-m-Y', $buy_time);

        $sell_time = strtotime($row['sell_date']);
        $sell_date = date('d-m-Y', $sell_time);

        $result[$count]['stock_name'] = $row['stock_name'];
        $result[$count]['buy_date'] = $buy_date;
        $result[$count]['buy_price'] = $row['buy_price'];
        $result[$count]['sell_date'] = $sell_date;
        $result[$count]['sell_price'] = $row['sell_price'];
        $result[$count]['profit_loss'] = $row['profit_loss'];
        ++$count;
    }
}

echo json_encode($result);
exit;
?>
