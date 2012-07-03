<?php
include_once(realpath(dirname(__FILE__)) . "/../../../../classes/Rubriquedesc.class.php");
require_once(realpath(dirname(__FILE__)) . "/../Urlrewriter_Token.class.php");
class Urlrewriter_Token_Rubrique_Titre extends Urlrewriter_Token {

    public static function getToken() { return 'titre'; }

    public static function getApplyOn() { return 'rubrique'; }

    public static function isInRequired() { return true; }

    public static function getDescription() { return 'Titre de la rubrique'; }

    public function calculate($params) {
        $req = $this->query('
            SELECT titre FROM ' . Rubriquedesc::TABLE . '
            WHERE rubrique=' . $params['rubrique'] . ' AND lang=' . $params['lang']);
        $row = mysql_fetch_assoc($req);
        if(empty($row)) return '';
        return $row['titre'];
    }
}
