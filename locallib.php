<?php

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");
require_once('localfuncs.php');

class enrol_datia_enrol_form extends moodleform
{
    protected $instance;

    /**
     * Overriding this function to get unique form id for multiple datia enrolments
     *
     * @return string form identifier
     */
    protected function get_form_identifier() {
        $formid = $this->_customdata->id.'_'.get_class($this);
        return $formid;
    }		

    public function definition() {
        global $USER, $DB, $COURSE;

        $mform = $this->_form;
        $instance = $this->_customdata;
        $this->instance = $instance;
        $plugin = enrol_get_plugin('datia');

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', $this->instance->courseid);

        $mform->addElement('hidden', 'instance');
        $mform->setType('instance', PARAM_INT);
        $mform->setDefault('instance', $this->instance->id);
				
        if ($instance->password) {
            $heading = $plugin->get_instance_name($instance);
            $mform->addElement('header', 'datiaheader', $heading);
            //change the id of datia enrolment key input as there can be multiple datia enrolment methods
            $mform->addElement('passwordunmask', 'enrolpassword', get_string('password', 'enrol_datia'),
                    array('id' => $instance->id."_enrolpassword"));
        } else {
            // nothing?
        }				

				$mform->addElement('header', '', get_string('paymentinfo', 'enrol_datia'), '');
				$mform->addElement('static', 'description', get_string('paymentcourse', 'enrol_datia'), $COURSE->fullname);
				//$mform->addElement('static', 'description', get_string('cost', 'enrol_datia'), $instance->cost . ' ' . $instance->currency);
														

        $firstlastnamestr = get_string('nameoncard', 'enrol_datia');
				$mform->addElement('header', '', $firstlastnamestr, '');
				
        $mform->addElement('text', 'firstname', get_string('firstnameoncard', 'enrol_datia'), 'size="16"');
        $mform->addElement('text', 'lastname', get_string('lastnameoncard', 'enrol_datia'), 'size="16"');
        $mform->addRule('firstname', get_string('missingfirstname'), 'required', null, 'client');
        $mform->addRule('lastname', get_string('missinglastname'), 'required', null, 'client');
        $mform->setType('firstname', PARAM_ALPHANUM);
        $mform->setType('lastname', PARAM_ALPHANUM);
        $mform->setDefault('firstname', $USER->firstname);
        $mform->setDefault('lastname', $USER->lastname);

				$mform->addElement('passwordunmask', 'cc', get_string('ccno', 'enrol_datia'), 'size="20"');
				$mform->setType('cc', PARAM_ALPHANUM);
				$mform->setDefault('cc', '');
				$mform->addRule('cc', get_string('missingcc', 'enrol_datia'), 'required', null, 'client');
				$mform->addRule('cc', get_string('ccinvalid', 'enrol_datia'), 'numeric', null, 'client');

				$monthsmenu = array('' => get_string('choose'));
				for ($i = 1; $i <= 12; $i++) {
						$monthsmenu[$i] = userdate(gmmktime(12, 0, 0, $i, 15, 2000), "%B");
				}
				$nowdate = getdate();
				$startyear = $nowdate["year"] - 1;
				$endyear = $startyear + 20;
				$yearsmenu = array('' => get_string('choose'));
				for ($i = $startyear; $i < $endyear; $i++) {
						$yearsmenu[$i] = $i;
				}
				$mform->addElement('select', 'ccexpiremm', get_string('expiremonth', 'enrol_datia'), $monthsmenu);
				$mform->addElement('select', 'ccexpireyyyy', get_string('expireyear', 'enrol_datia'), $yearsmenu);
				$mform->addRule('ccexpiremm', get_string('missingccexpiremonth', 'enrol_datia'), 'required', null, 'client');
				$mform->addRule('ccexpireyyyy', get_string('missingccexpireyear', 'enrol_datia'), 'required', null, 'client');
				$mform->setType('ccexpiremm', PARAM_INT);
				$mform->setType('ccexpireyyyy', PARAM_INT);
				$mform->setDefault('ccexpiremm', '');
				$mform->setDefault('ccexpireyyyy', '');

				$creditcardsmenu = array('' => get_string('choose')) + get_list_of_creditcards();
				$mform->addElement('select', 'cctype', get_string('cctype', 'enrol_datia'), $creditcardsmenu);
				$mform->setType('cctype', PARAM_ALPHA);
				$mform->addRule('cctype', get_string('missingcctype', 'enrol_datia'), 'required', null, 'client');
				$mform->setDefault('cctype', '');

				$mform->addElement('text', 'cvv', get_string('ccvv', 'enrol_datia'), 'size="4"');
				$mform->setType('cvv', PARAM_ALPHANUM);
				$mform->setDefault('cvv', '');
				$mform->addRule('cvv', get_string('missingcvv', 'enrol_datia'), 'required', null, 'client');
				$mform->addRule('cvv', get_string('missingcvv', 'enrol_datia'), 'numeric', null, 'client');

				$mform->addElement('header', '', get_string('address'), '');
				$mform->addElement('text', 'ccaddress', get_string('address'), 'size="30"');
				$mform->setType('ccaddress', PARAM_ALPHANUM);
				$mform->setDefault('ccaddress', '');
				$mform->addRule('ccaddress', get_string('missingaddress', 'enrol_datia'), 'required', null, 'client');

				$mform->addElement('text', 'cccity', get_string('cccity', 'enrol_datia'), 'size="14"');
				$mform->addElement('text', 'ccstate', get_string('ccstate', 'enrol_datia'), 'size="8"');
				$mform->addRule('cccity', get_string('missingcity'), 'required', null, 'client');
				$mform->setType('cccity', PARAM_ALPHANUM);
				$mform->setType('ccstate', PARAM_ALPHANUM);
				$mform->setDefault('cccity', '');
				$mform->setDefault('ccstate', '');

				$mform->addElement('select', 'cccountry', get_string('country'), get_string_manager()->get_list_of_countries());
				$mform->addRule('cccountry', get_string('missingcountry'), 'required', null, 'client');
				$mform->setType('cccountry', PARAM_ALPHA);
				$mform->setDefault('cccountry', $USER->country);

        $mform->addElement('text', 'cczip', get_string('zipcode', 'enrol_datia'), 'size="5"');
        $mform->setType('cczip', PARAM_ALPHANUM);
        $mform->setDefault('cczip', '');
        $mform->addRule('cczip', get_string('missingzip', 'enrol_datia'), 'required', null, 'client');
		
				$mform->addElement('header', '', 'Are you a DATIA member?', '');
       // $mform->addElement('text', 'memberornot', get_string('memberornot', 'enrol_datia'), 'size="16"');
       // $mform->setType('memberornot', PARAM_ALPHANUM);
        $mform->setDefault('memberornot', '');
		
		$radioarray=array();
		$radioarray[] =& $mform->createElement('radio', 'memberornot', '', get_string('yes'), 1, $attributes);
		$radioarray[] =& $mform->createElement('radio', 'memberornot', '', get_string('no'), 0, $attributes);
		$mform->addGroup($radioarray, 'radioar', '', array(' '), false);
		
				//$mform->addRule('memberornot', get_string('missingmemberornot', 'enrol_datia'), 'required', null, 'client');

				
				$mform->addElement('header', '', 'Extra Info', '');
        $mform->addElement('text', 'franchisecode', get_string('franchisecode', 'enrol_datia'), 'size="16"');
        $mform->setType('franchisecode', PARAM_ALPHANUM);
        $mform->setDefault('franchisecode', '');
		
        $mform->addElement('text', 'promodiscode', get_string('promodiscode', 'enrol_datia'), 'size="16"');
        $mform->setType('promodiscode', PARAM_ALPHANUM);
        $mform->setDefault('promodiscode', '');
		
		

		
		
		
		$mform->addElement('header', '', 'REFUND/CREDIT POLICY: Once the course has been registered for and login information provided, there are no refunds.<BR>In addition, login information cannot be transferred to another person.
', '');
        $mform->addElement('checkbox', 'refundterms', get_string('refundterms', 'enrol_datia'));
        $mform->setDefault('refundterms', '');
		$mform->addRule('refundterms', get_string('missingrefundterms', 'enrol_datia'), 'required', null, 'client');

        $this->add_action_buttons(false, get_string('sendpaymentbutton', 'enrol_datia'));
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        $plugin = enrol_get_plugin('datia');
				
				if (!in_array($data['cctype'], array_keys(get_list_of_creditcards()))) {
						$errors['cctype'] = get_string('missingcctype', 'enrol_datia');
				}

				$expdate = sprintf("%02d", intval($data['ccexpiremm'])) . $data['ccexpireyyyy'];
				$validcc = $this->validate_cc($data['cc'], $data['cctype'], $expdate);
				if (!$validcc) {
						if ($validcc === 0) {
								$errors['ccexpiremm'] = get_string('ccexpired', 'enrol_datia');
						}
						else {
								$errors['cc'] = get_string('ccinvalid', 'enrol_datia');
						}
				}

				if ($plugin->get_config('an_authcode') && !empty($data['haveauth']) && empty($data['ccauthcode'])) {
						$errors['ccauthgrp'] = get_string('missingccauthcode', 'enrol_datia');
				}

        return $errors;
    }

    private function validate_aba($aba)
    {
        if (preg_match("/^[0-9]{9}$/", $aba)) {
            $n = 0;
            for($i = 0; $i < 9; $i += 3) {
                $n += (substr($aba, $i, 1) * 3) + (substr($aba, $i + 1, 1) * 7) + (substr($aba, $i + 2, 1));
            }
            if ($n != 0 and $n % 10 == 0) {
                return true;
            }
        }
        return false;
    }

    private function validate_cc($Num, $Name = "n/a", $Exp = "")
    {
        // Check the expiration date first
        if (strlen($Exp))
        {
            $Month = substr($Exp, 0, 2);
            $Year  = substr($Exp, -2);
            $WorkDate = "$Month/01/$Year";
            $WorkDate = strtotime($WorkDate);
            $LastDay  = date("t", $WorkDate);
            $Expires  = strtotime("$Month/$LastDay/$Year 11:59:59");
            if ($Expires < time()) return 0;
        }

        //  Innocent until proven guilty
        $GoodCard = true;

        //  Get rid of any non-digits
        $Num = preg_replace("/[^0-9]~/", "", $Num);

        // Perform card-specific checks, if applicable
        switch ($Name)
        {
            case "mcd" :
                $GoodCard = preg_match("/^5[1-5].{14}$/", $Num);
                break;

            case "vis" :
                $GoodCard = preg_match("/^4.{15}$|^4.{12}$/", $Num);
                break;

            case "amx" :
                $GoodCard = preg_match("/^3[47].{13}$/", $Num);
                break;

            case "dsc" :
                $GoodCard = preg_match("/^6011.{12}$/", $Num);
                break;

            case "dnc" :
                $GoodCard = preg_match("/^30[0-5].{11}$|^3[68].{12}$/", $Num);
                break;

            case "jcb" :
                $GoodCard = preg_match("/^3.{15}$|^2131|1800.{11}$/", $Num);
                break;

            case "dlt" :
                $GoodCard = preg_match("/^4.{15}$/", $Num);
                break;

            case "swi" :
                $GoodCard = preg_match("/^[456].{15}$|^[456].{17,18}$/", $Num);
                break;

            case "enr" :
                $GoodCard = preg_match("/^2014.{11}$|^2149.{11}$/", $Num);
                break;
        }

        // The Luhn formula works right to left, so reverse the number.
        $Num = strrev($Num);
        $Total = 0;

        for ($x=0; $x < strlen($Num); $x++)
        {
            $digit = substr($Num, $x, 1);

            // If it's an odd digit, double it
            if ($x/2 != floor($x/2)) {
                $digit *= 2;

                // If the result is two digits, add them
                if (strlen($digit) == 2)
                $digit = substr($digit, 0, 1) + substr($digit, 1, 1);
            }
            // Add the current digit, doubled and added if applicable, to the Total
            $Total += $digit;
        }

        // If it passed (or bypassed) the card-specific check and the Total is
        // evenly divisible by 10, it's cool!
        return ($GoodCard && $Total % 10 == 0);
    }
}

