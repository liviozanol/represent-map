<?php

/* TO DOs:
Corrigir todos os erros PHP

- Fazer o About
- Colocar contato de e-mail da escola e envio automático de aprovação/reprovação de inserção e adição/remoção de cursos.
- Fazer forma de exibição de cursos e escolas em tabela ordenável
- Fazer forma de download das informações.
- Criar API e modificar código para usar API.
- Validação javascript do formulario.
- Mudar de mysql fetch para PDO ou mysqli
- Estruturar Javascripts nos locais corretos (estão dispersos) 
-  Fazer maneira de cadastramento de cursos pela própria instituição:
                1) Cadastrar instituição
                2) Cadastrar cursos
                - Tela especifica da instituição para cadastro de cursos e aprovação do admin (eg.: http://<mapa>/instituicao/cadastrarcurso)
 
- Fazer refactoring do code ajustando tudo que está bagunçado e organizando os códigos
- Consertar erros cosméticos na página após troca de versão do bootstrap (principalmente página login.php).
- Ajeitar código e posição (js pro final, css pro comeco, etc...)
- Filtro por eixo tecnológico de curso
- Cookie para primeira visita ao site explicando como funciona
- Ver ob_start e forma de cache/evitar Dos

 
Futuro:
- Integração com demandas de capacitação do mercado de trabalho
- Integração com demanda de emprego e trabalho (deve ser aberto via API para qualquer um) (cruzamento em 3 dimensões)
- Fazer Crawler do pronatec para exibir vaga de curso automática
- Outros Crawlers?
.


*/

//$typesFileName = "categories.json";

if(!file_exists('include/db.php')) require_once('installer.php');
include_once "header.php";
?>

<!DOCTYPE html>
<html>
  <head>
    <!--
    Disclaimer: Please, keep in mind that I am NOT a developer, and I've written this code fastly and it has a LOT of bugs.
    Also, if you want to use part of this code, leave my recognition for this poor job. thx.
    livio.zanol.puppim@gmail.com
    -->
    
    <link rel="stylesheet" href="normalize.css" type="text/css" />
    
    <title><?php echo $title_tag; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta charset="UTF-8">
    <link href='http://fonts.googleapis.com/css?family=Open+Sans+Condensed:700|Open+Sans:400,700' rel='stylesheet' type='text/css'>

    
    <link href="./bootstrap-new/css/bootstrap.css" rel="stylesheet" type="text/css" />
    <link href="./bootstrap-new/css/bootstrap-theme.css" rel="stylesheet" type="text/css" />
    
    <link rel="stylesheet" href="map.css?nocache=289671982568" type="text/css" />
    <link rel="stylesheet" media="only screen and (max-device-width: 768px)" href="mobile.css" type="text/css" />
    <script src="./scripts/jquery-1.11.3.min.js" type="text/javascript" charset="utf-8"></script>
    
    <script src="./bootstrap-new/js/bootstrap.js" type="text/javascript" charset="utf-8"></script>
    <script src="./scripts/bootstrap3-typeahead.min.js" type="text/javascript" charset="utf-8"></script>
    
    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?libraries=places"></script>
    <script type="text/javascript" src="./scripts/label.js"></script>
<style type="text/css">


.navbar-nav>li {
        display: inline-block;
    }
    
    #typeahead_seach {
		border: 0;
		padding: 0;
    }

    #typeahead_seach .tooltip {
	min-width:200px;
	width 200px;
    }
.dropdown-menu {
	min-width: 250px ;
}
	
 .popover {
  width: 310px;
  max-width: 310px;
 }
 .modal {
    top: 45%;
}

#modal_placeInfo
{
	top: 10%;
}

 .modal-backdrop {
	z-index: 1020;
}

  .redPlaceHolder::-webkit-input-placeholder {
      color: #FF6666;
   }

   .redPlaceHolder:-moz-placeholder { /* Firefox 18- */
      color: #FF6666;  
   }

   .redPlaceHolder::-moz-placeholder {  /* Firefox 19+ */
      color: #FF6666;  
   }

   .redPlaceHolder:-ms-input-placeholder {  
      color: #FF6666;  
   }
   


