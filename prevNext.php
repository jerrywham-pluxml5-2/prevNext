<?php 
/**
 * Plugin prevNext
 *
 * @package	PLX
 * @version	1.0
 * @date	19/01/2014
 * @author	Stéphane F., DanielSan, Cyril MAGUIRE
*/
class prevNext extends plxPlugin {
	
	/**
	 * Constructeur de la classe prevNext
	 *
	 * @param	default_lang	langue par défaut utilisée par PluXml
	 * @return	null
	 * @author	Stephane F
	 **/
	public function __construct($default_lang) {

		# Appel du constructeur de la classe plxPlugin (obligatoire)
		parent::__construct($default_lang);

		# Déclarations des hooks		
		$this->addHook('prevNext', 'prevNext');
	}
	/**
	 * Méthode permettant d'afficher les liens vers les articles précédent et suivant d'une catégorie ou de l'ensemble des articles
	 * 
	 * @param $params array Tableau des paramètres d'affichage
	 *				  $mode bool Si true, n'affiche que deux articles (au plus) d'une catégorie
	 *							 Si false affiche deux articles (au plus) de tous les articles
	 *				  $formatPrev string optional Le format d'affichage des liens "précédent"
	 *				  $formatNext string optional le format d'affichage des liens "suivant"
	 * @return $links string Au maximum, deux liens
	 * @author Stéphane F., DanielSan, Cyril MAGUIRE
	 */
	public function prevNext($params) {
		$mode = (bool)$params[0];
		if (isset($params[1])) {
			$formatPrev = $params[1];
		} else {
			$formatPrev = '<a href="#prevUrl" title="#prevTitle" rel="prev">&laquo; <span>#prevArt</span></a> ';
		}
		if (isset($params[2])) {
			$formatNext = $params[2];
		} else {
			$formatNext = ' <a href="#nextUrl" title="#nextTitle" rel="next"><span>#nextArt</span> &raquo;</a>';
		}

		$plxShow = plxShow::getInstance();
		$ordre = preg_match('/asc/',$plxShow->plxMotor->tri)?'sort':'rsort';
		$links = '';
		
		if($mode AND $plxShow->catId()!= "home") { // Des articles parmi les articles d'une catégorie
			$ID_CAT = str_pad ($plxShow->catId(), 3, '0', STR_PAD_LEFT);
			$aFiles = $plxShow->plxMotor->plxGlob_arts->query('/[0-9]{4}.['.$ID_CAT.']*.[0-9]{3}.[0-9]{12}.[a-z0-9-]+.xml$/','art',$ordre,0,false,'before');
		} else { // Des articles parmi tous les articles
			$aFiles = $plxShow->plxMotor->plxGlob_arts->query('/[0-9]{4}.[home|0-9,]*.[0-9]{3}.[0-9]{12}.[a-z0-9-]+.xml$/','art',$ordre,0,false,'before');
		}

		$key = array_search(basename($plxShow->plxMotor->plxRecord_arts->f('filename')), $aFiles);
		$prevUrl = $prev = isset($aFiles[$key-1])? $aFiles[$key-1] : false;
		$nextUrl = $next = isset($aFiles[$key+1])? $aFiles[$key+1] : false;

		$plxGlob_arts = clone $plxShow->plxMotor->plxGlob_arts;

			if($prev AND preg_match('/([0-9]{4}).[home|0-9,]*.[0-9]{3}.[0-9]{12}.([a-z0-9-]+).xml$/',$prev,$capture))
				$prevUrl=$plxShow->plxMotor->urlRewrite('?article'.intval($capture[1]).'/'.$capture[2]);
				if ($prev){
					$art = $plxShow->plxMotor->parseArticle(PLX_ROOT.$plxShow->plxMotor->aConf['racine_articles'].$prev);
					$nextTitle = STRIP_TAGS($art['title']);
				}
			if($next AND preg_match('/([0-9]{4}).[home|0-9,]*.[0-9]{3}.[0-9]{12}.([a-z0-9-]+).xml$/',$next,$capture))
				$nextUrl=$plxShow->plxMotor->urlRewrite('?article'.intval($capture[1]).'/'.$capture[2]);
				if ($next) {
					$art = $plxShow->plxMotor->parseArticle(PLX_ROOT.$plxShow->plxMotor->aConf['racine_articles'].$next);
					$prevTitle = STRIP_TAGS($art['title']);
				}
			if($ordre=='rsort') { 
				$dummy=$prevUrl; $prevUrl=$nextUrl; $nextUrl=$dummy; 
			}
			if($prevUrl) {
				$links = str_replace('#prevUrl', $prevUrl, $formatPrev);
				$links = str_replace('#prevTitle', $prevTitle, $links);
				$links = str_replace('#prevArt', $this->getlang('L_PREV_ART'), $links);
				$links = str_replace('#prevImg', $prevImg, $links);
			}
			if($nextUrl) {
				$links .= str_replace('#nextUrl', $nextUrl, $formatNext);
				$links = str_replace('#nextTitle', $nextTitle, $links);
				$links = str_replace('#nextArt', $this->getlang('L_NEXT_ART'), $links);
			}
			return $links;
	}
}
 ?>