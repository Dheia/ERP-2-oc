<?php namespace Placecompany\Board\Classes;
use Backend\Facades\BackendAuth;

/**
 * KBoard Captcha
 * @link www.cosmosfarm.com
 * @copyright Copyright 2019 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class BoardCaptchaManager {

    /**
     * Captcha 이미지를 생성한다.
     */
    public function createImage(){
        if(!isset($_SESSION['board_captcha'])) $_SESSION['board_captcha'] = array();

        $captcha_folder = storage_path( 'placecompany/board/board_captcha');
        $captcha_name = uniqid('captcha_').'.png';

        // 디렉토리 생성
        if($captcha_folder)
        \File::makeDirectory($captcha_folder, 0755, true, true);

        // 1시간이 지난 이미지는 삭제한다.
        $file_handler = new BoardFileHandler();
        $captcha_files = $file_handler->getDirlist($captcha_folder);
        foreach($captcha_files as $file){
            $filetime = @filemtime($captcha_folder . $file);
            $created = (time() - $filetime) / 60 / 60;
            if($created > 1) $file_handler->delete($captcha_folder . $file);
        }

        $text = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
        shuffle($text);
        $text = substr(implode('', $text), 0, 5);

        $image = imagecreate(50, 20);
        $background_color = imagecolorallocate($image, 255, 255, 255);
        $font_color = imagecolorallocate($image, 194, 51, 21);

        imagestring($image, 5, 2, 2, $text, $font_color);
        imageline($image, 0, 0, 50, 20, $font_color);
        @imagepng($image, $captcha_folder . $captcha_name);
        imagedestroy($image);

        if(file_exists($captcha_folder . $captcha_name)){
            \Session::put('board_captcha[]', $text);
            $src = \Storage::url(storage_path('placecompany/board/board_captcha'. $captcha_name));
        }
        else{
            \Session::put('board_captcha[]', 'ERROR');
            $src = plugins_path('placecompany/board/assets/images/captcha-error.png');
        }

        return $src;
    }

    /**
     * Captcha 검증
     * @return boolean
     */
    public function validate(){
        if(BackEndAuth::check()){
            return true;
        }
        if(isset($_POST['g-recaptcha-response'])){
            if($this->recaptcha()){
                return true;
            }
        }
        if(isset($_POST['captcha'])){
            if(in_array(strtoupper($_POST['captcha']), $_SESSION['board_captcha'])){
                unset($_SESSION['board_captcha']);
                return true;
            }
        }
        return false;
    }

    /**
     * 구글 reCAPTCHA 검증
     * @return boolean
     */
    function recaptcha(){
        $siteverify_url = 'https://www.google.com/recaptcha/api/siteverify?'. array(
                'secret'   => Helpers::board_recaptcha_secret_key(),
                'response' => post('g-recaptcha-response') ?: '',
                'remoteip' => Helpers::board_user_ip()
            );

        $response = \File::get($siteverify_url);

        if(empty($response['body']) || !($json = json_decode($response['body'])) || !$json->success){
            return false;
        }
        return true;
    }
}
?>
