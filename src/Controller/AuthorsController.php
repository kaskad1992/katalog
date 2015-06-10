<?php
	/**
	 * Controller AuthorsController
	 *
	 * @class AuthorsController
	 * @author Katarzyna Drapala
	 * @link wierzba.wzks.uj.edu.pl/~12_drapala/silex
	 * @uses Silex\Application
	 */

	namespace Controller;

	use Silex\Application;
	use Silex\ControllerProviderInterface;
	use Symfony\Component\HttpFoundation\Request;
	use Symfony\Component\Validator\Constraints as Assert;
	use Model\AuthorsModel;
	use Model\BooksModel;

	class AuthorsController implements ControllerProviderInterface
	{
		//protected $_model;

		/**
		 * Connection
		 *
		 * @param Application $app application object
		 * @access public
		 * @return \Silex\ControllerCollection
		 */
		public function connect(Application $app)
		{
			$authorsController = $app['controllers_factory'];
			$authorsController
				->match('/add/', array($this, 'add'))
				->bind('/authors/add'); 
			$authorsController
				->match('/edit/{id}', array($this, 'edit'))
				->bind('/authors/edit');
			$authorsController
				->match('/delete/{id}', array($this, 'delete'))
				->bind('/authors/delete');
			$authorsController
				->get('/view/{id}', array($this, 'view'))
				->bind('/authors/view');
			$authorsController
				->match('/connect/{id}', array($this, 'connectAuthor'))
				->bind('/authors/connect');
			// $authorsController
				// ->match(
					// '/disconnect/{id}/{idBook}', 
					// array($this, 'disconnectAuthor')
				// )
				// ->bind('/authors/disconnect');
			$authorsController
				->match('/manage/{id}', array($this, 'manageAuthor'))
				->bind('/authors/manage');
			$authorsController
				->match('/controlpanel/{page}', array($this, 'controlAuthor'))
				->value('page', 1)->bind('/authors/controlpanel');
			$authorsController
                ->get('/{page}', array($this, 'index'))
                ->value('page', 1)
				->bind('/authors/');
			return $authorsController;
		}

		/**
		 * List of one book's authors.
		 *
		 * @param Application $app application object
		 * @param Request $request request
		 * @access public
		 * @return mixed Generates page.
		 */
		public function index(Application $app, Request $request)
		{
			$pageLimit = 3;
			$page = (int) $request->get('page', 1);
			$authorsModel = new AuthorsModel($app);
			$pagesCount = $authorsModel->countAuthorsPages($pageLimit);

			if (($page < 1) || ($page > $pagesCount)) {
				$page = 1;
			}
			$authors = $authorsModel
				->getAuthorsPage($page, $pageLimit, $pagesCount);
			$paginator = array(
				'page' => $page, 'pagesCount' => $pagesCount
			);

			return $app['twig']->render(
				'authors/index.twig', array(
					'authors' => $authors//, 
					//'paginator' => $paginator
				)
			);
			
			// $idbook = (int) $request->get('id', 1);
			// $authorsModel = new AuthorsModel($app);
			// $booksModel = new BooksModel($app);
			// $authors = $authorsModel->getAuthorsListByBook($idbook);
			// return $app['twig']->render(
				// 'authors/index.twig', array(
					// 'authors' => $authors, 'id' => $idbook
				// )
			// );
		}    
		
		/**
		 * Add new author.
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
                $app['url_generator']->generate('/authors/controlpanel'), 301
            ); 
        } */
        $data = array(
            'name' => 'Imie autora',
            'surname' => 'Nazwisko autora',
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
            $authorsModel = new AuthorsModel($app);
            $authorsModel->addAuthor($form->getData());
            $app['session']->getFlashBag()->add(
                'message', array(
                    'type' => 'success', 
                    'content' => 'Dodano nowego autora.'
                )
            );
            return $app->redirect(
                $app['url_generator']->generate('/authors/controlpanel'), 301
            ); 
        }

        return $app['twig']->render(
            'authors/add.twig', array('form' => $form->createView())
        );
	}
		
		/**
		 * Edit an author.
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
                $app['url_generator']->generate('/authors/controlpanel'), 301
            ); */
        
	      $authorsModel = new AuthorsModel($app);

			$id = (int) $request->get('id', 0);//
			$author = $authorsModel->getAuthor($id);
			
			$data = array(
				'id'=> $author['id'],
				'name'=> $author['name'],
				'surname'=> $author['surname'],
				);
			
			if (count($author) && $authorsModel->idExist($id)) {

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
					$authorsModel = new AuthorsModel($app);
					$authorsModel->saveAuthor($form->getData());
					$app['session']->getFlashBag()->add(
						'message', array(
							'type' => 'success', 
							'content' => 'Zmieniono dane autora.'
						)
					);
					return $app->redirect(
						$app['url_generator']->generate(
							'/authors/view', array('id' => $id)
						)
					); 
				}

				return $app['twig']->render(
					'authors/edit.twig', array(
						'form' => $form->createView(), 'author' => $author
					)
				);

			} else {
				$app['session']->getFlashBag()->add(
					'message', array(
						'type' => 'error', 
						'content' => 'Autor o podanym id nie istnieje. 
						Dodaj nowego autora:'
					)
				);
				return $app->redirect(
					$app['url_generator']->generate('/authors/add'), 301
				);
			}
		}
		
		/**
		 * Delete an author.
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
                $app['url_generator']->generate('/authors/controlpanel'), 301
            ); */
        
			$authorsModel = new AuthorsModel($app);
	 
			$id = (int) $request->get('id');
			if (ctype_digit((string)$id) && $authorsModel->idExist($id)) {
				$author = $authorsModel->getAuthor($id);      
			} else {
				$app['session']->getFlashBag()->add(
					'message', array(
						'type' => 'error', 
						'content' => 'Autor o podanym id nie istnieje.'
					)
				);
				return $app->redirect(
					$app['url_generator']->generate('/authors/controlpanel'), 301
				);
			}
			 $data = array(
				'id'=> $author['id'],
				'name'=> $author['name'],
				'surname'=> $author['surname'],
			);
			if (count($author)) {
	 
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
					$authorsModel = new AuthorsModel($app);
					$data = $form->getData();
					//odlaczenie od ksiazki
					$books = $authorsModel->getBooksByAuthor($id);
					foreach ($books as $book) {
						$data['id'] = $book['id'];
						$authorsModel->disconnectWithBook($data);
					}
					$authorsModel->deleteAuthor($form->getData());
					$app['session']
						->getFlashBag()
						->add(
							'message', array(
								'type' => 'success', 
								'content' => 'Usunięto autora.'
							)
						);
					return $app->redirect(
						$app['url_generator']->generate(
							'/authors/controlpanel'
						), 301
					);
				}
	 
				return $app['twig']->render(
					'authors/delete.twig', array(
						'form' => $form->createView(), 'author' => $author
					)
				);
	 
			} else {
				return $app->redirect(
					$app['url_generator']->generate('/authors/'), 301
				);
			}
		}

		/**
		 * View single author and his books.
		 *
		 * @param Application $app application object
		 * @param Request $request request
		 * @access public
		 * @return mixed Generates page or Redirect.
		 */
		public function view(Application $app, Request $request)
		{
			$authorsModel = new AuthorsModel($app);
			$id = (int) $request->get('id', 0); 
			if ($authorsModel->idExist($id)) {
				$author = $authorsModel->getAuthor($id); 
				$books = $authorsModel->getBooksByAuthor($id);
				return $app['twig']->render(
					'authors/view.twig', 
					array('author' => $author, 'books' => $books)
				);
			} else {
				$app['session']
					->getFlashBag()
					->add(
						'message', 
						array(
							'type' => 'error', 
							'content' => 'Podany autor nie istnieje!'
						)
					);
				return $app->redirect(
					$app['url_generator']->generate('/authors/controlpanel'), 301
				);
			}
		}
		
		/**
		 * Manage connection between book and authors.
		 *
		 * @param Application $app application object
		 * @param Request $request request
		 * @access public
		 * @return mixed Generates page or Redirect.
		 */    
