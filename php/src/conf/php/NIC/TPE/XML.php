<?php // vim: set expandtab ts=4 sw=4 sts=4:
/**
 * NIC XML Conversion Base Class
 *
 * Longer description of script (multi-line)
 *
 * @package NIC_PE
 * @author Trent Bills <tbills@nicusa.com>
 * @version $Id: XML.php,v 1.6 2004/12/07 21:43:33 scottm Exp $
 * @copyright 2001-2004 NIC, Inc. All Rights Reserved http://www.nicusa.com
 *
 * @TODO Complete API Documentation (the doc-blocks that have the keyword 'TODO')
 */

/**TODO
 * Short description of script (one line)
 *
 * Longer description of script (multi-line)
 *
 * @package NIC_PE
 */
class NIC_XML
{
    /**TODO
     * var doc block
     */
    var $name;

    /**TODO
     * var doc block
     */
    var $text;

    /**TODO
     * var doc block
     */
    var $attribs;

    /**TODO
     * var doc block
     */
    var $objs;

    /**TODO
     * Constructor
     *
     * Longer description...
     *
     * @return NIC_XML
     */
    function NIC_XML()
    {
        $this->text    = '';
        $this->attribs = array();
        $this->vars    = array();
        $this->objs    = array();
        $this->objary  = array();
    }

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @param 
     * @return 
     */
    function indent($depth, $pp)
    {
        $sp = '';

        if ($pp) {
            for ($j = 0; $j < $depth; $j++) {
                $sp .= '   ';
            }
        }

        return($sp);
    }

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function linebreak($pp)
    {
        if ($pp) {
            return("\n");
        }

        return('');
    }

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function attributeEncode($attrib)
    {
        $patterns = array('&' => '&amp;', '<' => '&lt;', '>' => '&gt;', '"' => '&quot;');
        $encoded = $attrib;

        foreach ($patterns as $from => $to) {
            $encoded = str_replace($from, $to, $encoded);
        }

        return($encoded);
    }

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function textEncode($text)
    {
        $patterns = array('&' => '&amp;', '<' => '&lt;', '>' => '&gt;');
        $encoded = $text;

        foreach ($patterns as $from => $to) {
            $encoded = str_replace($from, $to, $encoded);
        }

        return($encoded);
    }

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @param 
     * @return 
     */
    function ToXML($pp = false, $depth = 0)
    {
        $s = '';

        if (!isset($this->nodisp)) {
            $s .= $this->indent($depth, $pp);
            $s .= '<' . $this->name;

            foreach ($this->attribs as $name => $value) {
                $s .= ' ' . $name . '="' . $this->attributeEncode($value) . '"';
            }
        }

        if (!count($this->vars) && !count($this->objs) && !count($this->objary) && !strlen($this->text)) {
            if (!isset($this->nodisp)) {
                $s .= '/>';
                $s .= $this->linebreak($pp);
            }
        } else {
            if (!isset($this->nodisp)) {
                $s .= '>';
                $s .= $this->linebreak($pp);
            }

            foreach ($this->vars as $name => $value) {
                $s .= $this->indent($depth + 1, $pp);
                $s .= '<' . $name . '>' . $this->textEncode($value) . '</' . $name . '>';
                $s .= $this->linebreak($pp);
            }

            foreach (array_keys($this->objs) as $key) {
                $obj = &$this->objs[$key];
                $s  .= $obj->ToXML($pp, $depth + 1);
            }

            foreach (array_keys($this->objary) as $name) {
                if (count($this->objary[$name])) {
                    $s .= $this->indent($depth + 1, $pp);
                    $s .= '<' . $name . '>';
                    $s .= $this->linebreak($pp);

                    foreach (array_keys($this->objary[$name]) as $n2) {
                        $obj = &$this->objary[$name][$n2];
                        $s  .= $obj->ToXML($pp, $depth + 2);
                    }

                    $s .= $this->indent($depth + 1, $pp);
                    $s .= '</' . $name . '>';
                    $s .= $this->linebreak($pp);
                }
            }

            if (!isset($this->nodisp)) {
                $s .= $this->indent($depth + 1, $pp);
                $s .= $this->textEncode($this->text);
                $s .= $this->linebreak($pp);
                $s .= $this->indent($depth, $pp);
                $s .= '</' . $this->name . '>';
                $s .= $this->linebreak($pp);
            }
        }

        if (isset($this->nodisp)) {
            unset($this->nodisp);
        }

        return($s);
    }
}

