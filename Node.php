<?php
	include './Keyelement.php';
	
	class Node {
		private $keys;
		private $leaf;
		private $rootReplacement;
	
		public function __construct($keys=null, $isLeaf=true)
		{
			if (is_null($keys)) {
				$this->keys = array();
			}
			else {
				$this->keys = $keys;
			}
			$this->leaf = $isLeaf;
			$this->rootReplacement = null;
		}
	
		/**
		 * Returns all keys of this Node.
		 *
		 * @return array $keys    The keys of this Node.
		 */
		public function getKeys()
		{
			return $this->keys;
		}
	
		/**
		 * Sets the keys for this Node.
		 *
		 * @param array $keys    The new keys for this node.
		 *
		 * @return void
		 */
		public function setKeys(array $keys)
		{
			$this->keys = $keys;
		}
		
		/**
		 * Returns the number of keys contained in the array $keys.
		 * 
		 * @return int count($keys)    The number of keys.
		 */
		public function countKeys()
		{
			return count($this->keys);
		}
		
		/**
		 * Returns the key of the array $keys with the given index if it exists.
		 * Else return false.
		 * 
		 * @param int $index    The given index for the key.
		 * 
		 * @return array / false    Returns an array representing the key if found, else false.
		 */
		public function getKey($index)
		{
			return (isset($this->keys[$index])) ? $this->keys[$index] : false;
		}
		
		/**
		 * Returns the value of $rootReplacement.
		 * 
		 * @return node/null $rootReplacement    The replacemtent for the root if needed.
		 */
		public function getRootReplacement()
		{
			return $this->rootReplacement;
		}
		
		/**
		 * Sets the value of $rootReplacement.
		 * 
		 * @param node $rootReplacement    The new replacement.
		 * 
		 * @return void
		 */
		public function setRootReplacement($rootReplacement)
		{
			$this->rootReplacement = $rootReplacement;
		}
		
		/**
		 * Returns if the array $keys is empty or not.
		 * 
		 * @return boolean    True if not empty, else false.
		 */
		public function hasKeys()
		{
			return !empty($this->keys);
		}
		
		/**
		 * Sets the state of a node as leaf or not.
		 * 
		 * @param boolean $state    The new state for the leaf.
		 * 
		 * @return void
		 */
		public function setLeaf($state)
		{
			$this->leaf = (bool)$state;
		}
		
		/**
		 * Returns if the node is a leaf or an inner node.
		 * Returns true, if it is a leaf, else false for inner node.
		 * 
		 * @return boolean $leaf    The markup for this node.
		 */
		public function isLeaf()
		{
			return $this->leaf;
		}
		
		/**
		 * Returns if this node contains the maximal number of keys.
		 * 
		 * @return boolean    True if this node has maximal number of keys stored, else false.
		 */
		public function isFull()
		{
			return (count($this->keys) >= MIN_NUM_CHILDREN * 2);
		}
		
		/**
		 * Returns if this node contains the minimal number of keys.
		 * 
		 * @return boolean    True if this node has the minimal number of keys stored, else false.
		 */
		public function isAtMinimum()
		{
			return (count($this->keys) <= MIN_NUM_CHILDREN - 1);
		}
		
		/**
		 * Returns the BTreeData linked to a specific key of this node.
		 * 
		 * @param int $key    The key to the data.
		 * 
		 * @return BTreeData    The BTreeData linked to the key.
		 */
		public function getData($key)
		{
			return $this->keys[$key]['data'];
		}
		
		/**
		 * Checks if a given key has a right and/or left neighbour and
		 * returns their keys.
		 * 
		 * @param int $key    The key from which the neighbours are to be found.
		 * 
		 * @return array $neighbours    The result of the search containing the found keys.
		 */
		public function findNeighbours($key)
		{
			$neighbours = array('left' => null, 'right' => null);
			
			if (isset($this->keys[$key - 1])) {
				$neighbours['left'] = $key - 1;
			}
			if (isset($this->keys[$key + 1])) {
				$neighbours['right'] = $key + 1;
			}
			
			return $neighbours;
		}
		
		/**
		 * Checks if the searched key is an element of this node's keys.
		 * If so returns the data.
		 * Else checks if this is a leaf.
		 * If so returns false.
		 * Else find the child node possibly containing the key and
		 * return the value of the search in the child node.
		 * 
		 * @param int $key    The key to the data.
		 * 
		 * @return BTreeData / false    Returns the data if found, else false.
		 */
		public function prepareGetData($key)
		{
			$searchResult = array('found' => null, 'parentIndex' => null, 'side' => null);
			
			$searchResult = $this->findKey($key);
			
			if (!is_null($searchResult['found'])) {
				return $this->getData($searchResult['found']);
			}
			else {
				if ($this->isLeaf()) {
					return false;
				}
				else {					
					$childNode = (($searchResult['side'] == 'left') 
						? $this->keys[$searchResult['parentIndex']]->getLeftChild() 
						: $this->keys[$searchResult['parentIndex']]->getRightChild());
					
					return $childNode->prepareGetData($key);
				}
			}
		}
		
		/**
		 * Returns the node where the data is to be inserted or
		 * returns false if the key already exists.
		 * If this node already contains the given key return false.
		 * If not and this node is a leaf, return this node.
		 * If not and this node is not a leaf, find the child node possibly containing
		 * the key and return the value of the search in the child node.
		 * 
		 * @param int $key    The key to be inserted.
		 * 
		 * @return Node / false    Returns a node or false.
		 */
		public function prepareInsert($key)
		{
			$searchResult = array('found' => null, 'parentIndex' => null, 'side' => null);
			$searchResult = $this->findKey($key);
			
			if (!is_null($searchResult['found'])) {
				return false;
			}
			
			if ($this->isLeaf()) {
				return $this;
			}
			else {
				$childNode = (($searchResult['side'] == 'left') 
					? ($this->keys[$searchResult['parentIndex']]->getLeftChild()) 
					: ($this->keys[$searchResult['parentIndex']]->getRightChild()));
				
				if ($childNode->isFull()) {
					$this->splitNode($childNode);
					return $this->prepareInsert($key);
				}
				
				return $childNode->prepareInsert($key);
			}
		}
		
		/**
		 * Returns the node containing the given key if such a node is found,
		 * else returns false.
		 * Checks first for the key in this node's keys.
		 * If the key is found and this node is a leaf, return this node.
		 * If the key is found and this node is not a leaf, prepare this node and
		 * return a result depending on that.
		 * If the key is not found and this node is a leaf, return false.
		 * If the key is not found and this node is not a leaf, find the child node 
		 * possibly containing the given key, if necessary prepare the child node,
		 * and return the value of the search in the child node.
		 * 
		 * @param int $key    The key to be deleted.
		 * 
		 * @return Node / false    Returns a node or false.
		 */
		public function prepareDelete($key)
		{
			$searchResult = array('found' => null, 'parentIndex' => null, 'side' => null);
			$nodeKeyConnection = array();
			$childNode = null;
			
			$searchResult = $this->findKey($key);
			
			if ($this->isLeaf()) {
				$nodeKeyConnection = array('node' => $this, 'key' => $searchResult['found']);
				return $nodeKeyConnection; 
			}
			else {
				if (is_null($searchResult['found'])) {
					$childNode = $this->prepareChild($searchResult['parentIndex'], $searchResult['side']);
					
					return $childNode->prepareDelete($key);
				}
				else {
					$nodeKeyConnection = $this->prepareNode($searchResult['found']);
					
					return $nodeKeyConnection;
				}
			}
		}
		
		/**
		 * Prepares a child node for the deletion of a key.
		 * The preparation is done by getting the right child node
		 * and ,if necessary, rotating keys or merging nodes.
		 * 
		 * @param int $parentIndex    The index number in this node's keys array of the parent key.
		 * @param string $side    A string indicating if the left or right child is needed.
		 * 
		 * @return Node $childnode    Returns a node that is connected to the previous childnode, but can be different and can have a different parent. 
		 */
		public function prepareChild($parentIndex, $side)
		{
			$childNode = null;
			
			if ($side === 'left') {
				$childNode = $this->keys[$parentIndex]->getLeftChild();
			}
			else {
				$childNode = $this->keys[$parentIndex]->getRightChild();
			}
			
			if ($childNode->isLeaf() && $childNode->isAtMinimum()) {
				$rotation = $this->prepareLeaf($parentIndex, $side);
				
				if (!$rotation) {
					$childNode = $this->mergeNodes($parentIndex);
				}
			}
			return $childNode;
		}
		
		/**
		 * Returns the numeric index of a given key if the
		 * key is found in this node's keys.
		 * Else return the numeric index a the child side for the key
		 * element where the search has to be continued if wanted.
		 * 
		 * @param int $key    The key to be found.
		 * 
		 * @return array $result    An array possibly containing the found index or information for a further search.
		 */
		public function findKey($key)
		{
			$result = array('found' => null, 'parentIndex' => null, 'side' => null);
			
			foreach ($this->keys as $datakey => $value) {
				if ($value->getKey() == $key) {
					$result['found'] = $datakey;
					return $result;
				}
				else if ($key < $value->getKey()) {
					$result['parentIndex'] = $datakey;
					$result['side'] = 'left';
					return $result;
				}
			}
			$result['parentIndex'] = ($this->countKeys() - 1);
			$result['side'] = 'right';
			
			return $result;
		}

		/**
		 * Prepares a node for the deletion of an key.
		 * For this purpose first it tries to get the symmectric key of its left child.
		 * 
		 * If a symmetric key is found, use it to replace the key to be deleted.
		 * Then delete the symmetric key from the child branch, where it was found.
		 * 
		 * @param in $parentIndex    The index number of the key to be deleted.
		 * 
		 * @return array $nodeKeyConnection    Returns a node key connection as given by the prepareDelete-function.
		 */
		public function prepareNode($parentIndex)
		{
			$nodeKeyConnection = array();
			$child = $this->keys[$parentIndex]->getLeftChild();
			$symmetricKey = end($child->getKeys());
			
			while ($symmetricKey->hasRightChild()) {
				$child = $symmetricKey->getRightChild();
				$symmetricKey = end($child->getKeys());
			}
			$parentKey = $this->keys[$parentIndex]->getKey();
			
			$key = $symmetricKey->getKey();
			$data = $symmetricKey->getBTreeData();
			
			$nodeKeyConnection = $this->prepareDelete($key);
			
			if (isset($this->keys[$parentIndex]) && $this->keys[$parentIndex]->getKey() === $parentKey) {
				$this->keys[$parentIndex]->setKey($key);
				$this->keys[$parentIndex]->setBTreeData($data);
			}
			else {
				$nodeKeyConnection = $this->prepareDelete($parentKey);
			}
			
			return $nodeKeyConnection;
		}
		
		/**
		 * Prepares a leaf for the deletion of a key.
		 * For this purpose is either a left or right rotation initiated.
		 * 
		 * @param array $childInfo    An array hinting the child for the deletion 
		 * 							  by giving the key to the parent and the side of the child.
		 * 
		 * @return boolean    Returns true if a rotation could be done, else false.
		 */
		public function prepareLeaf($parentIndex, $side)
		{
			$leftExists = false;
			$rightExists = false;
			$leftRotation = false;
			$rightRotation = false;
			
			if ($side == 'left') {
				$rightExists = true;
				$leftRotation = $this->checkRight($parentIndex);
				
				if (!$leftRotation && isset($this->keys[$parentIndex - 1])) {
					$leftExists = true;
					$rightRotation = $this->checkLeft($parentIndex -1);
				}
			}
			else if ($side == 'right') {
				$leftExists = true;
				$rightRotation = $this->checkLeft($parentIndex);
				
				if (!$rightRotation && isset($this->keys[$parentIndex + 1])) {
					$rightExists = true;
					$leftRotation = $this->checkRight($parentIndex + 1);
				}
			}
			if (!$leftRotation && !$rightRotation) {
				return false;
			}
			return true;
		}
		
		/**
		 * Checks if the left child of a key of the node has the minimal number of children.
		 * If not initiate rotateKeyRight and return true, else return false.
		 * 
		 * @param int $parentIndex    The index number of the parentkey in the array $keys of this node.
		 * 
		 * @return boolean    Returns true if the left child is no at the minimum, else false.
		 */
		public function checkLeft($parentIndex)
		{
			if (!($this->keys[$parentIndex]->getLeftChild()->isAtMinimum())) {
				$this->rotateKeyRight($parentIndex);
				return true;
			}
			else {
				return false;
			}
		}
		
		/**
		 * Checks if the right child of a key of the node has the minimal number of children.
		 * If not initiate rotateKeyLeft and return true, else return false.
		 * 
		 * @param int $parentIndex    The index number of the parentkey in the array $keys of this node.
		 * 
		 * @return boolean    Returns true if the right child is no at the minimum, else false.
		 */
		public function checkRight($parentIndex)
		{
			if (!($this->keys[$parentIndex]->getRightChild()->isAtMinimum())) {
				$this->rotateKeyLeft($parentIndex);
				return true;
			}
			else {
				return false;
			}
		}
		
		/**
		 * Moves the parentkey with the given index into its leftchild node and
		 * replaces it with its symmetric successor (the utmost left element of its right child).
		 * 
		 * @param int $parentIndex    The index number of the parentkey in the array $keys of this node.
		 * 
		 * @return void
		 */
		public function rotateKeyLeft($parentIndex)
		{
			$parent =& $this->keys[$parentIndex];
			$leftChild = $parent->getLeftChild();
			$leftChild->insertData($parent->getKey(), $parent->getBTreeData());
			
			$indexFirstKey = 0;
			$firstKeyRightChild =& $parent->getRightChild()->getKey($indexFirstKey);
			$parent->setKey($firstKeyRightChild->getKey());
			$parent->setBTreeData($firstKeyRightChild->getBTreeData());
			
			$parent->getRightChild()->deleteData($indexFirstKey);
		}
		
		/**
		 * Moves the parentkey with the given index into its rightchild node and
		 * replaces it with its symmetric predecessor (the utmost right element of its left child).
		 * 
		 * @param int $parentIndex    The index number of the parentkey in the array $keys of this node.
		 * 
		 * @return void
		 */
		public function rotateKeyRight($parentIndex)
		{
			$parent =& $this->keys[$parentIndex];
			$rightChild = $parent->getRightChild();
			$rightChild->insertData($parent->getKey(), $parent->getBTreeData());
			
			$indexLastKey = ($parent->getLeftChild()->countKeys()) - 1;
			$lastKeyLeftChild =& $parent->getLeftChild()->getKey($indexLastKey);
			$parent->setKey($lastKeyLeftChild->getKey());
			$parent->setBTreeData($lastKeyLeftChild->getBTreeData());
			
			$parent->getLeftChild()->deleteData($indexLastKey);
		}
		
		/**
		 * Merges two childnodes of a key of this node.
		 * A merge is only initiated if both childnodes have the minimal number of keys.
		 * The childnodes are merged by creating a new node with their shared parentkey
		 * as the middle element between the left and right childs data.
		 * The parentkey has to be deleted from this node afterwards.
		 * If this node has no keys left after the merge, it is the root. Since it is
		 * empty, the merged node has become the new root and is saved in $rootReplacement
		 * so it can be replaced in the BTree class.
		 * 
		 * @param int $parentIndex    The index number of the parentkey in the array $keys of this node.
		 * 
		 * @return void
		 */
		public function mergeNodes($parentIndex)
		{
			$parent =& $this->keys[$parentIndex];
			$leftChildKeys =& $parent->getLeftChild()->getKeys();
			$lastKeyLeftChild = end($leftChildKeys);
			$rightChildKeys =& $parent->getRightChild()->getKeys();
			$firstKeyRightChild = reset($rightChildKeys);
			
			$parentData = array(new Keyelement(
				$parent->getKey(),
				$parent->getBTreeData(),
				$lastKeyLeftChild->getRightChild(),
				$firstKeyRightChild->getLeftChild()
			));
			
			$mergedNode = new Node(array_merge($leftChildKeys, $parentData, $rightChildKeys), $parent->getLeftChild()->isLeaf());
			
			if ($this->countKeys() > 1) {
				if (isset($this->keys[$parentIndex - 1])) {
					$this->keys[$parentIndex - 1]->setRightChild($mergedNode);
				}
				if (isset($this->keys[$parentIndex + 1])) {
					$this->keys[$parentIndex + 1]->setLeftChild($mergedNode);
				}
				$this->deleteData($parentIndex);
			}
			else {
				$this->rootReplacement = $mergedNode;
			}
			return $mergedNode;
		}
		
		/**
		 * Splits a full node into two parts.
		 * The split has to be handled by the parent node.
		 * The first part has half of the original length starting at the first value.
		 * Then the one value following the first part is extracted to be inserted 
		 * into the parent node of this node.
		 * Then the remaining values form the third part.
		 * The first and the third part are added as children of the second part, that was
		 * added to the parent node.
		 * 
		 * @param Node $childNode    The node to be split.
		 * 
		 * @return void
		 */
		public function splitNode($childNode)
		{
			$childKeys = $childNode->getKeys();
			$tempLeft = array_slice($childKeys, 0, MIN_NUM_CHILDREN);
			$tempRight = array_slice($childKeys, MIN_NUM_CHILDREN+1, MIN_NUM_CHILDREN-1);
			$tempElement = array_slice($childKeys, MIN_NUM_CHILDREN, 1);
			
			$insertPoint = $this->insertData($tempElement[0]->getKey(), $tempElement[0]->getBTreeData());
			
			$this->keys[$insertPoint]->setLeftChild(new Node($tempLeft, $childNode->isLeaf()));
			$this->keys[$insertPoint]->setRightChild(new Node($tempRight, $childNode->isLeaf()));
			
			$neighbours = array('left' => null, 'right' => null);
			
			if ($this->countKeys() > 1) {
				$neighbours = $this->findNeighbours($insertPoint);
			}
		
			if (!is_null($neighbours['left'])) {
				$this->keys[$neighbours['left']]->setRightChild($this->keys[$insertPoint]->getLeftChild());
			}
			if (!is_null($neighbours['right'])) {
				$this->keys[$neighbours['right']]->setLeftChild($this->keys[$insertPoint]->getRightChild());
			}
		}
		
		/**
		 * Adds a new Keyelement with the given key and data to this node's keys.
		 * The insertion point is decided by the value of the key compared to the other keys.
		 * Returns a counter hinting the position of the newly inserted Keyelement in this node's keys.
		 * 
		 * @param int $key    The key linked to the data.
		 * @param BTreeData    The data to be saved.
		 * 
		 * @return int $counter    The position of the newly inserted data in the array $keys. 
		 */
		public function insertData($key, BTreeData $data)
		{
			$counter = 0;
			
			if ($this->hasKeys()) {
				while (isset($this->keys[$counter]) && $this->keys[$counter]->getKey() < $key) {
					$counter++;
				}
				
				array_splice($this->keys, $counter, 0, array (new Keyelement($key, $data)));
			}
			else {
				$this->keys[0] = new Keyelement($key, $data); 
			}
			return $counter;
		}
		
		/**
		 * Deletes the data linked to the given key.
		 * 
		 * @param int $key    The key to the data to be deleted.
		 * 
		 * @return void
		 */
		public function deleteData($key)
		{
			array_splice($this->keys,$key, 1);
		}
		
		public function printNode($indent, $output)
		{
			foreach ($this->keys as $value) {
				if (!($this->isLeaf())) {
					$output = $value->getLeftChild()->printNode($indent + 30, $output);
				}
				$output .= '<span style="margin-left: ' . $indent . 'px">' . $value->getKey() . '<span><br>';
				if ($value == end($this->keys) && !($this->isLeaf())) {
					$output = $value->getRightChild()->printNode($indent + 30, $output);
				}
			}
			return $output;
		}
	}
