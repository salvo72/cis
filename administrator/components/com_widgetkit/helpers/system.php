<?php
/**
* @package   Widgetkit Component
* @file      system.php
* @version   1.0.0 BETA 8 August 2011
* @author    YOOtheme http://www.yootheme.com
* @copyright Copyright (C) 2007 - 2011 YOOtheme GmbH
* @license   YOOtheme Proprietary Use License (http://www.yootheme.com/license)
*/

/*
	Class: SystemWidgetkitHelper
		System helper class
*/
class SystemWidgetkitHelper extends WidgetkitHelper {

	/* application */
	public $application;

	/* document */
	public $document;

	/* language */
	public $language;

	/* system path */
	public $path;

	/* system url */
	public $url;

	/* options */
	public $options;

	/* cache path */
	public $cache_path;

	/*
		Function: Constructor
			Class Constructor.
	*/
	public function __construct($widgetkit) {
		parent::__construct($widgetkit);

		// init vars
		$this->application = JFactory::getApplication();
        $this->document    = JFactory::getDocument();
		$this->language    = JFactory::getLanguage();
        $this->path        = JPATH_ROOT;
        $this->url         = JURI::root(true);
		$this->options     = $this['data']->create($this->_getParams());
        $this->cache_path  = $this->path.'/cache/widgetkit';

		// set cache directory
		if (!file_exists($this->cache_path)) {
			JFolder::create($this->cache_path);
		}
	}

	/*
		Function: init
			Initialize system

		Returns:
			Void
	*/
	public function init() {
		
		// set translations
		$this->language->load('widgetkit', $this['path']->path('widgetkit:'), null, true);

		// get media directory
		$media = JComponentHelper::getParams('com_media')->get('file_path');

		// set paths
        $this['path']->register($this->path.'/media/widgetkit', 'widgetkit');
        $this['path']->register($this->path.'/media/widgetkit/widgets', 'widgets');
        $this['path']->register($this->path.'/modules', 'modules');
        $this['path']->register($this->cache_path, 'cache');
        $this['path']->register($this->path."/$media", 'media');

		// load widgets
		foreach ($this['path']->dirs('widgets:') as $name) {
			if ($file = $this['path']->path("widgets:{$name}/{$name}.php")) {
				require_once($file);
			}
		}

		// is admin ?
		if ($this->application->isAdmin() && $this['request']->get('option', 'string') == 'com_widgetkit') {

			// cache writable ?
			if (!file_exists($this->cache_path) || !is_writable($this->cache_path)) {
				$this->application->enqueueMessage("Widgetkit cache folder is not writable! Please check directory permissions ({$this->cache_path})", 'notice');
			}

			// load editor
			$this['editor']->init();

            // add stylesheets/javascripts
			$this['asset']->addFile('css', 'widgetkit:css/admin.css');
			$this['asset']->addFile('css', 'widgetkit:css/system.css');
			$this['asset']->addFile('js', 'widgetkit:js/jquery.ui.js');
			$this['asset']->addFile('js', 'widgetkit:js/jquery.plugins.js');
			$this['asset']->addFile('js', 'widgetkit:js/admin.js');
			$this['asset']->addString('js', 'var widgetkitajax = "'.$this['system']->link(array('ajax' => true)).'";');

            // get request vars
			$task = $this['request']->get('task', 'string');

			// trigger event
			$this['event']->trigger('admin');

			// execute task
			echo $this['template']->render($task ? 'task' : 'dashboard', compact('task'));

			// add assets
			$this['template']->render('assets');
		}

		// is site ?
		if ($this->application->isSite() && is_a($this->document, 'JDocumentHTML')) {

			// set direction
			$this->options->set('direction', $this->document->direction);

            // add stylesheets/javascripts
			$this['asset']->addFile('css', 'widgetkit:css/widgetkit.css');
			$this['asset']->addFile('js', 'widgetkit:js/jquery.plugins.js');

			// trigger event
			$this['event']->trigger('site');

			// add assets
			$this['template']->render('assets');
		}

	}

	/*
		Function: link
			Get link to system related resources.

		Parameters:
			$query - HTTP query options

		Returns:
			String
	*/
	public function link($query = array()) {

		// build query
		$query = array_merge(array('option' => $this['request']->get('option', 'string')), $query);

		if (isset($query['ajax'])) {
			$query = array_merge(array('format' => 'raw'), $query);
		}

		return $this->url.'/administrator/index.php?'.http_build_query($query, '', '&');
	}

	/*
		Function: saveOptions
			Save plugin options

		Returns:
			Void
	*/
	public function saveOptions() {
		$this->_setParams((string) $this->options);
	}

	/*
		Function: __
			Retrieve translated strings

		Returns:
			String
	*/
    public function __($string) {
		return JText::_($string);
    }

	/*
		Function: _getParams
			Get parameter from database

		Returns:
			String
	*/
	protected function _getParams() {

		$db = JFactory::getDBO();
		$db->setQuery("SELECT params FROM #__components AS c WHERE c.option='com_widgetkit'");

		return $db->loadResult();
	}

	/*
		Function: _saveParams
			Set parameter in database

		Returns:
			Boolean
	*/
	protected function _setParams($params) {

		$db = JFactory::getDBO();
		$db->setQuery(sprintf("UPDATE #__components AS c SET c.params='%s' WHERE c.option='com_widgetkit'", $db->getEscaped($params)));

		return $db->query();
	}

}