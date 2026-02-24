<?php
/**
 * Book item view for CListView.
 * 
 * @var Book $data The book model
 */
?>

<div class="col-md-6 col-lg-4 mb-4">
	<div class="card h-100 book-card">
		<?php if (!empty($data->cover_image)): ?>
			<div class="text-center pt-3">
				<img src="<?php echo Yii::app()->request->baseUrl . CHtml::encode($data->cover_image); ?>" 
					 alt="<?php echo CHtml::encode($data->title); ?>" 
					 class="cover-thumbnail rounded">
			</div>
		<?php else: ?>
			<div class="text-center pt-3">
				<div class="cover-thumbnail bg-secondary d-flex align-items-center justify-content-center rounded mx-auto">
					<i class="bi bi-book text-white" style="font-size: 2rem;"></i>
				</div>
			</div>
		<?php endif; ?>
		
		<div class="card-body">
			<h5 class="card-title">
				<a href="<?php echo Yii::app()->createUrl('books/view', array('id' => $data->id)); ?>">
					<?php echo CHtml::encode($data->title); ?>
				</a>
			</h5>
			
			<p class="card-text">
				<small class="text-muted">
					<i class="bi bi-calendar"></i> <?php echo CHtml::encode($data->year_published); ?>
				</small>
			</p>
			
			<?php if (!empty($data->isbn)): ?>
				<p class="card-text">
					<small class="text-muted">
						<i class="bi bi-upc-scan"></i> ISBN: <?php echo CHtml::encode($data->isbn); ?>
					</small>
				</p>
			<?php endif; ?>
			
			<?php if (!empty($data->authors)): ?>
				<div class="mb-2">
					<?php foreach ($data->authors as $index => $author): ?>
						<?php if ($index > 0) echo ', '; ?>
						<a href="<?php echo Yii::app()->createUrl('authors/view', array('id' => $author->id)); ?>" 
						   class="author-badge text-decoration-none">
							<i class="bi bi-person"></i> 
							<?php echo CHtml::encode($author->full_name); ?>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
		
		<div class="card-footer bg-transparent border-0 pb-3">
			<a href="<?php echo Yii::app()->createUrl('books/view', array('id' => $data->id)); ?>" 
			   class="btn btn-outline-primary btn-sm">
				<i class="bi bi-eye"></i> View Details
			</a>
		</div>
	</div>
</div>
