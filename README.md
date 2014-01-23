#VCF- View v1.0.0 for CakePHP 2.3+

A view class that is used for vCard (.vcf) responses.

## Table of Contents  
[Installation](#install)  
[Usage](#usage)  
[VcfView API](#api)  
[License](#license) 

<a name="install"/>
## Installation
1. Download or clone this repository and paste in `app/View`
2. Small SamplesController and SamplesView in `test` Folder
3. Done  
[Top](#vcf--view-v100-for-cakephp-23)

<a name="usage"/>
## Usage
There are two ways:

The first way is as simple as CakePHPs JSON and XML views by setting the '_serialize' key in your controller.
Thus you can specify a view variable that should be used
for VCF and used as the response for the request.

In your controller you could do the following:

```php
//app/Controller/UsersController

public function sendVcf(){
  //setting up the viewClass
  $this->viewClass = 'Vcf';
	
	//vCard Entries
	$vcfdata = array(
	  'name' => array(
				'givenName' => 'John',
				'middleName' => 'Middle',
				'familyName' => 'Doe'
			),
	  'gender'=>'M'
	);
	
	$this->set(compact('vcfdata'));
	$this->set('_serialize','vcfdata');
}

```

Response (here as text): 

```
BEGIN:VCARD
VERSION:4
PROFILE:VCARD
REV:140122T175916Z
SOURCE:http://example.com/project/users/sendVcf
N:Doe;John;Middle;;
FN:John Middle Doe
GENDER:M
END:VCARD
```


The second way is to create a view file in `app/View/vcf/`

```php
//app/Controller/UserController
public function sample(){
  //setting up the viewClass
  $this->viewClass = 'Vcf';
  
  //vCard Entries
	$jondoesdata = array(
	  'name' => array(
				'givenName' => 'Unknown',
				'middleName' => 'Middle',
				'familyName' => 'Artist',
				'honorificPrefix'=>'Prof. Dr.',
				'honorificSuffix' => 'M.D.'
			),
	  'gender'=>'M',
	  'bithday' => '1970-01-01',
	  'telephone' => array(
	    'number' => '0126/123456',
			'type' => array('work','home')
	  )
	);
	
  $this->set(compact('jondoesdata'));
}
```
```php
//app/View/vcf/sample.ctp
<?php
  //get viewVars['jondoesdata']
  $jondowsdata = $this->get('jondoesdata');
  
  //creating vCard
  $vcard = '';
  $vcard .= $this->start('VCF_V40');
  $vcard .= $this->name($jondowsdata['name']);
  $vcard .= $this->gender($jondowsdata['gender']);
  $vcard .= $this->telephone($jondowsdata['telephone']);
  $vcard .= $this->birthday($jondowsdata['birthday']);
  $vcard .= $this->end();
  print $vcard;

```
Response (here as text): 
```
BEGIN:VCARD
VERSION:4
PROFILE:VCARD
REV:140122T175916Z
SOURCE:http://example.com/project/users/sample
N:Doe;John;Middle;Prof.Dr.;M.D.
FN: Prof. Dr. Unknown Middle Artist M.D.
GENDER:M
TEL;TYPE=work, home, voice:0126/123456
BDAY:1970-01-01
END:VCARD
```

[Top](#vcf--view-v100-for-cakephp-23)


<a name="api"/>
## VcfView API

`class` VcfView

VcfView methods are available in all views when the viewClass property was defined in controller or action.

Example:

```php
class SomesController extends AppController{
	// defining viewClass here, or in action
	public $viewClass = 'Vcf';
	
	public function index(){
		//funny code 
		$this->viewClass = 'Vcf';
		//....
	}
	
}
```

All vCard elements in `VcfView::$_elements` property could be called by their name.

Example:

```php 
//app/View/Somes/vcf/index.ctp
//VcfView::$_elements['language']
print $this->language('de');//"LANG:de"

```

`constant` VcfView::VCF_V21  
   >vCard Version 2.1

`constant` VcfView::VCF_V30  
   >vCard Version 3.0

`constant` VcfView::VCF_V40  
   >vCard Version 4.0  
   
VcfView::**start**( *constant* $version = VcfView::VCF_V40 ); 
   >Start of a vCard Block
   
VcfView::**end**( *void* ); 
   >End of vCard Block

`protected` `property` VcfView::$_elements  
   > contains all available vCard elements

[Top](#vcf--view-v100-for-cakephp-23)
