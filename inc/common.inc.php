<?php

	if (!function_exists("get_context_instance")) die('Invalid access');

	function is_admin() {
		$context = get_context_instance(CONTEXT_SYSTEM);
		$roles = get_user_roles($context, $USER->id, false);
		$role = key($roles);
		$roleid = $roles[$role]->roleid;

		return $roleid;
	}