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
	
	// Liste des tokens disponibles pour les règles de rewriting
	private $_tokens = null;
	// Liste d'objets à ne pas prendre en compte dans le calcul
	// des rewritings.
	private $_excludedObjects = array();
	// Erreur
	public $error = null;

	/**
	 * Génération de la liste des tokens disponibles
	 */
	public function retrieveTokens() {
		$tokensLang = array(
			'id_lang' => array(
				'label' => 'ID de la langue',
				'type' => 'value',
				'data' => '%lang%'
			), 
			'nom_lang' => array(
				'label' => 'Nom de la langue',
				'type' => 'sql',
				'data' => array(
					'select' => 'description',
					'from' => Lang::TABLE,
					'where' => 'id=%lang%'
				)
			));

		$this->_tokens = array(
			'rubrique' => array(
				'titre' => array(
					'label' => 'Titre de la rubrique',
					'in_required' => true,
					'type' => 'sql',
					'data' => array(
						'select' => 'rubriquedesc.titre',
						'from' => array(
							Rubriquedesc::TABLE . ' AS rubriquedesc',
						),
						'where' => array(
							'rubriquedesc.rubrique=%rubrique%',
							'rubriquedesc.lang=%lang%'
						)
					)
				),
				'chemin' => array(
					'label' => 'Chemin de la rubrique',
					'type' => 'value',
					'data' => array()
				)
			),
			'produit' => array(
				'ref' => array(
					'label' => 'Référence du produit',
					'in_required' => true,
					'type' => 'sql',
					'data' => array(
						'select' => 'ref',
						'from' => array(Produit::TABLE),
						'where' => array('id=%produit%')
					)
				),
				'titre' => array(
					'label' => 'Titre du produit',
					'in_required' => true,
					'type' => 'sql',
					'data' => array(
						'select' => 'produitdesc.titre',
						'from' => array(
							Produit::TABLE . ' AS produit',
							Produitdesc::TABLE . ' AS produitdesc',
						),
						'where' => array(
							'produit.id=%produit%',
							'produitdesc.produit=produit.id',
							'produitdesc.lang=%lang%'
						)
					)
				),
				'nom_rubrique' => array(
					'label' => 'Titre de la rubrique',
					'type' => 'sql',
					'data' => array(
						'select' => 'rubriquedesc.titre',
						'from' => array(
							Produit::TABLE . ' AS produit',
							Rubriquedesc::TABLE . ' AS rubriquedesc',
						),
						'where' => array(
							'produit.id=%produit%',
							'rubriquedesc.rubrique=produit.rubrique',
							'rubriquedesc.lang=%lang%'
						)
					)
				),
				'url_rubrique' => array(
					'label' => 'URL de la rubrique',
					'type' => 'value',
					'data' => array()
				),
			),
			'dossier' => array(
				'titre' => array(
					'label' => 'Titre du dossier',
					'in_required' => true,
					'type' => 'sql',
					'data' => array(
						'select' => 'dossierdesc.titre',
						'from' => array(
							Dossierdesc::TABLE . ' AS dossierdesc',
						),
						'where' => array(
							'dossierdesc.dossier=%dossier%',
							'dossierdesc.lang=%lang%'
						)
					)
				),
				'chemin' => array(
					'label' => 'Chemin du dossier',
					'type' => 'value',
					'data' => array()
				)
			),
			'contenu' => array(
				'titre' => array(
					'label' => 'Titre du contenu',
					'in_required' => true,
					'type' => 'sql',
					'data' => array(
						'select' => 'contenudesc.titre',
						'from' => array(
							Contenu::TABLE . ' AS contenu',
							Contenudesc::TABLE . ' AS contenudesc',
						),
						'where' => array(
							'contenu.id=%contenu%',
							'contenudesc.contenu=contenu.id',
							'contenudesc.lang=%lang%'
						)
					)
				),
				'nom_dossier' => array(
					'label' => 'Titre du dossier',
					'type' => 'sql',
					'data' => array(
						'select' => 'dossierdesc.titre',
						'from' => array(
							Contenu::TABLE . ' AS contenu',
							Dossierdesc::TABLE . ' AS dossierdesc',
						),
						'where' => array(
							'contenu.id=%contenu%',
							'dossierdesc.dossier=contenu.dossier',
							'dossierdesc.lang=%lang%'
						)
					)
				),
				'url_dossier' => array(
					'label' => 'URL du dossier',
					'type' => 'value',
					'data' => array()
				),
			)
		);

		// langues pour les produits, les rubriques, les dossiers, et les contenus
		$this->_tokens['produit'] = array_merge($this->_tokens['produit'], $tokensLang);
		$this->_tokens['rubrique'] = array_merge($this->_tokens['rubrique'], $tokensLang);
		$this->_tokens['dossier'] = array_merge($this->_tokens['dossier'], $tokensLang);
		$this->_tokens['contenu'] = array_merge($this->_tokens['contenu'], $tokensLang);
		
		// appel aux pipelines
		$this->_tokens = $this->_callPipelines('urlrewriter_retrieveTokens', $this->_tokens);
		return $this->_tokens;
	}
	
	/**
	 * Traitement des formulaires de l'UI
	 */
	public function verifForms() {
		$res = array(
            'etape' => 1
        );

		switch(intval($_POST['urlrewriter_step'])) {
			case 1 :
				$rewritings = $this->_verifFormStep1();
				if(!empty($rewritings) && $rewritings != false) $res['etape'] = 2;
				$res['rewritings'] = $rewritings;
				break;
			case 2 :
				$result = $this->_verifFormStep2();
				if($result == false) {
					$res['etape'] = 2;
				}
				$res['etape'] = 3;
				break;
		}

		if(!file_exists(realpath(dirname(__FILE__)) . '/forms/etape' . $res['etape'] . '.php')) $res['etape'] = 1;
        return $res;
	}
	
	/**
	 * Traitement de l'étape 1
	 */
	private function _verifFormStep1() {
		if(empty($_POST['urlrewriter_rewrite'])) return false;
		if(empty($this->_tokens)) $this->retrieveTokens();
		
		$rewritings = array(); // tabeau qui contiendra toutes les URLs calculées
		
		$langues = CacheBase::getCache()->mysql_query('SELECT id FROM ' . Lang::TABLE, $this->link);

		foreach((array) $_POST['urlrewriter_rewrite'] as $objet => $regle) {
			if(empty($regle)) continue;
			if(empty($this->_tokens[$objet])) continue;

			preg_match_all("`\%([a-z_^\%]{1,})\%`", $regle, $cut);

			$tokensFound = array();
			$tokensRequiredFound = array();
			foreach($cut[1] as $token) {
				if(array_key_exists($token, $this->_tokens[$objet])) {
					$tokensFound[$token] = $this->_tokens[$objet][$token];
					if(!empty($this->_tokens[$objet][$token]['in_required'])) {
						$tokensRequiredFound[] = $token;
					}
				}
			}
			if(empty($tokensFound)) {
				$this->error = 'Vous devez spécifier un marqueur pour les objets de type ' . $objet;
				return false;
				break;
			}
			if(empty($tokensRequiredFound)) {
				$this->error = 'Vous devez spécifier un marqueur unique pour les objets de type ' . $objet . ' : ';
				foreach($this->_tokens[$objet] as $tokenName => $tokenData) {
					if($tokenData['in_required']) $this->error.= '<br/>-' . $tokenName;
				}
				return false;
				break;
			}
			
			// Appel aux modules pour obtenir la liste des tuples sur lesquels travailler
			$req_objects = $this->_callPipelines('urlrewriter_reqObjects', $objet);
			while($row = mysql_fetch_assoc($req_objects)) {
				foreach($langues as $lang) {
					$search = array();
					$replacements = array();

					$contextVars = $row;
					$contextVars['lang'] = $lang->id;

					foreach($tokensFound as $token => $tokenData) {
						$calculatedToken = $this->_calculateToken(
							$objet,
							$token,
							$tokenData,
							$contextVars
						);
						$search[] = '%' . $token . '%';
						$replacements[] = trim($calculatedToken, ' ');
					}

					// assemblage de l'URL rewritée, puis appel aux pipelines
					$urlRewrited = str_replace($search, $replacements, $regle);
					$urlRewrited = $this->_callPipelines('urlrewriter_santitizeUrl', $urlRewrited);
					// assemblage de l'URL non rewritée					
					$urlNormal = $this->_callPipelines('urlrewriter_unrewritedUrl', array('objet' => $objet, 'context' => $contextVars));
					// Ajout à la liste des URLs calculées
					$rewritings[$objet][] = array(
						'urlRewrited' => $urlRewrited, 
						'urlNormal' => $urlNormal,
						'context' => $contextVars
					);
				}
			}
		}
		
		$_SESSION['urlrewriter']['rewritings'] = $rewritings;
		return $rewritings;
	}
	
	/**
	 * Traitement de l'étape 2
	 */
	private function _verifFormStep2() {
		if(empty($_SESSION['urlrewriter']['rewritings']) || count($_SESSION['urlrewriter']['rewritings']) == 0) {
			$this->error = 'Aucune URL à traiter';
			return false;
		}
		
		// suppression des URLS existantes
		foreach((array) $_SESSION['urlrewriter']['rewritings'] as $objet => $rewritings) {
			$selectIds = array();
			foreach((array) $rewritings as $rewriting) {
				$selectIds[] = '(param=\'' . $rewriting['urlNormal'] . '\' AND lang=' . $rewriting['context']['lang'] . ')';
			}
			$req = 'SELECT id FROM ' . Reecriture::TABLE . ' WHERE ' .
			$req.= implode(' OR ', $selectIds);
			
			$reqDelete = $this->query($req);
			if(mysql_num_rows($reqDelete) > 0) {
				$req_del = 'DELETE FROM ' . Reecriture::TABLE . ' WHERE id IN(';
				while($rowDelete = mysql_fetch_assoc($reqDelete)) {
					$req_del.= $rowDelete['id'] . ',';
				}
				$req_del = substr($req_del, 0, -1);
				$req_del .= ');';
				$this->query($req_del);
			}
		}
		
		// insertion des nouvelles URLs
		foreach((array) $_SESSION['urlrewriter']['rewritings'] as $objet => $rewritings) {
			$reqInsert = 'INSERT INTO ' . Reecriture::TABLE . '(
				`url`, `fond`, `param`, `lang`, `actif`
				) VALUES ';
			$insertValues = $reqInsert;
			$i = 0;
			if(!empty($rewritings)) {
				foreach((array) $rewritings as $rewriting) {
					
					/*if($i == 500) {
						$insertValues = substr($insertValues, 0, -1);
						$insertValues .= ';' . $reqInsert;
						$i=0;
					}*/
					
					$insertValues .= "(
						'" . $rewriting['urlRewrited'] . "',
						'" . $objet . "',
						'" . $rewriting['urlNormal'] . "',
						'" . $rewriting['context']['lang'] . "',
						1
					),";
					$i++;
				}
				$insertValues = substr($insertValues, 0, -1);
				$insertValues .= ';';
				//var_dump($insertValues);
				$this->query($insertValues);
			}
			
		}
	}

	/**
	 * Pipeline urlrewriter_santitizeUrl
	 * Adapté de http://cubiq.org/the-perfect-php-clean-url-generator
	 * @param string $str : l'URL à vérifier
	 */
	public function urlrewriter_santitizeUrl($str) {
		//echo $str . '<br/>';
		$delimiter = '-';
		$str = str_replace(("'"), ' ', $str);
		
		$str = str_replace(
	        array(
	            'à', 'â', 'ä', 'á', 'ã', 'å',
	            'î', 'ï', 'ì', 'í', 
	            'ô', 'ö', 'ò', 'ó', 'õ', 'ø', 
	            'ù', 'û', 'ü', 'ú', 
	            'é', 'è', 'ê', 'ë', 
	            'ç', 'ÿ', 'ñ', 
	        ),
	        array(
	            'a', 'a', 'a', 'a', 'a', 'a', 
	            'i', 'i', 'i', 'i', 
	            'o', 'o', 'o', 'o', 'o', 'o', 
	            'u', 'u', 'u', 'u', 
	            'e', 'e', 'e', 'e', 
	            'c', 'y', 'n', 
	        ),
	        $str
	    );
	
		$clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
		$clean = preg_replace("/[^a-zA-Z0-9\/_\.|+ -]/", '', $clean);
		$clean = strtolower(trim($clean, '-'));
		$clean = preg_replace("/[_|+ -]+/", $delimiter, $clean);
		
		if(substr($clean, 0, 1) == '/') $clean = substr($clean, 1);
	
		return $clean;
	}
	
	/**
	 * Pipeline urlrewriter_reqObjects
	 * Retourne la requête SQL permettant d'obtnir la liste 
	 * des tuples sur lesquels travailler
	 * @param string $object
	 */
	public function urlrewriter_reqObjects($objet) {
		$req = null;
		switch($objet) {
			case 'produit' : 
				$req = $this->query('SELECT id AS produit, rubrique FROM ' . Produit::TABLE);
				break;
			case 'rubrique' :
				$req = $this->query('SELECT id AS rubrique FROM ' . Rubrique::TABLE);
				break;
			case 'dossier' : 
				$req = $this->query('SELECT id AS dossier FROM ' . Dossier::TABLE);
				break;
			case 'contenu' : 
				$req = $this->query('SELECT id AS contenu, dossier FROM ' . Contenu::TABLE);
				break;
		}
		return $req;
	}

	/**
	 * Pipeline urlrewriter_unrewritedUrl
	 * Retourne l'URL non rewritée pour un type d'objet
	 * @param array $params('objet' => ..., 'context' => array())
	 */
	public function urlrewriter_unrewritedUrl($params) {
		$url = null;
		switch($params['objet']) {
			case 'produit' : 
				$url = '&id_produit=' . $params['context']['produit'] . '&id_rubrique=' . $params['context']['rubrique'];
				break;
			case 'rubrique' : 
				$url = '&id_rubrique=' . $params['context']['rubrique'];
				break;
			case 'dossier' : 
				$url = '&id_dossier=' . $params['context']['dossier'];
				break;
			case 'contenu' : 
				$url = '&id_contenu=' . $params['context']['contenu'] . '&id_dossier=' . $params['context']['dossier'];
				break;
		}
		return $url;
	}
	
	/**
	 * Pipeline urlrewriter_calculateToken_{objet}_{tokenName}()
	 * Calcul la valeur du token 'id_lang' pour l'objet 'produit'
	 * @param array $params
	 * @return array $params
	 */
	public function urlrewriter_calculateToken_produit_id_lang($params) {
		$params['return'] = $params['tokenParams']['data'];
		return $params;
	}
	
	/**
	 * Pipeline urlrewriter_calculateToken_{objet}_{tokenName}()
	 * Calcul la valeur du token 'url_rubrique' pour l'objet 'produit'
	 * @param array $params
	 * @return array $params
	 */
	public function urlrewriter_calculateToken_produit_url_rubrique($params) {
		$sql = '
			SELECT url
            FROM ' . Reecriture::TABLE . '
            WHERE
                actif=1
                AND lang=' . $params['context']['lang'] . '
                AND param=\'&id_rubrique=' . $params['context']['rubrique'] . '\'
		 	ORDER BY id DESC
		 	LIMIT 0,1';
		$result = CacheBase::getCache()->mysql_query($sql, $this->link);
        if(!empty($result[0]->url)) {
			$params['return'] = $result[0]->url;
        }       
        return $params;
	}
	
	/**
	 * Pipeline urlrewriter_calculateToken_{objet}_{tokenName}()
	 * Calcul la valeur du token 'id_lang' pour l'objet 'contenu'
	 * @param array $params
	 * @return array $params
	 */
	public function urlrewriter_calculateToken_contenu_id_lang($params) {
		$params['return'] = $params['tokenParams']['data'];
		return $params;
	}
	
	/**
	 * Pipeline urlrewriter_calculateToken_{objet}_{tokenName}()
	 * Calcul la valeur du token 'url_dossier' pour l'objet 'contenu'
	 * @param array $params
	 * @return array $params
	 */
	public function urlrewriter_calculateToken_contenu_url_dossier($params) {
		$sql = '
			SELECT url
            FROM ' . Reecriture::TABLE . '
            WHERE
                actif=1
                AND lang=' . $params['context']['lang'] . '
                AND param=\'&id_dossier=' . $params['context']['dossier'] . '\'
		 	ORDER BY id DESC
		 	LIMIT 0,1';
		$result = CacheBase::getCache()->mysql_query($sql, $this->link);
        if(!empty($result[0]->url)) {
			$params['return'] = $result[0]->url;
        }       
        return $params;
	}
	
	/**
	 * Pipeline urlrewriter_calculateToken_{objet}_{tokenName}()
	 * Calcul la valeur du token 'id_lang' pour l'objet 'rubrique'
	 * @param array $params
	 * @return array $params
	 */
	public function urlrewriter_calculateToken_rubrique_id_lang($params) {
		$params['return'] = $params['tokenParams']['data'];
		return $params;
	}
	
	/**
	 * Pipeline urlrewriter_calculateToken_{objet}_{tokenName}()
	 * Calcul la valeur du token 'chemin' pour l'objet 'rubrique'
	 * @param array $params
	 * @return array $params
	 */
	public function urlrewriter_calculateToken_rubrique_chemin($params) {
		$idRubrique = $params['context']['rubrique'];
        while($idRubrique != 0) {
            $sql = '
                SELECT
                	rubrique.id,
                    rubriquedesc.titre,
                    rubrique.parent
                FROM ' .
                    Rubrique::TABLE . ' AS rubrique,' .
                    Rubriquedesc::TABLE . ' AS rubriquedesc
                WHERE
                    rubrique.id=' . $idRubrique . '
                    AND rubrique.id=rubriquedesc.rubrique
                    AND rubriquedesc.lang=' . $params['context']['lang'] . '
                ORDER BY rubriquedesc.titre ASC';
            //var_dump($sql);
            $resul = CacheBase::getCache()->mysql_query($sql, $this->link);
            if(count($resul) > 0) {
	            foreach((array) $resul as $row) {
	            	// La rubrique sur laquelle on travaille ne doit pas
	            	// apparaitre dans le chemin : elle dispose de ses prores
	            	// tokens (@titre_rubrique% par exemple) 
	            	if($row->id != $params['context']['rubrique']) {
	            		$params['return'] = $row->titre . '/' . $params['return'];
	            	}
	            	$idRubrique = $row->parent;
	            }
	            // on vire le trailing slash
	            if(substr($params['return'], -1) == '/') $params['return'] = substr($params['return'], 0, -1);
            } else $idRubrique = 0;
            
            if($idRubrique == 0) break;
        }
		return $params;
	}
	
	/**
	 * Pipeline urlrewriter_calculateToken_{objet}_{tokenName}()
	 * Calcul la valeur du token 'id_lang' pour l'objet 'dossier'
	 * @param array $params
	 * @return array $params
	 */
	public function urlrewriter_calculateToken_dossier_id_lang($params) {
		$params['return'] = $params['tokenParams']['data'];
		return $params;
	}
	
	/**
	 * Pipeline urlrewriter_calculateToken_{objet}_{tokenName}()
	 * Calcul la valeur du token 'chemin' pour l'objet 'dossier'
	 * @param array $params
	 * @return array $params
	 */
	public function urlrewriter_calculateToken_dossier_chemin($params) {
		$idDossier = $params['context']['dossier'];
        while($idDossier != 0) {
            $sql = '
                SELECT
                	dossier.id,
                    dossierdesc.titre,
                    dossier.parent
                FROM ' .
                    Dossier::TABLE . ' AS dossier,' .
                    Dossierdesc::TABLE . ' AS dossierdesc
                WHERE
                    dossier.id=' . $idDossier . '
                    AND dossier.id=dossierdesc.dossier
                    AND dossierdesc.lang=' . $params['context']['lang'] . '
                ORDER BY dossierdesc.titre ASC';
            //var_dump($sql);
            $resul = CacheBase::getCache()->mysql_query($sql, $this->link);
            if(count($resul) > 0) {
	            foreach((array) $resul as $row) {
	            	$idDossier = $row->parent;
	            	
	            	// Dossiers à exclure ?
	            	if(!empty($this->_tokens['dossier']['@excluded'])) {
	            		if(is_array($this->_tokens['dossier']['@excluded'])) {
	            			if(in_array($row->id, $this->_tokens['dossier']['@excluded'])) 
	            				continue;	            			
	            		} elseif(is_string($this->_tokens['dossier']['@excluded'])) {
            				if($row->id == intval($this->_tokens['dossier']['@excluded']))
            					continue;
            			}
	            	}
	            	
	            	// Le dossier sur lequel on travaille ne doit pas
	            	// apparaitre dans le chemin : il dispose de ses prores
	            	// tokens (@titre% par exemple) 
	            	if($row->id != $params['context']['dossier']) {
	            		$params['return'] = $row->titre . '/' . $params['return'];
	            	}
	            }
	            // on vire le trailing slash
	            if(substr($params['return'], -1) == '/') $params['return'] = substr($params['return'], 0, -1);
            } else $idDossier = 0;
            
            if($idDossier == 0) break;
        }
		return $params;
	}

	/****** Private methods *********/

	/**
	 * Appel aux modules
	 * @param string $methodName
	 * @param array $params
	 */
	private function _callPipelines($methodName, $params) {
		if(empty($methodName)) return false;
		$liste = ActionsModules::instance()->lister(false, true);
		foreach($liste as $module) {
			try {
				$instance = ActionsModules::instance()->instancier($module->nom);
				if (method_exists($instance, $methodName)) {
					$params = $instance->$methodName($params);
				}
			} catch (Exception $e) {}
		}
		return $params;
	}
	
	/**
	 * Calcul de la valeur d'un token pour un certain contexte
	 * @param string $objet
	 * @param string $token
	 * @param mixed $tokenParams
	 * @param array $context
	 */
	private function _calculateToken($objet, $tokenName, $tokenParams=null, $contextVars=null) {
		if(!isset($this->_tokens[$objet][$tokenName])) return '';
		
		// remplacement des variables de contexte dans 
		// la règle de calcul du token
		foreach((array) $contextVars as $contextKey => $contextValue) {
			if(!isset($tokenParams['data'])) continue;
			if(is_array($tokenParams['data'])) {
				array_walk_recursive(
					$tokenParams['data'], 
					array($this, '_contextualTokenReplace'), 
					array('key' => $contextKey, 'value' => $contextValue)
				);
			} else {
				$this->_contextualTokenReplace($tokenParams['data'], null, array('key' => $contextKey, 'value' => $contextValue));
			}
		}

		switch($tokenParams['type']) {
			case 'value' :
				$params = array(
					'tokenParams' => $tokenParams,
					'context' => $contextVars,
					'return' => $return
				);
				$params = $this->_callPipelines(
					'urlrewriter_calculateToken_' . $objet . '_' . $tokenName,
					$params 
				);
				return $params['return'];
				break;
			case 'sql' :
				$sql = $this->_buildSQL($tokenParams['data']);
				$row = CacheBase::getCache()->mysql_query($sql, $this->link);
				if(count($row) != 1) break;
				return reset($row[0]);
				break;
			default :
				return '%' . $tokenName . '%';
		}
	}
	
	/**
	 * Remplace les valeurs de contexte dans les tokens.
	 * @param string $item : passé par référence
	 * @param string $key
	 * @param array $context
	 */
	private function _contextualTokenReplace(&$item, $key=null, $context=array()) {
		if(empty($context['key'])) return;		
		$item  = preg_replace('/\%' . $context['key'] . '\%/', $context['value'], $item);
	}
	
	/**
	 * Assemblage de la requete SQL
	 * @param unknown_type $datas
	 */
	private function _buildSQL($datas) {
		$select = $this->_buildPieceOfSQL('select', $datas['select']);
		if(!$select) return false;
		
		$from = $this->_buildPieceOfSQL('from', $datas['from']);
		if(!$from) return false;
		
		$where = $this->_buildPieceOfSQL('where', $datas['where']);
		if(!$where) return false;
		
		$orderby = $this->_buildPieceOfSQL('orderby', $datas['orderby']);
		$groupby = $this->_buildPieceOfSQL('groupby', $datas['groupby']);
		$limit = $this->_buildPieceOfSQL('limit', $datas['limit']);

		$sql = '
			SELECT ' . $select . '
			FROM ' . $from . '
			WHERE ' . $where;
		if(!empty($groupby)) $sql.= ' GROUP BY ' . $groupby;
		if(!empty($orderby)) $sql.= ' ORDER BY ' . $orderby;
		if(!empty($limit)) $sql.= ' LIMIT ' . $limit;
		//var_dump($sql);
		return $sql;
	}

	/**
	 * Assemblage d'un morceau de requête SQL
	 * @param string $type : select, from, where, orderby ou groupby
	 * @param mixed $data
	 */
	private function _buildPieceOfSQL($type, $data) {
		$return = null;
		if(is_array($data)) {
			switch($type) {
				case 'where' :
					return implode(' AND ', $data);
					break;
				case 'limit' : 
					$limit = '';
					if(preg_match('/^[0-9]{1,}$/', $data['max'])) {
						if(preg_match('/^[0-9]{1,}$/', $data['min'])) $limit .= $data['min'] . ',';
						$limit .= $data['max'];	
					}
					return $limit;
					break;
				default : 
					return implode(', ', $data);
			}
		}
		if(is_string($data)) $return = $data;
		return $return;
	}
}