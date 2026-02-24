<?php
/**
 * Books list view.
 * 
 * @var CActiveDataProvider $dataProvider Books data provider
 * @var Author[] $authors List of all authors for filter
 * @var array $years List of years for filter
 */

$this->breadcrumbs = array(
	'Books',
);

// Set page title
$this->pageTitle = 'Books Catalog';
?>

<div class="row">
	<div class="col-md-12">
		<h1><i class="bi bi-journal-text"></i> Books Catalog</h1>
		<p class="text-muted">Browse our collection of books</p>
	</div>
</div>

<!-- Filter Form -->
<div class="filter-form">
	<form method="get" action="<?php echo Yii::app()->createUrl('books/index'); ?>" class="row g-3">
		<div class="col-md-4">
			<label for="search" class="form-label">Search</label>
			<input type="text" class="form-control" id="search" name="search" 
				   placeholder="Search by title or ISBN..." 
				   value="<?php echo isset($_GET['search']) ? CHtml::encode($_GET['search']) : ''; ?>">
		</div>
		<div class="col-md-3">
			<label for="author_id" class="form-label">Author</label>
			<select class="form-select" id="author_id" name="author_id">
				<option value="">All Authors</option>
				<?php foreach ($authors as $author): ?>
					<option value="<?php echo $author->id; ?>" 
							<?php echo (isset($_GET['author_id']) && $_GET['author_id'] == $author->id) ? 'selected' : ''; ?>>
						<?php echo CHtml::encode($author->full_name); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="col-md-2">
			<label for="year" class="form-label">Year</label>
			<select class="form-select" id="year" name="year">
				<option value="">All Years</option>
				<?php foreach ($years as $year): ?>
					<option value="<?php echo $year; ?>" 
							<?php echo (isset($_GET['year']) && $_GET['year'] == $year) ? 'selected' : ''; ?>>
						<?php echo $year; ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="col-md-3 d-flex align-items-end">
			<button type="submit" class="btn btn-primary me-2">
				<i class="bi bi-search"></i> Filter
			</button>
			<a href="<?php echo Yii::app()->createUrl('books/index'); ?>" class="btn btn-outline-secondary">
				<i class="bi bi-x-circle"></i> Clear
			</a>
		</div>
	</form>
</div>

<?php if (!Yii::app()->user->isGuest): ?>
<div class="row mb-3">
	<div class="col-md-12">
		<a href="<?php echo Yii::app()->createUrl('books/create'); ?>" class="btn btn-success">
			<i class="bi bi-plus-circle"></i> Add New Book
		</a>
	</div>
</div>
<?php endif; ?>

<!-- Books List -->
<?php if ($dataProvider->totalItemCount > 0): ?>
	<?php $this->widget('zii.widgets.CListView', array(
		'dataProvider' => $dataProvider,
		'itemView' => '_view',
		'summaryText' => 'Displaying {start}-{end} of {count} books',
		'emptyText' => 'No books found.',
		'pager' => array(
			'class' => 'CLinkPager',
			'htmlOptions' => array('class' => 'pagination justify-content-center'),
			'header' => '',
			'firstPageLabel' => '<i class="bi bi-chevron-double-left"></i>',
			'lastPageLabel' => '<i class="bi bi-chevron-double-right"></i>',
			'prevPageLabel' => '<i class="bi bi-chevron-left"></i>',
			'nextPageLabel' => '<i class="bi bi-chevron-right"></i>',
		),
		'itemsTagName' => 'div',
		'itemsCssClass' => 'row',
	)); ?>
<?php else: ?>
	<div class="alert alert-info">
		<i class="bi bi-info-circle"></i> No books found matching your criteria.
		<?php if (!empty($_GET['search']) || !empty($_GET['author_id']) || !empty($_GET['year'])): ?>
			<a href="<?php echo Yii::app()->createUrl('books/index'); ?>">Clear filters</a>
		<?php endif; ?>
	</div>
<?php endif; ?>
