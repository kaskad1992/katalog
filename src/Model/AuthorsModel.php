<?php

	namespace Model;

	use Doctrine\DBAL\DBALException;
	use Silex\Application;
	//use Controller\AuthorsController;

	/**
	 * AuthorsModel
	 *
	 * @class AuthorsModel
	 * @author Katarzyna Drapala
	 * @link wierzba.wzks.uj.edu.pl/~12_drapala/silex
	 * @uses Doctrine\DBAL\DBALException
	 * @uses Silex\Application
	 */

	class AuthorsModel
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
		 * Puts one author.
		 *
		 * @param  Array $data Associative array with information about author
		 *
		 * @access public
		 * @return Void
		 */
		public function addAuthor($data)
		{
			$sql = 'INSERT INTO Authors (name, surname) VALUES (?,?);';
			$this->_db->executeQuery(
				$sql, array($data['name'], $data['surname'])
			);
		}

		/**
		 * Updates one author.
		 *
		 * @param Array $data array with informations about author
		 *
		 * @access public
		 * @return Void
		 */
		public function saveAuthor($data)
		{
			if (isset($data['id']) && ctype_digit((string)$data['id'])) {
				$sql = 'UPDATE Authors SET name = ?, surname = ? 
				WHERE id = ?;';
				$this->_db->executeQuery(
					$sql, array(
						$data['name'], $data['surname'], $data['id']
					)
				);
			} else {
				$sql = 'INSERT INTO Authors (name, surname) 
				VALUES (?,?);';
				$this->_db->executeQuery(
					$sql, array($data['name'], $data['surname'])
				);
			}
		}

		/**
		 * Gets authors for all books
		 *
		 * @access public
		 * @return Array tags array.
		 */
		public function getAuthorsList()
		{
			$sql = 'SELECT * FROM Authors;';
			return $this->_db->fetchAll($sql);
		}
		public function getAuthors()
		{
			$sql = 'SELECT * FROM Authors ORDER BY surname;';
			return $this->_db->fetchAll($sql);
		}
		
		/**
		 * Change key in authors array
		 *
		 * @access public
		 * @return Array authors array.
		 */ 
		public function getAuthorsListDict()
		{
			$authors = $this->getAuthors();
			$data = array();
			foreach ($authors as $row) {
				$data[$row['id']] = $row['surname'];
			}
			return $data;
		}

		/**
		 * Create a list of authors for one book.
		 *
		 * @param Integer $id book id
		 * @access public
		 * @return Array Associative authors array
		 */
		public function getAuthorsListByBook($id)
		{
			// $sql = 'SELECT 
			// Author.id, Author.name, Author.surname 
			// FROM Books INNER JOIN Authors
			// ON Book.idAuthor=Author.id
			// WHERE Book.id = ?;';
			$sql = 'SELECT idAuthor FROM Books
			WHERE Books.id = ?;';
			return $this->_db->fetchAll($sql, array($id));
		}

		/**
		 * Gets author's id by book.
		 *
		 * @param Integer $id book id
		 * @access public
		 * @return Array Associative author id array
		 */
		public function getAuthorsIdByBook($id)
		{
			$sql = 'SELECT idAuthor 
			FROM Books WHERE id = ?;';
			return $this->_db->fetchAll($sql, array($id));
		}

		/**
		 * Gets one author by id.
		 *
		 * @param Integer $id author id
		 * @access public
		 * @return Array Associative array contains information about author
		 */
		public function getAuthor($id)
		{
			if (($id != '') && ctype_digit((string)$id)) {
				$sql = 'SELECT * FROM Authors WHERE id = ?;';
				return $this->_db->fetchAssoc($sql, array((int) $id));
			} else {
				return array();
			}
		}

		/**
		 * Count authors
		 *
		 * @param Integer $limit number of authors on page
		 * @access public
		 * @return Integer number of page
		 */
		public function countAuthorsPages($limit)
		{
			$pagesCount = 0;
			$sql = 'SELECT COUNT(*) as pages_count FROM Authors';
			$result = $this->_db->fetchAssoc($sql);
			if ($result) {
				$pagesCount =  ceil($result['pages_count']/$limit);
			}
			return $pagesCount;
		}

		/**
		 * Get authors page
		 *
		 * @param Integer $page
		 * @param Integer $limit number of authors on page
		 * @param Integer $pagesCount
		 * @access public
		 * @return Array number of page
		 */  

		public function getAuthorsPage($page, $limit, $pagesCount)
		{
			$sql = 'SELECT id, name, surname 
			FROM Authors ORDER BY surname;';
			$statement = $this->_db->prepare($sql);
			$statement->execute();
			return $statement->fetchAll();
		}

		/**
		 * Gets all books with the same author
		 *
		 * @param Integer $id author id
		 * @access public
		 * @return Array Associative authors array
		 */
		public function getBooksByAuthor($id)
		{
			$sql = 'SELECT * FROM Books 
			WHERE Books.idAuthor = ? 
			ORDER BY Books.title;';
			return $this->_db->fetchAll($sql, array((int) $id));
		}

		/**
		 * Check if author exists
		 *
		 * @param $id author id
		 * @access public
		 * @return bool true if exists
		 */ 
		public function idExist($id)
		{
		   if (($id != '') && ctype_digit((string)$id)) {
		   $sql = 'SELECT id, name, surname 
		   FROM Authors WHERE id = ?';
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
		public function deleteAuthor($data)
		{
			$sql = 'START TRANSACTION; DELETE FROM Authors WHERE id = ?; 
				COMMIT';
			$this->_db->executeQuery(
				$sql, array($data['id'])
			);
		}

		/**
		 * Connect author with his book
		 *
		 * @param Array $data Associative array with author's and book's id
		 * @access public
		 * @return bool false if connection already exists
		 */ 
		public function connectWithBook($data)
		{
			$sql = 'SELECT Books.id, Books.idAuthor 
					FROM Books
					WHERE Books.id = ? AND Books.idAuthor = ?';
            if (
                $this->_db->executeUpdate(
                    $sqll, array($data['idBook'], $data['idAuthor'])
                ) == 1
            ) {
               return false;
            } else {
                $sql = "INSERT INTO `Books` 
                (`Books.id`, `Books.idAuthor`) 
                VALUES (?, ?);";
                $this->_db->executeQuery(
                    $sql, array($data['idBook'], $data['idAuthor'])
                );
                return true;
            }
		}

		/**
		 * Disconnect author from book
		 *
		 * @param Array $data Array contains author's id and book's id
		 * @access public
		 * @return Void
		 */
		/*public function disconnectWithBook($data)
		{
			$sql = "DELETE FROM `Books` 
			WHERE `Books.id`= ? && `Books.idAuthor`= ?;";
			$this->_db->executeQuery(
				$sql, array($data['idBook'], $data['idAuthor'])
			);
		}*/
	}