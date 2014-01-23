<?php

App::uses('AppController', 'Controller');

class SamplesController extends AppController {
	
	public $viewClass = 'Vcf';
	
	//test vcf folder and manual created vcard
	public function test1() {
		$personal = array(
			'name' => array(
				'givenName' => 'Alexander',
				'middleName' => 'Magnus',
				'familyName' => 'Lang'
			),
			'gender'=>'M',
			'birthday'=> '1987-07-30',
			'email' => 'asd@asd.com',
			'telephone' => array(
				'number' => '0126/123456',
				'type' => array('work', 'voice', 'asd', 'qwe')
			),
			'address' => array(
				'streetAddress' => '20 Lincoln Center',
				'addressCountry' => 'USA',
				'addressLocality' => 'New York',
				'addressRegion' => 'NY',
				'postalCode' => '10023'
			),
		);
		$this->set(compact('personal'));
	}
	
	public function test2(){
		$this->test2();
		$this->set('_serialize', 'personal');
	}
	
}