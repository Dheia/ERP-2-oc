<?php namespace Placecompany\BoardComment\Classes;

use Placecompany\Board\Classes\BoardContentMediaManager;
use Placecompany\Board\Models\BoardMedia;

/**
 * KBoard 댓글 미디어
 * @link www.cosmosfarm.com
 * @copyright Copyright 2019 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class BoardCommentMediaManager extends BoardContentMediaManager
{
    var $comment_id;

    /**
     * 미디어 리스트를 반환한다.
     */
    public function getList(){
        $media_list = array();

        $this->board_id = intval($this->board_id);
        $this->comment_id = intval($this->comment_id);
        $this->media_group = e($this->media_group);

        if($this->comment_id && $this->media_group){
            $media_list = \DB::table('placecompany_board_media')
                ->leftJoin('placecompany_board_media_relationships', 'placecompany_board_media.id', '=', 'placecompany_board_media_relationships.media_id')
                ->where('placecompany_board_media_relationships.comment_id', '=', $this->comment_id)
                ->orWhere('placecompany_board_media.media_group', '=', $this->media_group)
                ->orderBy('placecompany_board_media.id', 'desc')->get();
        }
        else if($this->comment_id){
            $media_list = \DB::table('placecompany_board_media')
                ->leftJoin('placecompany_board_media_relationships', 'placecompany_board_media.id', '=', 'placecompany_board_media_relationships.media_id')
                ->where('placecompany_board_media_relationships.comment_id', '=', $this->comment_id)
                ->orderBy('placecompany_board_media.id', 'desc')->get();
        }
        else if($this->media_group){
            $media_list = \DB::table('placecompany_board_media')
                ->leftJoin('placecompany_board_media_relationships', 'placecompany_board_media.id', '=', 'placecompany_board_media_relationships.media_id')
                ->where('placecompany_board_media.media_group', '=', $this->media_group)
                ->orderBy('placecompany_board_media.id', 'desc')->get();
        }

        foreach($media_list as $key=>$media){
            $media->file_url = url($media->file_path);
            $media->thumbnail_url = url($media->file_path);
            $media->metadata = ($media->metadata ? unserialize($media->metadata) : array());
            $media_list[$key] = $media;
        }
        \Event::fire('placecompany.board.board_comments_media_list', [&$media_list, $this]);

        return $media_list;
    }

    /**
     * 댓글과 미디어의 관계를 입력한다.
     */
    public function createRelationships(){
        $this->board_id = intval($this->board_id);
        $this->comment_id = intval($this->comment_id);
        $this->media_group = e($this->media_group);

        if($this->comment_id && $this->media_group){
            $results = BoardMedia::where('media_group', $this->media_group)->get();
            foreach($results as $row){
                \DB::table('placecompany_board_media_relationships')->insert([
                    'content_id' => 0,
                    'comment_id' => $this->comment_id,
                    'media_id' => $row->id
                ]);
            }
        }
    }

    /**
     * 미디어를 삭제한다.
     * @param int $comment_id
     */
    public function deleteWithCommentID($comment_id){
        $comment_id = intval($comment_id);
        if($comment_id){
            $row = \DB::table('placecompany_board_media')
                ->leftJoin('placecompany_board_media_relationships', 'placecompany_board_media.id', '=', 'placecompany_board_media_relationships.media_id')
                ->where('placecompany_board_media_relationships.comment_id', $comment_id)->first();
            if($row)
                $this->deleteWithMedia($row);
        }
    }
}
