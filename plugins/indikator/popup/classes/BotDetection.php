<?php namespace Indikator\Popup\Classes;

class BotDetection
{
    public static function isRobot()
    {
        if (!isset($_SERVER['HTTP_USER_AGENT'])) {
            return false;
        }

        $bots = [
            'Googlebot',
            'alexa',
            'appie',
            'Ask Jeeves',
            'Baiduspider',
            'Bingbot',
            'crawler',
            'DuckDuckBot',
            'Exabot',
            'facebot',
            'FAST',
            'Feedfetcher-Google',
            'Firefly',
            'froogle',
            'Gigabot',
            'girafabot',
            'InfoSeek',
            'inktomi',
            'looksmart',
            'Mediapartners-Google',
            'msnbot',
            'NationalDirectory',
            'rabaz',
            'Rankivabot',
            'Scooter',
            'Slurp',
            'Sogou',
            'Spade',
            'TechnoratiSnoop',
            'TECNOSEEK',
            'Teoma',
            'WebAlta Crawler',
            'WebBug',
            'WebFindBot',
            'YandexBot',
            'ZyBorg'
        ];

        foreach ($bots as $bot) {
            if (substr_count($_SERVER['HTTP_USER_AGENT'], $bot) > 0) {
                return true;
            }
        }

        return false;
    }
}
