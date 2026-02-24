<?php
/**
 * User login view.
 * 
 * @var LoginForm $model The login form model
 */

$this->breadcrumbs = array(
	'Login',
);

$this->pageTitle = 'Login';
?>

<div class="row justify-content-center">
	<div class="col-md-6 col-lg-5">
		<div class="card shadow">
			<div class="card-body p-5">
				<div class="text-center mb-4">
					<i class="bi bi-person-circle" style="font-size: 3rem; color: #3498db;"></i>
					<h2 class="mt-2">Login</h2>
					<p class="text-muted">Sign in to your account</p>
				</div>

				<?php $form = $this->beginWidget('CActiveForm', array(
					'id' => 'login-form',
					'enableClientValidation' => true,
					'clientOptions' => array(
						'validateOnSubmit' => true,
					),
					'htmlOptions' => array('role' => 'form'),
				)); ?>

				<p class="text-muted small">Fields with <span class="text-danger">*</span> are required.</p>

				<!-- Username -->
				<div class="mb-3">
					<?php echo $form->labelEx($model, 'username', array('class' => 'form-label')); ?>
					<div class="input-group">
						<span class="input-group-text"><i class="bi bi-person"></i></span>
						<?php echo $form->textField($model, 'username', array(
							'class' => 'form-control',
							'placeholder' => 'Enter your username',
							'autofocus' => true,
						)); ?>
					</div>
					<?php echo $form->error($model, 'username', array('class' => 'text-danger small')); ?>
				</div>

				<!-- Password -->
				<div class="mb-3">
					<?php echo $form->labelEx($model, 'password', array('class' => 'form-label')); ?>
					<div class="input-group">
						<span class="input-group-text"><i class="bi bi-lock"></i></span>
						<?php echo $form->passwordField($model, 'password', array(
							'class' => 'form-control',
							'placeholder' => 'Enter your password',
						)); ?>
					</div>
					<?php echo $form->error($model, 'password', array('class' => 'text-danger small')); ?>
				</div>

				<!-- Remember Me -->
				<div class="mb-3 form-check">
					<?php echo $form->checkBox($model, 'rememberMe', array('class' => 'form-check-input')); ?>
					<?php echo $form->label($model, 'rememberMe', array('class' => 'form-check-label')); ?>
				</div>

				<!-- Submit Button -->
				<div class="d-grid mb-3">
					<button type="submit" class="btn btn-primary btn-lg">
						<i class="bi bi-box-arrow-in-right"></i> Login
					</button>
				</div>

				<?php $this->endWidget(); ?>

				<hr>

				<div class="text-center">
					<p class="mb-0">Don't have an account?</p>
					<a href="<?php echo Yii::app()->createUrl('user/register'); ?>" class="btn btn-outline-success mt-2">
						<i class="bi bi-person-plus"></i> Register here
					</a>
				</div>
			</div>
		</div>
	</div>
</div>
