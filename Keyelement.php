<?php
include './BTreeData.php';

/**
 * 
 */
class Keyelement {
	private $key;
	private $bTreeData;
	private $leftChild;
	private $rightChild;
	
	function __construct($key, BTreeData $bTreeData, Node $leftChild=null, Node $rightChild=null)
	{
		$this->key = $key;
		$this->bTreeData = $bTreeData;
		$this->leftChild = $leftChild;
		$this->rightChild = $rightChild;
	}
	
	/**
	 * Returns the key of this Keyelement.
	 * 
	 * @return int $key    The key of this Keyelement.
	 */
	public function getKey()
	{
		return $this->key;
	}
	
	/**
	 * Sets the key for this Keyelement.
	 * 
	 * @param int $key    The new key for this Keyelement
	 * 
	 * @return void
	 */
	public function setKey($key)
	{
		$this->key = $key;
	}
	
	/**
	 * Returns the BTreeData of this Keyelement.
	 * 
	 * @return BTreeData bTreeData    The BTreeData of this Keyelement
	 */
	public function getBTreeData()
	{
		return $this->bTreeData;
	}
	
	/**
	 * Sets the BTreeData for this Keyelement.
	 * 
	 * @param BTreeData $bTreeData    The new BTreeData for this Keyelement
	 */
	public function setBTreeData(BTreeData $bTreeData)
	{
		$this->bTreeData = $bTreeData;
	}
	
	/**
	 * Returns the left child of this Keyelement.
	 * 
	 * @return Node leftChild     The left child of this Keyelement
	 */
	public function getLeftChild()
	{
		return $this->leftChild;
	}
	
	/**
	 * Sets the left child for this Keyelement.
	 * 
	 * @param Node $leftChild    The new left child for this Keyelement
	 */
	public function setLeftChild(Node $leftChild)
	{
		$this->leftChild = $leftChild;
	}
	
	/**
	 * Returns if this Keyelement has a left child.
	 * 
	 * @return boolean    Returns true, if a left child is set, else false.
	 */
	public function hasLeftChild()
	{
		return isset($this->leftChild);
	}
	
	/**
	 * Returns the right child of this Keyelement.
	 * 
	 * @return Node rightChild    The right child of this Keyelement
	 */
	public function getRightChild()
	{
		return $this->rightChild;
	}
	
	/**
	 * Sets the right child for this Keyelement.
	 * 
	 * @param Node $rightChild    The new right child for this Keyelement
	 */
	public function setRightChild(Node $rightChild)
	{
		$this->rightChild = $rightChild;
	}
	
	/**
	 * Returns if this Keyelement has a right child.
	 * 
	 * @return boolean    Returns true, if a right child is set, else false.
	 */
	public function hasRightChild()
	{
		return isset($this->rightChild);
	}
}

