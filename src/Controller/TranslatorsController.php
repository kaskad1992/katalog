<?php
	/**
	 * Controller TranslatorsController
	 *
	 * @class TranslatorsController
	 * @author Katarzyna Drapala
	 * @link wierzba.wzks.uj.edu.pl/~12_drapala/silex
	 * @uses Silex\Application
	 * @uses Silex\ControllerProviderInterface
	 * @uses Symfony\Component\HttpFoundation\Request
	 * @uses Symfony\Component\Validator\Constraints
	 * @uses Model\TranslatorsModel
	 * @uses Model\BooksModel
	 */
	 
	namespace Controller;

	use Silex\Application;
	use Silex\ControllerProviderInterface;
	use Symfony\Component\HttpFoundation\Request;
	use Symfony\Component\Validator\Constraints as Assert;
	use Model\TranslatorsModel;
	use Model\BooksModel;

	class TranslatorsController implements ControllerProviderInterface
	{
		protected $_model;

		/**
		 * Connection
		 *
		 * @param Application $app application object
		 * @access public
		 * @return \Silex\ControllerCollection
		 */
		public function connect(Application $app)
		{
			$translatorsController = $app['controllers_factory'];
			$translatorsController
				->match('/add/', array($this, 'add'))
				->bind('/translators/add'); 
			$translatorsController
				->match('/edit/{id}', array($this, 'edit'))
				->bind('/translators/edit');
			$translatorsController
				->match('/delete/{id}', array($this, 'delete'))
				->bind('/translators/delete');
			$translatorsController
				->get('/view/{id}', array($this, 'view'))
				->bind('/translators/view');
			$translatorsController
				->match('/connect/{id}', array($this, 'connectTranslator'))
				->bind('/translators/connect');
			// $translatorsController
				// ->match(
					// '/disconnect/{id}/{idBook}', 
					// array($this, 'disconnectTranslator')
				// )
				// ->bind('/translators/disconnect');
			$translatorsController
				->match('/manage/{id}', array($this, 'manageTranslator'))
				->bind('/translators/manage');
			$translatorsController
				->match('/controlpanel/{page}', array($this, 'controlTranslator'))
				->value('page', 1)->bind('/translators/controlpanel');
			$translatorsController
                ->get('/{page}', array($this, 'index'))
                ->value('page', 1)->bind('/translators/');
			return $translatorsController;
		}

		/**
		 * List of one book's translators.
		 *
		 * @param Application $app application object
		 * @param Request $request request
		 * @access public
		 * @return mixed Generates page.
		 */
		public function index(Application $app, Request $request)
		{
			$pageLimit = 6;
			$page = (int) $request->get('page', 1);
			$translatorsModel = new TranslatorsModel($app);
			$pagesCount = $translatorsModel->countTranslatorsPages($pageLimit);
			if (($page < 1) || ($page > $pagesCount)) {
				$page = 1;
			}
			$translators = $translatorsModel->getTranslatorsPage(
				$page, $pageLimit, $pagesCount
			);
			$paginator = array('page' => $page, 'pagesCount' => $pagesCount);

			return $app['twig']->render(
				'translators/control.twig', array(
					'translators' => $translators, 'paginator' => $paginator
				)
			);
		}    
		
		/**
		 * Add new translator.
		 *
		 * @param Application $app application object
		 * @param Request $request request
		 * @access public
		 * @return mixed Generates page or Redirect.
		 */
		public function add(Application $app, Request $request)
		{
/*			if (!$app['security']->isGranted('ROLE_ADMIN')) {
				$app['session']->getFlashBag()->add(
					'message', array(
						'type' => 'error', 
						'content' => 'Nie masz uprawnień do korzystania z tej strony.'
					)
				);
				return $app->redirect(
					$app['url_generator']->generate('/translators/controlpanel'), 301
				); 
			} */
			$data = array(
				'name' => 'Imie tłumacza',
				'surname' => 'Nazwisko tłumacza',
			);

			$form = $app['form.factory']->createBuilder('form', $data)
				->add(
					'name', 'text', array(
						'constraints' => array(
							new Assert\NotBlank(), 
							new Assert\Length(
								array(
									'min' => 2, 
									'minMessage' => 'Minimalna ilość znaków: 2'
								)
							), 
							new Assert\Type(
								array(
									'type' => 'string', 
									'message' => 'Wpisz poprawne imię.'
								)
							)
						)
					)
				)
				->add(
					'surname', 'text', array(
						'constraints' => array(
							new Assert\NotBlank(), 
							new Assert\Length(
								array(
									'min' => 2, 
									'minMessage' => 'Minimalna ilość znaków: 2'
								)
							), 
							new Assert\Type(
								array(
									'type' => 'string', 
									'message' => 'Wpisz poprawne nazwisko.'
								)
							)
						)
					)
				)
				->add('dodaj', 'submit')
				->getForm();

			$form->handleRequest($request);

			if ($form->isValid()) {
				$translatorsModel = new TranslatorsModel($app);
				$translatorsModel->addTranslator($form->getData());
				$app['session']->getFlashBag()->add(
					'message', array(
						'type' => 'success', 
						'content' => 'Dodano nowego tłumacza.'
					)
				);
				return $app->redirect(
					$app['url_generator']->generate('/translators/controlpanel'), 301
				); 
			}

			return $app['twig']->render(
				'translators/add.twig', array('form' => $form->createView())
			);
		}
		
		/**
		 * Edit an translator.
		 *
		 * @param Application $app application object
		 * @param Request $request request
		 * @access public
		 * @return mixed Generates page or Redirect.
		 */
		public function edit(Application $app, Request $request)
		{
/*			if (!$app['security']->isGranted('ROLE_ADMIN')) {
            $app['session']
                ->getFlashBag()
                ->add(
                    'message', array(
                        'type' => 'error', 
                        'content' => 'Nie masz uprawnień do korzystania z tej strony.'
                    )
                ); 
            return $app->redirect(
                $app['url_generator']->generate('/translators/controlpanel'), 301
            ); */
        
	      $translatorsModel = new TranslatorsModel($app);

			$id = (int) $request->get('id', 0);
			$translator = $translatorsModel->getTranslator($id);
			
			$data = array(
				'id'=> $translator['id'],
				'name'=> $translator['name'],
				'surname'=> $translator['surname'],
				);
			
			if (count($translator) && $translatorsModel->idExist($id)) {

				$form = $app['form.factory']->createBuilder('form', $data)
				->add('id', 'hidden', array('data'=> $id,))
				->add(
					'name', 'text', array(
						'constraints' => array(
							new Assert\NotBlank(), 
							new Assert\Length(
								array(
									'min' => 2, 
									'minMessage' => 'Minimalna ilość znaków: 2'
								)
							), 
							new Assert\Type(
								array(
									'type' => 'string', 
									'message' => 'Wpisz poprawne imię.')
							)
						)
					)
				)
				->add(
					'surname', 'text', array(
						'constraints' => array(
							new Assert\NotBlank(), 
							new Assert\Length(
								array(
									'min' => 2, 
									'minMessage' => 'Minimalna ilość znaków: 2'
								)
							), 
							new Assert\Type(
								array(
									'type' => 'string', 
									'message' => 'Wpisz poprawne nazwisko.'
								)
							)
						)
					)
				)
				->add('zapisz', 'submit')
					->getForm();

				$form->handleRequest($request);

				if ($form->isValid()) {
					$translatorsModel = new TranslatorsModel($app);
					$translatorsModel->saveTranslator($form->getData());
					$app['session']->getFlashBag()->add(
						'message', array(
							'type' => 'success', 
							'content' => 'Zmieniono dane tłumacza.'
						)
					);
					return $app->redirect(
						$app['url_generator']->generate(
							'/translators/view', array('id' => $id)
						)
					); 
				}

				return $app['twig']->render(
					'translators/edit.twig', array(
						'form' => $form->createView(), 'translator' => $translator
					)
				);

			} else {
				$app['session']->getFlashBag()->add(
					'message', array(
						'type' => 'error', 
						'content' => 'Tłumacz o podanym id nie istnieje. 
						Dodaj nowego tłumacza:'
					)
				);
				return $app->redirect(
					$app['url_generator']->generate('/translators/add'), 301
				);
			}
		}
		
		/**
		 * Delete an translator.
		 *
		 * @param Application $app application object
		 * @param Request $request request
		 * @access public
		 * @return mixed Generates page or Redirect.
		 */
		public function delete(Application $app, Request $request)
		{
			/*			if (!$app['security']->isGranted('ROLE_ADMIN')) {
            $app['session']->getFlashBag()->add(
                'message', array(
                    'type' => 'error', 
                    'content' => 'Nie masz uprawnień do korzystania z tej strony.'
                )
            ); 
            return $app->redirect(
                $app['url_generator']->generate('/translators/controlpanel'), 301
            ); */
        
			$translatorsModel = new TranslatorsModel($app);
	 
			$id = (int) $request->get('id');
			if (ctype_digit((string)$id) && $translatorsModel->idExist($id)) {
				$translator = $translatorsModel->getTranslator($id);      
			} else {
				$app['session']->getFlashBag()->add(
					'message', array(
						'type' => 'error', 
						'content' => 'Tłumacz o podanym id nie istnieje.'
					)
				);
				return $app->redirect(
					$app['url_generator']->generate('/translators/controlpanel'), 301
				);
			}
			 $data = array(
				'id'=> $translator['id'],
				'name'=> $translator['name'],
				'surname'=> $translator['surname'],
			);
			if (count($translator)) {
	 
				$form = $app['form.factory']->createBuilder('form', $data)
					->add(
						'id', 'hidden', array('data'=>$id,)
					)
					->add(
						'name', 'hidden', array(
							'constraints' => array(
								new Assert\NotBlank(), 
								new Assert\Length(array('min' => 3))
							)
						)
					)
					->add(
						'surname', 'hidden', array(
							'constraints' => array(
								new Assert\NotBlank(), 
								new Assert\Length(array('min' => 3))
							)
						)
					)
					->add('usun', 'submit')
					->getForm();
	 
				$form->handleRequest($request);
	 
				if ($form->isValid()) {
					$translatorsModel = new TranslatorsModel($app);
					$data = $form->getData();
					
					$translatorsModel->deleteTranslator($form->getData());
					$app['session']
						->getFlashBag()
						->add(
							'message', array(
								'type' => 'success', 
								'content' => 'Usunięto tłumacza.'
							)
						);
					return $app->redirect(
						$app['url_generator']->generate(
							'/translators/controlpanel'
						), 301
					);
				}
	 
				return $app['twig']->render(
					'translators/delete.twig', array(
						'form' => $form->createView(), 'translator' => $translator
					)
				);
	 
			} else {
				return $app->redirect(
					$app['url_generator']->generate('/translators/'), 301
				);
			}
		}

		/**
		 * View single translator and his books.
		 *
		 * @param Application $app application object
		 * @param Request $request request
		 * @access public
		 * @return mixed Generates page or Redirect.
		 */
		public function view(Application $app, Request $request)
		{
			$translatorsModel = new TranslatorsModel($app);
			$booksModel = new BooksModel($app);
			$id = (int) $request->get('id', 0); 
			if ($translatorsModel->idExist($id)) {
				$translator = $translatorsModel->getTranslator($id);
				$books = $booksModel->getBooksByTranslator($id);
				return $app['twig']->render(
					'translators/view.twig', array(
						'translator' => $translator, 
						'books' => $books
					)
				);
			} else {
				$app['session']->getFlashBag()->add(
					'message', array(
						'type' => 'error', 
						'content' => 'Podany tłumacz nie istnieje!'
					)
				);
				return $app->redirect(
					$app['url_generator']->generate('/translators/'), 301
				);
			}
		}
		
		/**
		 * Manage connection between book and translator.
		 *
		 * @param Application $app application object
		 * @param Request $request request
		 * @access public
		 * @return mixed Generates page or Redirect.
		 */    
		public function manageTranslator(Application $app, Request $request)
		{
			
		}
		
		/**
		 * Show all translators.
		 *
		 * @param Application $app application object
		 * @param Request $request request
		 * @access public
		 * @return mixed Generates page or Redirect.
		 */
		public function controlTranslator(Application $app, Request $request)
		{
			$pageLimit = 6;
			$page = (int) $request->get('page', 1);
			$translatorsModel = new TranslatorsModel($app);
			$pagesCount = $translatorsModel->countTranslatorsPages($pageLimit);
			if (($page < 1) || ($page > $pagesCount)) {
				$page = 1;
			}
			$translators = $translatorsModel->getTranslatorsPage(
				$page, $pageLimit, $pagesCount
			);
			$paginator = array('page' => $page, 'pagesCount' => $pagesCount);

			return $app['twig']->render(
				'translators/control.twig', array(
					'translators' => $translators, 'paginator' => $paginator
				)
			);
		}

		/**
		 * Connect an translator with a book.
		 *
		 * @param Application $app application object
		 * @param Request $request request
		 * @access public
		 * @return mixed Generates page or Redirect.
		 */
		public function connectTranslator(Application $app, Request $request)
		{
			
		}
		
		/**
		 * Disconnect an translator from a book.
		 *
		 * @param Application $app application object
		 * @param Request $request request
		 * @access public
		 * @return mixed Generates page or Redirect.
		 */
		/*public function disconnectTranslator(Application $app, Request $request)
		{
			
		}*/
		
	}