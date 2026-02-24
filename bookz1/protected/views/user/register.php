<?php
/**
 * User registration view.
 * 
 * @var User $model The user model for registration
 */
?>

<h1>Register</h1>

<p>Please fill out the following form to create a new account:</p>

<div class="form">
    <?php $form = $this->beginWidget('CActiveForm', array(
        'id' => 'register-form',
        'enableClientValidation' => true,
        'clientOptions' => array(
            'validateOnSubmit' => true,
        ),
    )); ?>

    <p class="note">Fields with <span class="required">*</span> are required.</p>

    <?php if ($model->hasErrors()): ?>
    <div class="errorSummary">
        <p>Please fix the following errors:</p>
        <?php echo $form->errorSummary($model); ?>
    </div>
    <?php endif; ?>

    <div class="row">
        <?php echo $form->labelEx($model, 'username'); ?>
        <?php echo $form->textField($model, 'username', array('size' => 60, 'maxlength' => 255)); ?>
        <?php echo $form->error($model, 'username'); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx($model, 'email'); ?>
        <?php echo $form->textField($model, 'email', array('size' => 60, 'maxlength' => 255)); ?>
        <?php echo $form->error($model, 'email'); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx($model, 'password'); ?>
        <?php echo $form->passwordField($model, 'password', array('size' => 60, 'maxlength' => 255)); ?>
        <?php echo $form->error($model, 'password'); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx($model, 'password_confirm'); ?>
        <?php echo $form->passwordField($model, 'password_confirm', array('size' => 60, 'maxlength' => 255)); ?>
        <?php echo $form->error($model, 'password_confirm'); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx($model, 'phone'); ?>
        <?php echo $form->textField($model, 'phone', array('size' => 15, 'maxlength' => 15)); ?>
        <p class="hint">Phone number must be 10-15 digits (e.g., 79001234567)</p>
        <?php echo $form->error($model, 'phone'); ?>
    </div>

    <div class="row buttons">
        <?php echo CHtml::submitButton('Register'); ?>
    </div>

    <div class="row">
        <p>Already have an account? <?php echo CHtml::link('Login here', array('user/login')); ?></p>
    </div>

    <?php $this->endWidget(); ?>
</div>
