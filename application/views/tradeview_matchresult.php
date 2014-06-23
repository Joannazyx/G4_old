<html>
<table border="1">
<caption> Match Item Produced</caption>
<th>Buyer</th>
<tr>
<td>CommissionID</td>   <td>StockAccountID</td>   <td>StockHolderID</td>   <td>Amount</td>   <td>Price</td>   <td>SubmitTime</td>   <td>Currency</td>
</tr>
<tr>
<?php echo "<td>".$debug['buyer']."</td><td>".$debug['buyzj']."</td><td>".$debug['buyzq']."</td><td>".$buy['CommissionAmount']."</td><td>".$buy['CommissionPrice']."</td><td>".$buy['CommissionTime']."</td><td>".$debug['currency']."</td>"; ?>
</tr>
<th>Seller</th>
<tr>
<td>CommissionID</td>   <td>StockAccountID</td>   <td>StockHolderID</td>   <td>Amount</td>   <td>Price</td>   <td>SubmitTime</td><td>Currency</td>
</tr>
<tr>
<?php echo "<td>".$debug['seller']."</td><td>".$debug['sellzj']."</td><td>".$debug['sellzq']."</td><td>".$sell['CommissionAmount']."</td><td>".$sell['CommissionPrice']."</td><td>".$sell['CommissionTime']."</td><td>".$debug['currency']."</td>"; ?>
</tr>
<th>Result</th>
<tr>
<td>StockID</td>   <td>Amount</td>   <td>Price</td> <td>time</td>
</tr>
<tr>
<?php echo "<td>".$debug['StockID']."</td><td>".$debug['amount']."</td><td>".$debug['price']."</td><td>".$time."</td>"; ?>
</tr>
</table>
</html>