<?php

	/**
	 * Controller AuthController
	 *
	 * @class AuthController
	 * @author Katarzyna Drapala
	 * @link wierzba.wzks.uj.edu.pl/~12_drapala
	 * @uses Silex\Application
	 * @uses Silex\ControllerProviderInterface
	 * @uses Symfony\Component\HttpFoundation\Request
	 * @uses Symfony\Component\Validator\Constraints as Assert
	 * @uses Model\UsersModel
	 *
	 */
	 
	namespace Controller;

	use Silex\Application;
	use Silex\ControllerProviderInterface;
	use Symfony\Component\HttpFoundation\Request;
	use Symfony\Component\Validator\Constraints as Assert;
	use Model\UsersModel;

	class AuthController implements ControllerProviderInterface
	{
		/**
		 * Connection
		 *
		 * @param Application $app application object
		 * @return \Silex\ControllerCollection
		 */ 
		public function connect(Application $app)
		{
			$authController = $app['controllers_factory'];
			$authController->match(
				'/login', array($this, 'login')
			)->bind('/auth/login');
			$authController->match(
				'/logout', array($this, 'logout')
			)->bind('/auth/logout');
			return $authController;
		}
		
		/**
		 * Login
		 *
		 * @param Application $app application object
		 * @param Request $request request
		 * @access public
		 * @return mixed Generates page.
		 */
		public function login(Application $app, Request $request)
		{
			$data = array();
			$form = $app['form.factory']->createBuilder('form')
				->add(
					'username', 
					'text', 
					array(
						'label' => 'Username', 
						'data' => $app['session']->get('_security.last_username')
					)
				)
				->add(
					'password', 
					'password', 
					array('label' => 'Password')
				)
				->add('Zaloguj', 'submit')
				->getForm();

			//$form->handleRequest($request);

			return $app['twig']->render(
				'auth/login.twig', array(
					'form' => $form->createView(),
					'error' => $app['security.last_error']($request)
				)
			);
		}
		
		/**
		 * Logout
		 *
		 * @param Application $app application object
		 * @param Request $request request
		 * @access public
		 * @return mixed Generates page.
		 */
		public function logout(Application $app, Request $request)
		{
			$app['session']->clear();
			return $app['twig']->render('auth/logout.twig');
		}
	}