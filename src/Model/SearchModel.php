<?php

	namespace Model;

	use Doctrine\DBAL\DBALException;
	use Silex\Application;
	//use Controller\SearchController;

	/**
	 * SearchModel
	 *
	 * @class SearchModel
	 * @author Katarzyna Drapala
	 * @link wierzba.wzks.uj.edu.pl/~12_drapala/silex
	 * @uses Doctrine\DBAL\DBALException
	 * @uses Silex\Application
	 */

	class SearchModel
	{
		/**
		 * Database access object.
		 *
		 * @access protected
		 * @var $_db Doctrine\DBAL
		 */
		protected $_db;

		/**
		 * Class constructor.
		 *
		 * @access public
		 * @param Appliction $app Silex application object
		 */
		public function __construct(Application $app)
		{
			$this->_db = $app['db'];
		}

	}
