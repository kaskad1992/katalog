<?php

	namespace Model;

	use Doctrine\DBAL\DBALException;
	use Silex\Application;
	use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
	use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
	use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
	use Controller\UsersController;


	/**
	 * UsersModel
	 *
	 * @class UsersModel
	 * @author Katarzyna Drapala
	 * @link wierzba.wzks.uj.edu.pl/~12_drapala/silex
	 * @uses Doctrine\DBAL\DBALException
	 * @uses Silex\Application
	 * @uses Symfony\Component\Security\Core\Exception\UnsupportedUserException
	 * @uses Symfony\Component\Security\Core\Exception\UsernameNotFoundException
	 * @uses Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder
	 */

	 class UsersModel
{
    /**
     * Database access object.
     *
     * @access protected
     * @var $_db Doctrine\DBAL
     */
    protected $_db;
    protected $_app;

    /**
     * Class constructor.
     *
     * @access public
     * @param Appliction $app Silex application object
     */
    public function __construct(Application $app)
    {
        $this->_db = $app['db'];
        $this->_app = $app;
    }

    /**
     * Login user.
     *
     * @access public
     * @return array User or null
     */
    public function login($data)
    {
        $user = $this->getUserByLogin($data['login']); 
        if (count($user)) {
            if (
                $user['password'] == crypt(
                    $data['password'], $user['password']
                )
            ) {
                return $user;
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    /**
     * Puts one user to database.
     *
     * @param  Array $data Associative array with information about user
     * @access public
     * @return Void
     */
    public function addUser($data)
    {
        $sql = 'INSERT INTO `Users` 
        (login, password) 
        VALUES (?,?)';
        $this->_db->executeQuery(
            $sql, array(
				$data['login'], 
				$data['password']
            )
        );
        $userLastId = $this->_db->lastInsertId(); 
        return $userLastId;
    }
	/**
     * Connected user with his role.
     *
     * @param Array $data Associative array with user's id
     * @access public
     * @return Void
     */
    public function addRole($data)
    {
        $sql = 'INSERT INTO `Users_Roles` (`idUser`, `idRole`) VALUES (?,2)';
        $this->_db->executeQuery($sql, array( $data['id']));
    }
	/*public function addRole($data)
    {
		$lastId = 'SELECT id FROM `Users` 
					ORDER BY `id` DESC LIMIT 1';
		$sql =  "UPDATE Users SET idRole = 2 
					WHERE id = '".$lastId."'";
        $this->_db->executeQuery(
            $sql, array(  
				$data['idRole']
            )
        ); 
    }*/

    /**
     * Count users
     *
     * @param Integer $limit number of users on page
     * @access public
     * @return Integer number of page
     */
    public function countUsersPages($limit)
    {
        $pagesCount = 0;
        $sql = 'SELECT COUNT(*) as pages_count FROM Users';
        $result = $this->_db->fetchAssoc($sql);
        if ($result) {
            $pagesCount =  ceil($result['pages_count']/$limit);
        }
        return $pagesCount;
    } 
    
    /**
     * Get user page
     *
     * @param Integer $page
     * @param Integer $limit number of users on page
     * @param Integer $pagesCount
     * @access public
     * @return Array number of page
     */
    public function getUsersPage($page, $limit, $pagesCount)
    {
        if (($page <= 1) || ($page > $pagesCount)) {
            $page = 1;
        }
        $sql = 'SELECT * FROM `Users` ORDER BY login LIMIT :start, :limit';
        $statement = $this->_db->prepare($sql);
        $statement->bindValue('start', ($page-1)*$limit, \PDO::PARAM_INT);
        $statement->bindValue('limit', $limit, \PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll();
    } 

    /**
     * Get information about user
     *
     * @param $id user id
     * @access public
     * @return array Associative array with information about user
     */
    public function getUser($id)
    {
        $sql = 'SELECT * FROM `Users` WHERE id = ?;';
        return $this->_db->fetchAssoc($sql, array((int) $id));
    }
    
    /**
     * Get user by login.
     *
     * @param String $login
     * @access public
     * @return Array Information about searching user.
     */
    public function getUserByLogin($login)
    {
        $sql = 'SELECT * FROM `Users` WHERE login = ?;';
       return $this->_db->fetchAssoc($sql, array((string) $login));
    }

    /**
     * Updates information about user.
     *
     * @param Array $data Associative array with information about user
     * @access public
     * @return Void
     */
    public function editUser($data)
    {
        if (isset($data['id']) && ctype_digit((string)$data['id'])) {
            $sql = 'UPDATE `Users` 
            SET login = ?, 
            WHERE id = ?;';
            $this->_db->executeQuery(
                $sql, array(
                    $data['login'], 
                    $data['id']
                )
            );
        } else {
            return false;
        }
    }

    /**
     * Updates user's password.
     *
     * @param Array $data Associative array with new password
     * @param Integer $id user's id
     * @access public
     * @return Void
     */ 
    // public function changePassword($data, $id)
    // {
        // $sql = "UPDATE `Users` SET `password`=? WHERE `id`= ?;";
        // $this->_db->executeQuery($sql, array($data['new_password'],$id));
    // }

    /**
     * Delete user
     *
     * @param Array $data Associative array with user's id
     * @access public
     * @return Void
     */
/*    public function deleteUser($data)
    {
        $sql = 'START TRANSACTION; 
        DELETE FROM `Users` WHERE id= ?; 
        COMMIT';
        $this->_db->executeQuery($sql, array($data['id'],);
    } */

    /**
     * Check if user's id exists
     *
     * @param $id user id
     * @access public
     * @return bool true if exists
     */
    public function idExist($id)
    {
        if (($id != '') && ctype_digit((string)$id)) {
        $sql = 'SELECT * FROM `Users` WHERE id = ?;';
            if ($this->_db->executeUpdate($sql, array((int) $id)) == 1) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }    

    /**
     * Checks if user's login exists 
     *
     * @param String $name user's login
     * @access public
     * @return bool true if login exists
     *
     **/
    public function nameExist($name)
    {
       $sql = 'SELECT id FROM Users WHERE login = ?;';
        if ($this->_db->executeUpdate($sql, array($name)) == 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Load User by login
     *
     * @access public
     * @param String $login
     * @return array
     * @throws \Symfony\Component\Security\Core
     * \Exception\UsernameNotFoundException
     **/
    public function loadUserByLogin($login)
    {
        $data = $this->getUserByLogin($login);

        if (!$data) {
            throw new UsernameNotFoundException(
                sprintf('Użytkownik "%s" nie istnieje.', $login)
            );
        }

        $roles = $this->getUserRoles($data['id']);

        if (!$roles) {
            throw new UsernameNotFoundException(
                sprintf('Użytkownik "%s" nie istnieje.', $login)
            );
        }

        $user = array(
            'login' => $data['login'],
            'password' => $data['password'],
			//'idRole' => $data['idRole'],
			//'idRole' => $roles,
            'roles' => $roles
        );

        return $user;
    }

    /**
     * Get users role.
     *
     * @param String $userId
     * @access public
     * @return Array
     */
	public function getUserRoles($userId)
    {
        $sql = '
            SELECT Roles.role 
				FROM Users_Roles 
				INNER JOIN Roles 
					ON Users_Roles.idRole=Roles.idRole 
				WHERE Users_Roles.idUser = ?
        ';

        $result = $this->_db->fetchAll($sql, array((string) $userId));

        $roles = array();
        foreach ($result as $row) {
            $roles[] = $row['role'];
        }
        return $roles;
    }
    
    /**
     * Get id of current logged in user
     *
     * @param $app
     * @access public
     * @return mixed
     */
    public function getIdCurrentUser($app)
    {
        $login = $this->getCurrentUser($app);
        $userid = $this->getUserByLogin($login);
        return $userid['id'];
    }

    /**
     * Get information about actual logged in user
     *
     * @param $app
     *
     * @access protected
     * @return mixed
     */
    protected function getCurrentUser($app)
    {
        $token = $app['security']->getToken();
        if (null !== $token) {
            $user = $token->getUser()->getUsername();
        }
        return $user;
    }

    /**
     * Check if user is logged in
     *
     * @param Application $app
     * @access public
     * @return bool
     */
    public function _isLoggedIn(Application $app)
    {
        if ('anonymous' !== $user = $app['security']->getToken()->getUser()) {
            return true;
        } else {
            return false;
        }
    }
}