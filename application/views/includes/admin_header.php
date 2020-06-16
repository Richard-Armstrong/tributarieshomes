
<!DOCTYPE html>

<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

	<title>TREG</title>


		<!-- Custom fonts for this template-->
		  <link href="<?php echo base_url();?>vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
		  <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

			<!-- Le styles -->
			<link rel="stylesheet" media="all" type="text/css" href="<?= base_url("css/app.css") ?>"/>
			<link rel="stylesheet" media="all" type="text/css" href="<?= base_url("css/style.css") ?>"/>
			<link rel="stylesheet" media="all" type="text/css" href="<?= base_url("css/jquery-ui.css") ?>"/>
			<!-- Custom styles for this template-->
  	  	<link href="<?=base_url();?>css/sb-admin-2.css" rel="stylesheet">

		<!-- Bootstrap core JavaScript-->
		<script src="<?php echo base_url();?>vendor/jquery/jquery.min.js"></script>
		<script src="<?php echo base_url();?>vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
		<!-- Core plugin JavaScript-->
		<script src="<?php echo base_url();?>vendor/jquery-easing/jquery.easing.min.js"></script>
		<script type="text/javascript" src="<?= base_url("js/jquery-ui.js") ?>"></script>
</head>

<body id="page-top">
	<div id="wrapper">

		<!-- Sidebar -->
		<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
			<a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?= base_url("index.php") ?>">
				<div class="sidebar-brand-text mx-3">
					<img src="<?= base_url("assets/img/tributary_logo.png") ?>"  style="background:linear-gradient(135deg, #FFF 0%, #FFF 60%);"/>
				</div>
			</a>
			<!-- Divider -->
			<hr class="sidebar-divider">

			<!-- Nav Item - Pages Collapse Menu -->
			<?php if ($this->session->userdata('is_logged_in')) : ?>
				<?php if (!is_base_company($this->session->userdata('user_company'))) : ?>
					<?php if (is_company_manager($this->session->userdata('user_level')) && $this->session->userdata('departments')) : ?>
						<li class="nav-item">
							<a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseDepartments" aria-expanded="true" aria-controls="collapseDepartments">
								<i class="fas fa-fw fa-folder"></i>
								<span>Departments</span>
							</a>

							<div id="collapseDepartments" class="collapse" aria-labelledby="headingPages" data-parent="#accordionSidebar">
								<div class="bg-white py-2 collapse-inner rounded">
									<?php foreach ($this->session->userdata('departments') as $department) : ?>
										<?php if ($department->name != 'administration' && $department->name != 'unassigned') : ?>
											<a class="collapse-item" href="<?= base_url("index.php/main/department/{$department->id}") ?>">
												<?= $department->name ?>
											</a>
										<?php endif // $department->name ?>
									<?php endforeach ?>
								</div>
							</div>
						</li>
					<?php else : // company manager + has departments ?>
						<?php foreach ($this->session->userdata('user_groups') as $group) : ?>
							<?php if ($group->name != 'administration' && $group->name != 'unassigned') : ?>
								<li class="nav-item">
									<a class="nav-link" href="<?= base_url("index.php/main/department/{$group->id}") ?>">
										<?= $group->name ?>
									</a>
								</li>
							<?php endif // $group->name ?>
						<?php endforeach ?>
					<?php endif // else ?>

					<?php if (is_company_manager($this->session->userdata('user_level'))) : ?>
						<li class="nav-item">
							<a class="nav-link" href="<?= base_url("index.php/reports/list") ?>">
								<i class="fas fa-fw fa-folder"></i>
								<span>Reports</span>
							</a>
						</li>
					<?php endif ?>
				<?php endif // base_company ?>

				<?php if (is_company_manager($this->session->userdata('user_level'))) : ?>
					<li class="nav-item">
						  <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseUtilities" aria-expanded="true" aria-controls="collapseUtilities">
							<i class="fas fa-fw fa-wrench"></i>
							<span>Admin</span>
						  </a>
						  <div id="collapseUtilities" class="collapse" aria-labelledby="headingPages" data-parent="#accordionSidebar">
						    <div class="bg-white py-2 collapse-inner rounded">
						<?php if (!is_base_company($this->session->userdata('user_company'))) : ?>
							<a class="collapse-item" href="<?= base_url("index.php/maintenance/deactivated_forms") ?>">Deactivated Forms</a>
							<a class="collapse-item" href="<?= base_url("index.php/reports/deactivated") ?>">Deactivated Reports</a>
							<a class="collapse-item" href="<?= base_url("index.php/maintenance/event_log") ?>">Event Log</a>
							<a class="collapse-item" href="<?= base_url("index.php/maintenance/search") ?>">Company Search</a>
						<?php endif // !base_company ?>

						<a class="collapse-item" href="<?= base_url("index.php/auth") ?>">Users</a>
						<a class="collapse-item" href="<?= base_url("index.php/admin/bios") ?>">Bios</a>
						<a class="collapse-item" href="<?= base_url("index.php/admin/inventory") ?>">Inventory</a>

						<?php if (is_account_manager($this->session->userdata('user_level'))) : ?>
							<?php if (!is_base_company($this->session->userdata('user_company'))) : ?>
									<a class="collapse-item" href="<?= base_url("index.php/maintenance/departments") ?>">Departments</a>
									<a class="collapse-item"
										href="<?= base_url("index.php/maintenance/edit_company/{$this->session->userdata('user_company')}") ?>">Company Info</a>
							<?php endif // is_base_company ?>
						<?php endif // is_account_manager ?>

						<?php if (is_superuser($this->session->userdata('user_level'))) : ?>
								<a class="collapse-item" href="<?= base_url("index.php/maintenance/companies") ?>">Companies</a>
								<a class="collapse-item" href="<?= base_url("index.php/maintenance/nvp_codes") ?>">Codes</a>
						<?php endif // is_superuser ?>
							</div>
						</div>
					</li>
					<?php endif // is_company_manager ?>
				<?php endif // is_logged_in ?>
				<!-- Divider -->
      <hr class="sidebar-divider d-none d-md-block">

      <!-- Sidebar Toggler (Sidebar) -->
      <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
      </div>
		</ul>

				<!-- Content Wrapper -->
			    <div id="content-wrapper" class="d-flex flex-column">

			      <!-- Main Content -->
			      <div id="content">

			        <!-- Topbar -->
			        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

			          <!-- Sidebar Toggle (Topbar) -->
			          <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
			            <i class="fa fa-bars"></i>
			          </button>
					  <!-- Topbar Navbar -->
			          <ul class="navbar-nav ml-auto">


		  				<?php if (is_superuser($this->session->userdata('user_level'))) : ?>
							<li class="nav-item dropdown no-arrow">
		  						<a class="nav-link dropdown-toggle" href="<?= base_url("index.php/maintenance/change_company") ?>">
		  							Current Company: <?= $this->session->userdata('company_name') ?>
		  						</a>
		  					</li>
		  				<?php endif // is_superuser ?>
						  <div class="topbar-divider d-none d-sm-block"></div>
						  <!-- Nav Item - User Information -->
			              <li class="nav-item dropdown no-arrow">
			                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
			                  <span class="mr-2 d-none d-lg-inline text-gray-600 small">
							<?= $this->session->userdata('user_name') ?>
							</span>
						</a>
						<!-- Dropdown - User Information -->
		                 <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
		                   <a class="dropdown-item" href="<?= base_url("index.php/auth/edit_user");?>">
		                     <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
		                     Profile
		                   </a>
						   <div class="dropdown-divider"></div>
			                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
			                  <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
			                  Logout
			                </a>
			              </div>

					</li>

				</ul>
			</nav>

			<!-- End of Topbar -->

        <!-- Begin Page Content -->
        <div class="container-fluid">
