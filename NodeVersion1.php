<?php
	include './BTreeData.php';

/**
 * DEPRECATED
 * NOT FUNCTIONAL
 * First version of the class Node.
 */
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
			$searchResult = array('found' => null, 'parentKey' => null, 'side' => null);
			$searchResult = $this->findKey($key);
			
			if (!is_null($searchResult['found'])) {
				return false;
			}
			
			if ($this->isLeaf()) {
				return $this;
			}
			else {
				$childNode = (($searchResult['side'] == 'left') 
					? $this->keys[$searchResult['parentKey']]['leftChild'] 
					: $this->keys[$searchResult['parentKey']]['rightChild']);
				
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
			$searchResult = array('found' => null, 'parentKey' => null, 'side' => null);
			$nodeKeyConnection = array();
			
			$searchResult = $this->findKey($key);
			
			if ($this->isLeaf()) {
				$nodeKeyConnection = array('node' => $this, 'key' => $searchResult['found']);
				return (is_null($searchResult['found'])) ? false : $nodeKeyConnection; 
			}
			else {
				if (is_null($searchResult['found'])) {
					$childNode = (($searchResult['side'] == 'left') 
						? $this->keys[$searchResult['parentKey']]['leftChild'] 
						: $this->keys[$searchResult['parentKey']]['rightChild']);
					
					if ($childNode->isLeaf() && $childNode->isAtMinimum()) {
						$rotation = $this->prepareLeaf($searchResult);
						
						if (!$rotation) {
							$this->mergeNodes($searchResult['parentKey']);
							
							if (is_null($this->rootReplacement)) {
								return $this->prepareDelete($key);
							}
							else {
								return true;
							}
						}
					}
					return $childNode->prepareDelete($key);
				}
				else {
					$nodeKeyConnection = $this->prepareNode($searchResult['found']);
					
					if (!is_null($nodeKeyConnection['found'])) {
						return $nodeKeyConnection;
					}
					else if (!is_null($this->rootReplacement)) {
						return array('node' => null, 'key' => (MIN_NUM_CHILDREN - 1));
					}
					else {
						return false;
					}
				}
			}
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
			$searchResult = array('found' => null, 'parentKey' => null, 'side' => null);
			
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
						? $this->keys[$searchResult['parentKey']]['leftChild'] 
						: $this->keys[$searchResult['parentKey']]['rightChild']);
					
					return $childNode->prepareGetData($key);
				}
			}
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
			$result = array('found' => null, 'parentKey' => null, 'side' => null);
			
			foreach ($this->keys as $datakey => $value) {
				if ($value['key'] == $key) {
					$result['found'] = $datakey;
					return $result;
				}
				else if ($key < $value['key']) {
					$result['parentKey'] = $datakey;
					$result['side'] = 'left';
					return $result;
				}
			}
			$result['parentKey'] = ($this->countKeys() - 1);
			$result['side'] = 'right';
			
			return $result;
		}
		
		/**
		 * Returns infos about a childnode of this node possibly containing the given key.
		 * 
		 * @param int $key    The key to be contained.
		 * 
		 * @return array $childInfo    The infos about the childnode possibly containing the given key.
		 */
		public function getChildInfo($key)
		{
			$childInfo = array('parentKey' => null, 'side' => null);
			
			foreach ($this->keys as $datakey => $value) {
				if ($key < $value['key']) {
					$childInfo['parentKey'] = $datakey;
					$childInfo['side'] = 'left';
					return $childInfo;
				}
			}
			$childInfo['parentKey'] = (count($this->keys) - 1);
			$childInfo['side'] = 'right';
			
			return $childInfo;
		}

		/**
		 * ====================================================================================================================
		 * Prepares a node for the deletion of an key.
		 * For this purpose first it tries to get the symmectric key of its left child.
		 * If the left child is at its minimum, try right child.
		 * 
		 * If a symmetric key is found, use it to replace the key to be deleted.
		 * Then delete the symmetric key from the child branch, where it was found.
		 * 
		 * FOR THE CASE THAT BOTH CHILDS ARE AT THEIR MINIMUM, THERE IS PROBABLY A MISTAKE HERE.
		 * 
		 * @param in $parentIndex    The index number of the key to be deleted.
		 * 
		 * @return array $result    ?????????????????????????????????????????????
		 */
		public function prepareNode($parentIndex)
		{
			$result = array();
			$child = $this->keys[$parentIndex]['leftChild'];
			$symmetricKey = end($child->getKeys());
			$node = $child;
			
			while (isset($symmetricKey['rightChild'])) {
				$node = $symmetricKey['rightChild'];
				$symmetricKey = end($node->getKeys());
			}
			if (!$node->isAtMinimum()) {
				$this->keys[$parentIndex]['key'] = $symmetricKey['key'];
				$this->keys[$parentIndex]['data'] = $symmetricKey['data'];
				$result = $child->findKey($symmetricKey['key'], 'deleteData');
			}
			else {
				$child = $this->keys[$parentIndex]['rightChild'];
				$symmetricKey = reset($child->getKeys());
				
				while (isset($symmetricKey['leftChild'])) {
					$node = $symmetricKey['leftChild'];
					$symmetricKey = reset($node->getKeys());
				}
				if (!$node->isAtMinimum()) {
					$this->keys[$parentIndex]['key'] = $symmetricKey['key'];
					$this->keys[$parentIndex]['data'] = $symmetricKey['data'];
					$result = $child->findKey($symmetricKey['key'], 'deleteData');
				}
				else {
					$this->findKey($symmetricKey['key'], 'deleteData');
					
					if (is_null($this->rootReplacement)) {
						$this->keys[$parentIndex]['key'] = $symmetricKey['key'];
						$this->keys[$parentIndex]['data'] = $symmetricKey['data'];
					}
				}
			}
			return $result;
		}
		
		/**
		 * Prepares a leaf for the deletion of a key.
		 * For this purpose is either a left or right rotation initiated.
		 * If that is not possible, a merge of two nodes is initiated.
		 * 
		 * @param array $childInfo    An array hinting the child for the deletion 
		 * 							  by giving the key to the parent and the side of the child.
		 * 
		 * @return void
		 */
		public function prepareLeaf($childInfo)
		{
			$parentKey = $childInfo['parentKey'];
			$side = $childInfo['side'];
			$leftExists = false;
			$rightExists = false;
			$leftRotation = false;
			$rightRotation = false;
			
			if ($side == 'left') {
				$rightExists = true;
				$leftRotation = $this->checkRight($parentKey);
				
				if (!$leftRotation && isset($this->keys[$parentKey - 1])) {
					$leftExists = true;
					$rightRotation = $this->checkLeft($parentKey -1);
				}
				
				// if (!$leftRotation && !$rightRotation) {
					// $this->mergeNodes($parentKey);
				// }
			}
			else if ($side == 'right') {
				$leftExists = true;
				$rightRotation = $this->checkLeft($parentKey);
				
				if (!$rightRotation && isset($this->keys[$parentKey + 1])) {
					$rightExists = true;
					$leftRotation = $this->checkRight($parentKey + 1);
				}
				
				// if (!$leftRotation && !$rightRotation) {
					// $this->mergeNodes($parentKey);
				// }
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
			if (!($this->keys[$parentIndex]['leftChild']->isAtMinimum())) {
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
			if (!($this->keys[$parentIndex]['rightChild']->isAtMinimum())) {
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
			$leftChild = $parent['leftChild'];
			$leftChild->insertData($parent['key'], $parent['data']);
			
			$indexFirstKey = 0;
			$firstKeyRightChild =& $parent['rightChild']->getKey($indexFirstKey);
			$parent['key'] = $firstKeyRightChild['key'];
			$parent['data'] = $firstKeyRightChild['data'];
			
			$parent['rightChild']->deleteData($indexFirstKey);
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
			$rightChild = $parent['rightChild'];
			$rightChild->insertData($parent['key'], $parent['data']);
			
			$indexLastKey = ($parent['leftChild']->countKeys()) - 1;
			$lastKeyLeftChild =& $parent['leftChild']->getKey($indexLastKey);
			$parent['key'] = $lastKeyLeftChild['key'];
			$parent['data'] = $lastKeyLeftChild['data'];
			
			$parent['leftChild']->deleteData($indexLastKey);
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
			$leftChildKeys =& $parent['leftChild']->getKeys();
			$lastKeyLeftChild = end($leftChildKeys);
			$rightChildKeys =& $parent['rightChild']->getKeys();
			$firstKeyRightChild = reset($rightChildKeys);
			
			$parentData = array(array(
				'key' => $parent['key'],
				'data' => $parent['data'],
				'leftChild' => $lastKeyLeftChild['rightChild'],
				'rightChild' => $firstKeyRightChild['leftChild'],
			));
			
			$mergedNode = new Node(array_merge($leftChildKeys, $parentData, $rightChildKeys), $parent['leftChild']->isLeaf());
			
			if ($this->countKeys() > 1) {
				if (isset($this->keys[$parentIndex - 1])) {
					$this->keys[$parentIndex - 1]['rightChild'] = $mergedNode;
				}
				if (isset($this->keys[$parentIndex + 1])) {
					$this->keys[$parentIndex + 1]['leftChild'] = $mergedNode;
				}
				$this->deleteData($parentIndex);
			}
			else {
				$this->rootReplacement = $mergedNode;
			}
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
			
			$insertPoint = $this->insertData($tempElement[0]['key'], $tempElement[0]['data']);
			
			$this->keys[$insertPoint]['leftChild'] = new Node($tempLeft, $childNode->isLeaf());
			$this->keys[$insertPoint]['rightChild'] = new Node($tempRight, $childNode->isLeaf());
			
			$neighbours = array('left' => null, 'right' => null);
			
			if ($this->countKeys() > 1) {
				$neighbours = $this->findNeighbours($insertPoint);
			}
		
			if (!is_null($neighbours['left'])) {
				$this->keys[$neighbours['left']]['rightChild'] = $this->keys[$insertPoint]['leftChild'];
			}
			if (!is_null($neighbours['right'])) {
				$this->keys[$neighbours['right']]['leftChild'] = $this->keys[$insertPoint]['rightChild'];
			}
		}
		
		/**
		 * Adds an array containing a key and BTreeData to this node's keys.
		 * The insertion point is decided by the value of the key compared to the other keys.
		 * Returns a counter hinting the position of the newly inserted data in the array $keys.
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
				while (isset($this->keys[$counter]) && $this->keys[$counter]['key'] < $key) {
					$counter++;
				}
				
				array_splice($this->keys, $counter, 0, array (array (
					'key' => $key,
					'data' => $data,
					'leftChild' => null,
					'rightChild' => null,
				)));
			}
			else {
				$this->keys[0] = array(
					'key' => $key,
					'data' => $data,
					'leftChild' => null,
					'rightChild' => null,
				); 
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
		
		/**
		 * ====================================================================================================================
		 * Case $purpose == 'deleteData' || $purpose == 'getData'
		 *    Returns the node containing the given key if such a node is found,
		 *    else returns false.
		 * Case $purpose == 'insertData'
		 *    Returns false if a node containing the given key is found,
		 *    else returns the node where the key can be inserted.
		 * 
		 * @param int $key    The key to be found.
		 * @param string $purpose    The purpose of the search.
		 * 
		 * @return Node/false    Either return the node if the criteria are met, else false.
		 */
		public function findKeyDEPRECATED($key, $purpose)
		{
			$found = null;
			foreach ($this->keys as $dataKey => $value) {
				if ($value['key'] == $key) {
					$found = $dataKey;
					break;
				}
			}
			
			if ($this->isLeaf()) {
				if (!is_null($found)) {
					return ($purpose == 'deleteData' || $purpose == 'getData') ? array('node' => $this, 'key' => $found) : false;
				}
				else {
					return ($purpose == 'insertData') ? $this : false;
				}
			}
			else {
				if (!is_null($found)) {
					if ($purpose == 'deleteData') {
						$result = $this->prepareNode($found);
						if (!empty($result)) {
							return $result;
						}
						else if (!is_null($this->rootReplacement)) {
							return array('node' => null, 'key' => (MIN_NUM_CHILDREN - 1));
						}
						else {
							return false;
						}
					}
					else {
						return ($purpose == 'getData') ? $this : false;
					}
				}
				else {
					$childInfo = $this->getChildInfo($key);
					$childNode = (($childInfo['side'] == 'left') 
						? $this->keys[$childInfo['parentKey']]['leftChild'] 
						: $this->keys[$childInfo['parentKey']]['rightChild']);
						
					if ($purpose == 'insertData' && $childNode->isFull()) {
						$this->splitNode($childNode);
					}
					if ($purpose == 'deleteData' && $childNode->isLeaf() && $childNode->isAtMinimum()) {
						$this->prepareLeaf($childInfo);
					}
					return $childNode->findKey($key, $purpose);
				}
			}
		}
	}
