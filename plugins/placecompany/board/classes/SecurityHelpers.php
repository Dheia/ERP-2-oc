<?php namespace Placecompany\Board\Classes;
use HTMLPurifier;
use HTMLPurifier_Config;
use Placecompany\Board\Models\Settings;

/**
 * KBoard 워드프레스 게시판 보안 함수
 * @link www.cosmosfarm.com
 * @copyright Copyright 2019 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */

$board_xssfilter_active = Settings::get('board_xssfilter') ? false : true;
$board_htmlpurifier_path = storage_path( 'placecompany/board/board_htmlpurifier');
if($board_xssfilter_active) {
    \File::makeDirectory($board_htmlpurifier_path, 0755, true, true);
}
class SecurityHelpers
{

    /**
     * 허용된 도메인의 아이프레임만 남기고 모두 제거한다.
     * @param string $data
     * @return string
     */
    public static function board_safeiframe($data){
        if(is_array($data)) return array_map('board_safeiframe', $data);

        /*
         * 허가된 도메인 호스트 (화이트리스트)
         */
        $whitelist = static::board_iframe_whitelist(true);

        // board_iframe_whitelist 필터
        \Event::fire('placecompany.board.board_iframe_whitelist', [&$whitelist]);

        preg_match_all('/<iframe.+?src="(.+?)".+?[^>]*+>/is', $data, $matches);

        $iframe = $matches[0];
        $domain = $matches[1];

        foreach($domain as $key=>$value){
            $value = 'http://' . preg_replace('/^(http:\/\/|https:\/\/|\/\/)/i', '', $value);
            $url = parse_url($value);
            if(!in_array($url['host'], $whitelist)){
                $data = str_replace($iframe[$key].'</iframe>', '', $data);
                $data = str_replace($iframe[$key], '', $data);
            }
        }

        // 풀스크린 허용
        $data = preg_replace('/<iframe(.*?)>/is', '<iframe$1 allowfullscreen>', $data);

        return $data;
    }

    /**
     * 모든 html을 제거한다.
     * @param object $data
     * @return array || string
     */
    public static function board_htmlclear($data){
        if(is_array($data)) return array_map('board_htmlclear', $data);
        $data = filter_var($data);
        return htmlspecialchars($data, FILTER_SANITIZE_STRING);
    }

    /**
     * 아이프레임 화이트리스트를 반환한다.
     * @param boolean $to_array
     * @return array
     */
    public static function board_iframe_whitelist($to_array=false){
        /*
         * 허가된 도메인 호스트 (화이트리스트)
         */
        $whitelist = 'google.com' . PHP_EOL;
        $whitelist .= 'www.google.com' . PHP_EOL;
        $whitelist .= 'youtube.com' . PHP_EOL;
        $whitelist .= 'www.youtube.com' . PHP_EOL;
        $whitelist .= 'maps.google.com' . PHP_EOL;
        $whitelist .= 'maps.google.co.kr' . PHP_EOL;
        $whitelist .= 'docs.google.com' . PHP_EOL;
        $whitelist .= 'tv.naver.com' . PHP_EOL;
        $whitelist .= 'serviceapi.nmv.naver.com' . PHP_EOL;
        $whitelist .= 'serviceapi.rmcnmv.naver.com' . PHP_EOL;
        $whitelist .= 'videofarm.daum.net' . PHP_EOL;
        $whitelist .= 'tv.kakao.com' . PHP_EOL;
        $whitelist .= 'player.vimeo.com' . PHP_EOL;
        $whitelist .= 'w.soundcloud.com' . PHP_EOL;
        $whitelist .= 'slideshare.net' . PHP_EOL;
        $whitelist .= 'www.slideshare.net' . PHP_EOL;
        $whitelist .= 'channel.pandora.tv' . PHP_EOL;
        $whitelist .= 'mgoon.com' . PHP_EOL;
        $whitelist .= 'www.mgoon.com' . PHP_EOL;
        $whitelist .= 'tudou.com' . PHP_EOL;
        $whitelist .= 'www.tudou.com' . PHP_EOL;
        $whitelist .= 'player.youku.com' . PHP_EOL;
        $whitelist .= 'videomega.tv' . PHP_EOL;
        $whitelist .= 'mtab.clickmon.co.kr' . PHP_EOL;
        $whitelist .= 'tab2.clickmon.co.kr';

        $iframe_whitelist_data = Settings::get('board_iframe_whitelist');
        $iframe_whitelist_data = trim($iframe_whitelist_data);

        if(!$iframe_whitelist_data){
            $iframe_whitelist_data = $whitelist;
        }

        if($to_array){
            $iframe_whitelist_data = explode(PHP_EOL, $iframe_whitelist_data);
            return array_map('trim', $iframe_whitelist_data);
        }
        return $iframe_whitelist_data;
    }


