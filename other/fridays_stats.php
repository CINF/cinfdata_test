<?php
#include("graphsettings.php");
include("../common_functions_v2.php");
$dbi = std_dbi();


function items_from_query($query, $type="int"){
  global $dbi;
  $result = $dbi->query($query);
  $data = Array();
  while($row = $result->fetch_row()) {
    if ($type == "int"){
      $data[] = Array($row[0], (int) $row[1]);
    } else {
      $data[] = Array($row[0], (float) $row[1]);
    }
  }
  return $data;
}  


echo(html_header());

echo("<h1>Items</h1>\n");
$query = "" . 
  "select fridays_items.name, count(fridays_transactions.id) as item_count " .
  "from fridays_transactions " .
  "INNER JOIN fridays_items " .
  "ON fridays_transactions.item_barcode=fridays_items.barcode " .
  "where fridays_transactions.user_id < 999999 and " .
  "fridays_transactions.item_barcode IS NOT NULL " .
  "group by fridays_transactions.item_barcode " . 
  "order by item_count desc, fridays_transactions.id desc;";
$pie_info = Array("title" => 'Top 10 items');
$pie_info['data'] = items_from_query($query);
$pie_json = JSON_encode($pie_info);
echo('<img alt="Vertical bar chart" class="centered" width="800" height="500" src="pie.php?data=' . urlencode($pie_json) . '&element_count=10"/>' . "\n");


echo("<h1>Revenue by item</h1>\n");

$query = "" .
  "select fridays_items.name, -sum(fridays_transactions.amount), " .
  "count(fridays_transactions.id) as item_count " .
  "from fridays_transactions " .
  "INNER JOIN fridays_items " .
  "ON fridays_transactions.item_barcode=fridays_items.barcode " .
  "where fridays_transactions.user_id < 999999 and fridays_transactions.item_barcode IS NOT NULL group by fridays_transactions.item_barcode order by item_count desc, fridays_transactions.id desc;";
$pie_info = Array("title" => 'Top 10 revenue by item');
$pie_info['data'] = items_from_query($query);
$pie_json = JSON_encode($pie_info);

echo('<img alt="Vertical bar chart" class="centered" width="800" height="500" src="pie.php?data=' . urlencode($pie_json) . '&element_count=10"/>' . "\n");

echo("<h1>Volume by item</h1>\n");
$query = "" . 
  "select fridays_items.name, count(fridays_transactions.id) * fridays_items.volume as item_count " .
  "from fridays_transactions " .
  "INNER JOIN fridays_items " .
  "ON fridays_transactions.item_barcode=fridays_items.barcode " .
  "where fridays_transactions.user_id < 999999 and " .
  "fridays_transactions.item_barcode IS NOT NULL " .
  "group by fridays_transactions.item_barcode " . 
  "order by item_count desc, fridays_transactions.id desc;";
$pie_info = Array("title" => 'Top 10 volume by item');
$pie_info['data'] = items_from_query($query, $type='float');
$pie_json = JSON_encode($pie_info);
echo('<img alt="Vertical bar chart" class="centered" width="800" height="500" src="pie.php?data=' . urlencode($pie_json) . '&element_count=10&decimals=2"/>' . "\n");


?>

<script>
function checkMods(event){
  if (event.shiftKey && event.ctrlKey) {
    event.preventDefault();
    window.location = "fridays_transactions.php";
    return true;
  } else {
    return false;
  }
}
</script>

<?php
# Secret link
echo("<p style=\"text-align:right\"><a href=\"#\" onClick=\"return checkMods(event)\">&pi;</a></p>");

echo(html_footer());
?>