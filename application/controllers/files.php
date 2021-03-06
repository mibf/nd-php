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

class Files extends ND_Controller {
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
	
	/** Other overloads **/

	/** Custom functions **/
	public function access($table, $entry_id, $field, $filename, $download = 'no') {
		$file_path = SYSTEM_BASE_DIR . '/uploads/' . $this->session->userdata('user_id') . '/' . $table . '/' . $entry_id . '/' . $field . '/' . openssl_digest(rawurldecode($filename), 'sha256');

		/* Set the field name to be used to check permissions */
		$field_perm = $field;

		/* Mixed relationships require some field name handling... */
		if (substr($field, 0, 6) == 'mixed_') {
			/* Basically the $field form name matches the mixed_<foreign-table>_<field-name>_<mixed_id> format and
			 * we need to convert it to mixed_<base-table>_<foreign-table> format, as this is the field format for
			 * permission checks (stored on _acl_rtcp).
			 */
			$field_perm = 'mixed_' . $table . '_' . explode('_', $field)[1];
		}

		/* Check if file exists and if there are enough permissions to reveal it */
		if (!file_exists($file_path) || !$this->security->perm_check($this->_security_perms, $this->security->perm_read, $table, $field_perm)) {
			/* TODO: Log the failed request? */

			/* NOTE: Do not reveal if the true cause ... */
			header('HTTP/1.1 403 Forbidden');
			die(NDPHP_LANG_MOD_ACCESS_FILE_ACCESS_OR_PERM);
		} else {
			$file_contents = file_get_contents($file_path);

			/* Unencrypt file contents if required*/
			if ($this->_encrypted_uploaded_files === true)
				$file_contents = $this->encrypt->decode($file_contents);

			/* If logging is enabled, check for changed fields and log them */
			if ($this->_logging === true) {
				$log_transaction_id = openssl_digest(date('Y-m-d H:i:s') . mt_rand(100000, 999999), 'md5');

				$this->db->insert('logging', array(
					'operation' => 'READ',
					'_table' => $table,
					'_field' => $field,
					'entryid' => $entry_id,
					'value_old' => $filename,
					'value_new' => $filename,
					'transaction' => $log_transaction_id,
					'registered' => date('Y-m-d H:i:s'),
					'sessions_id' => $this->session->userdata('sessions_id'),
					'users_id' => $this->session->userdata('user_id')
				));
			}

			/* Fetch and set the mime type and dump the file contents */
			$file_info = new finfo(FILEINFO_MIME);
			header('Content-Type: ' . $file_info->buffer($file_contents));
			
			/* Check if we need to set the content disposition as attachment (aka save file instead of browser display) */
			if ($download == 'yes')
				header('Content-disposition: attachment; filename=' . $filename);

			/* Dump file contents */
			echo($file_contents);
		}
	}
}
