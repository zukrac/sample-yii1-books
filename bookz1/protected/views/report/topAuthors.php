<?php
/**
 * TOP 10 Authors report view.
 * 
 * @var array $authors Array of author data
 * @var int $selectedYear Selected year for filter
 * @var array $years Available years for filter
 */

$this->breadcrumbs = array(
	'Reports' => array('#'),
	'TOP 10 Authors',
);

$this->pageTitle = 'TOP 10 Authors Report';
?>

<div class="row">
	<div class="col-md-12">
		<h1><i class="bi bi-trophy"></i> TOP 10 Authors by Book Count</h1>
		<p class="text-muted">Most productive authors for the selected year</p>
	</div>
</div>

<!-- Year Filter -->
<div class="row mt-4">
	<div class="col-md-12">
		<div class="filter-form">
			<form method="get" action="<?php echo Yii::app()->createUrl('report/topAuthors'); ?>" class="row g-3">
				<div class="col-md-4">
					<label for="year" class="form-label">Select Year</label>
					<select class="form-select" id="year" name="year">
						<?php foreach ($years as $year): ?>
							<option value="<?php echo $year; ?>" <?php echo $year == $selectedYear ? 'selected' : ''; ?>>
								<?php echo $year; ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="col-md-2 d-flex align-items-end">
					<button type="submit" class="btn btn-primary">
						<i class="bi bi-funnel"></i> Apply
					</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- Report Results -->
<div class="row mt-4">
	<div class="col-md-12">
		<?php if (!empty($authors)): ?>
			<div class="card">
				<div class="card-header">
					<h5 class="mb-0">
						<i class="bi bi-bar-chart"></i> 
						Results for <?php echo $selectedYear; ?>
						<span class="badge bg-secondary ms-2"><?php echo count($authors); ?> authors</span>
					</h5>
				</div>
				<div class="card-body">
					<div class="table-responsive">
						<table class="table table-hover">
							<thead>
								<tr>
									<th style="width: 80px;">Rank</th>
									<th>Author</th>
									<th class="text-center">Books in <?php echo $selectedYear; ?></th>
									<th class="text-center">Total Books</th>
									<th>Latest Book</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($authors as $index => $author): ?>
									<tr>
										<td>
											<?php 
											$rank = $index + 1;
											$rankClass = 'rank-other';
											if ($rank == 1) $rankClass = 'rank-1';
											elseif ($rank == 2) $rankClass = 'rank-2';
											elseif ($rank == 3) $rankClass = 'rank-3';
											?>
											<span class="rank-badge rounded-circle <?php echo $rankClass; ?>">
												<?php echo $rank; ?>
											</span>
										</td>
										<td>
											<a href="<?php echo Yii::app()->createUrl('authors/view', array('id' => $author['id'])); ?>">
												<i class="bi bi-person"></i> 
												<?php echo CHtml::encode($author['full_name']); ?>
											</a>
										</td>
										<td class="text-center">
											<span class="badge bg-primary fs-6">
												<?php echo (int)$author['books_in_year']; ?>
											</span>
										</td>
										<td class="text-center">
											<span class="badge bg-secondary">
												<?php echo (int)$author['total_books']; ?>
											</span>
										</td>
										<td>
											<?php if (!empty($author['latest_book'])): ?>
												<i class="bi bi-journal-text"></i> 
												<?php echo CHtml::encode($author['latest_book']); ?>
												<?php if (!empty($author['latest_book_year'])): ?>
													<small class="text-muted">(<?php echo $author['latest_book_year']; ?>)</small>
												<?php endif; ?>
											<?php else: ?>
												<span class="text-muted">-</span>
											<?php endif; ?>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			
			<!-- Summary Stats -->
			<div class="row mt-4">
				<div class="col-md-4">
					<div class="card text-center">
						<div class="card-body">
							<h6 class="card-title text-muted">Total Books in <?php echo $selectedYear; ?></h6>
							<h2 class="text-primary">
								<?php 
								$totalBooks = 0;
								foreach ($authors as $author) {
									$totalBooks += (int)$author['books_in_year'];
								}
								echo $totalBooks;
								?>
							</h2>
						</div>
					</div>
				</div>
				<div class="col-md-4">
					<div class="card text-center">
						<div class="card-body">
							<h6 class="card-title text-muted">Top Author</h6>
							<h5 class="text-success mb-0">
								<?php if (!empty($authors)): ?>
									<a href="<?php echo Yii::app()->createUrl('authors/view', array('id' => $authors[0]['id'])); ?>">
										<?php echo CHtml::encode($authors[0]['full_name']); ?>
									</a>
								<?php else: ?>
									-
								<?php endif; ?>
							</h5>
							<small class="text-muted">
								<?php echo !empty($authors) ? (int)$authors[0]['books_in_year'] . ' books' : ''; ?>
							</small>
						</div>
					</div>
				</div>
				<div class="col-md-4">
					<div class="card text-center">
						<div class="card-body">
							<h6 class="card-title text-muted">Average per Author</h6>
							<h2 class="text-info">
								<?php 
								echo !empty($authors) ? round($totalBooks / count($authors), 1) : 0;
								?>
							</h2>
						</div>
					</div>
				</div>
			</div>
		<?php else: ?>
			<div class="alert alert-info">
				<i class="bi bi-info-circle"></i> 
				No authors found with books published in <?php echo $selectedYear; ?>.
				<br>
				Try selecting a different year.
			</div>
		<?php endif; ?>
	</div>
</div>

<div class="row mt-4">
	<div class="col-md-12">
		<a href="<?php echo Yii::app()->createUrl('site/index'); ?>" class="btn btn-outline-secondary">
			<i class="bi bi-arrow-left"></i> Back to Home
		</a>
	</div>
</div>
