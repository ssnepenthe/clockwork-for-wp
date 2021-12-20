<?php

namespace Cfw_Test_Helper;

function obnoxious_warning() {
	?>

	<p><strong>Warning</strong>: The CFW Test Helper plugin is currently active.</p>
	<p>This plugin is designed to give complete control over the Clockwork For WP Plugin configuration in order to facilitate end to end testing.</p>
	<p>It should only be active on a development machine while you are testing the Clockwork For WP plugin.</p>
	<p>If that is not currently the case, please deactivate this plugin immediately.</p>

	<?php
}

function obnoxious_admin_warning() {
	?>

	<div class="notice notice-warning">
		<?php obnoxious_warning(); ?>
	</div>

	<?php
}

function obnoxious_frontend_warning() {
	// Styles roughly mimic the appearance of admin notices... Only tested against twentytwentyone theme.
	?>

	<style>
		.cfwth-notice {
			background-color: #fff;
			border: 1px solid #c3c4c7;
			border-left-color: #dba617;
			border-left-width: 4px;
			margin: 1.25rem;
			padding: 0.75rem;
		}
	</style>

	<div class="cfwth-notice">
		<?php obnoxious_warning(); ?>
	</div>

	<?php
}
