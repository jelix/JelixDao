<?php 

require_once(__DIR__.'/post.php');
abstract class PostTracker extends Post {
    
    function open() {
        $this->status = 'open';
        $this->save();
    }
    
    function close() {
        $this->status = 'closed';
        $this->save();
    }

}
