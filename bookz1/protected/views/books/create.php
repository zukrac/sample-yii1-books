<?php
/**
 * Book creation form view.
 * 
 * @var Book $book The book model
 * @var Author[] $authors List of all authors for multi-select
 */

$this->breadcrumbs = array(
	'Books' => array('index'),
	'Create',
);

$this->pageTitle = 'Create New Book';
?>

<div class="row">
	<div class="col-md-12">
		<h1><i class="bi bi-plus-circle"></i> Create New Book</h1>
		<p class="text-muted">Fill in the details below to add a new book to the catalog</p>
	</div>
</div>

<div class="row mt-4">
	<div class="col-lg-8">
		<div class="card">
			<div class="card-body">
				<?php $form = $this->beginWidget('CActiveForm', array(
					'id' => 'book-form',
					'enableClientValidation' => true,
					'clientOptions' => array(
						'validateOnSubmit' => true,
					),
					'htmlOptions' => array(
						'enctype' => 'multipart/form-data',
						'role' => 'form',
					),
				)); ?>

				<p class="text-muted">Fields with <span class="text-danger">*</span> are required.</p>

				<?php if ($book->hasErrors()): ?>
					<div class="alert alert-danger">
						<i class="bi bi-exclamation-triangle"></i> Please fix the following errors:
						<?php echo $form->errorSummary($book); ?>
					</div>
				<?php endif; ?>

				<!-- Title -->
				<div class="mb-3">
					<?php echo $form->labelEx($book, 'title', array('class' => 'form-label')); ?>
					<?php echo $form->textField($book, 'title', array(
						'class' => 'form-control',
						'placeholder' => 'Enter book title',
						'maxlength' => 255,
					)); ?>
					<?php echo $form->error($book, 'title', array('class' => 'text-danger small')); ?>
				</div>

				<!-- Year Published -->
				<div class="mb-3">
					<?php echo $form->labelEx($book, 'year_published', array('class' => 'form-label')); ?>
					<?php echo $form->numberField($book, 'year_published', array(
						'class' => 'form-control',
						'placeholder' => 'e.g., 2024',
						'min' => 1000,
						'max' => 2100,
					)); ?>
					<?php echo $form->error($book, 'year_published', array('class' => 'text-danger small')); ?>
				</div>

				<!-- Authors Multi-Select -->
				<div class="mb-3">
					<label class="form-label">
						Authors <span class="text-danger">*</span>
					</label>
					<div class="row">
						<div class="col-md-10">
							<select name="Book[authorIds][]" id="Book_authorIds" class="form-select" multiple size="5" required>
								<?php foreach ($authors as $author): ?>
									<option value="<?php echo $author->id; ?>">
										<?php echo CHtml::encode($author->full_name); ?>
									</option>
								<?php endforeach; ?>
							</select>
							<small class="text-muted">Hold Ctrl/Cmd to select multiple authors</small>
						</div>
						<div class="col-md-2 d-flex align-items-end">
							<button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#authorModal">
								<i class="bi bi-person-plus"></i> New
							</button>
						</div>
					</div>
				</div>

				<!-- ISBN -->
				<div class="mb-3">
					<?php echo $form->labelEx($book, 'isbn', array('class' => 'form-label')); ?>
					<?php echo $form->textField($book, 'isbn', array(
						'class' => 'form-control',
						'placeholder' => 'e.g., 978-3-16-148410-0',
						'maxlength' => 20,
					)); ?>
					<small class="text-muted">Optional. ISBN must be unique if provided.</small>
					<?php echo $form->error($book, 'isbn', array('class' => 'text-danger small')); ?>
				</div>

				<!-- Description -->
				<div class="mb-3">
					<?php echo $form->labelEx($book, 'description', array('class' => 'form-label')); ?>
					<?php echo $form->textArea($book, 'description', array(
						'class' => 'form-control',
						'rows' => 5,
						'placeholder' => 'Enter book description...',
					)); ?>
					<?php echo $form->error($book, 'description', array('class' => 'text-danger small')); ?>
				</div>

				<!-- Cover Image -->
				<div class="mb-3">
					<?php echo $form->labelEx($book, 'cover_image', array('class' => 'form-label')); ?>
					<input type="file" name="Book[cover_image]" id="Book_cover_image" class="form-control" 
						   accept="image/jpeg,image/png,image/gif,image/webp">
					<small class="text-muted">Allowed formats: JPG, PNG, GIF, WEBP. Max size: 5MB</small>
					<?php echo $form->error($book, 'cover_image', array('class' => 'text-danger small')); ?>
				</div>

				<!-- Submit Buttons -->
				<div class="mt-4">
					<button type="submit" class="btn btn-success">
						<i class="bi bi-check-circle"></i> Create Book
					</button>
					<a href="<?php echo Yii::app()->createUrl('books/index'); ?>" class="btn btn-outline-secondary ms-2">
						<i class="bi bi-x-circle"></i> Cancel
					</a>
				</div>

				<?php $this->endWidget(); ?>
			</div>
		</div>
	</div>
