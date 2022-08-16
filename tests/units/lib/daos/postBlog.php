<?php 

require_once(__DIR__.'/post.php');
abstract class PostBlog extends Post {
    
    function publish() {
        $this->status = 'published';
        $this->save();
    }
    
    function unpublish() {
        $this->status = NULL;
        $this->save();
    }

}
