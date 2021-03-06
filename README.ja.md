# LightQueue

LightQueue はPHPで書かれたシンプルなジョブキューエンジンです。MySQLとPHP4,PDOを必要とします。

ジョブを順次実行するデーモンと、ジョブをDBに登録するシンプルなPHP APIを提供します。

## ライセンス

The MIT Licenseです。

また以下のライセンスのソフトウェアのコードを含んでいます。

myqueue

    https://github.com/kotas/myqueue/blob/master/MyQueue.php
    The MIT License.

System_Daemon

    http://trac.plutonia.nl/projects/system_daemon
    New BSD Licence.


## インストール
適当なディレクトリにlightqueueディレクトリ一式を配置します。

lightqueue/schema/lightqueue.sqlのDDLでMySQLにテーブルを作成します。

    -- -----------------------------------------------------
    -- Create database
    -- -----------------------------------------------------
    CREATE DATABASE lightqueue CHARACTER SET utf8;

    -- -----------------------------------------------------
    -- Table `queue`
    -- -----------------------------------------------------
    DROP TABLE IF EXISTS `queue` ;

    CREATE  TABLE IF NOT EXISTS `queue` (
      `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
      `kind` VARCHAR(30) NOT NULL ,
      `task` LONGTEXT NOT NULL ,
      `locked_until` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
      `created_at` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' ,
      PRIMARY KEY (`id`) )
    ENGINE = InnoDB
    DEFAULT CHARACTER SET = utf8
    COLLATE = utf8_bin;

lightqueue/command/lightqueued_setup.phpを実行して起動スクリプトを作成します。
    
    例)
    # php ${your lightqueue dir}/command/lightqueued_setup.php

## 使い方

ジョブを実行するデーモンを実行します。
    
    # /etc/init.d/lightqueued start
  
停止は以下のようにします。
    
    # /etc/init.d/lightqueued stop
  
ジョブを投入するには、以下のようにLightQueue#addTaskメソッドでPHPのオブジェクトを投入します。ジョブはrunメソッドを持ちシリアライズ可能であるなら何でも投入できます。
    
    require_once 'LightQueue.php';
    require_once 'LightQueue/Manager.php';
    require_once 'LightQueue/SampleTask/HelloTask.php';
    
    LightQueue_Manager::setup('mysql:host=127.0.0.1;dbname=lightqueue', 'root', 'root');
    $lighQueue = new LightQueue();
    $task = new LightQueue_SampleTask_HelloTask();
    $lighQueue->addTask($task, 'sample');

その後はデーモンがジョブを呼び出して、runメソッドを実行してくれます。

## 備考
CentOS5,PHP5,MySQL5.xでのみ動作検証済みです。

ドキュメントは作成中です。

## 今後
ジョブを並列処理させたい。

ドキュメントちゃんと書く。

DBのトランザクションとかちゃんと設計する。

