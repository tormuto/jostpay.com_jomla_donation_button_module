<?php
/** 
 * @author JostPay Limited
 * @email support@jostpay.com
 * @package Wp JostPay
 * @copyright (C) 2016 - JostPay - All rights reserved
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
**/

// Check to ensure this file is included in Joomla!
defined ( '_JEXEC' ) or die ( 'Restricted' );

/**
 * @ Wp JostPay
 * @ Version 1.0
**/ 
 
jimport ( 'joomla.plugin.plugin' );
class plgContentWp_JostPay_Donation extends JPlugin { 
	
	public function onContentAfterDisplay($context,&$article,&$params,$page=0){ 
		
		// Start Joomla variables
		
		$app = JFactory::getApplication();
		$doc = JFactory::getDocument();
		$docType = $doc->getType();
		// $lang = JFactory::getLanguage();
		// $lang->	load('plg_content_wp_instapagseguro', JPATH_ADMINISTRATOR);
		
		// ...
		
		if ($app->isAdmin()||JRequest::getCmd('task')=='edit'||JRequest::getCmd('layout')=='edit'){
			return;
		}
		
		$matches = array();
		$overrides = array();

		if(!isset($article->text)){
			$article->text = &$article->introtext;
		}
		
		if (strcmp("html", $docType)!=0) {
			$article->text = preg_replace("/{wpjostpaydonation}(.*?){\/wpjostpaydonation}/i",'',$article->text);
			return;
		}

		if(JRequest::getCmd('print')){
			$article->text = preg_replace("/{wpjostpaydonation}(.*?){\/wpjostpaydonation}/i",'',$article->text);
			return;
		}
		
		preg_match_all('/{wpjostpaydonation}(.*?){\/wpjostpaydonation}/',$article->text,$matches,PREG_PATTERN_ORDER);
		if(count($matches[0])){
			for($i=0;$i<count($matches[0]);$i++){
			
				// Start the extraction of command parameters used by the plugin
				
				$overridesArray = array();
				$overrides = strlen(trim($matches[1][$i])) ? explode( "|",trim($matches[1][$i])) : array(); 
				if(count($overrides)){
					foreach ($overrides as $overrideParam){
						$temp = explode("=",$overrideParam);
						$paramKey = trim($temp[0]);
						$paramVal = trim($temp[1]);
						$overridesArray[$paramKey] = $paramVal;
					}
				}

				 
				$rand = floor(mt_rand(20,1000));
				//$merchant_id = $params->get('merchant_id');
				$payment_button_text = $params->get('payment_button_text');
				$store_id = $params->get('store_id');
				$memo = $params->get('memo');
				
				$notify_url = $params->get('notify_url');
				$success_url = $params->get('success_url');
				$fail_url = $params->get('fail_url');
				$merchant_id = $params->get('merchant_id');
				//$show_price = $params->get('show_price');
				
				$merchant_ref = $this->params->get('merchant_id').' '.date('Y-m-d H:i:s');
				if(!empty($store_id))$merchant_ref.=' '.$this->params->get('merchant_id');
				$merchant_ref.=' '.mt_rand(0,999999);
				
				if(empty($notify_url)) $notify_url=$this->params->get('notify_url');
				if(empty($success_url)) $success_url=$this->params->get('success_url');
				if(empty($fail_url)) $fail_url=$this->params->get('fail_url');
				if(empty($memo)) $memo=$this->params->get('memo');
				if(empty($payment_button_text)) $payment_button_text=$this->params->get('payment_button_text');
				
				
				if(empty($memo))$memo='';
				if(empty($payment_button_text))$payment_button_text='Pay with Mastercard, Visacard, Verve, Perfect Money or Bitcoin (via JostPay)';
				
				
			$f = '<form method="POST" action="https://jostpay.com/sci/" name="jostpaybn_form'.$rand.'">
			<input type="hidden" name="merchant" value="'.$this->params->get('merchant_id').'" />
			<div >
				<label>Enter Amount<label><br />
				<input type="text" name="amount" style="width:120px" /> NGN
			</div><br />
			<input type="hidden" name="ref" value="'.$merchant_ref.'" />';
			
			$f .= "<input type='hidden' name='notification_url' value='$notify_url' />";
			$f .= "<input type='hidden' name='success_url' value='$success_url' />";
			$f .= "<input type='hidden' name='cancel_url' value='$fail_url' />";
			$f .= "<div>
						<label>Memo:</label><br/>
						<textarea name='memo' style='width:200px' >".$memo."</textarea>
					</div><br/>";
			
			//if($show_price == 1) "<div class='label'>Unit Price</div> <div>".$price."<div/>";
			$f .= '<input type="submit" value="'.$payment_button_text.'" />
			</form>';  

			$finalform = $f;
			$previousContent = $matches[1][$i];
			$article->text = $article->introtext = str_replace("{wpjostpaydonation}$previousContent{/wpjostpaydonation}",$finalform,$article->text);
			
			}
		}
		return null;
	}
}