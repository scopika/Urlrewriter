<?php
interface Urlrewriter_TokenInterface {
    const OBJECT = null; // Objet sur lequel le token peut s'appliquer

    /**
     * @static
     * @abstract
     * Retourne le token (ex : 'titre' pour %titre%)
     */
    public static function getToken();

    /**
     * @static
     * @abstract
     * Retourne le nom de l'objet sur lequel s'applique le token : rubrique, produit, ...
     */
    public static function getApplyOn();

    /**
     * @abstract
     * Retourne la description du token
     */
    public static function getDescription();

    /**
     * Le token fait-il parti de la liste des tokens requis ?
     * @return bool
     */
    public static function isInRequired();

    /**
     * Calcule la valeur du token
     * @abstract
     * @param $params
     */
    public function calculate($params);
}