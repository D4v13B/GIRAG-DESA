jQuery.fn.center = function () {
    this.css("position","absolute");
    this.css("top", (($(window).height() - this.outerHeight()) / 2) +  $(window).scrollTop() + "px");
    this.css("left", (($(window).width() - this.outerWidth()) / 2) + $(window).scrollLeft() + "px");
    return this;
}


$(function () {
$("#modal").draggable({ });
$('#modal').hide();
$('#overlay').hide();
$('#modal').center();

$("#modal2").draggable({ });
$('#modal2').hide();
$('#overlay2').hide();
$('#modal2').center();

$("#modal3").draggable({ });
$('#modal3').hide();
$('#overlay3').hide();
$('#modal3').center();

$("#modal4").draggable({ });
$('#modal4').hide();
$('#overlay4').hide();
$('#modal4').center();

$("#modal5").draggable({ });
$('#modal5').hide();
$('#overlay5').hide();
$('#modal5').center();

//
$("#modal6").draggable({ });
$('#modal6').hide();
$('#overlay6').hide();
$('#modal6').center();

$("#modal7").draggable({ });
$('#modal7').hide();
$('#overlay7').hide();
$('#modal7').center();

$("#modal8").draggable({ });
$('#modal8').hide();
$('#overlay8').hide();
$('#modal8').center();

$("#modal9").draggable({ });
$('#modal9').hide();
$('#overlay9').hide();
$('#modal9').center();

$("#modal10").draggable({ });
$('#modal10').hide();
$('#overlay10').hide();
$('#modal10').center();

$("#modal11").draggable({ });
$('#modal11').hide();
$('#overlay11').hide();
$('#modal11').center();

$("#modal12").draggable({ });
$('#modal12').hide();
$('#overlay12').hide();
$('#modal12').center();

$("#modal13").draggable({ });
$('#modal13').hide();
$('#overlay13').hide();
$('#modal13').center();

$("#modal14").draggable({ });
$('#modal14').hide();
$('#overlay14').hide();
$('#modal14').center();

//PARA MODULOS
$("#modal100").draggable({ });
$('#modal100').hide();
$('#overlay100').hide();
$('#modal100').center();

$("#modal200").draggable({ });
$('#modal200').hide();
$('#overlay200').hide();
$('#modal200').center();

$("#modal300").draggable({ });
$('#modal300').hide();
$('#overlay300').hide();
$('#modal300').center();

$("#modal400").draggable({ });
$('#modal400').hide();
$('#overlay400').hide();
$('#modal400').center();

$("#modal500").draggable({ });
$('#modal500').hide();
$('#overlay500').hide();
$('#modal500').center();
//PARA MODULOS

$('#sobretodo').hide();
$('#procesando').hide();

$('#procesando').center();


});


$(function () {
	$('#close').click(
		function(){
		$('#modal').hide();
		$('#overlay').hide();
	});
});

$(function () {
	$('#close2').click(
		function(){
		$('#modal2').hide();
		$('#overlay2').hide();
	});
});

$(function () {
	$('#close3').click(
		function(){
		$('#modal3').hide();
		$('#overlay3').hide();
	});
});

$(function () {
	$('#close4').click(
		function(){
		$('#modal4').hide();
		$('#overlay4').hide();
	});
});


$(function () {
	$('#close5').click(
		function(){
		$('#modal5').hide();
		$('#overlay5').hide();
	});
});

$(function () {
	$('#close6').click(
		function(){
		$('#modal6').hide();
		$('#overlay6').hide();
	});
});

$(function () {
	$('#close7').click(
		function(){
		$('#modal7').hide();
		$('#overlay7').hide();
	});
});

$(function () {
	$('#close8').click(
		function(){
		$('#modal8').hide();
		$('#overlay8').hide();
	});
});

$(function () {
	$('#close9').click(
		function(){
		$('#modal9').hide();
		$('#overlay9').hide();
	});
});

$(function () {
	$('#close10').click(
		function(){
		$('#modal10').hide();
		$('#overlay10').hide();
	});
});

$(function () {
	$('#close11').click(
		function(){
		$('#modal11').hide();
		$('#overlay11').hide();
	});
});

$(function () {
	$('#close12').click(
		function(){
		$('#modal12').hide();
		$('#overlay12').hide();
	});
});

$(function () {
	$('#close13').click(
		function(){
		$('#modal13').hide();
		$('#overlay13').hide();
	});
});

$(function () {
	$('#close14').click(
		function(){
		$('#modal14').hide();
		$('#overlay14').hide();
	});
});

//PARA MODULOS
$(function () {
	$('#close100').click(
		function(){
		$('#modal100').hide();
		$('#overlay100').hide();
	});
});

$(function () {
	$('#close200').click(
		function(){
		$('#modal200').hide();
		$('#overlay200').hide();
	});
});

$(function () {
	$('#close300').click(
		function(){
		$('#modal300').hide();
		$('#overlay300').hide();
	});
});

$(function () {
	$('#close400').click(
		function(){
		$('#modal400').hide();
		$('#overlay400').hide();
	});
});

$(function () {
	$('#close500').click(
		function(){
		$('#modal500').hide();
		$('#overlay500').hide();
	});
});
//PARA MODULOS


$(function () {
	$('#dmodal').click(
	function(){
	$('#modal').show();
	$('#overlay').show();
	});
});

$(function () {
	$('#dmodal2').click(
	function(){
	$('#modal2').show();
	$('#overlay2').show();
	});
});

$(function () {
	$('#dmodal3').click(
	function(){
	$('#modal3').show();
	$('#overlay3').show();
	});
});

$(function () {
	$('#dmodal4').click(
	function(){
	$('#modal4').show();
	$('#overlay4').show();
	});
});

$(function () {
	$('#dmodal5').click(
	function(){
	$('#modal5').show();
	$('#overlay5').show();
	});
});

$(function () {
	$('#dmodal6').click(
	function(){
	$('#modal6').show();
	$('#overlay6').show();
	});
});

$(function () {
	$('#dmodal7').click(
	function(){
	$('#modal7').show();
	$('#overlay7').show();
	});
});

$(function () {
	$('#dmodal8').click(
	function(){
	$('#modal8').show();
	$('#overlay8').show();
	});
});

$(function () {
	$('#dmodal9').click(
	function(){
	$('#modal9').show();
	$('#overlay9').show();
	});
});