<?php
class LightQueue_SampleTask_HelloTask
{
    const OUTPUT_FILE = '/var/tmp/hello_lightqueue.txt';
    
    public function run()
    {
      file_put_contents(self::OUTPUT_FILE, "Hello!\n", FILE_APPEND);
    }
    
}