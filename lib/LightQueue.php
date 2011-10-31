<?php

require_once 'LightQueue/Exception.php';
require_once 'LightQueue/Manager.php';
/* 
 * Represents a connection to a "LightQueue" Job Queue Engine.
 * 
 */
class LightQueue
{
    protected $queueTable;
    
    public function __construct($queueTable = 'queue') 
    {
      $this->queueTable = $queueTable;
    }
    
    public function addTask($task, $kind = 'none')
    {
      $conn = LightQueue_Manager::getConnection();
      
      $stask = serialize($task);
      $stmt = $conn->prepare("INSERT INTO `".$this->queueTable."` SET task = :task, kind = :kind;");
      $stmt->bindParam(':task', $stask);
      $stmt->bindParam(':kind', $kind);
      
      return $stmt->execute();
    }
    
    
    public function getTask($id = null)
    {
      
    }
    
}