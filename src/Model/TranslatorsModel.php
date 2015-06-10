<?php

	namespace Model;

	use Doctrine\DBAL\DBALException;
	use Silex\Application;
	use Controller\TranslatorsController;

	/**
	 * TranslatorsModel
	 *
	 * @class TranslatorsModel
	 * @author Katarzyna Drapala
	 * @link wierzba.wzks.uj.edu.pl/~12_drapala/silex
	 * @uses Doctrine\DBAL\DBALException
	 * @uses Silex\Application
	 */

	class TranslatorsModel
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
		 * Puts one translator.
		 *
		 * @param  Array $data Associative array with information about translator
		 *
		 * @access public
		 * @return Void
		 */
		public function addTranslator($data)
		{
			$sql = 'INSERT INTO Translators (name, surname) VALUES (?,?);';
			$this->_db->executeQuery(
				$sql, array($data['name'], $data['surname'])
			);
		}

		/**
		 * Updates one translator.
		 *
		 * @param Array $data array with informations about translator
		 *
		 * @access public
		 * @return Void
		 */
		public function saveTranslator($data)
		{
			if (isset($data['id']) && ctype_digit((string)$data['id'])) {
				$sql = 'UPDATE Translators SET name = ?, surname = ? 
				WHERE id = ?;';
				$this->_db->executeQuery(
					$sql, array(
						$data['name'], $data['surname'], $data['id']
					)
				);
			} else {
				$sql = 'INSERT INTO Translators (name, surname) 
				VALUES (?,?);';
				$this->_db->executeQuery(
					$sql, array($data['name'], $data['surname'])
				);
			}
		}

		/**
		 * Gets translators for all books
		 *
		 * @access public
		 * @return Array tags array.
		 */
		public function getTranslatorsList()
		{
			$sql = 'SELECT * FROM Translators;';
			return $this->_db->fetchAll($sql);
		}
		public function getTranslators()
		{
			$sql = 'SELECT * FROM Translators ORDER BY surname;';
			return $this->_db->fetchAll($sql);
		}
		
		/**
		 * Change key in translators array
		 *
		 * @access public
		 * @return Array translators array.
		 */ 
		public function getTranslatorsListDict()
		{
			$translators = $this->getTranslators();
			$data = array();
			foreach ($translators as $row) {
				$data[$row['id']] = $row['surname'];
			}
			return $data;
		}

		/**
		 * Create a list of translators for one book.
		 *
		 * @param Integer $id book id
		 * @access public
		 * @return Array Associative translators array
		 */
		public function getTranslatorsListByBook($id)
		{
			$sql = 'SELECT 
			Translators.id, Translators.name, Translators.surname 
			FROM Books INNER JOIN Translators 
			ON Books.idTranslator=Translators.id
			WHERE Books.id = ?;';
			return $this->_db->fetchAll($sql, array($id));
		}

		/**
		 * Gets translator's id by book.
		 *
		 * @param Integer $id book id
		 * @access public
		 * @return Array Associative translator id array
		 */
		public function getTranslatorsIdByBook($id)
		{
			$sql = 'SELECT idTranslator 
			FROM Books WHERE id = ?;';
			return $this->_db->fetchAll($sql, array($id));
		}

		/**
		 * Gets one translator by id.
		 *
		 * @param Integer $id author id
		 * @access public
		 * @return Array Associative array contains information about translator
		 */
		public function getTranslator($id)
		{
			if (($id != '') && ctype_digit((string)$id)) {
				$sql = 'SELECT * FROM Translators WHERE id = ?;';
				return $this->_db->fetchAssoc($sql, array((int) $id));
			} else {
				return array();
			}
		}

		/**
		 * Count translators
		 *
		 * @param Integer $limit number of translators on page
		 * @access public
		 * @return Integer number of page
		 */
		public function countTranslatorsPages($limit)
		{
			$pagesCount = 0;
        $sql = 'SELECT COUNT(*) as pages_count FROM Translators';
        $result = $this->_db->fetchAssoc($sql);
        if ($result) {
            $pagesCount =  ceil($result['pages_count']/$limit);
        }
        return $pagesCount;
		}

		/**
		 * Get translators page
		 *
		 * @param Integer $page
		 * @param Integer $limit number of translators on page
		 * @param Integer $pagesCount
		 * @access public
		 * @return Array number of page
		 */  
		public function getTranslatorsPage($page, $limit, $pagesCount)
		{
			$sql = 'SELECT id, name, surname 
			FROM Translators ORDER BY surname;';
			$statement = $this->_db->prepare($sql);
			$statement->execute();
			return $statement->fetchAll();
		}

		/**
		 * Gets all books with the same translator
		 *
		 * @param Integer $id translator id
		 * @access public
		 * @return Array Associative translators array
		 */
		public function getBooksByTranslator($id)
		{
			$sql = 'SELECT * FROM Books
			INNER JOIN Translators
			ON Book.id=Translator.idBook 
			WHERE Books.Translators.id = ? 
			ORDER BY Book.title';
			return $this->_db->fetchAll($sql, array((int) $id));
		}

		/**
		 * Check if translator exists
		 *
		 * @param $id translator id
		 * @access public
		 * @return bool true if exists
		 */ 
		public function idExist($id)
		{
		   if (($id != '') && ctype_digit((string)$id)) {
		   $sql = 'SELECT id, name, surname 
		   FROM Translators WHERE id = ?;';
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
		 * @param Array $data Associative array with information about translator
		 * @access public
		 * @return Void
		 */
		public function deleteTranslator($data)
		{
			$sql = 'START TRANSACTION; DELETE FROM Translators WHERE id = ?; 
				COMMIT';
			$this->_db->executeQuery(
				$sql, array($data['id'])
			);
		}

		/**
		 * Connect translator with his book
		 *
		 * @param Array $data Associative array with translator's and book's id
		 * @access public
		 * @return bool false if connection already exists
		 */ 
		public function connectWithBook($data)
		{
			$sql = 'SELECT Books.id, Books.idTranslator 
					FROM Books
					WHERE Books.id = ? AND Books.idTranslator = ?';
            if (
                $this->_db->executeUpdate(
                    $sqll, array($data['idBook'], $data['idTranslator'])
                ) == 1
            ) {
               return false;
            } else {
                $sql = "INSERT INTO `Books` 
                (`Books.id`, `Books.idTranslator`) 
                VALUES (?, ?);";
                $this->_db->executeQuery(
                    $sql, array($data['idBook'], $data['idTranslator'])
                );
                return true;
            }
		}

		/**
		 * Disconnect translator from book
		 *
		 * @param Array $data Array contains translator's id and book's id
		 * @access public
		 * @return Void
		 */
		/*public function disconnectWithBook($data)
		{
			$sql = "DELETE FROM `Books` 
			WHERE `Book.id`= ? && `idTranslator`= ?;";
			$this->_db->executeQuery(
				$sql, array($data['id'], $data['id'])
			);
		}*/
	}