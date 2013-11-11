<?php

require_once APPLICATION_PATH . '/models/Db.php';

class Model_Likes extends Model_Db {
	
	protected $_name = 'likes';
	protected $_primary = 'id';
	protected $_order = 'id';
	
    public function userLikeReview($user_id, $review_id){

        $sql = $this->select()->where("id_user = ".(int) $user_id." AND id_review = ".(int)$review_id);
       	$r = $this->fetchAll($sql)->toArray();
        return $r;
    }

}
