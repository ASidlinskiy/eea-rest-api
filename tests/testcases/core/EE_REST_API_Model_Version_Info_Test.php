<?php
if ( !defined( 'EVENT_ESPRESSO_VERSION' ) ) {
	exit( 'No direct script access allowed' );
}

/**
 *
 * EE_REST_API_Model_Version_Info_Test
 *
 * @package			Event Espresso
 * @subpackage
 * @author				Mike Nelson
 *
 */
class EE_REST_API_Model_Version_Info_Test extends EE_UnitTestCase{
	/**
	 * pretend currently we're on 4.8, but they send in a request for 4.6. So this
	 * shoudl return all the changes from 4.7 and 4.8
	 */
	function test_get_all_model_changes_between_requested_version_and_current__req_46_cur_48() {
		$this->_pretend_current_version_48();
		$model_info = new EE_REST_API_Model_Version_Info( '4.6' );
		$changes = $model_info->model_changes_between_requested_version_and_current();
		$this->assertArrayNotHasKey( '4.6', $changes );
		$this->assertArrayHasKey( '4.7', $changes );
		$this->assertArrayHasKey( '4.8', $changes );
	}

	/**
	 * pretend currently we're on 4.8, but they send in a request for 4.6. So this
	 * shoudl return all the changes from 4.7 and 4.8
	 */
	function test_get_all_model_changes_between_requested_version_and_current__req_47_cur_48() {
		$this->_pretend_current_version_48();
		$model_info = new EE_REST_API_Model_Version_Info( '4.7' );
		$changes = $model_info->model_changes_between_requested_version_and_current();
		$this->assertArrayNotHasKey( '4.7', $changes );
		$this->assertArrayHasKey( '4.8', $changes );
	}

	/**
	 * @group 32
	 */
	function test_get_all_models_for_requested_version__no_registration_payment_model_in_46(){
		//pretend we are at version 4.8, and have the Registration_Payment model
		if( ! isset( EE_Registry::instance()->non_abstract_db_models[ 'Registration_Payment' ] ) ) {
			EE_Registry::instance()->non_abstract_db_models[ 'Registration_Payment' ] = 'EE_Registration_Payment';
			$pretend_got_registration_payment = true;
		}else{
			$pretend_got_registration_payment = false;
		}
		//but the request is for 4.6, where there was no such model
		$this->_pretend_current_version_48();

		$model_info = new EE_REST_API_Model_Version_Info( '4.6' );
		$models = $model_info->models_for_requested_version();
		//cleanup before making an assertion
		if( $pretend_got_registration_payment ) {
			unset( EE_Registry::instance()->non_abstract_db_models[ 'Registration_Payment' ] );
		}
		$this->assertArrayNotHasKey( 'Registration_Payment', $models );
	}

	function test_get_all_models_for_requested_version__has_registration_payment_model_in_47(){
		//pretend we are at version 4.8, and have the Registration_Payment model
		if( ! isset( EE_Registry::instance()->non_abstract_db_models[ 'Registration_Payment' ] ) ) {
			EE_Registry::instance()->non_abstract_db_models[ 'Registration_Payment' ] = 'EE_Registration_Payment';
			$pretend_got_registration_payment = true;
		}else{
			$pretend_got_registration_payment = false;
		}
		//but the request is for 4.6, where there was no such model
		$this->_pretend_current_version_48();

		$model_info = new EE_REST_API_Model_Version_Info( '4.7' );

		$models = $model_info->models_for_requested_version();
		//cleanup before making an assertion
		if( $pretend_got_registration_payment ) {
			unset( EE_Registry::instance()->non_abstract_db_models[ 'Registration_Payment' ] );
		}
		$this->assertArrayHasKey( 'Registration_Payment', $models );
	}

	function test_fields_on_model_in_this_version__no_reg_paid_in_46() {
		$this->_pretend_added_field_onto_registration_model();
		$this->_pretend_current_version_48();
		$model_info = new EE_REST_API_Model_Version_Info( '4.6' );
		$fields_on_reg = $model_info->fields_on_model_in_this_version( EEM_Registration::instance() );
		$this->assertArrayNotHasKey( 'REG_paid', $fields_on_reg );
	}

	function test_fields_on_model_in_this_version__has_reg_paid_in_47() {
		$this->_pretend_added_field_onto_registration_model();
		$this->_pretend_current_version_48();
		$model_info = new EE_REST_API_Model_Version_Info( '4.7' );
		$fields_on_reg = $model_info->fields_on_model_in_this_version( EEM_Registration::instance() );
		$this->assertArrayHasKey( 'REG_paid', $fields_on_reg );
	}

	protected function _pretend_current_version_48(){
		add_filter( 'FHEE__EED_REST_API__core_version', array( $this, '_tell_EED_REST_API_current_version_is_48' ) );
	}

	/**
	 * Used on a filter to make the API think core's version is 4.8
	 * @param type $current_version
	 * @return string
	 */
	public function _tell_EED_REST_API_current_version_is_48( $current_version ) {
		return '4.8';
	}

	protected function _pretend_added_field_onto_registration_model(){
		add_filter( 'FHEE__EEM_Registration__construct__fields', array( $this, '_add_reg_paid_field' ) );
		EEM_Registration::reset();
	}

	public function _add_reg_paid_field( $reg_fields ) {
		if( ! isset( $reg_fields[ 'Registration'][ 'REG_paid' ] ) ) {
			$reg_fields[ 'Registration'][ 'REG_paid' ] = new EE_Money_Field( 'REG_paid', __( 'Amount paid for registration', 'event_espresso' ), true );
		}
		return $reg_fields;
	}
}

// End of file EE_REST_API_Model_Version_Info_Test.php