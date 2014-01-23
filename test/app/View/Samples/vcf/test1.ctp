<?php
$personal = $this->get('personal');
$vcard = '';
$vcard .= $this->start('VCF_V40');
$vcard .= $this->name($personal['name']);
$vcard .= $this->gender($personal['gender']);
$vcard .= $this->telephone($personal['telephone']);
$vcard .= $this->birthday($personal['birthday']);
$vcard .= $this->address($personal['address']);
$vcard .= $this->email($personal['email']);
$vcard .= $this->uid();
$vcard .= $this->end();
print $vcard;
