<?php
/**
 * Inline author creation form (for AJAX requests).
 * 
 * @var Author $author The author model
 */
?>

<form id="inline-author-form">
	<div class="mb-3">
		<label for="author_full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
		<input type="text" class="form-control" id="author_full_name" name="Author[full_name]" 
			   placeholder="Enter author's full name" required minlength="2" maxlength="255"
			   value="<?php echo CHtml::encode($author->full_name); ?>">
		<?php if ($author->hasErrors('full_name')): ?>
			<div class="text-danger small mt-1">
				<?php echo implode(', ', $author->getErrors('full_name')); ?>
			</div>
		<?php endif; ?>
	</div>
</form>
