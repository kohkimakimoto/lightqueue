<?php
require_once "System/Daemon.php";

/**
 * This software has some code from MyQueue.php. 
 * "MyQueue.php" is licensed The MIT License.
 * Please check it 
 * 
 * https://github.com/kotas/myqueue/blob/master/MyQueue.php 
 * 
 * and read following License description.
 * ------------------------------------------------------------------------------
 * https://github.com/kotas/myqueue/blob/master/MyQueue.php 
 * 
 * Sample implementation of message queue using MySQL.
 *
 * Requirement: PHP5 and PDO extension for MySQL.
 *
 * The MIT License
 *
 * Copyright (c) 2010 kotas <kotas at kotas dot jp>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 * ------------------------------------------------------------------------------
 */

/**
 * LightQueue_Daemon.
 * 
 * @category  LightQueue
 * @package   LightQueue_Daemon
 * @author    Kohki Makimoto <kohki.makimoto@gmail.com>
 */
class LightQueue_Daemon
{
  protected $options = array();
  protected $qtable = 'queue';
  
  public function __construct($iniFile)
  {
    $this->options = parse_ini_file($iniFile, true);
  }
  
  public function run()
  {
    System_Daemon::setOptions($this->options['daemon']);
    
    System_Daemon::start();
    while (!System_Daemon::isDying()) {
      // Main process of this daemon.
      System_Daemon::debug('memory usage is '.intval(memory_get_usage() / 1024)."k bytes.");
      
      $this->runTasks();
      
      System_Daemon::iterate(5);
    }
    System_Daemon::stop();
  }
  
  protected function runTasks()
  {
    $pdo = new PDO("mysql:host=".$this->options['mysql']['host'].";dbname=".$this->options['mysql']['dbname'], $this->options['mysql']['user'], $this->options['mysql']['password']);
    
    $runTaskCounter = 0;
    $runTaskLimit = 100;
    
    while ($this->runSingleTask($pdo) && $runTaskCounter < $runTaskLimit) {
      $runTaskCounter++;
    }
  }
  
  protected function runSingleTask($pdo)
  {
    // Lock the top message in the queue.
    $affected = $pdo->exec("
        UPDATE `".$this->options['mysql']['queueTable']."`
        SET id = LAST_INSERT_ID(id),
            locked_until = NOW() + INTERVAL 10 SECOND
            WHERE locked_until < NOW() ORDER BY id LIMIT 1;");
        
    if ($affected == 0) {
        // No job in the queue.
        System_Daemon::debug("There are not any tasks to do.");
        return false;
    }

    // Get the ID of the locked task.
    $task_id = $pdo->lastInsertId();
    if (!$task_id) {
        // Oops, no message in the queue, or failed to lock a message maybe.
        System_Daemon::err("Oops, no message in the queue, or failed to lock a message maybe.");
        return false;  
    }
    
    // Get the data of the locked job.
    $stmt = $pdo->prepare("SELECT task FROM `".$this->options['mysql']['queueTable']."` WHERE id = :task_id;");
    $stmt->bindParam(':task_id', $task_id, PDO::PARAM_INT);
    if (!$stmt->execute()) {
        // In this case, we locked the task but have failed to get the data.
        System_Daemon::err("We locked the task but have failed to get the task data.");
        return false;
    }
    System_Daemon::info("Got a task id = ".$task_id);
    
    // execute job task.
    $task = $stmt->fetchColumn();
    $this->executeTask($task_id, $task);
    System_Daemon::info("Executed a task id = ".$task_id);
    
    // Delete the locked job from the queue.
    $stmt = $pdo->prepare("DELETE FROM `".$this->options['mysql']['queueTable']."` WHERE id = :task_id;");
    $stmt->bindParam(':task_id', $task_id, PDO::PARAM_INT);
    if (!$stmt->execute()) {
        // In this case, we have the task data but the task stays in the queue,
        System_Daemon::err("Oops, no message in the queue, or failed to lock a message maybe.");
        return false;
    }
    System_Daemon::info("Deleted a task id = ".$task_id);
    
    return true;
  }
  
  protected function executeTask($task_id, $task)
  {
    $unstask = @unserialize($task);
    if (!$unstask) {
      System_Daemon::warning("Task was unserialized (task id = ".$task_id."). ");
      return;
    }
    
    if (method_exists($unstask, "run")) {
       $unstask->run();
    } else {
      System_Daemon::warning("Task has not run method. (task id = ".$task_id."). ");
      return;
    }
  }
  
  public function writeAutoRun()
  {
    System_Daemon::setOptions($this->options['daemon']);
    $path = System_Daemon::writeAutoRun();
  }
  
  
}