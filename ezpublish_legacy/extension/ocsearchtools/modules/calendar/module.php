<?php


$Module = array( 'name' => 'Calendar',
                 'variable_params' => true );

$ViewList = array();

$ViewList['view'] = array( 'functions' => array( 'view' ),
                           'script' => 'view.php',
                           'params' => array( 'NodeID' ) );

$ViewList['search'] = array( 'functions' => array( 'view' ),
                           'script' => 'search.php',
                           'params' => array( 'ContextIdentifier' ) );

$FunctionList['view'] = array();




?>