/*		public function manageAuthor(Application $app, Request $request)
		{
/*			if (!$app['security']->isGranted('ROLE_ADMIN')) {
            $app['session']
                ->getFlashBag()
                ->add(
                    'message', array(
                        'type' => 'error', 
                        'content' => 
                            'Nie masz uprawnień do korzystania z tej strony.'
                    )
                ); */
/*            return $app->redirect(
                $app['url_generator']->generate('/authors/controlpanel'), 301
            );
        }
        $idbook = (int)$request->get('id', 0); //id ksiazki
        $booksModel = new BooksModel($app);
        if ($booksModel->idExist($idbook)) {
            $book = $booksModel->getBook($idbook); //dane ksiazki
            $authorsModel = new AuthorsModel($app);
            $authors = $authorsModel->getAuthorsListByBook($idbook); //autorzy
            return $app['twig']->render(
                'authors/manage.twig', array(
                    'authors' => $authors, 'id' => $idbook, 'book' => $book
                )
            );
        } else {
            $app['session']
                ->getFlashBag()
                ->add(
                    'message', array(
                        'type' => 'error', 
                        'content' => 'Podana książka nie istnieje!'
                    )
                );
            return $app->redirect(
                $app['url_generator']->generate('/books/'), 301
            );
        }
		}
*/		
		/**
		 * Show all authors.
		 *
		 * @param Application $app application object
		 * @param Request $request request
		 * @access public
		 * @return mixed Generates page or Redirect.
		 */
		public function controlAuthor(Application $app, Request $request)
		{
			$pageLimit = 6;
			$page = (int) $request->get('page', 1);
			$authorsModel = new AuthorsModel($app);
			$pagesCount = $authorsModel->countAuthorsPages($pageLimit);
			if (($page < 1) || ($page > $pagesCount)) {
				$page = 1;
			}
			$authors = $authorsModel->getAuthorsPage(
				$page, $pageLimit, $pagesCount
			);
			$paginator = array('page' => $page, 'pagesCount' => $pagesCount);

			return $app['twig']->render(
				'authors/control.twig', array(
					'authors' => $authors, 'paginator' => $paginator
				)
			);
		}

		/**
		 * Connect an author with a book.
		 *
		 * @param Application $app application object
		 * @param Request $request request
		 * @access public
		 * @return mixed Generates page or Redirect.
		 */
