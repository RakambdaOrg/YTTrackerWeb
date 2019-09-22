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
		 * @param array $to
		 * @param string $object
		 * @param string $body
		 * @param string $from The name of the sender that'll be displayed
		 * @return bool If the message was sent
		 */
		public static function sendMail($to, $object = '', $body = '', $from = 'mrcraftcod@mrcraftcod.fr')
		{
			$headers = "MIME-Version: 1.0" . "\n";
			$headers .= "Content-Type: text/html; charset=UTF-8" . "\n";
			$headers .= 'From: ' . $from . ' <mrcraftcod@mrcraftcod.fr>' . "\n";
			$headers .= "Disposition-Notification-To: $from" . "\n";
			$headers .= 'Reply-To: mrcraftcod@mrcraftcod.fr' . "\n";
			$headers .= 'X-Mailer: PHP/' . phpversion() . "\n";
			$headers .= "X-Priority: 1" . "\n";
			$headers .= "X-MSMail-Priority: High" . "\n";
			return mail(implode(',', $to), $object, $body, $headers);
		}

		/**
		 * @return PDO
		 */
		protected function getConnection(){
			return DBConnection::getConnection();
		}
	}