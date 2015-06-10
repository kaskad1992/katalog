<?php
	/**
	 * Controller PublishersController
	 *
	 * @class PublishersController
	 * @author Katarzyna Drapala
	 * @link wierzba.wzks.uj.edu.pl/~12_drapala/silex
	 * @uses Silex\Application
	 * @uses Silex\ControllerProviderInterface
	 * @uses Symfony\Component\HttpFoundation\Request
	 * @uses Symfony\Component\Validator\Constraints
	 * @uses Model\PublishersModel
	 * @uses Model\BooksModel
	 */
	 
	namespace Controller;

	use Silex\Application;
	use Silex\ControllerProviderInterface;
	use Symfony\Component\HttpFoundation\Request;
	use Symfony\Component\Validator\Constraints as Assert;
	use Model\PublishersModel;
	use Model\BooksModel;

	class PublishersController implements ControllerProviderInterface
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
			$publishersController = $app['controllers_factory'];
			$publishersController
				->match('/add/', array($this, 'add'))
				->bind('/publishers/add'); 
			$publishersController
				->match('/edit/{id}', array($this, 'edit'))
				->bind('/publishers/edit');
			$publishersController
				->match('/delete/{id}', array($this, 'delete'))
				->bind('/publishers/delete');
			$publishersController
				->get('/view/{id}', array($this, 'view'))
				->bind('/publishers/view');
			$publishersController
				->match('/connect/{id}', array($this, 'connectPublisher'))
				->bind('/publishers/connect');
			// $publishersController
				// ->match(
					// '/disconnect/{id}', 
					// array($this, 'disconnectPublisher')
				// )
				// ->bind('/publishers/disconnect');
			$publishersController
				->match('/manage/{id}', array($this, 'managePublisher'))
				->bind('/publishers/manage');
			$publishersController
				->match('/controlpanel/{page}', array($this, 'controlPublisher'))
				->value('page', 1)->bind('/publishers/controlpanel');
			$publishersController
                ->get('/{page}', array($this, 'index'))
                ->value('page', 1)
				->bind('/publishers/');
			return $publishersController;
		}

		/**
		 * List of one book's publishers.
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
			$publishersModel = new PublishersModel($app);
			$pagesCount = $publishersModel->countPublishersPages($pageLimit);
			if (($page < 1) || ($page > $pagesCount)) {
				$page = 1;
			} 
			$publishers = $publishersModel
				->getPublishersPage($page, $pageLimit, $pagesCount);
			$paginator = array(
				'page' => $page, 'pagesCount' => $pagesCount
			); 
			return $app['twig']->render(
				'publishers/index.twig', array(
					'publishers' => $publishers, //'paginator' => $paginator
				)
			);
		}    
		
		/**
		 * Add new publisher.
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
                $app['url_generator']->generate('/publishers/controlpanel'), 301
            ); 
			} */
			$data = array(
				'name' => 'Nazwa wydawnictwa',
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
									'message' => 'Wpisz poprawną nazwę.'
								)
							)
						)
					)
				)
				->add('dodaj', 'submit')
				->getForm();

			$form->handleRequest($request);

			if ($form->isValid()) {
				$publishersModel = new PublishersModel($app);
				$publishersModel->addPublisher($form->getData());
				$app['session']->getFlashBag()->add(
					'message', array(
						'type' => 'success', 
						'content' => 'Dodano nowe wydawnictwo.'
					)
				);
				return $app->redirect(
					$app['url_generator']->generate('/publishers/controlpanel'), 301
				); 
			}

			return $app['twig']->render(
				'publishers/add.twig', array('form' => $form->createView())
			);
		}
		
		/**
		 * Edit an publisher.
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
                $app['url_generator']->generate('/publishers/controlpanel'), 301
            ); 
			} */
			$publishersModel = new PublishersModel($app);

			$id = (int) $request->get('id', 0);//
			$publisher = $publishersModel->getPublisher($id);
			
			$data = array(
				'id'=> $publisher['id'],
				'name'=> $publisher['name'],
				);
			
			if (count($publisher) && $publishersModel->idExist($id)) {

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
									'message' => 'Wpisz poprawną nazwę.')
							)
						)
					)
				)
				->add('zapisz', 'submit')
					->getForm();

				$form->handleRequest($request);

				if ($form->isValid()) {
					$publishersModel = new PublishersModel($app);
					$publishersModel->savePublisher($form->getData());
					$app['session']->getFlashBag()->add(
						'message', array(
							'type' => 'success', 
							'content' => 'Zmieniono dane wydawnictwa.'
						)
					);
					return $app->redirect(
						$app['url_generator']->generate(
							'/publishers/view', array('id' => $id)
						)
					); 
				}

				return $app['twig']->render(
					'publishers/edit.twig', array(
						'form' => $form->createView(), 'publisher' => $publisher
					)
				);

			} else {
				$app['session']->getFlashBag()->add(
					'message', array(
						'type' => 'error', 
						'content' => 'Wydawnictwo o podanym id nie istnieje. 
						Dodaj nowe wydawnictwo:'
					)
				);
				return $app->redirect(
					$app['url_generator']->generate('/publishers/add'), 301
				);
			}
		}
		
		/**
		 * Delete an publisher.
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
                $app['url_generator']->generate('/publishers/controlpanel'), 301
            ); */
        
			$publishersModel = new PublishersModel($app);
	 
			$id = (int) $request->get('id');
			if (ctype_digit((string)$id) && $publishersModel->idExist($id)) {
				$publisher = $publishersModel->getPublisher($id);      
			} else {
				$app['session']->getFlashBag()->add(
					'message', array(
						'type' => 'error', 
						'content' => 'Wydawnictwo o podanym id nie istnieje.'
					)
				);
				return $app->redirect(
					$app['url_generator']->generate('/publishers/controlpanel'), 301
				);
			}
			$data = array(
				'id'=> $publisher['id'],
				'name'=> $publisher['name'],
			);
			if (count($publisher)) {
	 
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
					->add('Usuń', 'submit')
					->getForm();
	 
				$form->handleRequest($request);
	 
				if ($form->isValid()) {
					$publishersModel = new PublishersModel($app);
					$data = $form->getData();
					
					$publishersModel->deletePublisher($form->getData());
					$app['session']
						->getFlashBag()
						->add(
							'message', array(
								'type' => 'success', 
								'content' => 'Usunięto wydawnictwo.'
							)
						);
					return $app->redirect(
						$app['url_generator']->generate(
							'/publishers/controlpanel'
						), 301
					);
				}
	 
				return $app['twig']->render(
					'publishers/delete.twig', array(
						'form' => $form->createView(), 'publisher' => $publisher
					)
				);
	 
			} else {
				return $app->redirect(
					$app['url_generator']->generate('/publishers/'), 301
				);
			}
		}

		/**
		 * View single publisher and his books.
		 *
		 * @param Application $app application object
		 * @param Request $request request
		 * @access public
		 * @return mixed Generates page or Redirect.
		 */
		public function view(Application $app, Request $request)
		{
			$publishersModel = new PublishersModel($app);
			$booksModel = new BooksModel($app);
			$id = (int) $request->get('id', 0); 
			if ($publishersModel->idExist($id)) {
				$publisher = $publishersModel->getPublisher($id);
				$books = $booksModel->getBooksByPublisher($id);
				return $app['twig']->render(
					'publishers/view.twig', array(
						'publisher' => $publisher, 
						'books' => $books
					)
				);
			} else {
				$app['session']->getFlashBag()->add(
					'message', array(
						'type' => 'error', 
						'content' => 'Podane wydawnictwo nie istnieje!'
					)
				);
				return $app->redirect(
					$app['url_generator']->generate('/books/'), 301
				);
			}
		}
		
		/**
		 * Manage connection between book and publishers.
		 *
		 * @param Application $app application object
		 * @param Request $request request
		 * @access public
		 * @return mixed Generates page or Redirect.
		 */    
