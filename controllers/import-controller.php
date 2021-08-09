<?php

class ImportController
{
    // getting connection in constructor
    function __construct($conn)
    {
        $this->conn = $conn;
    }

    function group_by($key, $data)
    {
        $result = [];

        foreach ($data as $val) {
            if (array_key_exists($key, $val)) {
                $result[$val[$key]][] = $val;
            } else {
                $result[''][] = $val;
            }
        }

        return $result;
    }

    // function for reading csv file
    public function index()
    {
        $fileName = '';

        $imported = false;
        // if file size is not empty
        if (isset($_FILES['file']) && $_FILES['file']['size'] > 0) {
            $imported = true;
            // reading tmp_file name
            $fileName = $_FILES['file']['tmp_name'];

            $arrayFromCSV = array_map('str_getcsv', file($fileName));

            // Group data by the "1" key
            $arrayGroup = $this->group_by('1', $arrayFromCSV);
            unset($arrayGroup['stock_name']);

            // Open a file in write mode ('w')
            $error_filename = 'logs/error_log_' . date('d_m_Y') . '.csv';
            $fp = fopen($error_filename, 'a');

            $results = [];
            $counter = 0;
            foreach ($arrayGroup as $key => $stocks) {
                foreach ($stocks as $ikey => $date) {
                    $buy_time = strtotime($stocks[$ikey][0]);
                    $buy_date = date('Y-m-d', $buy_time);

                    $check_stock_sql =
                        "select id from stock_import where s_date='" .
                        $buy_date .
                        "' and stock_name='" .
                        $key .
                        "'";
                    $check_stock = $this->conn->query($check_stock_sql);

                    if ($check_stock->num_rows > 0) {
                        fputcsv($fp, [
                            '[' . date('d-m-Y H:i:s') . ']',
                            $stocks[$ikey][0],
                            $key,
                            $stocks[$ikey][2],
                            'error! duplicate record',
                        ]);
                        continue;
                    } else {
                        // inserting values into the table
                        $inert_import_sql =
                            "INSERT INTO stock_import (s_date, stock_name, price) VALUES ('" .
                            $buy_date .
                            "','" .
                            $key .
                            "','" .
                            $stocks[$ikey][2] .
                            "')";
                        $inert_import = $this->conn->query($inert_import_sql);
                    }

                    foreach ($stocks as $iikey => $price) {
                        if (isset($stocks[$ikey]) && isset($stocks[$iikey])) {
                            $sell_time = strtotime($stocks[$iikey][0]);
                            $sell_date = date('Y-m-d', $sell_time);

                            if ($buy_time < $sell_time) {
                                $results[$counter]['stock_name'] = $key;
                                $results[$counter]['buy_date'] = $buy_date;
                                $results[$counter]['buy_price'] =
                                    $stocks[$ikey][2];
                                $results[$counter]['sell_date'] = $sell_date;
                                $results[$counter]['sell_price'] =
                                    $stocks[$iikey][2];
                                $results[$counter]['profit_loss'] =
                                    $stocks[$iikey][2] - $stocks[$ikey][2];
                                ++$counter;
                            }
                        }
                    }
                }
            }

            fclose($fp);

            // inserting values into the table
            $insert_profit_sql =
                'INSERT INTO stock_profit_loss (stock_name, buy_date, buy_price, sell_date, sell_price, profit_loss) VALUES';
            foreach ($results as $r) {
                $insert_profit_sql =
                    $insert_profit_sql .
                    " ('" .
                    $r['stock_name'] .
                    "', '" .
                    $r['buy_date'] .
                    "', " .
                    $r['buy_price'] .
                    ", '" .
                    $r['sell_date'] .
                    "', " .
                    $r['sell_price'] .
                    ', ' .
                    $r['profit_loss'] .
                    '),';
            }
            $insert_profit_sql = trim($insert_profit_sql, ',');
            $insert_profit = $this->conn->query($insert_profit_sql);
        }

        // select rows of the table
        $first_date = date('Y-m-01');
        $last_date = date('Y-m-t', strtotime($first_date));

        $select_profit_sql =
            "SELECT stock_name,buy_date,buy_price,sell_date,sell_price,profit_loss FROM stock_profit_loss where buy_date >= '" .
            $first_date .
            "' and buy_date <= '" .
            $last_date .
            "' ORDER BY FIELD(stock_name, ''), profit_loss DESC";
        $select_profit = $this->conn->query($select_profit_sql);
        ?>
         
         <?php if ($imported) { ?>
            <div style="text-align: center;background-color:#00e835;">Records Processed Successfully. Duplicate records excluded (<a href="/stock-analysis/logs/error_log_<?php echo date(
                'd_m_Y'
            ); ?>.csv" download>Download Error Log</a>)</div>
         <?php } ?>
         
         <table class="table">
                 <thead>
                     <th> Sl </th>
                     <th> Stock Name </th>
                     <th> Buy Date </th>
                     <th> Buy Price </th>
                     <th> Sell Date </th>
                     <th> Sell Price </th>
                     <th> Profit/Loss </th>
                 </thead>
                 
         <?php if ($select_profit->num_rows > 0) {
             // output data of each row
             $count = 1;
             while ($row = $select_profit->fetch_assoc()) {

                 $buy_time = strtotime($row['buy_date']);
                 $buy_date = date('d-m-Y', $buy_time);

                 $sell_time = strtotime($row['sell_date']);
                 $sell_date = date('d-m-Y', $sell_time);
                 ?>
             <tr style="
             <?php if ($row['profit_loss'] > 0) {
                 echo 'background-color: #52ff69;';
             } elseif ($row['profit_loss'] < 0) {
                 echo 'background-color: #ff4c43;';
             } else {
                 echo 'background-color: #f0ff0c;';
             } ?>">
                 <td> <?php echo $count; ?> </td>
                 <td> <?php echo $row['stock_name']; ?> </td>
                 <td> <?php echo $buy_date; ?> </td>
                 <td> <?php echo $row['buy_price']; ?> </td>
                 <td> <?php echo $sell_date; ?> </td>
                 <td> <?php echo $row['sell_price']; ?> </td>
                 <td> <?php echo $row['profit_loss']; ?> </td>
             </tr>
            <?php ++$count;
             }
         } else {
              ?>
         <tr>
             <td  colspan="7" style="text-align: center;"> No Records Found... </td>
         </tr>
<?php
         } ?>
</table>
<?php $this->conn->close();
    }
}

?>
