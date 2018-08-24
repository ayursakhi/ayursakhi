<?php
namespace Codazon\AjaxLayeredNav\Plugin;
class NavigationStatePlugin
{
	public function afterGetActiveFilters($subject, $result)
    {
        $merged = $result;
		$final  = array();
		foreach ($merged as $current) {
			$duplicated = false;
			foreach($final as $item){
				if ($item->getName() == $current->getName()){
					$duplicated = true;
					break;
				}
			}
			if(!$duplicated){
				$final[] = $current;
			}
		}
        return $final;
    }
}