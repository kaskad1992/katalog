<?php

	namespace Model;

	use Doctrine\DBAL\DBALException;
	use Silex\Application;
	//use Controller\CategoriesController;

	/**
	 * CategoriesModel
	 *
	 * @class CategoriesModel
	 * @author Katarzyna Drapala
	 * @link wierzba.wzks.uj.edu.pl/~12_drapala/silex
	 * @uses Doctrine\DBAL\DBALException
	 * @uses Silex\Application
	 */

	class CategoriesModel
	{
		/**
		 * Database access object.
		 *
		 * @access protected
		 * @var $_db Doctrine\DBAL
		 */
		protected $_db;

		/**
		 * Class constructor.
		 *
		 * @access public
		 * @param Appliction $app Silex application object
		 */
		public function __construct(Application $app)
		{
			$this->_db = $app['db'];
		}

		/**
		 * Puts one category.
		 *
		 * @param  Array $data Associative array with cateogory name
		 *
		 * @access public
		 * @return Void
		 */
		public function addCategory($data)
		{
			$sql = 'INSERT INTO Categories (name) VALUES (?);';
			$this->_db->executeQuery($sql, array($data['name']));
		}
		
		/**
		 * Updates category.
		 *
		 * @param Array $data Associative array with id and new name.
		 * @access public
		 * @return Void
		 */
		public function saveCategory($data)
		{
			if (isset($data['id']) && ctype_digit((string)$data['id'])) {
				$sql = 'UPDATE Categories SET name = ? WHERE id = ?;';
				$this->_db->executeQuery($sql, array($data['name'], $data['id']));
			} else {
				$sql = 'INSERT INTO Categories (name) VALUES (?);';
				$this->_db->executeQuery($sql, array($data['name']));
			}
		}

		/**
		 * Gets all categories.
		 *
		 * @access public
		 * @return Array Categories array.
		 */
		public function getAll()
		{
			$sql = 'SELECT id, name FROM Categories;';
			return $this->_db->fetchAll($sql);
		}
		public function getCategories()
		{
			$sql = 'SELECT id, name FROM Categories ORDER BY name;';
			return $this->_db->fetchAll($sql);
		}
		
		/**
		 * Change key in categories array
		 *
		 * @access public
		 * @return Array Categories array.
		 */
		public function getCategoriesList()
		{
			$categories = $this->getCategories();
			$data = array();
			foreach ($categories as $row) {
				$data[$row['id']] = $row['name'];
			}
			return $data;
		} 
		
		/**
		 * Get a category by id.
		 *
		 * @access public
		 * @return array Category array
		 */    
		public function getCategory($id)
		{
			if (($id != '') && ctype_digit((string)$id)) {
				$sql = 'SELECT id, name 
				FROM Categories WHERE id= ?;';
				return $this->_db->fetchAssoc($sql, array((int) $id));
			} else {
				return array();
			}
		}

		/**
		 * Count pages.
		 *
		 * @access public
		 * @return integer pagesCount
		 */
		public function countCategoriesPages($limit)
		{
			$pagesCount = 0;
			$sql = 'SELECT COUNT(*) as pages_count FROM Categories';
			$result = $this->_db->fetchAssoc($sql);
			if ($result) {
				$pagesCount =  ceil($result['pages_count']/$limit);
			}
			return $pagesCount;
		}

		/**
		 * Get categories page
		 *
		 * @param Integer $page
		 * @param Integer $limit number of categories page
		 * @param Integer $pagesCount
		 *
		 * @access public
		 * @return Array number of page
		 */ 
		public function getCategoriesPage($page, $limit, $pagesCount)
		{
			$sql = 'SELECT id, name FROM Categories
			ORDER BY name;';
			$statement = $this->_db->prepare($sql);
			$statement->execute();
			return $statement->fetchAll(); 
		}
		
		/**
		 * Check if category id exists
		 *
		 * @param $id category id
		 * @access public
		 * @return bool True if exists.
		 */
		public function idExist($id)
		{
		   if (($id != '') && ctype_digit((string)$id)) {
		   $sql = 'SELECT id, name FROM Categories WHERE id = ?;';
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
		 * Delete category.
		 *
		 * @param Array $data Associative array with category id.
		 * @access public
		 * @return Void
		 */
		public function deleteCategory($data)
		{
			$sql = 'START TRANSACTION; DELETE FROM Categories WHERE id = ?; 
				COMMIT';
			$this->_db->executeQuery(
				$sql, array($data['id'])
			);
		}
		
		/**
		 * Count books in category.
		 *
		 * @access public
		 * @return integer countBooksInCategory
		 */
		public function countBooksInCategory($id)
		{
			$sql = 'SELECT COUNT(*) as count FROM `Books` 
			WHERE idCategory = ?;';
			$result = $this->_db->fetchAssoc($sql, array((int) $id));
			$countBooksInCategory = $result['count'];
			return (int) $countBooksInCategory;
		}
	}