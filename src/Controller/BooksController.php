<?php
	/**
	 * Controller BooksController
	 *
	 * @class BooksController
	 * @author Katarzyna Drapala
	 * @link wierzba.wzks.uj.edu.pl/~12_drapala/silex
	 * @uses Silex\Application
	 * @uses Silex\ControllerProviderInterface
	 * @uses Symfony\Component\HttpFoundation\Request
	 * @uses Symfony\Component\Validator\Constraints
	 * @uses Model\BooksModel;
	 * @uses Model\CategoriesModel
	 * @uses Model\AuthorsModel
	 * @uses Model\VotesModel
	 */

	namespace Controller;

	use Silex\Application;
	use Silex\ControllerProviderInterface;
	use Symfony\Component\HttpFoundation\Request;
	use Symfony\Component\Validator\Constraints as Assert;
	use Model\BooksModel;
	use Model\CategoriesModel;
	use Model\AuthorsModel;
	use Model\TranslatorsModel;
	use Model\PublishersModel;
	use Model\VotesModel;

	class BooksController implements ControllerProviderInterface
	{
		protected $_model;

		/**
		 * Connection
		 *
		 * @param Application $app application object
		 *
		 * @access public
		 * @return \Silex\ControllerCollection
		 */
		public function connect(Application $app)
		{
			$booksController = $app['controllers_factory'];
			$booksController
				->match('/add/', array($this, 'add'))->bind('/books/add');        
			$booksController
				->match('/edit/{id}', array($this, 'edit'))
				->bind('/books/edit');
			$booksController
				->match('/delete/{id}', array($this, 'delete'))
				->bind('/books/delete');
			$booksController
				->get('/view/{id}', array($this, 'view'))
				->bind('/books/view');
            $booksController
                ->get('/{page}', array($this, 'index'))
                ->value('page', 1)
				->bind('/books/');
			return $booksController;
		}

		/**
		 * List of all books.
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
			$booksModel = new BooksModel($app);
			$pagesCount = $booksModel->countBooksPages($pageLimit);



			if (($page < 1) || ($page > $pagesCount)) {
				$page = 1;
			}
			$books = $booksModel
				->getBooksPage($page, $pageLimit, $pagesCount);
			$paginator = array(
				'page' => $page, 'pagesCount' => $pagesCount
			);

			return $app['twig']->render(
				'books/index.twig', array(
					'books' => $books//, 
					//'paginator' => $paginator
				)
			);
		}

		/**
		 * Add a book.
		 *
		 * @param Application $app application object
		 * @param Request $request request
		 * @access public
		 * @return mixed Generates page.
		 */
		public function add(Application $app, Request $request)
		{    
			// if (!$app['security']->isGranted('ROLE_ADMIN')) {
				// $app['session']
					// ->getFlashBag()
					// ->add(
						// 'message', array(
							// 'type' => 'error', 
							// 'content' => 'Nie masz uprawnień do korzystania z tej strony.'
						// )
					// );
				// return $app->redirect(
					// $app['url_generator']->generate('/books/'), 301
				// ); 
			// }
			$categoriesModel =  new CategoriesModel($app);
			$categories = $categoriesModel->getCategoriesList();
			$publishersModel =  new PublishersModel($app);
			$publishers = $publishersModel->getPublishersList();
			$authorsModel =  new AuthorsModel($app);
			$authors = $authorsModel->getAuthorsList();
			$translatorsModel =  new TranslatorsModel($app);
			$translators = $translatorsModel->getTranslatorsList();
			
			$data = array(
				'title' => 'Tytuł',
				'original_title' => 'Tytuł oryginalny',
				'published' => 'Data publikacji',
				'category' => '$categories',
				'publisher' => '$publishers',
				'author' => '$authors',//$book['$authors'],
				'translator' => '$translators',
				'published' => 'Data publikacji',
				'ISBN' => 'ISBN',
			);

			$form = $app['form.factory']->createBuilder('form', $data)
				->add(
					'title', 'text', array(
						'constraints' => array(
							new Assert\NotBlank(), 
							new Assert\Length(
								array(
									'min' => 2, 
									'minMessage' => 'Minimalna ilość znakow to 2'
								)	
							), 
							new Assert\Type(
								array(
									'type' => 'string', 
									'message' => 'Tytuł jest niepoprawny.'
								)
							)
						)
					)
				)
				->add(
					'original_title', 'text', array(
						'constraints' => array(
							new Assert\NotBlank(), 
							new Assert\Length(
								array(
									'min' => 2, 
									'minMessage' => 'Minimalna ilość znakow to 2'
								)	
							), 
							new Assert\Type(
								array(
									'type' => 'string', 
									'message' => 'Tytuł jest niepoprawny.'
								)
							)
						)
					)
				)
				->add(
					'published', 'text', array(
						'constraints' => array(
							new Assert\NotBlank(), 
							new Assert\Length(
								array(
									'min' => 4, 
									'minMessage' => 'Minimalna ilość znakow to 4'
								)	
							), 
							new Assert\Type(
								array(
									'type' => 'string', 
									'message' => 'Format daty jest niepoprawny.'
								)
							)
						)
					)
				)
				->add(
					'category', 'choice', array(
						'choices'=> $categories
					)
				)
				->add(
					'publisher', 'choice', array(
						'choices'=> $publishers
					)
				)
				->add(
					'author', 'choice', array(
						'choices'=> $authors
					)
				)
				->add(
					'translator', 'choice', array(
						'choices'=> $translators
					)
				)
				->add(
					'ISBN', 'text', array(
						'constraints' => array(
							new Assert\NotBlank(), 
							new Assert\Length(
								array(
									'min' => 13, 
									'minMessage' => 'Wymagana ilość znaków: 13'
								)	
							), 
							new Assert\Type(
								array(
									'type' => 'string', 
									'message' => 'Numer jest niepoprawny.'
								)
							)
						)
					)
				)
				->add('dodaj', 'submit')
				->getForm();

			$form->handleRequest($request);

			if ($form->isValid()) {
				$booksModel = new BooksModel($app);
				$data = $form->getData();
				$lastBookId = $booksModel->addBook($data);
				$app['session']
					->getFlashBag()
					->add(
						'message', array(
							'type' => 'success', 
							'content' => 'Dodano książkę do katalogu.'
						)
					);
				return $app->redirect(
					$app['url_generator']->generate(
						'/books/', array('id' => $lastBookId)
					)
				);
			}
	
			return $app['twig']->render(
				'books/add.twig', array('form' => $form->createView())
			);
		
		}

		/**
		 * Edit book.
		 *
		 * @param Application $app application object
		 * @param Request $request request
		 * @access public
		 * @return mixed Generates page or Redirect.
		 */
		public function edit(Application $app, Request $request)
		{
			// if (!$app['security']->isGranted('ROLE_ADMIN')) {
				// $app['session']
					// ->getFlashBag()
					// ->add(
						// 'message', array(
							// 'type' => 'error', 
							// 'content' => 'Nie masz uprawnień do korzystania z tej strony.'
						// )
					// );
				// return $app->redirect(
					// $app['url_generator']->generate('/books/'), 301
				// ); 
			// }
			$booksModel = new BooksModel($app);
			$categoriesModel =  new CategoriesModel($app);
			$categories = $categoriesModel->getCategoriesList();
			$publishersModel =  new PublishersModel($app);
			$publishers = $publishersModel->getPublishersList();
			$authorsModel =  new AuthorsModel($app);
			$authors = $authorsModel->getAuthorsList();
			$translatorsModel =  new TranslatorsModel($app);
			$translators = $translatorsModel->getTranslatorsList();
			
			$id = (int) $request->get('id', 0);

			$book = $booksModel->getBook($id);

			$data = array(
				'id'=> $book['id'],
				'title'=> $book['title'],
				'otiginal_title'=> $book['original_title'],
				'category' => $categories['$categories'],
				'publisher' => $publishers['$publishers'],
				'author' => $authors['$authors'],
				'translator' => $translators['$translators'],
			);

			if (count($book) && $booksModel->idExist($id)) {

				$form = $app['form.factory']->createBuilder('form', $data)
					->add('id', 'hidden', array('data'=> $id,))
					->add(
						'title', 'text', array(
							'constraints' => array(
								new Assert\NotBlank(), 
								new Assert\Length(
									array(
										'min' => 2, 'minMessage' => 
										'Minimalna ilość znaków to 2'
									)
								), 
								new Assert\Type(
									array(
										'type' => 'string', 
										'message' => 'Tytuł jest niepoprawny.',
									)
								)
							)
						)
					)
					->add(
						'original_title', 'text', array(
							'constraints' => array(
								new Assert\NotBlank(), 
								new Assert\Length(
									array(
										'min' => 2, 'minMessage' => 
										'Minimalna ilość znaków to 2'
									)
								), 
								new Assert\Type(
									array(
										'type' => 'string', 
										'message' => 'Tytuł jest niepoprawny.',
									)
								)
							)
						)
					)
					->add('category', 'choice', array('choices'=> $categories))
					->add('publisher', 'choice', array('choices'=> $publishers))
					->add('author', 'choice', array('choices'=> $authors))
					->add('translator', 'choice', array('choices'=> $translators))
					 
					->add('zapisz', 'submit')
					->getForm();

				$form->handleRequest($request);

				if ($form->isValid()) {
					$data = $form->getData();
				
					$booksModel = new BooksModel($app);
					$booksModel->saveBook($form->getData());
					$app['session']->getFlashBag()->add(
						'message', array(
							'type' => 'success', 
							'content' => 'Dane zostały zmienione.'
						)
					);
					return $app->redirect(
						$app['url_generator']
							->generate('/books/view', array('id' => $id))
					);
				}

				return $app['twig']->render(
					'books/edit.twig', array(
						'form' => $form->createView(), 
						'id' => $id
					)
				);

			} else {
				$app['session']->getFlashBag()->add(
					'message', array(
						'type' => 'error', 
						'content' => 'Książka o podanym id nie istnieje. 
						Dodaj nową ksiazkę:'
					)
				);
				return $app->redirect(
					$app['url_generator']->generate('/books/add'), 301
				);
			}
		}

		/**
		 * Delete book.
		 *
		 * @param Application $app application object
		 * @param Request $request request
		 * @access public
		 * @return mixed Generates page or Redirect.
		 */
		public function delete(Application $app, Request $request)
		{
			// if (!$app['security']->isGranted('ROLE_ADMIN')) {
				// $app['session']
					// ->getFlashBag()
					// ->add(
						// 'message', array(
							// 'type' => 'error', 
							// 'content' => 'Nie masz uprawnień do korzystania z tej strony.'
						// )
					// );
				// return $app->redirect(
					// $app['url_generator']->generate('/books/'), 301
				// ); 
			// }
			$booksModel = new BooksModel($app);
			$id = (int) $request->get('id');

			//pobierz ksiazke o podanym id
			if (ctype_digit((string)$id) && $booksModel->idExist($id)) {
				$book = $booksModel->getBook($id);      
			} else {
				$app['session']
					->getFlashBag()
					->add(
						'message', array(
							'type' => 'error', 
							'content' => 'Książka o podanym id nie istnieje.'
						)
					);
				return $app->redirect(
					$app['url_generator']->generate('/books/'), 301
				); 
			}
			//
			$data = array(
				'id'=> $book['id'],
				'title'=> $book['title'],
			);
			//generuj formularz usuwania
			if (count($book)) {
				$form = $app['form.factory']->createBuilder('form', $data)
					->add('id', 'hidden', array('data'=>$id,))
					->add(
						'title', 'text', array(
						'constraints' => array(
							new Assert\NotBlank(), 
							new Assert\Length(array('min' => 1))
						)
						)
					)
					
					->add('usun', 'submit')
					->getForm();
	 
				$form->handleRequest($request);
	 
				if ($form->isValid()) {
					$booksModel = new BooksModel($app);
					$data = $form->getData();
					//odlacz autorow od ksiazki
					$authorsModel = new AuthorsModel($app);
					$authors = $authorsModel->getAuthorsListByBook($id);
					foreach ($authors as $author) {
						$data['id'] = $author['id']; //author
						$data['id'] = $data['id']; //book
						//$authorsModel->disconnectWithBook($data);
					}
					$booksModel->deleteBook($form->getData());
					$app['session']
						->getFlashBag()
						->add(
							'message', array(
								'type' => 'success', 
								'content' => 'Książka została usunięta.'
							)
						);
					return $app->redirect(
						$app['url_generator']->generate('/books/'), 301
					);
				} 
	 
				return $app['twig']->render(
					'books/delete.twig', array(
						'form' => $form->createView(), 
						'book' => $book
					)
				);
	 
			} else {
				return $app->redirect(
					$app['url_generator']->generate('/books/'), 301
				);
			}
		}

		/**
		 * View single book.
		 *
		 * @param Application $app application object
		 * @param Request $request request
		 * @access public
		 * @return mixed Generates page or Redirect.
		 */
		public function view(Application $app, Request $request)
		{
			$id = (int) $request->get('id', 0);
			$booksModel = new BooksModel($app);
			if (ctype_digit((string)$id) && $booksModel->idExist($id)) {
				$book = $booksModel->getBook($id);

				//getAuthor
				$authorsModel = new AuthorsModel($app);
				$authors = $authorsModel->getAuthorsListByBook($id);

				//getTranslator
				$idtranslator = $book['id'];
				$translatorsModel = new TranslatorsModel($app);
				$translators = $translatorsModel->getTranslatorsListByBook($idtranslator);

				//getPublisher
				$idpublisher = $book['id'];
				$publishersModel = new PublishersModel($app);
				$publishers = $publishersModel->getPublishersListByBook($idpublisher);

				//getCategory
				$idcategory = $book['id'];
				$categoriesModel = new CategoriesModel($app);
				$category = $categoriesModel->getCategory($idcategory);

				return $app['twig']->render(
					'books/view.twig', 
					array(
						'book' => $book,  
						'authors' => $authors, 'id' => $id,
						'translators' => $translators, 'id' => $idtranslator,
						'publishers' => $publishers, 'id' => $idpublisher,
						'category' => $category, 'id' => $idcategory,
					)
				);
			} else {
				$app['session']
					->getFlashBag()
					->add(
						'message', array(
							'type' => 'error', 
							'content' => 'Wybrana książka nie istnieje!'
						)
					);
				return $app->redirect(
					$app['url_generator']->generate('/books/'), 301
				);
			}
		}
	}
