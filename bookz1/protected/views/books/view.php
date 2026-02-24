<?php
/**
 * Book detail view.
 * 
 * @var Book $book The book model
 * @var bool $isOwner Whether current user is the owner
 */

$this->breadcrumbs = array(
	'Books' => array('index'),
	$book->title,
);

$this->pageTitle = $book->title;
?>

<div class="row">
	<div class="col-md-12">
		<h1>
			<i class="bi bi-journal-text"></i> 
			<?php echo CHtml::encode($book->title); ?>
		</h1>
	</div>
</div>

<div class="row mt-4">
	<!-- Book Cover -->
	<div class="col-md-4 mb-4">
		<?php if (!empty($book->cover_image)): ?>
			<img src="<?php echo Yii::app()->request->baseUrl . CHtml::encode($book->cover_image); ?>" 
				 alt="<?php echo CHtml::encode($book->title); ?>" 
				 class="cover-image img-fluid rounded shadow">
		<?php else: ?>
			<div class="cover-image bg-secondary d-flex align-items-center justify-content-center rounded mx-auto" 
				 style="width: 200px; height: 300px;">
				<i class="bi bi-book text-white" style="font-size: 4rem;"></i>
			</div>
		<?php endif; ?>
		
		<?php if ($isOwner): ?>
			<div class="mt-3">
				<a href="<?php echo Yii::app()->createUrl('books/update', array('id' => $book->id)); ?>" 
				   class="btn btn-primary w-100 mb-2">
					<i class="bi bi-pencil"></i> Edit Book
				</a>
			</div>
			<div>
				<?php echo CHtml::link(
					'<i class="bi bi-trash"></i> Delete Book',
					array('books/delete', 'id' => $book->id),
					array(
						'class' => 'btn btn-danger w-100',
						'confirm' => 'Are you sure you want to delete this book? This action cannot be undone.',
					)
				); ?>
			</div>
		<?php endif; ?>
	</div>
	
	<!-- Book Details -->
	<div class="col-md-8">
		<div class="card">
			<div class="card-header">
				<h5 class="mb-0"><i class="bi bi-info-circle"></i> Book Details</h5>
			</div>
			<div class="card-body">
				<table class="table table-borderless mb-0">
					<tr>
						<th style="width: 150px;">Title:</th>
						<td><?php echo CHtml::encode($book->title); ?></td>
					</tr>
					<tr>
						<th>Year Published:</th>
						<td><?php echo CHtml::encode($book->year_published); ?></td>
					</tr>
					<?php if (!empty($book->isbn)): ?>
						<tr>
							<th>ISBN:</th>
							<td><code><?php echo CHtml::encode($book->isbn); ?></code></td>
						</tr>
					<?php endif; ?>
					<tr>
						<th>Authors:</th>
						<td>
							<?php if (!empty($book->authors)): ?>
								<?php foreach ($book->authors as $index => $author): ?>
									<?php if ($index > 0) echo '<br>'; ?>
									<a href="<?php echo Yii::app()->createUrl('authors/view', array('id' => $author->id)); ?>">
										<i class="bi bi-person"></i> 
										<?php echo CHtml::encode($author->full_name); ?>
									</a>
								<?php endforeach; ?>
							<?php else: ?>
								<span class="text-muted">No authors assigned</span>
							<?php endif; ?>
						</td>
					</tr>
					<?php if ($book->creator): ?>
						<tr>
							<th>Added By:</th>
							<td>
								<i class="bi bi-person-circle"></i> 
								<?php echo CHtml::encode($book->creator->username); ?>
								<small class="text-muted">(<?php echo CHtml::encode($book->created_at); ?>)</small>
							</td>
						</tr>
					<?php endif; ?>
					<?php if (!empty($book->updated_at) && $book->updated_at != $book->created_at): ?>
						<tr>
							<th>Last Updated:</th>
							<td><?php echo CHtml::encode($book->updated_at); ?></td>
						</tr>
					<?php endif; ?>
				</table>
			</div>
		</div>
		
		<?php if (!empty($book->description)): ?>
			<div class="card mt-3">
				<div class="card-header">
					<h5 class="mb-0"><i class="bi bi-text-left"></i> Description</h5>
				</div>
				<div class="card-body">
					<p class="card-text"><?php echo nl2br(CHtml::encode($book->description)); ?></p>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>

<div class="row mt-4">
	<div class="col-md-12">
		<a href="<?php echo Yii::app()->createUrl('books/index'); ?>" class="btn btn-outline-secondary">
			<i class="bi bi-arrow-left"></i> Back to Books
		</a>
	</div>
</div>
