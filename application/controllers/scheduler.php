<?php if (!defined('FROM_BASE')) { header('HTTP/1.1 403 Forbidden'); die('Invalid requested path.'); }

/*
 * This file is part of ND PHP Framework.
 *
 * ND PHP Framework - An handy PHP Framework (www.nd-php.org)
 * Copyright (C) 2015-2016  Pedro A. Hortas (pah@ucodev.org)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

/*
 * ND PHP Framework (www.nd-php.org) - Contributor Agreement
 *
 * When contributing material to this project, please read, accept, sign
 * and send us the Contributor Agreement located in the file NDCA.pdf
 * inside the documentation/ directory.
 *
 */

class Scheduler extends ND_Controller {
	/* Constructor */
	public function __construct($session_enable = true, $json_replies = false) {
		parent::__construct($session_enable, $json_replies);

		$this->_viewhname = get_class();
		$this->_name = strtolower($this->_viewhname);
		$this->_hook_construct();

		/* Include any setup procedures from ide builder. */
		include('lib/ide_setup.php');
	}
	
	/** Hooks **/
	protected function _hook_insert_post(&$id, &$POST, &$fields, $hook_pre_return) {
		$this->db->where('id', $id);
		$this->db->update($this->_name, array(
			'registered' => date('Y-m-d H:i:s')
		));
	}

	/** Other overloads **/

	/* Aliases for the current table field names */
	protected $_table_field_aliases = array(
		'entry_name' => NDPHP_LANG_MOD_COMMON_ENTRY_NAME,
		'description' => NDPHP_LANG_MOD_COMMON_DESCRIPTION,
		'url' => 'URL',
		'period' => NDPHP_LANG_MOD_COMMON_PERIOD,
		'registered' => NDPHP_LANG_MOD_COMMON_REGISTERED,
		'last_run' => NDPHP_LANG_MOD_COMMON_LAST_RUN,
		'next_run' => NDPHP_LANG_MOD_COMMON_NEXT_RUN,
		'output' => NDPHP_LANG_MOD_COMMON_OUTPUT
	);

	/** Custom functions **/

}