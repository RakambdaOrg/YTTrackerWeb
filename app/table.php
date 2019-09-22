<table id="dataTable" class="table table-striped table-bordered table-sm table-hover" cellspacing="0" width="100%">
    <thead class="thead-dark">
    <tr>
        <th class="userCell th-sm" scope="col" rowspan="2">User<br/>(most recent record)<br/>(oldest record)</th>
        <th class="th-sm" scope="col" colspan="3">Total</th>
        <th class="th-sm" scope="col" colspan="3">Last Week</th>
        <th class="th-sm" scope="col" colspan="3">Last 24h</th>
		<?php

			use YTT\UsersHandler;

			if($customPeriodDisplayed)
			{
				?>
                <th class="th-sm" colspan="3">Period</th>
				<?php
			}
		?>
    </tr>
    <tr>
        <th class="totalOpenedCell th-sm">Opened</th>
        <th class="totalWatchedCell th-sm">Watched</th>
        <th class="totalCountCell th-sm">Count</th>
        <th class="weekOpenedCell th-sm">Opened</th>
        <th class="weekWatchedCell th-sm">Watched</th>
        <th class="weekCountCell th-sm">Count</th>
        <th class="todayOpenedCell th-sm">Opened</th>
        <th class="todayWatchedCell th-sm">Watched</th>
        <th class="todayCountCell th-sm">Count</th>
		<?php
			if($customPeriodDisplayed)
			{
				?>
                <th class="periodOpenedCell th-sm">Opened</th>
                <th class="periodWatchedCell th-sm">Watched</th>
                <th class="periodCountCell th-sm">Count</th>
				<?php
			}
		?>
    </tr>
    </thead>
    <tbody>
	<?php
		{
			require_once __DIR__ . '/api/v2/model/UsersHandler.class.php';
			$userHandler = new UsersHandler();
			$usersRequest = $userHandler->getUsers(null, null);
			if($usersRequest['code'] === 200)
			{
				foreach($usersRequest['users'] as $userIndex => $user)
				{
					?>
                    <tr id="user<?php
						echo $user['uuid'];
					?>">
                        <td class="userCell">
                            <div>
                                <span class="username">
								<?php
									echo $user['username'] ? $user['username'] : $user['ID'];
								?>
                                </span>
                                <br/>
                                (
								<?php
									echo $handler->getMostRecentRecord($user['UUID']);
								?>
                                )
                                <br/>
                                (
								<?php
									echo $handler->getOldestRecord($user['UUID']);
								?>
                                )
                            </div>
                        </td>
                        <td class="totalOpenedCell">
							<?php
								echo $siteHelper->millisecondsToTimeString($handler->getTotalOpened($user['UUID']));
							?>
                        </td>
                        <td class="totalWatchedCell">
							<?php
								echo $siteHelper->millisecondsToTimeString($handler->getTotalWatched($user['UUID']));
							?>
                        </td>
                        <td class="totalCountCell">
							<?php
								echo $handler->getTotalOpenedCount($user['UUID']);
							?>
                        </td>
                        <td class="weekOpenedCell">
							<?php
								echo $siteHelper->millisecondsToTimeString($handler->getWeekOpened($user['UUID']));
							?>
                        </td>
                        <td class="weekWatchedCell">
							<?php
								echo $siteHelper->millisecondsToTimeString($handler->getWeekWatched($user['UUID']));
							?>
                        </td>
                        <td class="weekCountCell">
							<?php
								echo $handler->getWeekOpenedCount($user['UUID']);
							?>
                        </td>
                        <td class="todayOpenedCell">
							<?php
								echo $siteHelper->millisecondsToTimeString($handler->getLast24hOpened($user['UUID']));
							?>
                        </td>
                        <td class="todayWatchedCell">
							<?php
								echo $siteHelper->millisecondsToTimeString($handler->getLast24hWatched($user['UUID']));
							?>
                        </td>
                        <td class="todayCountCell">
							<?php
								echo $handler->getLast24hOpenedCount($user['UUID']);
							?>
                        </td>
						<?php
							if($customPeriodDisplayed)
							{
								$start = 'STR_TO_DATE("' . $_GET['startPeriod'] . '", "%Y-%m-%dT%H:%i")';
								$end = 'STR_TO_DATE("' . $_GET['endPeriod'] . ':59", "%Y-%m-%dT%H:%i:%s")';
								?>
                                <td class="periodOpenedCell">
									<?php
										echo $siteHelper->millisecondsToTimeString($handler->getPeriodOpened($user['UUID'], $start, $end));
									?>
                                </td>
                                <td class="periodWatchedCell">
									<?php
										echo $siteHelper->millisecondsToTimeString($handler->getPeriodWatched($user['UUID'], $start, $end));
									?>
                                </td>
                                <td class="periodCountCell">
									<?php
										echo $handler->getPeriodCount($user['UUID'], $start, $end)
									?>
                                </td>
								<?php
							}
						?>
                    </tr>
					<?php
				}
			}
			else
			{
				?>
                <tr>
                    <td colspan="4">Error while getting users</td>
                </tr>
				<?php
			}
		}
	?>
    </tbody>
</table>