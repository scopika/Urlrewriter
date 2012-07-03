<?php
require_once(realpath(dirname(__FILE__)) . "/../Urlrewriter_Token.class.php");
class Urlrewriter_Token_Dossier_Id extends Urlrewriter_Token {

    public static function getToken() { return 'id'; }

    public static function getApplyOn() { return 'dossier'; }

    public static function isInRequired() { return true; }

    public static function getDescription() { return 'ID du dossier'; }

    public function calculate($params) {
        return $params['dossier'];
    }
}
