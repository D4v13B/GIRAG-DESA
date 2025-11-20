<?php include('conexion.php');
$id=$_GET['id'];
$qsql="SELECT a.*, cons_tipo_constribuyente, cons_ruc,
cons_dv, cons_nombre, cons_telefono, cons_email, cons_direccion
FROM ingresos a, consignee b WHERE ingr_id='$id' AND a.clie_id=b.cons_id";
$rs=mysql_query($qsql);
$i=0;
echo mysql_result($rs,$i,'ingr_id') . '||';
echo mysql_result($rs,$i,'clie_id') . '||';
echo mysql_result($rs,$i,'ingr_fecha') . '||';
echo mysql_result($rs,$i,'ingr_numero_factura') . '||';
echo mysql_result($rs,$i,'infp_id') . '||';
echo mysql_result($rs,$i,'inen_id') . '||';
echo mysql_result($rs,$i,'inpa_id') . '||';
echo mysql_result($rs,$i,'cons_tipo_constribuyente') . '||';
echo mysql_result($rs,$i,'cons_direccion') . '||';
echo mysql_result($rs,$i,'cons_ruc') . '||';
echo mysql_result($rs,$i,'cons_dv') . '||';
echo mysql_result($rs,$i,'cons_nombre') . '||';
echo mysql_result($rs,$i,'cons_telefono') . '||';
echo mysql_result($rs,$i,'cons_email') . '||';
echo mysql_result($rs,$i,'ingr_subtotal') . '||';
echo mysql_result($rs,$i,'ingr_total') . '||';
echo mysql_result($rs,$i,'ingr_impuesto') . '||';
echo mysql_result($rs,$i,'ingr_tipo_cliente_FE') . '||';
echo mysql_result($rs,$i,'ingr_tipo_contribuyente_FE');
?>
