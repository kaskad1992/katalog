<?php
	/**
	 * Controller UsersController
	 *
	 * @class UsersController
	 * @author Katarzyna Drapala
	 * @link wierzba.wzks.uj.edu.pl/~12_drapala/silex
	 * @uses Silex\Application
	 * @uses Silex\ControllerProviderInterface
	 * @uses Symfony\Component\HttpFoundation\Request
	 * @uses Symfony\Component\Validator\Constraints as Assert
	 * @uses Model\UsersModel
	 * @uses Model\BooksModel
	 */

	namespace Controller;

	use Silex\Application;
	use Silex\ControllerProviderInterface;
	use Symfony\Component\HttpFoundation\Request;
	use Symfony\Component\Validator\Constraints as Assert;
	use Symfony\Component\Config\Definition\Exception\Exception;
	use Model\UsersModel;
	use Model\BooksModel;

	class UsersController implements ControllerProviderInterface
	{
		protected $_model;
		
		public function connect(Application $app)
		{
			$usersController = $app['controllers_factory'];
			$usersController->get('/{page}', array($this, 'index'))
				->value('page', 1)->bind('/users/'); 
			$usersController->match('/register/', array($this, 'register'))
				->bind('/users/register'); 
			$usersController->match('/edit/{id}', array($this, 'edit'))
				->bind('/users/edit');
			// $usersController
				// ->match('/editpassword/{id}', array($this, 'editpassword'))
				// ->bind('/users/editpassword');
			$usersController->match('/delete/{id}', array($this, 'delete'))
				->bind('/users/delete');
			$usersController->get('/view/{id}', array($this, 'view'))
				->bind('/users/view');
			$usersController->get('/sorry/', array($this, 'sorry'))
				->bind('/users/sorry');
			return $usersController;
		}
		
		/**
		 * View all users
		 *
		 * @param Application $app application object
		 * @param Request $request request
		 *
		 * @access public
		 * @return mixed Generates page or redirect.
		 */
		public function index(Application $app, Request $request)
		{
			if ($app['security']->isGranted('ROLE_USER')) {
				$pageLimit = 8;
				$page = (int) $request->get('page', 1);
				$usersModel = new UsersModel($app);
				$currentuser = $usersModel->getIdCurrentUser($app);
				$pagesCount = $usersModel->countUsersPages($pageLimit);
				if (($page < 1) || ($page > $pagesCount)) {
					$page = 1;
				}
				$users = $usersModel
					->getUsersPage($page, $pageLimit, $pagesCount);
				$paginator = array(
					'page' => $page, 
					'pagesCount' => $pagesCount
				);
			
				return $app['twig']->render(
					'users/index.twig', array(
						'users' => $users, 
						'paginator' => $paginator, 
						'currentuser' => $currentuser
					)
				);
			} else {
				return $app->redirect(
					$app['url_generator']->generate('/auth/login'), 301
				);
			}
		}

		/**
		 * Add new user.
		 *
		 * @param Application $app application object
		 * @param Request $request request
		 *
		 * @access public
		 * @return mixed Generates page or redirect.
		 */
		public function register(Application $app, Request $request)
		{        
			if ($app['security']->isGranted('ROLE_USER') && !$app['security']->isGranted('ROLE_USER')) {
				$app['session']
					->getFlashBag()
					->add(
						'message', array(
							'type' => 'error', 
							'content' => 'Posiadasz już konto!'
						)
					);
				return $app->redirect(
					$app['url_generator']->generate('/users/'), 301
				);
			}
			$data = array(
				'login' => 'Login',
				'password' => 'Hasło',
				//'idRole' => '2',
				//'name' => 'Imie',
				//'surname' => 'Nazwisko',
				//'email' => 'E-mail',
			);

			$form = $app['form.factory']->createBuilder('form', $data)
				->add(
					'login', 'text', array(
						'constraints' => array(
							new Assert\NotBlank(), 
							new Assert\Length(
								array(
									'min' => 4, 
									'minMessage' => 'Login musi mieć 
									minimum 4 znaki.'
								)
							), 
							new Assert\Type(
								array(
									'type' => 'string', 
									'message' => 'Wpisz poprawny login.'
								)
							)
						)
					)
				)
				->add(
					'password', 'password', array(
						'constraints' => array(
							new Assert\NotBlank(), 
							new Assert\Length(
								array(
									'min' => 5, 
									'minMessage' => 'Hasło musi mieć 
									minimum 5 znaków.'
								)
							), 
							new Assert\Type(
								array(
									'type' => 'string', 
									'message' => 'Wpisz poprawne hasło.'
								)
							)
						)
					)
				)
				->add(
					'confirm_password', 'password', array(
						'constraints' => array(
							new Assert\NotBlank(), 
							new Assert\Length(
								array(
									'min' => 5, 
									'minMessage' => 'Potwierdź hasło.'
								)
							)
						)
					)
				)
				
				->add('Potwierdź', 'submit')
				->getForm();

			$form->handleRequest($request);

			if ($form->isValid()) {
				$data = $form->getData();
				$usersModel = new UsersModel($app);
				if ($usersModel->nameExist($data['login'])) {
					$app['session']->getFlashBag()->add(
						'message', array(
							'type' => 'warning', 
							'content' => 'Login jest już zajęty, 
							wybierz inny.'
						)
					);
					return $app['twig']->render(
						'users/register.twig', array(
							'form' => $form->createView()
						)
					);
				}
		
				if ($data['password'] === $data['confirm_password']) {
					$data['password'] = $app['security.encoder.digest']
						->encodePassword("{$data['password']}", '');
					$usersModel = new UsersModel($app);
					$lastUserId = $usersModel->addUser($data);
					$usersModel->addRole(array('id' => $lastUserId));
					
					$app['session']->getFlashBag()->add(
						'message', array(
							'type' => 'success', 
							'content' => 'Rejestracja przebiegła pomyślnie! 
							Możesz zalogować się na swoje konto.'
						)
					);
					return $app->redirect(
						$app['url_generator']->generate('/auth/login'), 301
					);
				} else {
					$app['session']->getFlashBag()
						->add(
							'message', array(
								'type' => 'warning', 
								'content' => 'Hasła muszą być identyczne!'
							)
						);
					return $app['twig']->render(
						'users/register.twig', array(
							'form' => $form->createView()
						)
					);
				}
			}

			return $app['twig']->render(
				'users/register.twig', array(
					'form' => $form->createView()
				)
			);
		}

		/**
		 * Edit user's data.
		 *
		 * @param Application $app application object
		 * @param Request $request request
		 * @access public
		 * @return mixed Generates page or redirect.
		 */
		public function edit(Application $app, Request $request)
		{
			if (!$app['security']->isGranted('ROLE_USER')) {
				return $app->redirect(
					$app['url_generator']->generate('/auth/login'), 301
				);
			}
			$usersModel = new UsersModel($app);
			$id = (int)$request->get('id', 0);
			$user = $usersModel->getUser($id);
			$currentuser = $usersModel->getIdCurrentUser($app); //id
			
			if (!($currentuser==$id || $app['security']->isGranted('ROLE_USER'))) {
				$app['session']->getFlashBag()->add(
					'message', array(
						'type' => 'error', 
						'content' => 'Nie masz uprawnień do korzystania z tej strony.'
					)
				);
				return $app->redirect(
					$app['url_generator']->generate('/books/'), 301
				); 
			}
			
			$data = array(
				'id' => $user['id'],
				'login' => $user['login'],
			);

			if (count($user) && $usersModel->idExist($id)) {

				$form = $app['form.factory']->createBuilder('form', $data)
					->add('id', 'hidden', array('data' => $id,))
					->add('login', 'hidden', array('data' => $login,))
					->add('zapisz', 'submit')
					->getForm();
				$form->handleRequest($request);

				if ($form->isValid()) {
					$data = $form->getData();
					$model = $usersModel->editUser($data);
					if (!$model) {
						$app['session']->getFlashBag()->add(
							'message', array(
								'type' => 'success', 
								'content' => 'Dane zostaly zmienione!'
							)
						);
						return $app->redirect(
							$app['url_generator']->generate(
								'/users/view', array('id' => $id)
							)
						);
					}
				}
				return $app['twig']->render(
					'users/edit.twig', array(
						'user' => $user, 
						'form' => $form->createView()
					)
				);
			} else {
				$app['session']->getFlashBag()->add(
					'message', array(
						'type' => 'error', 
						'content' => 'Użytkownik nie istnieje. 
						Utworz nowe konto:'
					)
				);
				return $app->redirect(
					$app['url_generator']->generate('/users/add'), 301
				);
			}
		}

		/**
		 * Block user's account
		 *
		 * @param Application $app application object
		 * @param Request $request request
		 *
		 * @access public
		 * @return mixed Generates page or redirect.
		 */
		public function block(Application $app, Request $request)
		{
			
		}
		
		/**
		 * View user's profile
		 *
		 * @param Application $app application object
		 * @param Request $request request
		 *
		 * @access public
		 * @return mixed Generates page or redirect.
		 */
		public function view(Application $app, Request $request) 
		{
			if ($app['security']->isGranted('ROLE_USER')) {
				$id = (int) $request->get('id', 0);
				$usersModel = new UsersModel($app);
				if ($usersModel->idExist($id)) {
					$user = $usersModel->getUser($id);
					$currentuser = $usersModel->getIdCurrentUser($app);
					return $app['twig']->render(
						'users/view.twig', array(
							'user' => $user, 
							'currentuser' => $currentuser
						)
					);
				} else {
					$app['session']->getFlashBag()->add(
						'message', array(
							'type' => 'error', 
							'content' => 'Podany użytkownik nie istnieje!'
						)
					);
					return $app->redirect(
						$app['url_generator']->generate('/books/'), 301
					);
				}
			} else {
				return $app->redirect(
					$app['url_generator']->generate('/auth/login'), 301
				);
			}
    
		}

		/**
		 * Not found
		 *
		 * @param Application $app application object
		 * @param Request $request request
		 *
		 * @access public
		 * @return mixed Generates page.
		 */
		public function sorry(Application $app, Request $request)
		{
			return $app['twig']->render('users/sorry.twig');
		}
		
	}