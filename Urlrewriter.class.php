<?php
include_once(realpath(dirname(__FILE__)) . "/../../../classes/actions/ActionsModules.class.php");
include_once(realpath(dirname(__FILE__)) . "/../../../classes/PluginsClassiques.class.php");
include_once(realpath(dirname(__FILE__)) . "/../../../classes/Reecriture.class.php");
include_once(realpath(dirname(__FILE__)) . "/../../../classes/Contenu.class.php");
include_once(realpath(dirname(__FILE__)) . "/../../../classes/Contenudesc.class.php");
include_once(realpath(dirname(__FILE__)) . "/../../../classes/Dossier.class.php");
include_once(realpath(dirname(__FILE__)) . "/../../../classes/Dossierdesc.class.php");
include_once(realpath(dirname(__FILE__)) . "/../../../classes/Produit.class.php");
include_once(realpath(dirname(__FILE__)) . "/../../../classes/Produitdesc.class.php");
include_once(realpath(dirname(__FILE__)) . "/../../../classes/Rubrique.class.php");
include_once(realpath(dirname(__FILE__)) . "/../../../classes/Rubriquedesc.class.php");
include_once(realpath(dirname(__FILE__)) . "/../../../classes/Lang.class.php");

class Urlrewriter extends PluginsClassiques {

	private $_tokens = null; // Liste des tokens disponibles pour les règles de rewriting
	public $error = null;// Erreur

	/**
	 * @see PluginsClassiques::init()
	 */
	function init() {
		$variable = new Variable();
        $variable->charger('urlrewriter_params');
        if(empty($variable->valeur)) {
            $variable->nom = 'urlrewriter_params';
            $variable->valeur = serialize(array('rules' => array()));
            $variable->protege = 1;
            $variable->cache = 1;
            $variable->add();
        }
	}

    /**
     * @see PluginsClassiques::destroy()
     */
    function destroy() {
        $variable = new Variable('urlrewriter_params');
        $variable->delete();
    }

    /**
     * Pipeline ajoutcont() : enregistrement d'un contenu
     * @see PluginsClassiques::ajoutcont()
     * @param Contenu $contenu
     */
    function ajoutcont($contenu) {
        $this->modcont($contenu);
    }

	/**
	 * Pipeline modcont() : modification d'un contenu
     * @see PluginsClassiques::modcont()
	 * @param Contenu $contenu
	 */
	function modcont($contenu) {
        global $lang;
        if(!$lang) $lang=1;
        $newUrl = $this->getRewritedUrl('contenu', $contenu->id, $lang, true, false);
        $this->updateObjectUrl('contenu', $contenu->id, $newUrl, $lang);
        unset($newUrl);
	}

    /**
     * Pipeline ajoutdos() : création d'un dossier
     * @see PluginsClassiques::ajoutdos()
     * @param Dossier $dossier
     */
    function ajoutdos($dossier) {
        $this->moddos($dossier);
    }

	/**
	 * Pipeline moddos() : modification d'un dossier
     * @see PluginsClassiques::moddos()
	 * @param Dossier $dossier
	 */
	function moddos($dossier) {
        global $lang;
        if(!$lang) $lang=1;
        $newUrl = $this->getRewritedUrl('dossier', $dossier->id, $lang, true, false);
        $this->updateObjectUrl('dossier', $dossier->id, $newUrl, $lang);
        unset($newUrl);
	}

    /**
     * Pipeline ajoutprod() : Création d'un produit
     * @see PluginsClassiques::ajoutprod()
     * @param Produit $produit
     */
    function ajoutprod($produit) {
        $this->modprod($produit);
    }

	/**
	 * Pipeline modprod() : modification d'un produit
     * @see PluginsClassiques::modprod()
	 * @param Produit $produit
	 */
	function modprod($produit) {
        global $lang;
		if(!$lang) $lang=1;
        $newUrl = $this->getRewritedUrl('produit', $produit->id, $lang, true, false);
        $this->updateObjectUrl('produit', $produit->id, $newUrl, $lang);
        unset($newUrl);
	}

