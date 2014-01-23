<?php

App::uses('AppController', 'Controller');

class SamplesController extends AppController {
	
	public $viewClass = 'Vcf';
	
	//test vcf folder and manual created vcard
	public function test1() {
		$personal = array(
			'name' => array(
				'givenName' => 'John',
				'middleName' => 'Middel',
				'familyName' => 'Doe'
			),
			'gender'=>'M',
			'birthday'=> '1970-01-01',
			'email' => 'asd@asd.com',
			'telephone' => array(
				'number' => '0126/123456',
				'type' => array('work', 'voice', 'asd', 'qwe')
			),
			'address' => array(
				'streetAddress' => 'SomeStreet',
				'addressCountry' => 'USA',
				'addressLocality' => 'Somestate',
				'addressRegion' => 'SS',
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