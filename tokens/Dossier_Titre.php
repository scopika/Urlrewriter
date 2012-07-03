<?php
include_once(realpath(dirname(__FILE__)) . "/../../../../classes/Dossierdesc.class.php");
require_once(realpath(dirname(__FILE__)) . "/../Urlrewriter_Token.class.php");
class Urlrewriter_Token_Dossier_Titre extends Urlrewriter_Token {

    public static function getToken() { return 'titre'; }

    public static function getApplyOn() { return 'dossier'; }

    public static function isInRequired() { return true; }

    public static function getDescription() { return 'Titre du dossier'; }

    public function calculate($params) {
        $req = $this->query('
            SELECT titre FROM ' . Dossierdesc::TABLE . '
            WHERE dossier=' . $params['dossier'] . ' AND lang=' . $params['lang']);
        $row = mysql_fetch_assoc($req);
        if(empty($row['titre'])) return '';
        return $row['titre'];
    }
}
