<?php
include "../include/db.php";
// get task
if(isset($_GET['task'])) { $task = $_GET['task']; } 
else if(isset($_POST['task'])) { $task = $_POST['task']; }

// get view
if(isset($_GET['view'])) { $view = $_GET['view']; } 
else if(isset($_POST['view'])) { $view = $_POST['view']; }
else { $view = ""; }

// get page
if(isset($_GET['p'])) { $p = $_GET['p']; } 
else if(isset($_POST['p'])) { $p = $_POST['p']; }
else { $p = 1; }

// get search
if(isset($_GET['search'])) { $search = $_GET['search']; } 
else if(isset($_POST['search'])) { $search = $_POST['search']; }
else { $search = ""; }

// make sure admin is logged in
if (isset($page)) {
	if($page != "login") {
	  if($_COOKIE["representmap_user"] != crypt($admin_user, $admin_user) OR $_COOKIE["representmap_pass"] != crypt($admin_pass, $admin_pass)) {
	    header("Location: login.php");
	    exit;
	  }
	}
}

// connect to db
mysql_connect($db_host, $db_user, $db_pass) or die(mysql_error());
mysql_select_db($db_name) or die(mysql_error());

// get marker totals
$total_approved = mysql_num_rows(mysql_query("SELECT id FROM places WHERE approved='1'"));
$total_rejected = mysql_num_rows(mysql_query("SELECT id FROM places WHERE approved='0'"));
$total_pending = mysql_num_rows(mysql_query("SELECT id FROM places WHERE approved IS null"));
$total_all = mysql_num_rows(mysql_query("SELECT id FROM places"));

// admin header
$admin_head = "
  <!DOCTYPE html>
  <html>
  <head>
    <title>Mapa da Capacitação -  Admin</title>
    <meta charset='UTF-8'>
        <script src='../scripts/jquery-1.11.3.min.js' type='text/javascript' charset='utf-8'></script>
    <link href='../bootstrap-new/css/bootstrap.css' rel='stylesheet' type='text/css' />
    <link href='../bootstrap-new/css/bootstrap-theme.css' rel='stylesheet' type='text/css' />
    <link rel='stylesheet' href='admin.css' type='text/css' />
    <script src='../bootstrap-new/js/bootstrap.js' type='text/javascript' charset='utf-8'></script>
    <script src='https://maps.googleapis.com/maps/api/js?libraries=places&sensor=false' type='text/javascript' charset='utf-8'></script>
    <script src='../scripts/bootstrap3-typeahead.min.js' type='text/javascript' charset='utf-8'></script>
    <script type='text/javascript'>
		var elementToDelete;
		var typeAheadJSON = [];
		function removeCourse(element,courseId) {
			//alert('remove'+$('#course\\\['+courseId+'\\\]').html());
			//var temp=$('#course\\\['+courseId+'\\\]\\\.course_name');
			//var temp1=$('#course\\\['+courseId+'\\\]');
			//alert('removeHTML'+temp.toSource());
			elementToDelete=element;
			$('#confirm-delete').attr('data-curCourseId', courseId);
			//$('#confirm-delete').attr('data-curCourseName', $(element).parents('input[id*=\"course_name\"]').val());
			$('#confirm-delete').attr('data-curCourseName', $(element).parent().parent().find('input[id*=\"course_name\"]').val());
			$('#confirm-delete').modal('show');			
			//element.parents('.courseFieldset').remove();
			//onclick='$(this).parents(\'.courseFieldset\').remove()
		}
		function removeCourseDelete(courseInstanceId){
			$.ajax({
				type: 'GET',
				url: 'index.php',
				data: 'task=deleteCourse&courseInstance_id='+courseInstanceId,
				cache: false,
				success: function(data){
					if ($.trim(data) == 'ok') {
						//alert('success');
						elementToDelete.parents('.courseFieldset').remove();
					}
					//$('#resultarea').text(data);
				}
			});
		}
			
	    $(document).ready(function(){
			$('[data-toggle=\"tooltip\"]').tooltip();
			$('[data-toggle=\"popover\"]').popover();
			
			
			$('#confirm-delete').find('.btn-ok').on('click', function(e) {
				removeCourseDelete($('#confirm-delete').attr('data-curCourseId'));
				$('#confirm-delete').modal('hide');
				//e.stopPropagation();
				
			});
			$('#confirm-delete').on('show.bs.modal', function(e) {
				var courseId = $(this).attr('data-curCourseId');
				var courseName = $(this).attr('data-curCourseName');
				//alert($(this).attr('data-curCourseId'));
				//$(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
				$('.modal-dialog .course-url').html('Id do Curso: <strong>' + courseId  + '</strong>. Nome do Curso: <strong>'+   courseName +'</strong>');
			});
			
			/*$('#confirm-delete').on('hide.bs.modal', function(e) {
				var clickevent = $(this).find('.btn-ok').off( 'click');
				alert('a');
			});
			*/
			
	    });
    </script>
    <style type='text/css'>
		#typeahead_seach {
			border: 0;
			padding: 0;
		}

		#typeahead_seach .tooltip {
			min-width:200px;
			width 200px;
		}
	</style>
  </head>
  <body>
";

if (isset($page)) {
	if($page != "login") {

	 

	  $admin_head .= "
	    <nav class='navbar navbar-default navbar-inverse'>
		   <div class='container'>
		    <div class='navbar-header'>
		    
			<a class='navbar-brand' href='index.php'>
			  Mapa Capacitacação
			</a>
		    </div>
		    
		    <div class='collapse navbar-collapse'>
			 <ul class='nav navbar-nav'>
			  <li"; if($view == "") { $admin_head .= " class='active'"; } $admin_head .= ">
			    <a href='index.php'>All Listings</a>
			  </li>
			  <li"; if($view == "approved") { $admin_head .= " class='active'"; } $admin_head .= ">
			    <a href='index.php?view=approved'>
				 Approved
				 <span class='badge badge-info'>$total_approved</span>
			    </a>
			  </li>
			  <li"; if($view == "pending") { $admin_head .= " class='active'"; } $admin_head .= ">
			    <a href='index.php?view=pending'>
				 Pending
				 <span class='badge badge-info'>$total_pending</span>
			    </a>
			  </li>
			  <li"; if($view == "rejected") { $admin_head .= " class='active'"; } $admin_head .= ">
			    <a href='index.php?view=rejected'>
				 Rejected
				 <span class='badge badge-info'>$total_rejected</span>
			    </a>
			  </li>
			</ul>
			<form class='navbar-form navbar-left' role='search' action='index.php' method='get'>
			 <div class='form-group'>
			  <input type='text' name='search' class='form-control search-query' placeholder='Search' autocomplete='off' value='$search'>
			 </div>
			</form>
			<ul class='nav navbar-nav navbar-right'>
			  <li><a href='login.php?task=logout'>Sign Out</a></li>
			</ul>
			</div>
		   </div>
	    </nav>
	  ";
	}
}
$admin_head .= "
  <div id='content'>
";






// admin footer 
$admin_foot = "
    </div>
  </body>
</html>
";




?>