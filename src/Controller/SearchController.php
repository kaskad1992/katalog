<?php
	/**
	 * Controller SearchController
	 *
	 * @class SearchController
	 * @author Katarzyna Drapala
	 * @link wierzba.wzks.uj.edu.pl/~12_drapala/silex
	 * @uses Silex\Application
	 * @uses Silex\ControllerProviderInterface
	 * @uses Symfony\Component\HttpFoundation\Request
	 * @uses Symfony\Component\Validator\Constraints
	 * @uses Model\SearchModel;
	 */

	namespace Controller;

	use Silex\Application;
	use Silex\ControllerProviderInterface;
	use Symfony\Component\HttpFoundation\Request;
	use Symfony\Component\Validator\Constraints as Assert;
	use Model\SearchModel;

	class SearchController implements ControllerProviderInterface
	{
		protected $_model;

		public function connect(Application $app)
		{
			$searchController = $app['controllers_factory'];
			$searchController
				->get('/', array($this, 'index'))
				->bind('/search/');
			$searchController
				->get('/results/', array($this, 'results'))
				->bind('/search/results');
			return $searchController;
		}

		/**
		 * Search a book.
		 *
		 * @access public
		 * @return view search/index.twig
		 */
		public function index(Application $app, Request $request)
		{
			return $app['twig']->render('search/index.twig', array());
		}

		/**
		 * View results.
		 *
		 * @access public
		 * @return view search/results.twig
		 */

		public function results(Application $app, Request $request)
		{
			return $app['twig']->render('search/results.twig', array());
		}
	}
