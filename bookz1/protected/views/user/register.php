<?php
/**
 * User registration view.
 * 
 * @var User $model The user model for registration
 */

$this->breadcrumbs = array(
	'Register',
);

$this->pageTitle = 'Register';
?>

<div class="row justify-content-center">
	<div class="col-md-8 col-lg-6">
		<div class="card shadow">
			<div class="card-body p-5">
				<div class="text-center mb-4">
					<i class="bi bi-person-plus" style="font-size: 3rem; color: #28a745;"></i>
					<h2 class="mt-2">Create Account</h2>
					<p class="text-muted">Register for a new account</p>
				</div>

				<?php $form = $this->beginWidget('CActiveForm', array(
					'id' => 'register-form',
					'enableClientValidation' => true,
					'clientOptions' => array(
						'validateOnSubmit' => true,
					),
					'htmlOptions' => array('role' => 'form'),
				)); ?>

				<p class="text-muted small">Fields with <span class="text-danger">*</span> are required.</p>

				<?php if ($model->hasErrors()): ?>
					<div class="alert alert-danger">
						<i class="bi bi-exclamation-triangle"></i> Please fix the following errors:
						<?php echo $form->errorSummary($model); ?>
					</div>
				<?php endif; ?>

				<!-- Username -->
				<div class="mb-3">
					<?php echo $form->labelEx($model, 'username', array('class' => 'form-label')); ?>
					<div class="input-group">
						<span class="input-group-text"><i class="bi bi-person"></i></span>
						<?php echo $form->textField($model, 'username', array(
							'class' => 'form-control',
							'placeholder' => 'Choose a username',
							'maxlength' => 255,
						)); ?>
					</div>
					<?php echo $form->error($model, 'username', array('class' => 'text-danger small')); ?>
				</div>

				<!-- Email -->
				<div class="mb-3">
					<?php echo $form->labelEx($model, 'email', array('class' => 'form-label')); ?>
					<div class="input-group">
						<span class="input-group-text"><i class="bi bi-envelope"></i></span>
						<?php echo $form->textField($model, 'email', array(
							'class' => 'form-control',
							'placeholder' => 'Enter your email',
							'maxlength' => 255,
							'type' => 'email',
						)); ?>
					</div>
					<?php echo $form->error($model, 'email', array('class' => 'text-danger small')); ?>
				</div>

				<!-- Password -->
				<div class="mb-3">
					<?php echo $form->labelEx($model, 'password', array('class' => 'form-label')); ?>
					<div class="input-group">
						<span class="input-group-text"><i class="bi bi-lock"></i></span>
						<?php echo $form->passwordField($model, 'password', array(
							'class' => 'form-control',
							'placeholder' => 'Create a password',
							'maxlength' => 255,
						)); ?>
					</div>
					<small class="text-muted">Minimum 6 characters</small>
					<?php echo $form->error($model, 'password', array('class' => 'text-danger small')); ?>
				</div>

				<!-- Password Confirm -->
				<div class="mb-3">
					<?php echo $form->labelEx($model, 'password_confirm', array('class' => 'form-label')); ?>
					<div class="input-group">
						<span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
						<?php echo $form->passwordField($model, 'password_confirm', array(
							'class' => 'form-control',
							'placeholder' => 'Confirm your password',
							'maxlength' => 255,
						)); ?>
					</div>
					<?php echo $form->error($model, 'password_confirm', array('class' => 'text-danger small')); ?>
				</div>

				<!-- Phone -->
				<div class="mb-3">
					<?php echo $form->labelEx($model, 'phone', array('class' => 'form-label')); ?>
					<div class="input-group">
						<span class="input-group-text"><i class="bi bi-phone"></i></span>
						<?php echo $form->textField($model, 'phone', array(
							'class' => 'form-control',
							'placeholder' => 'e.g., 79001234567',
							'maxlength' => 15,
						)); ?>
					</div>
					<small class="text-muted">Phone number for SMS notifications (10-15 digits)</small>
					<?php echo $form->error($model, 'phone', array('class' => 'text-danger small')); ?>
				</div>

				<!-- Submit Button -->
				<div class="d-grid mb-3">
					<button type="submit" class="btn btn-success btn-lg">
						<i class="bi bi-person-plus"></i> Create Account
					</button>
				</div>

				<?php $this->endWidget(); ?>

				<hr>

				<div class="text-center">
					<p class="mb-0">Already have an account?</p>
					<a href="<?php echo Yii::app()->createUrl('user/login'); ?>" class="btn btn-outline-primary mt-2">
						<i class="bi bi-box-arrow-in-right"></i> Login here
					</a>
				</div>
			</div>
		</div>
	</div>
</div>