    /**
     * Pipeline ajoutrub() : Création d'une rubrique
     * @see PluginsClassiques::ajoutrub()
     * @param Rubrique $rubrique
     */
    function ajoutrub($rubrique) {
        $this->modrub($rubrique);
    }

	/**
	 * Pipeline modrub() : modification d'une rubrique
     * @see PluginsClassiques::modrub()
	 * @param Rubrique $rubrique
	 */
	function modrub($rubrique) {
        global $lang;
		if(!$lang) $lang=1;
        $newUrl = $this->getRewritedUrl('rubrique', $rubrique->id, $lang, true, false);
        $this->updateObjectUrl('rubrique', $rubrique->id, $newUrl, $lang);
        unset($newUrl);
	}

/***************************
 * FIN DES PIPELINES THELIA
 **************************/

    /**
     * Retourne l'URL d'un ID d'objet
     * @param $type : rubrique, produit, ...
     * @param $objectId : ID objet
     * @param int $lang : ID langue
     * @return string
     */
    public function getRewritedUrl($type, $objectId, $lang=1, $recalculate = false, $absolute=true) {
        $url = '';
        if($absolute === true) {
            $langObj = new Lang($lang);
            $url .= empty($langObj->url) ? Variable::lire('urlsite') : $langObj->url;
            if(substr($url, -1) != '/') $url.= '/';
        }

        if(!$recalculate) {
            $param = $this->_getReecritureParams($type, $objectId, $lang);
            $req = $this->query('
                SELECT url FROM ' . Reecriture::TABLE . '
                WHERE fond="' . $type .'"
                AND param="' . $param . '"
                AND actif=1
                ORDER BY id DESC
                LIMIT 1'
            );
            while($row = mysql_fetch_array($req)) $url .= $row[0];
            unset($param, $req, $row, $langObj, $recalculate, $param);
            return $url;
        }

        $context = array('lang' => $lang);
        switch($type) {
            case 'rubrique' :
                $context['rubrique'] = $objectId;
                break;
            case 'produit' :
                $context['produit'] = $objectId;
                // On doit aussi récupérer l'ID rubrique
                $req = $this->query('SELECT rubrique FROM ' . Produit::TABLE . ' WHERE id=' . $objectId . ' LIMIT 1');
                while($row = mysql_fetch_assoc($req)) $context['rubrique'] = intval($row['rubrique']);
                break;
            case 'dossier' :
                $context['dossier'] = $objectId;
                break;
            case 'contenu' :
                $context['contenu'] = $objectId;
                // On doit aussi récupérer l'ID rubrique
                $req = $this->query('SELECT dossier FROM ' . Contenu::TABLE . ' WHERE id=' . $objectId . ' LIMIT 1');
                while($row = mysql_fetch_assoc($req)) $context['dossier'] = intval($row['dossier']);
                break;
        }
        unset($req, $row);

        $pluginParams = unserialize(Variable::lire('urlrewriter_params'));
        return $url . $this->_calculateRewritedUrl(
            $pluginParams['rules'][$type],
            $type,
            $context
        );
    }

    /**
     * Retourne l'URL classique (sans URL-rewriting)
     * @param $type : rubrique, produit, ...
     * @param $objectId : ID de l'objet
     * @param int $lang : ID langue
     * @param bool $absolute : URL absolue (true) ou relative (false)
     * @return string
     */
    public function getBasicUrl($type, $objectId, $lang=1, $absolute=true) {
        $url = $this->callUserFunction('urlrewriter_getUnrewritedUrl', $type, $objectId, $lang);
        if(!is_null($url)) return $url;

        $url = '';
        if($absolute === true) {
            $langObj = new Lang($lang);
            $url .= empty($langObj->url) ? Variable::lire('urlsite') : $langObj->url;
            if(substr($url, -1) != '/') $url.= '/';
        }
        $url .= 'index.php';
        switch($type) {
            case 'produit' :
                $url .= '?fond=produit&id_produit=' . $objectId;
                // On doit aussi récupérer l'ID rubrique
                $req = $this->query('SELECT rubrique FROM ' . Produit::TABLE . ' WHERE id=' . $objectId . ' LIMIT 1');
                while($row = mysql_fetch_assoc($req)) $url.= '&id_rubrique=' . $row['rubrique'];
                break;
            case 'rubrique' :
                $url .= '?fond=rubrique&id_rubrique=' . $objectId;
                break;
            case 'dossier' :
                $url .= '?fond=dossier&id_dossier=' . $objectId;
                break;
            case 'contenu' :
                $url .= '?fond=contenu&id_contenu=' . $objectId;
                // On doit aussi récupérer l'ID rubrique
                $req = $this->query('SELECT dossier FROM ' . Contenu::TABLE . ' WHERE id=' . $objectId . ' LIMIT 1');
                while($row = mysql_fetch_assoc($req)) $url.= '&id_dossier=' . $row['dossier'];
                break;
        }
        unset($req, $row, $langObj);
        return $url;
    }

