<?php

	namespace Model;

	use Doctrine\DBAL\DBALException;
	use Silex\Application;
	//use Controller\BooksController;

	/**
	 * BooksModel
	 *
	 * @class BooksModel
	 * @author Katarzyna Drapala
	 * @link wierzba.wzks.uj.edu.pl/~12_drapala/silex
	 * @uses Doctrine\DBAL\DBALException
	 * @uses Silex\Application
	 */

	class BooksModel
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
		 * Puts one book.
		 *
		 * @param Array $data Associative array with information about book
		 * @access public
		 * @return Void
		 */
		public function addBook($data)
		{
			$sql = 'INSERT INTO Books (title, original_title, idAuthor, idTranslator, idPublisher, published, ISBN, idCategory) 
			VALUES (?,?,?,?,?,?,?,?);';
			$this->_db->executeQuery(
				$sql, array(
					$data['title'], 
					$data['original_title'], 
					$data['author'], 
					$data['translator'], 
					$data['publisher'], 
					$data['published'], 
					$data['ISBN'], 
					$data['category'],
				)
			);
			$bookLastId = $this->_db->lastInsertId();
			return $bookLastId;
		}

		/**
		 * Gets all books.
		 *
		 * @access public
		 * @return Array Books array
		 */
		public function getAll()
		{
			$sql = 'SELECT * FROM Books';
			return $this->_db->fetchAll($sql);
		}
		public function getBooksList(){
			$sql = 'SELECT * FROM Books';
			return $this->_db->fetchAll($sql);
		}
		
		/**
		 * Gets all books with categories.
		 *
		 * @access public
		 * @return Array Books array
		 */
		public function getAllWithCategory()
		{
			$sql = 'SELECT * FROM Books INNER JOIN Categories
			ON Books.idCategory=Categories.id;';
			return $this->_db->fetchAll($sql);
		}
		
		/**
		 * Gets all books with votes.
		 *
		 * @access public
		 * @return Array Books array
		 */
		public function getAllWithVotes()
		{
			$sql = 'SELECT * FROM Books INNER JOIN Votes
			ON Books.id = Votes.idBook;';
			return $this->_db->fetchAll($sql);
		}

		/**
		 * Updates one book.
		 *
		 * @param Array $data array with information about book
		 * @access public
		 * @return Void
		 */
		public function saveBook($data)
		{
			if (isset($data['id']) && ctype_digit((string)$data['id'])) {
				$sql = 'START TRANSACTION; 
				UPDATE BOOK SET 
					title = ?, 
					original_title = ?,
					idAuthor = ?,
					idCategory = ?,
					idPublisher = ?,
					idTranslator = ?,
					published = ?,
					ISBN = ?,
				WHERE id = ?; 
				COMMIT';
				$this->_db->executeQuery(
					$sql, array(
						$data['title'], 
						$data['original_title'],
						$data['author'], 						
						$data['category'],  
						$data['publisher'], 
						$data['translator'],
						$data['published'],
						$data['ISBN'],
						$data['id']
					)
				);
			} else {
			$sql = 'INSERT INTO Books (title, original_title, idAuthor, idTranslator, idPublisher, published, ISBN, idCategory) 
				VALUES (?,?,?,?,?,?,?,?);';
				$this->_db->executeQuery(
					$sql, array(
						$sql, array(
							$data['title'], 
							$data['original_title'], 
							$data['author'], 
							$data['translator'], 
							$data['publisher'], 
							$data['published'], 
							$data['ISBN'], 
							$data['category'],
						)
					)
				); 
			}
		}

		/**
		 * Get a book by id.
		 *
		 * @param Integer $id book id
		 * @access public
		 * @return Array Associative array with information about book
		 */
		public function getBook($id)
		{
			$sql = 'SELECT * FROM Books
			WHERE id= ?;';
			return $this->_db->fetchAssoc($sql, array((int) $id));
		}

		/**
		 * Get a book with category
		 *
		 * @param Integer $id book id
		 * @access public
		 * @return Array Associative array with information about book
		 */
		public function getBookWithCategory($id)
		{
			$sql = 'SELECT * FROM Books INNER JOIN Categories
				ON Books.idCategory = Categories.id
				WHERE Books.id = ?;';
			return $this->_db->fetchAssoc($sql, array($id));
		}
		
		/**
		 * Get books with the same category
		 *
		 * @param Integer $id category id
		 * @access public
		 * @return array Books array
		 */
		public function getBooksByCategory($id)
		{
			$sql = 'SELECT * FROM Books WHERE idCategory = ?;';
			return $this->_db->fetchAll($sql, array($id));
		}

		public function countBooksPages($limit)
		{
			$pagesCount = 0;
			$sql = 'SELECT COUNT(*) as pages_count FROM Books';
			$result = $this->_db->fetchAssoc($sql);
			if ($result) {
				$pagesCount =  ceil($result['pages_count']/$limit);
			}
			return $pagesCount;
		}
		
		/**
		 * Get books with the same publisher
		 *
		 * @param Integer $id publisher id
		 * @access public
		 * @return array Books array
		 */
		public function getBooksByPublisher($id)
		{
			$sql = 'SELECT * FROM Books WHERE idPublisher = ?;';
			return $this->_db->fetchAll($sql, array($id));
		}
		
		/**
		 * Get books with the same translator
		 *
		 * @param Integer $id translator id
		 * @access public
		 * @return array Books array
		 */
		public function getBooksByTranslator($id)
		{
			$sql = 'SELECT * FROM Books WHERE idTranslator = ?;';
			return $this->_db->fetchAll($sql, array($id));
		}


		/**
		 * Get book page
		 *
		 * @param Integer $page
		 * @param Integer $limit number of books on page
		 * @param Integer $pagesCount
		 *
		 * @access public
		 * @return Array number of page
		 */
		public function getBooksPage($page, $limit, $pagesCount)
		{
			$sql = 'SELECT * FROM Books;';
			$statement = $this->_db->prepare($sql);
			$statement->execute();
			return $statement->fetchAll();
		}
		
		/**
		 * Delete one book.
		 *
		 * @param Array $data Associative array with book's id
		 * @access public
		 * @return Void
		 */
		public function deleteBook($data)
		{
			$sql = 'START TRANSACTION; DELETE FROM Books WHERE id= ?; 
				COMMIT';
			$this->_db->executeQuery(
				$sql, array($data['id'])
			);
		}

		/**
		 * Check if book's id exists
		 *
		 * @param $id book id
		 * @access public
		 * @return bool true if exists
		 */
		public function idExist($id)
		{
			if (($id != '') && ctype_digit((string)$id)) {
			$sql = 'SELECT * FROM Books WHERE id = ?;';
				if ($this->_db->executeUpdate($sql, array((int) $id)) == 1) {
					return true;
				} else {
					return false;
				}
			} else {
				return false;
			}
		}
	}