/**TODO
 * Short description of script (one line)
 *
 * Longer description of script (multi-line)
 *
 * @package NIC_PE
 */
class NIC_XMLParse
{
    /**
     * @var 
     */
    var $curvar;

    /**TODO
     * Constructor
     *
     * Longer description...
     *
     * @param 
     * @return NIC_XMLParse
     */
    function NIC_XMLParse($table)
    {
        $this->Tag2Object = $table;
        $this->objary     = array();
        $this->objstack   = array();
        $this->nameary    = array();
        $this->curvar     = null;
    } 

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @return 
     */
    function parse($xml)
    {
        $this->parser = xml_parser_create();
        xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, false);
        xml_set_object($this->parser, $this);
        xml_set_element_handler($this->parser, 'startTag', 'endTag');
        xml_set_character_data_handler($this->parser, 'cData');
        $this->objstack = array();
        $this->objindex = -1;
        $this->curobj = 0;
        $this->numobjects = 0;
        xml_parse($this->parser, $xml, true);
        xml_parser_free($this->parser);
        $ret = &$this->objary[0];
        return($ret);
    } 

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @param 
     * @param 
     * @return 
     */
    function startTag($parser, $name, $attribs)
    {
        $shortname = substr($name, 0, -1);
        
        if (isset($this->Tag2Object[$name])) {
            $objname = $this->Tag2Object[$name]; 
            // print "creating new object " . $this->objindex . " " . $this->curobj . " of type " . $objname . "\n";
            $this->objary[$this->numobjects] = new $objname ();
            $this->objindex++;
            $this->objstack[$this->objindex] = $this->numobjects;
            $this->curobj = $this->numobjects;
            $this->numobjects++; 
            // print "creating new object " . $this->objindex . " " . $this->curobj . " of type " . $objname . "\n";
            
            foreach ($attribs as $key => $value) {
                $this->objary[$this->curobj]->attribs[$key] = $value;
            } 
            
            $this->curname = $name;
            $this->obj = &$this->objary[$this->curobj];
            $this->nameary[$this->curobj] = $name;
            
            if ($this->objindex > 0) { // Parent contains this object
                $parent = &$this->objary[$this->objstack[$this->objindex - 1]];
                
                if (isset($parent->objary[$name . 's'])) { // add to objary
                    // print "pushing object of name " . $this->obj->name . " and class " . get_class($this->obj) . " on to parent objary of name " . $parent->name . " and class " . get_class($parent) . "\n";
                    array_push($parent->objary[$name . 's'], $this->objary[$this->curobj]);
                } 
                // elsif (isset($parent->objary[$this->objstack[$this->objindex - 1]])) {
                // array
                // }
                else { // simple contained object
                    $parent->objs[$name] = &$this->objary[$this->curobj];
                } 
            } 
        } elseif (substr($name, -1) == 's' && isset($this->Tag2Object[$shortname])) {
            // blank
        } else { // must be a variable for the current object
            $this->curvar = $name;
        } 
    } 

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @param 
     * @return 
     */
    function endTag($parser, $name)
    {
        if (isset($this->curvar)) {
            // end of a variable
            unset($this->curvar);
        } elseif ($name == $this->curname && isset($this->Tag2Object[$name])) {
            // end of an object
            $this->objindex--;
            
            if ($this->objindex >= 0) {
                $this->curobj  = $this->objstack[$this->objindex];
                $this->obj     = &$this->objary[$this->curobj];
                $this->curname = $this->obj->name;
            } 
        } else {
            // end of an object container
        } 
    } 

    /**TODO
     * Short Description...
     *
     * Longer description...
     *
     * @param 
     * @param 
     * @return 
     */
    function cData($parser, $data)
    {
        if (isset($this->curvar)) {
            $this->obj->vars[$this->curvar] = $data;
        } else {
            $this->obj->text = $data;
        } 
    } 
}

?>
