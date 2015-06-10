<?php
	/**
	 * Controller CategoriesController
	 *
	 * @class CategoriesController
	 * @author Katarzyna Drapala
	 * @link wierzba.wzks.uj.edu.pl/~12_drapala/silex
	 * @uses Silex\Application
	 * @uses Silex\ControllerProviderInterface
	 * @uses Symfony\Component\HttpFoundation\Request
	 * @uses Symfony\Component\Validator\Constraints
	 * @uses Model\CategoriesModel
	 * @uses Model\BooksModel
	 */
	 
	namespace Controller;

	use Silex\Application;
	use Silex\ControllerProviderInterface;
	use Symfony\Component\HttpFoundation\Request;
	use Symfony\Component\Validator\Constraints as Assert;
	use Model\CategoriesModel;
	use Model\BooksModel;

	class CategoriesController implements ControllerProviderInterface
	{
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
			$categoriesController = $app['controllers_factory'];
			$categoriesController
				->match('/add/', array($this, 'add'))
				->bind('/categories/add'); 
			$categoriesController
				->match('/edit/{id}', array($this, 'edit'))
				->bind('/categories/edit');
			$categoriesController
				->match('/delete/{id}', array($this, 'delete'))
				->bind('/categories/delete');
			$categoriesController
				->get('/view/{id}', array($this, 'view'))
				->bind('/categories/view');
			$categoriesController
				->get('/{page}', array($this, 'index'))
				->value('page', 1)
				->bind('/categories/'); 
			return $categoriesController;
		}

		/**
		 * List of all categories.
		 *
		 * @param Application $app application object
		 * @param Request $request request
		 *
		 * @access public
		 * @return mixed Generates page.
		 */
		public function index(Application $app, Request $request)
		{
			$pageLimit = 6;
			$page = (int) $request->get('page', 1);
			$categoriesModel = new CategoriesModel($app);
			$pagesCount = $categoriesModel->countCategoriesPages($pageLimit);
			if (($page < 1) || ($page > $pagesCount)) {
				$page = 1;
			} 
			$categories = $categoriesModel
				->getCategoriesPage($page, $pageLimit, $pagesCount);
			$paginator = array(
				'page' => $page, 'pagesCount' => $pagesCount
			); 
			return $app['twig']->render(
				'categories/index.twig', array(
					'categories' => $categories, //'paginator' => $paginator
				)
			);
		}    
		
		/**
		 * Add new category.
		 *
		 * @param Application $app application object
		 * @param Request $request request
		 *
		 * @access public
		 * @return mixed Generates page or Redirect.
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
					// $app['url_generator']->generate('/categories/'), 301
				// ); 
			// }
			$categoriesModel =  new CategoriesModel($app);
			$categories = $categoriesModel->getCategoriesList();
			
			
			$data = array(
				'name' => 'Nazwa',
			);

			$form = $app['form.factory']->createBuilder('form', $data)
				->add(
					'name', 'text', array(
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
									'message' => 'Nazwa jest niepoprawna.'
								)
							)
						)
					)
				)
				
				->add('dodaj', 'submit')
				->getForm();

			$form->handleRequest($request);

			if ($form->isValid()) {
				$categoriesModel = new CategoriesModel($app);
				$data = $form->getData();
				$lastCategoryId = $categoriesModel->addCategory($data);
				$app['session']
					->getFlashBag()
					->add(
						'message', array(
							'type' => 'success', 
							'content' => 'Dodano nową kategorię.'
						)
					);
				return $app->redirect(
					$app['url_generator']->generate(
						'/categories/', array('id' => $lastCategoryId)
					)
				);
			}
	
			return $app['twig']->render(
				'categories/add.twig', array('form' => $form->createView())
			);
		}
		
		/**
		 * Edit existing category.
		 *
		 * @param Application $app application object
		 * @param Request $request request
		 *
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
			
			$id = (int) $request->get('id', 0);

			$category = $categoriesModel->getCategory($id);

			$data = array(
				'id'=> $category['id'],
				'name'=> $category['name'],
			);

			if (count($category) && $categoriesModel->idExist($id)) {

				$form = $app['form.factory']->createBuilder('form', $data)
					->add('id', 'hidden', array('data'=> $id,))
					->add(
						'name', 'text', array(
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
										'message' => 'Nazwa jest niepoprawna.',
									)
								)
							)
						)
					)
					 
					->add('zapisz', 'submit')
					->getForm();

				$form->handleRequest($request);

				if ($form->isValid()) {
					$data = $form->getData();
				
					$categoriesModel = new CategoriesModel($app);
					$categoriesModel->saveCategory($form->getData());
					$app['session']->getFlashBag()->add(
						'message', array(
							'type' => 'success', 
							'content' => 'Dane zostały zmienione.'
						)
					);
					return $app->redirect(
						$app['url_generator']
							->generate('/categories/view', array('id' => $id))
					);
				}

				return $app['twig']->render(
					'categories/edit.twig', array(
						'form' => $form->createView(), 
						'id' => $id
					)
				);

			} else {
				$app['session']->getFlashBag()->add(
					'message', array(
						'type' => 'error', 
						'content' => 'Kategoria o podanym id nie istnieje. 
						Dodaj nową kategorię:'
					)
				);
				return $app->redirect(
					$app['url_generator']->generate('/categories/add'), 301
				);
			}
		}
		
		/**
		 * Delete a category.
		 *
		 * @param Application $app application object
		 * @param Request $request request
		 *
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
					// $app['url_generator']->generate('/categories/'), 301
				// ); 
			// }
			$categoriesModel = new CategoriesModel($app);
			$id = (int) $request->get('id');

			if (ctype_digit((string)$id) && $categoriesModel->idExist($id)) {
				$category = $categoriesModel->getCategory($id);      
			} else {
				$app['session']
					->getFlashBag()
					->add(
						'message', array(
							'type' => 'error', 
							'content' => 'Kategoria o podanym id nie istnieje.'
						)
					);
				return $app->redirect(
					$app['url_generator']->generate('/categories/'), 301
				); 
			}
			
			$data = array(
				'id'=> $category['id'],
				'name'=> $category['name'],
			);
	
			if (count($category)) {
				$form = $app['form.factory']->createBuilder('form', $data)
					->add(
						'id', 'hidden', array('data'=>$id,)
					)
					->add(
						'name', 'text', array(
							'constraints' => array(
								new Assert\NotBlank(), 
								new Assert\Length(array('min' => 1))
							)
						)
					)
					
					->add('Usuń', 'submit')
					->getForm();
	 
				$form->handleRequest($request);
				if ($form->isValid()) {
					$categoriesModel->deleteCategory($form->getData());
					$app['session']
						->getFlashBag()
						->add(
							'message', array(
								'type' => 'success', 
								'content' => 'Kategoria została usunięta.'
							)
						);
					return $app->redirect(
						$app['url_generator']->generate('/categories/'), 301
					);
				}
				return $app['twig']->render(
					'categories/delete.twig', array(
						'form' => $form->createView(), 
						'category' => $category
					)
				);
	 
			} else {
				return $app->redirect(
					$app['url_generator']->generate('/categories/'), 301
				);
			}
		}

		/**
		 * View single category.
		 *
		 * @param Application $app application object
		 * @param Request $request request
		 *
		 * @access public
		 * @return mixed Generates page or Redirect.
		 */
		public function view(Application $app, Request $request)
		{
			$categoriesModel = new CategoriesModel($app);
			$booksModel = new BooksModel($app);
			$id = (int) $request->get('id', 0); 
			if ($categoriesModel->idExist($id)) {
				$category = $categoriesModel->getCategory($id);
				$books = $booksModel->getBooksByCategory($id);
				return $app['twig']->render(
					'categories/view.twig', array(
						'category' => $category, 
						'books' => $books
					)
				);
			} else {
				$app['session']->getFlashBag()->add(
					'message', array(
						'type' => 'error', 
						'content' => 'Podana kategoria nie istnieje!'
					)
				);
				return $app->redirect(
					$app['url_generator']->generate('/categories/'), 301
				);
			}
		}

	}