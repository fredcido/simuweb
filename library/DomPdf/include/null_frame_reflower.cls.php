<?php
/**
 * @package dompdf
 * @link    http://www.dompdf.com/
 * @author  Benj Carson <benjcarson@digitaljunkies.ca>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 * @version $Id: null_frame_reflower.cls.php 631 2012-05-21 14:54:17Z fred $
 */

/**
 * Dummy reflower
 *
 * @access private
 * @package dompdf
 */
class Null_Frame_Reflower extends Frame_Reflower {

  function __construct(Frame $frame) { parent::__construct($frame); }

  function reflow(Frame_Decorator $block = null) { return; }
  
}
