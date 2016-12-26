<?php

/**
 * 
 */
use CFPropertyList\CFPropertyList;
use CFPropertyList\CFTypeDetector;

class BaseJSONAction extends sfAction
{

    public function execute($request)
    {
        
    }

    /**
     * Return in JSON when requested via AJAX or as plain text when requested directly in debug mode
     * @param mixed $data
     */
    protected function returnJSON($data)
    {
        if (!count($data)) {
            $json = json_encode((object) null);
        } else {
            $json = json_encode($data);
        }
        $this->getResponse()->setHttpHeader('Content-type', 'application/json');
        return $this->renderText($json);
//    }
    }

    /**
     * Return JSON Header, for very short response data only.
     * 
     * Usage:
     * <code>
     * public function executeRefresh()
     * {
     *   $data = array(
     *     'title'   => 'My basic letter',
     *     'name'    => 'Mr.Brown',
     *     'version' => '2.0'
     *   );
     *   //Important
     *   return $this->returnHeader($data);
     * }
     * </code>
     * 
     * @param mixed $data
     * @return integer
     */
    protected function returnHeader($data)
    {
        $json = json_decode($data);
        $this->getResponse()->setHttpHeader('Content-type', 'application/json');
        $this->getResponse()->setHttpHeader('X-JSON', '(' . $json . ')');

        return sfView::HEADER_ONLY;
    }

    /**
     * Return in PLIST when requested via AJAX or as plain text when requested directly in debug mode
     * @param mixed $data
     */
    protected function returnPLIST($data, $render = true)
    {
        if (!count($data)) {
            $xml = "";
        } else {
            $plist = new CFPropertyList();
            $td = new CFTypeDetector();
            $guessedStructure = $td->toCFType($data);
            $plist->add($guessedStructure);
            $xml = $plist->toXML();
        }
        if (!$render) {
            return $xml;
        }
        $this->getResponse()->setHttpHeader('Content-type', 'text/xml');
        return $this->renderText($xml);
    }

    /**
     * Return in XML when requested via AJAX or as plain text when requested directly in debug mode
     * @param mixed $data
     */
    protected function returnXML($data, $render = true)
    {
        $this->getResponse()->setHttpHeader('Content-length', strlen($data));
        $this->getResponse()->setHttpHeader('Content-type', 'application/vnd.syncml.dm+xml');
        return $this->renderText($data);
    }

}
