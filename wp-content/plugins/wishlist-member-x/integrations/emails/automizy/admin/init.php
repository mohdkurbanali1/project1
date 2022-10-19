<?php
$data          = $ar_data[ $config['id'] ];
$data['tags']  = array();
$data['lists'] = array();
thirdparty_integration_data( $config['id'], $data );