</style>
    <script type="text/javascript">
      var map;
      var infowindow = null;
      var gmarkers = [];
	 var isDragging = false;
      var highestZIndex = 0;
      var agent = "default";
      var zoomControl = true;
      var googleMapsAutocomplete;
      var insertedMark;
      var googleMapsApiClickListener;
      var fullAddress = {
		"street_name" :"",
		"street_number" : "",
		"neighborhood": "", //Bairro?
		"city" : "", 
		"state" : "", //Estado?
		"country" : "",
		"postal_code": "",
      
      };
	 var typeAheadJSON = [];
	 var placesJson = {};
	 var courseSelected = false;
	 var categoriesCounter = {
		"total" : {
		},
		"current" : {
		}
	 
	 };
	 var googleGeocodeCountryRestriction = "<?php echo $geocodeCoutry;?>";
	 var googleGeocodeCentralLocation = new google.maps.LatLng(<?php echo $lat_lng; ?>);
	 var typeAheadFound = false;
	 var courseInstancesDivsInfoWindow = 0;
	 
	function showModalPlaceInfo (place) {
		$('.tempDivCreatedforCourse').remove();
		courseInstancesDivsInfoWindow = 0;
		$('#placeInfoModal').html(placesJson[place].name);
		
		$('#modal_placeInfo_inep').html('INEP: '+ placesJson[place].inep);
				
		$('#modal_placeInfo_url').html('<a target="_blank" href="'+placesJson[place].uri+'">'+placesJson[place].uri+'</a>');
		
		$('#modal_placeInfo_address').html(placesJson[place].complete_address);
			
		$('#modal_placeInfo_telephone').html('<span class="glyphicon glyphicon-phone-alt" aria-hidden="true"></span> '
		+	placesJson[place].address_telephone);
		jQuery.each(placesJson[place]['coursesInstances'], function(j, val) {
			if (val.name) {
			
				var $template = $('#coursesTemplate'),
				$clone = $template
					.clone()
					.removeClass('hide')
					.addClass('tempDivCreatedforCourse')
					.removeAttr('id')
					.attr('data-course-index', courseInstancesDivsInfoWindow)
				.insertBefore($template);
				$clone
					.find('[id="modal_placeInfo_courseName"]').html(''
						+ '<h4><span class="glyphicon glyphicon glyphicon-book" aria-hidden="true"></span><strong> ' +	val.name + '</strong></h4>').end()
					.find('[id="modal_placeInfo_courseShift"]').html('<h5>Turno: <small>' + val.shift+'</small></h5>').end()
					.find('[id="modal_placeInfo_courseDuration"]').html('<h5>Duração: <small>' + val.duration+'</small></h5>').end()
					.find('[id="modal_placeInfo_courseMinGrade"]').html('<h5>Graduação Mínima: <small>' +val.minimumGrade+'</small></h5>').end()
					.find('[id="modal_placeInfo_courseClasses"]').html('<h5>Turmas: <small>' +val.class+'</small></h5>').end()
					.find('[id="modal_placeInfo_courseCapacity"]').html('<h5>Capacidade: <small>' +val.capacity+'</small></h5>').end()
				.find('[id="modal_placeInfo_courseEnrollments"]').html('<h5>Matrículas: <small>' +val.enrollments+'</small></h5>').end();
				$clone
					.find('[id="modal_placeInfo_courseName"]').attr('id','modal_placeInfo_courseName'+courseInstancesDivsInfoWindow).end()
					.find('[id="modal_placeInfo_courseShift"]').attr('id','modal_placeInfo_courseShift'+courseInstancesDivsInfoWindow).end()
					.find('[id="modal_placeInfo_courseDuration"]').attr('id','modal_placeInfo_courseDuration'+courseInstancesDivsInfoWindow).end()
					.find('[id="modal_placeInfo_courseMinGrade"]').attr('id','modal_placeInfo_courseMinGrade'+courseInstancesDivsInfoWindow).end()
					.find('[id="modal_placeInfo_courseClasses"]').attr('id','modal_placeInfo_courseClasses'+courseInstancesDivsInfoWindow).end()
					.find('[id="modal_placeInfo_courseCapacity"]').attr('id','modal_placeInfo_courseCapacity'+courseInstancesDivsInfoWindow).end()
				.find('[id="modal_placeInfo_courseEnrollments"]').attr('id','modal_placeInfo_courseEnrollments'+courseInstancesDivsInfoWindow).end();
				courseInstancesDivsInfoWindow++;
			}
		});
		$('#modal_placeInfo').modal('show');
	}
	 
      
	 function showAllMarkers () {
		for (var i=0; i<gmarkers.length; i++) {
			gmarkers[i].setVisible(true);
			$('#left_menu #place'+gmarkers[i]['id']).removeClass("hide");
			jQuery.each(categoriesCounter.total, function(j, val) {
				$('#left_menu #total'+j).html('('+val+')');
			});
		}
	 
	 }
	function hideByCourses (courseId) {
		var tempLowerCaseCategory;
		var stringCourseId = '' + courseId;
		jQuery.each(categoriesCounter.current, function(i, val) {
			categoriesCounter.current[i] = 0;
		});
		for (var i=0; i<gmarkers.length; i++) {
			if (jQuery.inArray(stringCourseId,gmarkers[i]['coursesArray']) == -1) {
				gmarkers[i].setVisible(false);
				$('#left_menu #place'+gmarkers[i]['id']).addClass("hide");
			} else {
				tempLowerCaseCategory = gmarkers[i]['type'].toLowerCase();
				categoriesCounter['current'][tempLowerCaseCategory] = categoriesCounter['current'][tempLowerCaseCategory] + 1;
			}
		}
		console.dir(categoriesCounter);
		jQuery.each(categoriesCounter.current, function(j, val) {
			$('#left_menu #total'+j).html('('+val+')');
		});
		courseSelected = true;
	}
	 

		
     //TO DO: Ver quais funções ficam em global
	function updateAddressFields() {

		//TO DO: Ver jeito mais fácil de consertar isto
		//BUG: DEPOIS DE MOVER O PIN, DAR OK, ENTRAR NO FORM, SAIR DO FORM E CLICAR EM ADD SOMETHING O ENDEREÇO APARECE ERRADO
		//Reset values
		$('#add_address_street').val("");
		$('#add_address_number').val("");
		$('#add_address_neighborhood').val("");
		$('#add_address_city').val("");
		$('#add_address_state').val("");
		$('#add_address_postal_code').val("");



		$('#add_address_street').val(fullAddress.street_name);
		$('#add_address_number').val(fullAddress.street_number);
		$('#add_address_neighborhood').val(fullAddress.neighborhood);
		$('#add_address_city').val(fullAddress.city);
		$('#add_address_state').val(fullAddress.state);
		$('#add_address_postal_code').val(fullAddress.postal_code);
		$('#add_lat').val(insertedMark.getPosition().lat());
		$('#add_long').val(insertedMark.getPosition().lng());
	}
      
      
      
      
      //TO DO: consertar detecção de browser mobile e tablet (dois ifs desnecessários também)
      // detect browser agent
     $(document).ready(function(){
		if(navigator.userAgent.toLowerCase().indexOf("iphone") > -1 || navigator.userAgent.toLowerCase().indexOf("ipod") > -1) {
			agent = "iphone";
			zoomControl = false;
		}
		if(navigator.userAgent.toLowerCase().indexOf("ipad") > -1) {
			agent = "ipad";
			zoomControl = false;
		}
		$('[data-toggle="tooltip"]').tooltip();
		$('[data-toggle="popover"]').popover();

		//TO DO: Colocar autocomplete na inserção de pontos com google maps autocomplete + twitter typeahead
		//Set input size to placeholder text
		$("#input-address").attr('size', $("#input-address").attr('placeholder').length);
         
		$(document).keyup(function(e) {
			if(e.keyCode == 27) {
				google.maps.event.removeListener(googleMapsApiClickListener);
				$('.popover ').popover('hide');
				if (courseSelected) {
					showAllMarkers();
					$("#typeahead_seach #search").val("");
					courseSelected = false;
				}
			}
          });
		
		
		//TO DO: Colocar evento de click para reexibir todos os markers (tem que ter exceção do menu do lado, de markers, etc...). //workaround for click event not detecting correclty.
		/*$(document).mousedown(function(e) {
			isDragging = false;
		});
		
		$(document).mousemove(function(e) {
			isDragging = true;
		});
		
		$(document).mouseup(function(e) {
			if (!isDragging) {
				if (courseSelected) {
					//alert("keyESCpressCourseSelected");
					showAllMarkers();
					$("#typeahead_seach #search").val("");
					courseSelected = false;
				}
			}
		});*/
		
		
		$("#typeahead_seach #search").on("click", function(e) {
			if (courseSelected) {
				showAllMarkers();
				$("#typeahead_seach #search").val("");
				courseSelected = false;
			}
		
		});
        
         //TO DO: Não usar o pop over. Transformar o iput de achar endereço em input de adicionar ponto
         $('#add_button').popover({
			html : true,
			placement : "bottom",
			trigger: "click",
			container :"body",
			content: function() {
				return $("#address_dialog_body").html();
			}          
         });
         
               
		$('#add_button').on('click', function () {
			var googleMapsApiClickListener = google.maps.event.addListener(map, 'click', function(event) {
				placeNewAddressMarker(event.latLng,true);
			});
       
			$('.popover #newaddressOkButton').on('click', function (e) {
				if (typeof insertedMark == 'undefined') {
					if ($(".popover #input-address").val()) {
						geocode($(".popover #input-address").val());
						e.stopPropagation();
				
					} else {
						$(".popover #input-address").addClass("redPlaceHolder");
						$(".popover #input-address").focus();
						e.stopPropagation();
					}
				} else {
					updateAddressFields();
					$('.popover ').popover('hide');
				}
			});
          

			$(".popover #input-address").on("keypress", function(e) {
				if(e.which == 13) {
					geocode($(this).val());
				}
			});
			$(".popover #input-address").focus();
			
		});
         
		function placeNewAddressMarker(location,doReverseGeocode) {
			if ( insertedMark ) {
				insertedMark.setPosition(location);
			} else {
				insertedMark = new google.maps.Marker({
					position: location,
					draggable: true,
					map: map
				});
				google.maps.event.addListener(insertedMark, 'dragend', function () {
					var newPosition = insertedMark.getPosition();
					reverseGeocode(newPosition);
					map.setCenter(newPosition);
				});
			}
			map.setCenter(location);
			if (doReverseGeocode == true) {
				reverseGeocode(location);
			}
		}

		function updateAddressPopOver(updatedVal) {
			//*Workaround para problema de mudanca de valor de input dentro do popover!
			var element = document.getElementById("input-address");
			element.value=updatedVal;
			document.getElementById("input-address").setAttribute("value",element.value);
			$('#add_button').data('bs.popover').setContent();
			$('.popover #newaddressOkButton').on('click', function () {
				updateAddressFields();
				$('.popover ').popover('hide');
			});

			$(".popover #input-address").on("keypress", function(e) {
				if(e.which == 13) {
					geocode($(this).val());
				}
			});   
			//*Fim do workaround!
		}
        
		//LatLng to Address
		function reverseGeocode (latLng) {
			var geocoder;
			var address;
			geocoder = new google.maps.Geocoder();
			geocoder.geocode( { 'latLng': latLng}, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK) {
					var i;
					for (i=0;i< results[0].address_components.length; i++) {
						if (results[0].address_components[i].types[0] == "street_number") {
							fullAddress.street_number = results[0].address_components[i].long_name;
						}
						if (results[0].address_components[i].types[0] == "route") {
							fullAddress.street_name = results[0].address_components[i].long_name;
						}
						if (results[0].address_components[i].types[0] == "intersection") {
							fullAddress.street_name = results[0].address_components[i].long_name;
						}
						if (results[0].address_components[i].types[0] == "neighborhood") {
							fullAddress.neighborhood = results[0].address_components[i].long_name;
						}
						if (results[0].address_components[i].types[0] == "locality") {
							fullAddress.city= results[0].address_components[i].long_name;
						}
						if (results[0].address_components[i].types[0] == "administrative_area_level_1") {
							fullAddress.state = results[0].address_components[i].long_name;
						}
						if (results[0].address_components[i].types[0] == "country") {
							fullAddress.country = results[0].address_components[i].long_name;
						}
						if (results[0].address_components[i].types[0] == "postal_code") {
							fullAddress.postal_code = results[0].address_components[i].long_name;
						}
					}
					updateAddressPopOver(results[0].formatted_address);
					return results[0].formatted_address;
				} else {
					return false;
					alert("Geocode was not successful for the following reason: " + status);
					//alert("Problema na conversão do endereço em coordenadas geográficas. Clicar em 'Ok' e digitar manualmente. Detalhe: " + status);
				}
			});
		}
         
		function geocodeAndCenterMap (address) {
			var geocoder;
			var latitude;
			var longitude;
			var location;
			geocoder = new google.maps.Geocoder();
			geocoder.geocode( { 'address': address, location : googleGeocodeCentralLocation, 'componentRestrictions' : { 'country' : googleGeocodeCountryRestriction}}, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK) {
					map.panTo(results[0].geometry.location);
					map.setZoom(15);
				} else {
					return false;
					alert("Geocode was not successful for the following reason: " + status);
				}
			});
		}
       
		function geocode (address) {
			var geocoder;
			var latitude;
			var longitude;
			var location;
			geocoder = new google.maps.Geocoder();
			geocoder.geocode( { 'address': address, location : googleGeocodeCentralLocation, 'componentRestrictions' : { 'country' : googleGeocodeCountryRestriction}}, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK) {
					placeNewAddressMarker(results[0].geometry.location,false);
					var i;
					for (i=0;i< results[0].address_components.length; i++) {
						if (results[0].address_components[i].types[0] == "street_number") {
							fullAddress.street_number = results[0].address_components[i].long_name;
						}
						if (results[0].address_components[i].types[0] == "route") {
							fullAddress.street_name = results[0].address_components[i].long_name;
						}
						if (results[0].address_components[i].types[0] == "intersection") {
							fullAddress.street_name = results[0].address_components[i].long_name;
						}
						if (results[0].address_components[i].types[0] == "neighborhood") {
							fullAddress.neighborhood = results[0].address_components[i].long_name;
						}
						if (results[0].address_components[i].types[0] == "locality") {
							fullAddress.city= results[0].address_components[i].long_name;
						}
						if (results[0].address_components[i].types[0] == "administrative_area_level_1") {
							fullAddress.state = results[0].address_components[i].long_name;
						}
						if (results[0].address_components[i].types[0] == "country") {
							fullAddress.country = results[0].address_components[i].long_name;
						}
						if (results[0].address_components[i].types[0] == "postal_code") {
							fullAddress.postal_code = results[0].address_components[i].long_name;
						}
					}
				} else {
					return false;
					alert("Geocode was not successful for the following reason: " + status);
					//alert("Problema na decodificação do endereço. Favor tentar novamente clicando no mapa. Detalhe: " + status);
				}
			});
		}
       
       
       
       
	  
	  
	     $('#typeahead_seach .typeahead').typeahead({
			source: typeAheadJSON
		});
	   
	   
	   
		$('#typeahead_seach .typeahead').change(function() {
			var current = $('#typeahead_seach .typeahead').typeahead("getActive");
			if (current) {
				// Some item from your model is active!
				if (current.name.toLowerCase() == $('#typeahead_seach .typeahead').val().toLowerCase()) {
					typeAheadFound = true;
					// This means the exact match is found. Use toLowerCase() if you want case insensitive match.
					if (current.type == "local") {
						goToMarker(current.markerId);
					} else {
						if (current.type == "course") {
							hideByCourses(current.courseId);
						}
					}
				} else {
					//typeAheadFound: variable for workaround on event firing dispute between typeahead and google place search
					typeAheadFound = false;
				}
			} else {
				typeAheadFound = false;
				// Nothing is active so it is a new value (or maybe empty value)
			}
		});

	   
	   
	   	$("#typeahead_seach #search").on("keyup", function(e) {
			if (e.keyCode == 13) {
				if (!typeAheadFound) {
					geocodeAndCenterMap($('#typeahead_seach #search').val());
				}
			}
			if (e.keyCode == 8) {
				typeAheadFound = false;
				if (courseSelected) {
					showAllMarkers();
					courseSelected = false;
				}			
			}
		});
      
		$('#modal_add').on('hidden.bs.modal', function () {
			$("#modal_addform p").css("display", "block");
			$("#modal_addform fieldset").css("display", "block");
			$("#modal_addform .btn-primary").css("display", "block");
		});
       
       
       //Fim do Document.ready
     });

      
      

      // resize marker list onload/resize
     $(document).ready(function(){
		resizeList()
     });
     $(window).resize(function() {
		resizeList();
     });

      // resize marker list to fit window
     function resizeList() {
		newHeight = $('html').height() - $('#topbar').height();
		$('#list').css('height', newHeight + "px");
		$('#left_menu').css('margin-top', $('#topbar').height());
	     /*if ($('html').width() > 768) {
			//display: none;
			$('#left_menu').css('display', 'block');
		} else {
			$('#left_menu').css('display', 'none');
		}*/
     }


      // initialize map
     function initialize() {
		var temp_courses_array = [];
        // set map styles
        var mapStyles = [
         {
            featureType: "road",
            elementType: "geometry",
            stylers: [
              { hue: "#8800ff" },
              { lightness: 100 }
            ]
          },{
            featureType: "road",
            stylers: [
              { visibility: "on" },
              { hue: "#91ff00" },
              { saturation: -62 },
              { gamma: 1.98 },
              { lightness: 45 }
            ]
          },{
            featureType: "water",
            stylers: [
              { hue: "#005eff" },
              { gamma: 0.72 },
              { lightness: 42 }
            ]
          },{
            featureType: "transit.line",
            stylers: [
              { visibility: "off" }
            ]
          },{
            featureType: "administrative.locality",
            stylers: [
              { visibility: "on" }
            ]
          },{
            featureType: "administrative.neighborhood",
            elementType: "geometry",
            stylers: [
              { visibility: "simplified" }
            ]
          },{
            featureType: "landscape",
            stylers: [
              { visibility: "on" },
              { gamma: 0.41 },
              { lightness: 46 }
            ]
          },{
            featureType: "administrative.neighborhood",
            elementType: "labels.text",
            stylers: [
              { visibility: "on" },
              { saturation: 33 },
              { lightness: 20 }
            ]
          }
        ];

        // set map options
        var myOptions = {
          zoom: 9,
          //minZoom: 10,
          center: googleGeocodeCentralLocation,
          mapTypeId: google.maps.MapTypeId.ROADMAP,
          streetViewControl: false,
          mapTypeControl: false,
          panControl: false,
          zoomControl: zoomControl,
          styles: mapStyles,
          zoomControlOptions: {
            style: google.maps.ZoomControlStyle.SMALL,
            position: google.maps.ControlPosition.LEFT_CENTER
          }
        };
        map = new google.maps.Map(document.getElementById('map_canvas'), myOptions);
        zoomLevel = map.getZoom();

        // prepare infowindow
        infowindow = new google.maps.InfoWindow({
          content: "holding...",
		maxWidth: 300
        });
        
        // only show marker labels if zoomed in
        google.maps.event.addListener(map, 'zoom_changed', function() {
          zoomLevel = map.getZoom();
          if(zoomLevel <= 20) {
            $(".marker_label").css("display", "none");
          } else {
            $(".marker_label").css("display", "inline");
          }
        });

         <?php
		$typesFile = file_get_contents ($typesFileName);
          $types = json_decode($typesFile);
          $marker_id = 0;
		$typeAheadJsonPHP = "";
          foreach ($types->categories as $type) {
			$places_query = mysql_query(" SELECT p.id as place_id, p.title as place_name, p.inep as place_inep, 
				p.cnpj as place_cnpj, p.lat as place_lat, p.lng as place_lng, 
				p.address_street as place_address_street, 
				p.address_number as place_address_number, 
				p.address_neighborhood as place_address_neighborhood, 
				p.address_city as place_address_city, 
				p.address_state as place_address_state, 
				p.address_postal_code as place_address_postal_code, 
				p.address_telephone as place_address_telephone, 
				p.uri as place_uri, 
				p.description as place_description, 
				p.owner_name as place_owner_name, 
				p.owner_email as place_owner_email,
				ci.id as courseInstance_id,
				ci.shift as courseInstance_shift,
				ci.duration as courseInstance_duration,
				ci.class as courseInstance_class,
				ci.capacity as courseInstance_capacity,
				ci.enrollments as courseInstance_enrollments,
				c.id as course_id, 
				c.name as course_name, 
				c.category as course_category, 
				c.min_grade as course_minimumGrade 
			  FROM places p
			  LEFT JOIN courses_instances ci
			  ON (p.id = ci.placeid)
			  LEFT JOIN courses c
			  ON (ci.courseid = c.id)
			  WHERE p.approved='1' AND p.type='$type->name'");

			while($row = mysql_fetch_assoc($places_query)) {
				//icon
				$places[$row['place_id']]['icon'] = $type->icon;
				$places[$row['place_id']]['type'] = $type->name;
				$places[$row['place_id']]['id'] = htmlspecialchars_decode(addslashes(htmlspecialchars($row['place_id'])));
				$places[$row['place_id']]['name'] = htmlspecialchars_decode(addslashes(htmlspecialchars($row['place_name'])));
				$places[$row['place_id']]['inep'] = htmlspecialchars_decode(addslashes(htmlspecialchars($row['place_inep'])));
				$places[$row['place_id']]['cnpj'] = htmlspecialchars_decode(addslashes(htmlspecialchars($row['place_cnpj'])));
				$places[$row['place_id']]['lat'] = htmlspecialchars_decode(addslashes(htmlspecialchars($row['place_lat'])));
				$places[$row['place_id']]['lng'] = htmlspecialchars_decode(addslashes(htmlspecialchars($row['place_lng'])));
				$places[$row['place_id']]['address_street'] = htmlspecialchars_decode(addslashes(htmlspecialchars($row['place_address_street'])));
				$places[$row['place_id']]['address_number'] = htmlspecialchars_decode(addslashes(htmlspecialchars($row['place_address_number'])));
				$places[$row['place_id']]['address_neighborhood'] = htmlspecialchars_decode(addslashes(htmlspecialchars($row['place_address_neighborhood'])));
				$places[$row['place_id']]['address_city'] = htmlspecialchars_decode(addslashes(htmlspecialchars($row['place_address_city'])));
				$places[$row['place_id']]['address_state'] = htmlspecialchars_decode(addslashes(htmlspecialchars($row['place_address_state'])));
				$places[$row['place_id']]['address_postal_code'] = htmlspecialchars_decode(addslashes(htmlspecialchars($row['place_address_postal_code'])));
				$places[$row['place_id']]['address_telephone'] = htmlspecialchars_decode(addslashes(htmlspecialchars($row['place_address_telephone'])));
				$places[$row['place_id']]['uri'] = htmlspecialchars_decode(addslashes(htmlspecialchars($row['place_uri'])));
				$places[$row['place_id']]['description'] = htmlspecialchars_decode(addslashes(htmlspecialchars($row['place_description'])));
				$places[$row['place_id']]['owner_name'] = htmlspecialchars_decode(addslashes(htmlspecialchars($row['place_owner_name'])));
				$places[$row['place_id']]['owner_email'] =htmlspecialchars_decode(addslashes(htmlspecialchars( $row['place_owner_email'])));
				$places[$row['place_id']]['courses'][$row['course_id']]['name'] = htmlspecialchars_decode(addslashes(htmlspecialchars($row['course_name'])));
				$places[$row['place_id']]['courses'][$row['course_id']]['category'] = htmlspecialchars_decode(addslashes(htmlspecialchars($row['course_category'])));
				$places[$row['place_id']]['courses'][$row['course_id']]['minimumGrade'] = htmlspecialchars_decode(addslashes(htmlspecialchars($row['course_minimumGrade'])));
				$places[$row['place_id']]['coursesInstances'][$row['courseInstance_id']]['name'] = htmlspecialchars_decode(addslashes(htmlspecialchars($row['course_name'])));
				$places[$row['place_id']]['coursesInstances'][$row['courseInstance_id']]['category'] = htmlspecialchars_decode(addslashes(htmlspecialchars($row['course_category'])));
				$places[$row['place_id']]['coursesInstances'][$row['courseInstance_id']]['minimumGrade'] = htmlspecialchars_decode(addslashes(htmlspecialchars($row['course_minimumGrade'])));
				$places[$row['place_id']]['coursesInstances'][$row['courseInstance_id']]['shift'] = htmlspecialchars_decode(addslashes(htmlspecialchars($row['courseInstance_shift'])));
				$places[$row['place_id']]['coursesInstances'][$row['courseInstance_id']]['duration'] = htmlspecialchars_decode(addslashes(htmlspecialchars($row['courseInstance_duration'])));
				$places[$row['place_id']]['coursesInstances'][$row['courseInstance_id']]['class'] = htmlspecialchars_decode(addslashes(htmlspecialchars($row['courseInstance_class'])));
				$places[$row['place_id']]['coursesInstances'][$row['courseInstance_id']]['capacity'] = htmlspecialchars_decode(addslashes(htmlspecialchars($row['courseInstance_capacity'])));
				$places[$row['place_id']]['coursesInstances'][$row['courseInstance_id']]['enrollments'] = htmlspecialchars_decode(addslashes(htmlspecialchars($row['courseInstance_enrollments'])));
				
				
				
				$places[$row['place_id']]['complete_address'] = $places[$row['place_id']]['address_street'] . " " . $places[$row['place_id']]['address_number'] . ", " . $places[$row['place_id']]['address_neighborhood'] . ", " . $places[$row['place_id']]['address_city'] . " - " . $places[$row['place_id']]['address_postal_code'];
								
			}
			//$courses_query = "SELECT DISTINCT c.id as course_id, c.name as course_name, c.category as course_category
			//	FROM courses c, courses_instances ci 
			//WHERE c.id = ci.courseid ORDER BY c.name";
			//while($row = mysql_fetch_assoc($courses_query)) {
			//	//if (! in_array($typeAheadJsonPHP,'curso: '. $places[$row['place_id']]['courses'][$row['course_id']]['name'])) {
			//		$typeAheadJsonPHP[] = array('type' => "course", 'name' =>  'curso: '. htmlspecialchars_decode(addslashes(htmlspecialchars($row['course_id']))), "courseId" => htmlspecialchars_decode(addslashes(htmlspecialchars($row['course_name']))), 'category' => htmlspecialchars_decode(addslashes(htmlspecialchars($row['course_category']))) );
			//		//typeAheadJSON.push({"type" : "course", "courseId" : j, "name" : "curso: "+vall.name,"category" : vall.category});	
			//	//}
			//}
		 //print_r($typeAheadJsonPHP);
		 //print_r($places);
		 //exit;
		}
		$placesJson = json_encode($places);
		echo "placesJson = ".$placesJson;
				
        ?>
	   
	   
		var tempcount = 0
		jQuery.each(placesJson, function(i, val) {
			infowindow = new google.maps.InfoWindow({
				content: "",
				maxWidth: 300
			});
			//create typeahead JSON
			typeAheadJSON.push({"type" : "local","name" : val.name,"markerId" : val.id});

			// offset latlong ever so slightly to prevent marker overlap
			rand_x = Math.random();
			rand_y = Math.random();
			var newlat = parseFloat(val.lat) + parseFloat(parseFloat(rand_x) / 6000);
			var newlng = parseFloat(val.lng) + parseFloat(parseFloat(rand_y) / 6000);

			// show smaller marker icons on mobile
			//TO DO: consertar aqui após consertar detecção de browser mobile...
			if(agent == "iphone") {
				var iconSize = new google.maps.Size(16,19);
			} else {
				iconSize = null;
			}

			if (val.icon) {
				var markerImage = new google.maps.MarkerImage("./images/icons/"+val.icon+"", null, null, null, iconSize);
			} else {
				var markerImage = new google.maps.MarkerImage("./images/icons/<?php echo $defaultIcon; ?>", null, null, null, iconSize);
			}
			var marker = new google.maps.Marker({
				position: new google.maps.LatLng(newlat,newlng),
				map: map,
				title: '',
				clickable: true,
				infoWindowHtml: '',
				zIndex: 10 + tempcount,
				icon: markerImage
			});

			marker['type'] = val.type;
			marker['id'] = val.id;
			marker['coursesArray'] = [];
			var infoWindowCourses = "";
			var coursesCount = 0;
			jQuery.each(val.courses, function(j, vall) {
				coursesCount = coursesCount + 1;
				marker['coursesArray'].push(j);
				if (temp_courses_array.indexOf(j) == -1) {
					temp_courses_array.push(j);
					typeAheadJSON.push({"type" : "course", "courseId" : j, "name" : "curso: "+vall.name,"category" : vall.category});
				}
				if (coursesCount <= 3) {
					infoWindowCourses = infoWindowCourses + "<div class='mark_course_name'> - "+vall.name+" </div>";
				} 
				if (coursesCount == 4) {
					infoWindowCourses = infoWindowCourses + "<div class='mark_course_name'> - ... </div>";
				}
			});
			//console.dir(typeAheadJSON);

			gmarkers.push(marker);

			// add marker hover events (if not viewing on mobile)
			if(agent == "default") {
				google.maps.event.addListener(marker, "mouseover", function() {
					this.old_ZIndex = this.getZIndex();
					this.setZIndex(9999);
					$("#marker"+val.id).css("display", "inline");
					$("#marker"+val.id).css("z-index", "99999");
				});
				google.maps.event.addListener(marker, "mouseout", function() {
					if (this.old_ZIndex && zoomLevel <= 15) {
						this.setZIndex(this.old_ZIndex);
						$("#marker"+val.id).css("display", "none");
					}
				});
			}

			// format marker URI for display and linking
			var markerURI = val.uri;
			if(markerURI.substr(0,7) != "http://") {
				markerURI = "http://" + markerURI;
			}
			var markerURI_short = markerURI.replace("http://", "");
			var markerURI_short = markerURI_short.replace("www.", "");
			
			google.maps.event.addListener(marker, 'click', function () {
				infowindow.setContent(
					"<div class='marker_title'>"+val.name+"</div>"
					+ "<div class='marker_uri'><a target='_blank' href='"+markerURI+"'>"+markerURI_short+"</a></div>"
					+ "<div class='marker_address'>"+val.complete_address+". "+val.address_telephone+" </div>"
					+ "<hr>"
					+ "<div class='marker_courses'> Cursos:"
					+ infoWindowCourses
					+ "</div><br/>"
					+ "<div class='marker_viewmore'><a onclick='showModalPlaceInfo(\""+i+"\")'  href='#'>Mais...</a></div>"
				);
				infowindow.open(map, this);
			});

			// add marker label
			var latLng = new google.maps.LatLng(newlat, newlng);
			var label = new Label({
				map: map,
				id: val.id
			});
			label.bindTo('position', marker);
			label.set("text", val.name);
			label.bindTo('visible', marker);
			label.bindTo('clickable', marker);
			label.bindTo('zIndex', marker);
			tempcount++;
		});
		console.dir(typeAheadJSON);
     }


      // zoom to specific marker
     function goToMarker(marker_id) {
		if(marker_id) {
			for (var i=0; i<gmarkers.length; i++) {
				if (gmarkers[i].id == marker_id) {
					map.panTo(gmarkers[i].getPosition());
					map.setZoom(15);
					google.maps.event.trigger(gmarkers[i], 'click');
					return;
				}
			}
          
		}
     }

     // toggle (hide/show) markers of a given type (on the map)
     function toggle(type) {
		if($('#filter_'+type).is('.inactive')) {
			show(type);
		} else {
			hide(type);
		}
     }

     // hide all markers of a given type
     function hide(type) {
		for (var i=0; i<gmarkers.length; i++) {
			if (gmarkers[i].type == type) {
				gmarkers[i].setVisible(false);
			}
        }
        $("#filter_"+type).addClass("inactive");
     }


     // show all markers of a given type
     function show(type) {
		for (var i=0; i<gmarkers.length; i++) {
			if (gmarkers[i].type == type) {
				gmarkers[i].setVisible(true);
			}
		}
		$("#filter_"+type).removeClass("inactive");
	}

     // toggle (hide/show) marker list of a given type
     function toggleList(type) {
		$("#list .list-"+type).toggle();
     }

      
     // hover on list item
     function markerListMouseOver(marker_id) {
		$("#marker"+marker_id).css("display", "inline");
     }
	
     function markerListMouseOut(marker_id) {
		$("#marker"+marker_id).css("display", "none");
     }

     google.maps.event.addDomListener(window, 'load', initialize);
    </script>

    <?php echo $head_html; ?>
  </head>
  <body>

    <!-- display error overlay if something went wrong -->
    <?php if (isset($error)) {echo $error;} ?>

    <!-- facebook like button code 
    <div id="fb-root"></div>
   
    <script>
     TO DO: Alterar ícone/link do Facebook.
     /*(function(d, s, id) {
      var js, fjs = d.getElementsByTagName(s)[0];
      if (d.getElementById(id)) return;
      js = d.createElement(s); js.id = id;
      js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=421651897866629";
      fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));
   
    </script>-->

    <!-- google map -->
    <div id="map_canvas"></div>

    <!-- topbar -->
    <nav class="topbar navbar navbar-default navbar-fixed-top" id="topbar">
		<div class="container-fluid">
			<div class='navbar-header'>
				<!--TO DO: Alterar Logo -->
				<!--
				<a class="navbar-brand logo" href="./">
					<img src="images/logo.png" alt="" />
				</a>
				-->
			</div>
			<!--
			Deleted twitter and FB nvabar goes here
			-->
			<div class="nav navbar-nav navbar-left">
				<ul class="nav navbar-nav buttons">
					<li id="about_li-button" style="margin-left: 5px; margin-right: 5px;">
						<p class="navbar-btn">
							<a href="#modal_info" class="btn btn-large btn-info" data-toggle="modal"><span aria-hidden="true" class="glyphicon glyphicon-info-sign"></span>&nbsp;Sobre o Mapa</a>
						</p>
						
					</li>
					<li id="add_li-button" style="margin-left: 5px; margin-right: 5px;">
						<p class="navbar-btn">
							<a id="add_button" href="#" class="btn btn-large btn-success" role="button"><span aria-hidden="true" class="glyphicon glyphicon-plus-sign"></span>&nbsp;Adicionar</a>
							
						</p>
					</li>
					<li style="margin-left: 15px; max-width: 400px">
						<div class="search navbar-right navbar-form " id="typeahead_seach">
							<input style="width: 250px;" class="typeahead form-control search-query" type="text" name="search" id="search" data-provide="typeahead" data-toggle="tooltip" data-placement="right" title="Digitar nome de Escolas, Cursos ou Lugares" placeholder="Procurar..." autocomplete="off" />
						</div>
					</li>
				</ul>
			</div>
		</div>
	</nav>

    <!-- right-side gutter -->
    <div class="menu" id="left_menu">
      <ul class="list" id="list">
        <?php
          $marker_id = 0;
          foreach ($types->categories as $type) {
			$markers = mysql_query("SELECT * FROM places WHERE approved='1' AND type='$type->name' ORDER BY title");
			$markers_total = mysql_num_rows($markers);
			echo "
				<script>
				categoriesCounter['total']['".strtolower($type->name)."'] = ".$markers_total.";
				categoriesCounter['current']['".strtolower($type->name)."'] = ".$markers_total.";
				</script>
			";
			if ($type->icon) {
				echo "
					<li class='category'>
					<div class='category_item'>
					<div class='category_toggle' onClick=\"toggle('$type->name')\" id='filter_$type->name'></div>
					<a href='#' onClick=\"toggleList('$type->name');\" class='category_info'><img src='./images/icons/$type->icon' alt='' />$type->name<span id='total".strtolower($type->name)."' class='total'> ($markers_total)</span></a>
					</div>
					<ul class='list-items list-$type->name'>
				";
				while($marker = mysql_fetch_assoc($markers)) {
					echo "
						<li id='place".$marker['id']."' class='".$marker['type']."'>
						<a href='#' onMouseOver=\"markerListMouseOver('".$marker['id']."')\" onMouseOut=\"markerListMouseOut('".$marker['id']."')\" onClick=\"goToMarker('".$marker['id']."');\">".$marker['title']."</a>
						</li>
					";
					$marker_id++;
				}
				echo "
					</ul>
					</li>
				";
			} else {
				echo "
					<li class='category'>
					<div class='category_item'>
					<div data-customId= class='category_toggle' onClick=\"toggle('$type->name')\" id='filter_$type->name'></div>
					<a href='#' onClick=\"toggleList('$type->name');\" class='category_info'><img src='./images/icons/".$defaultIcon."' alt='' />$type->name<span id='total".strtolower($type->name)."' class='total'> ($markers_total)</span></a>
					</div>
					<ul class='list-items list-$type->name'>
				";
				while($marker = mysql_fetch_assoc($markers)) {
					echo "
						<li id='place".$marker['id']."' class='".$marker['type']."'>
						<a href='#' onMouseOver=\"markerListMouseOver('".$marker['id']."')\" onMouseOut=\"markerListMouseOut('".$marker['id']."')\" onClick=\"goToMarker('".$marker['id']."');\">".$marker['title']."</a>
						</li>
					";
					$marker_id++;
				}
				echo "
					</ul>
					</li>
				";
			}
          }
        ?>
		<li class="blurb"><?php echo $blurb; ?></li>
		<li class="attribution">
			<!-- per Represent.Map license, you may not remove this line -->
			<?php echo $attribution;?>
		</li>
      </ul>
    </div>

    

    
    
    
    
    
    <!-- more info modal -->
	<div class="modal fade" id="modal_info" tabindex="-1" role="dialog" aria-labelledby="aboutModal">
		<div class="modal-dialog" role="document">
			 <div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">×</button>
					<h3>Sobre o Mapa</h3>
				</div>
				<div class="modal-body">
					<p>
						Este mapa foi construído com o objetivo de mapear os cursos técnicos oferecidos no Estado.
						##inserir dados da pesquisa##
						##Pedir colaboração##
						##Melhorias a serem implantadas##

						Este mapa é uma iniciativa do ______________________ em parceria com:
						##Logos ##
						##Parcerias##

						## GITHUB DO CÓDIGO ##
					</p>
					<p>
						Perguntas, Críticas, Sugestões???
						##Colocar form de e-mail e/ou link##
					</p>

					<!-- TO DO: Ver estes Badges
					<ul class="badges">
						<li>
							<img src="./images/badges/badge1.png" alt="">
						</li>
						<li>
							<img src="./images/badges/badge1_small.png" alt="">
						</li>
						<li>
							<img src="./images/badges/badge2.png" alt="">
						</li>
						<li>
							<img src="./images/badges/badge2_small.png" alt="">
						</li>
					</ul>
					-->
					<!--<p>
					This map was built with <a href="https://github.com/abenzer/represent-map">RepresentMap</a> - an open source project we started
					to help startup communities around the world create their own maps.
					Check out some <a target="_blank" href="http://www.representmap.com">startup maps</a> built by other communities!
					</p>
					-->
				</div>
				<div class="modal-footer">
					<a href="#" class="btn" data-dismiss="modal" style="float: right;">Close</a>
				</div>
			</div>
		</div>
	</div>



   
    <div id="address_dialog_body" class="form-inline hide">
		<div id="address_formgroup" class="form-group">
			<label class="sr-only" for="input-address">Inserir novo local</label>
			<div class="input-group">
				<input type="text" class="form-control" name="preaddress_name" data-toggle="tooltip" data-placement="bottom" title="Clique no mapa e arraste o marcador ou digite o endereço e aperte 'Enter'" id="input-address" placeholder="Clique no mapa ou digite endereço" value="">
				<span class="input-group-btn">
					<a id="newaddressOkButton" href="#" data-toggle="modal" class="btn btn-large btn-success" role="button" data-target="#modal_add">ok</a>
				</span>
			</div>
		</div>
     </div>
    
 
	<script>
       

     //****TO DO:*** Validar campos do form em javascript. Ver quais não são obrigatórios.
     
	// add modal form submit	 
	function submitFormValidation (event) {
		event.preventDefault();
		var $form =  $("#modal_addform"),
		owner_name = $form.find( '#add_owner_name' ).val(),
		owner_email = $form.find( '#add_owner_email' ).val(),
		title = $form.find( '#add_title' ).val(),
		type = $form.find( '#add_type' ).val(),
		inep = $form.find( '#add_inep' ).val(),
		cnpj = $form.find( '#add_cnpj' ).val(),
		address_street = $form.find( '#add_address_street' ).val(),
		address_number = $form.find( '#add_address_number' ).val(),
		address_neighborhood = $form.find( '#add_address_neighborhood' ).val(),
		address_city = $form.find( '#add_address_city' ).val(),
		address_state = $form.find( '#add_address_state' ).val(),
		address_postal_code = $form.find( '#add_address_postal_code' ).val(),
		address_telephone = $form.find( '#add_address_telephone' ).val(),
		lat = $form.find( '#add_lat' ).val(),
		lng = $form.find( '#add_long' ).val(),
		uri = $form.find( '#add_uri' ).val(),
		description = $form.find( '#add_description' ).val(),
		url = $form.attr( 'action' );
        
		// send data and get results
		$.post( url, { owner_name : owner_name, owner_email : owner_email, title : title, type : type, inep : inep, cnpj : cnpj, address_street : address_street, address_number : address_number, address_neighborhood : address_neighborhood, address_city : address_city, address_state :   address_state, address_postal_code : address_postal_code, address_telephone : address_telephone, lat : lat, lng : lng, uri : uri, description : description, url :  url },
			function( data ) {
				var content = $( data ).find( '#content' );

				// if submission was successful, show info alert
				if($.trim(data) == "success") {
					//alert('success');
					$("#modal_addform #result").html("Recebemos sua solicitação, iremos revisar e retornar em breve. Obrigado!");
					$("#modal_addform #result").addClass("alert alert-info");
					$("#modal_addform p").css("display", "none");
					$("#modal_addform fieldset").css("display", "none");
					$("#modal_addform .btn-primary").css("display", "none");

					// if submission failed, show error
				} else {
					//alert('not success'+data);
					$("#modal_addform #result").html(data);
					$("#modal_addform #result").addClass("alert alert-danger");
					$("#modal_addform p").css("display", "none");
					$("#modal_addform fieldset").css("display", "none");
					$("#modal_addform .btn-primary").css("display", "none");
				}
			}
		);
	
	}
    </script>


    
    
    
    
    
    <!-- place modal -->
     <div class="modal fade" id="modal_placeInfo" tabindex="-1" role="dialog" aria-labelledby="placeInfoModal">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">×</button>
					<h4 class="modal-title" id="placeInfoModal">Informações Sobre Lugar</h4>
					
				</div>
				<div class="modal-body" style="width:90%; position: relative; left: 2%;">
					<div class="form-group">
						<div class="row">
							<div class="col-xs-8"> 
								<div id="modal_placeInfo_name"></div>
							</div>
							<div class="col-xs-4">
								<div id="modal_placeInfo_inep"></div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-12">
								<div id="modal_placeInfo_url"></div>
							</div>
						</div>
						<div class="row">
							<div class="col-xs-8">
								<div id="modal_placeInfo_address"></div>
							</div>
							<div class="col-xs-4">
								<div id="modal_placeInfo_telephone"></div>
							</div>
						</div>
					</div>
					<hr>
					<fieldset id="coursesTemplate" class="categories hide">
						<fieldset class="courses">
							<div class="form-group">
								<div class="row">
									<div class="col-xs-12">
										<div id="modal_placeInfo_courseName"></div>
									</div>
								</div>
								<div class="row">
									<div class="col-xs-9">
										<div id="modal_placeInfo_courseMinGrade"></div>
									</div>
									<div class="col-xs-3">
										<div id="modal_placeInfo_courseShift"></div>
									</div>
								</div>
								<div class="row">
									<div class="col-xs-3">
										<div id="modal_placeInfo_courseDuration"></div>
									</div>
									<div class="col-xs-3">
										<div id="modal_placeInfo_courseClasses"></div>
									</div>
									<div class="col-xs-3">
										<div id="modal_placeInfo_courseCapacity"></div>
									</div>
									<div class="col-xs-3">
										<div id="modal_placeInfo_courseEnrollments"></div>
									</div>
								</div>
							</div>
						</fieldset>
					</fieldset>
				</div>
				<div class="modal-footer">
					<a href="#" class="btn" data-dismiss="modal" style="float: right;">Fechar</a>
				</div>
			</div>
		</div>
	</div>
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
     <!-- add something modal -->
    <div class="modal fade" id="modal_add" tabindex="-1" role="dialog" aria-labelledby="newPointModal">
     <div class="modal-dialog" role="document">
       <div class="modal-content">
       <form action="add.php" id="modal_addform"  role="form" method="post">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">×</button>
          <h3 class="modal-title" id="newPointModal">Adicione a sua instituição!</h3>
        </div>
        <div class="modal-body" style="width:90%; position: relative; left: 2%;">
          <div id="result"></div>
		
		<!--TO DO: Colocar validador de forms-->
		<fieldset>
		<div class="form-group">
			<div class="row">
				<div class="col-xs-6">
					<label class="control-label" for="add_owner_name" >Seu Nome</label>
					<input type="text" class="form-control" data-toggle="tooltip" data-placement="bottom" title="Seu nome (responsável pelo cadastro)" name="add_owner_name" id="add_owner_name"  />
				</div>

				<div class="col-xs-6">
					<label class="control-label" for="add_owner_email">Seu E-mail</label>
					<input type="text" class="form-control" data-toggle="tooltip" data-placement="bottom" title="Seu endereço de e-mail" name="add_owner_email" id="add_owner_email" />
				</div>
			</div>
		</div>
		<hr>
		<div class="form-group">
			<div class="row">
				<div class="col-xs-8">
					<label class="control-label" for="add_title" >Instituição de Ensino</label>
					<input type="text" class="form-control" name="add_title" data-toggle="tooltip" data-placement="bottom" title="Nome da instituição de ensino" id="add_title"  />
				</div>

				<div class="col-xs-4">
					<label class="control-label" for="add_type">Tipo</label>
					<select class="form-control" name="add_type" id="add_type" >
						<?php
							foreach ($types->categories as $type) {
								echo '<option value="'.$type->name.'">'.$type->name.'</option>';
							}
						?>
					</select>
				</div>
			</div>
		</div>
		<div class="form-group">
			<div class="row">
				<div class="col-xs-8">
					<label class="control-label" for="add_inep" >INEP</label>
					<input type="text" class="form-control" data-toggle="tooltip" data-placement="bottom" title="Código INEP da instituição" name="add_inep" id="add_inep"  />
				</div>

				<div class="col-xs-4">
					<label class="control-label" for="add_cnpj">CNPJ</label>
					<input type="text" class="form-control" data-toggle="tooltip" data-placement="bottom" title="CNPJ da instituição" name="add_cnpj" id="add_cnpj"  />
				</div>
			</div>
		</div>
		<hr>
		<div class="form-group">
			<div class="row">
				<div class="col-xs-6">
					<label class="control-label" for="add_address_street">Rua</label>
					<input type="text" class="form-control" data-toggle="tooltip" data-placement="bottom" title="Nome completo da rua da instituição" name="add_address_street" id="add_address_street"  />
				</div>

				<div class="col-xs-2">
					<label class="control-label" for="add_address_number">Nº</label>
					<input type="text" class="form-control" name="add_address_number" data-toggle="tooltip" data-placement="bottom" title="Número" id="add_address_number" />
				</div>
				<div class="col-xs-4">
					<label class="control-label" for="add_address_neighborhood">Bairro</label>
					<input type="text" class="form-control" name="add_address_neighborhood" data-toggle="tooltip" data-placement="bottom" title="Bairro" id="add_address_neighborhood" />
				</div>
			</div>
		</div>
		
		<div class="form-group">
			<div class="row">
				<div class="col-xs-5">
					<label class="control-label" for="add_address_city" >Cidade</label>
					<input type="text" class="form-control" name="add_address_city" data-toggle="tooltip" data-placement="bottom" title="Nome completo da cidade" id="add_address_city"  />
				</div>

				<div class="col-xs-3">
					<label class="control-label" for="add_address_postal_code">CEP</label>
					<input type="text" class="form-control" name="add_address_postal_code" data-toggle="tooltip" data-placement="bottom" title="CEP - Código de Endereçamento Postal" id="add_address_postal_code" />
				</div>
				<div class="col-xs-4">
					<label class="control-label" for="add_address_telephone">Telefone</label>
					<input type="text" class="form-control" name="add_address_telephone" id="add_address_telephone" />
				</div>
			</div>
			<div class="row">
				<div class="col-xs-12">
					<label class="control-label" for="add_uri" >URL</label>
					<input type="text" class="form-control" name="add_uri" data-toggle="tooltip" data-placement="bottom" title="Endereço da página web da instituição" id="add_uri"  />
				</div>
			</div>
			<!--##########################
			-->
			<input type="text" class="form-control hide" name="add_address_state" id="add_address_state" value="Espírito Santo" autocomplete="off">
		</div>
		
		<div class="form-group">
			<div class="row">
				<div class="col-xs-6">
					<label class="control-label" for="add_lat" >Latitude</label>
					<input type="text" class="form-control" data-toggle="tooltip" data-placement="bottom" title="Latitude do local da instituição (só use se precisar)" name="add_lat" id="add_lat"  />
				</div>

				<div class="col-xs-6">
					<label class="control-label" for="add_long">Longitude</label>
					<input type="text" class="form-control" data-toggle="tooltip" data-placement="bottom" title="Longitude do local da instituição (só use se precisar)" name="add_long" id="add_long" />
				</div>
			</div>
		</div>
		<hr>
		<div class="form-group">
			<div class="row">
				<div class="col-xs-12">
					<label class="control-label" for="add_description" >Observações</label>
					<input type="text" class="form-control" data-toggle="tooltip" data-placement="bottom" title="Insira aqui qualquer observação" id="add_description" name="add_description"  />
				</div>
			</div>
		</div>
		
		</fieldset>
        </div>
        <div class="modal-footer">
          <button id="submitFormButton"  onclick="submitFormValidation(event)" class="btn btn-primary">Enviar para Revisão</button>
          <a href="#" class="btn" data-dismiss="modal" style="float: right;">Fechar</a>
        </div>
        </form>
      </div>
      </div>
    </div>

  </body>
</html>
