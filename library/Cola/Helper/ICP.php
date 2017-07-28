<?php 
/**
 * cola php 框架里面使用
 * 放置于Cola/Helper/ICP.php
 * Cola_Helper_ICP::log2ICP ( 'login', $log );
 */
class Cola_Helper_ICP {
	/**
	 * cola php框架使用方式
	 *
	 * @see function log2ICP($type, array $log)
	 */
	public static function log2ICP($type, $log) {
		$log = formatICPLog ( $type, $log );
		if (empty ( $log )) {
			return false;
		}
	
		$logid = 'FOR_ICP_LOGS'; // 使用一个标识来区分log，简化syslog配置
		$log = $logid . implode( "\t", $log );
		openlog( $type, LOG_PID, LOG_LOCAL6 );
		$result = syslog( LOG_INFO, $log );
	
		if (empty ( $result )) {
			throw new Exception( 'ICP log fail' );
		}
		return $result;
	}
}
function formatICPLog($type, $log) {
	// ICP 各业务字段定义，标识为“1”的表示必填字段
	$ICPconfig = array (
			// 用户注册
			'register' => array (
					'ICP_CODE' => '11010513200700',
					'DATA_TYPE' => 'PASSPORT',
					'USER_ID' => '1',
					'USER_NAME' => '',
					'PASSWORD' => '',
					'NICK_NAME' => '',
					'STATUS' => '1',
					'REAL_NAME' => '',
					'SEX' => '',
					'BIRTHDAY' => '',
					'CONTACT_TEL' => '',
					'CERTIFICATE_TYPE' => '',
					'CERTIFICATE_CODE' => '',
					'BIND_TEL' => '',
					'BIND_QQ' => '',
					'BIND_MSN' => '',
					'EMAIL' => '',
					'REGISTER_TIME' => '1',
					'LAST_LOGIN_TIME' => '',
					'LAST_CHANGE_PASSWORD' => '',
					'LAST_MODIFY_TIME' => '',
					'REGISTER_IP' => '',
					'LAST_LOGIN_IP' => '',
					'REGISTER_MAC' => '',
					'REGISTER_BIOS_ID' => '',
					'PROVINCE' => '',
					'CITY' => '',
					'ADDRESS' => '' 
			),
			// 用户登陆
			'login' => array (
					'ICP_CODE' => '11010513200700',
					'DATA_TYPE' => 'PASSPORT',
					'SRC_IP' => '1',
					'SRC_PORT' => '',
					'DST_IP' => '',
					'DST_PORT' => '',
					'USER_ID' => '1',
					'USER_NAME' => '',
					'NICK_NAME' => '',
					'PASSWORD' => '',
					'MAC_ADDRESS' => '',
					'INNER_IP' => '',
					'ACTION_TIME' => '1',
					'ACTION' => '1',
					'LONGITUDE' => '',
					'LATITUDE' => '',
					'TERMINAL_TYPE' => '',
					'OS_TYPE' => '',
					'STATION_ID' => '',
					'COMMUNITY_CODE' => '',
					'IMEI_CODE' => '',
					'IMSI_CODE' => '',
			),
			// 观看以及评论视频
			'access' => array (
					'ICP_CODE' => '11010513200781',
					'DATA_TYPE' => 'VIDEO',
					'SRC_IP' => '1',
					'SRC_PORT' => '',
					'CORP_SITE' => '',
					'CORP_LOGIN_ACCOUNT' => '',
					'ACTION_TIME' => '1',
					'LONGITUDE' => '',
					'LATITUDE' => '',
					'TERMINAL_TYPE' => '',
					'OS_TYPE' => '',
					'OS_LANG' => '',
					'SOFT_TYPE' => '',
					'USER_ID' => '1',
					'USER_NAME' => '',
					'URL' => '',
					'ACTION' => '1',
					'TITLE' => '',
					'DESCRIPTION' => '',
					'V_ID' => '1',
					'CHANNEL' => '',
					'ALBUM' => '',
					'ORIGIN' => '',
					'CONTENT' => '' 
			) 
	);
	
	if (! isset ( $ICPconfig [$type] )) {
		return array ();
	}
	
	$data = $ICPconfig [$type];
	foreach ( $data as $key => $value ) {
		if (($value == '1') && ! (isset ( $log [$key] ) && $log [$key])) { // 必填字段缺失
			throw new Exception ( 'Required field lost' );
		}
		
		$data [$key] = (isset ( $log [$key] ) && $log [$key]) ? $log [$key] : $value;
	}
	
	return $data;
}
?>