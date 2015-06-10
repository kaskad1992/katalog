<?php

	namespace Model;

	use Doctrine\DBAL\DBALException;
	use Silex\Application;
	//use Controller\PublishersController;

	/**
	 * PublishersModel
	 *
	 * @class PublishersModel
	 * @author Katarzyna Drapala
	 * @link wierzba.wzks.uj.edu.pl/~12_drapala/silex
	 * @uses Doctrine\DBAL\DBALException
	 * @uses Silex\Application
	 */

	class PublishersModel
	{
		/**
		 * Database access object.
		 *
		 * @access protected
		 * @var $_db Doctrine\DBAL
		 */
		protected $_db;

		/**
		 * Constructor
		 *
		 * @param Application $app
		 * @access public
		 */
		public function __construct(Application $app)
		{
			$this->_db = $app['db'];
		}

		/**
		 * Puts one publisher.
		 *
		 * @param  Array $data Associative array with information about publisher
		 *
		 * @access public
		 * @return Void
		 */
		public function addPublisher($data)
		{
			$sql = 'INSERT INTO Publishers (name) VALUES (?);';
			$this->_db->executeQuery(
				$sql, array($data['name'])
			);
		}

		/**
		 * Updates one publisher.
		 *
		 * @param Array $data array with informations about publisher
		 *
		 * @access public
		 * @return Void
		 */
		public function savePublisher($data)
		{
			if (isset($data['id']) && ctype_digit((string)$data['id'])) {
				$sql = 'UPDATE Publishers SET name = ? 
				WHERE id = ?;';
				$this->_db->executeQuery(
					$sql, array(
						$data['name'], $data['id']
					)
				);
			} else {
				$sql = 'INSERT INTO Publishers (name) 
				VALUES (?);';
				$this->_db->executeQuery(
					$sql, array($data['name'])
				);
			}
		}

		/**
		 * Gets publishers for all books
		 *
		 * @access public
		 * @return Array tags array.
		 */
		public function getPublishersList()
		{
			$sql = 'SELECT * FROM Publishers;';
			return $this->_db->fetchAll($sql);
		}
		public function getPublishers()
		{
			$sql = 'SELECT * FROM Publishers ORDER BY name;';
			return $this->_db->fetchAll($sql);
		}
		
		/**
		 * Change key in publishers array
		 *
		 * @access public
		 * @return Array publishers array.
		 */ 
		public function getPublishersListDict()
		{
			$publishers = $this->getPublishers();
			$data = array();
			foreach ($publishers as $row) {
				$data[$row['id']] = $row['name'];
			}
			return $data;
		}

		/**
		 * Create a list of publishers for one book.
		 *
		 * @param Integer $id book id
		 * @access public
		 * @return Array Associative authors array
		 */
		public function getPublishersListByBook($id)
		{
			$sql = 'SELECT 
			Publishers.id, Publishers.name
			FROM Books INNER JOIN Publishers
			ON Books.idPublisher=Publishers.id
			WHERE Books.id = ?;';
			return $this->_db->fetchAll($sql, array($id));
		}

		/**
		 * Gets publisher's id by book.
		 *
		 * @param Integer $id book id
		 * @access public
		 * @return Array Associative publisher id array
		 */
		public function getPublishersIdByBook($id)
		{
			$sql = 'SELECT idPublisher 
			FROM Books WHERE id = ?;';
			return $this->_db->fetchAll($sql, array($id));
		}

		/**
		 * Gets one publisher by id.
		 *
		 * @param Integer $id publisher id
		 * @access public
		 * @return Array Associative array contains information about publisher
		 */
		public function getPublisher($id)
		{
			if (($id != '') && ctype_digit((string)$id)) {
				$sql = 'SELECT * FROM Publishers WHERE id = ?;';
				return $this->_db->fetchAssoc($sql, array((int) $id));
			} else {
				return array();
			}
		}

		/**
		 * Count publishers
		 *
		 * @param Integer $limit number of publisher on page
		 * @access public
		 * @return Integer number of page
		 */
		public function countPublishersPages($limit)
		{
			$pagesCount = 0;
			$sql = 'SELECT COUNT(*) as pages_count FROM Publishers';
			$result = $this->_db->fetchAssoc($sql);
			if ($result) {
				$pagesCount =  ceil($result['pages_count']/$limit);
			}
			return $pagesCount;
		}

		/**
		 * Get publishers page
		 *
		 * @param Integer $page
		 * @param Integer $limit number of publishers on page
		 * @param Integer $pagesCount
		 * @access public
		 * @return Array number of page
		 */  
		public function getPublishersPage($page, $limit, $pagesCount)
		{
			$sql = 'SELECT id, name 
			FROM Publishers ORDER BY name;';
			$statement = $this->_db->prepare($sql);
			$statement->execute();
			return $statement->fetchAll();
		}

		/**
		 * Gets all books with the same publisher
		 *
		 * @param Integer $id publisher id
		 * @access public
		 * @return Array Associative publishers array
		 */
		public function getBooksByPublisher($id)
		{
			$sql = 'SELECT * FROM Books 
			LEFT JOIN Publishers 
			ON Books.idPublisher=Publishers.id
			WHERE Books.Publishers.id = ? 
			ORDER BY Books.title;';
			return $this->_db->fetchAll($sql, array((int) $id));
		}

		/**
		 * Check if publisher exists
		 *
		 * @param $id publisher id
		 * @access public
		 * @return bool true if exists
		 */ 
		public function idExist($id)
		{
		   if (($id != '') && ctype_digit((string)$id)) {
		   $sql = 'SELECT id, name
		   FROM Publishers WHERE id = ?;';
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
		 * Delete one post.
		 *
		 * @param Array $data Associative array with information about author
		 * @access public
		 * @return Void
		 */
		public function deletePublisher($data)
		{
			$sql = 'START TRANSACTION; DELETE FROM Publishers WHERE id = ?; 
				COMMIT';
			$this->_db->executeQuery(
				$sql, array($data['id'])
			);
		}

		/**
		 * Connect publisher with his book
		 *
		 * @param Array $data Associative array with publisher's and book's id
		 * @access public
		 * @return bool false if connection already exists
		 */ 
		public function connectWithBook($data)
		{
			$sql = 'SELECT Books.id, Books.idPublisher 
					FROM Books
					WHERE Books.id = ? AND Books.idPublisher = ?';
            if (
                $this->_db->executeUpdate(
                    $sqll, array($data['idBook'], $data['idPublisher'])
                ) == 1
            ) {
               return false;
            } else {
                $sql = "INSERT INTO `Books` 
                (`Books.id`, `Books.idPublisher`) 
                VALUES (?, ?);";
                $this->_db->executeQuery(
                    $sql, array($data['idBook'], $data['idPublisher'])
                );
                return true;
            }
		}

		/**
		 * Disconnect publisher from book
		 *
		 * @param Array $data Array contains publisher's id and book's id
		 * @access public
		 * @return Void
		 */
		public function disconnectWithBook($data)
		{
			$sql = "DELETE FROM `Publishers` 
			WHERE `Book.id`= ? && `id`= ?;";
			$this->_db->executeQuery(
				$sql, array($data['idBook'], $data['id'])
			);
		} 
	}