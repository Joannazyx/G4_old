<html>
<table border="1">
<caption>Yesterday End Price</caption>
<tr>
<?php foreach (array_keys($yest) as $item) {
	echo "<td>".$item."</td>"; } ?>
</tr>
<tr>
<?php foreach ($yest as $item) {
	echo "<td>".$item."</td>";} ?>
</tr>
</table>
</html>