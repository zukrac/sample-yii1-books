<?php
/**
 * User profile view.
 * 
 * @var User $user The user model
 * @var UserSubscription[] $subscriptions User's subscriptions
 * @var CActiveDataProvider $booksProvider User's books data provider
 */

$this->breadcrumbs = array(
	'Profile',
);

$this->pageTitle = 'My Profile';
?>

<div class="row">
	<div class="col-md-12">
		<h1><i class="bi bi-person-circle"></i> My Profile</h1>
	</div>
</div>

<div class="row mt-4">
	<!-- User Info Card -->
	<div class="col-md-4 mb-4">
		<div class="card">
			<div class="card-header">
				<h5 class="mb-0"><i class="bi bi-info-circle"></i> Account Information</h5>
			</div>
			<div class="card-body">
				<div class="text-center mb-3">
					<i class="bi bi-person-circle" style="font-size: 4rem; color: #3498db;"></i>
				</div>
				<table class="table table-borderless mb-0">
					<tr>
						<th style="width: 100px;"><i class="bi bi-person"></i> Username</th>
						<td><?php echo CHtml::encode($user->username); ?></td>
					</tr>
					<tr>
						<th><i class="bi bi-envelope"></i> Email</th>
						<td><?php echo CHtml::encode($user->email); ?></td>
					</tr>
					<tr>
						<th><i class="bi bi-phone"></i> Phone</th>
						<td>
							<?php if (!empty($user->phone)): ?>
								<?php echo CHtml::encode($user->phone); ?>
							<?php else: ?>
								<span class="text-muted">Not set</span>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<th><i class="bi bi-shield"></i> Role</th>
						<td>
							<span class="badge bg-info"><?php echo CHtml::encode($user->role); ?></span>
						</td>
					</tr>
					<tr>
						<th><i class="bi bi-calendar"></i> Registered</th>
						<td><?php echo CHtml::encode($user->created_at); ?></td>
					</tr>
				</table>
			</div>
		</div>
		
		<!-- Quick Actions -->
		<div class="card mt-3">
			<div class="card-header">
				<h5 class="mb-0"><i class="bi bi-lightning"></i> Quick Actions</h5>
			</div>
			<div class="card-body">
				<a href="<?php echo Yii::app()->createUrl('books/create'); ?>" class="btn btn-success w-100 mb-2">
					<i class="bi bi-plus-circle"></i> Add New Book
				</a>
				<a href="<?php echo Yii::app()->createUrl('authors/index'); ?>" class="btn btn-outline-primary w-100">
					<i class="bi bi-people"></i> Browse Authors
				</a>
			</div>
		</div>
	</div>
	
	<!-- Subscriptions & Books -->
	<div class="col-md-8">
		<!-- Subscriptions -->
		<div class="card mb-4">
			<div class="card-header d-flex justify-content-between align-items-center">
				<h5 class="mb-0">
					<i class="bi bi-bell"></i> Author Subscriptions 
					<span class="badge bg-secondary"><?php echo count($subscriptions); ?></span>
				</h5>
			</div>
			<div class="card-body">
				<?php if (empty($subscriptions)): ?>
					<div class="alert alert-info mb-0">
						<i class="bi bi-info-circle"></i> You are not subscribed to any authors yet.
						<br>
						<a href="<?php echo Yii::app()->createUrl('authors/index'); ?>">Browse authors</a> to subscribe and receive notifications about new books.
					</div>
				<?php else: ?>
					<div class="table-responsive">
						<table class="table table-hover">
							<thead>
								<tr>
									<th>Author</th>
									<th>Subscribed At</th>
									<th>Phone for Notifications</th>
									<th>Actions</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($subscriptions as $subscription): ?>
									<tr>
										<td>
											<a href="<?php echo Yii::app()->createUrl('authors/view', array('id' => $subscription->author_id)); ?>">
												<i class="bi bi-person"></i> 
												<?php echo CHtml::encode($subscription->author->full_name); ?>
											</a>
										</td>
										<td><?php echo CHtml::encode($subscription->subscribed_at); ?></td>
										<td>
											<i class="bi bi-phone"></i> 
											<?php 
											if (!empty($subscription->phone_number)) {
												echo CHtml::encode($subscription->phone_number);
											} elseif (!empty($subscription->user) && !empty($subscription->user->phone)) {
												echo CHtml::encode($subscription->user->phone) . ' <small class="text-muted">(from profile)</small>';
											} else {
												echo '<span class="text-muted">Not set</span>';
											}
											?>
										</td>
										<td>
											<?php echo CHtml::link(
												'<i class="bi bi-bell-slash"></i> Unsubscribe',
												array('subscriptions/unsubscribe', 'id' => $subscription->id),
												array(
													'class' => 'btn btn-outline-danger btn-sm',
													'confirm' => 'Are you sure you want to unsubscribe from this author?',
												)
											); ?>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				<?php endif; ?>
			</div>
		</div>
		
		<!-- My Books -->
		<div class="card">
			<div class="card-header d-flex justify-content-between align-items-center">
				<h5 class="mb-0">
					<i class="bi bi-journal-text"></i> My Books 
					<span class="badge bg-secondary"><?php echo $booksProvider->totalItemCount; ?></span>
				</h5>
				<?php if ($booksProvider->totalItemCount > 0): ?>
					<a href="<?php echo Yii::app()->createUrl('books/create'); ?>" class="btn btn-success btn-sm">
						<i class="bi bi-plus"></i> Add Book
					</a>
				<?php endif; ?>
			</div>
			<div class="card-body">
				<?php if ($booksProvider->totalItemCount == 0): ?>
					<div class="alert alert-info mb-0">
						<i class="bi bi-info-circle"></i> You haven't created any books yet.
						<br>
						<a href="<?php echo Yii::app()->createUrl('books/create'); ?>">Create your first book</a> to get started.
					</div>
				<?php else: ?>
					<div class="table-responsive">
						<table class="table table-hover">
							<thead>
								<tr>
									<th style="width: 60px;">Cover</th>
									<th>Title</th>
									<th>Year</th>
									<th>Authors</th>
									<th style="width: 150px;">Actions</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($booksProvider->data as $book): ?>
									<tr>
										<td>
											<?php if (!empty($book->cover_image)): ?>
												<img src="<?php echo Yii::app()->request->baseUrl . CHtml::encode($book->cover_image); ?>" 
													 alt="<?php echo CHtml::encode($book->title); ?>" 
													 class="rounded" style="width: 40px; height: 60px; object-fit: cover;">
											<?php else: ?>
												<div class="bg-secondary rounded d-flex align-items-center justify-content-center" 
													 style="width: 40px; height: 60px;">
													<i class="bi bi-book text-white" style="font-size: 0.8rem;"></i>
												</div>
											<?php endif; ?>
										</td>
										<td>
											<a href="<?php echo Yii::app()->createUrl('books/view', array('id' => $book->id)); ?>">
												<?php echo CHtml::encode($book->title); ?>
											</a>
										</td>
										<td><?php echo CHtml::encode($book->year_published); ?></td>
										<td>
											<?php if (!empty($book->authors)): ?>
												<?php 
												$authorNames = array();
												foreach ($book->authors as $author) {
													$authorNames[] = CHtml::encode($author->full_name);
												}
												echo implode(', ', $authorNames);
												?>
											<?php else: ?>
												<span class="text-muted">-</span>
											<?php endif; ?>
										</td>
										<td>
											<a href="<?php echo Yii::app()->createUrl('books/update', array('id' => $book->id)); ?>" 
											   class="btn btn-outline-primary btn-sm">
												<i class="bi bi-pencil"></i>
											</a>
											<?php echo CHtml::link(
												'<i class="bi bi-trash"></i>',
												array('books/delete', 'id' => $book->id),
												array(
													'class' => 'btn btn-outline-danger btn-sm',
													'confirm' => 'Are you sure you want to delete this book?',
												)
											); ?>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
					
					<!-- Pagination -->
					<?php if ($booksProvider->pagination->pageCount > 1): ?>
						<?php $this->widget('CLinkPager', array(
							'pages' => $booksProvider->pagination,
							'htmlOptions' => array('class' => 'pagination justify-content-center'),
							'header' => '',
							'firstPageLabel' => '<i class="bi bi-chevron-double-left"></i>',
							'lastPageLabel' => '<i class="bi bi-chevron-double-right"></i>',
							'prevPageLabel' => '<i class="bi bi-chevron-left"></i>',
							'nextPageLabel' => '<i class="bi bi-chevron-right"></i>',
						)); ?>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
