<?php
/**
 * Extends SubmittedForm
 */
class DPC_SubmittedForm_Extension extends DataExtension{	
	/**
	 * Autoresponder
	 */
	public function updateAfterProcess(){
        // get userforms page
        $page = $this->owner->Parent();
        
        // send email
		if(
           $page->exists()
		   && $page->EnableAutoresponder
		   && $page->AutoresponderFromEmail
		   && Email::is_valid_address($page->AutoresponderFromEmail)
           && $page->AutoresponderToEmail
		   && $page->AutoresponderSubject
		   && $page->AutoresponderContent
		   && $this->owner->Values()->exists()
		)
		{
			$fields    = $this->owner->Values();
			$subject   = $page->AutoresponderSubject;
			$content   = $page->AutoresponderContent;
			$recipient = null;
			
			// replace tags
			foreach($fields as $field){
				$subject = str_replace('['.$field->Name.']', $field->Value, $subject);
				$content = str_replace('['.$field->Name.']', $field->Value, $content);
				
				if($field->Name == $page->AutoresponderToEmail){
					$recipient = $field->Value;
				}
			}
			
			// send email
			if( $recipient && Email::is_valid_address($recipient) )
			{
				$email = new Email();
				$email->setFrom($page->AutoresponderFromEmail)
					  ->setTo($recipient)
					  ->setSubject($subject)
					  ->setBody($content);
                      
				// do send mail
				$email->send();
			}
		}
	}
}