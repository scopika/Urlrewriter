<?php
include_once(realpath(dirname(__FILE__)) . "/../../../../classes/Contenudesc.class.php");
require_once(realpath(dirname(__FILE__)) . "/../Urlrewriter_Token.class.php");
class Urlrewriter_Token_Contenu_Titre extends Urlrewriter_Token {

    public static function getToken() { return 'titre'; }

    public static function getApplyOn() { return 'contenu'; }

    public static function isInRequired() { return true; }

    public static function getDescription() { return 'Titre du contenu'; }

    public function calculate($params) {
        $req = $this->query('
            SELECT titre FROM ' . Contenudesc::TABLE . '
            WHERE contenu=' . $params['contenu'] . ' AND lang=' . $params['lang']);
        $row = mysql_fetch_assoc($req);
        if(empty($row)) return '';
        return $row['titre'];
    }
}
