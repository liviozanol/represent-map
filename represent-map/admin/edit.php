<?php
include "header.php";
//$numberOfExistingCourses = 0;

if(isset($_GET['place_id'])) {
  $place_id = htmlspecialchars($_GET['place_id']); 
} else if(isset($_POST['place_id'])) {
  $place_id = htmlspecialchars($_POST['place_id']);
} else {
  exit; 
}


// get place info
$place_query = mysql_query("SELECT * FROM places WHERE id='$place_id' LIMIT 1");
if(mysql_num_rows($place_query) != 1) { exit; }
$place = mysql_fetch_assoc($place_query);

// get ALL courses info
$courses_query = mysql_query("SELECT * FROM courses ORDER BY name");
//if(mysql_num_rows($courses_query) != 1) { exit; }
while(($courses[] = mysql_fetch_assoc($courses_query)) || array_pop($courses));
//while(($resultArray[] = mysql_fetch_assoc($courses_query)) || array_pop($resultArray)); 
//echo "a";
//print_r($courses);
//exit;


// get ALL courses category
$coursesCategory_query = mysql_query("SELECT DISTINCT category FROM courses");
while(($coursesCategories[] = mysql_fetch_assoc($coursesCategory_query)) || array_pop($coursesCategorie));


// get courses from place
$coursesInstances_query = mysql_query("SELECT * FROM courses_instances WHERE placeid='$place_id'");
while(($coursesInstances[] = mysql_fetch_assoc($coursesInstances_query)) || array_pop($coursesInstances));
foreach ($coursesInstances as $key => $value) {
	$courseNameAndCategory = mysql_query("SELECT name,category,min_grade FROM courses WHERE id='".$value['courseid']."'");
	$tempVarCourseName = mysql_fetch_assoc($courseNameAndCategory);
	$coursesInstances[$key]['name'] = $tempVarCourseName['name'];
	$coursesInstances[$key]['category'] =  $tempVarCourseName['category'];
	$coursesInstances[$key]['mingrade'] =  $tempVarCourseName['min_grade'];
	//$value['name'] = $tempVarCourseName['name'];
	//$value['category'] = $tempVarCourseName['category'];
	//$coursesInstances[] = $coursesInstances;
}

//print_r ($coursesInstances);
//exit;

//print_r($coursesCategories);





function parseInput($value) {
  $value = htmlspecialchars($value, ENT_QUOTES);
  $value = str_replace("\r", "", $value);
  $value = str_replace("\n", "", $value);
  return $value;
}





// do place edit if requested
$task = "";
if (isset($_POST['task'])) {
	$task = parseInput($_POST['task']);
}
///$task = "doedit";