    /**
     * Enregistrement de l'URL rewritée pour un ID d'objet
     * - pas de doublons avec d'autres objets
     * - on désactive les autres URL liées au même ID d'objet (redir 301 gérées par Thelia)
     * @param $type : rubrique, produit, ...
     * @param $objectId : ID d'objet
     * @param $objectUrl : l'URL à enregistrer
     * @param int $lang : ID langue
     * @return string : l'URL enregistrée
     */
    public function updateObjectUrl($type, $objectId, $objectUrl, $lang=1) {
        // Champs 'param' de la table 'Reecriture'
        $rewritingparamField = $this->_getReecritureParams($type, $objectId, $lang);

        // Produit ou contenu :  a t-il été changé de rubrique ?
        // Si c'est le cas on doit mettre à jour le champs 'param' des URLs enregistrées
        if(isset($_POST['urlrewriter_object_originalParent'])){
            switch($type) {
                case 'produit' :
                    // récupération de l'ID rubrique
                    $req = $this->query('SELECT rubrique FROM ' . Produit::TABLE . ' WHERE id=' . $objectId);
                    while($row = mysql_fetch_assoc($req)) {
                        if($row['rubrique'] != $_POST['urlrewriter_object_originalParent']) {
                            $this->query('
                            UPDATE ' . Reecriture::TABLE . '
                            SET param="' . $rewritingparamField . '"
                            WHERE fond="produit" AND param LIKE "&id_produit=' . $objectId . '&id_rubrique=%"');
                        }
                    }
                    break;
                case 'contenu' :
                    // récupération de l'ID dossier
                    $req = $this->query('SELECT dossier FROM ' . Contenu::TABLE . ' WHERE id=' . $objectId);
                    while($row = mysql_fetch_assoc($req)) {
                        if($row['dossier'] != $_POST['urlrewriter_object_originalParent']) {
                            $this->query('
                                UPDATE ' . Reecriture::TABLE . '
                                SET param="' . $rewritingparamField . '"
                                WHERE fond="contenu" AND param LIKE "&id_contenu=' . $objectId . '&id_dossier=%"');
                        }
                    }
                    break;
            }
        }

        // l'URL est-elle déjà prise par un autre objet?
        // Si c'est le cas on lui ajoute un suffixe numérique
        $originalUrl = $objectUrl;
        $urlAlreadyExists = true;
        $iterator = 1;
        while($urlAlreadyExists) {
            $req = $this->query('
                SELECT url FROM ' . Reecriture::TABLE . '
                WHERE url="' . $objectUrl . '"
                AND param!="' . $rewritingparamField . '"
                AND actif=1');
            if(mysql_num_rows($req) == 0) {
                $urlAlreadyExists = false;
                break;
            }
            $iterator++;
            $objectUrl = $originalUrl;
            $trailingSlash = (substr($originalUrl, -1) == '/') ? true : false;
            if($trailingSlash) $objectUrl = substr($objectUrl,0 , -1);
            $objectUrl.= '-' . $iterator;
            if($trailingSlash) $objectUrl .= '/';
        }

        $rewritingsToDisable = array(); // liste des ID à désactiver
        $newUrlMustBeInserted = true; // enregistrer la nouvelle URL ?

        // On dresse la liste des URL liées à cet ID d'objet, pour connaitre
        // - si l'URL est déjà enregistrée, et activée
        // - quelles URL désactiver
        $req = $this->query('
            SELECT id, url, actif FROM ' . Reecriture::TABLE . '
            WHERE fond="' . $type .'"
            AND param="' . $rewritingparamField . '"
            AND lang=' . $lang
        );
        while($row = mysql_fetch_assoc($req)) {
            if($row['url'] == $objectUrl) {
                $newUrlMustBeInserted = false;
                if(intval($row['actif']) != 1)
                    $this->query('UPDATE ' . Reecriture::TABLE . ' SET actif=1 WHERE id=' . intval($row['id']));
                continue;
            }
            if(intval($row['actif']) != 0) $rewritingsToDisable[] = $row['id'];
        }

        // Enregistrer la nouvelle URL ?
        if($newUrlMustBeInserted) {
            $this->query('INSERT INTO ' . Reecriture::TABLE . '(`url`, `fond`, `param`, `lang`, `actif`) VALUES(
                "' . mysql_real_escape_string($objectUrl) . '",
                "' . $type . '",
                "' . $rewritingparamField . '",
                ' . $lang . ',
                1
            )');
        }

        // URL à désactiver
        foreach((array) $rewritingsToDisable as $idToDisable) {
            $this->query('UPDATE ' . Reecriture::TABLE . ' SET actif=0 WHERE id=' . $idToDisable);
        }

        unset($req, $row, $rewritingparamField, $newUrlMustBeInserted, $rewritingsToDisable, $originalUrl, $iterator, $trailingSlash);
        return $objectUrl;
    }

    /**
     * Retourne la liste des tokens disponibles
     * @param null $type : rubrique, produit, ...
     * @param bool $force : forcer le parcourt des fichiers
     * @return array
     */
	public function getTokens($type=null, $force=false) {
		if(!empty($this->_tokens) && !$force) {
            if(empty($type)) return (array) $this->_tokens;
            return (array) $this->_tokens[$type];
        }

        // On parcourt le dossier /tokens à la recherche des classes
        $dir = new DirectoryIterator(dirname(__FILE__) . '/tokens/');
        foreach ($dir as $fileinfo) {
            if($fileinfo->isDot()) continue;
            if($fileinfo->isDir()) continue;
            try {
                include $fileinfo->getPathname();
                $tokenClassName = 'Urlrewriter_Token_' . $fileinfo->getBasename('.php');
                $token = new $tokenClassName();
                if(!is_a($token, 'Urlrewriter_Token')) continue;
                $this->_tokens[strtolower($tokenClassName::getApplyOn())][] = $token;
            } catch(Exception $e) {}
        }

        unset($dir, $fileinfo, $tokenClassName, $token);
        if(empty($type)) return (array) $this->_tokens;
        return (array) $this->_tokens[$type];
	}

    /**
     * Retourne la liste des langues du site
     * @return array
     */
    public function getLanguages() {
        $langues = CacheBase::getCache()->mysql_query('SELECT id FROM ' . Lang::TABLE, $this->link); // la liste des langues du site
        return (array) $langues;
    }

    /**
     * Vérifie si la règle de rewriting d'un objet est conforme :
     * - au moins un token est trouvé
     * - au moins un token requis est trouvé
     * @param $type : rubrique, produit, ...
     * @param $rule : la règle de réécriture, contenant les tokens
     * @return bool
     */
    public function verifyObjectRule($type, $rule) {
        preg_match_all("`\%([a-z_^\%]{1,})\%`", $rule, $ruleTokens);
        $objectTokens = $this->getTokens($type);
        $tokensFound = array();
        $tokensRequiredFound = array();

        foreach($ruleTokens[1] as $ruleToken) {
            foreach($objectTokens as $objectToken) {
                if($ruleToken == $objectToken::getToken()) {
                    $tokensFound[] = $ruleToken;
                    if($objectToken::isInRequired()) $tokensRequiredFound[] = $ruleToken;
                }
            }
        }

        unset($ruleTokens, $ruleToken, $objectTokens, $objectToken);
        if(empty($tokensFound)) {
            $this->error = 'Vous devez spécifier un marqueur pour les objets de type ' . $type;
            unset($tokensFound, $tokensRequiredFound);
            return false;
        }
        if(empty($tokensRequiredFound)) {
            $this->error = 'Vous devez spécifier un marqueur unique pour les objets de type ' . $type;
            unset($tokensFound, $tokensRequiredFound);
            return false;
        }
        unset($tokensFound, $tokensRequiredFound, $objectTokens, $ruleTokens, $ruleToken);
        return true;
    }

    /**
     * Appelle si possible une fonction de personnalisation dans ./functions.php
     * @param $function
     * @return mixed|null
     */
    public function callUserFunction($function) {
        $functionFile = realpath(dirname(__FILE__)) . '/functions.php';
        if(!file_exists($functionFile)) return null;
        include_once $functionFile;
        if(!function_exists($function)) return null;
        unset($functionFile);
        return call_user_func_array($function, array_shift(func_get_args()));
    }

    /**
     * retourne la liste des tuples en fonction du type d'objet demandé.
     * @param $type : rubrique, produit, ...
     * @return array
     */
    public function getObjects($type) {
        $objects = $this->callUserFunction('urlrewriter_getObjects_' . ucfirst($type));
        if(!empty($objects) && !is_null($objects)) return $objects;

        $objects = array();
        switch($type) {
            case 'produit' :
                $req = $this->query('SELECT id, rubrique FROM ' . Produit::TABLE . ' ORDER BY rubrique ASC');
                while($row = mysql_fetch_assoc($req)) $objects[] = $row;
                break;
            case 'rubrique' :
                // On doit récupérer les rubriques en respectant la hiérarchie car
                // le rewriting de certaines rubriques peut être construit à partir de son parent
                $objects = $this->_getObjectsByArbo('rubrique');
                break;
            case 'dossier' :
                // On doit récupérer les rubriques en respectant la hiérarchie car
                // le rewriting de certaines rubriques peut être construit à partir de son parent
                $objects = $this->_getObjectsByArbo('dossier');
                break;
            case 'contenu' :
                $req = $this->query('SELECT id , dossier FROM ' . Contenu::TABLE);
                while($row = mysql_fetch_assoc($req)) $objects[] = $row;
                break;
        }
        unset($req, $row);
        return $objects;
    }

    /**
     * Tranforme une chaine textuelle en URL.
     * @param $url
     * @return mixed|null|string
     */
    public function sanitizeUrl($url) {
        $url = strtolower(trim($url));
        $url = html_entity_decode($url);

        //replace common characters
        $search = array('&', '£', '$');
        $replace = array('-', 'pounds', 'dollars');
        $url= str_replace($search, $replace, $url);

        //replace accent characters, forien languages
        $search = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ');
        $replace = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o');
        $url = str_replace($search, $replace, $url);

        $url = str_replace('l\'', '', $url);

        // remove - for spaces and union characters
        $find = array(' ', '&', '\r\n', '\n', '+', ',');
        $url = str_replace($find, '-', $url);

        //delete and replace rest of special chars
        $find = array('/[^a-z0-9\-\/<>]/', '/[\-]+/', '/<[^>]*>/');
        $replace = array('', '-', '');
        $url = preg_replace($find, $replace, $url);

        $url = str_replace('//', '/', $url); // delete double slash
        $url = str_replace('-/', '/', $url); // caractères inutiles avant les slash
        if(substr($url,0,1) == '/') $url = substr($url, 1); // on vire le heading slash
        if(substr($url,-1) == '-') $url = substr($url, 0, -1); // Si l'url se finit par un truc bizarre, on vire

        unset($search, $replace, $find);

        $customFilter = $this->callUserFunction('urlrewriter_santitizeUrl', $url);
        if(!is_null($customFilter)) $url = $customFilter;

        unset($customFilter, $search, $replace, $find);
        return $url;
    }

/*********
 * Méthodes privées
 */

    /**
     * Liste les tokens présents dans une règle de réécriture
     * @param $rule : la règle de réécriture
     * @param $type : type d'objet : rubrique, produit, ..
     * @return array
     */
    private function _getTokensFromRule($rule, $type) {
        $tokens = array();
        preg_match_all("`\%([a-z_^\%]{1,})\%`", $rule, $ruleTokens);
        $objectTokens = $this->getTokens($type);
        foreach($ruleTokens[1] as $ruleToken) {
            foreach($objectTokens as $objectToken) {
                if($ruleToken == $objectToken::getToken()) {
                    $tokens[] = $objectToken;
                }
            }
        }
        unset($objectTokens, $objectToken, $ruleToken);
        return $tokens;
    }

    /**
     * Méthode interne de calcul d'une URL
     * @param $rule : la règle de réécriture
     * @param $type : rubrique, produit, ...
     * @param  array $context
     * @return array|bool
     */
    private function _calculateRewritedUrl($rule, $type, $context) {
        $tokens = $this->_getTokensFromRule($rule, $type);
        $search = array();
        $replacements = array();
        foreach($tokens as $token) {
            $calculatedToken = $token->calculate($context);
            $search[] = '%' . $token::getToken() . '%';
            $replacements[] = trim($calculatedToken, ' ');
        }
        $urlRewrited = str_replace($search, $replacements, $rule);
        unset($tokens, $search, $replacements, $token, $calculatedToken);
        return $this->sanitizeUrl($urlRewrited);
    }

    /**
     * Retourne l'URL 'normale' pour un objet
     * @param string $type : rubrique, produit, ...
     * @param array $context
     * @return mixed|null|string
     */
    private function _getUnrewritedUrl($type, $context) {
        $url = $this->callUserFunction('urlrewriter_getUnrewritedUrl', $type, $context);
        if(!is_null($url)) return $url;

        $url = null;
        switch($type) {
            case 'produit' :
                $url = '&id_produit=' . $context['produit'] . '&id_rubrique=' . $context['rubrique'];
                break;
            case 'rubrique' :
                $url = '&id_rubrique=' . $context['rubrique'];
                break;
            case 'dossier' :
                $url = '&id_dossier=' . $context['dossier'];
                break;
            case 'contenu' :
                $url = '&id_contenu=' . $context['contenu'] . '&id_dossier=' . $context['dossier'];
                break;
        }
        return $url;
    }

    /**
     * Retourne le champs `param` paramètres d'URL de la table `Reecriture` pour un ID d'objet.
     * @param $type : rubrique, produit, ...
     * @param $objectId : ID objet
     * @param int $lang : ID langue
     * @return string
     */
    private function _getReecritureParams($type, $objectId, $lang=1) {
        $rewritingparamField = '';
        // Champs 'param' de la table 'Reecriture'
        switch($type) {
            case 'rubrique' :
                $rewritingparamField = '&id_rubrique=' . $objectId;
                break;
            case 'produit' :
                $rewritingparamField = '&id_produit=' . $objectId;
                // on doit ajouter l'ID de la rubrique
                $req = $this->query('SELECT rubrique FROM ' . Produit::TABLE . ' WHERE id=' . $objectId . ' LIMIT 1');
                while($row = mysql_fetch_array($req))$rewritingparamField .= '&id_rubrique=' . $row[0];
                break;
            case 'dossier' :
                $rewritingparamField = '&id_dossier=' . $objectId;
                break;
            case 'contenu' :
                $rewritingparamField = '&id_contenu=' . $objectId;
                // On doit aussi récupérer l'ID rubrique
                $req = $this->query('SELECT dossier FROM ' . Contenu::TABLE . ' WHERE id=' . $objectId . ' LIMIT 1');
                while($row = mysql_fetch_array($req)) $rewritingparamField .= '&id_dossier=' . $row[0];
                break;
        }
        unset($req, $row);
        return $rewritingparamField;
    }

    /**
     * Retourne une liste plate d'objets en respectant l'ordre de l'arborescence
     * @param $type
     * @param int $parentId
     * @return array
     */
    private function _getObjectsByArbo($type, $parentId=0) {
        $results = array();
        $table = Rubrique::TABLE;
        if($type == dossier) $table = Dossier::TABLE;
        $req = $this->query('SELECT id FROM ' . $table . ' WHERE parent=' . intval($parentId));
        while($row = mysql_fetch_assoc($req)) {
            if(empty($row['id'])) continue;
            $results[] = $row;
            $children = $this->_getObjectsByArbo($type, $row['id']);
            if(!empty($children)) $results = array_merge($results, $children);
        }
        unset($table, $type, $req, $row, $children);
        return $results;
    }
}