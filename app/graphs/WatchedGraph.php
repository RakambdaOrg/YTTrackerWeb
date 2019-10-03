<?php

	namespace YTT
	{
		require_once __DIR__ . '/../model/GraphSupplier.php';

		class WatchedGraph extends GraphSupplier
		{
			function getTitle()
			{
				return 'Watched time';
			}

			function getID()
			{
				return 'Watched';
			}

			function getBalloonTooltip()
			{
				return "Watched: {value.formatDuration(\\\"hh\'h\' mm\'m\' ss\'s\'\\\")}";
			}

			protected function getLegendText()
			{
				return "{value.formatDuration(\\\"hh\'h\' mm\'m\' ss\'s\'\\\")}";
			}

			protected function isDurationGraph()
			{
				return true;
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
				return "return '/api/v2/stats/' + uuid + '/watched';";
			}
		}
	}