if($task == "doedit") {
	//echo "doEdit";
	//print_r($_POST);
	//trigger_error("Cannot divide by zero#####".print_r($_POST), E_USER_ERROR);
 $owner_name = mysql_real_escape_string(parseInput($_POST['add_owner_name']));
 $owner_email = mysql_real_escape_string(parseInput($_POST['add_owner_email']));
 $title = mysql_real_escape_string(parseInput($_POST['add_title']));
 $type = mysql_real_escape_string(parseInput($_POST['add_type']));
 $inep = mysql_real_escape_string(parseInput($_POST['add_inep']));
 $cnpj = mysql_real_escape_string(parseInput($_POST['add_cnpj']));
 $address_street = mysql_real_escape_string(parseInput($_POST['add_address_street']));
 $address_number = mysql_real_escape_string(parseInput($_POST['add_address_number']));
 $address_neighborhood = mysql_real_escape_string(parseInput($_POST['add_address_neighborhood']));
 $address_city = mysql_real_escape_string(parseInput($_POST['add_address_city']));
 $address_state = mysql_real_escape_string(parseInput($_POST['add_address_state']));
 $address_postal_code = mysql_real_escape_string(parseInput($_POST['add_address_postal_code']));
 $address_telephone = mysql_real_escape_string(parseInput($_POST['add_address_telephone']));
 $lat = mysql_real_escape_string(parseInput($_POST['add_lat']));
 $lng = mysql_real_escape_string(parseInput($_POST['add_long']));
 $uri = mysql_real_escape_string(parseInput($_POST['add_uri']));
 $description = mysql_real_escape_string(parseInput($_POST['add_description']));
  //echo "antes query";
  mysql_query("UPDATE places SET 
	  title='$title', type='$type', inep='$inep', cnpj='$cnpj', lat='$lat', lng='$lng', 
	  uri = '$uri', address_street='$address_street', address_number='$address_number', 
	  address_neighborhood='$address_neighborhood', address_city='$address_city', 
	  address_state='$address_state', address_postal_code='$address_postal_code', 
	  address_telephone='$address_telephone', description='$description', 
	  owner_name='$owner_name', owner_email='$owner_email' 
  WHERE id='$place_id' LIMIT 1") or die(mysql_error());
  
  //$coursesIterator = 1;
  //echo parseInput($_POST['course']);
  //print_r($_POST);
  //echo"porra";
 
  foreach ($_POST['course'] as $course) {
	if (isset ($course['course_name']) && $course['course_name'] != "") {
		$courseInstance_bdId = mysql_real_escape_string(parseInput($course['courseInstance_bdId']));
		$courseName = mysql_real_escape_string(parseInput($course['course_name']));
		$courseCategory = mysql_real_escape_string(parseInput($course['course_type']));
		$courseShift = mysql_real_escape_string(parseInput($course['course_shift']));
		$courseDuration = mysql_real_escape_string(parseInput($course['course_duration']));
		$courseClass = mysql_real_escape_string(parseInput($course['course_class']));
		$courseCapacity = mysql_real_escape_string(parseInput($course['course_capacity']));
		$courseEnrollments = mysql_real_escape_string(parseInput($course['course_enrollments']));
		
		
		$existingCourseQuery = mysql_query("SELECT id FROM courses WHERE name = '$courseName' LIMIT 1") or die(mysql_error());
		//Check if course name Exists
		if(mysql_num_rows($existingCourseQuery) == 0) {
			mysql_query("INSERT INTO courses
				(name,category)
				VALUES
				('$courseName','$courseCategory')
			") or die(mysql_error());
			$courseId = mysql_insert_id();
		} else {
			$tempvarcur = mysql_fetch_assoc($existingCourseQuery);
			$courseId = $tempvarcur['id'];
		}			
		if ($courseInstance_bdId) {
			//UPDATE
			mysql_query("UPDATE courses_instances SET
				courseid='$courseId',placeid='$place_id',shift='$courseShift',duration='$courseDuration'
				,class='$courseClass',capacity='$courseCapacity',enrollments='$courseEnrollments'
			WHERE id='$courseInstance_bdId' LIMIT 1
			") or die(mysql_error());
		} else {
			//INSERT  
			mysql_query("INSERT INTO courses_instances
				(courseid,placeid,shift,duration,class,capacity,enrollments)
				VALUES
				('$courseId','$place_id','$courseShift','$courseDuration','$courseClass','$courseCapacity','$courseEnrollments')
			") or die(mysql_error());
		}
		//$tempvarcur
	}
  }
   //exit;
  
  
  
  
  
  
  
  
  
  
  
  //echo "depois query";
  // geocode
  //$hide_geocode_output = true;
  //include "../geocode.php";
  //echo "SADFAS";
  header("Location: index.php?view=$view&search=$search&p=$p");
  exit;
}


$typesFile = file_get_contents ("../".$typesFileName);
$types = json_decode($typesFile);

?>



<?php echo $admin_head; ?>

<form id="admin" class="form-horizontal" action="edit.php" role="form" method="post">
  <h1>
    Edit Place
  </h1>
  <fieldset>
		<div class="form-group">
			<div class="row">
				<div class="col-xs-6">
					<label class="control-label" for="add_owner_name" >Nome</label>
					<input type="text" class="form-control" data-toggle="tooltip" data-placement="bottom" title="Nome do responsável pelo cadastro" name="add_owner_name" id="add_owner_name"  value="<?php echo $place['owner_name']?>"/>
				</div>

				<div class="col-xs-6">
					<label class="control-label" for="add_owner_email">E-mail</label>
					<input type="text" class="form-control" data-toggle="tooltip" data-placement="bottom" title="Endereço de e-mail do responsável pelo cadatro" name="add_owner_email" id="add_owner_email" value="<?php echo $place['owner_email']?>"/>
				</div>
			</div>
		</div>
		<hr>
		<div class="form-group">
			<div class="row">
				<div class="col-xs-8">
					<label class="control-label" for="add_title" >Instituição de Ensino</label>
					<input type="text" class="form-control" name="add_title" data-toggle="tooltip" data-placement="bottom" title="Nome da instituição de ensino" id="add_title" value="<?php echo $place['title']?>" />
				</div>

				<div class="col-xs-4">
					<label class="control-label" for="add_type">Tipo</label>
					<select class="form-control" name="add_type" id="add_type" >
						<?php
						   foreach ($types->categories as $type) {
						    if ($place['type'] == $type->name) {
							echo '<option value="'.$type->name.'" selected="selected">'.$type->name.'</option>';
						    } else {
							    if ($type->name) {
									echo '<option value="'.$type->name.'">'.$type->name.'</option>';
							    }
						    }
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
					<input type="text" class="form-control" data-toggle="tooltip" data-placement="bottom" title="Código INEP da instituição" name="add_inep" id="add_inep"  value="<?php echo $place['inep']?>"/>
				</div>

				<div class="col-xs-4">
					<label class="control-label" for="add_cnpj">CNPJ</label>
					<input type="text" class="form-control" data-toggle="tooltip" data-placement="bottom" title="CNPJ da instituição" name="add_cnpj" id="add_cnpj" value="<?php echo $place['cnpj']?>" />
				</div>
			</div>
		</div>
		<hr>
		<div class="form-group">
			<div class="row">
				<div class="col-xs-6">
					<label class="control-label" for="add_address_street">Rua</label>
					<input type="text" class="form-control" data-toggle="tooltip" data-placement="bottom" title="Nome completo da rua da instituição" name="add_address_street" id="add_address_street" value="<?php echo $place['address_street']?>" />
				</div>

				<div class="col-xs-2">
					<label class="control-label" for="add_address_number">Nº</label>
					<input type="text" class="form-control" name="add_address_number" data-toggle="tooltip" data-placement="bottom" title="Número" id="add_address_number" value="<?php echo $place['address_number']?>"/>
				</div>
				<div class="col-xs-4">
					<label class="control-label" for="add_address_neighborhood">Bairro</label>
					<input type="text" class="form-control" name="add_address_neighborhood" data-toggle="tooltip" data-placement="bottom" title="Bairro" id="add_address_neighborhood" value="<?php echo $place['address_neighborhood']?>"/>
				</div>
			</div>
		</div>
		
		<div class="form-group">
			<div class="row">
				<div class="col-xs-5">
					<label class="control-label" for="add_address_city" >Cidade</label>
					<input type="text" class="form-control" name="add_address_city" data-toggle="tooltip" data-placement="bottom" title="Nome completo da cidade" id="add_address_city" value="<?php echo $place['address_city']?>" />
				</div>

				<div class="col-xs-3">
					<label class="control-label" for="add_address_postal_code">CEP</label>
					<input type="text" class="form-control" name="add_address_postal_code" data-toggle="tooltip" data-placement="bottom" title="CEP - Código de Endereçamento Postal" id="add_address_postal_code" value="<?php echo $place['address_postal_code']?>" />
				</div>
				<div class="col-xs-4">
					<label class="control-label" for="add_address_telephone">Telefone</label>
					<input type="text" class="form-control" name="add_address_telephone" id="add_address_telephone" value="<?php echo $place['address_telephone']?>"/>
				</div>
			</div>
			<div class="row">
				<div class="col-xs-12">
					<label class="control-label" for="add_uri" >URL</label>
					<input type="text" class="form-control" name="add_uri" data-toggle="tooltip" data-placement="bottom" title="Endereço da página web da instituição" id="add_uri" value="<?php echo $place['uri']?>" />
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
					<input type="text" class="form-control" data-toggle="tooltip" data-placement="bottom" title="Latitude do local da instituição (só use se precisar)" name="add_lat" id="add_lat" value="<?php echo $place['lat']?>" />
				</div>

				<div class="col-xs-6">
					<label class="control-label" for="add_long">Longitude</label>
					<input type="text" class="form-control" data-toggle="tooltip" data-placement="bottom" title="Longitude do local da instituição (só use se precisar)" name="add_long" id="add_long" value="<?php echo $place['lng']?>"/>
				</div>
			</div>
		</div>
		<hr>
		<div class="form-group">
			<div class="row">
				<div class="col-xs-12">
					<label class="control-label" for="add_description" >Observações</label>
					<input type="text" class="form-control" data-toggle="tooltip" data-placement="bottom" title="Insira aqui qualquer observação" id="add_description" name="add_description" value="<?php echo $place['description']?>" />
				</div>
			</div>
		</div>
  

		<div class="form-group">
			<div class="row">
				<div class="col-xs-12">
					<label class="control-label">Location</label>
					<div id="map" style="width:100%;height:300px;">
					</div>
					<script type="text/javascript">
						var map = new google.maps.Map( document.getElementById('map'), {
							zoom: 17,
							center: new google.maps.LatLng( <?php echo $place['lat']?>, <?php echo $place['lng']?> ),
							mapTypeId: google.maps.MapTypeId.ROADMAP,
							streetViewControl: false,
							mapTypeControl: false
						});
						var marker = new google.maps.Marker({
							position: new google.maps.LatLng( <?php echo $place['lat']?>, <?php echo $place['lng']?> ),
							map: map,
							draggable: true
						});
						google.maps.event.addListener(marker, 'dragend', function(e){
							document.getElementById('add_lat').value = e.latLng.lat().toFixed(6);
							document.getElementById('add_long').value = e.latLng.lng().toFixed(6);
						});
					</script>
				</div>
			</div>
		</div>
		<hr>
		<h3>Cursos</h3>
		<hr>
		<?php
			$tempCourseInstanceIndex = 1;
			foreach ($coursesInstances as $coursesInstance) {
				?>
				<fieldset class="courseFieldset">
				<h4>
					Curso <?php echo $tempCourseInstanceIndex;?>
					<button onclick="removeCourse($(this),<?php echo $coursesInstance['id'];?>)" class="btn btn-danger btn-remove" type="button" data-toggle="tooltip" data-placement="bottom" data-original-title="Clique para remover este curso"> <span class="glyphicon glyphicon-remove"></span> </button>
				</h4>
				<div class="hide">
					<input type="text" class="form-control hide"  id="course[<?php echo $tempCourseInstanceIndex;?>][courseInstance_bdId]" name="course[<?php echo $tempCourseInstanceIndex;?>][courseInstance_bdId]" value="<?php echo $coursesInstance['id']?>" />
				</div>
				<div class="form-group">
					<div class="row">
						<div class="col-xs-8">
							<label class="control-label" for="course[<?php echo $tempCourseInstanceIndex;?>][course_name]">Curso</label>
							<input type="text" class="form-control typeahead" data-provide="typeahead" data-toggle="tooltip" data-placement="bottom" title="Nome do Curso" id="course[<?php echo $tempCourseInstanceIndex;?>][course_name]" name="course[<?php echo $tempCourseInstanceIndex;?>][course_name]" value="<?php echo $coursesInstance['name']?>" autocomplete="off" />
						</div>
						<div class="col-xs-4">
							<label class="control-label" for="course[<?php echo $tempCourseInstanceIndex;?>][course_type]">Categoria</label>
							<select class="form-control" data-toggle="tooltip" data-placement="bottom" title="Selecione a categoria do curso" name="course[<?php echo $tempCourseInstanceIndex;?>][course_type]" id="course[<?php echo $tempCourseInstanceIndex;?>][course_type]" >
								<?php
									foreach ($coursesCategories as $courseCategory) {
											//print ($courseCategory['category']);
									    if ($coursesInstance['category'] == $courseCategory['category']) {
											echo '<option value="'.$courseCategory['category'].'" selected="selected">'.$courseCategory['category'].'</option>';
										} else {
											if ($courseCategory['category']) {
												echo '<option value="'.$courseCategory['category'].'">'.$courseCategory['category'].'</option>';
											}
										}
									}
								?>
							</select>
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="row">
						<div class="col-xs-8">
							<label class="control-label" for="course[<?php echo $tempCourseInstanceIndex;?>][course_mingrade]" >Graduação Mínima</label>
							<input type="text" class="form-control" data-toggle="tooltip" data-placement="bottom" title="Graduação Mínima Necessária para Fazer o Curso" id="course[<?php echo $tempCourseInstanceIndex;?>][course_mingrade]" name="course[<?php echo $tempCourseInstanceIndex;?>][course_mingrade]" value="<?php echo $coursesInstance['mingrade']?>" autocomplete="off" />
						</div>
						<div class="col-xs-4">
							<label class="control-label" for="course[<?php echo $tempCourseInstanceIndex;?>][course_shift]">Turno</label>
							<select class="form-control" data-toggle="tooltip" data-placement="bottom" title="Selecione o turno do curso" name="course[<?php echo $tempCourseInstanceIndex;?>][course_shift]" id="course[<?php echo $tempCourseInstanceIndex;?>][course_shift]">
								<option value="Matutino" <?php if ($coursesInstance['shift'] == 'Matutino') {echo 'selected="selected"';}?>>Matutino</option>
								<option value="Vespertino" <?php if ($coursesInstance['shift'] == 'Vespertino') {echo 'selected="selected"';}?>>Vespertino</option>
								<option value="Noturno" <?php if ($coursesInstance['shift'] == 'Noturno') {echo 'selected="selected"';}?>>Noturno</option>
							</select>
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="row">
						<div class="col-xs-3">
							<label class="control-label" for="course[<?php echo $tempCourseInstanceIndex;?>][course_duration]" >Duração</label>
							<input type="text" class="form-control" data-toggle="tooltip" data-placement="bottom" title="Duração do curso (em horas)" id="course[<?php echo $tempCourseInstanceIndex;?>][course_duration]" name="course[<?php echo $tempCourseInstanceIndex;?>][course_duration]" value="<?php echo $coursesInstance['duration']?>" autocomplete="off" />
						</div>
						<div class="col-xs-3">
							<label class="control-label" for="course[<?php echo $tempCourseInstanceIndex;?>][course_class]" >Turmas</label>
							<input type="text" class="form-control" data-toggle="tooltip" data-placement="bottom" title="Digite a quantidade de turmas" id="course[<?php echo $tempCourseInstanceIndex;?>][course_class]" name="course[<?php echo $tempCourseInstanceIndex;?>][course_class]" value="<?php echo $coursesInstance['class']?>" autocomplete="off" />
						</div>
						<div class="col-xs-3">
							<label class="control-label" for="course[<?php echo $tempCourseInstanceIndex;?>][course_capacity]" >Vagas</label>
							<input type="text" class="form-control" data-toggle="tooltip" data-placement="bottom" title="Digite a quantidade de vagas" id="course[<?php echo $tempCourseInstanceIndex;?>][course_capacity]" name="course[<?php echo $tempCourseInstanceIndex;?>][course_capacity]" value="<?php echo $coursesInstance['capacity']?>" autocomplete="off" />
						</div>
						<div class="col-xs-3">
							<label class="control-label" for="course[<?php echo $tempCourseInstanceIndex;?>][course_enrollments]" >Matrículas</label>
							<input type="text" class="form-control" data-toggle="tooltip" data-placement="bottom" title="Digite a quantidade de matrículas" id="course[<?php echo $tempCourseInstanceIndex;?>][course_enrollments]" name="course[<?php echo $tempCourseInstanceIndex;?>][course_enrollments]" value="<?php echo $coursesInstance['enrollments']?>" autocomplete="off" />
						</div>
					</div>
				</div>
				<hr>
				</fieldset>
				<?php
				$tempCourseInstanceIndex = $tempCourseInstanceIndex + 1;
			}
		?>
		
		
			
		
		<fieldset class="courseFieldset hide" id="courseTemplate">
			<h4>Curso</h4>
			<div class="hide">
				<input type="text" class="form-control hide"  id="courseInstance_bdId" name="courseInstance_bdId" value="" />
			</div>
			<div class="form-group">
				<div class="row">
					<div class="col-xs-8">
						<label class="control-label" for="course_name">Curso</label>
						<input type="text" class="form-control typeahead" data-provide="typeahead" data-toggle="tooltip" data-placement="bottom" title="Nome do Curso" id="course_name" name="course_name" value="" autocomplete="off"/>
					</div>
					<div class="col-xs-4">
						<label class="control-label" for="course_type">Categoria</label>
						<select class="form-control" data-toggle="tooltip" data-placement="bottom" title="Selecione a categoria do curso" name="course_type" id="course_type" >
							<?php
								foreach ($coursesCategories as $courseCategory) {
									if ($courseCategory) {
										//print ($courseCategory['category']);
										echo '<option value="'.$courseCategory['category'].'">'.$courseCategory['category'].'</option>';
									}
								}
							?>
						</select>
					</div>
				</div>
			</div>
			<div class="form-group">
				<div class="row">
					<div class="col-xs-8">
						<label class="control-label" for="course_mingrade" >Graduação Mínima</label>
						<input type="text" class="form-control" data-toggle="tooltip" data-placement="bottom" title="Graduação Mínima Necessária para Fazer o Curso" id="course_mingrade" name="course_mingrade" autocomplete="off" />
					</div>
					<div class="col-xs-4">
						<label class="control-label" for="course_shift">Turno</label>
						<select class="form-control" data-toggle="tooltip" data-placement="bottom" title="Selecione o turno do curso" name="course_shift" id="course_shift" >
							<option value="Matutino">Matutino</option>
							<option value="Vespertino">Vespertino</option>
							<option value="Noturno">Noturno</option>
						</select>
					</div>
				</div>
			</div>
			<div class="form-group">
				<div class="row">
					<div class="col-xs-3">
						<label class="control-label" for="course_duration" >Duração</label>
						<input type="text" class="form-control" data-toggle="tooltip" data-placement="bottom" title="Duração do curso (em horas)" id="course_duration" name="course_duration"  autocomplete="off" />
					</div>
					<div class="col-xs-3">
						<label class="control-label" for="course_class" >Turmas</label>
						<input type="text" class="form-control" data-toggle="tooltip" data-placement="bottom" title="Digite a quantidade de turmas" id="course_class" name="course_class"  autocomplete="off" />
					</div>
					<div class="col-xs-3">
						<label class="control-label" for="course_capacity" >Vagas</label>
						<input type="text" class="form-control" data-toggle="tooltip" data-placement="bottom" title="Digite a quantidade de vagas" id="course_capacity" name="course_capacity"  autocomplete="off" />
					</div>
					<div class="col-xs-3">
						<label class="control-label" for="course_enrollments" >Matrículas</label>
						<input type="text" class="form-control" data-toggle="tooltip" data-placement="bottom" title="Digite a quantidade de matrículas" id="course_enrollments" name="course_enrollments"  autocomplete="off" />
					</div>
				</div>
			</div>
			<!--<div class="form-group">
				<div class="row">
					<div class="col-xs-12">	
						<center>
							<button class="btn btn-danger btn-remove" type="button" id="buttonRemove">
								<span class="glyphicon glyphicon-minus"></span>
							</button>
						</center>
					</div>
				</div>
			</div>
			-->
			<hr>
		</fieldset>
		
		
		<div class="form-group">
			<div class="row">
				<div class="col-xs-12">	
					<center>
						<button class="btn btn-success btn-add" type="button" id="buttonAdd" data-toggle="tooltip" data-placement="bottom" data-original-title="Clique para adicionar um novo curso">
							<span class="glyphicon glyphicon-plus"></span>
						</button>
					</center>
				</div>
			</div>
		</div>
		<script type='text/javascript'>
			typeAheadJSON = [
			<?php 
				//print_r($courses);
				$arrayCatSize = count($courses);
				
				foreach ($courses as $key=>$course) {
					echo '
					{
						"courseId" : "'.$course['id'].'",
						"name" : "'.$course['name'].'",
						"category" : "'.$course['category'].'",
						"minGrade" : "'.$course['min_grade'].'",
						"minDuration" : "'.$course['min_duration'].'"
					}';
					if ( $key != $arrayCatSize-1) {
						echo ',';						
					}
				}
			//$courses
			?>
			];
		$(document).ready(function() {
			var courseIndex = <?php echo $tempCourseInstanceIndex - 1;?>;
			$('#buttonAdd').on('click', function() {
				//alert('aaa');
				courseIndex++;
				var $template = $('#courseTemplate'),
				$clone    = $template
				.clone()
				.removeClass('hide')
				.removeAttr('id')
				.attr('data-course-index', courseIndex)
				.insertBefore($template);
				$clone.find('h4').html('Curso '+ courseIndex + ' <button onclick="removeCourse($(this))" class="btn btn-danger btn-remove" type="button" data-toggle="tooltip" data-placement="bottom" data-original-title="Clique para remover este curso"> <span class="glyphicon glyphicon-remove"></span> </button>');
				//alert($clone.find('h4').toSource());
				//////////////course_mingrade
				$clone
					//courseInstance_bdId
					.find('[name="courseInstance_bdId"]').attr('name', 'course[' + courseIndex + '][courseInstance_bdId]').attr('id', 'course[' + courseIndex + '][courseInstance_bdId]').end()
					.find('[name="course_name"]').attr('name', 'course[' + courseIndex + '][course_name]').attr('id', 'course[' + courseIndex + '][course_name]').end()
					.find('[name="course_type"]').attr('name', 'course[' + courseIndex + '][course_type]').attr('id', 'course[' + courseIndex + '][course_type]').end()
					.find('[name="course_mingrade"]').attr('name', 'course[' + courseIndex + '][course_mingrade]').attr('id', 'course[' + courseIndex + '][course_mingrade]').end()
					.find('[name="course_shift"]').attr('name', 'course[' + courseIndex + '][course_shift]').attr('id', 'course[' + courseIndex + '][course_shift]').end()
					.find('[name="course_duration"]').attr('name', 'course[' + courseIndex + '][course_duration]').attr('id', 'course[' + courseIndex + '][course_duration]').end()
					.find('[name="course_class"]').attr('name', 'course[' + courseIndex + '][course_class]').attr('id', 'course[' + courseIndex + '][course_class]').end()
					.find('[name="course_capacity"]').attr('name', 'course[' + courseIndex + '][course_capacity]').attr('id', 'course[' + courseIndex + '][course_capacity]').end()
					.find('[name="course_enrollments"]').attr('name', 'course[' + courseIndex + '][course_enrollments]').attr('id', 'course[' + courseIndex + '][course_enrollments]').end();
					
					$('#course\\[' + courseIndex + '\\]\\[course_name\\]').typeahead({
						//source: markerTitles,
						source: typeAheadJSON
						/*onselect: function(obj) {
							alert("selected");
							//$('#course\\[' + courseIndex + '\\]\\[course_type\\]').val(current.category);
						  //alert("ASDF");
						  //$("#search").val("");
						}*/
					});
					//TO DO: Problema com múltiplos typeahead. Ao inserir novos cursos somente o último typeahead muda. não muda os outros adicionados dinamicamente...
					$('#course\\[' + courseIndex + '\\]\\[course_name\\]').change(function() {
						var inputField = $('#course\\[' + courseIndex + '\\]\\[course_name\\]');
						var selectCategory = $(inputField).parent().parent().find("select[id*='course_type']");
						var inputDuration = $(inputField).parent().parent().parent().parent().find("input[id*='course_duration']");
						var inputMinGrade = $(inputField).parent().parent().parent().parent().find("input[id*='course_mingrade']");
						var current = $(inputField).typeahead("getActive");
						if (current) {
							// Some item from your model is active!
							if (current.name.toLowerCase() == $(inputField).val().toLowerCase()) {
								//alert($(inputDuration).html());
								//alert($(selectCategory).val());
								 $(selectCategory).val(current.category);
								 if (current.minDuration) {
									 $(inputDuration).val(current.minDuration);
								 }
								 if (current.minGrade) {
									 $(inputMinGrade).val(current.minGrade);
								 }
								//alert("FULL MATCH");
								//alert("FULL MATCH"+current.toSource());
								//alert("FULL MATCH"+current.markerId);
								//goToMarker(current.markerId);
								// This means the exact match is found. Use toLowerCase() if you want case insensitive match.
							} else {
								//alert("partialmatch");
								// This means it is only a partial match, you can either add a new item 
								// or take the active if you don't want new items
							}
						} else {
							// Nothing is active so it is a new value (or maybe empty value)
						}
					});
					

				// Add new field
				//$('#surveyForm').formValidation('addField', $option);
				/*$('[data-toggle=\"tooltip\"]').tooltip();
				$('#buttonRemove').on('click', function() {
					alert('click');
					var $row  = $(this).parents('.courseFieldset '),
					index = $row.attr('data-book-index');
					$row.remove();

				});*/
			});
			
			

			/*$('#course\\[1\\]\\[course_name\\]').typeahead({
				//source: markerTitles,
				source: typeAheadJSON,
				onselect: function(obj) {
					//alert("selected");
				  marker_id = jQuery.inArray(obj, markerTitles);
				  if(marker_id > -1) {
				    map.panTo(gmarkers[marker_id].getPosition());
				    map.setZoom(15);
				    google.maps.event.trigger(gmarkers[marker_id], 'click');
				  }
				  //alert("ASDF");
				  //$("#search").val("");
				}
			});
			$('#course\\[1\\]\\[course_name\\]').change(function() {
				var current = $('#course\\[1\\]\\[course_name\\]').typeahead("getActive");
				if (current) {
					// Some item from your model is active!
					if (current.name.toLowerCase() == $('#course\\[1\\]\\[course_name\\]').val().toLowerCase()) {
						 $('#course\\[1\\]\\[course_type\\]').val(current.category);
						//alert("FULL MATCH");
						//alert("FULL MATCH"+current.toSource());
						//alert("FULL MATCH"+current.markerId);
						goToMarker(current.markerId);
						// This means the exact match is found. Use toLowerCase() if you want case insensitive match.
					} else {
						//alert("partialmatch");
						// This means it is only a partial match, you can either add a new item 
						// or take the active if you don't want new items
					}
				} else {
					// Nothing is active so it is a new value (or maybe empty value)
				}
			});
			*/


		});
		</script>
		

    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Save Changes</button>
      <input type="hidden" name="task" value="doedit" />
      <input type="hidden" name="place_id" value="<?php echo $place['id']?>" />
      <input type="hidden" name="view" value="<?php echo $view?>" />
      <input type="hidden" name="search" value="<?php echo $search?>" />
      <input type="hidden" name="p" value="<?php echo $p?>" />
      <a href="index.php" class="btn" style="float: right;">Cancel</a>
    </div>
  </fieldset>
</form>


 <div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
            
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel">Confirmação</h4>
                </div>
            
                <div class="modal-body">
                    <p>Você está prestes a deletar um curso, este processo é irreversível.</p>
                    <p>Gostaria de continuar?</p>
                    <p class="course-url"></p>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <a class="btn btn-danger btn-ok">Delete</a>
                </div>
            </div>
        </div>
    </div>



<?php echo $admin_foot; ?>