/*		public function connectAuthor(Application $app, Request $request)
		{
/*			if (!$app['security']->isGranted('ROLE_ADMIN')) {
				$app['session']
					->getFlashBag()
					->add(
						'message', array(
							'type' => 'error', 
							'content' => 
								'Nie masz uprawnień do korzystania z tej strony.'
						)
					);
				return $app->redirect(
					$app['url_generator']->generate('/authors/controlpanel'), 301
				); 
			} */
/*			$idbook = (int)$request->get('id', 0); //id ksiazki

			$authorsModel = new AuthorsModel($app);
			$authors = $authorsModel->getAuthorsListDict(); //lista nazwisk
			$booksModel = new BooksModel($app);
			$book = $booksModel->getBook($idbook);

			$data = array();

			$form = $app['form.factory']->createBuilder('form', $data)
				->add('idAuthor', 'choice', array('choices' => $authors,))
				->add('idBook', 'hidden', array('data' => $idbook,))
				->add('zapisz', 'submit')
				->getForm();

			$form->handleRequest($request);

			if ($form->isValid()) {
				$data = $form->getData();
				$model = $authorsModel->connectWithBook($data);//polacz
				if ($model) {
					$app['session']
						->getFlashBag()
						->add(
							'message', array(
								'type' => 'success', 
								'content' => 'Przypisano autora do książki.'
							)
						);
					return $app->redirect(
						$app['url_generator']->generate(
							'/authors/manage', array('idBook' => $idbook)
						)
					);
				} else {
					$app['session']
						->getFlashBag()
						->add(
							'message', array(
								'type' => 'error', 
								'content' => 'Autor już jest 
								przypisany do tej książki!'
							)
						);
					return $app->redirect(
						$app['url_generator']->generate(
							'/authors/manage', array('idBook' => $idbook)
						)
					);
				}
			}

			return $app['twig']->render(
				'authors/connect.twig', array(
					'form' => $form->createView(), 
					'idBook' => $idbook, 
					'book' => $book
				)
			);
		}
*/		
		/**
		 * Disconnect an author from a book.
		 *
		 * @param Application $app application object
		 * @param Request $request request
		 * @access public
		 * @return mixed Generates page or Redirect.
		 */
/*		public function disconnectAuthor(Application $app, Request $request)
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
					$app['url_generator']->generate('/authors/controlpanel'), 301
				); 
			} */
/*			$idbook = (int)$request->get('idBook', 0); //id ksiazki
			$idauthor = (int)$request->get('idAuthor', 0);

			$authorsModel = new AuthorsModel($app);
			$author = $authorsModel->getAuthor($idauthor); //dane autora
			$booksModel = new BooksModel($app);
			$book = $booksModel->getBook($idbook);
			
			$data = array();

			if (count($author)) {
				$redirect = $app->redirect(
					$app['url_generator']->generate('/books/'), 301
				);

				$form = $app['form.factory']
					->createBuilder('form', $data)
					->add('idBook', 'hidden', array('data' => $idbook,))
					->add('idAuthor', 'hidden', array('data' => $idauthor,))
					->add('usun', 'submit')
					->getForm();

				$form->handleRequest($request);

				if ($form->isValid()) {
					$data = $form->getData();
					$model = $authorsModel->disconnectWithBook($data); //odlacz
					if (!$model) {
						$app['session']->getFlashBag()->add(
							'message', array(
								'type' => 'success', 
								'content' => 'Usunięto autora książki.'
							)
						);
						return $app->redirect(
							$app['url_generator']->generate(
								'/authors/manage', array('idBook' => $idbook)
							)
						);
					}
				}

				return $app['twig']->render(
					'authors/disconnect.twig', array(
						'form' => $form->createView(), 
						'redirect' => $redirect, 
						'book' => $book, 
						'author' => $author
					)
				);

			} else {
				$app->notFound();
			}
		
		}
	*/	
	}