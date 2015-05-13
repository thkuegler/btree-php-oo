<?php
	include './Node.php';

	/**
	 * The class for a B-Tree
	 */
	class BTree {
		private $root;
		private $order;
		private $minNumChildren;
		
		public function __construct()
		{
			$this->root = new Node();
			$this->order = MIN_NUM_CHILDREN * 2;
			$this->minNumChildren = MIN_NUM_CHILDREN;
		}
		
		/**
		 * Returns the root element of the tree.
		 * 
		 * @return Node $root    The root node of the tree.
		 */
		public function getRoot()
		{
			return $this->root;
		}
		
		/**
		 * Sets the root element of the tree.
		 * 
		 * @param Node $node    The new root node for this tree.
		 * 
		 * @return void
		 */
		public function setRoot(Node $node)
		{
			$this->root = $node;
		}
		
		/**
		 * Returns the number of children a node has to have at least.
		 * 
		 * @return int $minNumChildren    The minimum of children.
		 */
		public function getMinNumChildren()
		{
			return $this->minNumChildren;
		}
		
		/**
		 * Returns the the order of the tree.
		 * Every node can have maximal $order children
		 * and maximal $order - 1 keys.
		 * Every node but the root must have minimal $order/2 children
		 * and minimal $order/2 - 1 keys.
		 * The root has minimal 1 child and minimal 2 keys.
		 * 
		 * @return int $order    The order for this tree.
		 */
		public static function getOrder()
		{
			return $this->order;
		}
		
		/**
		 * If found, returns the BTreeData linked to the given key,
		 * else returns false.
		 * 
		 * @param int $key    The key to the data.
		 * 
		 * @return BTreeData/false    If there is a key with data, return it, else return false.
		 */
		public function getData($key)
		{
			$node = $this->root->prepareGetData($key);
			if ($node) {
				return $node->getData($key);
			}
			else {
				return false;
			}
		}
		
		/**
		 * If a node suitable for an insertion of the given key is found, 
		 * do so and return true, else do nothing and return false.
		 * 
		 * @param int $key    The key under which the data is to be inserted.
		 * @param BTreeData $data    The data to be inserted into the tree.
		 * 
		 * @return boolean    If the data was inserted, return true, else false. 
		 */
		public function insertData($key, $data)
		{
			if ($this->root->isFull()) {
				$this->splitRoot();
			}
			$result = $this->root->prepareInsert($key);
			if ($result) {
				$result->insertData($key, $data);
				return true;
			}
			else {
				return false;
			}
		}
		
		/**
		 * Creates a new node as the root for this B-Tree
		 * and than initiates the split of the original root.
		 * 
		 * @return void
		 */
		public function splitRoot()
		{
			$newRoot = new Node();
			$newRoot->splitNode($this->root);
			$this->root = $newRoot;
			$this->root->setLeaf(false);
		}
		
		/**
		 * If a node containing the key is found, delete the data with the
		 * given key and return true, else do nothing and return false.
		 * 
		 * @param int $key    The key to the data to be deleted.
		 * 
		 * @return boolean    If the data was deleted, return true, else false.
		 */
		public function deleteData($key)
		{
			$result = $this->root->prepareDelete($key);
			
			if ($result && is_null($this->root->getRootReplacement())){
				$result['node']->deleteData($result['key']);
				return true;
			}
			else if ($result && !is_null($this->root->getRootReplacement())) {
				$this->root = $this->root->getRootReplacement();
				$result = $this->root->prepareDelete($key);
				$result['node']->deleteData($result['key']);
				return true;
			}
			else {
				return false;
			}
		}
		
		public function printTree()
		{
			$output = '';
			$output .= $this->root->printNode(0, $output);
			return $output;
		}
	}