<?php
/**
 * Site homepage view.
 */

$this->pageTitle = 'Home';
?>

<div class="row">
	<div class="col-md-12 text-center mb-5">
		<h1 class="display-4">
			<i class="bi bi-book" style="color: #3498db;"></i> Book Management System
		</h1>
		<p class="lead text-muted">
			Your comprehensive catalog for managing books and authors
		</p>
	</div>
</div>

<!-- Quick Stats -->
<div class="row mb-5">
	<div class="col-md-4 mb-3">
		<div class="card text-center h-100">
			<div class="card-body">
				<i class="bi bi-journal-text" style="font-size: 2.5rem; color: #3498db;"></i>
				<h2 class="mt-2">
					<?php 
					$bookCount = Yii::app()->db->createCommand()
						->select('COUNT(*)')
						->from('books')
						->queryScalar();
					echo $bookCount;
					?>
				</h2>
				<p class="text-muted mb-0">Books in Catalog</p>
			</div>
			<div class="card-footer bg-transparent border-0">
				<a href="<?php echo Yii::app()->createUrl('books/index'); ?>" class="btn btn-outline-primary">
					Browse Books <i class="bi bi-arrow-right"></i>
				</a>
			</div>
		</div>
	</div>
	<div class="col-md-4 mb-3">
		<div class="card text-center h-100">
			<div class="card-body">
				<i class="bi bi-people" style="font-size: 2.5rem; color: #28a745;"></i>
				<h2 class="mt-2">
					<?php 
					$authorCount = Yii::app()->db->createCommand()
						->select('COUNT(*)')
						->from('authors')
						->queryScalar();
					echo $authorCount;
					?>
				</h2>
				<p class="text-muted mb-0">Authors</p>
			</div>
			<div class="card-footer bg-transparent border-0">
				<a href="<?php echo Yii::app()->createUrl('authors/index'); ?>" class="btn btn-outline-success">
					View Authors <i class="bi bi-arrow-right"></i>
				</a>
			</div>
		</div>
	</div>
	<div class="col-md-4 mb-3">
		<div class="card text-center h-100">
			<div class="card-body">
				<i class="bi bi-trophy" style="font-size: 2.5rem; color: #ffc107;"></i>
				<h2 class="mt-2">TOP 10</h2>
				<p class="text-muted mb-0">Authors Report</p>
			</div>
			<div class="card-footer bg-transparent border-0">
				<a href="<?php echo Yii::app()->createUrl('report/topAuthors'); ?>" class="btn btn-outline-warning">
					View Report <i class="bi bi-arrow-right"></i>
				</a>
			</div>
		</div>
	</div>
</div>

<!-- Features -->
<div class="row mb-5">
	<div class="col-md-12">
		<h3 class="mb-4"><i class="bi bi-star"></i> Features</h3>
	</div>
	<div class="col-md-4 mb-3">
		<div class="card h-100">
			<div class="card-body">
				<h5 class="card-title">
					<i class="bi bi-search text-primary"></i> Search & Filter
				</h5>
				<p class="card-text text-muted">
					Search books by title or ISBN. Filter by author or publication year.
				</p>
			</div>
		</div>
	</div>
	<div class="col-md-4 mb-3">
		<div class="card h-100">
			<div class="card-body">
				<h5 class="card-title">
					<i class="bi bi-bell text-success"></i> SMS Notifications
				</h5>
				<p class="card-text text-muted">
					Subscribe to your favorite authors and get notified about new releases via SMS.
				</p>
			</div>
		</div>
	</div>
	<div class="col-md-4 mb-3">
		<div class="card h-100">
			<div class="card-body">
				<h5 class="card-title">
					<i class="bi bi-bar-chart text-warning"></i> Analytics
				</h5>
				<p class="card-text text-muted">
					View TOP 10 authors by book count with year-based filtering.
				</p>
			</div>
		</div>
	</div>
</div>

<!-- Call to Action -->
<?php if (Yii::app()->user->isGuest): ?>
	<div class="row">
		<div class="col-md-12">
			<div class="card bg-light">
				<div class="card-body text-center py-5">
					<h4>Ready to get started?</h4>
					<p class="text-muted">Create an account to add books and subscribe to authors</p>
					<a href="<?php echo Yii::app()->createUrl('user/register'); ?>" class="btn btn-success btn-lg">
						<i class="bi bi-person-plus"></i> Register Now
					</a>
					<span class="mx-2">or</span>
					<a href="<?php echo Yii::app()->createUrl('user/login'); ?>" class="btn btn-primary btn-lg">
						<i class="bi bi-box-arrow-in-right"></i> Login
					</a>
				</div>
			</div>
		</div>
	</div>
<?php else: ?>
	<div class="row">
		<div class="col-md-12">
			<div class="card bg-light">
				<div class="card-body text-center py-4">
					<h4>Welcome back, <?php echo CHtml::encode(Yii::app()->user->name); ?>!</h4>
					<p class="text-muted mb-0">
						<a href="<?php echo Yii::app()->createUrl('books/create'); ?>" class="btn btn-success">
							<i class="bi bi-plus-circle"></i> Add New Book
						</a>
						<a href="<?php echo Yii::app()->createUrl('user/profile'); ?>" class="btn btn-outline-primary ms-2">
							<i class="bi bi-person"></i> View Profile
						</a>
					</p>
				</div>
			</div>
		</div>
	</div>
<?php endif; ?>
