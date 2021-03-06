<?php
/**
 * @package dompdf
 * @link    http://www.dompdf.com/
 * @author  Benj Carson <benjcarson@digitaljunkies.ca>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 * @version $Id: positioner.cls.php 631 2012-05-21 14:54:17Z fred $
 */

/**
 * Base Positioner class
 *
 * Defines postioner interface
 *
 * @access private
 * @package dompdf
 */
abstract class Positioner {
  
  /**
   * @var Frame_Decorator
   */
  protected $_frame;
  
  //........................................................................

  function __construct(Frame_Decorator $frame) {
    $this->_frame = $frame;
  }
  
  /**
   * Class destructor
   */
  function __destruct() {
    clear_object($this);
  }
  //........................................................................

  abstract function position();
  
  function move($offset_x, $offset_y, $ignore_self = false) {
    list($x, $y) = $this->_frame->get_position();
    
    if ( !$ignore_self ) {
      $this->_frame->set_position($x + $offset_x, $y + $offset_y);
    }
    
    foreach($this->_frame->get_children() as $child) {
      $child->move($offset_x, $offset_y);
    }
  }
}
