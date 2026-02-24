<?php
/**
 * Author detail view.
 * 
 * @var Author $author The author model
 * @var CActiveDataProvider $booksProvider Author's books data provider
 * @var bool $isSubscribed Whether current user is subscribed
 * @var UserSubscription $subscription User's subscription (if any)
 */

$this->breadcrumbs = array(
	'Authors' => array('index'),
	$author->full_name,
);

$this->pageTitle = $author->full_name;
?>

<div class="row">
	<div class="col-md-12">
		<h1>
			<i class="bi bi-person"></i> 
			<?php echo CHtml::encode($author->full_name); ?>
		</h1>
	</div>
</div>

<div class="row mt-4">
	<!-- Author Info -->
	<div class="col-md-4 mb-4">
		<div class="card">
			<div class="card-header">
				<h5 class="mb-0"><i class="bi bi-info-circle"></i> Author Info</h5>
			</div>
			<div class="card-body">
				<table class="table table-borderless mb-0">
					<tr>
						<th style="width: 100px;">Name:</th>
						<td><?php echo CHtml::encode($author->full_name); ?></td>
					</tr>
					<tr>
						<th>Books:</th>
						<td>
							<span class="badge bg-primary">
								<?php echo count($author->books); ?> book(s)
							</span>
						</td>
					</tr>
					<tr>
						<th>Added:</th>
						<td><?php echo CHtml::encode($author->created_at); ?></td>
					</tr>
				</table>
			</div>
		</div>
		
		<!-- Subscription Section -->
		<div class="card mt-3">
			<div class="card-header">
				<h5 class="mb-0"><i class="bi bi-bell"></i> Notifications</h5>
			</div>
			<div class="card-body">
				<?php if (Yii::app()->user->isGuest): ?>
					<!-- Guest Subscription Form -->
					<p class="text-muted">Subscribe to get notified when this author releases a new book.</p>
					<form id="guest-subscription-form" action="<?php echo Yii::app()->createUrl('subscriptions/subscribe'); ?>" method="post">
						<input type="hidden" name="<?php echo Yii::app()->request->csrfTokenName; ?>" value="<?php echo Yii::app()->request->csrfToken; ?>">
						<input type="hidden" name="author_id" value="<?php echo $author->id; ?>">
						<div class="mb-3">
							<label for="phone_number" class="form-label">Phone Number</label>
							<input type="tel" class="form-control" id="phone_number" name="phone_number" 
								   placeholder="e.g., 79001234567" required pattern="[0-9]{10,15}">
							<small class="text-muted">10-15 digits, no spaces or dashes</small>
						</div>
						<button type="submit" class="btn btn-success w-100">
							<i class="bi bi-bell-fill"></i> Subscribe
						</button>
					</form>
				<?php else: ?>
					<!-- Authenticated User Subscription -->
					<?php if ($isSubscribed): ?>
						<div class="alert alert-success mb-0">
							<i class="bi bi-check-circle"></i> You are subscribed to this author.
							<?php if ($subscription): ?>
								<br><small class="text-muted">Since: <?php echo CHtml::encode($subscription->subscribed_at); ?></small>
							<?php endif; ?>
						</div>
						<?php echo CHtml::link(
							'<i class="bi bi-bell-slash"></i> Unsubscribe',
							array('subscriptions/unsubscribe', 'id' => $subscription->id),
							array(
								'class' => 'btn btn-outline-danger w-100 mt-2',
								'confirm' => 'Are you sure you want to unsubscribe from this author?',
							)
						); ?>
					<?php else: ?>
						<p class="text-muted">Get notified when this author releases a new book.</p>
						<form action="<?php echo Yii::app()->createUrl('subscriptions/subscribe'); ?>" method="post">
							<input type="hidden" name="<?php echo Yii::app()->request->csrfTokenName; ?>" value="<?php echo Yii::app()->request->csrfToken; ?>">
							<input type="hidden" name="author_id" value="<?php echo $author->id; ?>">
							<?php $user = User::model()->findByPk(Yii::app()->user->id); ?>
							<?php if (empty($user->phone)): ?>
								<div class="mb-3">
									<label for="user_phone" class="form-label">Phone Number (optional)</label>
									<input type="tel" class="form-control" id="user_phone" name="phone_number" 
										   placeholder="e.g., 79001234567" pattern="[0-9]{10,15}">
									<small class="text-muted">10-15 digits for SMS notifications</small>
								</div>
							<?php else: ?>
								<div class="alert alert-info py-2">
									<small><i class="bi bi-phone"></i> Notifications will be sent to: <?php echo CHtml::encode($user->phone); ?></small>
								</div>
							<?php endif; ?>
							<button type="submit" class="btn btn-success w-100">
								<i class="bi bi-bell-fill"></i> Subscribe
							</button>
						</form>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</div>
	</div>
	
	<!-- Author's Books -->
	<div class="col-md-8">
		<div class="card">
			<div class="card-header">
				<h5 class="mb-0"><i class="bi bi-journal-text"></i> Books by <?php echo CHtml::encode($author->full_name); ?></h5>
			</div>
			<div class="card-body">
				<?php if ($booksProvider->totalItemCount > 0): ?>
					<div class="table-responsive">
						<table class="table table-hover">
							<thead>
								<tr>
									<th>Cover</th>
									<th>Title</th>
									<th>Year</th>
									<th>ISBN</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($booksProvider->data as $book): ?>
									<tr>
										<td style="width: 80px;">
											<?php if (!empty($book->cover_image)): ?>
												<img src="<?php echo Yii::app()->request->baseUrl . CHtml::encode($book->cover_image); ?>" 
													 alt="<?php echo CHtml::encode($book->title); ?>" 
													 class="rounded" style="width: 50px; height: 75px; object-fit: cover;">
											<?php else: ?>
												<div class="bg-secondary rounded d-flex align-items-center justify-content-center" 
													 style="width: 50px; height: 75px;">
													<i class="bi bi-book text-white"></i>
												</div>
											<?php endif; ?>
										</td>
										<td>
											<a href="<?php echo Yii::app()->createUrl('books/view', array('id' => $book->id)); ?>">
												<?php echo CHtml::encode($book->title); ?>
											</a>
											<?php if (!empty($book->authors)): ?>
												<br><small class="text-muted">
													<?php 
													$otherAuthors = array();
													foreach ($book->authors as $a) {
														if ($a->id != $author->id) {
															$otherAuthors[] = CHtml::encode($a->full_name);
														}
													}
													if (!empty($otherAuthors)) {
														echo 'with ' . implode(', ', $otherAuthors);
													}
													?>
												</small>
											<?php endif; ?>
										</td>
										<td><?php echo CHtml::encode($book->year_published); ?></td>
										<td>
											<?php if (!empty($book->isbn)): ?>
												<code><?php echo CHtml::encode($book->isbn); ?></code>
											<?php else: ?>
												<span class="text-muted">-</span>
											<?php endif; ?>
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
				<?php else: ?>
					<div class="alert alert-info mb-0">
						<i class="bi bi-info-circle"></i> This author has no books in the catalog yet.
					</div>
				<?php endif; ?>
			</div>
		</div>
		
		<!-- Biography -->
		<?php if (!empty($author->biography)): ?>
			<div class="card mt-3">
				<div class="card-header">
					<h5 class="mb-0"><i class="bi bi-text-left"></i> Biography</h5>
				</div>
				<div class="card-body">
					<p class="card-text"><?php echo nl2br(CHtml::encode($author->biography)); ?></p>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>

<div class="row mt-4">
	<div class="col-md-12">
		<a href="<?php echo Yii::app()->createUrl('authors/index'); ?>" class="btn btn-outline-secondary">
			<i class="bi bi-arrow-left"></i> Back to Authors
		</a>
	</div>
</div>
