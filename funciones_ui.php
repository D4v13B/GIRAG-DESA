<?php
function armar_encabezado_sorter($columnas, $ordenar_por='')
{
	$i=0;
	$columnas_header = explode(",", $columnas);
	foreach ($columnas_header as $valor)
	{
		if($i==$ordenar_por) {$data_sort='data-sort-default';} else {$data_sort='';}
		$encabezado .= "<th class=tabla_datos_titulo $data_sort >$valor</th>";
		$i++;
	}
	
	return $encabezado;
}

function autocompletar_filtro($campo, $obtener_link, $variable_request, $min_largo, $variable_id, $con_modal='',$funcion_modal='')
{
    /*
    $campo = ID del input de texto
    $obtener_link = URL del script que devuelve los datos (ej. obtener_vuelos.php)
    $variable_request = Nombre de la columna del valor a mostrar (ej. 'codigo_vuelo')
    $min_largo = Número de caracteres para empezar la búsqueda
    $variable_id = ID del input oculto donde se guardará el ID real
    $columna_id = Nombre de la columna del ID real en la base de datos (ej. 'vuel_id')
    */
    $script_autocompletar = "$('#" . $campo . "').autocomplete({
        source: function(request, response) {
            $.ajax({
                url: '$obtener_link',
                dataType: 'json',
                data: {
                    // La clave del objeto data DEBE coincidir con la que espera tu script
                    '$variable_request': request.term
                },
                success: function(data) {
                    response($.map(data, function(item) {
                        return {
                            // jQuery UI espera las claves 'label', 'value' e 'id'
                            label: item.label,  
                            value: item.label,
                            id: item.id
                        };
                    }));
                }
            });
        },
        minLength: $min_largo,
        open: function() {
            setTimeout(function () {
                $('.ui-autocomplete').css('z-index', 99999999999999);
            }, 0);
        },
        select: function(event, ui) {
            $('#" . $variable_id . "').val(ui.item.id);
        }
    });";

    $modal = "<div id='overlay" . $con_modal . "'></div>
        <div id='modal" . $con_modal . "'><div id='content" . $con_modal . "'>
        <input type=hidden id=h" . $con_modal . "_id>
        <div id='div_modal_" . $con_modal . "'>
          <input type='text' style='width:250px;height:40px;font-size:14pt' id=in_buscar_cliente class=in_buscar_cliente autocomplete='off'>
          <input type=hidden id=cliente_busqueda>
          <div class=botones onclick='$funcion_modal'>ASIGNAR CLIENTE</DIV>
        </div>
      </div>
      <a href='javascript:void(0);' id='close" . $con_modal . "'>close</a>
      </div>";
      
    echo "<script>
    $(function () {
        $script_autocompletar
    });
    </script>";
}
?>