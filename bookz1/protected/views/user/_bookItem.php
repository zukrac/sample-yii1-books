<?php
/**
 * Book item partial view for user profile.
 * 
 * @var Book $data The book model
 */
?>

<div class="book-item">
    <h3>
        <?php echo CHtml::link(CHtml::encode($data->title), array('books/view', 'id' => $data->id)); ?>
    </h3>
    <div class="book-details">
        <span class="year">Year: <?php echo CHtml::encode($data->year_published); ?></span>
        <?php if ($data->isbn): ?>
            <span class="isbn">ISBN: <?php echo CHtml::encode($data->isbn); ?></span>
        <?php endif; ?>
    </div>
    <div class="book-authors">
        Authors: 
        <?php 
        $authorLinks = array();
        foreach ($data->authors as $author) {
            $authorLinks[] = CHtml::link(CHtml::encode($author->full_name), array('authors/view', 'id' => $author->id));
        }
        echo implode(', ', $authorLinks);
        ?>
    </div>
    <div class="book-actions">
        <?php echo CHtml::link('Edit', array('books/update', 'id' => $data->id), array('class' => 'button')); ?>
        <?php echo CHtml::link(
            'Delete',
            array('books/delete', 'id' => $data->id),
            array(
                'class' => 'button',
                'confirm' => 'Are you sure you want to delete "' . CHtml::encode($data->title) . '"?'
            )
        ); ?>
    </div>
</div>
