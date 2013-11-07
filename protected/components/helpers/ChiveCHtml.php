<?php

class ChiveCHtml extends CHtml {

    public static $idPrefix='';

    public static function generateRandomIdPrefix()
    {
        self::$idPrefix = 'r' . substr(md5(microtime()), 0, 3);
    }
}