<?php

namespace MediaWiki\Extension\BoilerPlate\Maintenance;

use Maintenance;

$IP = getenv( 'MW_INSTALL_PATH' );
if ( $IP === false ) {
	$IP = __DIR__ . '/../../..';
}
require_once "$IP/maintenance/Maintenance.php";

class BoilThePlate extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->requireExtension( 'BoilerPlate' );
	}

	public function execute() {
		$this->output( "BoilerPlate was here.\n" );
	}
}

$maintClass = BoilThePlate::class;
require_once RUN_MAINTENANCE_IF_MAIN;
