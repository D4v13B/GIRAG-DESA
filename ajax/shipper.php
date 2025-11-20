<?php

include "../conexion.php";

switch ($_SERVER["REQUEST_METHOD"]) {

  case "GET":
    $term = $_GET["term"];
    $data = [];

    $where = "";

    if ($term != "" && isset($_GET["term"])) {
      $where .= " AND ship_nombre LIKE '%$term%'";
    }
    $sql = "SELECT * FROM shipper WHERE 1=1 $where";

    $res = mysql_query($sql);

    while ($row = mysql_fetch_assoc($res)) {
      $data[] = [
        "ship_id" => $row["ship_id"],
        "ship_nombre" => $row["ship_nombre"]
      ];
    }

    echo json_encode($data);

    break;
}
