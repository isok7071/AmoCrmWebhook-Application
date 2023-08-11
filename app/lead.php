<?php
require 'vendor/autoload.php';

use Src\Controllers\CRM\LeadController;

if ($_POST) {
    if ($_POST['leads']['add']) {
        $lead = new LeadController();
        $lead->onLeadCreate($_POST['leads']['add'][0]['id']);
    }

    if ($_POST['leads']['update']) {
        $lead = new LeadController();
        $lead->onLeadUpdate($_POST['leads']['update'][0]['id']);
    }
}

$lead = new LeadController();
dd($lead->getLeadFields(44671863));