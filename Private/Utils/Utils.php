<?php	
	/* 
		These two arrays are globals that can be accessed by components to add
		extra css or javascript files to the page at runtime before the page
	 	is rendered. This then allows you to only add external files as required by each component. 
	 	
	 	An important factor to keep in mind is when transitioning from one component to another that you may
	 	accumulate redundant CSS and JS references. If this becomes an issue you can flush the arrays as needed.
	*/
	$extraCSS = array();
	$extraJS = array();
    $useHTML = true;
	
	function addJS($js_path)
	{
	    global $extraJS;
        if (! in_array($js_path, $extraJS))
	        $extraJS[] = $js_path;
	}
	
	function addCSS($css_path)
	{
	    global $extraCSS;
        if (! in_array($css_path, $extraCSS))
	        $extraCSS[] = $css_path;
	}
	
	function flushJS()
	{
	    global $extraJS;
	    $extraJS = array();
	}
	
	function flushCSS()
	{
	    global $extraCSS;
	    $extraCSS = array();
	}
    
    function useHTML()
    {
        global $useHTML;
        return $useHTML;
    }
    
    function setUseHTML($value) 
    {
        global $useHTML;
        $useHTML = $value;
    }
        
    //To remove all the hidden text not displayed on a webpage
    function strip_html_tags($str)
    {
        $str = preg_replace('/(<|>)\1{2}/is', '', $str);
        $str = preg_replace(
            array(// Remove invisible content
                '@<head[^>]*?>.*?</head>@siu',
                '@<style[^>]*?>.*?</style>@siu',
                '@<script[^>]*?.*?</script>@siu',
                '@<noscript[^>]*?.*?</noscript>@siu',
                ),
            "", //replace above with nothing
            $str );
        $str = replaceWhitespace($str);
        $str = strip_tags($str);
        return $str;
    }
    
    //To replace all types of whitespace with a single space
    function replaceWhitespace($str) 
    {
        $result = $str;
        foreach (array(
        "  ", " \t",  " \r",  " \n",
        "\t\t", "\t ", "\t\r", "\t\n",
        "\r\r", "\r ", "\r\t", "\r\n",
        "\n\n", "\n ", "\n\t", "\n\r",
        ) as $replacement) {
        $result = str_replace($replacement, $replacement[0], $result);
        }
        return $str !== $result ? replaceWhitespace($result) : $result;
    }
    
    
    //addButtonTagWithActions("",array("doNothing"), "", null, null, null, null)
    function addButtonTagWithActions($label, $actions, $class = null, $id = null, $callBack = null, $disabled = false, $innerHTML = null)
    {
    	$name = uniqid("blfm");
    	$str = "<button type=\"submit\" name=\"$name\" value=\"$label\"";
    	if ($class)
    		$str .= " class=\"$class\"";
    	if ($id)
    		$str .= " id=\"$id\"";
    	if ($disabled)
    		$str .= " disabled";
    
    	$str .= " onclick=\"";
    	foreach ($actions as $field => $action){
    		$str .= "this.form.elements['$field'].value='".doEncrypt($action)."';";
    	}
		
    	$str .= "\"";
    		
    	if ($callBack)
    		$str .= "return $callBack;";
    	 
    
    	if($innerHTML){
    		$str .= ">";
    		$str .= $innerHTML;
    		$str .= "</input>";
    	}
    	else{
    		$str .= "/";
    
    		$str .= ">";
    	}
    	 
    	debugln("returning string: " . $str);
    	echo $str;
    	//return "<button type=\"submit\" name=\"blfm59f122cb97ad5\" value=\"\" onclick=\"this.form.elements['0'].value='%98%15%09W%E3K%3C%D9%B2%A9%E5%A0%26%5CK%B4';><span class='glyphicon glyphicon-search'></span><\input>";
    }
    

?>