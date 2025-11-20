<?php
$qsql ="select * from menu a, menu_roles b 
where a.menu_id=b.menu_id
AND b.usti_id=$rol
order by mema_id, menu_nombre";

//echo $qsql;

$rs = mysql_query($qsql);
$num= mysql_num_rows($rs);
$i=0;
?>
<div class="sidebar">
          <!-- Sidebar user panel -->
		  <!-- Sidebar user panel (optional) -->
			<div class="user-panel mt-3 pb-3 mb-3 d-flex">
			<div class="image">
			  <img src="dist/img/neutral.png" class="img-circle elevation-2" alt="User Image">
			</div>
			<div class="info">
			  <a href="#" class="d-block"><?php echo $nombre_completo?></a>
			</div>
			</div>
          <nav class="mt-2">
          <ul class="nav nav-pills nav-sidebar flex-column nav-child-indent" data-widget="treeview" role="menu" data-accordion="false">
            <li class="nav-item">
			<a href="index.php" class="nav-link">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>
                Dashboard
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
			</li>
			<?php
			$mema_id2=0;
			while($i<$num)
			{
			//pongo una bandera para saber si ya escribi el primer titulo
			$mema_id=mysql_result($rs, $i, 'mema_id');

			if($mema_id2==0 || $mema_id!=$mema_id2)
			{
			if($mema_id2!=0) echo "</ul></li>";
			//saco el nombre del menú
			$nombre_menu=obtener_valor("select mema_nombre from menu_madre where mema_id=$mema_id", "mema_nombre");
			$nombre_icono=obtener_valor("select mema_icon from menu_madre where mema_id=$mema_id", "mema_icon");
			
			if($nombre_icono=='') $nombre_icono='fas fa-info-circle';
			
			echo "<li class='nav-item has-treeview'>
			<a href='javascript:void(0);' class='nav-link'>
                <i class='$nombre_icono'></i>
				<p>
				$nombre_menu
				<i class='fas fa-angle-left right'></i>
				<span class='badge badge-info right'></span>
				</p>
            </a>
			<ul class='nav nav-treeview'>";
			$mema_id2=$mema_id;
			}
			//escribo los submenus
			$menu_url=mysql_result($rs, $i, 'menu_url');
			$menu_nombre=mysql_result($rs, $i, 'menu_nombre');
			
			$menu_icono = mysql_result($rs, $i, 'menu_icono');
			if($menu_icono=='') $menu_icono = 'fas fa-caret-right';

			echo "<li class='nav-item'>
			<a href='$menu_url' class='nav-link'>
			<i class='$menu_icono'></i>
			<p>$menu_nombre</p>
			</a>
			</li>"; 
			$i++;
			}
			?>
			</ul>
			</li>
			<li class="header"><a href="logout.php">Salir</a></li>            
          </ul>
		  </nav>
</div>