<?php

// including db config file
include_once './config/db-config.php';

// including import controller file
include_once './controllers/import-controller.php';

// creating object of DBController class
$db = new DBController();

// calling connect() function using object
$conn = $db->connect();

// creating object of import controller and passing connection object as a parameter
$importCtrl = new ImportController($conn);
?>

<!DOCTYPE html>
<html>
<head>
	<title>Stock Price Analysis</title>

	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
	<link href = "https://code.jquery.com/ui/1.10.4/themes/ui-lightness/jquery-ui.css"
         rel = "stylesheet">
</head>
<body>
	<div class="container" style="border-left: 1px solid; border-right: 1px solid;">
		<h2 style="font-size: 30px; letter-spacing: -1px; color: #FFFFFF; background-color: #000000; margin: 10px 0 24px; text-align: center; line-height: 50px;">Stock Buy/Sell Analysis</h2>
		<form method="post" enctype="multipart/form-data">
			<div class="row mt-5">
				<div class="col-md-6 m-auto border shadow">
					<label> Import Data </label>
						<div class="form-group">					
							<input type="file" name="file" class="form-control">
						</div>
						<div class="form-group">
							<button type="submit" name="import" class="btn" style="background-color: #e78f08; color: #ffffff;"> Import Data </button>
						</div>				
				</div>		
			</div>
			<div class="row mt-4">
				<div class="col-md-10 m-auto">
					<div class="m-auto border shadow" style="padding: 10px;">						
						<div style="font-size:12px;color:red;text-align:center;">
							Note: Default loads the data for current month
						</div>
						<span style="color:red">*</span>From Date: <input type = "text" id = "fromdate" style="width: 15%;">
						<span style="color:red">*</span>To Date: <input type = "text" id = "todate" style="width: 15%;">
						<span style="color:red">*</span>Stock Name:
						<?php
      						$sel_stock_sql =
          						'SELECT stock_name FROM stock_import GROUP BY stock_name';
      						$stocks = $conn->query($sel_stock_sql);
      					?>
						<select id = "stock_name" style="width: 15%;padding: 4px;">
							<option value="0">All</option>
							<?php if ($stocks->num_rows > 0) {
           						// output data of each row
           						$count = 1;
           						while ($row = $stocks->fetch_assoc()) { ?>
                 					<option value="<?php echo $count; ?>"><?php echo $row[
    								'stock_name'
								]; ?></option>
            					<?php ++$count;}
       						} ?>
						</select>
						<button name="get_data" class="btn get_data" style="background-color: #0062cc; color: #ffffff;margin-bottom: 6px;margin-left: 10px;"> Get Data </button>
					</div>
					<div class="m-auto border shadow result-table">
						<?php $importResult = $importCtrl->index(); ?>
					</div>
				</div>
			</div>	
		</form>
	</div>


	<!--<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>-->
	<script src = "https://code.jquery.com/jquery-1.10.2.js"></script>
	<script src = "https://code.jquery.com/ui/1.10.4/jquery-ui.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
	<script>
         $(function() {
			var today = new Date();
			var firstDate = new Date(today.getFullYear(), today.getMonth(), 1);
			var lastDate = new Date(today.getFullYear(), today.getMonth(), new Date(today.getFullYear(), today.getMonth()+1, 0).getDate());
			$( "#fromdate" ).datepicker({ dateFormat: 'dd-mm-yy' }).datepicker("setDate",firstDate);
            $( "#todate" ).datepicker({ dateFormat: 'dd-mm-yy' }).datepicker("setDate",lastDate);
         });

		$(".get_data").click(function(e){
			e.preventDefault();

			var from_date_obj = $( "#fromdate" ).val().split("-");
			var to_date_obj = $( "#todate" ).val().split("-");
			var from_date = new Date(from_date_obj[2],parseInt(from_date_obj[1])-1,from_date_obj[0]);
			var to_date = new Date(to_date_obj[2],parseInt(to_date_obj[1])-1,to_date_obj[0]);
			
			if(to_date >= from_date){
				$.ajax({
        			type:"GET",
        			url:"getDataAjax.php",
        			data:{'from_date': $( "#fromdate" ).val(), 'to_date': $( "#todate" ).val(), 'stock_name': $("#stock_name option:selected").text()},
        			success:function (data) {
        				//console.log(data);
						$(".result-table").empty();
						var str='<table class="table">\
						<thead><th> Sl </th><th> Stock Name </th>\
						<th> Buy Date </th><th> Buy Price </th>\
						<th> Sell Date </th><th> Sell Price </th>\
						<th> Profit/Loss </th></thead>';
				
						var json_data = JSON.parse(data);

						if(json_data.length > 0){
							for(var i=0;i<json_data.length;i++){
								str = str + '<tr style="';

            					if (parseInt(json_data[i]['profit_loss']) > 0) {
                					str = str + 'background-color: #52ff69;';
             					} else if (parseInt(json_data[i]['profit_loss']) < 0) {
                					str = str + 'background-color: #ff4c43;';
             					} else {
                					str = str + 'background-color: #f0ff0c;';
             					}
								
								str = str + '>';
								
								str = str + '<td>'+(1+i)+'</td><td>'+json_data[i]['stock_name']+'</td>\
								<td>'+json_data[i]['buy_date']+'</td>\
								<td>'+json_data[i]['buy_price']+'</td>\
								<td>'+json_data[i]['sell_date']+'</td>\
								<td>'+json_data[i]['sell_price']+'</td>\
								<td>'+json_data[i]['profit_loss']+'</td>';
							}
						} else {
							str = str + '<tr><td  colspan="7" style="text-align: center;"> No Records Found... </td></tr>';
						}
					
						str = str + '</table>';
						$(".result-table").append(str);
        			}
    	 		});
			} else {
				alert('To date should be greater than from date');
			}
		});
    </script>
</body>
</html>