<?php /* @var $this Controller */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	
	<!-- Bootstrap 5 CSS -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
	<!-- Bootstrap Icons -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
	
	<title><?php echo CHtml::encode($this->pageTitle); ?> | Book Management System</title>
	
	<style>
		:root {
			--primary-color: #2c3e50;
			--secondary-color: #3498db;
		}
		
		body {
			min-height: 100vh;
			display: flex;
			flex-direction: column;
		}
		
		.navbar-brand {
			font-weight: bold;
			font-size: 1.4rem;
		}
		
		.navbar-brand i {
			color: var(--secondary-color);
		}
		
		.nav-link.active {
			font-weight: 600;
		}
		
		.search-form .form-control {
			border-radius: 20px 0 0 20px;
		}
		
		.search-form .btn {
			border-radius: 0 20px 20px 0;
		}
		
		.flash-success {
			background-color: #d4edda;
			border-color: #c3e6cb;
			color: #155724;
			padding: 15px;
			margin-bottom: 20px;
			border-radius: 5px;
		}
		
		.flash-error {
			background-color: #f8d7da;
			border-color: #f5c6cb;
			color: #721c24;
			padding: 15px;
			margin-bottom: 20px;
			border-radius: 5px;
		}
		
		.flash-notice {
			background-color: #fff3cd;
			border-color: #ffeeba;
			color: #856404;
			padding: 15px;
			margin-bottom: 20px;
			border-radius: 5px;
		}
		
		.breadcrumb {
			background-color: transparent;
			padding: 0;
			margin-bottom: 1rem;
		}
		
		footer {
			margin-top: auto;
			padding: 20px 0;
			background-color: #f8f9fa;
			border-top: 1px solid #e9ecef;
		}
		
		.book-card {
			transition: transform 0.2s, box-shadow 0.2s;
		}
		
		.book-card:hover {
			transform: translateY(-3px);
			box-shadow: 0 4px 12px rgba(0,0,0,0.15);
		}
		
		.cover-image {
			max-width: 200px;
			max-height: 300px;
			object-fit: cover;
		}
		
		.cover-thumbnail {
			width: 80px;
			height: 120px;
			object-fit: cover;
		}
		
		.author-badge {
			font-size: 0.85rem;
		}
		
		.data-table th {
			background-color: #f8f9fa;
		}
		
		.filter-form {
			background-color: #f8f9fa;
			padding: 15px;
			border-radius: 8px;
			margin-bottom: 20px;
		}
		
		.rank-badge {
			width: 40px;
			height: 40px;
			display: inline-flex;
			align-items: center;
			justify-content: center;
			font-weight: bold;
			font-size: 1.2rem;
		}
		
		.rank-1 { background-color: #ffd700; color: #000; }
		.rank-2 { background-color: #c0c0c0; color: #000; }
		.rank-3 { background-color: #cd7f32; color: #fff; }
		.rank-other { background-color: #6c757d; color: #fff; }
		
		@media (max-width: 768px) {
			.search-form {
				margin-top: 10px;
			}
		}
	</style>
</head>

<body>
	<!-- Navigation -->
	<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
		<div class="container">
			<a class="navbar-brand" href="<?php echo Yii::app()->createUrl('site/index'); ?>">
				<i class="bi bi-book"></i> BookManager
			</a>
			
			<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" 
					aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>
			
			<div class="collapse navbar-collapse" id="navbarMain">
				<ul class="navbar-nav me-auto">
					<li class="nav-item">
						<a class="nav-link" href="<?php echo Yii::app()->createUrl('site/index'); ?>">
							<i class="bi bi-house"></i> Home
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="<?php echo Yii::app()->createUrl('books/index'); ?>">
							<i class="bi bi-journal-text"></i> Books
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="<?php echo Yii::app()->createUrl('authors/index'); ?>">
							<i class="bi bi-people"></i> Authors
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="<?php echo Yii::app()->createUrl('report/topAuthors'); ?>">
							<i class="bi bi-trophy"></i> TOP 10 Report
						</a>
					</li>
				</ul>
				
				<!-- Search Form -->
				<form class="d-flex search-form me-3" action="<?php echo Yii::app()->createUrl('books/index'); ?>" method="get">
					<input class="form-control" type="search" name="search" placeholder="Search books..." 
						   value="<?php echo isset($_GET['search']) ? CHtml::encode($_GET['search']) : ''; ?>">
					<button class="btn btn-outline-light" type="submit">
						<i class="bi bi-search"></i>
					</button>
				</form>
				
				<!-- User Menu -->
				<ul class="navbar-nav">
					<?php if (Yii::app()->user->isGuest): ?>
						<li class="nav-item">
							<a class="nav-link" href="<?php echo Yii::app()->createUrl('user/login'); ?>">
								<i class="bi bi-box-arrow-in-right"></i> Login
							</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="<?php echo Yii::app()->createUrl('user/register'); ?>">
								<i class="bi bi-person-plus"></i> Register
							</a>
						</li>
					<?php else: ?>
						<li class="nav-item dropdown">
							<a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" 
							   data-bs-toggle="dropdown" aria-expanded="false">
								<i class="bi bi-person-circle"></i> 
								<?php echo CHtml::encode(Yii::app()->user->name); ?>
							</a>
							<ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
								<li>
									<a class="dropdown-item" href="<?php echo Yii::app()->createUrl('user/profile'); ?>">
										<i class="bi bi-person"></i> Profile
									</a>
								</li>
								<li>
									<a class="dropdown-item" href="<?php echo Yii::app()->createUrl('books/create'); ?>">
										<i class="bi bi-plus-circle"></i> Add Book
									</a>
								</li>
								<li><hr class="dropdown-divider"></li>
								<li>
									<a class="dropdown-item" href="<?php echo Yii::app()->createUrl('user/logout'); ?>">
										<i class="bi bi-box-arrow-right"></i> Logout
									</a>
								</li>
							</ul>
						</li>
					<?php endif; ?>
				</ul>
			</div>
		</div>
	</nav>

	<!-- Main Content -->
	<main class="container my-4">
		<!-- Breadcrumbs -->
		<?php if (isset($this->breadcrumbs)): ?>
			<?php $this->widget('zii.widgets.CBreadcrumbs', array(
				'links' => $this->breadcrumbs,
				'htmlOptions' => array('class' => 'breadcrumb'),
				'separator' => ' / ',
				'homeLink' => CHtml::link('<i class="bi bi-house"></i> Home', array('site/index')),
			)); ?>
		<?php endif; ?>

		<!-- Flash Messages -->
		<?php if (Yii::app()->user->hasFlash('success')): ?>
			<div class="alert alert-success alert-dismissible fade show" role="alert">
				<i class="bi bi-check-circle"></i> 
				<?php echo Yii::app()->user->getFlash('success'); ?>
				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			</div>
		<?php endif; ?>
		
		<?php if (Yii::app()->user->hasFlash('error')): ?>
			<div class="alert alert-danger alert-dismissible fade show" role="alert">
				<i class="bi bi-exclamation-triangle"></i> 
				<?php echo Yii::app()->user->getFlash('error'); ?>
				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			</div>
		<?php endif; ?>
		
		<?php if (Yii::app()->user->hasFlash('notice')): ?>
			<div class="alert alert-warning alert-dismissible fade show" role="alert">
				<i class="bi bi-info-circle"></i> 
				<?php echo Yii::app()->user->getFlash('notice'); ?>
				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			</div>
		<?php endif; ?>

		<!-- Page Content -->
		<?php echo $content; ?>
	</main>

	<!-- Footer -->
	<footer class="text-center text-muted">
		<div class="container">
			<p>&copy; <?php echo date('Y'); ?> Book Management System. All rights reserved.</p>
			<p><?php echo Yii::powered(); ?></p>
		</div>
	</footer>

	<!-- Bootstrap 5 JS Bundle -->
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
	
	<script>
		// Auto-hide alerts after 5 seconds
		document.addEventListener('DOMContentLoaded', function() {
			const alerts = document.querySelectorAll('.alert-dismissible');
			alerts.forEach(function(alert) {
				setTimeout(function() {
					const bsAlert = new bootstrap.Alert(alert);
					bsAlert.close();
				}, 5000);
			});
		});
	</script>
</body>
</html>
