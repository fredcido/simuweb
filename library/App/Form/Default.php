<?php

class App_Form_Default extends Zend_Form
{
    /**
     *
     * @var <array>
     */
    protected $_elementsForm = array();

    /**
     *
     * @var <array>
     */
    protected $_buttons = array();
    
    /**
     *
     * @var array
     */
    protected $_toolbarDecorator = array(
					'FormElements',
					array( array( 'wrapper' => 'HtmlTag' ), array( 'tag' => 'div', 'class' => 'form-actions' ) )
				    );

    /**
     * 
     * @param array $options
     */
    public function __construct( $options = null )
    {
	$name = App_General_String::friendName( get_class( $this ) );
	$options['name'] = $name;
	
	parent::__construct( $options );
    }
    
    /**
     *
     * @return <array>
     */
    public function getDefaultElementDecorators()
    {
            return array( 
			'ViewHelper',
			'Errors',
			array( array( 'wrapper' => 'HtmlTag' ), array( 'tag' => 'div', 'class' => 'controls' ) ),
			array( 'Label', array( 'class' => 'control-label' ) ),
			array( array( 'row' => 'HtmlTag' ), array( 'tag' => 'div', 'class' => 'control-group' ) )
		    );
    }
    
    /**
     *
     * @return <array>
     */
    public function getDefaultFormDecorators()
    {
            return array(
			'FormElements',
			'Form'
		    );
    }

    /**
     *
     * @return <array>
     */
    public function getDefaultButtonDecorators()
    {
        return array(
		    'ViewHelper',
		    array( array( 'wrapper' => 'HtmlTag' ), array( 'tag' => 'li', 'class' => 'buttons' ) )
		);
    }
}