    /**
     * Cross-site scripting (XSS) 공격을 방어하기 위해서 위험한 문자열을 제거한다.
     * @param string $data
     * @return array|string
     */
    public static function board_xssfilter($data){
        global $board_xssfilter_active, $board_htmlpurifier_path;
        if(is_array($data)) return array_map('board_xssfilter', $data);
        if($board_xssfilter_active){
            if(!isset($GLOBALS['KBOARD']) || !isset($GLOBALS['KBOARD']['HTMLPurifier']) || !$GLOBALS['KBOARD']['HTMLPurifier'] || !isset($GLOBALS['KBOARD']['HTMLPurifier_Config']) || !$GLOBALS['KBOARD']['HTMLPurifier_Config']){
                $HTMLPurifier_Config = HTMLPurifier_Config::createDefault();
                $HTMLPurifier_Config->set('URI.AllowedSchemes', array('http'=>true,'https'=>true,'mailto'=>true,'tel'=>true,'data'=>true));
                $HTMLPurifier_Config->set('URI.SafeIframeRegexp', '(.*)');
                $HTMLPurifier_Config->set('HTML.SafeIframe', true);
                $HTMLPurifier_Config->set('HTML.SafeObject', true);
                $HTMLPurifier_Config->set('HTML.SafeEmbed', true);
                $HTMLPurifier_Config->set('HTML.TidyLevel', 'light');
                $HTMLPurifier_Config->set('HTML.FlashAllowFullScreen', true);
                $HTMLPurifier_Config->set('HTML.AllowedElements','img,div,a,strong,font,span,em,del,ins,br,p,u,i,b,sup,sub,small,table,thead,tbody,tfoot,tr,td,th,caption,pre,code,ul,ol,li,big,code,blockquote,center,hr,h1,h2,h3,h4,h5,h6,iframe,dl,dt,dd');
                $HTMLPurifier_Config->set('HTML.AllowedAttributes', 'a.rel,a.href,a.target,img.src,iframe.src,iframe.frameborder,*.id,*.alt,*.style,*.class,*.title,*.width,*.height,*.border,*.colspan,*.rowspan');
                $HTMLPurifier_Config->set('HTML.TargetNoreferrer', false);
                $HTMLPurifier_Config->set('Attr.AllowedFrameTargets', array('_blank'));
                $HTMLPurifier_Config->set('Attr.EnableID', true);
                $HTMLPurifier_Config->set('Attr.AllowedRel', 'nofollow,noopener,noreferrer');
                $HTMLPurifier_Config->set('Output.FlashCompat', true);
                $HTMLPurifier_Config->set('Core.RemoveInvalidImg', true);
                $HTMLPurifier_Config->set('Core.LexerImpl', 'DirectLex');
                $HTMLPurifier_Config->set('Cache.SerializerPath', $board_htmlpurifier_path);
                $GLOBALS['KBOARD']['HTMLPurifier_Config'] = $HTMLPurifier_Config;
                $GLOBALS['KBOARD']['HTMLPurifier'] = HTMLPurifier::getInstance();
                unset($HTMLPurifier_Config);
            }
            $data = $GLOBALS['KBOARD']['HTMLPurifier']->purify($data, $GLOBALS['KBOARD']['HTMLPurifier_Config']);
        }
        return trim($data);
    }
}