</div>

<!-- Inline Author Creation Modal -->
<div class="modal fade" id="authorModal" tabindex="-1" aria-labelledby="authorModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="authorModalLabel">
					<i class="bi bi-person-plus"></i> Add New Author
				</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<form id="inline-author-form">
					<div class="mb-3">
						<label for="author_full_name" class="form-label">
							Full Name <span class="text-danger">*</span>
						</label>
						<input type="text" class="form-control" id="author_full_name" name="Author[full_name]" 
							   placeholder="Enter author's full name" required minlength="2" maxlength="255">
						<div id="author-error" class="text-danger small mt-1" style="display: none;"></div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-success" id="save-author-btn">
					<i class="bi bi-check"></i> Save Author
				</button>
			</div>
		</div>
	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
	const saveBtn = document.getElementById('save-author-btn');
	const authorInput = document.getElementById('author_full_name');
	const authorError = document.getElementById('author-error');
	const authorSelect = document.getElementById('Book_authorIds');
	
	saveBtn.addEventListener('click', function() {
		const fullName = authorInput.value.trim();
		
		if (fullName.length < 2) {
			authorError.textContent = 'Author name must be at least 2 characters.';
			authorError.style.display = 'block';
			return;
		}
		
		// Disable button during request
		saveBtn.disabled = true;
		saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Saving...';
		
		// Send AJAX request
		fetch('<?php echo Yii::app()->createUrl('authors/createInline'); ?>', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
				'X-Requested-With': 'XMLHttpRequest'
			},
			body: 'Author[full_name]=' + encodeURIComponent(fullName)
		})
		.then(response => response.json())
		.then(data => {
			saveBtn.disabled = false;
			saveBtn.innerHTML = '<i class="bi bi-check"></i> Save Author';
			
			if (data.success) {
				// Add new author to select
				const option = document.createElement('option');
				option.value = data.author.id;
				option.textContent = data.author.full_name;
				option.selected = true;
				authorSelect.appendChild(option);
				
				// Close modal and reset form
				const modal = bootstrap.Modal.getInstance(document.getElementById('authorModal'));
				modal.hide();
				authorInput.value = '';
				authorError.style.display = 'none';
			} else {
				// Show errors
				if (data.errors && data.errors.full_name) {
					authorError.textContent = data.errors.full_name.join(', ');
				} else {
					authorError.textContent = 'Failed to create author. Please try again.';
				}
				authorError.style.display = 'block';
			}
		})
		.catch(error => {
			saveBtn.disabled = false;
			saveBtn.innerHTML = '<i class="bi bi-check"></i> Save Author';
			authorError.textContent = 'An error occurred. Please try again.';
			authorError.style.display = 'block';
		});
	});
	
	// Hide error when user starts typing
	authorInput.addEventListener('input', function() {
		authorError.style.display = 'none';
	});
});
</script>