<?php

	namespace YTT
	{
		require_once __DIR__ . '/../model/GraphSupplier.php';

		class OpenedGraph extends GraphSupplier
		{
			function getTitle()
			{
				return 'Opened time';
			}

			function getID()
			{
				return 'Opened';
			}

			function getBalloonTooltip()
			{
				return "Opened: {value.formatDuration(\\\"hh\'h\' mm\'m\' ss\'s\'\\\")}";
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
				return "return '/api/v2/stats/' + uuid + '/opened';";
			}
		}
	}
