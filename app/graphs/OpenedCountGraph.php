<?php

	namespace YTT
	{
		require_once __DIR__ . '/../model/GraphSupplier.php';

		class OpenedCountGraph extends GraphSupplier
		{
			function getTitle()
			{
				return 'Opened count';
			}

			function getID()
			{
				return 'OpenedCount';
			}

			function getBalloonTooltip()
			{
				return "Count: {value}";
			}

			protected function getLegendText()
			{
				return "{value}";
			}

			protected function isDurationGraph()
			{
				return false;
			}

			/**
			 * @inheritDoc
			 */
			function getUsersURL()
			{
				return "/api/v2/users";
			}

			/**
			 * @inheritDoc
			 */
			function getUserDataURLFunction()
			{
				return "return '/api/v2/' + uuid + '/stats/opened-count';";
			}
		}
	}
