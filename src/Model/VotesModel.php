<?php

	namespace Model;

	use Doctrine\DBAL\DBALException;
	use Silex\Application;
	//use Controller\VotesController;

	/**
	 * VotesModel
	 *
	 * @class VotesModel
	 * @author Katarzyna Drapala
	 * @link wierzba.wzks.uj.edu.pl/~12_drapala/silex
	 * @uses Doctrine\DBAL\DBALException
	 * @uses Silex\Application
	 */

	class VotesModel
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
		 * Puts one vote.
		 *
		 * @param  Array $data Associative array with information about vote
		 *
		 * @access public
		 * @return Void
		 */
		public function addVote($data)
		{
			$sql = 'INSERT INTO Votes (id, idVote, idBook, idUser) VALUES (?,?,?,3);';
			$this->_db->executeQuery(
				$sql, array($data['id'], $data['idVote'], $data['idBook'], $data['idUser'])
			);
		}

		/**
		 * Updates one vote.
		 *
		 * @param Array $data array with informations about vote
		 *
		 * @access public
		 * @return Void
		 */
		public function saveVote($data)
		{
			if (isset($data['id']) && ctype_digit((string)$data['id'])) {
				$sql = 'UPDATE Votes SET idVote = ?, idBook = ?, idUser = 3
				WHERE id = ?;';
				$this->_db->executeQuery(
					$sql, array(
						$data['idVote'], $data['idBook'], $data['idUser'], $data['id']
					)
				);
			} else {
				$sql = 'INSERT INTO Votes (id, idVote, idBook, idUser) VALUES (?,?,?,3);';
				$this->_db->executeQuery(
					$sql, array($data['id'], $data['idVote'], $data['idBook'], $data['idUser'])
				);
			}
		}

		/**
		 * Gets votes for all books
		 *
		 * @access public
		 * @return Array tags array.
		 */
		public function getVotesList()
		{
			$sql = 'SELECT * FROM Votes;';
			return $this->_db->fetchAll($sql);
		}
		public function getVotes()
		{
			$sql = 'SELECT * FROM Votes ORDER BY idBook;';
			return $this->_db->fetchAll($sql);
		}
		
		/**
		 * Change key in votes array
		 *
		 * @access public
		 * @return Array votes array.
		 */ 
		/*public function getVotesListDict()
		{
			$votes = $this->getVotes();
			$data = array();
			foreach ($votes as $row) {
				$data[$row['id']] = $row['surname'];
			}
			return $data;
		}*/

		/**
		 * Create a list of votes for one book.
		 *
		 * @param Integer $id book id
		 * @access public
		 * @return Array Associative votes array
		 */
		public function getVotesListByBook($id)
		{
			$sql = 'SELECT Votes.id, Votes.idVote, Votes.idBook
					FROM Books INNER JOIN Votes
					ON Votes.idBook = Books.id
					WHERE Book.id = ?;';
			$sql = 'SELECT Votes.idVote, , Gradings.id, Gradings.vote 
					FROM Votes INNER JOIN Gradings
					ON Votes.idVote = Gradings.id
					WHERE Books.id = ?;';
			return $this->_db->fetchAll($sql, array($id));
		}

		/**
		 * Gets vote's id by book.
		 *
		 * @param Integer $id book id
		 * @access public
		 * @return Array Associative vote id array
		 */
		/*public function getVotesIdByBook($id)
		{
			$sql = 'SELECT idVote 
			FROM Books WHERE id = ?;';
			return $this->_db->fetchAll($sql, array($id));
		}*/

		/**
		 * Gets one vote by id.
		 *
		 * @param Integer $id vote id
		 * @access public
		 * @return Array Associative array contains information about vote
		 */
		public function getGrading($id)
		{
			if (($id != '') && ctype_digit((string)$id)) {
				$sql = 'SELECT AVG(vote) 
						FROM Gradings INNER JOIN Votes
						ON Gradings.id = Votes.idVote
						WHERE Votes.idBook = ?;';
				return $this->_db->fetchAssoc($sql, array((int) $id));
			} else {
				return array();
			}
		}

		/**
		 * Gets votes for all books
		 *
		 * @access public
		 * @return Array tags array.
		 */
		public function getGradingsList()
		{
			$sql = 'SELECT * FROM Gradings;';
			return $this->_db->fetchAll($sql);
		}
		public function getGradings()
		{
			$sql = 'SELECT * FROM Gradings;';
			return $this->_db->fetchAll($sql);
		}
		
		/**
		 * Count votes
		 *
		 * @param Integer $limit number of votes on page
		 * @access public
		 * @return Integer number of page
		 */
		public function countVotesPages($limit)
		{
			$pagesCount = 0;
			$sql = 'SELECT COUNT(*) as pages_count FROM Votes';
			$result = $this->_db->fetchAssoc($sql);
			if ($result) {
				$pagesCount =  ceil($result['pages_count']/$limit);
			}
			return $pagesCount;
		}

		/**
		 * Get votes page
		 *
		 * @param Integer $page
		 * @param Integer $limit number of votes on page
		 * @param Integer $pagesCount
		 * @access public
		 * @return Array number of page
		 */  

		public function getVotesPage($page, $limit, $pagesCount)
		{
			$sql = 'SELECT Books.id, Books.title, Votes.idVote, Votes.idBook FROM Votes INNER JOIN Books 
					ON Books.id = Votes.idBook 
					ORDER BY idBook;';
			$statement = $this->_db->prepare($sql);
			$statement->execute();
			return $statement->fetchAll();
		}

		/**
		 * Gets all books with the same vote
		 *
		 * @param Integer $id vote id
		 * @access public
		 * @return Array Associative votes array
		 */
		/*public function getBooksByVote($id)
		{
			$sql = 'SELECT * FROM Books 
			WHERE Books.idVote = ? 
			ORDER BY Books.title;';
			return $this->_db->fetchAll($sql, array((int) $id));
		}*/

		/**
		 * Check if vote exists
		 *
		 * @param $id vote id
		 * @access public
		 * @return bool true if exists
		 */ 
		public function idExist($id)
		{
		   if (($id != '') && ctype_digit((string)$id)) {
		   $sql = 'SELECT * FROM Votes WHERE id = ?';
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
		 * @param Array $data Associative array with information about vote
		 * @access public
		 * @return Void
		 */
		/*public function deleteVote($data)
		{
			$sql = 'START TRANSACTION; DELETE FROM Votes WHERE id = ?; 
				COMMIT';
			$this->_db->executeQuery(
				$sql, array($data['id'])
			);
		}*/

		/**
		 * Connect vote with his book
		 *
		 * @param Array $data Associative array with vote's and book's id
		 * @access public
		 * @return bool false if connection already exists
		 */ 
		/*public function connectWithBook($data)
		{
			$sql = 'SELECT Books.id, Books.idVote 
					FROM Books
					WHERE Books.id = ? AND Books.idVote = ?';
            if (
                $this->_db->executeUpdate(
                    $sqll, array($data['idBook'], $data['idVote'])
                ) == 1
            ) {
               return false;
            } else {
                $sql = "INSERT INTO `Books` 
                (`Books.id`, `Books.idVote`) 
                VALUES (?, ?);";
                $this->_db->executeQuery(
                    $sql, array($data['idBook'], $data['idVote'])
                );
                return true;
            }
		}*/

		/**
		 * Disconnect vote from book
		 *
		 * @param Array $data Array contains vote's id and book's id
		 * @access public
		 * @return Void
		 */
		/*public function disconnectWithBook($data)
		{
			$sql = "DELETE FROM `Books` 
			WHERE `Books.id`= ? && `Books.idvote`= ?;";
			$this->_db->executeQuery(
				$sql, array($data['idBook'], $data['idvote'])
			);
		}*/
	}