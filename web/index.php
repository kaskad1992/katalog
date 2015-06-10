<?php
	require_once __DIR__.'/../vendor/autoload.php';
	$app = new Silex\Application();
	$app['debug'] = true;

	$app->register(
		new Silex\Provider\TwigServiceProvider(),
		array(
			'twig.path' => __DIR__.'/../src/views',
		)
	);
	$app->register(new Silex\Provider\FormServiceProvider());
	$app->register(new Silex\Provider\ValidatorServiceProvider());
	$app->register(
		new Silex\Provider\TranslationServiceProvider(),
		array(
			'translator.domains' => array(),
		)
	);
	$app->register(
		new Silex\Provider\TranslationServiceProvider(),
		array(
			'translator.messages' => array(),
		)
	);
	$app->register(
		new Silex\Provider\DoctrineServiceProvider(),
		array(
			'db.options' => array(
				'driver'    => 'pdo_mysql',
				'host'      => 'localhost',
				'dbname'    => '12_drapala',
				'user'      => '12_drapala',
				'password'  => 'W3p8r1j8w1',
				'charset'   => 'utf8',
                'driverOptions' => array(
                        1002=>'SET NAMES utf8'
                )
			),
		)
	);

	$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
	$app->register(new Silex\Provider\SessionServiceProvider());
	$app->register(
		new Silex\Provider\SecurityServiceProvider(),
		array(
			'security.firewalls' => array(
				'admin' => array(
					'pattern' => '^.*$',
					'form' => array(
						'login_path' => '/auth/login',
						'check_path' => '/books/login_check',
						'default_target_path'=> '/books/1',
						'username_parameter' => 'form[login]',
						'password_parameter' => 'form[password]',
					),
					'logout'  => true,
					'anonymous' => true,
					'logout' => array(
						'logout_path' => '/auth/logout',
						'default_target_path' => '/books'),
					'users' => $app->share(
						function() use ($app) {
							return new User\UserProvider($app);
						}
					),
				),
			),
			'security.access_rules' => array(
				array('^/auth/.+$|^/users/register.*$|^/books.*$|^/authors/.*$|^/search/.*$|^/categories.*$|^/publishers.*$|^/translators.*$|^/votes/.*$', 'IS_AUTHENTICATED_ANONYMOUSLY'),
				//array('^/rates.*$|^/votes/.*$|^/users.*', 'ROLE_USER'),
				array('^/.+$', 'ROLE_ADMIN')
			),
			'security.role_hierarchy' => array(
				'ROLE_ADMIN' => array('ROLE_USER', 'ROLE_ANONYMOUS'),
				'ROLE_USER' => array('ROLE_ANONYMOUS')
			),
		)
	);

	$app->mount('/books/', new Controller\BooksController());
	$app->mount('/users/', new Controller\UsersController());
	$app->mount('/categories/', new Controller\CategoriesController());
	$app->mount('/authors/', new Controller\AuthorsController());
	$app->mount('/translators/', new Controller\TranslatorsController());
	$app->mount('/publishers/', new Controller\PublishersController());
	$app->mount('/auth/', new Controller\AuthController());
	$app->mount('/votes/', new Controller\VotesController());

	$app->get(
		'/', function () use ($app)
		{
			return $app->redirect($app["url_generator"]->generate("/books/"));
		}
	)
	->bind('/');

/*
	$app->error(function (\Exception $e, $code) use($app) {
		$app['session']->getFlashBag()->add(
						'message', array(
							'type' => 'error',
							'content' => 'Nie odnaleziono żądanej strony.'
						)
		);
		return $app->redirect($app["url_generator"]->generate("/books/"));
	});
*/
	$app->run();
