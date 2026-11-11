<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>YouTube Tracker stats</title>
	<link rel="icon" href="favicon.ico">
	<link rel="stylesheet" href="assets/style.css">
	<script src="https://cdn.jsdelivr.net/npm/chart.js@4.6.1/dist/chart.umd.min.js" integrity="sha384-Emf5ZVIUiarAdhkgtqK+5/Z5qIvM6QoVrS+qAVtfyZVuD5lIHrac0me485+Antp0" crossorigin="anonymous"></script>
	<script type="module" src="assets/main.js"></script>
</head>
<body>
<nav>
	<img src="assets/icon128.png" alt="">
	<strong>YouTube Tracker</strong>
	<span class="spacer"></span>
	<a class="button" href="https://chrome.google.com/webstore/detail/youtube-tracker/knnlnielflnfhdohmihofhdelgahgjdb/" target="_blank" rel="noopener">Chrome extension</a>
	<a class="button" href="https://addons.mozilla.org/en-US/firefox/addon/youtube-tracker/" target="_blank" rel="noopener">Firefox add-on</a>
</nav>
<main>
	<section class="card">
		<div class="card-title">
			<h2>Users</h2>
			<span class="muted">Active in the last 14 days</span>
		</div>
		<div class="table-wrap">
			<table>
				<thead id="usersHead">
				<tr>
					<th class="name" rowspan="2" scope="col"><button type="button" data-col="0">User</button></th>
					<th colspan="3" scope="colgroup">Total</th>
					<th colspan="3" scope="colgroup">Last 7 days</th>
					<th colspan="3" scope="colgroup">Today (<?= date('Y-m-d') ?>)</th>
				</tr>
				<tr>
					<th scope="col"><button type="button" data-col="1">Opened</button></th>
					<th scope="col"><button type="button" data-col="2">Watched</button></th>
					<th scope="col"><button type="button" data-col="3">Count</button></th>
					<th scope="col"><button type="button" data-col="4">Opened</button></th>
					<th scope="col"><button type="button" data-col="5">Watched</button></th>
					<th scope="col"><button type="button" data-col="6">Count</button></th>
					<th scope="col"><button type="button" data-col="7">Opened</button></th>
					<th scope="col" aria-sort="descending"><button type="button" data-col="8">Watched</button></th>
					<th scope="col"><button type="button" data-col="9">Count</button></th>
				</tr>
				</thead>
				<tbody id="usersBody">
				<tr><td class="message" colspan="10">Loading…</td></tr>
				</tbody>
			</table>
		</div>
	</section>
	<section class="card">
		<div class="card-title">
			<h2>Charts</h2>
			<span class="spacer"></span>
			<label>Period
				<select id="period">
					<option value="7">Last 7 days</option>
					<option value="30" selected>Last 30 days</option>
					<option value="90">Last 90 days</option>
					<option value="365">Last year</option>
					<option value="3650">Everything</option>
				</select>
			</label>
		</div>
		<p class="muted" id="chartsStatus" hidden></p>
		<h3>Watched time</h3>
		<div class="chart"><canvas id="chartWatched" role="img" aria-label="Watched time per day, one line per user. The table above has the totals."></canvas></div>
		<h3>Opened time</h3>
		<div class="chart"><canvas id="chartOpened" role="img" aria-label="Opened time per day, one line per user. The table above has the totals."></canvas></div>
		<h3>Opened videos</h3>
		<div class="chart"><canvas id="chartCount" role="img" aria-label="Opened videos per day, one line per user. The table above has the totals."></canvas></div>
	</section>
</main>
</body>
</html>
