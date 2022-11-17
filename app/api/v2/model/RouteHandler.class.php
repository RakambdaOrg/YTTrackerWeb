<?php


	namespace YTT;

	use PDO;

	class RouteHandler
	{
		/**
		 * @param array $array
		 * @param array $values
		 *
		 * @return bool
		 */
		protected static function checkFields($array, $values)
		{
			foreach($values as $valueIndex => $value)
			{
				if(!isset($array[$value]))
					return false;
			}
			return true;
		}

		/**
		 * @return PDO
		 */
		protected function getConnection(){
			return DBConnection::getConnection();
		}
	}