<?php
	/**
	 * Controller VotesController
	 *
	 * @class VotesController
	 * @author Katarzyna Drapala
	 * @link wierzba.wzks.uj.edu.pl/~12_drapala/silex
	 * @uses Silex\Application
	 * @uses Silex\ControllerProviderInterface
	 * @uses Symfony\Component\HttpFoundation\Request
	 * @uses Symfony\Component\Validator\Constraints
	 * @uses Model\VotesModel
	 * @uses Model\BooksModel
	 * @uses Model\UsersModel
	 */

	namespace Controller;

	use Silex\Application;
	use Silex\ControllerProviderInterface;
	use Symfony\Component\HttpFoundation\Request;
	use Symfony\Component\Validator\Constraints as Assert;
	use Model\VotesModel;
	use Model\BooksModel;
	use Model\UsersModel;

	class VotesController implements ControllerProviderInterface
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
			$votesController = $app['controllers_factory'];
			$votesController
				->match('/add/', array($this, 'add'))
				->bind('/votes/add');        
			$votesController
				->get('/view/{id}', array($this, 'view'))
				->bind('/votes/view');
            $votesController
                ->get('/{page}', array($this, 'index'))
                ->value('page', 1)
				->bind('/votes/');
			return $votesController;
		}

		/**
		 * List of all Votes.
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
			$votesModel = new VotesModel($app);
			$pagesCount = $votesModel->countVotesPages($pageLimit);

			if (($page < 1) || ($page > $pagesCount)) {
				$page = 1;
			}
			$votes = $votesModel
				->getVotesPage($page, $pageLimit, $pagesCount);
			$paginator = array(
				'page' => $page, 'pagesCount' => $pagesCount
			);

			return $app['twig']->render(
				'votes/index.twig', array(
					'votes' => $votes//, 
					//'paginator' => $paginator
				)
			);
		}

		/**
		 * Add a Vote.
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
					// $app['url_generator']->generate('/votes/'), 301
				// ); 
			// }
			$booksModel =  new BooksModel($app);
			$books = $booksModel->getBooksList();
			//$usersModel =  new UsersModel($app);
			//$users = $usersModel->getUsersList();
			$votesModel =  new VotesModel($app);
			$votes = $votesModel->getGradingsList();
			
			$data = array(
				'vote' => 'Ocena',
				'book' => 'Książka',
				//'user' => 'użytkownik',
			);

			$form = $app['form.factory']->createBuilder('form', $data)
				->add(
					'vote', 'choice', array(
						'choices'=> $votes
					)
				)
				->add(
					'book', 'choice', array(
						'choices'=> $books
					)
				)
				// ->add(
					// 'user', 'choice', array(
						// 'choices'=> $authors
					// )
				// )
				->add('Dodaj ocenę', 'submit')
				->getForm();

			$form->handleRequest($request);

			if ($form->isValid()) {
				$votesModel = new VotesModel($app);
				$data = $form->getData();
				$lastVoteId = $votesModel->addVote($data);
				$app['session']
					->getFlashBag()
					->add(
						'message', array(
							'type' => 'success', 
							'content' => 'Dodano ocenę książki.'
						)
					);
				return $app->redirect(
					$app['url_generator']->generate(
						'/votes/', array('id' => $lastVoteId)
					)
				);
			}
	
			return $app['twig']->render(
				'votes/add.twig', array('form' => $form->createView())
			);
		
		}

		/**
		 * View single Vote.
		 *
		 * @param Application $app application object
		 * @param Request $request request
		 * @access public
		 * @return mixed Generates page or Redirect.
		 */
		public function view(Application $app, Request $request)
		{
			$id = (int) $request->get('id', 0);
			$votesModel = new VotesModel($app);
			if (ctype_digit((string)$id) && $votesModel->idExist($id)) {
				$vote = $votesModel->getGrading($id);

				//getBook
				$idbook = $vote['id'];
				$booksModel = new BooksModel($app);
				$book = $booksModel->getBook($idbook);
				//$books = $booksModel->getBooksListByVote($idbook);

				//getUser
				$iduser = $vote['id'];
				$usersModel = new UsersModel($app);
				$user = $usersModel->getUser($iduser);
				
				//getGrading
				$idgrading = $vote['id'];
				$votesModel = new VotesModel($app);
				$grading = $votesModel->getGrading($idgrading);

				return $app['twig']->render(
					'votes/view.twig', 
					array(
						'idVote' => $votes,  'id' => $idgrading,
						'idBook' => $books, 'id' => $idbook,
						'idUser' => $users, 'id' => $iduser,
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
					$app['url_generator']->generate('/votes/'), 301
				);
			}
		}
	}
