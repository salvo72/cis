<?php
/**
* @package   Warp Theme Framework
* @file      config.php
* @version   6.0.7
* @author    YOOtheme http://www.yootheme.com
* @copyright Copyright 2007 - 2011 YOOtheme GmbH
* @license   YOOtheme Proprietary Use License (http://www.yootheme.com/license)
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

class JElementConfig extends JElement {

	var	$_name = 'Config';

	function fetchElement($name, $value, &$node, $control_name) {

		// copy callback
		$this->copyAjaxCallback();

		// load config
		require_once(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/config.php');

		// get warp
		$warp = Warp::getInstance();
		$warp['system']->document->addScript($warp['path']->url('lib:jquery/jquery.js'));
		$warp['system']->document->addScript($warp['path']->url('config:js/config.js'));
		$warp['system']->document->addScript($warp['path']->url('config:js/admin.js'));
		$warp['system']->document->addStyleSheet($warp['path']->url('config:css/config.css'));
		$warp['system']->document->addStyleSheet($warp['path']->url('config:css/admin.css'));

		// render config
		return $warp['template']->render('config:layouts/config');
	}

	function copyAjaxCallback() {

		$source = dirname(__FILE__).'/warp-ajax.php';
		$target = JPATH_ROOT.'/administrator/templates/system/warp-ajax.php';

		if (!file_exists($target) || md5_file($source) != md5_file($target)) {
			JFile::copy($source, $target);
		}

	}

}