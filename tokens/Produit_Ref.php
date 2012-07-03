<?php
include_once(realpath(dirname(__FILE__)) . "/../../../../classes/Produit.class.php");
require_once(realpath(dirname(__FILE__)) . "/../Urlrewriter_Token.class.php");
class Urlrewriter_Token_Produit_Ref extends Urlrewriter_Token {

    public static function getToken() { return 'ref'; }

    public static function getApplyOn() { return 'produit'; }

    public static function isInRequired() { return true; }

    public static function getDescription() { return 'Référence du produit'; }

    public function calculate($params) {
        $req = $this->query('
            SELECT ref FROM ' . Produit::TABLE . '
            WHERE id=' . $params['produit']);
        $row = mysql_fetch_assoc($req);
        if(empty($row['ref'])) return '';
        return $row['ref'];
    }
}
