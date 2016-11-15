<?php
/**
 * Extends UserDefinedForm
 */
class DPC_UserDefinedForm_Extension extends DataExtension{
	/**
	 * @return array
	 */
	private static $db = array(
	    'EnableAutoresponder'    => 'Boolean',
		'AutoresponderFromName'  => 'Varchar(255)',
		'AutoresponderFromEmail' => 'Varchar(255)',
		'AutoresponderToEmail'   => 'Varchar(255)',
		'AutoresponderSubject'   => 'Varchar(900)',
		'AutoresponderContent'   => 'HTMLText'
	);
	
	/**
	 * Update CMS fields
	 *
	 * @return FieldList
	 */
	public function updateCMSFields(FieldList $fields){
		// Get recipient emails
		$emails = array('' => _t('UserDefinedForm.SelectAutoresponderEmail', '- select -') );
		
		if( $this->owner->Fields() ){
			foreach( $this->owner->Fields() as $field ){
				if($field instanceof EditableEmailField){
					$emails[$field->Name] = $field->Title;
				}
			}
		}
		
		// Add new tab
		$fields->insertAfter( ($tab = new Tab('TabAutoresponder', _t('UserDefinedForm.TabLabelAutoresponder', 'Autoresponder'))), 'Submissions');
		
		$tab->push( new CheckboxField('EnableAutoresponder', _t('UserDefinedForm.EnableAutoresponder', 'Enable autoresponder')) );
		$tab->push( new EmailField('AutoresponderFromEmail', _t('UserDefinedForm.AutoresponderFromEmail', 'From email')) );
		$tab->push( $toEmail = new DropdownField('AutoresponderToEmail', _t('UserDefinedForm.AutoresponderToEmail', 'To email'), $emails) );
		$tab->push( new TextField('AutoresponderSubject', _t('UserDefinedForm.AutoresponderSubject', 'Email subject')) );
		$tab->push( new HtmlEditorField('AutoresponderContent', _t('UserDefinedForm.AutoresponderContent', 'Email content')) );
		
		$toEmail->setDescription( _t('UserDefinedForm.AutoresponderToEmailDesc', 'Select a email field to use as the recipient address.') );
		
		// Email variables
		if( $this->owner->Fields() )
		{
			$html  = '<div style="padding: 15px"><ul>';
			
			foreach( $this->owner->Fields() as $field ){
				if( $field->showInReports() ){
					$html .= '<li style="padding: 0 0 10px"><strong>['.$field->Name.']</strong> '._t('UserDefinedForm.ToDisplayValueOf', 'to display value of').' <strong>'.$field->Title.'</strong></li>';
				}
			}
			
			$html .= '</ul></div>';
			
			$tab->push(
				ToggleCompositeField::create(
					'EmailTags',
					_t('UserDefinedForm.AutoresponderTagsDesc', 'Click to show tags which can be used in subject and content.'),
					array( LiteralField::create('EmailTagsHtml', $html) )
				)->setHeadingLevel(4)
			);
		}
	}
	
	/**
	 * On before write
	 */
	public function onBeforeWrite(){
		parent::onBeforeWrite();
		
		// Validate inputs
		if( $this->owner->EnableAutoresponder ){
			// Email
			if( !Email::is_valid_address($this->owner->AutoresponderFromEmail) )
			    throw new ValidationException(ValidationResult::create(false, _t('UserDefinedForm.AutoresponderInvalidEmail', 'Autoresponder: From Email is not a valid email address.')));
			
			// Subject
			if( trim($this->owner->AutoresponderSubject) == '' )
			    throw new ValidationException(ValidationResult::create(false, _t('UserDefinedForm.AutoresponderEmptySubject', 'Autoresponder: Subject is required.')));
		}
	}
}
