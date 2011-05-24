<?php
	
	class Extension_CanOfSpam extends Extension {
	/*-------------------------------------------------------------------------
		Definition:
	-------------------------------------------------------------------------*/
		
		public function about() {
			return array(
				'name'			=> 'Can Of Spam',
				'version'		=> '1.0.4',
				'release-date'	=> '2009-12-09',
				'author'		=> array(
					'name'			=> 'Rowan Lewis',
					'website'		=> 'http://rowanlewis.com/',
					'email'			=> 'me@rowanlewis.com'
				),
				'description'	=> 'Protect your forms against spam with a hidden field.'
			);
		}
		
		public function uninstall() {
			Symphony::Configuration()->remove('canofspam');
			Administration::instance()->saveConfig();
			
			Symphony::Database()->query("DROP TABLE `tbl_canofspam_tracking`");
		}
		
		public function install() {
			Symphony::Configuration()->set('uniqueid', md5(time()), 'canofspam');
			Administration::instance()->saveConfig();
			
			Symphony::Database()->query("
				CREATE TABLE IF NOT EXISTS `tbl_canofspam_tracking` (
					`id` int(11) NOT NULL auto_increment,
					`client` varchar(32) NOT NULL,
					`form` varchar(32) default NULL,
					PRIMARY KEY (`id`),
					KEY `client` (`client`),
					KEY `form` (`form`)
				)
			");
			
			return true;
		}
		
		public function getSubscribedDelegates() {
			return array(
				array(
					'page'		=> '/blueprints/events/new/',
					'delegate'	=> 'AppendEventFilter',
					'callback'	=> 'appendEventFilter'
				),
				array(
					'page'		=> '/blueprints/events/edit/',
					'delegate'	=> 'AppendEventFilter',
					'callback'	=> 'appendEventFilter'
				),
				array(
					'page'		=> '/blueprints/events/new/',
					'delegate'	=> 'AppendEventFilterDocumentation',
					'callback'	=> 'appendEventFilterDocumentation'
				),
				array(
					'page'		=> '/blueprints/events/edit/',
					'delegate'	=> 'AppendEventFilterDocumentation',
					'callback'	=> 'appendEventFilterDocumentation'
				),
				array(
					'page'		=> '/system/preferences/',
					'delegate'	=> 'AddCustomPreferenceFieldsets',
					'callback'	=> 'addCustomPreferenceFieldsets'
				),
				array(
					'page'		=> '/frontend/',
					'delegate'	=> 'EventPreSaveFilter',
					'callback'	=> 'eventPreSaveFilter'
				),
				array(
					'page'		=> '/frontend/',
					'delegate'	=> 'EventPostSaveFilter',
					'callback'	=> 'eventPostSaveFilter'
				),
				array(
					'page'		=> '/frontend/',
					'delegate'	=> 'FrontendParamsResolve',
					'callback'	=> 'frontendParamsResolve'
				)
			);
		}
		
	/*-------------------------------------------------------------------------
		Utilities:
	-------------------------------------------------------------------------*/
		
		public function getUniqueId() {
			return Symphony::Configuration()->get('uniqueid', 'canofspam');
		}
		
		public function getClientId() {
			return md5($this->getUniqueId() . $_SERVER['REMOTE_ADDR']);
		}
		
		public function getFormId() {
			$client = $this->getClientId();
			
			// Find existing:
			$form = Symphony::Database()->fetchVar('form', 0, "
				SELECT
					t.*
				FROM
					`tbl_canofspam_tracking` AS t
				WHERE
					t.client = '{$client}'
			");
			
			// Create new:
			if (empty($form)) {
				$form = md5($this->getUniqueId() . '-' . time());
				
				Symphony::Database()->query("
					INSERT INTO
						`tbl_canofspam_tracking`
					SET
						`client` = '{$client}',
						`form` = '{$form}'
				");
			}
			
			return $form;
		}
		
		public function clearFormId() {
			$client = $this->getClientId();
			
			Symphony::Database()->query("
				DELETE FROM
					`tbl_canofspam_tracking`
				WHERE
					`client` = '{$client}'
			");
		}
		
	/*-------------------------------------------------------------------------
		Delegates:
	-------------------------------------------------------------------------*/
		
		public function appendEventFilter($context) {
			$context['options'][] = array(
				'canofspam',
				@in_array(
					'canofspam', $context['selected']
				),
				'Can Of Spam'
			);
		}
		
		public function appendEventFilterDocumentation($context) {
			if (!is_array($context['selected']) or !in_array('canofspam', $context['selected'])) return;
			
			$context['documentation'][] = new XMLElement('h3', 'Can Of Spam Filter');
			
			$context['documentation'][] = new XMLElement('p', '
				To use the Can Of Spam filter, add the following field to your form:
			');
			
			$context['documentation'][] = contentBlueprintsEvents::processDocumentationCode('
<input name="canofspam" value="{$canofspam}" type="hidden" />
			');
		}
		
		public function addCustomPreferenceFieldsets($context) {
			$group = new XMLElement('fieldset');
			$group->setAttribute('class', 'settings');
			$group->appendChild(
				new XMLElement('legend', 'Can Of Spam Filter')
			);
			
			$uniqueID = Widget::Label('Unique ID');
			$uniqueID->appendChild(Widget::Input(
				'settings[canofspam][uniqueid]', General::Sanitize($this->getUniqueID())
			));
			$group->appendChild($uniqueID);
			
			$context['wrapper']->appendChild($group);			
		}
		
	/*-------------------------------------------------------------------------
		Filtering:
	-------------------------------------------------------------------------*/
		
		public function frontendParamsResolve($context) {
			$context['params']['canofspam'] = $this->getFormId();
		}
		
		public function eventPreSaveFilter($context) {
			if (!in_array('canofspam', $context['event']->eParamFILTERS)) return;
			
			$form = $_POST['canofspam']; $valid = false;
			
			if ($form == $this->getFormId()) $valid = true;
			
			if (!$valid) $this->clearFormId();
			
			$context['messages'][] = array(
				'canofspam', $valid, (!$valid ? 'Data was identified as spam.' : null)
			);
		}
		
		public function eventPostSaveFilter($context) {
			$this->clearFormId();
		}
	}
	
?>