/*		public function managePublisher(Application $app, Request $request)
		{
			
		}
*/		
		/**
		 * Show all publishers.
		 *
		 * @param Application $app application object
		 * @param Request $request request
		 * @access public
		 * @return mixed Generates page or Redirect.
		 */
		public function controlPublisher(Application $app, Request $request)
		{
			
			$pageLimit = 6;
			$page = (int) $request->get('page', 1);
			$publishersModel = new PublishersModel($app);
			$pagesCount = $publishersModel->countPublishersPages($pageLimit);
			if (($page < 1) || ($page > $pagesCount)) {
				$page = 1;
			}
			$publishers = $publishersModel->getPublishersPage(
				$page, $pageLimit, $pagesCount
			);
			$paginator = array('page' => $page, 'pagesCount' => $pagesCount);

			return $app['twig']->render(
				'publishers/control.twig', array(
					'publishers' => $publishers, 'paginator' => $paginator
				)
			);
		}

		/**
		 * Connect an publisher with a book.
		 *
		 * @param Application $app application object
		 * @param Request $request request
		 * @access public
		 * @return mixed Generates page or Redirect.
		 */
/*		public function connectPublisher(Application $app, Request $request)
		{
			
		}
*/		
		/**
		 * Disconnect an publisher from a book.
		 *
		 * @param Application $app application object
		 * @param Request $request request
		 * @access public
		 * @return mixed Generates page or Redirect.
		 */
/*		public function disconnectPublisher(Application $app, Request $request)
		{
			
		}
*/		
	}
