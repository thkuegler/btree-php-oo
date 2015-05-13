<?php
	/**
	 * The class for saving data in the BTree.
	 */
	class BTreeData {
		/**
		 * The data to be saved.
		 * 
		 * @var undefined
		 * @access private
		 */
		private $data;
		
		public function __construct($data=null)
		{
			$this->data = $data;
		}
		
		/**
		 * Returns the data for this BTreeData.
		 * 
		 * @return mixed $data   The data of this BTreeData.
		 */
		public function getData()
		{
			return $this->data;
		}
		
		/**
		 * Sets the data for this BTreeData.
		 * 
		 * @param mixed $data    The new data for this BTreeData.
		 * 
		 * @return void
		 */
		public function setData($data)
		{
			$this->data = $data;
		}
	}
