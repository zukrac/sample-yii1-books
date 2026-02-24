<?php
/**
 * Authors list view.
 * 
 * @var CActiveDataProvider $dataProvider Authors data provider
 */

$this->breadcrumbs = array(
	'Authors',
);

$this->pageTitle = 'Authors';
?>

<div class="row">
	<div class="col-md-12">
		<h1><i class="bi bi-people"></i> Authors</h1>
		<p class="text-muted">Browse all authors in our catalog</p>
	</div>
</div>

<?php if ($dataProvider->totalItemCount > 0): ?>
	<div class="row mt-4">
		<?php foreach ($dataProvider->data as $author): ?>
			<div class="col-md-6 col-lg-4 mb-4">
				<div class="card h-100">
					<div class="card-body">
						<h5 class="card-title">
							<a href="<?php echo Yii::app()->createUrl('authors/view', array('id' => $author->id)); ?>">
								<i class="bi bi-person"></i> 
								<?php echo CHtml::encode($author->full_name); ?>
							</a>
						</h5>
						
						<?php if (!empty($author->biography)): ?>
							<p class="card-text text-truncate">
								<?php echo CHtml::encode($author->biography); ?>
							</p>
						<?php endif; ?>
						
						<p class="card-text">
							<span class="badge bg-primary">
								<i class="bi bi-journal-text"></i> 
								<?php echo count($author->books); ?> book(s)
							</span>
						</p>
					</div>
					<div class="card-footer bg-transparent border-0 pb-3">
						<a href="<?php echo Yii::app()->createUrl('authors/view', array('id' => $author->id)); ?>" 
						   class="btn btn-outline-primary btn-sm">
							<i class="bi bi-eye"></i> View Profile
						</a>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
	
	<!-- Pagination -->
	<?php $this->widget('CLinkPager', array(
		'pages' => $dataProvider->pagination,
		'htmlOptions' => array('class' => 'pagination justify-content-center'),
		'header' => '',
		'firstPageLabel' => '<i class="bi bi-chevron-double-left"></i>',
		'lastPageLabel' => '<i class="bi bi-chevron-double-right"></i>',
		'prevPageLabel' => '<i class="bi bi-chevron-left"></i>',
		'nextPageLabel' => '<i class="bi bi-chevron-right"></i>',
	)); ?>
	
	<p class="text-center text-muted">
		Displaying <?php echo $dataProvider->pagination->currentPage * $dataProvider->pagination->pageSize + 1; ?> - 
		<?php echo min(($dataProvider->pagination->currentPage + 1) * $dataProvider->pagination->pageSize, $dataProvider->totalItemCount); ?> 
		of <?php echo $dataProvider->totalItemCount; ?> authors
	</p>
<?php else: ?>
	<div class="alert alert-info mt-4">
		<i class="bi bi-info-circle"></i> No authors found in the catalog.
	</div>
<?php endif; ?>
