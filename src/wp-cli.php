<?php

namespace Clockwork_For_Wp;

use Clockwork_For_Wp\Wp_Cli\Clean_Command;
use Clockwork_For_Wp\Wp_Cli\Cli_Collection_Helper;
use Clockwork_For_Wp\Wp_Cli\Generate_Command_Lists_Command;

use function Clockwork_For_Wp\Wp_Cli\add_command;

// @todo Seems like a pretty fragile implementation for collecting commands... Likely going to need a lot of work.

add_command( new Clean_Command() );
add_command( new Generate_Command_Lists_Command() );

$cli = new Cli_Collection_Helper();
$cli->initialize_logger();

_cfw_instance()[ Cli_Collection_Helper::class ] = $cli;
