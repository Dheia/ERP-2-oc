<?php namespace Placecompany\Board\Classes;

use Carbon\Carbon;
use Placecompany\Board\Models\BoardMedia;

/**
 * KBoard 게시글 미디어
 * @link www.cosmosfarm.com
 * @copyright Copyright 2019 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class BoardContentMediaManager {

    private $abspath;

    var $board_id;
    var $content_id;
    var $media_group;

    public function __construct(){
        $this->abspath = rtrim(base_path());
    }

    /**
     * 미디어 리스트를 반환한다.
     */
    public function getList(){
        $media_list = array();

        $this->board_id = intval($this->board_id);
        $this->content_id = intval($this->content_id);
        $this->media_group = e($this->media_group);

        if($this->content_id && $this->media_group){
            $media_list = \DB::table('placecompany_board_media')
                ->leftJoin('placecompany_board_media_relationships', 'placecompany_board_media.id', '=', 'placecompany_board_media_relationships.media_id')
                ->where('placecompany_board_media_relationships.content_id', '=', $this->content_id)
                ->orWhere('placecompany_board_media.media_group', '=', $this->media_group)
                ->orderBy('placecompany_board_media.id', 'desc')->get();
        }
        else if($this->content_id){
            $media_list = \DB::table('placecompany_board_media')
                ->leftJoin('placecompany_board_media_relationships', 'placecompany_board_media.id', '=', 'placecompany_board_media_relationships.media_id')
                ->where('placecompany_board_media_relationships.content_id', '=', $this->content_id)
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
            $media->metadata = ($media->metadata ? json_decode($media->metadata, true) : array());
            $media_list[$key] = $media;
        }
        \Event::fire('placecompany.board.board_content_media_list', [&$media_list, $this]);

        return $media_list;
    }

    /**
     * 미디어 파일을 업로드한다.
     */
    public function upload(){
        $this->board_id = intval($this->board_id);
        $this->content_id = intval($this->content_id);
        $this->media_group = e($this->media_group);

        if($this->board_id && $this->media_group){
            $upload_dir = storage_path();
            $attach_store_path = str_replace($this->abspath, '', $upload_dir) . "/board_attached/{$this->board_id}/" . date('Ym', Carbon::now()->timestamp) . '/';

            $file = new BoardFileHandler();
            $file->setPath($attach_store_path);

            $upload_results = $file->upload('board_media_file');

            if(!is_array($upload_results)){
                $upload_results = array($upload_results);
            }

            foreach($upload_results as $upload){
                $file_name = e($upload['original_name']);
                $file_path = e($upload['path'] . $upload['stored_name']);
                $file_size = intval(filesize($this->abspath . $upload['path'] . $upload['stored_name']));

                $attach_file = new \stdClass();
                $attach_file->key = '';
                $attach_file->path = $file_path;
                $attach_file->name = $file_name;
                $attach_file->metadata = $upload['metadata'];

                \Event::fire('placecompany.board.board_content_media_metadata', [&$upload['metadata'], $attach_file, $this]);
                $metadata = $upload['metadata'];
                $metadata = json_encode($metadata);
                $metadata = e($metadata);

                if($file_name){
                    $board_media = new BoardMedia;
                    $board_media->media_group = $this->media_group;
                    $board_media->file_path = $file_path;
                    $board_media->file_name = $file_name;
                    $board_media->file_size = $file_size;
                    $board_media->download_count = 0;
                    $board_media->metadata = $metadata;
                    $board_media->media_group = $this->media_group;
                    $board_media->save();
                }
            }
        }
    }

    /**
     * 게시글과 미디어의 관계를 입력한다.
     */
    public function createRelationships(){
        $this->board_id = intval($this->board_id);
        $this->content_id = intval($this->content_id);
        $this->media_group = e($this->media_group);

        if($this->content_id && $this->media_group){
            $results = BoardMedia::where('media_group', $this->media_group)->get();
            foreach($results as $row){
                \DB::table('placecompany_board_media_relationships')->insert([
                    'content_id' => $this->content_id,
                    'comment_id' => '0',
                    'media_id' => $row->id
                ]);
            }
        }
    }

    /**
     * 미디어를 삭제한다.
     * @param int $media_id
     */
    public function deleteWithMediaID($media_id){
        $media_id = intval($media_id);
        if($media_id){
            $row = \DB::table('placecompany_board_media')
                ->leftJoin('placecompany_board_media_relationships', 'placecompany_board_media.id', '=', 'placecompany_board_media_relationships.media_id')
                ->where('placecompany_board_media.id', $media_id)->first();
            $this->deleteWithMedia($row);
        }
    }

    /**
     * 미디어를 삭제한다.
     * @param int $content_id
     */
    public function deleteWithContentID($content_id){
        $content_id = intval($content_id);
        if($content_id){
            $results = \DB::table('placecompany_board_media')
                ->leftJoin('placecompany_board_media_relationships', 'placecompany_board_media.id', '=', 'placecompany_board_media_relationships.media_id')
                ->where('placecompany_board_media_relationships.content_id', $content_id)->get();
            foreach($results as $key=>$row){
                $this->deleteWithMedia($row);
            }
        }
    }

    /**
     * 미디어를 삭제한다.
     * @param object $media
     */
    public function deleteWithMedia($media){
        if($media->id){
            Helpers::board_delete_resize($this->abspath . stripslashes($media->file_path));
            @unlink($this->abspath . stripslashes($media->file_path));
            BoardMedia::find($media->id)->delete();
        }
    }

    /**
     * 게시글과의 관계가 없는 미디어는 삭제한다.
     */
    public function truncate(){
        $date = date('YmdHis', Carbon::now()->timestamp - 3600);
        $results = \DB::table('placecompany_board_media')
            ->leftJoin('placecompany_board_media_relationships', 'placecompany_board_media.id', '=', 'placecompany_board_media_relationships.media_id')
            ->where('placecompany_board_media.created_at', '<', $date)
            ->whereRaw("(`placecompany_board_media_relationships`.`content_id` IS NULL 
AND `placecompany_board_media_relationships`.`comment_id` IS NULL)")->get();

        foreach($results as $row){
            $this->deleteWithMedia($row);
        }
    }
}
?>
