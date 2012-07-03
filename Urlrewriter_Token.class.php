<?php
require_once(realpath(dirname(__FILE__)) . "/Urlrewriter_TokenInterface.class.php");
abstract class Urlrewriter_Token extends Cnx implements Urlrewriter_TokenInterface{

    /**
     * @static
     * @return bool
     */
    public static function isInRequired() {
        return false;
    }


    /**
     * Retourne le parent d'un objet, en faisant attention aux parents à ignorer
     * @param $type : rubrique ou dossier
     * @param $objectId : ID objet
     * @return bool|int
     */
    protected function _getObjectParent($type, $objectId) {
        $urlRewriterOptions =  unserialize(Variable::lire('urlrewriter_params'));
        $exclude = (array) $urlRewriterOptions['exclude'][$type];

        $table = Rubrique::TABLE;
        if($type == 'dossier') $table = Dossier::TABLE;
        if($type == 'contenu') {
            $exclude = (array) $urlRewriterOptions['exclude']['dossier'];
            $reqParent = $this->query('SELECT dossier AS parent FROM ' . Contenu::TABLE . ' WHERE id=' . $objectId);
            $rowParent = mysql_fetch_assoc($reqParent);
            if(empty($exclude) || $rowParent == 0) return $rowParent['parent'];
            if(is_array($exclude) && in_array(strval($rowParent['parent']), $exclude)) {
                return $this->_getObjectParent('dossier', $rowParent['parent']);
            } elseif($rowParent['parent'] == intval($exclude)) return $this->_getObjectParent('dossier', $rowParent['parent']);
            else return $rowParent['parent'];
        }
        if($type == 'produit') {
            $exclude = (array) $urlRewriterOptions['exclude']['rubrique'];
            $reqParent = $this->query('SELECT rubrique AS parent FROM ' . Produit::TABLE . ' WHERE id=' . $objectId);
            $rowParent = mysql_fetch_assoc($reqParent);
            if(empty($exclude) || $rowParent == 0) return $rowParent['parent'];
            if(is_array($exclude) && in_array(strval($rowParent['parent']), $exclude)) {
                return $this->_getObjectParent('rubrique', $rowParent['parent']);
            } elseif($rowParent['parent'] == intval($exclude)) return $this->_getObjectParent('rubrique', $rowParent['parent']);
            else return $rowParent['parent'];
        }

        $result = false;
        while($result === false) {
            $reqParent = $this->query('
                SELECT parent
                FROM ' . $table . '
                WHERE id=' . $objectId, $this->link);
            $rowParent = mysql_fetch_assoc($reqParent);
            if(empty($exclude) || $rowParent == 0) {
                $result = $rowParent['parent'];
                break;
            }
            if(is_array($exclude)) {
                if(in_array(strval($rowParent['parent']), $exclude)) {
                    $objectId = $rowParent['parent'];
                    continue;
                }
            } elseif($rowParent['parent'] == intval($exclude)) {
                $objectId = $rowParent['parent'];
                continue;
            }
            $result = $rowParent['parent'];
        }
        return $result;
    }
}