<?php include('conexion.php');

$i_caso_descripcion=$_POST['i_caso_descripcion'];

$i_caes_id=$_POST['i_caes_id'];

$i_depa_id=$_POST['i_depa_id'];

$i_cati_id=$_POST['i_cati_id'];

$i_inso_id=$_POST['i_inso_id'];

$i_inpr_id=$_POST['i_inpr_id'];

$i_imec_id=$_POST['i_imec_id'];

$i_impe_id=$_POST['i_impe_id'];

$i_imma_id=$_POST['i_imma_id'];

$i_equi_id=$_POST['i_equi_id'];

$i_caso_fecha=$_POST['i_caso_fecha'];

$i_caso_nota=$_POST['i_caso_nota'];

$i_usua_id_aprobado=$_POST['i_usua_id_aprobado'];

$i_usua_id_asignado=$_POST['i_usua_id_asignado'];

$i_depa_id_asignado = $_POST["i_depa_id_asignado"];

$i_usua_id_asignado_2=$_POST["i_usua_id_asignado_2"];

$i_usua_id_asignado_3=$_POST["i_usua_id_asignado_3"];

$qsql = "insert into casos 

(

caso_descripcion

, 

caes_id

, 

depa_id

, 

cati_id

, 

inso_id

, 

inpr_id

, 

imec_id

, 

impe_id

, 

imma_id

, 

equi_id

, 

caso_fecha

, 

caso_nota

, 

usua_id_aprobado

, 

usua_id_asignado

,

depa_id_asignado

) 

values (

'$i_caso_descripcion', 

'$i_caes_id', 

'$i_depa_id', 

'$i_cati_id', 

'$i_inso_id', 

'$i_inpr_id', 

'$i_imec_id', 

'$i_impe_id', 

'$i_imma_id', 

'$i_equi_id', 

'$i_caso_fecha', 

'$i_caso_nota', 

'$i_usua_id_aprobado', 

'$i_usua_id_asignado',

'$i_depa_id_asignado'
)";

mysql_query($qsql);

?>



