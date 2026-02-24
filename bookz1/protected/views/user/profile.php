<?php
/**
 * User profile view.
 * 
 * @var User $user The user model
 * @var UserSubscription[] $subscriptions User's subscriptions
 * @var CActiveDataProvider $booksProvider User's books data provider
 */
?>

<h1>User Profile: <?php echo CHtml::encode($user->username); ?></h1>

<div class="profile-info">
    <h2>Account Information</h2>
    <table class="detail-view">
        <tr>
            <th>Username</th>
            <td><?php echo CHtml::encode($user->username); ?></td>
        </tr>
        <tr>
            <th>Email</th>
            <td><?php echo CHtml::encode($user->email); ?></td>
        </tr>
        <tr>
            <th>Phone</th>
            <td><?php echo CHtml::encode($user->phone); ?></td>
        </tr>
        <tr>
            <th>Role</th>
            <td><?php echo CHtml::encode($user->role); ?></td>
        </tr>
        <tr>
            <th>Registered</th>
            <td><?php echo CHtml::encode($user->created_at); ?></td>
        </tr>
    </table>
</div>

<div class="subscriptions">
    <h2>Author Subscriptions (<?php echo count($subscriptions); ?>)</h2>
    <?php if (empty($subscriptions)): ?>
        <p>You are not subscribed to any authors yet.</p>
        <p><?php echo CHtml::link('Browse authors', array('authors/index')); ?> to subscribe.</p>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Author</th>
                    <th>Subscribed At</th>
                    <th>Phone for Notifications</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subscriptions as $subscription): ?>
                <tr>
                    <td>
                        <?php echo CHtml::link(
                            CHtml::encode($subscription->author->full_name),
                            array('authors/view', 'id' => $subscription->author_id)
                        ); ?>
                    </td>
                    <td><?php echo CHtml::encode($subscription->subscribed_at); ?></td>
                    <td><?php echo CHtml::encode($subscription->phone_number); ?></td>
                    <td>
                        <?php echo CHtml::link(
                            'Unsubscribe',
                            array('subscriptions/unsubscribe', 'id' => $subscription->id),
                            array(
                                'class' => 'button',
                                'confirm' => 'Are you sure you want to unsubscribe from this author?'
                            )
                        ); ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<div class="my-books">
    <h2>My Books (<?php echo $booksProvider->totalItemCount; ?>)</h2>
    <?php if ($booksProvider->totalItemCount == 0): ?>
        <p>You haven't created any books yet.</p>
        <p><?php echo CHtml::link('Create a book', array('books/create')); ?></p>
    <?php else: ?>
        <?php $this->widget('zii.widgets.CListView', array(
            'dataProvider' => $booksProvider,
            'itemView' => '_bookItem',
            'summaryText' => 'Displaying {start}-{end} of {count} books',
            'emptyText' => 'No books found.',
        )); ?>
    <?php endif; ?>
</div>
