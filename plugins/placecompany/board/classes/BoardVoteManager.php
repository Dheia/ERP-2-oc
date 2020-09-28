<?php namespace Placecompany\Board\Classes;
use Backend\Facades\BackendAuth;
use Placecompany\Board\Models\BoardVote;

/**
 * KBoard KBVote
 * @link www.cosmosfarm.com
 * @copyright Copyright 2019 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class BoardVoteManager {

    static $TYPE_DOCUMENT = 'document';
    static $TYPE_COMMENT = 'commemt';

    static $VOTE_LIKE = 'like';
    static $VOTE_UNLIKE = 'unlike';

    /**
     * 투표 정보를 입력한다.
     * @param array $args
     * @return string
     */
    public function insert($args){
        $args = $this->filter($args);
        if(!$args) return '';

        if($args['user_id']){
            $data['target_id'] = intval($args['target_id']);
            $data['target_type'] = e($args['target_type']);
            $data['target_vote'] = e($args['target_vote']);
            $data['user_id'] = intval($args['user_id']);
            $data['ip_address'] = '';
        }
        else{
            $data['target_id'] = intval($args['target_id']);
            $data['target_type'] = e($args['target_type']);
            $data['target_vote'] = e($args['target_vote']);
            $data['user_id'] = 0;
            $data['ip_address'] = e($args['ip_address']);
        }

        $vote = BoardVote::create($data);

        return $vote->id;
    }

    /**
     * 투표 정보를 삭제한다.
     * @param array $args
     * @return boolean
     */
    public function delete($args){
        $args = $this->filter($args);
        if(!$args) return false;

        if($args['user_id']){
            $data[] = ['target_id', '=', intval($args['target_id'])];
            $data[] = ['target_type', '=', intval($args['target_type'])];
            $data[] = ['user_id', '=', intval($args['user_id'])];
        }
        else{
            $data[] = ['target_id', '=', intval($args['target_id'])];
            $data[] = ['target_type', '=', e($args['target_type'])];
            $data[] = ['ip_address', '=', e($args['ip_address'])];
        }

        BoardVote::where($data)->delete();
        return true;
    }

    /**
     * 투표 정보가 있는지 확인한다.
     * @param array $args
     * @return boolean|string
     */
    public function isExists($args){
        $args = $this->filter($args);
        if(!$args) return -1;

        if($args['user_id']){
            $data[] = ['target_id', '=', intval($args['target_id'])];
            $data[] = ['target_type', '=', e($args['target_type'])];
            $data[] = ['user_id', '=', intval($args['user_id'])];
        }
        else{
            $data[] = ['target_id', '=', intval($args['target_id'])];
            $data[] = ['target_type', '=', e($args['target_type'])];
            $data[] = ['ip_address', '=', e($args['ip_address'])];
        }

        return BoardVote::where($data)->exists();
    }

    /**
     * 투표 정보의 데이터를 확인한다.
     * @param array $args
     * @return array
     */
    private function filter($args){
        if(!isset($args['target_id']) || !$args['target_id']){
            return array();
        }
        if(!isset($args['target_type']) || !$args['target_type']){
            return array();
        }
        if(!isset($args['target_vote']) || !$args['target_vote']){
            return array();
        }
        if(!isset($args['user_id']) || !$args['user_id']){
            if(BackendAuth::check()){
                $args['user_id'] = BackendAuth::getUser() ? BackendAuth::getUser()->id : '';
                $args['ip_address'] = '';
            }
            else if(!isset($args['ip_address']) || !$args['ip_address']){
                $args['user_id'] = 0;
                $args['ip_address'] = Helpers::board_user_ip();
            }
        }
        if(!isset($args['ip_address'])){
            $args['ip_address'] = '';
        }
        return $args;
    }
}
